<?php
/**
 * Template Name: Blog Magazine
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$paged       = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
$categories  = get_categories( array( 'hide_empty' => true ) );
$brand_name  = function_exists( 'vmtheme_get_brand_name' ) ? vmtheme_get_brand_name() : get_bloginfo( 'name' );

$blog_query = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => 9,
	'paged'          => $paged,
) );
?>

<main class="container vmtheme-blog-archive-container" style="padding-top: 35px; padding-bottom: 60px;">
	<!-- Blog Hero Header -->
	<header class="vmtheme-blog-header">
		<div class="vmtheme-blog-header-badge">
			<i class="fas fa-newspaper"></i> <?php esc_html_e( 'Official Editorial & News', 'vmtheme' ); ?>
		</div>
		<h1 class="vmtheme-blog-title"><?php echo esc_html( $brand_name ); ?> <?php esc_html_e( 'Cinema Journal', 'vmtheme' ); ?></h1>
		<p class="vmtheme-blog-subtitle">
			<?php esc_html_e( 'In-depth cinema analysis, director retrospectives, 4K streaming guides, and breaking entertainment headlines.', 'vmtheme' ); ?>
		</p>

		<!-- Category Filter Pills -->
		<?php if ( ! empty( $categories ) ) : ?>
			<div class="vmtheme-blog-cat-nav">
				<a href="<?php echo esc_url( get_permalink() ); ?>" class="vm-cat-pill active">
					<?php esc_html_e( 'All Articles', 'vmtheme' ); ?>
				</a>
				<?php foreach ( $categories as $cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="vm-cat-pill">
						<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>

	<?php if ( $blog_query->have_posts() ) : ?>
		<?php
		if ( $paged == 1 ) :
			$blog_query->the_post();
			$feat_id       = get_the_ID();
			$feat_thumb    = vmtheme_get_blog_thumbnail_url( $feat_id, 'full' );
			$feat_cats     = get_the_category( $feat_id );
			$feat_cat_name = ! empty( $feat_cats ) ? $feat_cats[0]->name : __( 'Featured News', 'vmtheme' );
			$feat_read     = vmtheme_get_reading_time( $feat_id );
			$feat_author   = get_the_author();
			$feat_views    = vmtheme_get_post_views( $feat_id );
			?>
			<div class="vmtheme-featured-article-hero">
				<div class="vmtheme-featured-thumb-wrap">
					<img src="<?php echo esc_url( $feat_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="vmtheme-featured-thumb" loading="eager" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( vmtheme_get_fallback_blog_poster_url() ); ?>';">
					<span class="vmtheme-blog-cat-badge"><?php echo esc_html( $feat_cat_name ); ?></span>
					<a href="<?php the_permalink(); ?>" class="vmtheme-featured-overlay"></a>
				</div>
				<div class="vmtheme-featured-body">
					<div class="vmtheme-blog-meta-top">
						<span><i class="far fa-calendar-alt"></i> <?php echo get_the_date( 'M j, Y' ); ?></span>
						<span><i class="far fa-clock"></i> <?php echo esc_html( $feat_read ); ?></span>
						<span><i class="far fa-eye"></i> <?php echo esc_html( $feat_views ); ?></span>
					</div>
					<h2 class="vmtheme-featured-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<p class="vmtheme-featured-excerpt">
						<?php echo wp_trim_words( get_the_excerpt(), 30, '...' ); ?>
					</p>
					<div class="vmtheme-featured-footer">
						<span class="vmtheme-author-name"><i class="far fa-user"></i> <?php echo esc_html( $feat_author ); ?></span>
						<a href="<?php the_permalink(); ?>" class="vmtheme-read-more-btn">
							<?php esc_html_e( 'Read Full Article', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
						</a>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Standard Blog Grid -->
		<div class="vmtheme-blog-grid">
			<?php
			while ( $blog_query->have_posts() ) :
				$blog_query->the_post();
				$post_id   = get_the_ID();
				$thumb_url = vmtheme_get_blog_thumbnail_url( $post_id, 'medium_large' );
				$post_cats = get_the_category( $post_id );
				$cat_name  = ! empty( $post_cats ) ? $post_cats[0]->name : __( 'Cinema News', 'vmtheme' );
				$read_time = vmtheme_get_reading_time( $post_id );
				$author    = get_the_author();
				$views     = vmtheme_get_post_views( $post_id );
				?>
				<article class="vmtheme-blog-card">
					<div class="vmtheme-blog-card-thumb-wrap">
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" class="vmtheme-blog-card-thumb" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( vmtheme_get_fallback_blog_poster_url() ); ?>';">
						<span class="vmtheme-blog-cat-badge"><?php echo esc_html( $cat_name ); ?></span>
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
		<?php doodhtheme_render_pagination( $blog_query ); ?>
		<?php wp_reset_postdata(); ?>

	<?php else : ?>
		<div class="vmtheme-no-articles" style="text-align: center; padding: 60px 20px; background: var(--dt-bg-surface); border-radius: var(--dt-radius); border: 1px solid var(--dt-border);">
			<i class="fas fa-newspaper" style="font-size: 48px; color: var(--dt-text-muted); margin-bottom: 15px;"></i>
			<h2 style="color: #fff; font-size: 24px; margin-bottom: 10px;"><?php esc_html_e( 'No Articles Published Yet', 'vmtheme' ); ?></h2>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
