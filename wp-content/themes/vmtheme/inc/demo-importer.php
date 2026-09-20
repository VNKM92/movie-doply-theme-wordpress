<?php
/**
 * 1-Click Demo & 100 Titles Data Importer
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DOODHTHEME_DIR . '/inc/seed-100-data.php';

/**
 * Register Admin Menu for 1-Click Demo Import
 */
function doodhtheme_demo_importer_menu() {
	add_theme_page(
		__( 'DoodhTheme Data Seeder', 'vmtheme' ),
		__( 'Seed 100 Movies & Shows', 'vmtheme' ),
		'manage_options',
		'doodhtheme-demo-importer',
		'doodhtheme_render_demo_importer_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_demo_importer_menu' );

/**
 * Render Demo Importer Page
 */
function doodhtheme_render_demo_importer_page() {
	if ( isset( $_POST['doodhtheme_seed_100_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_seed_100_nonce'], 'doodhtheme_seed_100_action' ) ) {
		$result = doodhtheme_execute_100_seed();
		echo '<div class="notice notice-success is-dismissible" style="padding:15px; margin:20px 0;"><p><strong style="font-size:16px;"><i class="dashicons dashicons-yes-alt"></i> ' . esc_html( $result ) . '</strong></p></div>';
	}
	?>
	<div class="wrap" style="max-width:900px;">
		<h1 style="display:flex; align-items:center; gap:10px;">
			<span class="dashicons dashicons-video-alt2" style="font-size:32px; width:32px; height:32px; color:#e50914;"></span> 
			<?php esc_html_e( 'DoodhTheme Production Data Seeder (100 Titles: 2010 - 2026)', 'vmtheme' ); ?>
		</h1>
		<p style="font-size:15px; color:#64748b;">
			<?php esc_html_e( 'Populate your WordPress streaming database with 100 handpicked, real-world blockbuster movies and critically acclaimed TV shows categorized from 2010 to 2026. Includes 4K posters, backdrops, runtimes, ratings, trailers, 4 multi-server stream embeds, and 3 download links per title.', 'vmtheme' ); ?>
		</p>
		
		<div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:25px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-top:25px;">
			<h2 style="color:#0f172a; margin-top:0;"><i class="dashicons dashicons-database-import" style="color:#e50914;"></i> <?php esc_html_e( '100 Curated Production Titles Overview', 'vmtheme' ); ?></h2>
			
			<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin:20px 0;">
				<div style="background:#f8fafc; padding:15px; border-radius:6px; border-left:4px solid #e50914;">
					<strong>2010 - 2014</strong>
					<p style="font-size:12px; color:#64748b; margin:5px 0 0;">Inception, Interstellar, The Dark Knight Rises, Game of Thrones, Peaky Blinders, Django Unchained, Whiplash...</p>
				</div>
				<div style="background:#f8fafc; padding:15px; border-radius:6px; border-left:4px solid #2563eb;">
					<strong>2015 - 2019</strong>
					<p style="font-size:12px; color:#64748b; margin:5px 0 0;">Stranger Things, Avengers: Endgame, Joker, Parasite, Mad Max, Blade Runner 2049, Spider-Verse, The Boys...</p>
				</div>
				<div style="background:#f8fafc; padding:15px; border-radius:6px; border-left:4px solid #10b981;">
					<strong>2020 - 2024</strong>
					<p style="font-size:12px; color:#64748b; margin:5px 0 0;">Dune: Part 1 & 2, Oppenheimer, The Last of Us, Top Gun 2, Avatar 2, Fallout, Shōgun, Deadpool & Wolverine...</p>
				</div>
				<div style="background:#f8fafc; padding:15px; border-radius:6px; border-left:4px solid #f59e0b;">
					<strong>2025 - 2026</strong>
					<p style="font-size:12px; color:#64748b; margin:5px 0 0;">Superman (2025), Avatar: Fire and Ash, The Batman Part II, Avengers: Doomsday, Stranger Things S5...</p>
				</div>
			</div>

			<form method="post">
				<?php wp_nonce_field( 'doodhtheme_seed_100_action', 'doodhtheme_seed_100_nonce' ); ?>
				<button type="submit" class="button button-primary button-hero" style="background:#e50914; border-color:#b80710; font-weight:700; display:flex; align-items:center; gap:8px;">
					<span class="dashicons dashicons-cloud-upload"></span> <?php esc_html_e( 'Seed 100 Production Movies & Shows Now', 'vmtheme' ); ?>
				</button>
			</form>
		</div>
	</div>
	<?php
}
