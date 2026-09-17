<?php
/**
 * Instant Live AJAX Search & Taxonomy-Aware Search Engine
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX Live Search (Matches Titles, Actors, Directors, and Genres with Scope Filtering)
 */
function vmtheme_ajax_search_handler() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Invalid security token' ) );
	}

	$keyword    = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
	$scope_type = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'all';

	// Handle empty keyword by returning top trending suggestions
	if ( strlen( $keyword ) < 2 ) {
		$trending_args = array(
			'post_type'      => array( 'movies', 'tvshows' ),
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'meta_key'       => '_doodh_rating',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		);

		if ( $scope_type === 'movies' ) {
			$trending_args['post_type'] = 'movies';
		} elseif ( $scope_type === 'tvshows' ) {
			$trending_args['post_type'] = 'tvshows';
		}

		$trending_query = new WP_Query( $trending_args );
		$trending_items = array();

		if ( $trending_query->have_posts() ) {
			while ( $trending_query->have_posts() ) {
				$trending_query->the_post();
				$p_id = get_the_ID();
				$genres_arr = wp_get_post_terms( $p_id, 'genres', array( 'fields' => 'names' ) );
				$genres_str = ( ! is_wp_error( $genres_arr ) && ! empty( $genres_arr ) ) ? implode( ', ', array_slice( $genres_arr, 0, 2 ) ) : '';

				$trending_items[] = array(
					'id'        => $p_id,
					'title'     => get_the_title(),
					'url'       => get_permalink(),
					'poster'    => vmtheme_get_poster_url( $p_id, 'medium' ),
					'rating'    => vmtheme_get_rating( $p_id ),
					'year'      => vmtheme_get_release_year( $p_id ),
					'quality'   => vmtheme_get_quality_badge( $p_id ),
					'genres'    => $genres_str,
					'type'      => ( get_post_type() === 'movies' ) ? __( 'Movie', 'vmtheme' ) : __( 'TV Show', 'vmtheme' ),
					'type_slug' => get_post_type(),
				);
			}
			wp_reset_postdata();
		}

		wp_send_json_success( array(
			'results'     => array(),
			'trending'    => $trending_items,
			'total'       => 0,
			'is_trending' => true,
		) );
		return;
	}

	$target_post_types = array( 'movies', 'tvshows' );
	if ( $scope_type === 'movies' ) {
		$target_post_types = array( 'movies' );
	} elseif ( $scope_type === 'tvshows' ) {
		$target_post_types = array( 'tvshows' );
	}

	// 1. Direct Title & Content Search
	$args = array(
		'post_type'      => $target_post_types,
		'post_status'    => 'publish',
		's'              => $keyword,
		'posts_per_page' => 10,
		'orderby'        => 'relevance',
	);

	$query = new WP_Query( $args );
	$post_ids = array();
	$results  = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id    = get_the_ID();
			$post_ids[] = $post_id;
			$post_type  = get_post_type();
			$poster     = vmtheme_get_poster_url( $post_id, 'medium' );
			$rating     = vmtheme_get_rating( $post_id );
			$year       = vmtheme_get_release_year( $post_id );
			$quality    = vmtheme_get_quality_badge( $post_id );
			$type_name  = ( $post_type === 'movies' ) ? __( 'Movie', 'vmtheme' ) : __( 'TV Show', 'vmtheme' );
			$runtime    = vmtheme_get_runtime_formatted( $post_id );

			$genres_arr = wp_get_post_terms( $post_id, 'genres', array( 'fields' => 'names' ) );
			$genres_str = ( ! is_wp_error( $genres_arr ) && ! empty( $genres_arr ) ) ? implode( ', ', array_slice( $genres_arr, 0, 2 ) ) : '';

			$results[] = array(
				'id'        => $post_id,
				'title'     => get_the_title(),
				'url'       => get_permalink(),
				'poster'    => $poster,
				'rating'    => $rating,
				'year'      => $year,
				'quality'   => $quality,
				'runtime'   => $runtime,
				'genres'    => $genres_str,
				'type'      => $type_name,
				'type_slug' => $post_type,
			);
		}
		wp_reset_postdata();
	}

	// 2. If results < 10, search taxonomies (Actors, Directors, Genres)
	if ( count( $results ) < 10 ) {
		$tax_args = array(
			'post_type'      => $target_post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 10 - count( $results ),
			'post__not_in'   => $post_ids,
			'tax_query'      => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'dtcast',
					'field'    => 'name',
					'terms'    => $keyword,
					'operator' => 'LIKE',
				),
				array(
					'taxonomy' => 'dtdirector',
					'field'    => 'name',
					'terms'    => $keyword,
					'operator' => 'LIKE',
				),
				array(
					'taxonomy' => 'genres',
					'field'    => 'name',
					'terms'    => $keyword,
					'operator' => 'LIKE',
				),
			),
		);

		$tax_query = new WP_Query( $tax_args );
		if ( $tax_query->have_posts() ) {
			while ( $tax_query->have_posts() ) {
				$tax_query->the_post();
				$post_id   = get_the_ID();
				$post_type = get_post_type();
				$poster    = vmtheme_get_poster_url( $post_id, 'medium' );
				$rating    = vmtheme_get_rating( $post_id );
				$year      = vmtheme_get_release_year( $post_id );
				$quality   = vmtheme_get_quality_badge( $post_id );
				$type_name = ( $post_type === 'movies' ) ? __( 'Movie', 'vmtheme' ) : __( 'TV Show', 'vmtheme' );
				$runtime   = vmtheme_get_runtime_formatted( $post_id );

				$genres_arr = wp_get_post_terms( $post_id, 'genres', array( 'fields' => 'names' ) );
				$genres_str = ( ! is_wp_error( $genres_arr ) && ! empty( $genres_arr ) ) ? implode( ', ', array_slice( $genres_arr, 0, 2 ) ) : '';

				$results[] = array(
					'id'        => $post_id,
					'title'     => get_the_title(),
					'url'       => get_permalink(),
					'poster'    => $poster,
					'rating'    => $rating,
					'year'      => $year,
					'quality'   => $quality,
					'runtime'   => $runtime,
					'genres'    => $genres_str,
					'type'      => $type_name,
					'type_slug' => $post_type,
				);
			}
			wp_reset_postdata();
		}
	}

	$more_url = home_url( '/?s=' . urlencode( $keyword ) );
	if ( $scope_type === 'movies' ) {
		$more_url .= '&post_type=movies';
	} elseif ( $scope_type === 'tvshows' ) {
		$more_url .= '&post_type=tvshows';
	}

	wp_send_json_success( array(
		'results'     => $results,
		'total'       => count( $results ),
		'keyword'     => $keyword,
		'more_url'    => esc_url( $more_url ),
		'is_trending' => false,
	) );
}

add_action( 'wp_ajax_vmtheme_ajax_search', 'vmtheme_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_vmtheme_ajax_search', 'vmtheme_ajax_search_handler' );
add_action( 'wp_ajax_doodhtheme_ajax_search', 'vmtheme_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_doodhtheme_ajax_search', 'vmtheme_ajax_search_handler' );

if ( ! function_exists( 'doodhtheme_ajax_search_handler' ) ) {
	function doodhtheme_ajax_search_handler() {
		vmtheme_ajax_search_handler();
	}
}

