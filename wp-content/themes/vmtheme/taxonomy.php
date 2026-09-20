<?php
/**
 * Taxonomy Archive Template (Genres, Release Years, Quality, Actors, Directors)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
?>

<main class="container" style="padding-top: 30px;">
	<?php doodhtheme_render_breadcrumbs(); ?>

	<!-- Header Ad Slot -->
	<?php doodhtheme_display_ad( 'header' ); ?>

	<div class="doodh-section-header">
		<h1 class="doodh-section-title">
			<i class="fas fa-tag" style="color:var(--dt-primary);"></i> 
			<?php echo esc_html( $term->name ); ?>
		</h1>
	</div>

	<!-- Filter Bar -->
	<?php doodhtheme_render_filter_bar(); ?>

	<!-- Grid -->
	<div class="doodh-grid">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				$post_id   = get_the_ID();
				$poster    = doodhtheme_get_poster_url( $post_id );
				$rating    = doodhtheme_get_rating( $post_id );
				$year      = doodhtheme_get_release_year( $post_id );
				$quality   = doodhtheme_get_quality_badge( $post_id );
				?>
				<article class="doodh-card">
					<div class="doodh-card-poster-wrap">
						<img src="<?php echo esc_url( $poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $post_id ) ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
						<span class="doodh-badge-top-left doodh-badge-quality"><?php echo esc_html( $quality ); ?></span>
						<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $rating ); ?></span>
						<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
							<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
						</a>
					</div>
					<div class="doodh-card-body">
						<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
						<div class="doodh-card-meta">
							<span><?php echo esc_html( $year ); ?></span>
							<span><?php echo esc_html( doodhtheme_get_runtime_formatted( $post_id ) ); ?></span>
						</div>
					</div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p style="grid-column: 1 / -1; color: var(--dt-text-muted); text-align:center; padding: 40px 0;">
				<?php esc_html_e( 'No titles found in this category.', 'vmtheme' ); ?>
			</p>
		<?php endif; ?>
	</div>

	<!-- Pagination -->
	<?php doodhtheme_render_pagination(); ?>

	<!-- Dynamic Bottom Content & Rich Description Section (Admin Handled) -->
	<?php
	$bottom_title = '';
	$bottom_desc  = '';
	if ( ! empty( $term->term_id ) ) {
		$bottom_title = get_term_meta( $term->term_id, '_dt_genre_bottom_title', true );
		$bottom_desc  = get_term_meta( $term->term_id, '_dt_genre_bottom_description', true );
		if ( empty( $bottom_desc ) && ! empty( $term->description ) ) {
			$bottom_desc = $term->description;
		}
	}
	if ( ! empty( $bottom_title ) || ! empty( $bottom_desc ) ) :
	?>
		<section class="doodh-section doodh-genre-bottom-section" style="margin-top: 45px; margin-bottom: 25px;">
			<div class="doodh-genre-bottom-card" style="background: var(--dt-bg-surface); border: 1px solid var(--dt-border); border-radius: var(--dt-radius); padding: 28px; box-shadow: var(--dt-shadow);">
				<?php if ( ! empty( $bottom_title ) ) : ?>
					<div class="doodh-section-header" style="margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px;">
						<h2 class="doodh-section-title" style="font-size: 20px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px;">
							<i class="fas fa-align-left" style="color: var(--dt-primary);"></i>
							<?php echo esc_html( $bottom_title ); ?>
						</h2>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $bottom_desc ) ) : ?>
					<div class="doodh-rich-content doodh-genre-seo-text" style="color: #cbd5e1; font-size: 15px; line-height: 1.8;">
						<?php echo wpautop( wp_kses_post( $bottom_desc ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
