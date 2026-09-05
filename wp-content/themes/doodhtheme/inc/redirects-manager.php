<?php
/**
 * Broken URL 301 Redirects & 404 Auto-Healing Manager
 *
 * Provides a dedicated WP Admin panel to manage custom 301/302 redirects,
 * track broken URLs / 404 logs, and auto-redirect dead links.
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Live 301/302 Redirects and 404 Capture
 */
function doodhtheme_handle_redirects_and_404s() {
	$request_uri = $_SERVER['REQUEST_URI'] ?? '';
	if ( empty( $request_uri ) || is_admin() || wp_doing_ajax() ) {
		return;
	}

	$clean_path = parse_url( $request_uri, PHP_URL_PATH );
	$clean_path = untrailingslashit( $clean_path );
	$site_root  = untrailingslashit( parse_url( home_url(), PHP_URL_PATH ) ?: '' );

	if ( $site_root && strpos( $clean_path, $site_root ) === 0 ) {
		$relative_path = substr( $clean_path, strlen( $site_root ) );
	} else {
		$relative_path = $clean_path;
	}
	$relative_path = '/' . ltrim( $relative_path, '/' );

	// 1. Check Custom Redirect Rules
	$rules = get_option( 'doodh_redirect_rules', array() );
	if ( ! empty( $rules ) && is_array( $rules ) ) {
		foreach ( $rules as $rule ) {
			$source = '/' . trim( parse_url( $rule['source'], PHP_URL_PATH ) ?: '', '/' );
			$check  = '/' . trim( $relative_path, '/' );

			if ( strtolower( $source ) === strtolower( $check ) && ! empty( $rule['target'] ) ) {
				$status = ( (int) ( $rule['code'] ?? 301 ) === 302 ) ? 302 : 301;
				wp_safe_redirect( $rule['target'], $status );
				exit;
			}
		}
	}

	// 2. Handle 404 Errors & Logging
	if ( is_404() ) {
		// Log 404 for Admin review
		doodhtheme_log_404_request( $request_uri );

		// Check if Auto 404 Redirect is enabled
		$auto_redirect = get_option( 'doodh_auto_404_redirect', 'no' );
		$target_404    = get_option( 'doodh_404_target_url', home_url( '/' ) );

		if ( $auto_redirect === 'yes' && ! empty( $target_404 ) ) {
			wp_safe_redirect( esc_url_raw( $target_404 ), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'doodhtheme_handle_redirects_and_404s', 1 );

/**
 * Log 404 Request in Options Table
 */
function doodhtheme_log_404_request( $uri ) {
	$uri = sanitize_text_field( substr( $uri, 0, 255 ) );
	if ( strpos( $uri, 'wp-content' ) !== false || strpos( $uri, 'favicon' ) !== false ) {
		return;
	}

	$logs = get_option( 'doodh_404_logs', array() );
	if ( ! is_array( $logs ) ) {
		$logs = array();
	}

	$hash = md5( $uri );
	if ( isset( $logs[ $hash ] ) ) {
		$logs[ $hash ]['hits']++;
		$logs[ $hash ]['last_seen'] = time();
	} else {
		// Keep last 100 entries max
		if ( count( $logs ) > 100 ) {
			array_shift( $logs );
		}
		$logs[ $hash ] = array(
			'uri'       => $uri,
			'referrer'  => sanitize_text_field( $_SERVER['HTTP_REFERER'] ?? 'Direct / Search Engine' ),
			'hits'      => 1,
			'last_seen' => time(),
		);
	}

	update_option( 'doodh_404_logs', $logs, false );
}

/**
 * Register Admin Menu for 301 Redirects
 */
function doodhtheme_register_redirects_menu() {
	add_theme_page(
		__( '301 Redirects & 404 Manager', 'doodhtheme' ),
		__( '301 & 404 Redirects', 'doodhtheme' ),
		'manage_options',
		'doodhtheme-redirects-manager',
		'doodhtheme_render_redirects_manager_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_register_redirects_menu' );

/**
 * Render 301 Redirects & 404 Manager Admin Page
 */
function doodhtheme_render_redirects_manager_page() {
	// Handle Add Rule
	if ( isset( $_POST['doodh_add_redirect'] ) && check_admin_referer( 'doodh_redirect_nonce' ) ) {
		$source = sanitize_text_field( $_POST['redirect_source'] ?? '' );
		$target = esc_url_raw( $_POST['redirect_target'] ?? '' );
		$code   = (int) ( $_POST['redirect_code'] ?? 301 );

		if ( $source && $target ) {
			$rules = get_option( 'doodh_redirect_rules', array() );
			$rules[] = array(
				'id'     => uniqid(),
				'source' => $source,
				'target' => $target,
				'code'   => $code,
				'date'   => current_time( 'mysql' ),
			);
			update_option( 'doodh_redirect_rules', $rules );
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Redirect rule added successfully!', 'doodhtheme' ) . '</strong></p></div>';
		}
	}

	// Handle Delete Rule
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['rule_id'] ) && check_admin_referer( 'doodh_del_rule_' . $_GET['rule_id'] ) ) {
		$rules = get_option( 'doodh_redirect_rules', array() );
		$new_rules = array();
		foreach ( $rules as $r ) {
			if ( ( $r['id'] ?? '' ) !== $_GET['rule_id'] ) {
				$new_rules[] = $r;
			}
		}
		update_option( 'doodh_redirect_rules', $new_rules );
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Redirect rule deleted.', 'doodhtheme' ) . '</strong></p></div>';
	}

	// Handle Save 404 Settings
	if ( isset( $_POST['doodh_save_404_settings'] ) && check_admin_referer( 'doodh_404_settings_nonce' ) ) {
		update_option( 'doodh_auto_404_redirect', sanitize_text_field( $_POST['doodh_auto_404_redirect'] ?? 'no' ) );
		update_option( 'doodh_404_target_url', esc_url_raw( $_POST['doodh_404_target_url'] ?? home_url( '/' ) ) );
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( '404 auto-healing settings updated!', 'doodhtheme' ) . '</strong></p></div>';
	}

	// Handle Clear 404 Logs
	if ( isset( $_POST['doodh_clear_404_logs'] ) && check_admin_referer( 'doodh_clear_logs_nonce' ) ) {
		update_option( 'doodh_404_logs', array() );
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( '404 error logs cleared.', 'doodhtheme' ) . '</strong></p></div>';
	}

	$rules        = get_option( 'doodh_redirect_rules', array() );
	$auto_404     = get_option( 'doodh_auto_404_redirect', 'no' );
	$target_404   = get_option( 'doodh_404_target_url', home_url( '/' ) );
	$logs         = get_option( 'doodh_404_logs', array() );
	?>
	<div class="wrap" style="max-width:1100px; margin-top:20px;">
		<div style="background:#111827; color:#fff; padding:24px; border-radius:10px; margin-bottom:25px; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
			<h1 style="color:#fff; margin:0 0 8px; font-size:24px; display:flex; align-items:center; gap:10px;">
				<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center;"><i class="dashicons dashicons-randomize" style="color:#fff; font-size:20px; line-height:36px; height:36px; width:36px;"></i></span>
				<?php esc_html_e( 'Broken URL 301 Redirects & 404 Healing Manager', 'doodhtheme' ); ?>
			</h1>
			<p style="color:#94a3b8; margin:0; font-size:14px;">
				<?php esc_html_e( 'Create 301/302 redirects for broken or deleted URLs, automatically heal 404 errors, and track visitor 404 logs.', 'doodhtheme' ); ?>
			</p>
		</div>

		<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
			<!-- Create New Redirect Rule -->
			<div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
				<h3 style="margin-top:0; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
					<i class="dashicons dashicons-plus-alt"></i> <?php esc_html_e( 'Add New Redirect Rule', 'doodhtheme' ); ?>
				</h3>
				<form method="post" action="">
					<?php wp_nonce_field( 'doodh_redirect_nonce' ); ?>
					<p>
						<label><strong><?php esc_html_e( 'Source Relative Path (Broken URL):', 'doodhtheme' ); ?></strong></label><br>
						<input type="text" name="redirect_source" placeholder="/old-movie-slug/ or /movies/dead-link" class="large-text" required>
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Target Destination URL:', 'doodhtheme' ); ?></strong></label><br>
						<input type="text" name="redirect_target" placeholder="<?php echo esc_url( home_url( '/movies/' ) ); ?>" class="large-text" required>
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Redirect Type:', 'doodhtheme' ); ?></strong></label><br>
						<select name="redirect_code" style="width:100%;">
							<option value="301" selected><?php esc_html_e( '301 Permanent (Recommended for SEO)', 'doodhtheme' ); ?></option>
							<option value="302"><?php esc_html_e( '302 Temporary', 'doodhtheme' ); ?></option>
						</select>
					</p>
					<p>
						<input type="submit" name="doodh_add_redirect" class="button button-primary" value="<?php esc_attr_e( 'Save Redirect Rule', 'doodhtheme' ); ?>">
					</p>
				</form>
			</div>

			<!-- 404 Auto-Healing Configuration -->
			<div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
				<h3 style="margin-top:0; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
					<i class="dashicons dashicons-shield-alt"></i> <?php esc_html_e( '404 Auto-Healing Options', 'doodhtheme' ); ?>
				</h3>
				<form method="post" action="">
					<?php wp_nonce_field( 'doodh_404_settings_nonce' ); ?>
					<p>
						<label><strong><?php esc_html_e( 'Auto-Redirect 404 Broken URLs:', 'doodhtheme' ); ?></strong></label><br>
						<select name="doodh_auto_404_redirect" style="width:100%;">
							<option value="no" <?php selected( $auto_404, 'no' ); ?>><?php esc_html_e( 'Disabled (Display standard 404 page)', 'doodhtheme' ); ?></option>
							<option value="yes" <?php selected( $auto_404, 'yes' ); ?>><?php esc_html_e( 'Enabled (301 Redirect all 404s to target URL)', 'doodhtheme' ); ?></option>
						</select>
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Fallback Target URL for 404s:', 'doodhtheme' ); ?></strong></label><br>
						<input type="text" name="doodh_404_target_url" value="<?php echo esc_attr( $target_404 ); ?>" class="large-text">
					</p>
					<p>
						<input type="submit" name="doodh_save_404_settings" class="button button-secondary" value="<?php esc_attr_e( 'Update 404 Settings', 'doodhtheme' ); ?>">
					</p>
				</form>
			</div>
		</div>

		<!-- Active Redirect Rules Table -->
		<div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; margin-top:25px;">
			<h3 style="margin-top:0;">
				<i class="dashicons dashicons-list-view"></i> <?php esc_html_e( 'Active 301 / 302 Redirect Rules', 'doodhtheme' ); ?> (<?php echo count( $rules ); ?>)
			</h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Source Path', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Target Destination', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Code', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Date Added', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Action', 'doodhtheme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $rules ) ) : ?>
						<?php foreach ( $rules as $r ) : ?>
							<tr>
								<td><code><?php echo esc_html( $r['source'] ); ?></code></td>
								<td><a href="<?php echo esc_url( $r['target'] ); ?>" target="_blank"><?php echo esc_html( $r['target'] ); ?></a></td>
								<td><span class="badge" style="background:#22c55e; color:#fff; padding:2px 6px; border-radius:4px; font-weight:bold;"><?php echo esc_html( $r['code'] ); ?></span></td>
								<td><?php echo esc_html( $r['date'] ?? '-' ); ?></td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'themes.php?page=doodhtheme-redirects-manager&action=delete&rule_id=' . $r['id'] ), 'doodh_del_rule_' . $r['id'] ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('Delete this redirect rule?');">
										<?php esc_html_e( 'Delete', 'doodhtheme' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="5" style="text-align:center; padding:15px; color:#64748b;">
								<?php esc_html_e( 'No custom redirect rules created yet.', 'doodhtheme' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- 404 Error Log Tracker -->
		<div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0; margin-top:25px;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
				<h3 style="margin:0;"><i class="dashicons dashicons-warning"></i> <?php esc_html_e( 'Live 404 Error Log (Broken Links Encountered by Users & Bots)', 'doodhtheme' ); ?></h3>
				<form method="post" action="" style="margin:0;">
					<?php wp_nonce_field( 'doodh_clear_logs_nonce' ); ?>
					<input type="submit" name="doodh_clear_404_logs" class="button button-small" value="<?php esc_attr_e( 'Clear 404 Logs', 'doodhtheme' ); ?>" onclick="return confirm('Clear all recorded 404 logs?');">
				</form>
			</div>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Broken URL Requested', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Hits Count', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Referrer', 'doodhtheme' ); ?></th>
						<th><?php esc_html_e( 'Last Encountered', 'doodhtheme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $logs ) ) : ?>
						<?php foreach ( array_reverse( $logs ) as $entry ) : ?>
							<tr>
								<td><strong style="color:#e11d48;"><?php echo esc_html( $entry['uri'] ); ?></strong></td>
								<td><span style="background:#f1f5f9; padding:2px 8px; border-radius:10px; font-weight:700;"><?php echo (int) $entry['hits']; ?></span></td>
								<td style="color:#64748b; font-size:12px;"><?php echo esc_html( $entry['referrer'] ); ?></td>
								<td><?php echo human_time_diff( $entry['last_seen'], time() ) . ' ago'; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="4" style="text-align:center; padding:15px; color:#22c55e;">
								<i class="dashicons dashicons-yes-alt"></i> <?php esc_html_e( 'Clean Record! No 404 errors logged.', 'doodhtheme' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
