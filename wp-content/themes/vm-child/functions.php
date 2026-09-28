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
function vmtheme_child_enqueue_styles() {
	// Parent theme style handle
	wp_enqueue_style( 'vmtheme-parent-style', get_template_directory_uri() . '/style.css' );

	// Child theme stylesheet
	wp_enqueue_style( 'vmtheme-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'vmtheme-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
}

// Add %%currentyear%% variable to Yoast SEO.
// add_action( 'wpseo_register_extra_replacements', function() {
//     wpseo_register_var_replacement(
//         '%%currentyear%%',
//         'Current year',
//         function() {
//             return date( 'Y' );
//         },
//         'advanced'
//     );
// } );

add_action( 'wp_enqueue_scripts', 'vmtheme_child_enqueue_styles', 20 );


// add_filter( 'wpseo_replacements', 'my_custom_yoast_replacement', 10, 1 );

// function my_custom_yoast_replacement( $replacements ) {
//     // Check if the variable exists, then override its value
//     if ( isset( $replacements['%%company_name%%'] ) ) {
//         $replacements['%%company_name%%'] = 'Your New Custom Value';
//     }
//     return $replacements;
// }

/**
 * Add your custom hooks, filters, and functions below.
 * All changes made here will persist even when the parent theme is updated.
 */
