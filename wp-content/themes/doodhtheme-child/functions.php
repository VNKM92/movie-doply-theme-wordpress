<?php
/**
 * DoodhTheme Child Functions and Definitions
 *
 * @package DoodhTheme_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Parent and Child Theme Stylesheets
 */
function doodhtheme_child_enqueue_styles() {
	// Parent theme style handle
	wp_enqueue_style( 'doodhtheme-parent-style', get_template_directory_uri() . '/style.css' );

	// Child theme stylesheet
	wp_enqueue_style( 'doodhtheme-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'doodhtheme-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'doodhtheme_child_enqueue_styles', 20 );

/**
 * Add your custom hooks, filters, and functions below.
 * All changes made here will persist even when the parent theme is updated.
 */
