<?php
/**
 * The template for displaying archive pages
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Check if this archive is for standard blog posts (category, tag, date, author)
$is_blog_archive = is_category() || is_tag() || is_date() || is_author() || ( ! is_post_type_archive() && get_post_type() === 'post' );
?>

<main class="container" style="padding-top: 35px; padding-bottom: 60px;">
	<div class="doodh-section-header">
		<h1 class="doodh-section-title">
			<i class="fas fa-folder-open" style="color:var(--dt-primary);"></i> 
			<?php the_archive_title(); ?>
		</h1>
	</div>

	<?php if ( the_archive_description() ) : ?>
		<div class="archive-description" style="color: var(--dt-text-muted); margin-bottom: 25px; font-size: 15px;">
			<?php the_archive_description(); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! $is_blog_archive ) : ?>
		<!-- Dynamic Filter Bar for Cinema CPTs -->
		<?php doodhtheme_render_filter_bar(); ?>
	<?php endif; ?>

	<?php if ( $is_blog_archive ) : ?>
		<!-- Blog Grid -->
		<div class="vmtheme-blog-grid">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					$post_id   = get_the_ID();
					$thumb_url = vmtheme_get_blog_thumbnail_url( $post_id, 'medium_large' );
					$post_cats = get_the_category( $post_id );
					$cat_label = ! empty( $post_cats ) ? $post_cats[0]->name : __( 'Cinema News', 'vmtheme' );
					$read_time = vmtheme_get_reading_time( $post_id );
					$author    = get_the_author();
					?>
					<article class="vmtheme-blog-card">
						<div class="vmtheme-blog-card-thumb-wrap">
							<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" class="vmtheme-blog-card-thumb" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( vmtheme_get_fallback_blog_poster_url() ); ?>';">
							<span class="vmtheme-blog-cat-badge"><?php echo esc_html( $cat_label ); ?></span>
							<a href="<?php the_permalink(); ?>" class="vmtheme-blog-card-overlay"></a>
						</div>
						<div class="vmtheme-blog-card-body">
							<div class="vmtheme-blog-meta-top">
								<span><i class="far fa-calendar-alt"></i> <?php echo get_the_date( 'M j, Y' ); ?></span>
								<span><i class="far fa-clock"></i> <?php echo esc_html( $read_time ); ?></span>
							</div>
							<h3 class="vmtheme-blog-card-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="vmtheme-blog-card-excerpt">
								<?php echo wp_trim_words( get_the_excerpt(), 18, '...' ); ?>
							</p>
							<div class="vmtheme-blog-card-footer">
								<span class="vmtheme-author-name"><i class="far fa-user"></i> <?php echo esc_html( $author ); ?></span>
								<a href="<?php the_permalink(); ?>" class="vmtheme-read-link">
									<?php esc_html_e( 'Read Article', 'vmtheme' ); ?> <i class="fas fa-chevron-right"></i>
								</a>
							</div>
						</div>
					</article>
					<?php
				endwhile;
			else :
				?>
				<p style="grid-column: 1 / -1; color: var(--dt-text-muted); text-align:center; padding: 40px 0;">
					<?php esc_html_e( 'No articles found in this archive.', 'vmtheme' ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<!-- Cinema Grid -->
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
					<?php esc_html_e( 'No titles found in this archive.', 'vmtheme' ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Pagination -->
	<?php doodhtheme_render_pagination(); ?>
</main>

<?php
get_footer();
