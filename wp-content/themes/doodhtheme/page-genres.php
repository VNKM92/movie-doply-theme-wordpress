<?php
/**
 * Template Name: Genres Directory
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$genres = get_terms( array(
	'taxonomy'   => 'genres',
	'hide_empty' => false,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

// Predefined icons and background gradients for genres
$genre_icons = array(
	'action'    => array( 'icon' => 'fas fa-fist-raised', 'grad' => 'linear-gradient(135deg, #ef4444 0%, #7f1d1d 100%)' ),
	'adventure' => array( 'icon' => 'fas fa-compass', 'grad' => 'linear-gradient(135deg, #f59e0b 0%, #78350f 100%)' ),
	'animation' => array( 'icon' => 'fas fa-magic', 'grad' => 'linear-gradient(135deg, #8b5cf6 0%, #4c1d95 100%)' ),
	'comedy'    => array( 'icon' => 'fas fa-laugh-squint', 'grad' => 'linear-gradient(135deg, #10b981 0%, #064e3b 100%)' ),
	'crime'     => array( 'icon' => 'fas fa-user-secret', 'grad' => 'linear-gradient(135deg, #64748b 0%, #0f172a 100%)' ),
	'drama'     => array( 'icon' => 'fas fa-theater-masks', 'grad' => 'linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%)' ),
	'family'    => array( 'icon' => 'fas fa-home', 'grad' => 'linear-gradient(135deg, #ec4899 0%, #831843 100%)' ),
	'fantasy'   => array( 'icon' => 'fas fa-dragon', 'grad' => 'linear-gradient(135deg, #a855f7 0%, #581c87 100%)' ),
	'history'   => array( 'icon' => 'fas fa-landmark', 'grad' => 'linear-gradient(135deg, #d97706 0%, #451a03 100%)' ),
	'horror'    => array( 'icon' => 'fas fa-ghost', 'grad' => 'linear-gradient(135deg, #dc2626 0%, #450a0a 100%)' ),
	'music'     => array( 'icon' => 'fas fa-music', 'grad' => 'linear-gradient(135deg, #06b6d4 0%, #164e63 100%)' ),
	'mystery'   => array( 'icon' => 'fas fa-search', 'grad' => 'linear-gradient(135deg, #6366f1 0%, #312e81 100%)' ),
	'romance'   => array( 'icon' => 'fas fa-heart', 'grad' => 'linear-gradient(135deg, #f43f5e 0%, #881337 100%)' ),
	'sci-fi'    => array( 'icon' => 'fas fa-rocket', 'grad' => 'linear-gradient(135deg, #0ea5e9 0%, #082f49 100%)' ),
	'thriller'  => array( 'icon' => 'fas fa-skull', 'grad' => 'linear-gradient(135deg, #e11d48 0%, #4c0519 100%)' ),
	'war'       => array( 'icon' => 'fas fa-fighter-jet', 'grad' => 'linear-gradient(135deg, #78716c 0%, #1c1917 100%)' ),
	'western'   => array( 'icon' => 'fas fa-hat-cowboy', 'grad' => 'linear-gradient(135deg, #b45309 0%, #291e0a 100%)' ),
);
?>

<main class="container" style="padding-top: 35px;">
	<div class="doodh-section-header">
		<div>
			<h1 class="doodh-section-title"><i class="fas fa-tags" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Movie & TV Show Genres', 'vmtheme' ); ?></h1>
			<p style="color:var(--dt-text-muted); font-size:14px; margin-top:4px;"><?php esc_html_e( 'Explore endless movies and TV series categorized by genre.', 'vmtheme' ); ?></p>
		</div>
	</div>

	<!-- Genre Grid -->
	<div class="doodh-genres-grid">
		<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : ?>
			<?php foreach ( $genres as $g ) : 
				$slug = strtolower( $g->slug );
				$icon = $genre_icons[ $slug ]['icon'] ?? 'fas fa-film';
				$grad = $genre_icons[ $slug ]['grad'] ?? 'linear-gradient(135deg, #334155 0%, #0f172a 100%)';
				?>
				<a href="<?php echo esc_url( get_term_link( $g ) ); ?>" class="doodh-genre-card" style="background: <?php echo esc_attr( $grad ); ?>;">
					<div class="doodh-genre-icon"><i class="<?php echo esc_attr( $icon ); ?>"></i></div>
					<div class="doodh-genre-info">
						<h3 class="doodh-genre-name"><?php echo esc_html( $g->name ); ?></h3>
						<span class="doodh-genre-count"><?php printf( esc_html__( '%d Titles', 'vmtheme' ), $g->count ); ?></span>
					</div>
					<i class="fas fa-chevron-right doodh-genre-arrow"></i>
				</a>
			<?php endforeach; ?>
		<?php else : ?>
			<p style="color:var(--dt-text-muted); text-align:center; grid-column:1/-1; padding:40px 0;">
				<?php esc_html_e( 'No genres registered yet. Run the 1-click seeder in WP Admin.', 'vmtheme' ); ?>
			</p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
