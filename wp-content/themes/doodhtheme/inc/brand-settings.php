<?php
/**
 * Dynamic Brand Name, Logo, Media Fallbacks & SEO Alt Generator
 *
 * Centralizes theme branding, dynamic logos, SEO image attributes,
 * and media fallbacks so any brand change propagates globally.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Global Brand / Theme Name
 */
function vmtheme_get_brand_name() {
	$brand = get_option( 'vm_brand_name', get_option( 'doodh_brand_name' ) );
	if ( ! empty( $brand ) ) {
		return esc_html( $brand );
	}
	$site_name = get_bloginfo( 'name' );
	return ! empty( $site_name ) && $site_name !== 'WordPress' ? esc_html( $site_name ) : 'VMTheme';
}

if ( ! function_exists( 'doodhtheme_get_brand_name' ) ) {
	function doodhtheme_get_brand_name() {
		return vmtheme_get_brand_name();
	}
}

/**
 * Get Global Brand Tagline
 */
function vmtheme_get_brand_tagline() {
	$tagline = get_option( 'vm_brand_tagline', get_option( 'doodh_brand_tagline' ) );
	if ( ! empty( $tagline ) ) {
		return esc_html( $tagline );
	}
	$desc = get_bloginfo( 'description' );
	return ! empty( $desc ) ? esc_html( $desc ) : 'Watch HD Movies & TV Shows Online Free';
}

if ( ! function_exists( 'doodhtheme_get_brand_tagline' ) ) {
	function doodhtheme_get_brand_tagline() {
		return vmtheme_get_brand_tagline();
	}
}

/**
 * Get Brand Badge Text (e.g. "PRO", "UHD", "VIP")
 */
function vmtheme_get_brand_badge() {
	return esc_html( get_option( 'vm_brand_badge_text', get_option( 'doodh_brand_badge_text', 'PRO' ) ) );
}

if ( ! function_exists( 'doodhtheme_get_brand_badge' ) ) {
	function doodhtheme_get_brand_badge() {
		return vmtheme_get_brand_badge();
	}
}

/**
 * Render Global Brand Logo (Text or Image)
 */
function vmtheme_render_brand_logo() {
	$logo_url    = get_option( 'vm_brand_logo', get_option( 'doodh_brand_logo' ) );
	$logo_height = (int) get_option( 'vm_header_logo_height', 42 );
	$brand_name  = vmtheme_get_brand_name();
	$badge       = vmtheme_get_brand_badge();

	if ( ! empty( $logo_url ) ) {
		echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $brand_name ) . '" class="doodh-brand-img" style="max-height:' . esc_attr( $logo_height ) . 'px; width:auto; display:block;">';
	} else {
		// Modern SVG Play Icon + Styled Typography
		echo '<div class="doodh-brand-text-wrap" style="display:inline-flex; align-items:center; gap:8px;">';
		echo '<span class="doodh-brand-icon" style="background:linear-gradient(135deg,#e50914,#b91c1c); width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 10px rgba(229,9,20,0.4);"><i class="fas fa-play" style="color:#fff; font-size:13px; margin-left:2px;"></i></span>';
		echo '<span class="doodh-brand-title" style="font-size:20px; font-weight:900; color:#fff; letter-spacing:-0.5px;">' . $brand_name . '</span>';
		if ( ! empty( $badge ) ) {
			echo '<span class="brand-badge" style="background:var(--dt-primary); color:#fff; font-size:10px; font-weight:800; padding:2px 6px; border-radius:4px; text-transform:uppercase;">' . $badge . '</span>';
		}
		echo '</div>';
	}
}

if ( ! function_exists( 'doodhtheme_render_brand_logo' ) ) {
	function doodhtheme_render_brand_logo() {
		vmtheme_render_brand_logo();
	}
}

/**
 * Get Dynamic Footer Copyright Text
 */
function vmtheme_get_footer_copyright() {
	$brand_name = vmtheme_get_brand_name();
	$year       = date( 'Y' );
	$custom     = get_option( 'vm_footer_copyright', get_option( 'doodh_footer_copyright' ) );

	if ( ! empty( $custom ) ) {
		return str_replace( array( '%YEAR%', '%BRAND_NAME%' ), array( $year, $brand_name ), $custom );
	}

	return sprintf( '&copy; %s %s. All rights reserved. Watch Movies & TV Series in Ultra HD for Free.', esc_html( $year ), esc_html( $brand_name ) );
}

if ( ! function_exists( 'doodhtheme_get_footer_copyright' ) ) {
	function doodhtheme_get_footer_copyright() {
		return vmtheme_get_footer_copyright();
	}
}

/**
 * Get DMCA Email
 */
function vmtheme_get_dmca_email() {
	$email = get_option( 'vm_dmca_email', get_option( 'doodh_dmca_email' ) );
	if ( ! empty( $email ) ) {
		return sanitize_email( $email );
	}
	$host = ! empty( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : 'localhost';
	return 'dmca@' . preg_replace( '/^www\./', '', $host );
}

if ( ! function_exists( 'doodhtheme_get_dmca_email' ) ) {
	function doodhtheme_get_dmca_email() {
		return vmtheme_get_dmca_email();
	}
}

/**
 * Get Support / Contact Email
 */
function vmtheme_get_support_email() {
	$email = get_option( 'vm_support_email', get_option( 'doodh_support_email' ) );
	if ( ! empty( $email ) ) {
		return sanitize_email( $email );
	}
	$admin_email = get_option( 'admin_email' );
	return ! empty( $admin_email ) ? sanitize_email( $admin_email ) : 'support@vmtheme.com';
}

if ( ! function_exists( 'doodhtheme_get_support_email' ) ) {
	function doodhtheme_get_support_email() {
		return vmtheme_get_support_email();
	}
}

/**
 * Fallback Media Image URLs
 */
function vmtheme_get_fallback_poster_url() {
	return VMTHEME_URI . '/assets/images/poster-placeholder.svg';
}

if ( ! function_exists( 'doodhtheme_get_fallback_poster_url' ) ) {
	function doodhtheme_get_fallback_poster_url() {
		return vmtheme_get_fallback_poster_url();
	}
}

function vmtheme_get_fallback_backdrop_url() {
	return VMTHEME_URI . '/assets/images/backdrop-placeholder.svg';
}

if ( ! function_exists( 'doodhtheme_get_fallback_backdrop_url' ) ) {
	function doodhtheme_get_fallback_backdrop_url() {
		return vmtheme_get_fallback_backdrop_url();
	}
}

function vmtheme_get_fallback_avatar_url() {
	return VMTHEME_URI . '/assets/images/avatar-placeholder.svg';
}

if ( ! function_exists( 'doodhtheme_get_fallback_avatar_url' ) ) {
	function doodhtheme_get_fallback_avatar_url() {
		return vmtheme_get_fallback_avatar_url();
	}
}

/**
 * Generate SEO-Optimized Image Alt Tag
 *
 * @param int|null $post_id Post ID
 * @param string $context Context ('poster', 'backdrop', 'episode', 'cast', 'director')
 * @param string $custom_title Custom title or actor name
 * @return string
 */
function vmtheme_get_poster_alt( $post_id = null, $context = 'poster', $custom_title = '' ) {
	if ( ! empty( $custom_title ) ) {
		$title = $custom_title;
	} else {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$title = get_the_title( $post_id );
	}

	$brand = vmtheme_get_brand_name();

	if ( $context === 'cast' ) {
		return sprintf( esc_attr__( '%s - Actor Filmography & Biography Profile on %s', 'vmtheme' ), $title, $brand );
	} elseif ( $context === 'director' ) {
		return sprintf( esc_attr__( '%s - Director Movies, Shows & Film Catalog on %s', 'vmtheme' ), $title, $brand );
	} elseif ( $context === 'episode' ) {
		return sprintf( esc_attr__( '%s - Watch Episode Online Full HD Free on %s', 'vmtheme' ), $title, $brand );
	} elseif ( $context === 'backdrop' ) {
		return sprintf( esc_attr__( '%s - 4K Ultra HD Cinema Wallpaper & Backdrop on %s', 'vmtheme' ), $title, $brand );
	}

	return sprintf( esc_attr__( '%s Full Movie & Series HD Stream Poster - %s', 'vmtheme' ), $title, $brand );
}

if ( ! function_exists( 'doodhtheme_get_poster_alt' ) ) {
	function doodhtheme_get_poster_alt( $post_id = null, $context = 'poster', $custom_title = '' ) {
		return vmtheme_get_poster_alt( $post_id, $context, $custom_title );
	}
}

/**
 * Register Branding Settings Menu in WP Admin
 */
function vmtheme_register_brand_admin_menu() {
	add_theme_page(
		__( 'Brand & Site Identity', 'vmtheme' ),
		__( 'Brand & Identity', 'vmtheme' ),
		'manage_options',
		'vmtheme-brand-settings',
		'vmtheme_render_brand_settings_page'
	);
}
add_action( 'admin_menu', 'vmtheme_register_brand_admin_menu' );

if ( ! function_exists( 'doodhtheme_register_brand_admin_menu' ) ) {
	function doodhtheme_register_brand_admin_menu() {
		vmtheme_register_brand_admin_menu();
	}
}

/**
 * Render Brand & Site Identity Settings Page
 */
function vmtheme_render_brand_settings_page() {
	if ( isset( $_POST['vm_save_brand_settings'] ) || isset( $_POST['doodh_save_brand_settings'] ) ) {
		if ( check_admin_referer( 'vm_brand_settings_nonce' ) || check_admin_referer( 'doodh_brand_settings_nonce' ) ) {
			$name     = sanitize_text_field( $_POST['vm_brand_name'] ?? $_POST['doodh_brand_name'] ?? '' );
			$tagline  = sanitize_text_field( $_POST['vm_brand_tagline'] ?? $_POST['doodh_brand_tagline'] ?? '' );
			$logo     = esc_url_raw( $_POST['vm_brand_logo'] ?? $_POST['doodh_brand_logo'] ?? '' );
			$badge    = sanitize_text_field( $_POST['vm_brand_badge_text'] ?? $_POST['doodh_brand_badge_text'] ?? 'PRO' );
			$copy     = wp_kses_post( $_POST['vm_footer_copyright'] ?? $_POST['doodh_footer_copyright'] ?? '' );
			$dmca     = sanitize_email( $_POST['vm_dmca_email'] ?? $_POST['doodh_dmca_email'] ?? '' );
			$support  = sanitize_email( $_POST['vm_support_email'] ?? $_POST['doodh_support_email'] ?? '' );

			update_option( 'vm_brand_name', $name );
			update_option( 'doodh_brand_name', $name );
			update_option( 'vm_brand_tagline', $tagline );
			update_option( 'doodh_brand_tagline', $tagline );
			update_option( 'vm_brand_logo', $logo );
			update_option( 'doodh_brand_logo', $logo );
			update_option( 'vm_brand_badge_text', $badge );
			update_option( 'doodh_brand_badge_text', $badge );
			update_option( 'vm_footer_copyright', $copy );
			update_option( 'doodh_footer_copyright', $copy );
			update_option( 'vm_dmca_email', $dmca );
			update_option( 'doodh_dmca_email', $dmca );
			update_option( 'vm_support_email', $support );
			update_option( 'doodh_support_email', $support );

			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Brand & Site Identity settings updated globally across all templates, SEO tags, and emails!', 'vmtheme' ) . '</strong></p></div>';
		}
	}

	$brand_name   = vmtheme_get_brand_name();
	$tagline      = vmtheme_get_brand_tagline();
	$logo_url     = get_option( 'vm_brand_logo', get_option( 'doodh_brand_logo', '' ) );
	$badge_text   = vmtheme_get_brand_badge();
	$copyright    = get_option( 'vm_footer_copyright', get_option( 'doodh_footer_copyright', '© %YEAR% %BRAND_NAME%. All Rights Reserved. Designed for Cinema Lovers.' ) );
	$dmca_email   = vmtheme_get_dmca_email();
	$support_email= vmtheme_get_support_email();
	?>
	<div class="wrap" style="max-width:900px; margin-top:20px;">
		<div style="background:#111827; color:#fff; padding:24px; border-radius:10px; margin-bottom:25px; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
			<h1 style="color:#fff; margin:0 0 8px; font-size:24px; display:flex; align-items:center; gap:10px;">
				<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center;"><i class="dashicons dashicons-admin-appearance" style="color:#fff; font-size:20px; line-height:36px; height:36px; width:36px;"></i></span>
				<?php esc_html_e( 'Global Brand & Site Identity Manager', 'vmtheme' ); ?>
			</h1>
			<p style="color:#94a3b8; margin:0; font-size:14px;">
				<?php esc_html_e( 'Change your website brand name, logo, badge, and contact emails here. Changes apply everywhere automatically without editing code.', 'vmtheme' ); ?>
			</p>
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'vm_brand_settings_nonce' ); ?>

			<table class="form-table" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
				<tr>
					<th scope="row"><label for="vm_brand_name"><strong><?php esc_html_e( 'Website Brand Name', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="vm_brand_name" id="vm_brand_name" value="<?php echo esc_attr( $brand_name ); ?>" class="regular-text" style="font-size:16px; font-weight:700;">
						<p class="description"><?php esc_html_e( 'e.g. VMTheme, VMMovie, StreamSphere. Updates the navbar, footer, emails, schema, and sitemaps.', 'vmtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="vm_brand_tagline"><strong><?php esc_html_e( 'Brand Tagline / Slogan', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="vm_brand_tagline" id="vm_brand_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="large-text">
						<p class="description"><?php esc_html_e( 'Appears in hero banners, OpenGraph tags, and search engine title tags.', 'vmtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="vm_brand_badge_text"><strong><?php esc_html_e( 'Navbar Brand Badge', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="vm_brand_badge_text" id="vm_brand_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" style="width:100px; font-weight:bold;">
						<p class="description"><?php esc_html_e( 'Small pill badge next to the logo (e.g. PRO, 4K, VIP, HD).', 'vmtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="vm_brand_logo"><strong><?php esc_html_e( 'Custom Logo Image URL (Optional)', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="vm_brand_logo" id="vm_brand_logo" value="<?php echo esc_attr( $logo_url ); ?>" class="large-text" placeholder="https://yourdomain.com/wp-content/uploads/logo.png">
						<p class="description"><?php esc_html_e( 'Leave blank to use the modern dynamic CSS/SVG text logo with your brand name.', 'vmtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="vm_footer_copyright"><strong><?php esc_html_e( 'Footer Copyright Text', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="vm_footer_copyright" id="vm_footer_copyright" value="<?php echo esc_attr( $copyright ); ?>" class="large-text">
						<p class="description"><?php esc_html_e( 'Use %YEAR% for dynamic year and %BRAND_NAME% for dynamic brand name.', 'vmtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="vm_dmca_email"><strong><?php esc_html_e( 'DMCA & Legal Notice Email', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="email" name="vm_dmca_email" id="vm_dmca_email" value="<?php echo esc_attr( $dmca_email ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Displayed on the DMCA, Disclaimer, and Legal policy pages.', 'vmtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="vm_support_email"><strong><?php esc_html_e( 'Customer Support Email', 'vmtheme' ); ?></strong></label></th>
					<td>
						<input type="email" name="vm_support_email" id="vm_support_email" value="<?php echo esc_attr( $support_email ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Used for Contact Us notifications and customer support inquiries.', 'vmtheme' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="submit" style="margin-top:20px;">
				<input type="submit" name="vm_save_brand_settings" id="submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save Brand Settings Globally', 'vmtheme' ); ?>">
			</p>
		</form>
	</div>
	<?php
}

if ( ! function_exists( 'doodhtheme_render_brand_settings_page' ) ) {
	function doodhtheme_render_brand_settings_page() {
		vmtheme_render_brand_settings_page();
	}
}
