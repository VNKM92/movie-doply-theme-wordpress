<?php
/**
 * Category Archive Template (Blog Categories)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$current_cat = get_queried_object();
$cat_name    = $current_cat ? $current_cat->name : __( 'Category', 'vmtheme' );
$cat_desc    = $current_cat ? $current_cat->description : '';
$categories  = get_categories( array( 'hide_empty' => true ) );
?>

<main class="container vmtheme-blog-archive-container" style="padding-top: 35px; padding-bottom: 60px;">
	<!-- Breadcrumbs -->
	<nav class="vmtheme-breadcrumbs" aria-label="Breadcrumbs">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'vmtheme' ); ?></a>
		<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'vmtheme' ); ?></a>
		<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
		<span class="vm-current"><?php echo esc_html( $cat_name ); ?></span>
	</nav>

	<!-- Header -->
	<header class="vmtheme-blog-header">
		<div class="vmtheme-blog-header-badge">
			<i class="fas fa-folder-open"></i> <?php esc_html_e( 'Category Archive', 'vmtheme' ); ?>
		</div>
		<h1 class="vmtheme-blog-title"><?php echo esc_html( $cat_name ); ?></h1>
		<?php if ( $cat_desc ) : ?>
			<p class="vmtheme-blog-subtitle"><?php echo esc_html( $cat_desc ); ?></p>
		<?php else : ?>
			<p class="vmtheme-blog-subtitle"><?php echo sprintf( esc_html__( 'All articles, guides, and news filed under %s.', 'vmtheme' ), esc_html( $cat_name ) ); ?></p>
		<?php endif; ?>

		<!-- Category Filter Pills -->
		<?php if ( ! empty( $categories ) ) : ?>
			<div class="vmtheme-blog-cat-nav">
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="vm-cat-pill">
					<?php esc_html_e( 'All Articles', 'vmtheme' ); ?>
				</a>
				<?php foreach ( $categories as $cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="vm-cat-pill <?php echo ( is_category( $cat->term_id ) ) ? 'active' : ''; ?>">
						<?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<!-- Standard Blog Grid -->
		<div class="vmtheme-blog-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$post_id   = get_the_ID();
				$thumb_url = vmtheme_get_blog_thumbnail_url( $post_id, 'medium_large' );
				$post_cats = get_the_category( $post_id );
				$cat_label = ! empty( $post_cats ) ? $post_cats[0]->name : $cat_name;
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
			<i class="fas fa-folder-open" style="font-size: 48px; color: var(--dt-text-muted); margin-bottom: 15px;"></i>
			<h2 style="color: #fff; font-size: 24px; margin-bottom: 10px;"><?php esc_html_e( 'No Articles in this Category', 'vmtheme' ); ?></h2>
			<p style="color: var(--dt-text-muted); max-width: 480px; margin: 0 auto;"><?php esc_html_e( 'Check back soon as our writers publish new cinema articles in this section.', 'vmtheme' ); ?></p>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
