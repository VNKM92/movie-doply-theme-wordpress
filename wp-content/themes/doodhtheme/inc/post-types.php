<?php
/**
 * Custom Post Types for DoodhTheme (Movies, TV Shows, Seasons, Episodes)
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Post Types
 */
function doodhtheme_register_post_types() {
	// 1. Movies Post Type
	$movie_labels = array(
		'name'                  => _x( 'Movies', 'Post type general name', 'doodhtheme' ),
		'singular_name'         => _x( 'Movie', 'Post type singular name', 'doodhtheme' ),
		'menu_name'             => _x( 'Movies', 'Admin Menu text', 'doodhtheme' ),
		'name_admin_bar'        => _x( 'Movie', 'Add New on Toolbar', 'doodhtheme' ),
		'add_new'               => __( 'Add New Movie', 'doodhtheme' ),
		'add_new_item'          => __( 'Add New Movie', 'doodhtheme' ),
		'new_item'              => __( 'New Movie', 'doodhtheme' ),
		'edit_item'             => __( 'Edit Movie', 'doodhtheme' ),
		'view_item'             => __( 'View Movie', 'doodhtheme' ),
		'all_items'             => __( 'All Movies', 'doodhtheme' ),
		'search_items'          => __( 'Search Movies', 'doodhtheme' ),
		'not_found'             => __( 'No movies found.', 'doodhtheme' ),
		'not_found_in_trash'    => __( 'No movies found in Trash.', 'doodhtheme' ),
		'featured_image'        => _x( 'Movie Poster', 'Overrides the "Featured Image" phrase', 'doodhtheme' ),
		'set_featured_image'    => _x( 'Set movie poster', 'Overrides the "Set featured image" phrase', 'doodhtheme' ),
		'remove_featured_image' => _x( 'Remove movie poster', 'Overrides the "Remove featured image" phrase', 'doodhtheme' ),
		'use_featured_image'    => _x( 'Use as movie poster', 'Overrides the "Use as featured image" phrase', 'doodhtheme' ),
	);

	$movie_args = array(
		'labels'             => $movie_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'movies', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => 'movies',
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-video-alt2',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions' ),
		'show_in_rest'       => true,
	);
	register_post_type( 'movies', $movie_args );

	// 2. TV Shows Post Type
	$tv_labels = array(
		'name'                  => _x( 'TV Shows', 'Post type general name', 'doodhtheme' ),
		'singular_name'         => _x( 'TV Show', 'Post type singular name', 'doodhtheme' ),
		'menu_name'             => _x( 'TV Shows', 'Admin Menu text', 'doodhtheme' ),
		'name_admin_bar'        => _x( 'TV Show', 'Add New on Toolbar', 'doodhtheme' ),
		'add_new'               => __( 'Add New TV Show', 'doodhtheme' ),
		'add_new_item'          => __( 'Add New TV Show', 'doodhtheme' ),
		'new_item'              => __( 'New TV Show', 'doodhtheme' ),
		'edit_item'             => __( 'Edit TV Show', 'doodhtheme' ),
		'view_item'             => __( 'View TV Show', 'doodhtheme' ),
		'all_items'             => __( 'All TV Shows', 'doodhtheme' ),
		'search_items'          => __( 'Search TV Shows', 'doodhtheme' ),
		'not_found'             => __( 'No TV shows found.', 'doodhtheme' ),
		'not_found_in_trash'    => __( 'No TV shows found in Trash.', 'doodhtheme' ),
		'featured_image'        => _x( 'TV Show Poster', 'Overrides featured image', 'doodhtheme' ),
		'set_featured_image'    => _x( 'Set TV show poster', 'Overrides set featured image', 'doodhtheme' ),
	);

	$tv_args = array(
		'labels'             => $tv_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'tvshows', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => 'tvshows',
		'hierarchical'       => false,
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-format-video',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions' ),
		'show_in_rest'       => true,
	);
	register_post_type( 'tvshows', $tv_args );

	// 3. Seasons Post Type
	$season_labels = array(
		'name'                  => _x( 'Seasons', 'Post type general name', 'doodhtheme' ),
		'singular_name'         => _x( 'Season', 'Post type singular name', 'doodhtheme' ),
		'menu_name'             => _x( 'Seasons', 'Admin Menu text', 'doodhtheme' ),
		'all_items'             => __( 'All Seasons', 'doodhtheme' ),
		'add_new'               => __( 'Add New Season', 'doodhtheme' ),
		'add_new_item'          => __( 'Add New Season', 'doodhtheme' ),
		'edit_item'             => __( 'Edit Season', 'doodhtheme' ),
		'view_item'             => __( 'View Season', 'doodhtheme' ),
		'search_items'          => __( 'Search Seasons', 'doodhtheme' ),
	);

	$season_args = array(
		'labels'             => $season_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=tvshows',
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'seasons', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'show_in_rest'       => true,
	);
	register_post_type( 'seasons', $season_args );

	// 4. Episodes Post Type
	$episode_labels = array(
		'name'                  => _x( 'Episodes', 'Post type general name', 'doodhtheme' ),
		'singular_name'         => _x( 'Episode', 'Post type singular name', 'doodhtheme' ),
		'menu_name'             => _x( 'Episodes', 'Admin Menu text', 'doodhtheme' ),
		'all_items'             => __( 'All Episodes', 'doodhtheme' ),
		'add_new'               => __( 'Add New Episode', 'doodhtheme' ),
		'add_new_item'          => __( 'Add New Episode', 'doodhtheme' ),
		'edit_item'             => __( 'Edit Episode', 'doodhtheme' ),
		'view_item'             => __( 'View Episode', 'doodhtheme' ),
		'search_items'          => __( 'Search Episodes', 'doodhtheme' ),
	);

	$episode_args = array(
		'labels'             => $episode_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=tvshows',
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'episodes', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'comments', 'custom-fields' ),
		'show_in_rest'       => true,
	);
	register_post_type( 'episodes', $episode_args );
}
add_action( 'init', 'doodhtheme_register_post_types' );
