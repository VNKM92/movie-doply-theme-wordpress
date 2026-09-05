<?php
/**
 * Taxonomy Template: Director Filmography Profile
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$director_name  = $term->name;
$director_photo = get_term_meta( $term->term_id, '_dt_director_photo', true );
if ( empty( $director_photo ) || strpos( $director_photo, 'placeholder.jpg' ) !== false || strpos( $director_photo, 'placeholder.png' ) !== false ) {
	$director_photo = doodhtheme_get_fallback_avatar_url();
}
$post_count = $term->count;
?>

<main class="doodh-main-content container">
	<?php doodhtheme_render_breadcrumbs(); ?>

	<!-- Director Bio Header -->
	<div class="doodh-person-hero">
		<div class="doodh-person-avatar">
			<img src="<?php echo esc_url( $director_photo ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( null, 'director', $director_name ) ); ?>" loading="lazy" decoding="async" width="160" height="240" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';">
		</div>
		<div class="doodh-person-details">
			<span class="doodh-badge-quality"><i class="fas fa-video"></i> <?php esc_html_e( 'Film Director / Creator', 'doodhtheme' ); ?></span>
			<h1 class="doodh-person-name"><?php echo esc_html( $director_name ); ?></h1>
			<p class="doodh-person-meta">
				<span><i class="fas fa-film"></i> <?php echo sprintf( esc_html__( '%d Directed Works in Catalog', 'doodhtheme' ), $post_count ); ?></span>
			</p>
			<?php if ( ! empty( $term->description ) ) : ?>
				<div class="doodh-person-bio"><?php echo wpautop( esc_html( $term->description ) ); ?></div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Ad Slot -->
	<?php doodhtheme_display_ad( 'header' ); ?>

	<!-- Director Filmography Grid -->
	<section class="doodh-section" style="margin-top:30px;">
		<div class="doodh-section-header">
			<h2 class="doodh-section-title"><i class="fas fa-clapperboard" style="color:var(--dt-primary);"></i> <?php echo sprintf( esc_html__( 'Directed by %s', 'doodhtheme' ), esc_html( $director_name ) ); ?></h2>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="doodh-grid doodh-grid-movies">
				<?php while ( have_posts() ) : the_post(); 
					$film_id  = get_the_ID();
					$rating   = doodhtheme_get_rating( $film_id );
					$year     = doodhtheme_get_release_year( $film_id );
					$quality  = doodhtheme_get_quality_badge( $film_id );
					$poster   = doodhtheme_get_poster_url( $film_id );
					$is_tv    = ( get_post_type() === 'tvshows' );
					?>
					<article class="doodh-card">
						<div class="doodh-poster">
							<img src="<?php echo esc_url( $poster ); ?>" 
								 alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $film_id ) ); ?>" 
								 loading="lazy" 
								 decoding="async"
								 width="300" 
								 height="450"
								 onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
							<span class="doodh-badge-quality"><?php echo esc_html( $quality ); ?></span>
							<span class="doodh-badge-rating"><i class="fas fa-star"></i> <?php echo esc_html( $rating ); ?></span>
							<div class="doodh-poster-overlay">
								<a href="<?php the_permalink(); ?>" class="doodh-play-btn" aria-label="<?php the_title_attribute(); ?>">
									<i class="fas fa-play"></i>
								</a>
							</div>
						</div>
						<div class="doodh-card-body">
							<h3 class="doodh-card-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<div class="doodh-card-meta">
								<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
								<span><i class="<?php echo $is_tv ? 'fas fa-tv' : 'fas fa-film'; ?>"></i> <?php echo $is_tv ? esc_html__( 'Series', 'doodhtheme' ) : esc_html__( 'Movie', 'doodhtheme' ); ?></span>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<!-- Pagination -->
			<?php doodhtheme_render_pagination(); ?>
		<?php else : ?>
			<div class="doodh-no-results">
				<p><?php esc_html_e( 'No titles currently found for this director.', 'doodhtheme' ); ?></p>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php get_footer(); ?>
