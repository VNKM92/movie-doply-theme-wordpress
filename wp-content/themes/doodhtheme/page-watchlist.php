<?php
/**
 * Template Name: Watchlist / Saved Titles
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="container" style="padding-top: 30px;">
	<div class="doodh-section-header">
		<h1 class="doodh-section-title">
			<i class="fas fa-bookmark" style="color:var(--dt-primary);"></i> 
			<?php esc_html_e( 'My Watchlist', 'doodhtheme' ); ?>
		</h1>
	</div>

	<!-- Watchlist Container (populated by doodhtheme.js via LocalStorage) -->
	<div class="doodh-grid" id="doodh-watchlist-items-grid">
		<!-- Dynamic Client Rendered Cards -->
	</div>
</main>

<?php
get_footer();
