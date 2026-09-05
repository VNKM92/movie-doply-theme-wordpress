<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding: 80px 20px; text-align: center; min-height: 60vh;">
	<div style="max-width: 600px; margin: 0 auto;">
		<div style="font-size: 96px; font-weight: 900; color: var(--dt-primary); line-height: 1; margin-bottom: 20px; letter-spacing: -2px;">
			404
		</div>
		<h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 15px;">
			<?php esc_html_e( 'Lost in Streaming Space?', 'doodhtheme' ); ?>
		</h1>
		<p style="color: var(--dt-text-muted); font-size: 16px; margin-bottom: 30px; line-height: 1.6;">
			<?php esc_html_e( 'The movie, TV show, or page you are looking for does not exist or has been moved.', 'doodhtheme' ); ?>
		</p>
		<div style="display: flex; justify-content: center; gap: 15px;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-btn-primary">
				<i class="fas fa-home"></i> <?php esc_html_e( 'Back to Home', 'doodhtheme' ); ?>
			</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>" class="doodh-btn-secondary">
				<i class="fas fa-film"></i> <?php esc_html_e( 'Browse Movies', 'doodhtheme' ); ?>
			</a>
		</div>
	</div>
</main>

<?php
get_footer();