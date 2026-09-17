<?php
/**
 * Download Links Manager & Display Controller
 *
 * Provides global and per-post enable/disable controls for the Download Links section,
 * admin settings submenus under Movies, TV Shows, Episodes, Posts, and Appearance,
 * and dynamic frontend table rendering.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Download Links Settings Submenus
 */
function doodhtheme_register_downloads_admin_menus() {
	// Appearance Submenu
	add_theme_page(
		__( 'Download Links Settings', 'vmtheme' ),
		__( 'Download Settings', 'vmtheme' ),
		'manage_options',
		'doodhtheme-downloads-settings',
		'doodhtheme_render_downloads_settings_page'
	);

	// Movies Submenu
	add_submenu_page(
		'edit.php?post_type=movies',
		__( 'Movie Download Links Settings', 'vmtheme' ),
		__( 'Download Settings', 'vmtheme' ),
		'manage_options',
		'doodhtheme-downloads-settings',
		'doodhtheme_render_downloads_settings_page'
	);

	// TV Shows Submenu
	add_submenu_page(
		'edit.php?post_type=tvshows',
		__( 'TV Show Download Links Settings', 'vmtheme' ),
		__( 'Download Settings', 'vmtheme' ),
		'manage_options',
		'doodhtheme-downloads-settings',
		'doodhtheme_render_downloads_settings_page'
	);

	// Episodes Submenu
	add_submenu_page(
		'edit.php?post_type=episodes',
		__( 'Episode Download Links Settings', 'vmtheme' ),
		__( 'Download Settings', 'vmtheme' ),
		'manage_options',
		'doodhtheme-downloads-settings',
		'doodhtheme_render_downloads_settings_page'
	);

	// Posts Submenu
	add_submenu_page(
		'edit.php',
		__( 'Posts Download Links Settings', 'vmtheme' ),
		__( 'Download Settings', 'vmtheme' ),
		'manage_options',
		'doodhtheme-downloads-settings',
		'doodhtheme_render_downloads_settings_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_register_downloads_admin_menus' );

/**
 * Check if Download Links Section is Enabled for a given Post or Post Type
 *
 * @param int|null $post_id Post ID or current post
 * @return bool True if enabled, false otherwise
 */
function doodhtheme_is_downloads_enabled( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( $post_id ) {
		// 1. Per-Post Specific Override
		$post_override = get_post_meta( $post_id, '_doodh_downloads_enabled', true );
		if ( $post_override === 'disabled' || $post_override === '0' || $post_override === 'no' || $post_override === 'off' ) {
			return false;
		}
		if ( $post_override === 'enabled' || $post_override === '1' || $post_override === 'yes' || $post_override === 'on' ) {
			return true;
		}

		// 2. Post-Type Specific Setting
		$post_type = get_post_type( $post_id );
		if ( $post_type ) {
			$opt_key = 'doodh_enable_downloads_' . $post_type;
			$type_enabled = get_option( $opt_key, '1' );
			if ( $type_enabled === '0' || $type_enabled === 'no' || $type_enabled === false || $type_enabled === 'off' ) {
				return false;
			}
		}
	}

	// 3. Global Master Switch (default: 1 / enabled)
	$global_enabled = get_option( 'doodh_enable_downloads_global', '1' );
	if ( $global_enabled === '0' || $global_enabled === 'no' || $global_enabled === false || $global_enabled === 'off' ) {
		return false;
	}

	return true;
}

/**
 * Render Download Links Section on Frontend
 *
 * @param int|null $post_id Post ID
 */
if ( ! function_exists( 'doodhtheme_render_downloads' ) ) {
	function doodhtheme_render_downloads( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Check if enabled
		if ( ! doodhtheme_is_downloads_enabled( $post_id ) ) {
			return;
		}

		$downloads = get_post_meta( $post_id, '_doodh_downloads', true );
		$active_dls = array();
		if ( is_array( $downloads ) ) {
			foreach ( $downloads as $dl ) {
				if ( ! empty( $dl['url'] ) ) {
					$active_dls[] = $dl;
				}
			}
		}

		// Settings options
		$title        = get_option( 'doodh_downloads_title', __( 'Download Links', 'vmtheme' ) );
		$badge_text   = get_option( 'doodh_downloads_badge_text', __( 'Verified Safe Links', 'vmtheme' ) );
		$show_badge   = get_option( 'doodh_downloads_show_badge', '1' );
		$btn_text     = get_option( 'doodh_downloads_btn_text', __( 'Download', 'vmtheme' ) );
		$new_tab      = get_option( 'doodh_downloads_new_tab', '1' );
		$empty_action = get_option( 'doodh_downloads_empty_action', 'hide' ); // 'hide' or 'notice'
		$empty_notice = get_option( 'doodh_downloads_empty_notice', __( 'Direct high-speed download links are being prepared for this title.', 'vmtheme' ) );

		if ( empty( $active_dls ) ) {
			if ( $empty_action === 'notice' ) {
				?>
				<div class="doodh-download-box doodh-download-box-empty" style="background:var(--dt-bg-surface); border:1px dashed var(--dt-border); border-radius:var(--dt-radius); padding:20px; text-align:center; color:var(--dt-text-muted); margin-bottom:35px;">
					<i class="fas fa-cloud-download-alt" style="font-size:24px; color:var(--dt-primary); margin-bottom:8px; display:block;"></i>
					<p style="margin:0; font-size:14px;"><?php echo esc_html( $empty_notice ); ?></p>
				</div>
				<?php
			}
			return;
		}

		$target_attr = ( $new_tab === '1' ) ? 'target="_blank" rel="nofollow noopener noreferrer"' : 'rel="nofollow"';
		?>
		<div class="doodh-download-box" id="downloads">
			<div class="doodh-box-header">
				<h3><i class="fas fa-download" style="color:var(--dt-primary); margin-right:8px;"></i> <?php echo esc_html( $title ); ?></h3>
				<?php if ( $show_badge === '1' && ! empty( $badge_text ) ) : ?>
					<span class="doodh-tag-safe"><i class="fas fa-shield-alt"></i> <?php echo esc_html( $badge_text ); ?></span>
				<?php endif; ?>
			</div>
			<div class="doodh-dl-table-responsive">
				<table class="doodh-dl-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Server', 'vmtheme' ); ?></th>
							<th><?php esc_html_e( 'Quality', 'vmtheme' ); ?></th>
							<th><?php esc_html_e( 'Size', 'vmtheme' ); ?></th>
							<th><?php esc_html_e( 'Format', 'vmtheme' ); ?></th>
							<th><?php esc_html_e( 'Action', 'vmtheme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $active_dls as $dl ) : 
							$server_name = ! empty( $dl['server'] ) ? $dl['server'] : 'Direct Server';
							$quality     = ! empty( $dl['quality'] ) ? $dl['quality'] : '1080p';
							$size        = ! empty( $dl['size'] ) ? $dl['size'] : 'HD';
							$format      = ! empty( $dl['format'] ) ? $dl['format'] : 'MP4 / MKV';
							$url         = $dl['url'];
							?>
							<tr>
								<td>
									<strong class="doodh-dl-server">
										<i class="fas fa-cloud-download-alt" style="margin-right:5px; color:var(--dt-primary);"></i>
										<?php echo esc_html( $server_name ); ?>
									</strong>
								</td>
								<td><span class="doodh-badge-quality"><?php echo esc_html( $quality ); ?></span></td>
								<td><?php echo esc_html( $size ); ?></td>
								<td><span class="doodh-badge-format"><?php echo esc_html( $format ); ?></span></td>
								<td>
									<a href="<?php echo esc_url( $url ); ?>" <?php echo $target_attr; ?> class="doodh-dl-button">
										<i class="fas fa-download"></i> <?php echo esc_html( $btn_text ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}

/**
 * Render Download Links Settings Page in WP Admin
 */
function doodhtheme_render_downloads_settings_page() {
	if ( isset( $_POST['doodh_save_downloads_settings'] ) && check_admin_referer( 'doodh_downloads_settings_nonce' ) ) {
		// Global Master Toggle
		$global_toggle = isset( $_POST['doodh_enable_downloads_global'] ) ? '1' : '0';
		update_option( 'doodh_enable_downloads_global', $global_toggle );

		// Post Type Toggles
		$movies_toggle   = isset( $_POST['doodh_enable_downloads_movies'] ) ? '1' : '0';
		$tvshows_toggle  = isset( $_POST['doodh_enable_downloads_tvshows'] ) ? '1' : '0';
		$episodes_toggle = isset( $_POST['doodh_enable_downloads_episodes'] ) ? '1' : '0';
		$posts_toggle    = isset( $_POST['doodh_enable_downloads_post'] ) ? '1' : '0';

		update_option( 'doodh_enable_downloads_movies', $movies_toggle );
		update_option( 'doodh_enable_downloads_tvshows', $tvshows_toggle );
		update_option( 'doodh_enable_downloads_episodes', $episodes_toggle );
		update_option( 'doodh_enable_downloads_post', $posts_toggle );

		// Section Customization
		update_option( 'doodh_downloads_title', sanitize_text_field( $_POST['doodh_downloads_title'] ?? 'Download Links' ) );
		update_option( 'doodh_downloads_badge_text', sanitize_text_field( $_POST['doodh_downloads_badge_text'] ?? 'Verified Safe Links' ) );
		update_option( 'doodh_downloads_show_badge', isset( $_POST['doodh_downloads_show_badge'] ) ? '1' : '0' );
		update_option( 'doodh_downloads_btn_text', sanitize_text_field( $_POST['doodh_downloads_btn_text'] ?? 'Download' ) );
		update_option( 'doodh_downloads_new_tab', isset( $_POST['doodh_downloads_new_tab'] ) ? '1' : '0' );
		update_option( 'doodh_downloads_empty_action', sanitize_text_field( $_POST['doodh_downloads_empty_action'] ?? 'hide' ) );
		update_option( 'doodh_downloads_empty_notice', sanitize_text_field( $_POST['doodh_downloads_empty_notice'] ?? '' ) );

		// Purge page & object cache so changes apply immediately to frontend
		if ( class_exists( 'Doodh_Speed_Optimizer' ) ) {
			Doodh_Speed_Optimizer::get_instance()->purge_cache();
		}
		$cache_dir = WP_CONTENT_DIR . '/cache/doodh-speed/';
		if ( is_dir( $cache_dir ) ) {
			$cache_files = glob( $cache_dir . '*.html' );
			if ( ! empty( $cache_files ) ) {
				foreach ( $cache_files as $f ) {
					if ( basename( $f ) !== 'index.html' ) {
						@unlink( $f );
					}
				}
			}
		}
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		echo '<div class="notice notice-success is-dismissible" style="margin-top:15px;"><p><strong>' . esc_html__( 'Download Links settings updated successfully and cache cleared!', 'vmtheme' ) . '</strong></p></div>';
	}

	$global_enabled  = get_option( 'doodh_enable_downloads_global', '1' );
	$movies_enabled  = get_option( 'doodh_enable_downloads_movies', '1' );
	$tvshows_enabled = get_option( 'doodh_enable_downloads_tvshows', '1' );
	$episodes_enabled= get_option( 'doodh_enable_downloads_episodes', '1' );
	$posts_enabled   = get_option( 'doodh_enable_downloads_post', '1' );

	$title        = get_option( 'doodh_downloads_title', 'Download Links' );
	$badge_text   = get_option( 'doodh_downloads_badge_text', 'Verified Safe Links' );
	$show_badge   = get_option( 'doodh_downloads_show_badge', '1' );
	$btn_text     = get_option( 'doodh_downloads_btn_text', 'Download' );
	$new_tab      = get_option( 'doodh_downloads_new_tab', '1' );
	$empty_action = get_option( 'doodh_downloads_empty_action', 'hide' );
	$empty_notice = get_option( 'doodh_downloads_empty_notice', 'Direct high-speed download links are being prepared for this title.' );
	?>
	<div class="wrap" style="max-width:980px; margin-top:20px;">
		<!-- Header Banner -->
		<div style="background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; padding:25px 30px; border-radius:12px; margin-bottom:25px; box-shadow:0 4px 20px rgba(0,0,0,0.15); display:flex; justify-content:space-between; align-items:center;">
			<div>
				<h1 style="color:#fff; margin:0 0 8px; font-size:24px; font-weight:800; display:flex; align-items:center; gap:12px;">
					<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:40px; height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 10px rgba(229,9,20,0.4);">
						<i class="dashicons dashicons-download" style="color:#fff; font-size:22px; line-height:40px; height:40px; width:40px;"></i>
					</span>
					<?php esc_html_e( 'Download Links Section Manager', 'vmtheme' ); ?>
				</h1>
				<p style="color:#94a3b8; margin:0; font-size:14px;">
					<?php esc_html_e( 'Control the visibility of the Download Links box globally or per post type (Movies, TV Shows, Episodes, Posts) and customize section titles & buttons.', 'vmtheme' ); ?>
				</p>
			</div>
			<div style="text-align:right;">
				<span style="display:inline-block; background:<?php echo $global_enabled === '1' ? '#10b981' : '#ef4444'; ?>; color:#fff; font-weight:700; font-size:12px; padding:6px 14px; border-radius:20px; letter-spacing:0.5px; text-transform:uppercase;">
					<i class="dashicons dashicons-<?php echo $global_enabled === '1' ? 'yes-alt' : 'dismiss'; ?>" style="font-size:15px; line-height:16px; margin-right:2px;"></i>
					<?php echo $global_enabled === '1' ? esc_html__( 'Global Downloads: ACTIVE (ON)', 'vmtheme' ) : esc_html__( 'Global Downloads: DISABLED (OFF)', 'vmtheme' ); ?>
				</span>
			</div>
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'doodh_downloads_settings_nonce' ); ?>

			<!-- Card 1: Master Global & Post Type Toggles -->
			<div class="postbox" style="border-radius:10px; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:25px; overflow:hidden;">
				<div class="postbox-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px;">
					<h2 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
						<i class="dashicons dashicons-admin-settings" style="color:#2563eb;"></i>
						<?php esc_html_e( '1. Visibility & Section Enable/Disable Controls', 'vmtheme' ); ?>
					</h2>
				</div>
				<div class="inside" style="padding:22px;">
					
					<!-- Master Global Switch -->
					<div style="background:<?php echo $global_enabled === '1' ? '#f0fdf4' : '#fef2f2'; ?>; border:1px solid <?php echo $global_enabled === '1' ? '#bbf7d0' : '#fecaca'; ?>; border-radius:8px; padding:16px 20px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;">
						<div>
							<strong style="font-size:15px; color:#1e293b; display:block; margin-bottom:4px;">
								<?php esc_html_e( 'Master Global Switch (Show / Hide on Entire Site)', 'vmtheme' ); ?>
							</strong>
							<span style="font-size:13px; color:#64748b;">
								<?php esc_html_e( 'When turned OFF, download links will be hidden from all pages and templates regardless of post settings.', 'vmtheme' ); ?>
							</span>
						</div>
						<label style="position:relative; display:inline-flex; align-items:center; cursor:pointer; background:#fff; padding:6px 14px; border:1px solid #cbd5e1; border-radius:6px;">
							<input type="checkbox" name="doodh_enable_downloads_global" value="1" <?php checked( $global_enabled, '1' ); ?> style="transform:scale(1.3); margin-right:8px;">
							<span style="font-weight:700; color:<?php echo $global_enabled === '1' ? '#16a34a' : '#dc2626'; ?>;">
								<?php echo $global_enabled === '1' ? 'ENABLED (ON)' : 'DISABLED (OFF)'; ?>
							</span>
						</label>
					</div>

					<h3 style="font-size:14px; font-weight:700; color:#334155; margin:20px 0 12px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
						<?php esc_html_e( 'Enable / Disable Per Post Type:', 'vmtheme' ); ?>
					</h3>

					<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
						
						<!-- Movies Toggle -->
						<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
								<strong style="color:#0f172a; font-size:14px;"><i class="dashicons dashicons-format-video" style="color:#e50914;"></i> Movies</strong>
								<input type="checkbox" name="doodh_enable_downloads_movies" value="1" <?php checked( $movies_enabled, '1' ); ?>>
							</div>
							<p style="margin:0; font-size:12px; color:#64748b;"><?php esc_html_e( 'Show download box on single movie pages.', 'vmtheme' ); ?></p>
						</div>

						<!-- TV Shows Toggle -->
						<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
								<strong style="color:#0f172a; font-size:14px;"><i class="dashicons dashicons-video-alt" style="color:#2563eb;"></i> TV Shows</strong>
								<input type="checkbox" name="doodh_enable_downloads_tvshows" value="1" <?php checked( $tvshows_enabled, '1' ); ?>>
							</div>
							<p style="margin:0; font-size:12px; color:#64748b;"><?php esc_html_e( 'Show download box on single TV Show pages.', 'vmtheme' ); ?></p>
						</div>

						<!-- Episodes Toggle -->
						<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
								<strong style="color:#0f172a; font-size:14px;"><i class="dashicons dashicons-playlist-video" style="color:#059669;"></i> Episodes</strong>
								<input type="checkbox" name="doodh_enable_downloads_episodes" value="1" <?php checked( $episodes_enabled, '1' ); ?>>
							</div>
							<p style="margin:0; font-size:12px; color:#64748b;"><?php esc_html_e( 'Show download box on single Episode pages.', 'vmtheme' ); ?></p>
						</div>

						<!-- Regular Posts Toggle -->
						<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
								<strong style="color:#0f172a; font-size:14px;"><i class="dashicons dashicons-admin-post" style="color:#7c3aed;"></i> Blog / Posts</strong>
								<input type="checkbox" name="doodh_enable_downloads_post" value="1" <?php checked( $posts_enabled, '1' ); ?>>
							</div>
							<p style="margin:0; font-size:12px; color:#64748b;"><?php esc_html_e( 'Show download box on standard WordPress posts.', 'vmtheme' ); ?></p>
						</div>

					</div>

					<div style="margin-top:16px; background:#eff6ff; border-left:4px solid #3b82f6; padding:10px 15px; border-radius:0 6px 6px 0;">
						<p style="margin:0; font-size:13px; color:#1e40af;">
							<strong><i class="dashicons dashicons-info" style="font-size:16px; line-height:16px;"></i> <?php esc_html_e( 'Per-Post Override Available:', 'vmtheme' ); ?></strong>
							<?php esc_html_e( 'You can also turn Download Links ON or OFF for any individual movie or episode directly from its Edit Post screen in the "Download Links & File Sources" meta box.', 'vmtheme' ); ?>
						</p>
					</div>

				</div>
			</div>

			<!-- Card 2: Section Customization & Labels -->
			<div class="postbox" style="border-radius:10px; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:25px; overflow:hidden;">
				<div class="postbox-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px;">
					<h2 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
						<i class="dashicons dashicons-art" style="color:#e50914;"></i>
						<?php esc_html_e( '2. Section Title, Badges & Link Options', 'vmtheme' ); ?>
					</h2>
				</div>
				<div class="inside" style="padding:22px;">
					
					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:220px;">
								<label for="doodh_downloads_title"><strong><?php esc_html_e( 'Section Heading Title', 'vmtheme' ); ?></strong></label>
							</th>
							<td>
								<input type="text" id="doodh_downloads_title" name="doodh_downloads_title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" style="font-weight:600;">
								<p class="description"><?php esc_html_e( 'Title displayed in the box header (Default: Download Links)', 'vmtheme' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="doodh_downloads_badge_text"><strong><?php esc_html_e( 'Security / Safety Badge', 'vmtheme' ); ?></strong></label>
							</th>
							<td>
								<div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
									<input type="text" id="doodh_downloads_badge_text" name="doodh_downloads_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" class="regular-text">
									<label style="font-size:13px; font-weight:600; color:#334155;">
										<input type="checkbox" name="doodh_downloads_show_badge" value="1" <?php checked( $show_badge, '1' ); ?>>
										<?php esc_html_e( 'Show Badge', 'vmtheme' ); ?>
									</label>
								</div>
								<p class="description"><?php esc_html_e( 'Safety badge displayed beside header (e.g. "Verified Safe Links", "SSL Fast Download", "Direct CDN")', 'vmtheme' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="doodh_downloads_btn_text"><strong><?php esc_html_e( 'Download Button Text', 'vmtheme' ); ?></strong></label>
							</th>
							<td>
								<input type="text" id="doodh_downloads_btn_text" name="doodh_downloads_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Action button label inside download rows (Default: Download)', 'vmtheme' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<strong><?php esc_html_e( 'Link Target & SEO', 'vmtheme' ); ?></strong>
							</th>
							<td>
								<label style="font-weight:600; color:#334155; display:block; margin-bottom:6px;">
									<input type="checkbox" name="doodh_downloads_new_tab" value="1" <?php checked( $new_tab, '1' ); ?>>
									<?php esc_html_e( 'Open download links in a new browser tab (target="_blank" rel="nofollow noopener")', 'vmtheme' ); ?>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<strong><?php esc_html_e( 'When No Links Exist', 'vmtheme' ); ?></strong>
							</th>
							<td>
								<label style="display:block; margin-bottom:8px;">
									<input type="radio" name="doodh_downloads_empty_action" value="hide" <?php checked( $empty_action, 'hide' ); ?>>
									<strong><?php esc_html_e( 'Auto-Hide Section (Recommended)', 'vmtheme' ); ?></strong> - <?php esc_html_e( 'Hide the download section completely if the post has no download links.', 'vmtheme' ); ?>
								</label>
								<label style="display:block; margin-bottom:12px;">
									<input type="radio" name="doodh_downloads_empty_action" value="notice" <?php checked( $empty_action, 'notice' ); ?>>
									<strong><?php esc_html_e( 'Show Notice Box', 'vmtheme' ); ?></strong> - <?php esc_html_e( 'Display a subtle "Coming Soon / Preparing links" placeholder.', 'vmtheme' ); ?>
								</label>
								<input type="text" name="doodh_downloads_empty_notice" value="<?php echo esc_attr( $empty_notice ); ?>" class="large-text" placeholder="Notice message..." style="<?php echo $empty_action === 'notice' ? '' : 'display:none;'; ?>" id="doodh_empty_notice_input">
							</td>
						</tr>
					</table>

				</div>
			</div>

			<!-- Save Button Bar -->
			<div style="padding:15px 0;">
				<button type="submit" name="doodh_save_downloads_settings" class="button button-primary button-large" style="background:#e50914; border-color:#b91c1c; font-weight:700; font-size:15px; padding:4px 25px; height:auto; box-shadow:0 3px 10px rgba(229,9,20,0.3);">
					<i class="dashicons dashicons-saved" style="line-height:28px;"></i> <?php esc_html_e( 'Save Download Settings', 'vmtheme' ); ?>
				</button>
			</div>

		</form>
	</div>

	<script>
	jQuery(document).ready(function($) {
		$('input[name="doodh_downloads_empty_action"]').on('change', function() {
			if ($(this).val() === 'notice') {
				$('#doodh_empty_notice_input').slideDown();
			} else {
				$('#doodh_empty_notice_input').slideUp();
			}
		});
	});
	</script>
	<?php
}
