<?php
/**
 * Dynamic Brand Name, Logo, Media Fallbacks & SEO Alt Generator
 *
 * Centralizes theme branding, dynamic logos, SEO image attributes,
 * and media fallbacks so any brand change propagates globally.
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Global Brand / Theme Name
 */
function doodhtheme_get_brand_name() {
	$brand = get_option( 'doodh_brand_name' );
	if ( ! empty( $brand ) ) {
		return esc_html( $brand );
	}
	$site_name = get_bloginfo( 'name' );
	return ! empty( $site_name ) && $site_name !== 'WordPress' ? esc_html( $site_name ) : 'DoodhMovie';
}

/**
 * Get Global Brand Tagline
 */
function doodhtheme_get_brand_tagline() {
	$tagline = get_option( 'doodh_brand_tagline' );
	if ( ! empty( $tagline ) ) {
		return esc_html( $tagline );
	}
	$desc = get_bloginfo( 'description' );
	return ! empty( $desc ) ? esc_html( $desc ) : 'Watch HD Movies & TV Shows Online Free';
}

/**
 * Get Brand Badge Text (e.g. "PRO", "UHD", "VIP")
 */
function doodhtheme_get_brand_badge() {
	return esc_html( get_option( 'doodh_brand_badge_text', 'PRO' ) );
}

/**
 * Render Global Brand Logo (Text or Image)
 */
function doodhtheme_render_brand_logo() {
	$logo_url   = get_option( 'doodh_brand_logo' );
	$brand_name = doodhtheme_get_brand_name();
	$badge      = doodhtheme_get_brand_badge();

	if ( ! empty( $logo_url ) ) {
		echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $brand_name ) . '" class="doodh-brand-img" style="max-height:42px; width:auto;">';
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

/**
 * Get Dynamic Footer Copyright Text
 */
function doodhtheme_get_footer_copyright() {
	$brand_name = doodhtheme_get_brand_name();
	$year       = date( 'Y' );
	$custom     = get_option( 'doodh_footer_copyright' );

	if ( ! empty( $custom ) ) {
		return str_replace( array( '%YEAR%', '%BRAND_NAME%' ), array( $year, $brand_name ), $custom );
	}

	return sprintf( '&copy; %s %s. All rights reserved. Watch Movies & TV Series in Ultra HD for Free.', esc_html( $year ), esc_html( $brand_name ) );
}

/**
 * Get DMCA Email
 */
function doodhtheme_get_dmca_email() {
	$email = get_option( 'doodh_dmca_email' );
	if ( ! empty( $email ) ) {
		return sanitize_email( $email );
	}
	$host = ! empty( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : 'localhost';
	return 'dmca@' . preg_replace( '/^www\./', '', $host );
}

/**
 * Get Support / Contact Email
 */
function doodhtheme_get_support_email() {
	$email = get_option( 'doodh_support_email' );
	if ( ! empty( $email ) ) {
		return sanitize_email( $email );
	}
	$admin_email = get_option( 'admin_email' );
	return ! empty( $admin_email ) ? sanitize_email( $admin_email ) : 'support@doodhmovie.com';
}

/**
 * Fallback Media Image URLs
 */
function doodhtheme_get_fallback_poster_url() {
	return DOODHTHEME_URI . '/assets/images/poster-placeholder.svg';
}

function doodhtheme_get_fallback_backdrop_url() {
	return DOODHTHEME_URI . '/assets/images/backdrop-placeholder.svg';
}

function doodhtheme_get_fallback_avatar_url() {
	return DOODHTHEME_URI . '/assets/images/avatar-placeholder.svg';
}

/**
 * Generate SEO-Optimized Image Alt Tag
 *
 * @param int|null $post_id Post ID
 * @param string $context Context ('poster', 'backdrop', 'episode', 'cast', 'director')
 * @param string $custom_title Custom title or actor name
 * @return string
 */
function doodhtheme_get_poster_alt( $post_id = null, $context = 'poster', $custom_title = '' ) {
	if ( ! empty( $custom_title ) ) {
		$title = $custom_title;
	} else {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$title = get_the_title( $post_id );
	}

	$brand = doodhtheme_get_brand_name();

	if ( $context === 'cast' ) {
		return sprintf( esc_attr__( '%s - Actor Filmography & Biography Profile on %s', 'doodhtheme' ), $title, $brand );
	} elseif ( $context === 'director' ) {
		return sprintf( esc_attr__( '%s - Director Movies, Shows & Film Catalog on %s', 'doodhtheme' ), $title, $brand );
	} elseif ( $context === 'episode' ) {
		return sprintf( esc_attr__( '%s - Watch Episode Online Full HD Free on %s', 'doodhtheme' ), $title, $brand );
	} elseif ( $context === 'backdrop' ) {
		return sprintf( esc_attr__( '%s - 4K Ultra HD Cinema Wallpaper & Backdrop on %s', 'doodhtheme' ), $title, $brand );
	}

	return sprintf( esc_attr__( '%s Full Movie & Series HD Stream Poster - %s', 'doodhtheme' ), $title, $brand );
}

/**
 * Register Branding Settings Menu in WP Admin
 */
function doodhtheme_register_brand_admin_menu() {
	add_theme_page(
		__( 'Brand & Site Identity', 'doodhtheme' ),
		__( 'Brand & Identity', 'doodhtheme' ),
		'manage_options',
		'doodhtheme-brand-settings',
		'doodhtheme_render_brand_settings_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_register_brand_admin_menu' );

/**
 * Render Brand & Site Identity Settings Page
 */
function doodhtheme_render_brand_settings_page() {
	if ( isset( $_POST['doodh_save_brand_settings'] ) && check_admin_referer( 'doodh_brand_settings_nonce' ) ) {
		update_option( 'doodh_brand_name', sanitize_text_field( $_POST['doodh_brand_name'] ?? '' ) );
		update_option( 'doodh_brand_tagline', sanitize_text_field( $_POST['doodh_brand_tagline'] ?? '' ) );
		update_option( 'doodh_brand_logo', esc_url_raw( $_POST['doodh_brand_logo'] ?? '' ) );
		update_option( 'doodh_brand_badge_text', sanitize_text_field( $_POST['doodh_brand_badge_text'] ?? 'PRO' ) );
		update_option( 'doodh_footer_copyright', wp_kses_post( $_POST['doodh_footer_copyright'] ?? '' ) );
		update_option( 'doodh_dmca_email', sanitize_email( $_POST['doodh_dmca_email'] ?? '' ) );
		update_option( 'doodh_support_email', sanitize_email( $_POST['doodh_support_email'] ?? '' ) );

		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Brand & Site Identity settings updated globally across all templates, SEO tags, and emails!', 'doodhtheme' ) . '</strong></p></div>';
	}

	$brand_name   = doodhtheme_get_brand_name();
	$tagline      = doodhtheme_get_brand_tagline();
	$logo_url     = get_option( 'doodh_brand_logo', '' );
	$badge_text   = doodhtheme_get_brand_badge();
	$copyright    = get_option( 'doodh_footer_copyright', '© %YEAR% %BRAND_NAME%. All Rights Reserved. Designed for Cinema Lovers.' );
	$dmca_email   = doodhtheme_get_dmca_email();
	$support_email= doodhtheme_get_support_email();
	?>
	<div class="wrap" style="max-width:900px; margin-top:20px;">
		<div style="background:#111827; color:#fff; padding:24px; border-radius:10px; margin-bottom:25px; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
			<h1 style="color:#fff; margin:0 0 8px; font-size:24px; display:flex; align-items:center; gap:10px;">
				<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center;"><i class="dashicons dashicons-admin-appearance" style="color:#fff; font-size:20px; line-height:36px; height:36px; width:36px;"></i></span>
				<?php esc_html_e( 'Global Brand & Site Identity Manager', 'doodhtheme' ); ?>
			</h1>
			<p style="color:#94a3b8; margin:0; font-size:14px;">
				<?php esc_html_e( 'Change your website brand name, logo, badge, and contact emails here. Changes apply everywhere automatically without editing code.', 'doodhtheme' ); ?>
			</p>
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'doodh_brand_settings_nonce' ); ?>

			<table class="form-table" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
				<tr>
					<th scope="row"><label for="doodh_brand_name"><strong><?php esc_html_e( 'Website Brand Name', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="doodh_brand_name" id="doodh_brand_name" value="<?php echo esc_attr( $brand_name ); ?>" class="regular-text" style="font-size:16px; font-weight:700;">
						<p class="description"><?php esc_html_e( 'e.g. DoodhMovie, CineFlix, StreamSphere. Updates the navbar, footer, emails, schema, and sitemaps.', 'doodhtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="doodh_brand_tagline"><strong><?php esc_html_e( 'Brand Tagline / Slogan', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="doodh_brand_tagline" id="doodh_brand_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="large-text">
						<p class="description"><?php esc_html_e( 'Appears in hero banners, OpenGraph tags, and search engine title tags.', 'doodhtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="doodh_brand_badge_text"><strong><?php esc_html_e( 'Navbar Brand Badge', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="doodh_brand_badge_text" id="doodh_brand_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" style="width:100px; font-weight:bold;">
						<p class="description"><?php esc_html_e( 'Small pill badge next to the logo (e.g. PRO, 4K, VIP, HD).', 'doodhtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="doodh_brand_logo"><strong><?php esc_html_e( 'Custom Logo Image URL (Optional)', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="doodh_brand_logo" id="doodh_brand_logo" value="<?php echo esc_attr( $logo_url ); ?>" class="large-text" placeholder="https://yourdomain.com/wp-content/uploads/logo.png">
						<p class="description"><?php esc_html_e( 'Leave blank to use the modern dynamic CSS/SVG text logo with your brand name.', 'doodhtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="doodh_footer_copyright"><strong><?php esc_html_e( 'Footer Copyright Text', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="text" name="doodh_footer_copyright" id="doodh_footer_copyright" value="<?php echo esc_attr( $copyright ); ?>" class="large-text">
						<p class="description"><?php esc_html_e( 'Use %YEAR% for dynamic year and %BRAND_NAME% for dynamic brand name.', 'doodhtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="doodh_dmca_email"><strong><?php esc_html_e( 'DMCA & Legal Notice Email', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="email" name="doodh_dmca_email" id="doodh_dmca_email" value="<?php echo esc_attr( $dmca_email ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Displayed on the DMCA, Disclaimer, and Legal policy pages.', 'doodhtheme' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="doodh_support_email"><strong><?php esc_html_e( 'Customer Support Email', 'doodhtheme' ); ?></strong></label></th>
					<td>
						<input type="email" name="doodh_support_email" id="doodh_support_email" value="<?php echo esc_attr( $support_email ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Used for Contact Us notifications and customer support inquiries.', 'doodhtheme' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="submit" style="margin-top:20px;">
				<input type="submit" name="doodh_save_brand_settings" id="submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save Brand Settings Globally', 'doodhtheme' ); ?>">
			</p>
		</form>
	</div>
	<?php
}
