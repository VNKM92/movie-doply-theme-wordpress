<?php
/**
 * Custom Post Types for DoodhTheme (Movies, TV Shows, Seasons, Episodes)
 *
 * @package VMTheme
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
		'name'                  => _x( 'Movies', 'Post type general name', 'vmtheme' ),
		'singular_name'         => _x( 'Movie', 'Post type singular name', 'vmtheme' ),
		'menu_name'             => _x( 'Movies', 'Admin Menu text', 'vmtheme' ),
		'name_admin_bar'        => _x( 'Movie', 'Add New on Toolbar', 'vmtheme' ),
		'add_new'               => __( 'Add New Movie', 'vmtheme' ),
		'add_new_item'          => __( 'Add New Movie', 'vmtheme' ),
		'new_item'              => __( 'New Movie', 'vmtheme' ),
		'edit_item'             => __( 'Edit Movie', 'vmtheme' ),
		'view_item'             => __( 'View Movie', 'vmtheme' ),
		'all_items'             => __( 'All Movies', 'vmtheme' ),
		'search_items'          => __( 'Search Movies', 'vmtheme' ),
		'not_found'             => __( 'No movies found.', 'vmtheme' ),
		'not_found_in_trash'    => __( 'No movies found in Trash.', 'vmtheme' ),
		'featured_image'        => _x( 'Movie Poster', 'Overrides the "Featured Image" phrase', 'vmtheme' ),
		'set_featured_image'    => _x( 'Set movie poster', 'Overrides the "Set featured image" phrase', 'vmtheme' ),
		'remove_featured_image' => _x( 'Remove movie poster', 'Overrides the "Remove featured image" phrase', 'vmtheme' ),
		'use_featured_image'    => _x( 'Use as movie poster', 'Overrides the "Use as featured image" phrase', 'vmtheme' ),
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
		'name'                  => _x( 'TV Shows', 'Post type general name', 'vmtheme' ),
		'singular_name'         => _x( 'TV Show', 'Post type singular name', 'vmtheme' ),
		'menu_name'             => _x( 'TV Shows', 'Admin Menu text', 'vmtheme' ),
		'name_admin_bar'        => _x( 'TV Show', 'Add New on Toolbar', 'vmtheme' ),
		'add_new'               => __( 'Add New TV Show', 'vmtheme' ),
		'add_new_item'          => __( 'Add New TV Show', 'vmtheme' ),
		'new_item'              => __( 'New TV Show', 'vmtheme' ),
		'edit_item'             => __( 'Edit TV Show', 'vmtheme' ),
		'view_item'             => __( 'View TV Show', 'vmtheme' ),
		'all_items'             => __( 'All TV Shows', 'vmtheme' ),
		'search_items'          => __( 'Search TV Shows', 'vmtheme' ),
		'not_found'             => __( 'No TV shows found.', 'vmtheme' ),
		'not_found_in_trash'    => __( 'No TV shows found in Trash.', 'vmtheme' ),
		'featured_image'        => _x( 'TV Show Poster', 'Overrides featured image', 'vmtheme' ),
		'set_featured_image'    => _x( 'Set TV show poster', 'Overrides set featured image', 'vmtheme' ),
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
		'name'                  => _x( 'Seasons', 'Post type general name', 'vmtheme' ),
		'singular_name'         => _x( 'Season', 'Post type singular name', 'vmtheme' ),
		'menu_name'             => _x( 'Seasons', 'Admin Menu text', 'vmtheme' ),
		'all_items'             => __( 'All Seasons', 'vmtheme' ),
		'add_new'               => __( 'Add New Season', 'vmtheme' ),
		'add_new_item'          => __( 'Add New Season', 'vmtheme' ),
		'edit_item'             => __( 'Edit Season', 'vmtheme' ),
		'view_item'             => __( 'View Season', 'vmtheme' ),
		'search_items'          => __( 'Search Seasons', 'vmtheme' ),
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
		'name'                  => _x( 'Episodes', 'Post type general name', 'vmtheme' ),
		'singular_name'         => _x( 'Episode', 'Post type singular name', 'vmtheme' ),
		'menu_name'             => _x( 'Episodes', 'Admin Menu text', 'vmtheme' ),
		'all_items'             => __( 'All Episodes', 'vmtheme' ),
		'add_new'               => __( 'Add New Episode', 'vmtheme' ),
		'add_new_item'          => __( 'Add New Episode', 'vmtheme' ),
		'edit_item'             => __( 'Edit Episode', 'vmtheme' ),
		'view_item'             => __( 'View Episode', 'vmtheme' ),
		'search_items'          => __( 'Search Episodes', 'vmtheme' ),
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
