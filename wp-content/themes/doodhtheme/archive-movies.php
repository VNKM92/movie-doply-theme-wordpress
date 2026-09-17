<?php
/**
 * Movies Archive Template
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
		<h1 class="doodh-section-title"><i class="fas fa-film" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Browse Movies', 'vmtheme' ); ?></h1>
	</div>

	<!-- Dynamic Filter Bar -->
	<?php doodhtheme_render_filter_bar( 'movies' ); ?>

	<!-- Movies Grid -->
	<div class="doodh-grid">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				$m_id      = get_the_ID();
				$m_poster  = doodhtheme_get_poster_url( $m_id );
				$m_rating  = doodhtheme_get_rating( $m_id );
				$m_year    = doodhtheme_get_release_year( $m_id );
				$m_quality = doodhtheme_get_quality_badge( $m_id );
				?>
				<article class="doodh-card">
					<div class="doodh-card-poster-wrap">
						<img src="<?php echo esc_url( $m_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $m_id ) ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
						<span class="doodh-badge-top-left doodh-badge-quality"><?php echo esc_html( $m_quality ); ?></span>
						<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $m_rating ); ?></span>
						<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
							<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
						</a>
					</div>
					<div class="doodh-card-body">
						<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
						<div class="doodh-card-meta">
							<span><?php echo esc_html( $m_year ); ?></span>
							<span><?php echo esc_html( doodhtheme_get_runtime_formatted( $m_id ) ); ?></span>
						</div>
					</div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p style="grid-column: 1 / -1; color: var(--dt-text-muted); text-align:center; padding: 40px 0;">
				<?php esc_html_e( 'No movies found matching your filters.', 'vmtheme' ); ?>
			</p>
		<?php endif; ?>
	</div>

	<!-- Pagination -->
	<?php doodhtheme_render_pagination(); ?>
</main>

<?php
get_footer();
