<?php
/**
 * TV Shows Archive Template
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 30px;">
	<div class="doodh-section-header">
		<h1 class="doodh-section-title"><i class="fas fa-tv" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Browse TV Series & Shows', 'vmtheme' ); ?></h1>
	</div>

	<!-- Dynamic Filter Bar -->
	<?php doodhtheme_render_filter_bar( 'tvshows' ); ?>

	<!-- TV Shows Grid -->
	<div class="doodh-grid">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				$tv_id      = get_the_ID();
				$tv_poster  = doodhtheme_get_poster_url( $tv_id );
				$tv_rating  = doodhtheme_get_rating( $tv_id );
				$tv_year    = doodhtheme_get_release_year( $tv_id );
				$tv_seasons = (int) get_post_meta( $tv_id, '_doodh_total_seasons', true ) ?: 1;
				?>
				<article class="doodh-card">
					<div class="doodh-card-poster-wrap">
						<img src="<?php echo esc_url( $tv_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $tv_id ) ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
						<span class="doodh-badge-top-left doodh-badge-quality" style="background:#2563eb;"><?php printf( esc_html__( 'SS %d', 'vmtheme' ), $tv_seasons ); ?></span>
						<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $tv_rating ); ?></span>
						<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
							<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
						</a>
					</div>
					<div class="doodh-card-body">
						<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
						<div class="doodh-card-meta">
							<span><?php echo esc_html( $tv_year ); ?></span>
							<span><?php esc_html_e( 'TV Series', 'vmtheme' ); ?></span>
						</div>
					</div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p style="grid-column: 1 / -1; color: var(--dt-text-muted); text-align:center; padding: 40px 0;">
				<?php esc_html_e( 'No TV shows found matching your filters.', 'vmtheme' ); ?>
			</p>
		<?php endif; ?>
	</div>

	<!-- Pagination -->
	<?php doodhtheme_render_pagination(); ?>
</main>

<?php
get_footer();
