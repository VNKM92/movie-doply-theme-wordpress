<?php
/**
 * Agentic AI & LLM Discovery REST API Endpoints
 *
 * Exposes structured JSON endpoints tailored for AI Agents, Perplexity,
 * ChatGPT, Google Gemini, and assistive browsers to seamlessly query
 * the cinema & TV database with zero scraping overhead.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Agentic REST API Routes
 */
function vmtheme_register_agentic_routes() {
	register_rest_route( 'vmtheme/v1', '/agent-summary', array(
		'methods'             => 'GET',
		'callback'            => 'vmtheme_rest_agent_summary',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'vmtheme/v1', '/search', array(
		'methods'             => 'GET',
		'callback'            => 'vmtheme_rest_agent_search',
		'permission_callback' => '__return_true',
		'args'                => array(
			'q' => array(
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'type' => array(
				'required'          => false,
				'default'           => 'all',
				'sanitize_callback' => 'sanitize_key',
			),
			'limit' => array(
				'required'          => false,
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
		),
	) );
}
add_action( 'rest_api_init', 'vmtheme_register_agentic_routes' );

/**
 * REST Callback: Platform Overview & Catalog Intelligence for AI Agents
 */
function vmtheme_rest_agent_summary() {
	$brand_name = function_exists( 'vmtheme_get_brand_name' ) ? vmtheme_get_brand_name() : get_bloginfo( 'name' );
	$tagline    = function_exists( 'vmtheme_get_brand_tagline' ) ? vmtheme_get_brand_tagline() : get_bloginfo( 'description' );

	$count_movies   = wp_count_posts( 'movies' )->publish ?? 0;
	$count_tvshows  = wp_count_posts( 'tvshows' )->publish ?? 0;
	$count_episodes = wp_count_posts( 'episodes' )->publish ?? 0;

	// Fetch Top 5 Trending Movies
	$trending_movies_query = new WP_Query( array(
		'post_type'      => 'movies',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'meta_key'       => '_doodh_rating',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );

	$trending_movies = array();
	if ( $trending_movies_query->have_posts() ) {
		while ( $trending_movies_query->have_posts() ) {
			$trending_movies_query->the_post();
			$pid = get_the_ID();
			$trending_movies[] = array(
				'id'       => $pid,
				'title'    => get_the_title(),
				'url'      => get_permalink(),
				'year'     => function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $pid ) : get_the_date( 'Y' ),
				'rating'   => function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $pid ) : '7.5',
				'quality'  => function_exists( 'vmtheme_get_quality_badge' ) ? vmtheme_get_quality_badge( $pid ) : 'HD',
			);
		}
		wp_reset_postdata();
	}

	// Fetch Top 5 Trending TV Shows
	$trending_tv_query = new WP_Query( array(
		'post_type'      => 'tvshows',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'meta_key'       => '_doodh_rating',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );

	$trending_tv = array();
	if ( $trending_tv_query->have_posts() ) {
		while ( $trending_tv_query->have_posts() ) {
			$trending_tv_query->the_post();
			$pid = get_the_ID();
			$trending_tv[] = array(
				'id'       => $pid,
				'title'    => get_the_title(),
				'url'      => get_permalink(),
				'year'     => function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $pid ) : get_the_date( 'Y' ),
				'rating'   => function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $pid ) : '7.5',
			);
		}
		wp_reset_postdata();
	}

	// Fetch Top Genres
	$genres = get_terms( array(
		'taxonomy'   => 'genres',
		'hide_empty' => true,
		'number'     => 15,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );

	$genres_data = array();
	if ( ! is_wp_error( $genres ) ) {
		foreach ( $genres as $g ) {
			$genres_data[] = array(
				'name'  => $g->name,
				'slug'  => $g->slug,
				'count' => (int) $g->count,
				'url'   => get_term_link( $g ),
			);
		}
	}

	$response = array(
		'agent_status'    => 'ready',
		'protocol'        => 'VMTheme-Agentic-v1',
		'brand_name'      => $brand_name,
		'tagline'         => $tagline,
		'site_url'        => home_url( '/' ),
		'llms_manifest'   => home_url( '/llms.txt' ),
		'sitemap_url'     => home_url( '/sitemap.xml' ),
		'catalog_stats'   => array(
			'total_movies'   => (int) $count_movies,
			'total_tvshows'  => (int) $count_tvshows,
			'total_episodes' => (int) $count_episodes,
			'total_titles'   => (int) ( $count_movies + $count_tvshows + $count_episodes ),
		),
		'top_movies'      => $trending_movies,
		'top_tv_series'   => $trending_tv,
		'genres'          => $genres_data,
	);

	return rest_ensure_response( $response );
}

/**
 * REST Callback: Semantic & Keyword Search for AI Agents
 */
function vmtheme_rest_agent_search( $request ) {
	$keyword = $request->get_param( 'q' );
	$type    = $request->get_param( 'type' ) ?: 'all';
	$limit   = min( 25, max( 1, (int) ( $request->get_param( 'limit' ) ?: 10 ) ) );

	$post_types = array( 'movies', 'tvshows' );
	if ( $type === 'movies' ) {
		$post_types = array( 'movies' );
	} elseif ( $type === 'tvshows' ) {
		$post_types = array( 'tvshows' );
	}

	$args = array(
		'post_type'      => $post_types,
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
	);

	if ( ! empty( $keyword ) ) {
		$args['s'] = $keyword;
	} else {
		$args['meta_key'] = '_doodh_rating';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
	}

	$query   = new WP_Query( $args );
	$results = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid = get_the_ID();

			$genres_arr = wp_get_post_terms( $pid, 'genres', array( 'fields' => 'names' ) );
			$genres_str = ( ! is_wp_error( $genres_arr ) && ! empty( $genres_arr ) ) ? implode( ', ', $genres_arr ) : '';

			$cast_arr = wp_get_post_terms( $pid, 'dtcast', array( 'fields' => 'names' ) );
			$cast_str = ( ! is_wp_error( $cast_arr ) && ! empty( $cast_arr ) ) ? implode( ', ', array_slice( $cast_arr, 0, 5 ) ) : '';

			$results[] = array(
				'id'          => $pid,
				'title'       => get_the_title(),
				'url'         => get_permalink(),
				'type'        => get_post_type(),
				'poster'      => function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $pid ) : '',
				'rating'      => function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $pid ) : '7.5',
				'year'        => function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $pid ) : get_the_date( 'Y' ),
				'quality'     => function_exists( 'vmtheme_get_quality_badge' ) ? vmtheme_get_quality_badge( $pid ) : 'HD',
				'runtime'     => function_exists( 'vmtheme_get_runtime_formatted' ) ? vmtheme_get_runtime_formatted( $pid ) : '',
				'genres'      => $genres_str,
				'cast'        => $cast_str,
				'description' => wp_strip_all_tags( get_the_excerpt() ?: get_post_field( 'post_content', $pid ) ),
			);
		}
		wp_reset_postdata();
	}

	return rest_ensure_response( array(
		'query'        => $keyword,
		'total_found'  => count( $results ),
		'results'      => $results,
	) );
}
