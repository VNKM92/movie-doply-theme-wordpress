<?php
/**
 * The Footer for DoodhTheme
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="doodh-footer">
	<div class="container">
		<div class="doodh-footer-top" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
			<!-- Brand -->
			<div class="doodh-brand">
				<?php doodhtheme_render_brand_logo(); ?>
			</div>

			<!-- Footer Navigation -->
			<div class="doodh-footer-links" style="display:flex; gap:16px; flex-wrap:wrap; font-size:14px;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>"><?php esc_html_e( 'Movies', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>"><?php esc_html_e( 'TV Shows', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'About Us', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>"><?php esc_html_e( 'Disclaimer', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/dmca/' ) ); ?>"><?php esc_html_e( 'DMCA Notice', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'doodhtheme' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank"><?php esc_html_e( 'XML Sitemap', 'doodhtheme' ); ?></a>
			</div>
		</div>

		<!-- Legal / Non-Hosting Notice -->
		<div class="doodh-footer-dmca" style="margin: 20px 0; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: #64748b; line-height: 1.6;">
			<p style="margin:0;">
				<strong><?php esc_html_e( 'Non-Hosting Disclaimer:', 'doodhtheme' ); ?></strong> 
				<?php echo sprintf( esc_html__( '%s does not store any files or videos on its servers. All streaming links and media are provided and hosted by non-affiliated third-party services.', 'doodhtheme' ), esc_html( doodhtheme_get_brand_name() ) ); ?>
			</p>
		</div>

		<!-- Dynamic Copyright -->
		<div class="doodh-footer-copy" style="font-size: 13px; color: #94a3b8;">
			<p style="margin:0;"><?php echo doodhtheme_get_footer_copyright(); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>