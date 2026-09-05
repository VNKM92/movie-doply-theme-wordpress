<?php
/**
 * Instant Live AJAX Search & Taxonomy-Aware Search Engine
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX Live Search (Matches Titles, Actors, and Directors)
 */
function doodhtheme_ajax_search_handler() {
	check_ajax_referer( 'doodhtheme_nonce', 'nonce' );

	$keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';

	if ( strlen( $keyword ) < 2 ) {
		wp_send_json_success( array( 'results' => array(), 'total' => 0 ) );
	}

	// 1. Direct Title & Content Search
	$args = array(
		'post_type'      => array( 'movies', 'tvshows' ),
		'post_status'    => 'publish',
		's'              => $keyword,
		'posts_per_page' => 8,
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
			$poster     = doodhtheme_get_poster_url( $post_id, 'medium' );
			$rating     = doodhtheme_get_rating( $post_id );
			$year       = doodhtheme_get_release_year( $post_id );
			$type_name  = ( $post_type === 'movies' ) ? __( 'Movie', 'doodhtheme' ) : __( 'TV Show', 'doodhtheme' );

			$results[] = array(
				'id'        => $post_id,
				'title'     => get_the_title(),
				'url'       => get_permalink(),
				'poster'    => $poster,
				'rating'    => $rating,
				'year'      => $year,
				'type'      => $type_name,
				'type_slug' => $post_type,
			);
		}
		wp_reset_postdata();
	}

	// 2. If results < 8, search taxonomies (Actors, Directors, Genres)
	if ( count( $results ) < 8 ) {
		$tax_args = array(
			'post_type'      => array( 'movies', 'tvshows' ),
			'post_status'    => 'publish',
			'posts_per_page' => 8 - count( $results ),
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
				$poster    = doodhtheme_get_poster_url( $post_id, 'medium' );
				$rating    = doodhtheme_get_rating( $post_id );
				$year      = doodhtheme_get_release_year( $post_id );
				$type_name = ( $post_type === 'movies' ) ? __( 'Movie', 'doodhtheme' ) : __( 'TV Show', 'doodhtheme' );

				$results[] = array(
					'id'        => $post_id,
					'title'     => get_the_title(),
					'url'       => get_permalink(),
					'poster'    => $poster,
					'rating'    => $rating,
					'year'      => $year,
					'type'      => $type_name,
					'type_slug' => $post_type,
				);
			}
			wp_reset_postdata();
		}
	}

	wp_send_json_success( array(
		'results'  => $results,
		'total'    => count( $results ),
		'keyword'  => $keyword,
		'more_url' => esc_url( home_url( '/?s=' . urlencode( $keyword ) ) ),
	) );
}
add_action( 'wp_ajax_doodhtheme_ajax_search', 'doodhtheme_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_doodhtheme_ajax_search', 'doodhtheme_ajax_search_handler' );
