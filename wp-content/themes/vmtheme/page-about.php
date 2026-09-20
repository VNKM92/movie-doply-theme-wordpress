<?php
/**
 * Template Name: About Us Page
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 30px; padding-bottom: 60px; min-height: 65vh; max-width: 1000px;">
	<?php
	if ( function_exists( 'doodhtheme_render_breadcrumbs' ) ) {
		doodhtheme_render_breadcrumbs();
	}
	?>

	<div style="background:var(--dt-bg-surface); padding: 40px; border-radius:var(--dt-radius); border:1px solid var(--dt-border); margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
		<header style="margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 20px;">
			<h1 style="font-size: 32px; font-weight: 800; color: #fff; margin: 0 0 10px; display:flex; align-items:center; gap:12px;">
				<i class="fas fa-film" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'About', 'vmtheme' ); ?> <?php echo esc_html( doodhtheme_get_brand_name() ); ?>
			</h1>
			<p style="color: #94a3b8; margin: 0; font-size: 15px;">
				<?php echo esc_html( doodhtheme_get_brand_tagline() ); ?>
			</p>
		</header>

		<div class="doodh-page-body" style="color: #cbd5e1; line-height: 1.85; font-size: 15px;">
			<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom: 35px;">
				<div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:20px; border-radius:10px; text-align:center;">
					<i class="fas fa-bolt" style="font-size:32px; color:var(--dt-accent-yellow); margin-bottom:10px;"></i>
					<h4 style="color:#fff; margin:0 0 6px; font-size:16px;"><?php esc_html_e( 'Lightning Fast CDN', 'vmtheme' ); ?></h4>
					<p style="color:#94a3b8; font-size:13px; margin:0;"><?php esc_html_e( 'High-bitrate cloud edge streaming clusters worldwide.', 'vmtheme' ); ?></p>
				</div>
				<div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:20px; border-radius:10px; text-align:center;">
					<i class="fas fa-tv" style="font-size:32px; color:var(--dt-primary); margin-bottom:10px;"></i>
					<h4 style="color:#fff; margin:0 0 6px; font-size:16px;"><?php esc_html_e( '4K & 1080p Ultra HD', 'vmtheme' ); ?></h4>
					<p style="color:#94a3b8; font-size:13px; margin:0;"><?php esc_html_e( 'Crystal clear visuals and cinematic Dolby sound.', 'vmtheme' ); ?></p>
				</div>
				<div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:20px; border-radius:10px; text-align:center;">
					<i class="fas fa-mobile-alt" style="font-size:32px; color:#10b981; margin-bottom:10px;"></i>
					<h4 style="color:#fff; margin:0 0 6px; font-size:16px;"><?php esc_html_e( '100% Cross-Device', 'vmtheme' ); ?></h4>
					<p style="color:#94a3b8; font-size:13px; margin:0;"><?php esc_html_e( 'Flawless playback across Phones, Tablets, & Smart TVs.', 'vmtheme' ); ?></p>
				</div>
			</div>

			<h3 style="color:#fff; font-size:22px; margin-top:30px;"><?php esc_html_e( 'Our Mission', 'vmtheme' ); ?></h3>
			<p>
				<?php echo esc_html( doodhtheme_get_brand_name() ); ?> <?php esc_html_e( 'was built from the ground up for film enthusiasts and binge-watchers who demand instant access to cinema classics, trending TV series, and new releases with zero buffering. We index and catalog rich metadata, high-resolution artwork, trailers, and verified ratings.', 'vmtheme' ); ?>
			</p>

			<h3 style="color:#fff; font-size:22px; margin-top:30px;"><?php esc_html_e( 'Legal & Content Aggregation Disclaimer', 'vmtheme' ); ?></h3>
			<p>
				<?php echo esc_html( doodhtheme_get_brand_name() ); ?> <?php esc_html_e( 'does not host, upload, or store any media files on its servers. All videos and stream links are indexed from publicly available third-party sources across the internet. For DMCA inquiries or content removal requests, please visit our dedicated DMCA notice page.', 'vmtheme' ); ?>
			</p>

			<div style="margin-top:30px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.08); display:flex; gap:15px; flex-wrap:wrap;">
				<a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>" class="doodh-btn-primary" style="padding:12px 24px;">
					<i class="fas fa-envelope"></i> <?php esc_html_e( 'Contact Support', 'vmtheme' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/dmca/' ) ); ?>" class="button button-secondary" style="padding:10px 20px; font-size:14px; font-weight:600;">
					<i class="fas fa-shield-alt"></i> <?php esc_html_e( 'DMCA Policy', 'vmtheme' ); ?>
				</a>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
