<?php
/**
 * VMTheme - Master Theme Functions and Definitions
 *
 * @package VMTheme
 * @author VMTheme Senior Engineering
 * @version 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VMTHEME_VERSION', '1.2.0' );
define( 'VMTHEME_DIR', get_template_directory() );
define( 'VMTHEME_URI', get_template_directory_uri() );

// Backwards compatibility constants
if ( ! defined( 'DOODHTHEME_VERSION' ) ) {
	define( 'DOODHTHEME_VERSION', VMTHEME_VERSION );
}
if ( ! defined( 'DOODHTHEME_DIR' ) ) {
	define( 'DOODHTHEME_DIR', VMTHEME_DIR );
}
if ( ! defined( 'DOODHTHEME_URI' ) ) {
	define( 'DOODHTHEME_URI', VMTHEME_URI );
}

/**
 * Theme Setup
 */
function vmtheme_setup() {
	// Translations
	load_theme_textdomain( 'vmtheme', VMTHEME_DIR . '/languages' );
	load_theme_textdomain( 'doodhtheme', VMTHEME_DIR . '/languages' );

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

	// Custom Image Sizes (supporting both prefixes)
	add_image_size( 'vm-poster', 300, 450, true );
	add_image_size( 'vm-backdrop', 1280, 720, true );
	add_image_size( 'vm-still', 640, 360, true );
	add_image_size( 'doodh-poster', 300, 450, true );
	add_image_size( 'doodh-backdrop', 1280, 720, true );
	add_image_size( 'doodh-still', 640, 360, true );

	// Register Menus
	register_nav_menus( array(
		'primary'   => __( 'Primary Header Menu', 'vmtheme' ),
		'footer'    => __( 'Footer Navigation Menu', 'vmtheme' ),
		'genre_nav' => __( 'Genre Quick Menu', 'vmtheme' ),
	) );
}
add_action( 'after_setup_theme', 'vmtheme_setup' );

if ( ! function_exists( 'doodhtheme_setup' ) ) {
	function doodhtheme_setup() {
		vmtheme_setup();
	}
}

/**
 * Enqueue Theme Scripts and Stylesheets
 */
function vmtheme_enqueue_scripts() {
	// Google Fonts
	wp_enqueue_style( 'vmtheme-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap', array(), null );

	// Font Awesome Icons
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );

	// Core VMTheme Stylesheet
	wp_enqueue_style( 'vmtheme-core', VMTHEME_URI . '/assets/css/doodhtheme.css', array(), VMTHEME_VERSION );
	wp_enqueue_style( 'vmtheme-style', get_stylesheet_uri(), array( 'vmtheme-core' ), VMTHEME_VERSION );

	// Custom Customizer CSS
	$accent_color = get_theme_mod( 'vm_accent_color', get_theme_mod( 'doodh_accent_color', '#e50914' ) );
	$custom_css   = ":root { --dt-primary: {$accent_color}; --dt-primary-hover: " . vmtheme_adjust_brightness( $accent_color, -20 ) . "; }";
	wp_add_inline_style( 'vmtheme-core', $custom_css );

	// jQuery (WordPress bundled)
	wp_enqueue_script( 'jquery' );

	// Main VMTheme JavaScript
	wp_enqueue_script( 'vmtheme-main', VMTHEME_URI . '/assets/js/doodhtheme.js', array( 'jquery' ), VMTHEME_VERSION, true );

	// Localize script data for AJAX (localized for both VMThemeData and DoodhThemeData)
	$localized_data = array(
		'ajax_url'        => admin_url( 'admin-ajax.php' ),
		'nonce'           => wp_create_nonce( 'vmtheme_nonce' ),
		'home_url'        => home_url( '/' ),
		'current_post_id' => is_singular( array( 'movies', 'tvshows', 'episodes', 'post' ) ) ? get_the_ID() : 0,
		'searching'       => __( 'Searching...', 'vmtheme' ),
		'no_results'      => __( 'No titles found.', 'vmtheme' ),
		'view_all'        => __( 'View all results', 'vmtheme' ),
		'voted_msg'       => __( 'Vote submitted successfully!', 'vmtheme' ),
		'added_fav'       => __( 'Added to Watchlist', 'vmtheme' ),
		'removed_fav'     => __( 'Removed from Watchlist', 'vmtheme' ),
		'fallback_poster' => vmtheme_get_fallback_poster_url(),
	);

	wp_localize_script( 'vmtheme-main', 'VMThemeData', $localized_data );
	wp_localize_script( 'vmtheme-main', 'DoodhThemeData', $localized_data );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'vmtheme_enqueue_scripts' );

if ( ! function_exists( 'doodhtheme_enqueue_scripts' ) ) {
	function doodhtheme_enqueue_scripts() {
		vmtheme_enqueue_scripts();
	}
}

/**
 * Color brightness adjuster helper
 */
function vmtheme_adjust_brightness( $hex, $steps ) {
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

if ( ! function_exists( 'doodhtheme_adjust_brightness' ) ) {
	function doodhtheme_adjust_brightness( $hex, $steps ) {
		return vmtheme_adjust_brightness( $hex, $steps );
	}
}

/**
 * Get Poster URL (with meta fallback and placeholder)
 */
function vmtheme_get_poster_url( $post_id = null, $size = 'vm-poster' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$custom_poster = get_post_meta( $post_id, '_vm_poster_url', true );
	if ( empty( $custom_poster ) ) {
		$custom_poster = get_post_meta( $post_id, '_doodh_poster_url', true );
	}
	if ( ! empty( $custom_poster ) && strpos( $custom_poster, 'placeholder.jpg' ) === false && strpos( $custom_poster, 'placeholder.png' ) === false ) {
		return esc_url( $custom_poster );
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), $size );
		if ( $thumb ) {
			return esc_url( $thumb );
		}
	}

	return vmtheme_get_fallback_poster_url();
}

if ( ! function_exists( 'doodhtheme_get_poster_url' ) ) {
	function doodhtheme_get_poster_url( $post_id = null, $size = 'doodh-poster' ) {
		return vmtheme_get_poster_url( $post_id, $size );
	}
}

/**
 * Get Backdrop Image URL
 */
function vmtheme_get_backdrop_url( $post_id = null, $size = 'vm-backdrop' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$custom_backdrop = get_post_meta( $post_id, '_vm_backdrop_url', true );
	if ( empty( $custom_backdrop ) ) {
		$custom_backdrop = get_post_meta( $post_id, '_doodh_backdrop_url', true );
	}
	if ( ! empty( $custom_backdrop ) && strpos( $custom_backdrop, 'placeholder.jpg' ) === false && strpos( $custom_backdrop, 'placeholder.png' ) === false ) {
		return esc_url( $custom_backdrop );
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), $size );
		if ( $thumb ) {
			return esc_url( $thumb );
		}
	}

	return vmtheme_get_fallback_backdrop_url();
}

if ( ! function_exists( 'doodhtheme_get_backdrop_url' ) ) {
	function doodhtheme_get_backdrop_url( $post_id = null, $size = 'doodh-backdrop' ) {
		return vmtheme_get_backdrop_url( $post_id, $size );
	}
}

/**
 * Render Responsive and Attractive Pagination Component
 *
 * @param WP_Query|null $custom_query Custom WP_Query instance or null for main query
 */
function vmtheme_render_pagination( $custom_query = null ) {
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
		'prev_text' => '<i class="fas fa-chevron-left"></i> <span class="doodh-page-nav-text">' . esc_html__( 'Prev', 'vmtheme' ) . '</span>',
		'next_text' => '<span class="doodh-page-nav-text">' . esc_html__( 'Next', 'vmtheme' ) . '</span> <i class="fas fa-chevron-right"></i>',
		'type'      => 'plain',
	) );

	if ( ! empty( $pagination_links ) ) {
		echo '<nav class="doodh-pagination" aria-label="' . esc_attr__( 'Page Navigation', 'vmtheme' ) . '">';
		echo '<div class="doodh-pagination-container">';
		echo $pagination_links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '</nav>';
	}
}

if ( ! function_exists( 'doodhtheme_render_pagination' ) ) {
	function doodhtheme_render_pagination( $custom_query = null ) {
		vmtheme_render_pagination( $custom_query );
	}
}

/**
 * Get Rating Score
 */
function vmtheme_get_rating( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$rating = get_post_meta( $post_id, '_vm_rating', true );
	if ( empty( $rating ) ) {
		$rating = get_post_meta( $post_id, '_doodh_rating', true );
	}
	return ! empty( $rating ) ? number_format( (float) $rating, 1 ) : '7.5';
}

if ( ! function_exists( 'doodhtheme_get_rating' ) ) {
	function doodhtheme_get_rating( $post_id = null ) {
		return vmtheme_get_rating( $post_id );
	}
}

/**
 * Get Votes Count
 */
function vmtheme_get_votes( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$votes = get_post_meta( $post_id, '_vm_votes', true );
	if ( empty( $votes ) ) {
		$votes = get_post_meta( $post_id, '_doodh_votes', true );
	}
	return ! empty( $votes ) ? (int) $votes : 1240;
}

if ( ! function_exists( 'doodhtheme_get_votes' ) ) {
	function doodhtheme_get_votes( $post_id = null ) {
		return vmtheme_get_votes( $post_id );
	}
}

/**
 * Get Release Year
 */
function vmtheme_get_release_year( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$years = get_the_terms( $post_id, 'release-year' );
	if ( ! is_wp_error( $years ) && ! empty( $years ) ) {
		return esc_html( $years[0]->name );
	}

	$date = get_post_meta( $post_id, '_vm_release_date', true );
	if ( empty( $date ) ) {
		$date = get_post_meta( $post_id, '_doodh_release_date', true );
	}
	if ( ! empty( $date ) ) {
		return date( 'Y', strtotime( $date ) );
	}

	$tv_date = get_post_meta( $post_id, '_vm_first_air_date', true );
	if ( empty( $tv_date ) ) {
		$tv_date = get_post_meta( $post_id, '_doodh_first_air_date', true );
	}
	if ( ! empty( $tv_date ) ) {
		return date( 'Y', strtotime( $tv_date ) );
	}

	return get_the_date( 'Y', $post_id );
}

if ( ! function_exists( 'doodhtheme_get_release_year' ) ) {
	function doodhtheme_get_release_year( $post_id = null ) {
		return vmtheme_get_release_year( $post_id );
	}
}

/**
 * Get Quality Badge
 */
function vmtheme_get_quality_badge( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$qualities = get_the_terms( $post_id, 'dtquality' );
	if ( ! is_wp_error( $qualities ) && ! empty( $qualities ) ) {
		return esc_html( $qualities[0]->name );
	}

	return 'HD';
}

if ( ! function_exists( 'doodhtheme_get_quality_badge' ) ) {
	function doodhtheme_get_quality_badge( $post_id = null ) {
		return vmtheme_get_quality_badge( $post_id );
	}
}

/**
 * Format Runtime in Hours and Minutes
 */
function vmtheme_get_runtime_formatted( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$minutes = (int) get_post_meta( $post_id, '_vm_runtime', true );
	if ( ! $minutes ) {
		$minutes = (int) get_post_meta( $post_id, '_doodh_runtime', true );
	}
	if ( ! $minutes ) {
		$minutes = (int) get_post_meta( $post_id, '_vm_episode_runtime', true );
	}
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

if ( ! function_exists( 'doodhtheme_get_runtime_formatted' ) ) {
	function doodhtheme_get_runtime_formatted( $post_id = null ) {
		return vmtheme_get_runtime_formatted( $post_id );
	}
}

/**
 * Include Modules
 */
require_once VMTHEME_DIR . '/inc/post-types.php';
require_once VMTHEME_DIR . '/inc/taxonomies.php';
require_once VMTHEME_DIR . '/inc/meta-boxes.php';
require_once VMTHEME_DIR . '/inc/downloads-manager.php';
require_once VMTHEME_DIR . '/inc/player.php';
require_once VMTHEME_DIR . '/inc/ajax-search.php';
require_once VMTHEME_DIR . '/inc/filter.php';
require_once VMTHEME_DIR . '/inc/rating-watchlist.php';
require_once VMTHEME_DIR . '/inc/seo-schema.php';
require_once VMTHEME_DIR . '/inc/customizer.php';
require_once VMTHEME_DIR . '/inc/demo-importer.php';
require_once VMTHEME_DIR . '/inc/tmdb-fetcher.php';
require_once VMTHEME_DIR . '/inc/ads-manager.php';
require_once VMTHEME_DIR . '/inc/reviews-system.php';
require_once VMTHEME_DIR . '/inc/duplicate-validator.php';
require_once VMTHEME_DIR . '/inc/sitemap-seo.php';
require_once VMTHEME_DIR . '/inc/brand-settings.php';
require_once VMTHEME_DIR . '/inc/redirects-manager.php';
require_once VMTHEME_DIR . '/inc/seed-legal-pages.php';
require_once VMTHEME_DIR . '/inc/analytics-system.php';
require_once VMTHEME_DIR . '/inc/user-auth.php';
require_once VMTHEME_DIR . '/inc/requests-system.php';
require_once VMTHEME_DIR . '/inc/agentic-api.php';
require_once VMTHEME_DIR . '/inc/homepage-manager.php';
require_once VMTHEME_DIR . '/inc/blog-system.php';
require_once VMTHEME_DIR . '/inc/user-manager.php';


