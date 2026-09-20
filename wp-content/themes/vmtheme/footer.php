<?php
/**
 * The Footer for DoodhTheme
 * Production-Grade Responsive Footer with Rich Content, Multi-Column Navigation, and DMCA Compliance
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$brand_name = doodhtheme_get_brand_name();
?>

<footer class="doodh-footer" id="site-footer">
	<div class="container">
		
		<!-- ══════════════════════════════════════════════════════════════
		     1. Footer Call-to-Action / Request Banner
		     ══════════════════════════════════════════════════════════════ -->
		<!-- <div class="doodh-footer-cta-card">
			<div class="doodh-footer-cta-left">
				<span class="doodh-footer-badge">
					<i class="fas fa-bolt"></i> <?php esc_html_e( 'Ultra HD Streaming & Fast Downloads', 'vmtheme' ); ?>
				</span>
				<h3 class="doodh-footer-cta-title">
					<?php esc_html_e( 'Can\'t find what you are looking for?', 'vmtheme' ); ?>
				</h3>
				<p class="doodh-footer-cta-desc">
					<?php esc_html_e( 'Submit a movie or TV series request to our content indexing team or explore our Top 100 leaderboard.', 'vmtheme' ); ?>
				</p>
			</div>
			<div class="doodh-footer-cta-right">
				<a href="<?php echo esc_url( home_url( '/request/' ) ); ?>" class="doodh-footer-btn-primary">
					<i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Request Title', 'vmtheme' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/top-imdb/' ) ); ?>" class="doodh-footer-btn-secondary">
					<i class="fas fa-trophy"></i> <?php esc_html_e( 'Top 100 IMDb', 'vmtheme' ); ?>
				</a>
			</div>
		</div> -->

		<!-- ══════════════════════════════════════════════════════════════
		     2. Multi-Column Navigation Grid
		     ══════════════════════════════════════════════════════════════ -->
		<div class="doodh-footer-main-grid">
			
			<!-- Column 1: Brand & Platform Summary -->
			<div class="doodh-footer-col doodh-footer-brand-col">
				<div class="doodh-footer-brand-wrap">
					<?php vmtheme_render_footer_logo(); ?>
				</div>
				<p class="doodh-footer-about-text">
					<?php echo vmtheme_get_footer_about_text(); ?>
				</p>
				
				<?php //vmtheme_render_footer_feature_pills(); ?>

				<!-- Community / Social Icons -->
				<?php vmtheme_render_footer_socials(); ?>
			</div>

			<!-- Column 2: Explore Catalog -->
			<div class="doodh-footer-col">
				<h4 class="doodh-footer-heading">
					<i class="fas fa-film"></i> <?php esc_html_e( 'Explore Catalog', 'vmtheme' ); ?>
				</h4>
				<ul class="doodh-footer-nav-list">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Home Feed', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Latest Movies', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Cinema Blog & News', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/years/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Release Years Archive', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Genres Directory', 'vmtheme' ); ?></a></li>

					<!--<li><a href="<?php //echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>"> <i class="fas fa-angle-right"></i> <?php //esc_html_e( 'TV Shows & Series', 'vmtheme' ); ?></a></li> -->
					<!-- <li><a href="<?php //echo esc_url( home_url( '/top-imdb/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php //esc_html_e( 'Top 100 IMDb', 'vmtheme' ); ?></a></li> -->
				</ul>
			</div>

			<!-- Column 3: Top Genres -->
			<div class="doodh-footer-col">
				<h4 class="doodh-footer-heading">
					<i class="fas fa-tags"></i> <?php esc_html_e( 'Popular Genres', 'vmtheme' ); ?>
				</h4>
				<ul class="doodh-footer-nav-list">
					<?php
					$footer_genres = get_terms( array(
						'taxonomy'   => 'genres',
						'number'     => 6,
						'orderby'    => 'count',
						'order'      => 'DESC',
						'hide_empty' => false,
					) );

					if ( ! empty( $footer_genres ) && ! is_wp_error( $footer_genres ) ) :
						foreach ( $footer_genres as $fg ) :
							?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $fg ) ); ?>">
									<i class="fas fa-angle-right"></i> <?php echo esc_html( $fg->name ); ?>
								</a>
							</li>
							<?php
						endforeach;
					else :
						?>
						<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Action Movies', 'vmtheme' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Sci-Fi & Fantasy', 'vmtheme' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Drama & Romance', 'vmtheme' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Animation & Anime', 'vmtheme' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Horror & Thriller', 'vmtheme' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Comedy', 'vmtheme' ); ?></a></li>
					<?php endif; ?>
					<li>
						<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>" style="color:var(--dt-primary); font-weight:700;">
							<i class="fas fa-arrow-right"></i> <?php esc_html_e( 'View All Genres', 'vmtheme' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<!-- Column 4: Account & Support -->
			<div class="doodh-footer-col">
				<h4 class="doodh-footer-heading">
					<i class="fas fa-user-circle"></i> <?php esc_html_e( 'Support & Tools', 'vmtheme' ); ?>
				</h4>
				<ul class="doodh-footer-nav-list">
					<!-- <li><a href="<?php //echo esc_url( home_url( '/request/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php //esc_html_e( 'Request Title', 'vmtheme' ); ?></a></li> -->
					<!-- <li><a href="<?php //echo esc_url( home_url( '/watchlist/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php //esc_html_e( 'My Watchlist', 'vmtheme' ); ?></a></li> -->
					<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'About Us', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Contact Support', 'vmtheme' ); ?></a></li>
					<?php if ( is_user_logged_in() ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/watchlist/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Account Settings', 'vmtheme' ); ?></a></li>
					<?php else : ?>
						<li><a href="#" class="doodh-auth-trigger"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Sign In / Register', 'vmtheme' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( home_url( '/dmca/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'DMCA', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Disclaimer', 'vmtheme' ); ?></a></li>
				</ul>
			</div>

			<!-- Column 5: Legal & DMCA -->
			<div class="doodh-footer-col">
				<h4 class="doodh-footer-heading">
					<i class="fas fa-shield-alt"></i> <?php esc_html_e( 'Legal & Policy', 'vmtheme' ); ?>
				</h4>
				<ul class="doodh-footer-nav-list">
					
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Privacy Policy', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'Terms of Service', 'vmtheme' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank"><i class="fas fa-angle-right"></i> <?php esc_html_e( 'XML Sitemap', 'vmtheme' ); ?></a></li>
				</ul>
			</div>

		</div>

		<!-- ══════════════════════════════════════════════════════════════
		     3. Non-Hosting Compliance Disclaimer Card
		     ══════════════════════════════════════════════════════════════ -->
		<div class="doodh-footer-disclaimer-box">
			<div class="doodh-disclaimer-icon"><i class="fas fa-info-circle"></i></div>
			<div class="doodh-disclaimer-content">
				<strong><?php esc_html_e( 'Non-Hosting & Copyright Notice:', 'vmtheme' ); ?></strong>
				<span>
					<?php printf( esc_html__( '%s does not host, upload, or store any video files on its servers. All streaming links and media are provided by non-affiliated third-party platforms. If you have any legal issues please contact the appropriate media file owners or host sites, or submit a DMCA notice to our team for prompt link deletion.', 'vmtheme' ), esc_html( $brand_name ) ); ?>
				</span>
			</div>
		</div>

		<!-- ══════════════════════════════════════════════════════════════
		     4. Footer Bottom Bar
		     ══════════════════════════════════════════════════════════════ -->
		<div class="doodh-footer-bottom-bar">
			<div class="doodh-footer-bottom-left">
				<p class="doodh-footer-copyright">
					<?php echo doodhtheme_get_footer_copyright(); ?>
				</p>
			</div>

			<div class="doodh-footer-bottom-center">
				<span class="doodh-footer-status-pill">
					<span class="doodh-status-pulse"></span>
					<?php esc_html_e( 'Cineladdoo Review', 'vmtheme' ); ?>
				</span>
			</div>

			<div class="doodh-footer-bottom-right">
				<button type="button" class="doodh-footer-backtotop" id="doodh-footer-backtotop" title="<?php esc_attr_e( 'Back to top', 'vmtheme' ); ?>">
					<span><?php esc_html_e( 'Back to Top', 'vmtheme' ); ?></span>
					<i class="fas fa-arrow-up"></i>
				</button>
			</div>
		</div>

	</div>
</footer>

<?php 
if ( function_exists( 'doodhtheme_render_auth_modal' ) ) {
	doodhtheme_render_auth_modal();
}
wp_footer(); ?>
</body>
</html>