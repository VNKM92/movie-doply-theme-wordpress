<?php
/**
 * The template for displaying standard pages
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 30px; padding-bottom: 60px; min-height: 65vh; max-width: 1000px;">
	<?php
	if ( function_exists( 'doodhtheme_render_breadcrumbs' ) ) {
		doodhtheme_render_breadcrumbs();
	}
	?>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="background:var(--dt-bg-surface); padding: 40px; border-radius:var(--dt-radius); border:1px solid var(--dt-border); margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
			<header class="entry-header" style="margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 20px;">
				<h1 class="entry-title" style="font-size: 32px; font-weight: 800; color: #fff; margin: 0; line-height: 1.2;"><?php the_title(); ?></h1>
				<div style="font-size: 13px; color: var(--dt-text-muted); margin-top: 8px;">
					<i class="far fa-clock"></i> <?php esc_html_e( 'Last Updated:', 'doodhtheme' ); ?> <?php echo esc_html( get_the_modified_date( 'F j, Y' ) ); ?> &bull; 
					<i class="fas fa-shield-alt"></i> <?php echo esc_html( doodhtheme_get_brand_name() ); ?> <?php esc_html_e( 'Official Policy', 'doodhtheme' ); ?>
				</div>
			</header>

			<div class="entry-content doodh-page-body" style="color: #cbd5e1; line-height: 1.85; font-size: 15px;">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();