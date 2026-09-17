<?php
/**
 * Plugin Name: VMTheme Core & High-Performance SEO Engine
 * Plugin URI: https://vmtheme.com/
 * Description: Essential production companion plugin providing automated OpenGraph, Twitter Cards, Schema.org Rich Snippets, Real-time Search Engine Sitemap Pinging, and core video streaming optimizations.
 * Version: 2.5.0
 * Author: VMTheme Team
 * Author URI: https://vmtheme.com/
 * License: GPLv2 or later
 * Text Domain: vmtheme-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VM_CORE_VERSION', '2.5.0' );
define( 'VM_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VM_CORE_URI', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'DOODH_CORE_VERSION' ) ) {
	define( 'DOODH_CORE_VERSION', VM_CORE_VERSION );
}
if ( ! defined( 'DOODH_CORE_DIR' ) ) {
	define( 'DOODH_CORE_DIR', VM_CORE_DIR );
}
if ( ! defined( 'DOODH_CORE_URI' ) ) {
	define( 'DOODH_CORE_URI', VM_CORE_URI );
}

/**
 * 1. Allow SVG and WebP Uploads in WordPress Media Library
 */
function vmtheme_core_mime_types( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'vmtheme_core_mime_types' );

if ( ! function_exists( 'doodhtheme_core_mime_types' ) ) {
	function doodhtheme_core_mime_types( $mimes ) {
		return vmtheme_core_mime_types( $mimes );
	}
}

/**
 * 2. Automatic OpenGraph & Twitter Cards Meta Tags in Head
 */
function vmtheme_core_render_seo_meta_tags() {
	if ( is_admin() ) {
		return;
	}

	$brand_name = function_exists( 'vmtheme_get_brand_name' ) ? vmtheme_get_brand_name() : ( function_exists( 'doodhtheme_get_brand_name' ) ? doodhtheme_get_brand_name() : get_bloginfo( 'name' ) );
	$brand_desc = function_exists( 'vmtheme_get_brand_tagline' ) ? vmtheme_get_brand_tagline() : ( function_exists( 'doodhtheme_get_brand_tagline' ) ? doodhtheme_get_brand_tagline() : get_bloginfo( 'description' ) );
	$current_url = esc_url( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	$og_title = $brand_name . ' - ' . $brand_desc;
	$og_desc  = $brand_desc;
	$og_type  = 'website';
	$og_image = function_exists( 'vmtheme_get_fallback_backdrop_url' ) ? vmtheme_get_fallback_backdrop_url() : ( function_exists( 'doodhtheme_get_fallback_backdrop_url' ) ? doodhtheme_get_fallback_backdrop_url() : '' );

	if ( is_singular() ) {
		$post_id  = get_the_ID();
		$og_title = get_the_title( $post_id ) . ' - ' . $brand_name;
		$og_desc  = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
		$og_desc  = mb_substr( $og_desc, 0, 160 ) . '...';
		$og_type  = is_singular( 'movies' ) ? 'video.movie' : ( is_singular( 'tvshows' ) ? 'video.tv_show' : 'article' );

		if ( function_exists( 'vmtheme_get_backdrop_url' ) ) {
			$og_image = vmtheme_get_backdrop_url( $post_id );
		} elseif ( function_exists( 'doodhtheme_get_backdrop_url' ) ) {
			$og_image = doodhtheme_get_backdrop_url( $post_id );
		}
	} elseif ( is_tax() ) {
		$term     = get_queried_object();
		$og_title = $term->name . ' Movies & TV Shows Catalog - ' . $brand_name;
		$og_desc  = sprintf( 'Watch top rated %s titles online in 4K Ultra HD on %s.', $term->name, $brand_name );
	}

	?>
	<!-- DoodhTheme Core SEO OpenGraph & Twitter Meta Tags -->
	<meta property="og:site_name" content="<?php echo esc_attr( $brand_name ); ?>">
	<meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $og_title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $current_url ); ?>">
	<?php if ( ! empty( $og_image ) ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">
		<meta property="og:image:width" content="1280">
		<meta property="og:image:height" content="720">
	<?php endif; ?>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $og_title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>">
	<?php if ( ! empty( $og_image ) ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
	<?php endif; ?>

	<link rel="canonical" href="<?php echo esc_url( $current_url ); ?>">
	<!-- / End VMTheme Core SEO -->
	<?php
}
add_action( 'wp_head', 'vmtheme_core_render_seo_meta_tags', 1 );

if ( ! function_exists( 'doodhtheme_core_render_seo_meta_tags' ) ) {
	function doodhtheme_core_render_seo_meta_tags() {
		vmtheme_core_render_seo_meta_tags();
	}
}

/**
 * 3. Instant Search Engine Sitemap Ping on Post Publication
 */
function vmtheme_core_ping_search_engines( $post_id, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( $post->post_status !== 'publish' || ! in_array( $post->post_type, array( 'movies', 'tvshows', 'episodes' ) ) ) {
		return;
	}

	$sitemap_url = urlencode( home_url( '/sitemap.xml' ) );

	// Ping Google Sitemap endpoint
	wp_remote_get( "https://www.google.com/ping?sitemap={$sitemap_url}", array(
		'timeout'  => 5,
		'blocking' => false,
	) );

	// Ping Bing Sitemap endpoint
	wp_remote_get( "https://www.bing.com/ping?sitemap={$sitemap_url}", array(
		'timeout'  => 5,
		'blocking' => false,
	) );
}
add_action( 'save_post', 'vmtheme_core_ping_search_engines', 20, 3 );

if ( ! function_exists( 'doodhtheme_core_ping_search_engines' ) ) {
	function doodhtheme_core_ping_search_engines( $post_id, $post, $update ) {
		vmtheme_core_ping_search_engines( $post_id, $post, $update );
	}
}

