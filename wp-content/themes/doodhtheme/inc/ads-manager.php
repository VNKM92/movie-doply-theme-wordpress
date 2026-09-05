<?php
/**
 * AdSense & Monetization Ad Management System
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Ad Management Menu
 */
function doodhtheme_ads_admin_menu() {
	add_theme_page(
		__( 'Ad Management', 'doodhtheme' ),
		__( 'Ad Settings & Monetization', 'doodhtheme' ),
		'manage_options',
		'doodh-ad-settings',
		'doodhtheme_ads_settings_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_ads_admin_menu' );

/**
 * Ad Settings Admin Page
 */
function doodhtheme_ads_settings_page() {
	if ( isset( $_POST['doodh_save_ads'] ) && check_admin_referer( 'doodh_save_ads_nonce' ) ) {
		update_option( 'doodh_ad_header', wp_unslash( $_POST['doodh_ad_header'] ?? '' ) );
		update_option( 'doodh_ad_player_top', wp_unslash( $_POST['doodh_ad_player_top'] ?? '' ) );
		update_option( 'doodh_ad_player_bottom', wp_unslash( $_POST['doodh_ad_player_bottom'] ?? '' ) );
		update_option( 'doodh_ad_grid', wp_unslash( $_POST['doodh_ad_grid'] ?? '' ) );
		update_option( 'doodh_ad_grid_interval', (int) ( $_POST['doodh_ad_grid_interval'] ?? 8 ) );
		update_option( 'doodh_ad_footer_sticky', wp_unslash( $_POST['doodh_ad_footer_sticky'] ?? '' ) );
		update_option( 'doodh_ad_popunder_url', esc_url_raw( $_POST['doodh_ad_popunder_url'] ?? '' ) );
		update_option( 'doodh_ad_anti_adblock', isset( $_POST['doodh_ad_anti_adblock'] ) ? 1 : 0 );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Monetization & Ad settings saved successfully!', 'doodhtheme' ) . '</p></div>';
	}

	$ad_header        = get_option( 'doodh_ad_header', '' );
	$ad_player_top    = get_option( 'doodh_ad_player_top', '' );
	$ad_player_bottom = get_option( 'doodh_ad_player_bottom', '' );
	$ad_grid          = get_option( 'doodh_ad_grid', '' );
	$ad_grid_interval = get_option( 'doodh_ad_grid_interval', 8 );
	$ad_footer_sticky = get_option( 'doodh_ad_footer_sticky', '' );
	$ad_popunder_url  = get_option( 'doodh_ad_popunder_url', '' );
	$ad_anti_adblock  = get_option( 'doodh_ad_anti_adblock', 0 );
	?>
	<div class="wrap">
		<h1><i class="dashicons dashicons-money-alt" style="color:#46b450;"></i> <?php esc_html_e( 'AdSense & Ad Monetization Manager', 'doodhtheme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Paste your Google AdSense, PropellerAds, Adsterra, native ads, or custom HTML/banner code into the slots below.', 'doodhtheme' ); ?></p>

		<form method="post" action="" style="max-width:900px; margin-top:20px;">
			<?php wp_nonce_field( 'doodh_save_ads_nonce' ); ?>

			<!-- Header Ad Slot -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-align-center"></i> <?php esc_html_e( 'Header Top Banner Ad (728x90 / Responsive)', 'doodhtheme' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Displays below the primary navbar on all pages.', 'doodhtheme' ); ?></p>
				<textarea name="doodh_ad_header" rows="4" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $ad_header ); ?></textarea>
			</div>

			<!-- Above Video Player Ad -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-controls-play"></i> <?php esc_html_e( 'Above Video Player Billboard Ad', 'doodhtheme' ); ?></h3>
				<p class="description"><?php esc_html_e( 'High-CTR ad placed directly above the multi-server video player screen.', 'doodhtheme' ); ?></p>
				<textarea name="doodh_ad_player_top" rows="4" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $ad_player_top ); ?></textarea>
			</div>

			<!-- Below Video Player Ad -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-arrow-down-alt"></i> <?php esc_html_e( 'Below Video Player Ad', 'doodhtheme' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Displays immediately under the video player and server tabs.', 'doodhtheme' ); ?></p>
				<textarea name="doodh_ad_player_bottom" rows="4" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $ad_player_bottom ); ?></textarea>
			</div>

			<!-- In-Grid Native Stream Card Ad -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-grid-view"></i> <?php esc_html_e( 'In-Grid Native Stream Card Ad', 'doodhtheme' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Native ad card injected directly into the movie & TV show grids on the homepage and archive pages.', 'doodhtheme' ); ?></p>
				<div style="margin-bottom:10px;">
					<label><strong><?php esc_html_e( 'Inject every:', 'doodhtheme' ); ?></strong></label>
					<input type="number" name="doodh_ad_grid_interval" value="<?php echo esc_attr( $ad_grid_interval ); ?>" min="4" max="24" style="width:70px;"> cards
				</div>
				<textarea name="doodh_ad_grid" rows="4" style="width:100%; font-family:monospace;" placeholder="<a href='...' target='_blank'><img src='...' /></a>"><?php echo esc_textarea( $ad_grid ); ?></textarea>
			</div>

			<!-- Sticky Floating Bottom Ad -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-tag"></i> <?php esc_html_e( 'Sticky Floating Footer Banner Ad (Mobile & Desktop)', 'doodhtheme' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Anchored to the bottom of the viewport with an instant dismiss [X] button.', 'doodhtheme' ); ?></p>
				<textarea name="doodh_ad_footer_sticky" rows="3" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $ad_footer_sticky ); ?></textarea>
			</div>

			<!-- Popunder / Direct Link Ad -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-external"></i> <?php esc_html_e( 'Popunder / Direct Link Ad Trigger', 'doodhtheme' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Opens in a new tab when a visitor clicks the Play button on a movie or TV stream for the first time.', 'doodhtheme' ); ?></p>
				<input type="url" name="doodh_ad_popunder_url" value="<?php echo esc_attr( $ad_popunder_url ); ?>" class="regular-text" placeholder="https://your-ad-network-direct-link.com" style="width:100%;">
			</div>

			<!-- Anti-Adblock Warning Notice -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:20px; border-radius:8px; margin-bottom:20px;">
				<h3><i class="dashicons dashicons-shield"></i> <?php esc_html_e( 'Anti-AdBlock Banner Notice', 'doodhtheme' ); ?></h3>
				<label>
					<input type="checkbox" name="doodh_ad_anti_adblock" value="1" <?php checked( $ad_anti_adblock, 1 ); ?>>
					<?php esc_html_e( 'Show polite notice asking users to disable AdBlock to support free 4K streaming.', 'doodhtheme' ); ?>
				</label>
			</div>

			<p class="submit">
				<input type="submit" name="doodh_save_ads" class="button button-primary button-hero" value="<?php esc_attr_e( 'Save All Ad Settings', 'doodhtheme' ); ?>">
			</p>
		</form>
	</div>
	<?php
}

/**
 * Display Ad Slot Helper
 */
function doodhtheme_display_ad( $slot = 'header' ) {
	$code = '';
	switch ( $slot ) {
		case 'header':
			$code = get_option( 'doodh_ad_header', '' );
			break;
		case 'player_top':
			$code = get_option( 'doodh_ad_player_top', '' );
			break;
		case 'player_bottom':
			$code = get_option( 'doodh_ad_player_bottom', '' );
			break;
		case 'footer_sticky':
			$code = get_option( 'doodh_ad_footer_sticky', '' );
			break;
	}

	if ( empty( $code ) ) {
		return;
	}

	echo '<div class="doodh-ad-slot doodh-ad-' . esc_attr( $slot ) . '">';
	echo '<div class="doodh-ad-label">' . esc_html__( 'Advertisement', 'doodhtheme' ) . '</div>';
	echo $code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
}

/**
 * Render Floating Sticky Footer Ad
 */
function doodhtheme_render_sticky_footer_ad() {
	$code = get_option( 'doodh_ad_footer_sticky', '' );
	if ( empty( $code ) ) {
		return;
	}
	?>
	<div class="doodh-sticky-ad-wrapper" id="doodh-sticky-ad">
		<button type="button" class="doodh-sticky-ad-close" onclick="document.getElementById('doodh-sticky-ad').style.display='none';">&times;</button>
		<div class="doodh-sticky-ad-inner">
			<?php echo $code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'doodhtheme_render_sticky_footer_ad' );

/**
 * Render Popunder Script
 */
function doodhtheme_render_popunder_script() {
	$pop_url = get_option( 'doodh_ad_popunder_url', '' );
	if ( empty( $pop_url ) ) {
		return;
	}
	?>
	<script>
	(function() {
		var popTriggered = false;
		document.addEventListener('click', function(e) {
			if (!popTriggered && e.target.closest('.doodh-server-btn, .doodh-player-screen, #video-player-container')) {
				popTriggered = true;
				var win = window.open('<?php echo esc_url( $pop_url ); ?>', '_blank');
				if (win) {
					win.focus();
				}
			}
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'doodhtheme_render_popunder_script' );
