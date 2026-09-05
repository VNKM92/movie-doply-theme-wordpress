<?php
/**
 * Custom Taxonomies for DoodhTheme (Genres, Release Year, Quality, Cast, Director, Country, Network)
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Taxonomies
 */
function doodhtheme_register_taxonomies() {
	// 1. Genres Taxonomy (Movies & TV Shows)
	$genre_labels = array(
		'name'                       => _x( 'Genres', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Genre', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Genres', 'doodhtheme' ),
		'all_items'                  => __( 'All Genres', 'doodhtheme' ),
		'parent_item'                => __( 'Parent Genre', 'doodhtheme' ),
		'parent_item_colon'          => __( 'Parent Genre:', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Genre', 'doodhtheme' ),
		'update_item'                => __( 'Update Genre', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Genre', 'doodhtheme' ),
		'new_item_name'              => __( 'New Genre Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Genres', 'doodhtheme' ),
	);
	register_taxonomy( 'genres', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => true,
		'labels'                => $genre_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'genre', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 2. Release Year Taxonomy
	$year_labels = array(
		'name'                       => _x( 'Release Years', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Release Year', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Years', 'doodhtheme' ),
		'all_items'                  => __( 'All Years', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Year', 'doodhtheme' ),
		'update_item'                => __( 'Update Year', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Year', 'doodhtheme' ),
		'new_item_name'              => __( 'New Year Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Release Years', 'doodhtheme' ),
	);
	register_taxonomy( 'release-year', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $year_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'release-year', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 3. Quality Taxonomy (4K, 1080p, 720p, CAM, HD, WEBRip, BluRay)
	$quality_labels = array(
		'name'                       => _x( 'Qualities', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Quality', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Qualities', 'doodhtheme' ),
		'all_items'                  => __( 'All Qualities', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Quality', 'doodhtheme' ),
		'update_item'                => __( 'Update Quality', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Quality', 'doodhtheme' ),
		'new_item_name'              => __( 'New Quality Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Quality', 'doodhtheme' ),
	);
	register_taxonomy( 'dtquality', array( 'movies', 'tvshows', 'episodes' ), array(
		'hierarchical'          => false,
		'labels'                => $quality_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'quality', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 4. Cast / Actors Taxonomy
	$cast_labels = array(
		'name'                       => _x( 'Cast / Actors', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Actor', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Actors', 'doodhtheme' ),
		'all_items'                  => __( 'All Actors', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Actor', 'doodhtheme' ),
		'update_item'                => __( 'Update Actor', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Actor', 'doodhtheme' ),
		'new_item_name'              => __( 'New Actor Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Cast / Actors', 'doodhtheme' ),
	);
	register_taxonomy( 'dtcast', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $cast_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'cast', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 5. Directors Taxonomy
	$director_labels = array(
		'name'                       => _x( 'Directors', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Director', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Directors', 'doodhtheme' ),
		'all_items'                  => __( 'All Directors', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Director', 'doodhtheme' ),
		'update_item'                => __( 'Update Director', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Director', 'doodhtheme' ),
		'new_item_name'              => __( 'New Director Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Directors', 'doodhtheme' ),
	);
	register_taxonomy( 'dtdirector', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $director_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'director', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 6. Country Taxonomy
	$country_labels = array(
		'name'                       => _x( 'Countries', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Country', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Countries', 'doodhtheme' ),
		'all_items'                  => __( 'All Countries', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Country', 'doodhtheme' ),
		'update_item'                => __( 'Update Country', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Country', 'doodhtheme' ),
		'new_item_name'              => __( 'New Country Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Countries', 'doodhtheme' ),
	);
	register_taxonomy( 'dtcountry', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $country_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'country', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 7. Networks / Studios Taxonomy
	$network_labels = array(
		'name'                       => _x( 'Networks & Studios', 'taxonomy general name', 'doodhtheme' ),
		'singular_name'              => _x( 'Network', 'taxonomy singular name', 'doodhtheme' ),
		'search_items'               => __( 'Search Networks', 'doodhtheme' ),
		'all_items'                  => __( 'All Networks', 'doodhtheme' ),
		'edit_item'                  => __( 'Edit Network', 'doodhtheme' ),
		'update_item'                => __( 'Update Network', 'doodhtheme' ),
		'add_new_item'               => __( 'Add New Network', 'doodhtheme' ),
		'new_item_name'              => __( 'New Network Name', 'doodhtheme' ),
		'menu_name'                  => __( 'Networks / Studios', 'doodhtheme' ),
	);
	register_taxonomy( 'dtnetwork', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $network_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'network', 'with_front' => false ),
		'show_in_rest'          => true,
	) );
}
add_action( 'init', 'doodhtheme_register_taxonomies' );
