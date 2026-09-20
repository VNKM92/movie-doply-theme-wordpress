<?php
/**
 * Search Results Template - Modern Responsive Streaming Layout
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;
$total_results = $wp_query->found_posts;
$search_query  = get_search_query();
?>

<main class="doodh-main-content container">
	<?php doodhtheme_render_breadcrumbs(); ?>

	<!-- Header Ad Slot -->
	<?php doodhtheme_display_ad( 'header' ); ?>

	<!-- Search Query Header & Statistics -->
	<div class="doodh-search-header-box">
		<div class="doodh-search-header-content">
			<span class="doodh-search-kicker"><i class="fas fa-search"></i> <?php esc_html_e( 'Search Catalog', 'vmtheme' ); ?></span>
			<h1 class="doodh-search-main-title">
				<?php printf( esc_html__( 'Results for: "%s"', 'vmtheme' ), esc_html( $search_query ) ); ?>
			</h1>
			<p class="doodh-search-count-meta">
				<span><i class="fas fa-film"></i> <?php printf( esc_html__( 'Found %s matching titles in catalog', 'vmtheme' ), number_format_i18n( $total_results ) ); ?></span>
			</p>
		</div>

		<!-- In-Page Quick Refine Search Form -->
		<div class="doodh-search-refine-form">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-refine-input-wrap">
				<input type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search movies, TV shows, actors...', 'vmtheme' ); ?>" class="doodh-refine-input" autocomplete="off" required>
				<button type="submit" class="doodh-refine-btn"><i class="fas fa-search"></i> <?php esc_html_e( 'Search', 'vmtheme' ); ?></button>
			</form>
		</div>
	</div>

	<!-- Archive Filter Bar -->
	<?php doodhtheme_render_filter_bar(); ?>

	<!-- Search Results Grid -->
	<?php if ( have_posts() ) : ?>
		<div class="doodh-grid doodh-grid-movies" style="margin-top:25px;">
			<?php while ( have_posts() ) : the_post(); 
				$post_id  = get_the_ID();
				$poster   = doodhtheme_get_poster_url( $post_id );
				$rating   = doodhtheme_get_rating( $post_id );
				$year     = doodhtheme_get_release_year( $post_id );
				$quality  = doodhtheme_get_quality_badge( $post_id );
				$is_tv    = ( get_post_type() === 'tvshows' );
				?>
					<article class="doodh-card">
						<div class="doodh-poster">
							<img src="<?php echo esc_url( $poster ); ?>" 
								 alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $post_id ) ); ?>" 
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
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h3>
							<div class="doodh-card-meta">
								<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
								<span><i class="<?php echo $is_tv ? 'fas fa-tv' : 'fas fa-film'; ?>"></i> <?php echo $is_tv ? esc_html__( 'Series', 'vmtheme' ) : esc_html__( 'Movie', 'vmtheme' ); ?></span>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<!-- Pagination -->
			<?php doodhtheme_render_pagination(); ?>
		<?php else : ?>
		<!-- Empty State with Popular Categories -->
		<div class="doodh-search-empty-box">
			<div class="doodh-empty-icon"><i class="fas fa-search-minus"></i></div>
			<h2><?php esc_html_e( 'No Matching Titles Found', 'vmtheme' ); ?></h2>
			<p><?php esc_html_e( 'We couldn\'t find any movies or TV series matching your search. Try different keywords or browse popular genres below.', 'vmtheme' ); ?></p>
			
			<div class="doodh-empty-actions" style="margin-top:20px;">
				<a href="<?php echo esc_url( home_url( '/request/' ) ); ?>" class="doodh-btn-primary" style="margin-right:10px;">
					<i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Request This Title', 'vmtheme' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>" class="doodh-btn-secondary">
					<i class="fas fa-tags"></i> <?php esc_html_e( 'Explore Genres', 'vmtheme' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
