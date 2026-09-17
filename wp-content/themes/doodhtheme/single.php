<?php
/**
 * The template for displaying all single blog posts
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Delegate directly to single-post template logic
require get_template_directory() . '/single-post.php';