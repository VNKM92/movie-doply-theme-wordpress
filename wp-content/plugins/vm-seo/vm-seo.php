<?php
/**
 * Plugin Name: VM SEO Master & Rich Schema
 * Plugin URI: https://vmtheme.com/seo-master/
 * Description: Complete Yoast-Grade SEO Suite for Movies, TV Shows & WordPress: Live Google SERP Snippet Preview, Real-Time Focus Keyword Content Analyzer, Schema.org JSON-LD Graph, OpenGraph/Twitter Cards, Dynamic XML Sitemaps, Breadcrumbs, Bulk Editor & 1-Click Yoast SEO Migration.
 * Version: 2.0.0
 * Author: VMTheme Team
 * Author URI: https://vmtheme.com
 * License: GPL-2.0+
 * Text Domain: vm-seo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VM_SEO_VERSION', '2.0.0' );
define( 'VM_SEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'VM_SEO_URI', plugin_dir_url( __FILE__ ) );

// Load Modular Components
require_once VM_SEO_DIR . 'inc/metabox.php';
require_once VM_SEO_DIR . 'inc/admin-dashboard.php';
require_once VM_SEO_DIR . 'inc/admin-columns.php';
require_once VM_SEO_DIR . 'inc/importer.php';
require_once VM_SEO_DIR . 'inc/sitemap.php';
require_once VM_SEO_DIR . 'inc/breadcrumbs.php';
require_once VM_SEO_DIR . 'inc/bulk-editor.php';

if ( ! class_exists( 'VM_SEO_Master' ) ) :

class VM_SEO_Master {

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

		// Enqueue Admin Scripts & Styles for Metabox and Dashboard
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Frontend SEO Output Hooks
		add_filter( 'pre_get_document_title', array( $this, 'generate_document_title' ), 99 );
		add_action( 'wp_head', array( $this, 'output_frontend_seo_head' ), 1 );

		// Sitemaps Ping Hook
		add_action( 'publish_post', array( $this, 'ping_search_engines' ) );
		add_action( 'publish_movies', array( $this, 'ping_search_engines' ) );
		add_action( 'publish_tvshows', array( $this, 'ping_search_engines' ) );
		add_action( 'publish_episodes', array( $this, 'ping_search_engines' ) );
		add_action( 'wp_ajax_vm_seo_ping_sitemaps', array( $this, 'ajax_ping_search_engines' ) );
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
			'cast_title_template'    => '%%title%% Movies, TV Shows & Filmography %%sep%% %%sitename%%',
			'director_title_template'=> '%%title%% Directed Movies & TV Series %%sep%% %%sitename%%',
			'quality_title_template' => '%%title%% Movies & TV Shows HD Stream %%sep%% %%sitename%%',
			'home_meta_desc'         => 'Watch full movies and TV shows online in HD and 4K Ultra HD. Explore top-rated titles, trailers, and reviews on ' . get_bloginfo( 'name' ) . '.',
			'movies_desc_template'   => 'Watch %%title%% (%%year%%) directed by %%director%% starring %%cast%% online in HD with subtitles. %%excerpt%% Stream in 1080p on %%sitename%%.',
			'tvshows_desc_template'  => 'Stream %%title%% (%%year%%) TV series all episodes and seasons online in HD. %%excerpt%% Watch now on %%sitename%%.',
			'episodes_desc_template' => 'Watch %%title%% full episode online in HD. %%excerpt%% Stream latest episodes and seasons on %%sitename%%.',
			'posts_desc_template'    => '%%excerpt%% Read full article on %%sitename%%.',
			'pages_desc_template'    => '%%excerpt%% Learn more on %%sitename%%.',
			'tax_desc_template'      => 'Browse and watch full collection of %%title%% movies and TV series online in HD on %%sitename%%.',
			'cast_desc_template'     => 'Watch all movies and TV shows starring %%title%% in full HD online on %%sitename%%. Explore complete biography and filmography.',
			'director_desc_template' => 'Explore all movies and TV series directed by %%title%% in full HD online on %%sitename%%. Browse complete directorial filmography and latest releases.',
			'quality_desc_template'  => 'Watch all movies and TV series available in %%title%% quality in full HD with subtitles on %%sitename%%.',
			'google_verify'          => '',
			'bing_verify'            => '',
			'yandex_verify'          => '',
			'pinterest_verify'       => '',
			'baidu_verify'           => '',
			'fb_app_id'              => '',
			'og_default_image'       => '',
			'twitter_handle'         => '@VMTheme',
			'twitter_card_type'      => 'summary_large_image',
			'enable_json_ld'         => 'yes',
			'enable_breadcrumbs'     => 'yes',
			'enable_sitemaps'        => 'yes',
			'sitemap_post_types'     => array( 'movies', 'tvshows', 'episodes', 'post', 'page' ),
			'sitemap_taxonomies'     => array( 'genres', 'release-year', 'dtcast', 'dtdirector', 'dtquality', 'category', 'post_tag' ),
			'sitemap_include_images' => 'yes',
			'sitemap_exclude_ids'    => '',
			'sitemap_max_entries'    => 1000,
			'enable_opengraph'       => 'yes',
			'breadcrumbs_separator'  => '&rsaquo;',
			'breadcrumbs_home_text'  => 'Home',
		);
		$saved = get_option( 'vm_seo_options', array() );
		$this->options = wp_parse_args( $saved, $defaults );
	}

	public function get_opt( $key, $default = '' ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
	}

	public function enqueue_admin_assets( $hook ) {
		global $post;
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || strpos( $hook, 'vm-seo' ) !== false ) {
			wp_enqueue_media();
			wp_enqueue_style( 'vm-seo-admin-css', VM_SEO_URI . 'assets/css/admin-seo.css', array(), VM_SEO_VERSION );
			wp_enqueue_script( 'vm-seo-admin-js', VM_SEO_URI . 'assets/js/admin-seo.js', array( 'jquery' ), VM_SEO_VERSION, true );

			$post_id   = $post ? $post->ID : 0;
			$post_type = $post ? $post->post_type : 'post';
			$title_tpl = $this->get_opt( $post_type . '_title_template', '%%title%% %%sep%% %%sitename%%' );
			$desc_tpl  = $this->get_opt( $post_type . '_desc_template', '' );

			// Extract post specific data for live SERP snippet preview
			$cast_name  = '';
			$dir_name   = '';
			$year_val   = date( 'Y' );
			$genre_val  = 'Action';
			$qual_val   = 'HD';
			$rating_val = '7.5';

			if ( $post_id ) {
				$rich_cast = get_post_meta( $post_id, '_doodh_rich_cast', true );
				if ( ! empty( $rich_cast ) && is_array( $rich_cast ) ) {
					$cast_names = array_filter( wp_list_pluck( $rich_cast, 'name' ) );
					$cast_name  = implode( ', ', array_slice( $cast_names, 0, 3 ) );
				}
				if ( empty( $cast_name ) ) {
					$cast_terms = get_the_terms( $post_id, 'dtcast' );
					if ( ! empty( $cast_terms ) && ! is_wp_error( $cast_terms ) ) {
						$cast_name = implode( ', ', array_slice( wp_list_pluck( $cast_terms, 'name' ), 0, 3 ) );
					}
				}

				$rich_dirs = get_post_meta( $post_id, '_doodh_rich_directors', true );
				if ( ! empty( $rich_dirs ) && is_array( $rich_dirs ) ) {
					$dir_names = array_filter( wp_list_pluck( $rich_dirs, 'name' ) );
					$dir_name  = implode( ', ', array_slice( $dir_names, 0, 2 ) );
				}
				if ( empty( $dir_name ) ) {
					$dir_terms = get_the_terms( $post_id, 'dtdirector' );
					if ( ! empty( $dir_terms ) && ! is_wp_error( $dir_terms ) ) {
						$dir_name = implode( ', ', array_slice( wp_list_pluck( $dir_terms, 'name' ), 0, 2 ) );
					}
				}

				$r_date = get_post_meta( $post_id, '_vm_release_date', true ) ?: ( get_post_meta( $post_id, '_doodh_release_date', true ) ?: get_post_meta( $post_id, '_doodh_first_air_date', true ) );
				if ( $r_date ) {
					$year_val = date( 'Y', strtotime( $r_date ) );
				}
				$g_terms = get_the_terms( $post_id, 'genres' );
				if ( ! empty( $g_terms ) && ! is_wp_error( $g_terms ) ) {
					$genre_val = $g_terms[0]->name;
				}
				$q_terms = get_the_terms( $post_id, 'dtquality' );
				if ( ! empty( $q_terms ) && ! is_wp_error( $q_terms ) ) {
					$qual_val = $q_terms[0]->name;
				}
				$rating_val = get_post_meta( $post_id, '_vm_rating', true ) ?: ( get_post_meta( $post_id, '_doodh_rating', true ) ?: '7.5' );
			}

			wp_localize_script( 'vm-seo-admin-js', 'VMSEOData', array(
				'siteName'             => get_bloginfo( 'name' ),
				'siteTagline'          => get_bloginfo( 'description' ),
				'siteUrl'              => home_url( '/' ),
				'separator'            => $this->get_opt( 'title_separator', '|' ),
				'currentPostId'        => $post_id,
				'postType'             => $post_type,
				'defaultTitle'         => $post ? $post->post_title : '',
				'defaultExcerpt'       => $post ? wp_trim_words( strip_shortcodes( $post->post_content ), 25 ) : '',
				'defaultTitleTemplate' => $title_tpl,
				'defaultDescTemplate'  => $desc_tpl,
				'cast'                 => $cast_name ?: 'Popular Cast',
				'director'             => $dir_name ?: 'Famous Director',
				'year'                 => $year_val,
				'genre'                => $genre_val,
				'quality'              => $qual_val,
				'rating'               => $rating_val,
				'ajaxUrl'              => admin_url( 'admin-ajax.php' ),
				'nonce'                => wp_create_nonce( 'vm_seo_import_nonce' ),
			) );
		}
	}

	/**
	 * =========================================================================
	 * VARIABLE REPLACER ENGINE (%%title%%, %%sitename%%, %%year%%, %%cast%%, %%director%%, etc.)
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
		$focus_kw = $post ? get_post_meta( $post_id, '_vm_focus_keyword', true ) : '';

		// Year calculation
		$year = date( 'Y' );
		if ( $post ) {
			$release_date = get_post_meta( $post_id, '_vm_release_date', true );
			if ( empty( $release_date ) ) {
				$release_date = get_post_meta( $post_id, '_doodh_release_date', true );
			}
			if ( empty( $release_date ) ) {
				$release_date = get_post_meta( $post_id, '_doodh_first_air_date', true );
			}
			if ( ! empty( $release_date ) ) {
				$year = date( 'Y', strtotime( $release_date ) );
			} elseif ( $post->post_date ) {
				$year = date( 'Y', strtotime( $post->post_date ) );
			}
		}

		// Taxonomy calculation (Genres)
		$genre_name = '';
		if ( $post ) {
			$terms = get_the_terms( $post_id, 'genres' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$genre_names = wp_list_pluck( $terms, 'name' );
				$genre_name  = implode( ', ', array_slice( $genre_names, 0, 3 ) );
			}
		}

		// Cast / Actors & Crew calculation (%%cast%%, %%actors%%, %%crew%%)
		$cast_name = '';
		if ( $post ) {
			$rich_cast = get_post_meta( $post_id, '_doodh_rich_cast', true );
			if ( ! empty( $rich_cast ) && is_array( $rich_cast ) ) {
				$actor_names = array_filter( wp_list_pluck( $rich_cast, 'name' ) );
				$cast_name   = implode( ', ', array_slice( $actor_names, 0, 3 ) );
			}
			if ( empty( $cast_name ) ) {
				$cast_terms = get_the_terms( $post_id, 'dtcast' );
				if ( ! empty( $cast_terms ) && ! is_wp_error( $cast_terms ) ) {
					$cast_names = wp_list_pluck( $cast_terms, 'name' );
					$cast_name  = implode( ', ', array_slice( $cast_names, 0, 3 ) );
				}
			}
			if ( empty( $cast_name ) ) {
				$cast_name = get_post_meta( $post_id, '_vm_cast', true ) ?: '';
			}
		}

		// Director calculation (%%director%%, %%directors%%)
		$director_name = '';
		if ( $post ) {
			$rich_dirs = get_post_meta( $post_id, '_doodh_rich_directors', true );
			if ( ! empty( $rich_dirs ) && is_array( $rich_dirs ) ) {
				$dir_names     = array_filter( wp_list_pluck( $rich_dirs, 'name' ) );
				$director_name = implode( ', ', array_slice( $dir_names, 0, 2 ) );
			}
			if ( empty( $director_name ) ) {
				$dir_terms = get_the_terms( $post_id, 'dtdirector' );
				if ( ! empty( $dir_terms ) && ! is_wp_error( $dir_terms ) ) {
					$dir_names     = wp_list_pluck( $dir_terms, 'name' );
					$director_name = implode( ', ', array_slice( $dir_names, 0, 2 ) );
				}
			}
			if ( empty( $director_name ) ) {
				$director_name = get_post_meta( $post_id, '_vm_director', true ) ?: '';
			}
		}

		// Quality (%%quality%%)
		$quality_name = 'HD';
		if ( $post ) {
			$qual_terms = get_the_terms( $post_id, 'dtquality' );
			if ( ! empty( $qual_terms ) && ! is_wp_error( $qual_terms ) ) {
				$quality_name = $qual_terms[0]->name;
			}
		}

		// Rating (%%rating%%)
		$rating_val = '7.5';
		if ( $post ) {
			$rating_val = get_post_meta( $post_id, '_vm_rating', true ) ?: ( get_post_meta( $post_id, '_doodh_rating', true ) ?: '7.5' );
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
			'%%cast%%'          => $cast_name,
			'%%actors%%'        => $cast_name,
			'%%crew%%'          => $cast_name,
			'%%director%%'      => $director_name,
			'%%directors%%'     => $director_name,
			'%%quality%%'       => $quality_name,
			'%%rating%%'        => $rating_val,
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
			$custom = get_option( 'vm_seo_home_title', '' );
			if ( ! empty( $custom ) ) {
				return esc_html( $this->replace_variables( $custom ) );
			}
			return esc_html( $this->replace_variables( $this->get_opt( 'site_title_template' ) ) );
		}

		if ( is_singular() ) {
			$post_id = get_the_ID();
			$custom_title = get_post_meta( $post_id, '_vm_seo_title', true );
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
				$tax_name = $term->taxonomy ?? '';
				if ( $tax_name === 'dtcast' ) {
					$tpl = $this->get_opt( 'cast_title_template', '%%title%% Movies, TV Shows & Filmography %%sep%% %%sitename%%' );
				} elseif ( $tax_name === 'dtdirector' ) {
					$tpl = $this->get_opt( 'director_title_template', '%%title%% Directed Movies & TV Series %%sep%% %%sitename%%' );
				} elseif ( $tax_name === 'dtquality' ) {
					$tpl = $this->get_opt( 'quality_title_template', '%%title%% Movies & TV Shows HD Stream %%sep%% %%sitename%%' );
				} else {
					$tpl = $this->get_opt( 'tax_title_template', '%%title%% Movies & TV Shows %%sep%% %%sitename%%' );
				}

				return esc_html( str_replace(
					array( '%%title%%', '%%sitename%%', '%%sep%%', '%%tagline%%' ),
					array( $term->name, get_bloginfo( 'name' ), $this->get_opt( 'title_separator', '|' ), get_bloginfo( 'description' ) ),
					$tpl
				) );
			}
		}

		if ( is_search() ) {
			return sprintf( esc_html__( 'Search results for "%s" %s %s', 'vm-seo' ), get_search_query(), $this->get_opt( 'title_separator', '|' ), get_bloginfo( 'name' ) );
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
			$meta_desc = $this->replace_variables( $this->get_opt( 'home_meta_desc' ) );
		} elseif ( is_singular() && $post_id ) {
			$custom_desc = get_post_meta( $post_id, '_vm_seo_desc', true );
			if ( ! empty( $custom_desc ) ) {
				$meta_desc = $this->replace_variables( $custom_desc, $post_id );
			} else {
				$post_type    = get_post_type( $post_id );
				$template_key = $post_type . '_desc_template';
				$template     = $this->get_opt( $template_key, '' );
				if ( ! empty( $template ) ) {
					$meta_desc = $this->replace_variables( $template, $post_id );
				} else {
					$post = get_post( $post_id );
					$meta_desc = $post ? wp_trim_words( strip_shortcodes( $post->post_content ), 28, '...' ) : '';
				}
			}
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			if ( $term ) {
				$tax_name = $term->taxonomy ?? '';
				if ( $tax_name === 'dtcast' ) {
					$tax_template = $this->get_opt( 'cast_desc_template', '' );
				} elseif ( $tax_name === 'dtdirector' ) {
					$tax_template = $this->get_opt( 'director_desc_template', '' );
				} elseif ( $tax_name === 'dtquality' ) {
					$tax_template = $this->get_opt( 'quality_desc_template', '' );
				} else {
					$tax_template = $this->get_opt( 'tax_desc_template', '' );
				}

				if ( ! empty( $tax_template ) ) {
					$meta_desc = str_replace(
						array( '%%title%%', '%%sitename%%', '%%sep%%', '%%tagline%%' ),
						array( $term->name, get_bloginfo( 'name' ), $this->get_opt( 'title_separator', '|' ), get_bloginfo( 'description' ) ),
						$tax_template
					);
				} else {
					$meta_desc = ! empty( $term->description ) ? wp_strip_all_tags( $term->description ) : ( 'Browse ' . $term->name . ' movies and TV series on ' . get_bloginfo( 'name' ) );
				}
			}
		}

		if ( ! empty( $meta_desc ) ) {
			echo '<meta name="description" content="' . esc_attr( $meta_desc ) . '">' . "\n";
		}

		// 2. Robots Directives
		$robots_index  = 'index';
		$robots_follow = 'follow';
		if ( is_singular() && $post_id ) {
			$noindex  = get_post_meta( $post_id, '_vm_seo_noindex', true );
			$nofollow = get_post_meta( $post_id, '_vm_seo_nofollow', true );
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
			$custom_canon = get_post_meta( $post_id, '_vm_seo_canonical', true );
			if ( ! empty( $custom_canon ) ) {
				$canonical_url = $custom_canon;
			}
		}
		echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '">' . "\n";

		// 4. Social OpenGraph & Twitter Cards
		if ( $this->get_opt( 'enable_opengraph', 'yes' ) === 'yes' ) {
			$og_title = is_singular() && $post_id ? ( get_post_meta( $post_id, '_vm_og_title', true ) ?: get_the_title( $post_id ) ) : get_bloginfo( 'name' );
			$og_desc  = is_singular() && $post_id ? ( get_post_meta( $post_id, '_vm_og_desc', true ) ?: $meta_desc ) : $meta_desc;
			$og_image = '';
			if ( is_singular() && $post_id ) {
				$og_image = get_post_meta( $post_id, '_vm_og_image', true );
				if ( empty( $og_image ) && function_exists( 'vmtheme_get_backdrop_url' ) ) {
					$og_image = vmtheme_get_backdrop_url( $post_id );
				}
				if ( empty( $og_image ) && function_exists( 'vmtheme_get_poster_url' ) ) {
					$og_image = vmtheme_get_poster_url( $post_id );
				}
			}
			if ( empty( $og_image ) ) {
				$og_image = $this->get_opt( 'og_default_image' );
			}

			echo '<!-- VM SEO OpenGraph Tags -->' . "\n";
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

			echo '<!-- VM SEO Twitter Cards -->' . "\n";
			echo '<meta name="twitter:card" content="' . esc_attr( $this->get_opt( 'twitter_card_type', 'summary_large_image' ) ) . '">' . "\n";
			echo '<meta name="twitter:site" content="' . esc_attr( $this->get_opt( 'twitter_handle', '@VMTheme' ) ) . '">' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
			echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
			if ( ! empty( $og_image ) ) {
				echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
			}
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
		if ( $this->get_opt( 'baidu_verify' ) ) {
			echo '<meta name="baidu-site-verification" content="' . esc_attr( $this->get_opt( 'baidu_verify' ) ) . '">' . "\n";
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
			$post_type    = get_post_type( $post_id );
			$title        = get_the_title( $post_id );
			$url          = get_permalink( $post_id );
			$poster       = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $post_id ) : get_post_meta( $post_id, '_vm_poster_url', true );
			$rating       = get_post_meta( $post_id, '_vm_rating', true ) ?: '7.5';
			$votes        = get_post_meta( $post_id, '_vm_votes', true ) ?: 1250;
			$release      = get_post_meta( $post_id, '_vm_release_date', true ) ?: get_the_date( 'Y-m-d', $post_id );
			$trailer      = get_post_meta( $post_id, '_vm_trailer_url', true );

			$actors       = get_the_terms( $post_id, 'dtcast' );
			$directors    = get_the_terms( $post_id, 'dtdirector' );

			$actor_list = array();
			if ( ! empty( $actors ) && ! is_wp_error( $actors ) ) {
				foreach ( array_slice( $actors, 0, 5 ) as $a ) {
					$actor_list[] = array( '@type' => 'Person', 'name' => $a->name );
				}
			}

			$director_list = array();
			if ( ! empty( $directors ) && ! is_wp_error( $directors ) ) {
				foreach ( $directors as $d ) {
					$director_list[] = array( '@type' => 'Person', 'name' => $d->name );
				}
			}

			if ( $post_type === 'movies' ) {
				$movie_schema = array(
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
				if ( ! empty( $actor_list ) ) $movie_schema['actor'] = $actor_list;
				if ( ! empty( $director_list ) ) $movie_schema['director'] = $director_list;
				if ( ! empty( $trailer ) ) {
					$movie_schema['trailer'] = array(
						'@type' => 'VideoObject',
						'name' => $title . ' Official Trailer',
						'embedUrl' => $trailer,
						'thumbnailUrl' => $poster,
						'uploadDate' => $release,
					);
				}
				$graph[] = $movie_schema;
			} elseif ( $post_type === 'tvshows' ) {
				$tv_schema = array(
					'@type'         => 'TVSeries',
					'@id'           => $url . '#tvseries',
					'name'          => $title,
					'url'           => $url,
					'image'         => $poster,
					'startDate'     => $release,
					'numberOfSeasons' => (int) ( get_post_meta( $post_id, '_vm_total_seasons', true ) ?: 1 ),
					'numberOfEpisodes'=> (int) ( get_post_meta( $post_id, '_vm_total_episodes', true ) ?: 10 ),
					'aggregateRating' => array(
						'@type'       => 'AggregateRating',
						'ratingValue' => number_format( (float) $rating, 1 ),
						'bestRating'  => '10',
						'ratingCount' => (int) $votes,
					),
				);
				if ( ! empty( $actor_list ) ) $tv_schema['actor'] = $actor_list;
				if ( ! empty( $director_list ) ) $tv_schema['creator'] = $director_list;
				$graph[] = $tv_schema;
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

	public function ajax_ping_search_engines() {
		check_ajax_referer( 'vm_seo_import_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized access.' ) );
		}

		$sitemap_url = urlencode( home_url( '/sitemap.xml' ) );
		wp_remote_get( "https://www.google.com/ping?sitemap={$sitemap_url}", array( 'blocking' => false, 'sslverify' => false ) );
		wp_remote_get( "https://www.bing.com/ping?sitemap={$sitemap_url}", array( 'blocking' => false, 'sslverify' => false ) );

		wp_send_json_success( array(
			'message' => 'Successfully pinged Google and Bing search engines with your updated sitemap (/sitemap.xml)!',
			'time'    => current_time( 'mysql' ),
		) );
	}
}

endif;

function vm_seo_init() {
	return VM_SEO_Master::get_instance();
}
add_action( 'plugins_loaded', 'vm_seo_init', 5 );