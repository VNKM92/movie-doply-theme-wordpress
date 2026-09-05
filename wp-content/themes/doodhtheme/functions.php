<?php
/**
 * DoodhTheme - Master Theme Functions and Definitions
 *
 * @package DoodhTheme
 * @author DoodhTheme Engineering
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DOODHTHEME_VERSION', '1.0.0' );
define( 'DOODHTHEME_DIR', get_template_directory() );
define( 'DOODHTHEME_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function doodhtheme_setup() {
	// Translations
	load_theme_textdomain( 'doodhtheme', DOODHTHEME_DIR . '/languages' );

	// Theme supports
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Custom Image Sizes
	add_image_size( 'doodh-poster', 300, 450, true );
	add_image_size( 'doodh-backdrop', 1280, 720, true );
	add_image_size( 'doodh-still', 640, 360, true );

	// Register Menus
	register_nav_menus( array(
		'primary'   => __( 'Primary Header Menu', 'doodhtheme' ),
		'footer'    => __( 'Footer Navigation Menu', 'doodhtheme' ),
		'genre_nav' => __( 'Genre Quick Menu', 'doodhtheme' ),
	) );
}
add_action( 'after_setup_theme', 'doodhtheme_setup' );

/**
 * Enqueue Theme Scripts and Stylesheets
 */
function doodhtheme_enqueue_scripts() {
	// Google Fonts
	wp_enqueue_style( 'doodhtheme-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap', array(), null );

	// Font Awesome Icons
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );

	// Core DoodhTheme Stylesheet
	wp_enqueue_style( 'doodhtheme-core', DOODHTHEME_URI . '/assets/css/doodhtheme.css', array(), DOODHTHEME_VERSION );
	wp_enqueue_style( 'doodhtheme-style', get_stylesheet_uri(), array( 'doodhtheme-core' ), DOODHTHEME_VERSION );

	// Custom Customizer CSS
	$accent_color = get_theme_mod( 'doodh_accent_color', '#e50914' );
	$custom_css   = ":root { --dt-primary: {$accent_color}; --dt-primary-hover: " . doodhtheme_adjust_brightness( $accent_color, -20 ) . "; }";
	wp_add_inline_style( 'doodhtheme-core', $custom_css );

	// jQuery (WordPress bundled)
	wp_enqueue_script( 'jquery' );

	// Main DoodhTheme JavaScript
	wp_enqueue_script( 'doodhtheme-main', DOODHTHEME_URI . '/assets/js/doodhtheme.js', array( 'jquery' ), DOODHTHEME_VERSION, true );

	// Localize script data for AJAX
	wp_localize_script( 'doodhtheme-main', 'DoodhThemeData', array(
		'ajax_url'        => admin_url( 'admin-ajax.php' ),
		'nonce'           => wp_create_nonce( 'doodhtheme_nonce' ),
		'home_url'        => home_url( '/' ),
		'searching'       => __( 'Searching...', 'doodhtheme' ),
		'no_results'      => __( 'No titles found.', 'doodhtheme' ),
		'view_all'        => __( 'View all results', 'doodhtheme' ),
		'voted_msg'       => __( 'Vote submitted successfully!', 'doodhtheme' ),
		'added_fav'       => __( 'Added to Watchlist', 'doodhtheme' ),
		'removed_fav'     => __( 'Removed from Watchlist', 'doodhtheme' ),
		'fallback_poster' => doodhtheme_get_fallback_poster_url(),
	) );
}
add_action( 'wp_enqueue_scripts', 'doodhtheme_enqueue_scripts' );

/**
 * Color brightness adjuster helper
 */
function doodhtheme_adjust_brightness( $hex, $steps ) {
	$hex = str_replace( '#', '', $hex );
	if ( strlen( $hex ) === 3 ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}
	$color = '';
	for ( $x = 0; $x < 3; $x++ ) {
		$c = hexdec( substr( $hex, ( 2 * $x ), 2 ) ) + $steps;
		$c = max( 0, min( 255, $c ) );
		$color .= str_pad( dechex( $c ), 2, '0', STR_PAD_LEFT );
	}
	return '#' . $color;
}

/**
 * Get Poster URL (with meta fallback and placeholder)
 */
function doodhtheme_get_poster_url( $post_id = null, $size = 'doodh-poster' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$custom_poster = get_post_meta( $post_id, '_doodh_poster_url', true );
	if ( ! empty( $custom_poster ) && strpos( $custom_poster, 'placeholder.jpg' ) === false && strpos( $custom_poster, 'placeholder.png' ) === false ) {
		return esc_url( $custom_poster );
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), $size );
		if ( $thumb ) {
			return esc_url( $thumb );
		}
	}

	return doodhtheme_get_fallback_poster_url();
}

/**
 * Get Backdrop Image URL
 */
function doodhtheme_get_backdrop_url( $post_id = null, $size = 'doodh-backdrop' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$custom_backdrop = get_post_meta( $post_id, '_doodh_backdrop_url', true );
	if ( ! empty( $custom_backdrop ) && strpos( $custom_backdrop, 'placeholder.jpg' ) === false && strpos( $custom_backdrop, 'placeholder.png' ) === false ) {
		return esc_url( $custom_backdrop );
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), $size );
		if ( $thumb ) {
			return esc_url( $thumb );
		}
	}

	return doodhtheme_get_fallback_backdrop_url();
}

/**
 * Render Responsive and Attractive Pagination Component
 *
 * @param WP_Query|null $custom_query Custom WP_Query instance or null for main query
 */
function doodhtheme_render_pagination( $custom_query = null ) {
	global $wp_query;
	$query = $custom_query ?: $wp_query;

	$total_pages = (int) $query->max_num_pages;
	if ( $total_pages <= 1 ) {
		return;
	}

	$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
	if ( get_query_var( 'page' ) ) {
		$paged = (int) get_query_var( 'page' );
	}

	$pagination_links = paginate_links( array(
		'total'     => $total_pages,
		'current'   => max( 1, $paged ),
		'mid_size'  => 2,
		'end_size'  => 1,
		'prev_text' => '<i class="fas fa-chevron-left"></i> <span class="doodh-page-nav-text">' . esc_html__( 'Prev', 'doodhtheme' ) . '</span>',
		'next_text' => '<span class="doodh-page-nav-text">' . esc_html__( 'Next', 'doodhtheme' ) . '</span> <i class="fas fa-chevron-right"></i>',
		'type'      => 'plain',
	) );

	if ( ! empty( $pagination_links ) ) {
		echo '<nav class="doodh-pagination" aria-label="' . esc_attr__( 'Page Navigation', 'doodhtheme' ) . '">';
		echo '<div class="doodh-pagination-container">';
		echo $pagination_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '</nav>';
	}
}

/**
 * Get Rating Score
 */
function doodhtheme_get_rating( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$rating = get_post_meta( $post_id, '_doodh_rating', true );
	return ! empty( $rating ) ? number_format( (float) $rating, 1 ) : '7.5';
}

/**
 * Get Votes Count
 */
function doodhtheme_get_votes( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$votes = get_post_meta( $post_id, '_doodh_votes', true );
	return ! empty( $votes ) ? (int) $votes : 1240;
}

/**
 * Get Release Year
 */
function doodhtheme_get_release_year( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$years = get_the_terms( $post_id, 'release-year' );
	if ( ! is_wp_error( $years ) && ! empty( $years ) ) {
		return esc_html( $years[0]->name );
	}

	$date = get_post_meta( $post_id, '_doodh_release_date', true );
	if ( ! empty( $date ) ) {
		return date( 'Y', strtotime( $date ) );
	}

	$tv_date = get_post_meta( $post_id, '_doodh_first_air_date', true );
	if ( ! empty( $tv_date ) ) {
		return date( 'Y', strtotime( $tv_date ) );
	}

	return get_the_date( 'Y', $post_id );
}

/**
 * Get Quality Badge
 */
function doodhtheme_get_quality_badge( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$qualities = get_the_terms( $post_id, 'dtquality' );
	if ( ! is_wp_error( $qualities ) && ! empty( $qualities ) ) {
		return esc_html( $qualities[0]->name );
	}

	return 'HD';
}

/**
 * Format Runtime in Hours and Minutes
 */
function doodhtheme_get_runtime_formatted( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$minutes = (int) get_post_meta( $post_id, '_doodh_runtime', true );
	if ( ! $minutes ) {
		$minutes = (int) get_post_meta( $post_id, '_doodh_episode_runtime', true );
	}

	if ( ! $minutes ) {
		return '';
	}

	$h = floor( $minutes / 60 );
	$m = $minutes % 60;

	if ( $h > 0 ) {
		return sprintf( '%dh %02dm', $h, $m );
	}
	return sprintf( '%d min', $m );
}

/**
 * Include Modules
 */
require_once DOODHTHEME_DIR . '/inc/post-types.php';
require_once DOODHTHEME_DIR . '/inc/taxonomies.php';
require_once DOODHTHEME_DIR . '/inc/meta-boxes.php';
require_once DOODHTHEME_DIR . '/inc/player.php';
require_once DOODHTHEME_DIR . '/inc/ajax-search.php';
require_once DOODHTHEME_DIR . '/inc/filter.php';
require_once DOODHTHEME_DIR . '/inc/rating-watchlist.php';
require_once DOODHTHEME_DIR . '/inc/seo-schema.php';
require_once DOODHTHEME_DIR . '/inc/customizer.php';
require_once DOODHTHEME_DIR . '/inc/demo-importer.php';
require_once DOODHTHEME_DIR . '/inc/tmdb-fetcher.php';
require_once DOODHTHEME_DIR . '/inc/ads-manager.php';
require_once DOODHTHEME_DIR . '/inc/reviews-system.php';
require_once DOODHTHEME_DIR . '/inc/duplicate-validator.php';
require_once DOODHTHEME_DIR . '/inc/sitemap-seo.php';
require_once DOODHTHEME_DIR . '/inc/brand-settings.php';
require_once DOODHTHEME_DIR . '/inc/redirects-manager.php';
require_once DOODHTHEME_DIR . '/inc/seed-legal-pages.php';
