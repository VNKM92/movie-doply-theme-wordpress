<?php
/**
 * Plugin Name: Doodh SEO Master & Rich Schema
 * Plugin URI: https://doodhtheme.com/seo-master/
 * Description: All-in-One Yoast-style SEO Suite: Live Google SERP Snippet Preview, Focus Keyword Content Analyzer, Schema.org JSON-LD Graph, OpenGraph/Twitter Cards, Dynamic XML Sitemaps, and Webmaster Tools.
 * Version: 1.0.0
 * Author: DoodhTheme Team
 * Author URI: https://doodhtheme.com
 * License: GPL-2.0+
 * Text Domain: doodh-seo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DOODH_SEO_VERSION', '1.0.0' );
define( 'DOODH_SEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'DOODH_SEO_URI', plugin_dir_url( __FILE__ ) );

require_once DOODH_SEO_DIR . 'inc/metabox.php';
require_once DOODH_SEO_DIR . 'inc/admin-dashboard.php';
require_once DOODH_SEO_DIR . 'inc/admin-columns.php';

if ( ! class_exists( 'Doodh_SEO_Master' ) ) :

class Doodh_SEO_Master {

	private static $instance = null;
	public $options = array();

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_options();

		// Enqueue Admin Scripts & Styles for Metabox
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Frontend SEO Output Hooks
		add_filter( 'pre_get_document_title', array( $this, 'generate_document_title' ), 99 );
		add_action( 'wp_head', array( $this, 'output_frontend_seo_head' ), 1 );

		// Sitemaps Ping Hook
		add_action( 'publish_post', array( $this, 'ping_search_engines' ) );
		add_action( 'publish_movies', array( $this, 'ping_search_engines' ) );
		add_action( 'publish_tvshows', array( $this, 'ping_search_engines' ) );
	}

	public function load_options() {
		$defaults = array(
			'title_separator'        => '|',
			'site_title_template'    => '%%sitename%% %%sep%% %%tagline%%',
			'movies_title_template'  => '%%title%% (%%year%%) Full Movie HD Stream %%sep%% %%sitename%%',
			'tvshows_title_template' => '%%title%% (%%year%%) TV Series HD Online %%sep%% %%sitename%%',
			'episodes_title_template'=> '%%title%% %%sep%% %%sitename%%',
			'posts_title_template'   => '%%title%% %%sep%% %%sitename%%',
			'pages_title_template'   => '%%title%% %%sep%% %%sitename%%',
			'tax_title_template'     => '%%title%% Movies & TV Shows %%sep%% %%sitename%%',
			'home_meta_desc'         => 'Watch full movies and TV shows online in HD and 4K Ultra HD. Explore top-rated titles, trailers, and reviews on ' . get_bloginfo( 'name' ) . '.',
			'google_verify'          => '',
			'bing_verify'            => '',
			'yandex_verify'          => '',
			'pinterest_verify'       => '',
			'og_default_image'       => '',
			'twitter_handle'         => '@DoodhTheme',
			'twitter_card_type'      => 'summary_large_image',
			'enable_json_ld'         => 'yes',
			'enable_breadcrumbs'     => 'yes',
		);
		$saved = get_option( 'doodh_seo_options', array() );
		$this->options = wp_parse_args( $saved, $defaults );
	}

	public function get_opt( $key, $default = '' ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
	}

	public function enqueue_admin_assets( $hook ) {
		global $post;
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || strpos( $hook, 'doodh-seo' ) !== false ) {
			wp_enqueue_style( 'doodh-seo-admin-css', DOODH_SEO_URI . 'assets/css/admin-seo.css', array(), DOODH_SEO_VERSION );
			wp_enqueue_script( 'doodh-seo-admin-js', DOODH_SEO_URI . 'assets/js/admin-seo.js', array( 'jquery' ), DOODH_SEO_VERSION, true );

			wp_localize_script( 'doodh-seo-admin-js', 'DoodhSEOData', array(
				'siteName'       => get_bloginfo( 'name' ),
				'siteTagline'    => get_bloginfo( 'description' ),
				'siteUrl'        => home_url( '/' ),
				'separator'      => $this->get_opt( 'title_separator', '|' ),
				'currentPostId'  => $post ? $post->ID : 0,
				'postType'       => $post ? $post->post_type : 'post',
				'defaultTitle'   => $post ? $post->post_title : '',
				'defaultExcerpt' => $post ? wp_trim_words( $post->post_content, 25 ) : '',
			) );
		}
	}

	/**
	 * =========================================================================
	 * VARIABLE REPLACER ENGINE (%%title%%, %%sitename%%, %%year%%, etc.)
	 * =========================================================================
	 */
	public function replace_variables( $string, $post_id = null ) {
		if ( empty( $string ) ) {
			return '';
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$post = $post_id ? get_post( $post_id ) : null;

		$title    = $post ? get_the_title( $post_id ) : get_bloginfo( 'name' );
		$sitename = get_bloginfo( 'name' );
		$tagline  = get_bloginfo( 'description' );
		$sep      = $this->get_opt( 'title_separator', '|' );
		$excerpt  = $post ? wp_trim_words( strip_shortcodes( $post->post_content ), 25, '...' ) : '';
		$focus_kw = $post ? get_post_meta( $post_id, '_doodh_focus_keyword', true ) : '';

		// Year calculation
		$year = date( 'Y' );
		if ( $post ) {
			$release_date = get_post_meta( $post_id, '_doodh_release_date', true );
			if ( ! empty( $release_date ) ) {
				$year = date( 'Y', strtotime( $release_date ) );
			} elseif ( $post->post_date ) {
				$year = date( 'Y', strtotime( $post->post_date ) );
			}
		}

		// Taxonomy calculation
		$genre_name = '';
		if ( $post ) {
			$terms = get_the_terms( $post_id, 'genres' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$genre_name = $terms[0]->name;
			}
		}

		$replacements = array(
			'%%title%%'         => $title,
			'%%sitename%%'      => $sitename,
			'%%tagline%%'       => $tagline,
			'%%sep%%'           => $sep,
			'%%excerpt%%'       => $excerpt,
			'%%year%%'          => $year,
			'%%category%%'      => $genre_name,
			'%%genres%%'        => $genre_name,
			'%%focus_keyword%%' => $focus_kw,
			'%%date%%'          => $post ? get_the_date( '', $post_id ) : date( 'Y-m-d' ),
		);

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $string );
	}

	/**
	 * =========================================================================
	 * DYNAMIC TITLE & HEAD META OUTPUT
	 * =========================================================================
	 */
	public function generate_document_title( $title ) {
		if ( is_front_page() || is_home() ) {
			$custom = get_option( 'doodh_seo_home_title', '' );
			if ( ! empty( $custom ) ) {
				return esc_html( $this->replace_variables( $custom ) );
			}
			return esc_html( $this->replace_variables( $this->get_opt( 'site_title_template' ) ) );
		}

		if ( is_singular() ) {
			$post_id = get_the_ID();
			$custom_title = get_post_meta( $post_id, '_doodh_seo_title', true );
			if ( ! empty( $custom_title ) ) {
				return esc_html( $this->replace_variables( $custom_title, $post_id ) );
			}

			$post_type = get_post_type( $post_id );
			$template_key = $post_type . '_title_template';
			$template = $this->get_opt( $template_key, '%%title%% %%sep%% %%sitename%%' );
			return esc_html( $this->replace_variables( $template, $post_id ) );
		}

		if ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			if ( $term ) {
				return esc_html( $term->name . ' Movies & TV Shows ' . $this->get_opt( 'title_separator', '|' ) . ' ' . get_bloginfo( 'name' ) );
			}
		}

		if ( is_search() ) {
			return sprintf( esc_html__( 'Search results for "%s" %s %s', 'doodh-seo' ), get_search_query(), $this->get_opt( 'title_separator', '|' ), get_bloginfo( 'name' ) );
		}

		return $title;
	}

	public function output_frontend_seo_head() {
		$post_id   = is_singular() ? get_the_ID() : 0;
		$site_name = get_bloginfo( 'name' );
		$sep       = $this->get_opt( 'title_separator', '|' );

		// 1. Meta Description
		$meta_desc = '';
		if ( is_front_page() || is_home() ) {
			$meta_desc = $this->get_opt( 'home_meta_desc' );
		} elseif ( is_singular() && $post_id ) {
			$custom_desc = get_post_meta( $post_id, '_doodh_seo_desc', true );
			if ( ! empty( $custom_desc ) ) {
				$meta_desc = $this->replace_variables( $custom_desc, $post_id );
			} else {
				$post = get_post( $post_id );
				$meta_desc = wp_trim_words( strip_shortcodes( $post->post_content ), 28, '...' );
			}
		}

		if ( ! empty( $meta_desc ) ) {
			echo '<meta name="description" content="' . esc_attr( $meta_desc ) . '">' . "\n";
		}

		// 2. Robots Directives
		$robots_index  = 'index';
		$robots_follow = 'follow';
		if ( is_singular() && $post_id ) {
			$noindex  = get_post_meta( $post_id, '_doodh_seo_noindex', true );
			$nofollow = get_post_meta( $post_id, '_doodh_seo_nofollow', true );
			if ( $noindex === 'yes' ) {
				$robots_index = 'noindex';
			}
			if ( $nofollow === 'yes' ) {
				$robots_follow = 'nofollow';
			}
		}
		if ( is_search() || is_404() ) {
			$robots_index = 'noindex';
		}
		echo '<meta name="robots" content="' . esc_attr( "{$robots_index}, {$robots_follow}, max-snippet:-1, max-image-preview:large, max-video-preview:-1" ) . '">' . "\n";

		// 3. Canonical URL
		$canonical_url = is_singular() ? get_permalink( $post_id ) : home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
		if ( is_singular() && $post_id ) {
			$custom_canon = get_post_meta( $post_id, '_doodh_seo_canonical', true );
			if ( ! empty( $custom_canon ) ) {
				$canonical_url = $custom_canon;
			}
		}
		echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '">' . "\n";

		// 4. Social OpenGraph & Twitter Cards
		$og_title = is_singular() && $post_id ? ( get_post_meta( $post_id, '_doodh_og_title', true ) ?: get_the_title( $post_id ) ) : get_bloginfo( 'name' );
		$og_desc  = is_singular() && $post_id ? ( get_post_meta( $post_id, '_doodh_og_desc', true ) ?: $meta_desc ) : $meta_desc;
		$og_image = '';
		if ( is_singular() && $post_id ) {
			$og_image = get_post_meta( $post_id, '_doodh_og_image', true );
			if ( empty( $og_image ) && function_exists( 'doodhtheme_get_backdrop_url' ) ) {
				$og_image = doodhtheme_get_backdrop_url( $post_id );
			}
		}
		if ( empty( $og_image ) ) {
			$og_image = $this->get_opt( 'og_default_image' );
		}

		echo '<!-- Doodh SEO OpenGraph Tags -->' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
		echo '<meta property="og:type" content="' . ( is_singular( 'movies' ) ? 'video.movie' : ( is_singular( 'tvshows' ) ? 'video.tv_show' : 'website' ) ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $canonical_url ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
		if ( ! empty( $og_image ) ) {
			echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
			echo '<meta property="og:image:width" content="1280">' . "\n";
			echo '<meta property="og:image:height" content="720">' . "\n";
		}

		echo '<!-- Doodh SEO Twitter Cards -->' . "\n";
		echo '<meta name="twitter:card" content="' . esc_attr( $this->get_opt( 'twitter_card_type', 'summary_large_image' ) ) . '">' . "\n";
		echo '<meta name="twitter:site" content="' . esc_attr( $this->get_opt( 'twitter_handle', '@DoodhTheme' ) ) . '">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
		if ( ! empty( $og_image ) ) {
			echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
		}

		// 5. Webmaster Tools Verification Meta
		if ( $this->get_opt( 'google_verify' ) ) {
			echo '<meta name="google-site-verification" content="' . esc_attr( $this->get_opt( 'google_verify' ) ) . '">' . "\n";
		}
		if ( $this->get_opt( 'bing_verify' ) ) {
			echo '<meta name="msvalidate.01" content="' . esc_attr( $this->get_opt( 'bing_verify' ) ) . '">' . "\n";
		}
		if ( $this->get_opt( 'yandex_verify' ) ) {
			echo '<meta name="yandex-verification" content="' . esc_attr( $this->get_opt( 'yandex_verify' ) ) . '">' . "\n";
		}
		if ( $this->get_opt( 'pinterest_verify' ) ) {
			echo '<meta name="p:domain_verify" content="' . esc_attr( $this->get_opt( 'pinterest_verify' ) ) . '">' . "\n";
		}

		// 6. Schema.org JSON-LD Structured Graph
		if ( $this->get_opt( 'enable_json_ld', 'yes' ) === 'yes' ) {
			$this->output_schema_json_ld( $post_id );
		}
	}

	public function output_schema_json_ld( $post_id = null ) {
		$graph = array();

		// WebSite Schema
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);

		// Movie / TV Show / Episode Schema
		if ( is_singular() && $post_id ) {
			$post_type = get_post_type( $post_id );
			$title     = get_the_title( $post_id );
			$url       = get_permalink( $post_id );
			$poster    = function_exists( 'doodhtheme_get_poster_url' ) ? doodhtheme_get_poster_url( $post_id ) : '';
			$rating    = get_post_meta( $post_id, '_doodh_rating', true ) ?: '7.5';
			$votes     = get_post_meta( $post_id, '_doodh_votes', true ) ?: 1250;
			$release   = get_post_meta( $post_id, '_doodh_release_date', true ) ?: get_the_date( 'Y-m-d', $post_id );

			if ( $post_type === 'movies' ) {
				$graph[] = array(
					'@type'         => 'Movie',
					'@id'           => $url . '#movie',
					'name'          => $title,
					'url'           => $url,
					'image'         => $poster,
					'dateCreated'   => $release,
					'aggregateRating' => array(
						'@type'       => 'AggregateRating',
						'ratingValue' => number_format( (float) $rating, 1 ),
						'bestRating'  => '10',
						'ratingCount' => (int) $votes,
					),
				);
			} elseif ( $post_type === 'tvshows' ) {
				$graph[] = array(
					'@type'         => 'TVSeries',
					'@id'           => $url . '#tvseries',
					'name'          => $title,
					'url'           => $url,
					'image'         => $poster,
					'startDate'     => $release,
					'aggregateRating' => array(
						'@type'       => 'AggregateRating',
						'ratingValue' => number_format( (float) $rating, 1 ),
						'bestRating'  => '10',
						'ratingCount' => (int) $votes,
					),
				);
			}
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
	}

	public function ping_search_engines() {
		$sitemap_url = urlencode( home_url( '/sitemap.xml' ) );
		wp_remote_get( "https://www.google.com/ping?sitemap={$sitemap_url}", array( 'blocking' => false, 'sslverify' => false ) );
		wp_remote_get( "https://www.bing.com/ping?sitemap={$sitemap_url}", array( 'blocking' => false, 'sslverify' => false ) );
	}
}

endif;

function doodh_seo_init() {
	return Doodh_SEO_Master::get_instance();
}
add_action( 'plugins_loaded', 'doodh_seo_init', 5 );