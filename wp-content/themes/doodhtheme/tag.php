<?php
/**
 * Tag Archive Template (Blog Tags)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_tag = get_queried_object();
$tag_name    = $current_tag ? $current_tag->name : __( 'Tag', 'vmtheme' );
?>

<main class="container vmtheme-blog-archive-container" style="padding-top: 35px; padding-bottom: 60px;">
	<!-- Breadcrumbs -->
	<nav class="vmtheme-breadcrumbs" aria-label="Breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'vmtheme' ); ?></a>
		<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'vmtheme' ); ?></a>
		<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
		<span class="vm-current">#<?php echo esc_html( $tag_name ); ?></span>
	</nav>

	<!-- Header -->
	<header class="vmtheme-blog-header">
		<div class="vmtheme-blog-header-badge">
			<i class="fas fa-tag"></i> <?php esc_html_e( 'Topic Tag', 'vmtheme' ); ?>
		</div>
		<h1 class="vmtheme-blog-title">#<?php echo esc_html( $tag_name ); ?></h1>
		<p class="vmtheme-blog-subtitle"><?php echo sprintf( esc_html__( 'All cinema articles, insights, and reviews tagged with #%s.', 'vmtheme' ), esc_html( $tag_name ) ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="vmtheme-blog-grid">
			<?php
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
			<?php endwhile; ?>
		</div>

		<!-- Pagination -->
		<?php doodhtheme_render_pagination(); ?>
	<?php else : ?>
		<div class="vmtheme-no-articles" style="text-align: center; padding: 60px 20px; background: var(--dt-bg-surface); border-radius: var(--dt-radius); border: 1px solid var(--dt-border);">
			<i class="fas fa-tags" style="font-size: 48px; color: var(--dt-text-muted); margin-bottom: 15px;"></i>
			<h2 style="color: #fff; font-size: 24px; margin-bottom: 10px;"><?php esc_html_e( 'No Articles with this Tag', 'vmtheme' ); ?></h2>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
