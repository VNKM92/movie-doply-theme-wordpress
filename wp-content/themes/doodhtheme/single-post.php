<?php
/**
 * Single Blog Post Template (Cinema Editorial & News)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id     = get_the_ID();
	$thumb_url   = vmtheme_get_blog_thumbnail_url( $post_id, 'full' );
	$categories  = get_the_category( $post_id );
	$primary_cat = ! empty( $categories ) ? $categories[0] : null;
	$read_time   = vmtheme_get_reading_time( $post_id );
	$views_count = vmtheme_get_post_views( $post_id );
	$author_id   = get_the_author_meta( 'ID' );
	$author_name = get_the_author_meta( 'display_name', $author_id );
	$author_url  = get_author_posts_url( $author_id );
	$author_av   = get_avatar_url( $author_id, array( 'size' => 80 ) );

	// Record post view
	vmtheme_set_post_views( $post_id );
	?>

	<main class="container vmtheme-blog-single-container" style="padding-top: 35px; padding-bottom: 60px;">
		<!-- Breadcrumbs -->
		<nav class="vmtheme-breadcrumbs" aria-label="Breadcrumbs">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'vmtheme' ); ?></a>
			<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'vmtheme' ); ?></a>
			<?php if ( $primary_cat ) : ?>
				<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
				<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>"><?php echo esc_html( $primary_cat->name ); ?></a>
			<?php endif; ?>
			<span class="vm-sep"><i class="fas fa-chevron-right"></i></span>
			<span class="vm-current"><?php the_title(); ?></span>
		</nav>

		<!-- Article Hero Header -->
		<header class="vmtheme-article-header">
			<?php if ( $primary_cat ) : ?>
				<div class="vmtheme-article-cat-wrap">
					<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>" class="vmtheme-blog-cat-badge">
						<?php echo esc_html( $primary_cat->name ); ?>
					</a>
				</div>
			<?php endif; ?>

			<h1 class="vmtheme-article-title"><?php the_title(); ?></h1>

			<div class="vmtheme-article-meta-bar">
				<div class="vmtheme-author-mini">
					<img src="<?php echo esc_url( $author_av ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" class="vmtheme-author-mini-img">
					<div>
						<a href="<?php echo esc_url( $author_url ); ?>" class="vmtheme-author-mini-name"><?php echo esc_html( $author_name ); ?></a>
						<span class="vmtheme-post-date"><i class="far fa-calendar-alt"></i> <?php echo get_the_date( 'F j, Y' ); ?></span>
					</div>
				</div>
				<div class="vmtheme-meta-pills">
					<span class="vmtheme-pill"><i class="far fa-clock"></i> <?php echo esc_html( $read_time ); ?></span>
					<span class="vmtheme-pill"><i class="far fa-eye"></i> <?php echo esc_html( $views_count ); ?> <?php esc_html_e( 'Views', 'vmtheme' ); ?></span>
				</div>
			</div>
		</header>

		<!-- Featured Image / Fallback Poster Banner -->
		<div class="vmtheme-article-featured-media">
			<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" class="vmtheme-article-hero-img" loading="eager" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( vmtheme_get_fallback_blog_poster_url() ); ?>';">
		</div>

		<!-- Article Layout: Main Body & Share Toolbar -->
		<div class="vmtheme-article-layout">
			<!-- Social Share Bar (Top) -->
			<?php vmtheme_render_social_share_bar( $post_id ); ?>

			<!-- Content Area -->
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'vmtheme-article-content' ); ?>>
				<?php the_content(); ?>

				<?php
				wp_link_pages( array(
					'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'vmtheme' ) . '</span>',
					'after'  => '</div>',
				) );
				?>
			</article>

			<!-- Tags Cloud -->
			<?php
			$post_tags = get_the_tags( $post_id );
			if ( $post_tags ) :
				?>
				<div class="vmtheme-article-tags">
					<span class="vmtheme-tags-label"><i class="fas fa-tags"></i> <?php esc_html_e( 'Topics:', 'vmtheme' ); ?></span>
					<?php foreach ( $post_tags as $tag ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="vmtheme-tag-pill">#<?php echo esc_html( $tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Social Share Bar (Bottom) -->
			<?php vmtheme_render_social_share_bar( $post_id ); ?>

			<!-- Author Bio Box -->
			<?php vmtheme_render_author_bio( $author_id ); ?>

			<!-- Next / Previous Post Navigation Cards -->
			<?php
			$prev_post = get_previous_post();
			$next_post = get_next_post();
			if ( $prev_post || $next_post ) :
				?>
				<nav class="vmtheme-post-navigation" aria-label="Articles Navigation">
					<?php if ( $prev_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="vmtheme-nav-card vmtheme-nav-prev">
							<span class="vmtheme-nav-dir"><i class="fas fa-arrow-left"></i> <?php esc_html_e( 'Previous Article', 'vmtheme' ); ?></span>
							<h4 class="vmtheme-nav-title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></h4>
						</a>
					<?php else : ?>
						<div class="vmtheme-nav-card-empty"></div>
					<?php endif; ?>

					<?php if ( $next_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="vmtheme-nav-card vmtheme-nav-next">
							<span class="vmtheme-nav-dir"><?php esc_html_e( 'Next Article', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i></span>
							<h4 class="vmtheme-nav-title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></h4>
						</a>
					<?php else : ?>
						<div class="vmtheme-nav-card-empty"></div>
					<?php endif; ?>
				</nav>
			<?php endif; ?>

			<!-- Related Cinema Articles Grid -->
			<?php vmtheme_render_related_blog_posts( $post_id, 3 ); ?>

			<!-- Comments / Discussion -->
			<div class="vmtheme-blog-comments-wrap">
				<?php
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
				?>
			</div>
		</div>
	</main>

	<!-- Toast Notification for Copy Link -->
	<script>
	function vmthemeCopyArticleLink(url, btn) {
		navigator.clipboard.writeText(url).then(function() {
			var orig = btn.innerHTML;
			btn.innerHTML = '<i class="fas fa-check" style="color:#22c55e;"></i>';
			var toast = document.createElement('div');
			toast.className = 'vmtheme-toast-notify';
			toast.innerHTML = '<i class="fas fa-check-circle"></i> Link copied to clipboard!';
			document.body.appendChild(toast);
			setTimeout(function() { toast.classList.add('show'); }, 10);
			setTimeout(function() {
				toast.classList.remove('show');
				setTimeout(function() { toast.remove(); }, 300);
				btn.innerHTML = orig;
			}, 2500);
		});
	}
	</script>

<?php
endwhile;

get_footer();
