<?php
/**
 * The template for displaying all single blog posts
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 40px;">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="max-width:860px; margin:0 auto; background:var(--dt-bg-surface); padding:35px; border-radius:var(--dt-radius); border:1px solid var(--dt-border);">
			<header class="entry-header" style="margin-bottom:25px;">
				<div style="font-size:13px; color:var(--dt-primary); font-weight:700; margin-bottom:8px; text-transform:uppercase;">
					<?php the_category( ', ' ); ?>
				</div>
				<h1 class="entry-title" style="font-size:36px; font-weight:800; color:#fff; line-height:1.2; margin-bottom:12px;"><?php the_title(); ?></h1>
				<div style="font-size:13px; color:var(--dt-text-muted); display:flex; gap:15px;">
					<span><i class="far fa-user"></i> <?php the_author(); ?></span>
					<span><i class="far fa-calendar-alt"></i> <?php echo get_the_date(); ?></span>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div style="margin-bottom:25px; border-radius:var(--dt-radius); overflow:hidden;">
					<?php the_post_thumbnail( 'large', array( 'style' => 'width:100%; height:auto;' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content" style="color:#cbd5e1; line-height:1.8; font-size:16px;">
				<?php the_content(); ?>
			</div>

			<footer class="entry-footer" style="margin-top:30px; padding-top:20px; border-top:1px solid var(--dt-border);">
				<?php the_tags( '<div style="font-size:13px; color:var(--dt-text-muted);"><i class="fas fa-tags"></i> ', ', ', '</div>' ); ?>
			</footer>
		</article>

		<div style="max-width:860px; margin:30px auto 0;">
			<?php
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			?>
		</div>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();