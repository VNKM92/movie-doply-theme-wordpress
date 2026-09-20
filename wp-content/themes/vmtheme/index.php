<?php
/**
 * VMTheme - Dynamic Production Streaming Landing Page & Homepage
 * Powered by Homepage Section Manager with Graceful Conditional Rendering
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$home_settings = function_exists( 'vmtheme_get_home_settings' ) ? vmtheme_get_home_settings() : array();
$search_s      = $home_settings['spotlight_search'] ?? array( 'enabled' => 1 );
$hero_s        = $home_settings['hero_showcase'] ?? array( 'enabled' => 1 );
$movies_s      = $home_settings['trending_movies'] ?? array( 'enabled' => 1 );
$tv_s          = $home_settings['popular_tvshows'] ?? array( 'enabled' => 1 );
$top10_s       = $home_settings['top_imdb'] ?? array( 'enabled' => 1 );
$genres_s      = $home_settings['genres_directory'] ?? array( 'enabled' => 1 );
$curated_1     = $home_settings['custom_collection_1'] ?? array();
$curated_2     = $home_settings['custom_collection_2'] ?? array();
$curated_3     = $home_settings['custom_collection_3'] ?? array();
$faq_s         = $home_settings['faq_section'] ?? array();
$rich_1        = $home_settings['rich_content_1'] ?? array();
$rich_2        = $home_settings['rich_content_2'] ?? array();
$cta_s         = $home_settings['cta_banner'] ?? array();
?>

<?php
// ══════════════════════════════════════════════════════════════
// SECTION 1: Advanced Spotlight Search Bar (Directly After Navbar)
// ══════════════════════════════════════════════════════════════
if ( ! empty( $search_s['enabled'] ) ) :
	$search_title    = $search_s['title'] ?? __( 'Search 10,000+ Movies, TV Shows & Stars', 'vmtheme' );
	$search_subtitle = $search_s['subtitle'] ?? __( 'Instant real-time search with 4K quality filters, IMDb ratings, and smart taxonomy matching.', 'vmtheme' );
	$quick_tags_raw  = $search_s['quick_tags'] ?? 'Action, Sci-Fi, Drama, Anime, Horror';
	$quick_tags      = array_filter( array_map( 'trim', explode( ',', $quick_tags_raw ) ) );
	?>
	<div class="container doodh-navbar-bottom-search-wrap">
		<section class="doodh-home-spotlight-search" id="doodh-home-search-section">
			<div class="doodh-spotlight-glow-aura"></div>
			<div class="doodh-spotlight-inner">
				<div class="doodh-spotlight-header">
					<div class="doodh-spotlight-badge">
						<span class="doodh-pulse-dot"></span>
						<i class="fas fa-bolt"></i> <?php esc_html_e( '✨ Welcome to Cineladdoo! 🎬🍿', 'vmtheme' ); ?>
					</div>
					<h2 class="doodh-spotlight-title"><?php echo esc_html( $search_title ); ?></h2>
					<?php if ( ! empty( $search_subtitle ) ) : ?>
						<p class="doodh-spotlight-subtitle"><?php echo esc_html( $search_subtitle ); ?></p>
					<?php endif; ?>
				</div>

				<!-- Scope Filter Tabs (All / Movies / TV Shows) -->
				<div class="doodh-search-scope-bar">
					<!-- <button type="button" class="doodh-scope-pill active" data-scope="all">
						<i class="fas fa-layer-group"></i> <?php esc_html_e( 'All Titles', 'vmtheme' ); ?>
					</button> -->
					<button type="button" class="doodh-scope-pill" data-scope="movies">
						<i class="fas fa-film"></i> <?php esc_html_e( 'Movies', 'vmtheme' ); ?>
					</button>
					<!-- <button type="button" class="doodh-scope-pill" data-scope="tvshows">
						<i class="fas fa-tv"></i> <?php esc_html_e( 'TV Series', 'vmtheme' ); ?>
					</button> -->
				</div>

				<!-- Advance Search Bar Wrapper -->
				<div class="doodh-home-search-bar-wrap">
					<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-home-search-form" id="doodh-home-search-form">
						<input type="hidden" name="post_type" id="doodh-home-search-scope-input" value="">
						
						<div class="doodh-search-input-group">
							<span class="doodh-search-icon-decor"><i class="fas fa-search"></i></span>
							<input type="search" 
								   id="doodh-home-search-input" 
								   class="doodh-home-search-input" 
								   placeholder="<?php esc_attr_e( 'Search movies, TV shows, actors, directors...', 'vmtheme' ); ?>" 
								   value="<?php echo get_search_query(); ?>" 
								   name="s" 
								   autocomplete="off">
							
							<button type="button" class="doodh-search-clear-btn doodh-home-clear-btn" id="doodh-home-search-clear" title="<?php esc_attr_e( 'Clear search', 'vmtheme' ); ?>" style="display:none;">
								<i class="fas fa-times"></i>
							</button>

							<span class="doodh-home-kbd-badge" title="Press Ctrl+K or / to search">
								<kbd>Ctrl</kbd> + <kbd>K</kbd>
							</span>

							<button type="submit" class="doodh-home-search-submit" aria-label="<?php esc_attr_e( 'Search', 'vmtheme' ); ?>">
								<i class="fas fa-search"></i>
								<span><?php esc_html_e( 'Search', 'vmtheme' ); ?></span>
							</button>
						</div>
					</form>

					<div class="doodh-home-live-results" id="doodh-home-live-results" style="display:none;"></div>
				</div>

				<?php if ( ! empty( $quick_tags ) ) : ?>
					<div class="doodh-search-quick-tags">
						<span class="doodh-quick-label"><i class="fas fa-fire-alt"></i> <?php esc_html_e( 'Popular Searches:', 'vmtheme' ); ?></span>
						<div class="doodh-quick-tags-list">
							<?php foreach ( $quick_tags as $tag ) : ?>
								<button type="button" class="doodh-quick-pill" data-query="<?php echo esc_attr( $tag ); ?>"><?php //echo esc_html( $tag ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
<?php endif; ?>

<?php
// ══════════════════════════════════════════════════════════════
// SECTION 2: Featured Hero Showcase Banner
// ══════════════════════════════════════════════════════════════
if ( ! empty( $hero_s['enabled'] ) ) :
	$hero_q_type = $hero_s['query_type'] ?? 'latest';
	$hero_p_type = $hero_s['post_type'] ?? 'both';
	$hero_types  = ( $hero_p_type === 'both' ) ? array( 'movies', 'tvshows' ) : array( $hero_p_type );

	$hero_args = array(
		'post_type'      => $hero_types,
		'posts_per_page' => 1,
		'post_status'    => 'publish',
	);

	if ( $hero_q_type === 'top_rated' ) {
		$hero_args['meta_key'] = '_doodh_rating';
		$hero_args['orderby']  = 'meta_value_num';
		$hero_args['order']    = 'DESC';
	} elseif ( $hero_q_type === 'custom' && ! empty( $hero_s['custom_ids'] ) ) {
		$hero_args['post__in'] = array_map( 'intval', explode( ',', $hero_s['custom_ids'] ) );
		$hero_args['orderby']  = 'post__in';
	} else {
		$hero_args['orderby'] = 'date';
		$hero_args['order']   = 'DESC';
	}

	$hero_query = new WP_Query( $hero_args );

	if ( $hero_query->have_posts() ) :

		while ( $hero_query->have_posts() ) :
			$hero_query->the_post();
			$hero_id       = get_the_ID();
			$hero_backdrop = function_exists( 'vmtheme_get_backdrop_url' ) ? vmtheme_get_backdrop_url( $hero_id ) : '';
			$hero_rating   = function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $hero_id ) : '7.5';
			$hero_year     = function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $hero_id ) : '';
			$hero_quality  = function_exists( 'vmtheme_get_quality_badge' ) ? vmtheme_get_quality_badge( $hero_id ) : 'HD';
			$hero_trailer  = get_post_meta( $hero_id, '_vm_trailer_url', true ) ?: get_post_meta( $hero_id, '_doodh_trailer_url', true );
			$hero_cert     = get_post_meta( $hero_id, '_vm_certification', true ) ?: get_post_meta( $hero_id, '_doodh_certification', true ) ?: 'PG-13';
			?>
			<section class="doodh-hero-section" style="background-image: url('<?php echo esc_url( $hero_backdrop ); ?>');">
				<div class="doodh-hero-overlay"></div>
				<div class="container">
					<div class="doodh-hero-content">
						<span class="doodh-hero-tagline"><i class="fas fa-fire"></i> <?php esc_html_e( '#1 Trending Premiere', 'vmtheme' ); ?></span>
						<h1 class="doodh-hero-title"><?php the_title(); ?></h1>
						<div class="doodh-hero-meta">
								
							<span class="doodh-badge-imdb"><i class="fas fa-star"></i> <?php echo esc_html( $hero_rating ); ?></span>
							<span class="doodh-badge-quality"><?php echo esc_html( $hero_quality ); ?></span>
							<span class="doodh-tag-pill"><?php echo esc_html( $hero_cert ); ?></span>
							<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $hero_year ); ?></span>
							<span><i class="far fa-clock"></i> <?php echo esc_html( function_exists( 'vmtheme_get_runtime_formatted' ) ? vmtheme_get_runtime_formatted( $hero_id ) : '' ); ?></span>
						</div>
						<div class="doodh-hero-desc">
							<?php echo wp_trim_words( get_the_excerpt() ?: get_the_content(), 35, '...' ); ?>
						</div>
						<div class="doodh-hero-btns">
							<a href="<?php the_permalink(); ?>" class="doodh-btn-primary">
								<i class="fas fa-play"></i> <?php esc_html_e( 'View Review', 'vmtheme' ); ?>
							</a>
							<?php if ( ! empty( $hero_trailer ) ) : ?>
								<a href="<?php echo esc_url( $hero_trailer ); ?>" target="_blank" class="doodh-btn-secondary">
									<i class="fab fa-youtube"></i> <?php esc_html_e( 'Watch Trailer', 'vmtheme' ); ?>
								</a>
							<?php endif; ?>
							<?php if ( function_exists( 'vmtheme_render_watchlist_btn' ) ) { vmtheme_render_watchlist_btn( $hero_id ); } ?>
						</div>
					</div>
				</div>
			</section>
			<?php
		endwhile;
		wp_reset_postdata();
	endif;
endif;
?>

<main class="container">

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 3: Top 10 Today / IMDb Leaderboard Slider
	// ══════════════════════════════════════════════════════════════
	if ( ! empty( $top10_s['enabled'] ) ) :
		$top10_count = (int) ( $top10_s['count'] ?? 10 );
		$top10_query = new WP_Query( array(
			'post_type'      => array( 'movies', 'tvshows' ),
			'posts_per_page' => $top10_count,
			'post_status'    => 'publish',
			'meta_key'       => '_doodh_rating',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		) );

		if ( $top10_query->have_posts() ) :
			$top_title = $top10_s['title'] ?? __( 'Top 10 Today in Movies & TV', 'vmtheme' );
			?>
			<section class="doodh-section">
				<div class="doodh-section-header">
					<h2 class="doodh-section-title">
						<i class="fas fa-chart-line" style="color:var(--dt-primary);"></i> 
						<?php echo esc_html( $top_title ); ?>
					</h2>
					<!-- <a href="<?php // echo esc_url( home_url( '/top-imdb/' ) ); ?>" class="doodh-view-all"> -->
						<?php esc_html_e( 'Top 100 Leaderboard', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				</div>

				<div class="doodh-top10-slider">
					<?php
					$rank = 1;
					while ( $top10_query->have_posts() ) :
						$top10_query->the_post();
						$t10_id     = get_the_ID();
						$t10_poster = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $t10_id ) : '';
						$t10_rating = function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $t10_id ) : '7.5';
						?>
						<div class="doodh-top10-item">
							<span class="doodh-top10-rank"><?php echo esc_html( $rank ); ?></span>
							<div class="doodh-card doodh-top10-card">
								<div class="doodh-card-poster-wrap">
									<img src="<?php echo esc_url( $t10_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( function_exists( 'vmtheme_get_poster_alt' ) ? vmtheme_get_poster_alt( $t10_id ) : get_the_title() ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( function_exists( 'vmtheme_get_fallback_poster_url' ) ? vmtheme_get_fallback_poster_url() : '' ); ?>';">
									<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $t10_rating ); ?></span>
									<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
										<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
									</a>
								</div>
								<div class="doodh-card-body">
									<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								</div>
							</div>
						</div>
						<?php
						$rank++;
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 4: Interactive Genre Quick Filter Nav
	// ══════════════════════════════════════════════════════════════
	if ( ! empty( $genres_s['enabled'] ) ) :
		?>
		<div class="doodh-genre-tabs-bar">
			<button type="button" class="doodh-genre-tab active" data-genre="all"><i class="fas fa-fire"></i> <?php esc_html_e( 'All Genres', 'vmtheme' ); ?></button>
			<button type="button" class="doodh-genre-tab" data-genre="action"><i class="fas fa-fist-raised"></i> <?php esc_html_e( 'Action', 'vmtheme' ); ?></button>
			<button type="button" class="doodh-genre-tab" data-genre="sci-fi"><i class="fas fa-rocket"></i> <?php esc_html_e( 'Sci-Fi', 'vmtheme' ); ?></button>
			<button type="button" class="doodh-genre-tab" data-genre="drama"><i class="fas fa-theater-masks"></i> <?php esc_html_e( 'Drama', 'vmtheme' ); ?></button>
			<button type="button" class="doodh-genre-tab" data-genre="animation"><i class="fas fa-magic"></i> <?php esc_html_e( 'Animation', 'vmtheme' ); ?></button>
			<button type="button" class="doodh-genre-tab" data-genre="thriller"><i class="fas fa-skull"></i> <?php esc_html_e( 'Thriller', 'vmtheme' ); ?></button>
			<button type="button" class="doodh-genre-tab" data-genre="comedy"><i class="fas fa-laugh-squint"></i> <?php esc_html_e( 'Comedy', 'vmtheme' ); ?></button>
			<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>" class="doodh-genre-tab doodh-genre-tab-more"><i class="fas fa-ellipsis-h"></i> <?php esc_html_e( 'More', 'vmtheme' ); ?></a>
		</div>
	<?php endif; ?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 5: Trending Blockbuster Movies
	// ══════════════════════════════════════════════════════════════
	if ( ! empty( $movies_s['enabled'] ) ) :
		$m_count   = (int) ( $movies_s['count'] ?? 12 );
		$m_orderby = $movies_s['orderby'] ?? 'date';
		$m_genre   = $movies_s['genre'] ?? '';

		$m_args = array(
			'post_type'      => 'movies',
			'posts_per_page' => $m_count,
			'post_status'    => 'publish',
		);

		if ( $m_orderby === 'rating' ) {
			$m_args['meta_key'] = '_doodh_rating';
			$m_args['orderby']  = 'meta_value_num';
			$m_args['order']    = 'DESC';
		} elseif ( $m_orderby === 'rand' ) {
			$m_args['orderby'] = 'rand';
		} else {
			$m_args['orderby'] = 'date';
			$m_args['order']   = 'DESC';
		}

		if ( ! empty( $m_genre ) ) {
			$m_args['tax_query'] = array(
				array(
					'taxonomy' => 'genres',
					'field'    => 'slug',
					'terms'    => $m_genre,
				),
			);
		}

		$movies_query = new WP_Query( $m_args );

		// echo '<pre>';
		// print_r($movies_query);
		// echo '</pre>';
		// die();

		if ( $movies_query->have_posts() ) :
			$m_title = $movies_s['title'] ?? __( 'Latest Blockbuster Movies', 'vmtheme' );
			?>
			<section class="doodh-section" id="doodh-movies-grid-section">
				<div class="doodh-section-header">
					<h2 class="doodh-section-title"><i class="fas fa-film" style="color:var(--dt-primary);"></i> <?php echo esc_html( $m_title ); ?></h2>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>" class="doodh-view-all">
						<?php esc_html_e( 'View All Movies', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				</div>

				<div class="doodh-grid">
					<?php
					while ( $movies_query->have_posts() ) :
						$movies_query->the_post();
						$m_id      = get_the_ID();
						$m_poster  = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $m_id ) : '';
						$m_rating  = function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $m_id ) : '7.5';
						$m_year    = function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $m_id ) : '';
						$m_quality = function_exists( 'vmtheme_get_quality_badge' ) ? vmtheme_get_quality_badge( $m_id ) : 'HD';
						$genres    = wp_get_post_terms( $m_id, 'genres', array( 'fields' => 'slugs' ) );
						$genre_cls = ! is_wp_error( $genres ) ? implode( ' ', array_map( function($g){ return 'genre-' . $g; }, $genres ) ) : '';
						?>
						<article class="doodh-card doodh-filterable-card <?php echo esc_attr( $genre_cls ); ?>">
							<div class="doodh-card-poster-wrap">
								<img src="<?php echo esc_url( $m_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( function_exists( 'vmtheme_get_poster_alt' ) ? vmtheme_get_poster_alt( $m_id ) : get_the_title() ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( function_exists( 'vmtheme_get_fallback_poster_url' ) ? vmtheme_get_fallback_poster_url() : '' ); ?>';">
								<!-- <span class="doodh-badge-top-left doodh-badge-quality"><?php //echo esc_html( $m_quality ); ?></span> -->
								<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $m_rating ); ?></span>
								<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
									<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
								</a>
							</div>
							<div class="doodh-card-body">
								<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
								<div class="doodh-card-meta">
									<span><?php echo esc_html( $m_year ); ?></span>
									<span><?php echo esc_html( function_exists( 'vmtheme_get_runtime_formatted' ) ? vmtheme_get_runtime_formatted( $m_id ) : '' ); ?></span>
								</div>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 6: Curated Custom Collection 1 (e.g. 4K Ultra HD Showcase)
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_curated_collection' ) && ! empty( $curated_1['enabled'] ) ) {
		vmtheme_render_curated_collection( $curated_1 );
	}
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 7: Popular TV Series & Seasons
	// ══════════════════════════════════════════════════════════════
	if ( ! empty( $tv_s['enabled'] ) ) :
		$tv_count   = (int) ( $tv_s['count'] ?? 12 );
		$tv_orderby = $tv_s['orderby'] ?? 'date';

		$tv_args = array(
			'post_type'      => 'tvshows',
			'posts_per_page' => $tv_count,
			'post_status'    => 'publish',
		);

		if ( $tv_orderby === 'rating' ) {
			$tv_args['meta_key'] = '_doodh_rating';
			$tv_args['orderby']  = 'meta_value_num';
			$tv_args['order']    = 'DESC';
		} elseif ( $tv_orderby === 'rand' ) {
			$tv_args['orderby'] = 'rand';
		} else {
			$tv_args['orderby'] = 'date';
			$tv_args['order']   = 'DESC';
		}

		$tv_query = new WP_Query( $tv_args );

		if ( $tv_query->have_posts() ) :
			$tv_title = $tv_s['title'] ?? __( 'Popular TV Series & Seasons', 'vmtheme' );
			?>
			<section class="doodh-section">
				<div class="doodh-section-header">
					<h2 class="doodh-section-title"><i class="fas fa-tv" style="color:var(--dt-primary);"></i> <?php echo esc_html( $tv_title ); ?></h2>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>" class="doodh-view-all">
						<?php esc_html_e( 'View All TV Shows', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				</div>

				<div class="doodh-grid">
					<?php
					while ( $tv_query->have_posts() ) :
						$tv_query->the_post();
						$tv_id       = get_the_ID();
						$tv_poster   = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $tv_id ) : '';
						$tv_rating   = function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $tv_id ) : '7.5';
						$tv_year     = function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $tv_id ) : '';
						$tv_seasons  = (int) get_post_meta( $tv_id, '_vm_total_seasons', true ) ?: (int) get_post_meta( $tv_id, '_doodh_total_seasons', true ) ?: 1;
						?>
						<article class="doodh-card">
							<div class="doodh-card-poster-wrap">
								<img src="<?php echo esc_url( $tv_poster ); ?>" class="doodh-card-poster" alt="<?php echo esc_attr( function_exists( 'vmtheme_get_poster_alt' ) ? vmtheme_get_poster_alt( $tv_id ) : get_the_title() ); ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( function_exists( 'vmtheme_get_fallback_poster_url' ) ? vmtheme_get_fallback_poster_url() : '' ); ?>';">
								<span class="doodh-badge-top-left doodh-badge-quality" style="background:#2563eb;"><?php printf( esc_html__( 'SS %d', 'vmtheme' ), $tv_seasons ); ?></span>
								<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $tv_rating ); ?></span>
								<a href="<?php the_permalink(); ?>" class="doodh-card-overlay">
									<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
								</a>
							</div>
							<div class="doodh-card-body">
								<h3 class="doodh-card-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
								<div class="doodh-card-meta">
									<span><?php echo esc_html( $tv_year ); ?></span>
									<span><?php esc_html_e( 'TV Series', 'vmtheme' ); ?></span>
								</div>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 8: Curated Custom Collection 2 (e.g. Trending Anime)
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_curated_collection' ) && ! empty( $curated_2['enabled'] ) ) {
		vmtheme_render_curated_collection( $curated_2 );
	}
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 9: Curated Custom Collection 3 (e.g. Award Winners)
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_curated_collection' ) && ! empty( $curated_3['enabled'] ) ) {
		vmtheme_render_curated_collection( $curated_3 );
	}
	?>

	<!-- ══════════════════════════════════════════════════════════════
	     Section 10: Streaming Platform High-Tech Feature Highlights
	     ══════════════════════════════════════════════════════════════ -->
	<section class="doodh-features-banner">
		<div class="doodh-features-grid">
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-tv"></i></div>
				<h3><?php esc_html_e( '4K UltraHD Streaming', 'vmtheme' ); ?></h3>
				<p><?php esc_html_e( 'Enjoy crisp cinematic resolutions with immersive surround sound on any device.', 'vmtheme' ); ?></p>
			</div>
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-server"></i></div>
				<h3><?php esc_html_e( 'Multi-Server Redundancy', 'vmtheme' ); ?></h3>
				<p><?php esc_html_e( 'Never face buffering or broken links with 4 high-speed failover streaming servers.', 'vmtheme' ); ?></p>
			</div>
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-download"></i></div>
				<h3><?php esc_html_e( 'Direct High-Speed Downloads', 'vmtheme' ); ?></h3>
				<p><?php esc_html_e( 'Download full movies and seasons to watch offline anytime with verified safe links.', 'vmtheme' ); ?></p>
			</div>
			<div class="doodh-feature-item">
				<div class="doodh-feature-icon"><i class="fas fa-bolt"></i></div>
				<h3><?php esc_html_e( 'Daily Premieres', 'vmtheme' ); ?></h3>
				<p><?php esc_html_e( 'New movies, TV series, and fresh episodes updated 24/7 across all genres.', 'vmtheme' ); ?></p>
			</div>
		</div>
	</section>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 11: Streaming Platform FAQ Accordion
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_faq_section' ) && ! empty( $faq_s['enabled'] ) ) {
		vmtheme_render_faq_section( $faq_s );
	}
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 12: Custom Editorial Rich Content Block 1
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_rich_content_section' ) && ! empty( $rich_1['enabled'] ) ) {
		vmtheme_render_rich_content_section( $rich_1, 'rich-content-1' );
	}
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 13: Custom Editorial Rich Content Block 2
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_rich_content_section' ) && ! empty( $rich_2['enabled'] ) ) {
		vmtheme_render_rich_content_section( $rich_2, 'rich-content-2' );
	}
	?>

	<?php
	// ══════════════════════════════════════════════════════════════
	// SECTION 14: Community Title Request CTA Banner
	// ══════════════════════════════════════════════════════════════
	if ( function_exists( 'vmtheme_render_cta_banner' ) && ! empty( $cta_s['enabled'] ) ) {
		vmtheme_render_cta_banner( $cta_s );
	}
	?>

</main>

<?php
get_footer();
