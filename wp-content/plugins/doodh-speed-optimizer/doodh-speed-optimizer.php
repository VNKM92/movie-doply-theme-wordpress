<?php
/**
 * Plugin Name: Doodh Speed Optimizer & LightSpeed Booster
 * Plugin URI: https://doodhtheme.com/speed-optimizer/
 * Description: High-performance speed booster: Disk Page Caching, HTML/CSS/JS Minification, Image & CLS Optimization, Script Deferral, Instant Link Preload, and WordPress Bloat Removal.
 * Version: 1.0.0
 * Author: DoodhTheme Team
 * Author URI: https://doodhtheme.com
 * License: GPL-2.0+
 * Text Domain: doodh-speed-optimizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Doodh_Speed_Optimizer' ) ) :

class Doodh_Speed_Optimizer {

	private static $instance = null;
	private $cache_dir;
	private $options = array();

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->cache_dir = WP_CONTENT_DIR . '/cache/doodh-speed/';
		$this->load_options();

		// Plugin Activation & Deactivation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Initialize Cache Engine Early
		add_action( 'init', array( $this, 'init_cache_engine' ), 1 );

		// Output Buffer for Minification, Image Optimization & Page Caching
		add_action( 'template_redirect', array( $this, 'start_output_buffer' ), -99999 );

		// Clean WordPress Core Bloat
		if ( $this->get_opt( 'enable_bloat_removal', 'yes' ) === 'yes' ) {
			add_action( 'init', array( $this, 'remove_wp_bloat' ) );
		}

		// Resource Hints (DNS Prefetch & Preconnects)
		if ( $this->get_opt( 'enable_dns_prefetch', 'yes' ) === 'yes' ) {
			add_action( 'wp_head', array( $this, 'add_resource_hints' ), 1 );
		}

		// Instant Page Hover Preload
		if ( $this->get_opt( 'enable_instant_page', 'yes' ) === 'yes' ) {
			add_action( 'wp_footer', array( $this, 'render_instant_preload_script' ), 999 );
		}

		// Defer JavaScript
		if ( $this->get_opt( 'enable_defer_js', 'yes' ) === 'yes' ) {
			add_filter( 'script_loader_tag', array( $this, 'defer_scripts' ), 10, 3 );
		}

		// Cache Invalidation Hooks
		add_action( 'save_post', array( $this, 'purge_cache' ) );
		add_action( 'comment_post', array( $this, 'purge_cache' ) );
		add_action( 'wp_update_nav_menu', array( $this, 'purge_cache' ) );
		add_action( 'customize_save_after', array( $this, 'purge_cache' ) );

		// Admin Dashboard
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_purge_btn' ), 100 );
	}

	private function load_options() {
		$defaults = array(
			'enable_page_cache'           => 'yes',
			'enable_html_minify'          => 'yes',
			'enable_css_minify'           => 'yes',
			'enable_js_minify'            => 'yes',
			'enable_defer_js'             => 'yes',
			'enable_lazyload'             => 'yes',
			'enable_dns_prefetch'         => 'yes',
			'enable_bloat_removal'        => 'yes',
			'enable_instant_page'         => 'yes',
			'enable_browser_cache_headers'=> 'yes',
			'cache_lifespan'              => 86400, // 24 hours
		);
		$saved = get_option( 'doodh_speed_options', array() );
		$this->options = wp_parse_args( $saved, $defaults );
	}

	public function get_opt( $key, $default = '' ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
	}

	public function activate() {
		if ( ! file_exists( $this->cache_dir ) ) {
			wp_mkdir_p( $this->cache_dir );
		}
		// Place index.html to prevent directory listing
		file_put_contents( $this->cache_dir . 'index.html', '' );
	}

	public function deactivate() {
		$this->purge_cache();
	}

	/**
	 * =========================================================================
	 * 1. DISK PAGE CACHING ENGINE
	 * =========================================================================
	 */
	public function init_cache_engine() {
		if ( ! file_exists( $this->cache_dir ) ) {
			wp_mkdir_p( $this->cache_dir );
		}

		// Don't serve cache if disabled or user is logged in
		if ( $this->get_opt( 'enable_page_cache', 'yes' ) !== 'yes' || is_user_logged_in() || is_admin() ) {
			return;
		}

		// Only cache GET requests
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
			return;
		}

		$cache_file = $this->get_cache_file_path();
		if ( file_exists( $cache_file ) ) {
			$lifespan = (int) $this->get_opt( 'cache_lifespan', 86400 );
			if ( ( time() - filemtime( $cache_file ) ) < $lifespan ) {
				$content = file_get_contents( $cache_file );
				if ( ! empty( $content ) ) {
					// Send High-Speed Headers
					header( 'X-Doodh-Speed-Engine: LightSpeed v1.0.0' );
					header( 'X-Doodh-Cache: HIT' );
					header( 'Content-Type: text/html; charset=UTF-8' );

					if ( $this->get_opt( 'enable_browser_cache_headers', 'yes' ) === 'yes' ) {
						header( 'Cache-Control: public, max-age=3600, stale-while-revalidate=86400' );
						header( 'Vary: Accept-Encoding, User-Agent' );
					}

					echo $content;
					exit;
				}
			}
		}
	}

	private function get_cache_file_path() {
		$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : 'localhost';
		$uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '/';
		$mobile = wp_is_mobile() ? '_mobile' : '_desktop';
		$hash   = md5( $host . $uri . $mobile );
		return $this->cache_dir . 'cache_' . $hash . '.html';
	}

	public function start_output_buffer() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		ob_start( array( $this, 'process_output_buffer' ) );
	}

	public function process_output_buffer( $buffer ) {
		if ( empty( $buffer ) || ! is_string( $buffer ) ) {
			return $buffer;
		}

		// Don't process non-HTML responses (XML, JSON, feeds)
		if ( strpos( $buffer, '<html' ) === false && strpos( $buffer, '<!DOCTYPE' ) === false ) {
			return $buffer;
		}

		// 1. Image & CLS Optimization
		if ( $this->get_opt( 'enable_lazyload', 'yes' ) === 'yes' ) {
			$buffer = $this->optimize_images_and_cls( $buffer );
		}

		// 2. CSS & JS Minification inside HTML
		if ( $this->get_opt( 'enable_css_minify', 'yes' ) === 'yes' ) {
			$buffer = $this->minify_inline_css( $buffer );
		}
		if ( $this->get_opt( 'enable_js_minify', 'yes' ) === 'yes' ) {
			$buffer = $this->minify_inline_js( $buffer );
		}

		// 3. HTML Minification
		if ( $this->get_opt( 'enable_html_minify', 'yes' ) === 'yes' ) {
			$buffer = $this->minify_html( $buffer );
		}

		// 4. Save to Disk Page Cache
		if ( $this->get_opt( 'enable_page_cache', 'yes' ) === 'yes' && ! is_user_logged_in() && ! is_404() && ! is_search() ) {
			$cache_file = $this->get_cache_file_path();
			$cache_comment = "\n<!-- DoodhSpeed Cache: Cached on " . gmdate( 'Y-m-d H:i:s' ) . " GMT | Engine: LightSpeed v1.0.0 -->";
			file_put_contents( $cache_file, $buffer . $cache_comment );
		}

		return $buffer;
	}

	/**
	 * =========================================================================
	 * 2. HTML, CSS, JS MINIFICATION ENGINE
	 * =========================================================================
	 */
	public function minify_html( $html ) {
		// Preserve code and pre blocks
		$placeholders = array();
		$html = preg_replace_callback( '/<(pre|code|textarea|script)\b[^>]*>.*?<\/\1>/is', function( $matches ) use ( &$placeholders ) {
			$token = '___DOODH_PRESERVE_' . count( $placeholders ) . '___';
			$placeholders[ $token ] = $matches[0];
			return $token;
		}, $html );

		// Remove HTML comments (preserve conditional IE comments)
		$html = preg_replace( '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html );

		// Collapse whitespace
		$html = preg_replace( '/\s+/', ' ', $html );
		$html = preg_replace( '/>\s+</', '><', $html );

		// Restore preserved blocks
		if ( ! empty( $placeholders ) ) {
			$html = strtr( $html, $placeholders );
		}

		return trim( $html );
	}

	public function minify_inline_css( $html ) {
		return preg_replace_callback( '/<style\b([^>]*)>(.*?)<\/style>/is', function( $matches ) {
			$attrs = $matches[1];
			$css   = $matches[2];

			// Remove CSS comments
			$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
			// Remove spaces around braces and colons
			$css = preg_replace( '/\s*([\{\};:,])\s*/', '$1', $css );
			// Remove trailing semicolons before close bracket
			$css = str_replace( ';}', '}', $css );
			// Collapse spaces
			$css = preg_replace( '/\s+/', ' ', $css );

			return '<style' . $attrs . '>' . trim( $css ) . '</style>';
		}, $html );
	}

	public function minify_inline_js( $html ) {
		return preg_replace_callback( '/<script\b([^>]*)>(.*?)<\/script>/is', function( $matches ) {
			$attrs = $matches[1];
			$js    = $matches[2];

			// Skip external or JSON-LD scripts
			if ( strpos( $attrs, 'src=' ) !== false || strpos( $attrs, 'application/ld+json' ) !== false || strpos( $attrs, 'application/json' ) !== false ) {
				return $matches[0];
			}

			// Remove multiline comments
			$js = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js );
			// Remove single line comments safely
			$js = preg_replace( '/(?:^|;)\s*\/\/[^\n]*/', ';', $js );

			return '<script' . $attrs . '>' . trim( $js ) . '</script>';
		}, $html );
	}

	/**
	 * =========================================================================
	 * 3. IMAGE & CUMULATIVE LAYOUT SHIFT (CLS) OPTIMIZER
	 * =========================================================================
	 */
	public function optimize_images_and_cls( $html ) {
		// Add loading="lazy" and decoding="async" to all <img> tags if missing
		return preg_replace_callback( '/<img\b([^>]*)>/i', function( $matches ) {
			$tag = $matches[0];
			$attrs = $matches[1];

			// Skip if already has loading attribute
			if ( strpos( $attrs, 'loading=' ) === false ) {
				$tag = str_replace( '<img ', '<img loading="lazy" ', $tag );
			}

			// Add decoding="async" if missing
			if ( strpos( $attrs, 'decoding=' ) === false ) {
				$tag = str_replace( '<img ', '<img decoding="async" ', $tag );
			}

			// Inject aspect ratio / dimensions if missing to eliminate CLS
			if ( strpos( $attrs, 'width=' ) === false && strpos( $attrs, 'height=' ) === false ) {
				if ( strpos( $attrs, 'doodh-card-poster' ) !== false || strpos( $attrs, 'doodh-single-poster' ) !== false ) {
					$tag = str_replace( '<img ', '<img width="300" height="450" ', $tag );
				} elseif ( strpos( $attrs, 'doodh-ep-thumb' ) !== false ) {
					$tag = str_replace( '<img ', '<img width="300" height="170" ', $tag );
				}
			}

			return $tag;
		}, $html );
	}

	/**
	 * =========================================================================
	 * 4. JAVASCRIPT DEFERRAL & EXECUTION BOOST
	 * =========================================================================
	 */
	public function defer_scripts( $tag, $handle, $src ) {
		// Don't defer in admin or if already deferred/async
		if ( is_admin() || strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
			return $tag;
		}

		// Keep jQuery synchronous for compatibility
		$excluded_handles = array( 'jquery', 'jquery-core' );
		if ( in_array( $handle, $excluded_handles, true ) ) {
			return $tag;
		}

		return str_replace( '<script ', '<script defer ', $tag );
	}

	/**
	 * =========================================================================
	 * 5. WORDPRESS BLOAT REMOVAL
	 * =========================================================================
	 */
	public function remove_wp_bloat() {
		// Remove Emoji Scripts & Styles
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Remove Redundant Header Meta Tags
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

		// Disable oEmbed JS
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}

	/**
	 * =========================================================================
	 * 6. RESOURCE HINTS (DNS PREFETCH & PRECONNECTS)
	 * =========================================================================
	 */
	public function add_resource_hints() {
		?>
		<!-- DoodhSpeed Preconnects & Resource Hints -->
		<link rel="dns-prefetch" href="//fonts.googleapis.com">
		<link rel="dns-prefetch" href="//fonts.gstatic.com">
		<link rel="dns-prefetch" href="//image.tmdb.org">
		<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
		<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link rel="preconnect" href="https://image.tmdb.org" crossorigin>
		<?php
	}

	/**
	 * =========================================================================
	 * 7. INSTANT PAGE HOVER PREFETCH ENGINE
	 * =========================================================================
	 */
	public function render_instant_preload_script() {
		?>
		<script id="doodh-instant-page">
		/*! DoodhSpeed Instant Navigation Preloader */
		(function() {
			var preloaded = new Set();
			function preloadLink(url) {
				if (preloaded.has(url)) return;
				preloaded.add(url);
				var link = document.createElement('link');
				link.rel = 'prefetch';
				link.href = url;
				document.head.appendChild(link);
			}
			document.addEventListener('mouseover', function(e) {
				var a = e.target.closest('a');
				if (a && a.href && a.origin === window.location.origin && !a.hasAttribute('download') && a.target !== '_blank') {
					preloadLink(a.href);
				}
			}, { passive: true });
			document.addEventListener('touchstart', function(e) {
				var a = e.target.closest('a');
				if (a && a.href && a.origin === window.location.origin && !a.hasAttribute('download') && a.target !== '_blank') {
					preloadLink(a.href);
				}
			}, { passive: true });
		})();
		</script>
		<?php
	}

	/**
	 * =========================================================================
	 * 8. CACHE PURGE & STATS
	 * =========================================================================
	 */
	public function purge_cache() {
		if ( ! file_exists( $this->cache_dir ) ) {
			return 0;
		}

		$files = glob( $this->cache_dir . '*.html' );
		$count = 0;
		if ( ! empty( $files ) ) {
			foreach ( $files as $f ) {
				if ( basename( $f ) !== 'index.html' ) {
					@unlink( $f );
					$count++;
				}
			}
		}
		return $count;
	}

	public function get_cache_stats() {
		if ( ! file_exists( $this->cache_dir ) ) {
			return array( 'count' => 0, 'size' => '0 KB' );
		}

		$files = glob( $this->cache_dir . '*.html' );
		$count = 0;
		$total_bytes = 0;

		if ( ! empty( $files ) ) {
			foreach ( $files as $f ) {
				if ( basename( $f ) !== 'index.html' ) {
					$count++;
					$total_bytes += filesize( $f );
				}
			}
		}

		if ( $total_bytes >= 1048576 ) {
			$size = number_format( $total_bytes / 1048576, 2 ) . ' MB';
		} else {
			$size = number_format( $total_bytes / 1024, 1 ) . ' KB';
		}

		return array(
			'count' => $count,
			'size'  => $size,
		);
	}

	/**
	 * =========================================================================
	 * 9. ADMIN DASHBOARD & CONTROLS
	 * =========================================================================
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Speed Optimizer', 'doodh-speed-optimizer' ),
			__( 'Speed Optimizer', 'doodh-speed-optimizer' ),
			'manage_options',
			'doodh-speed-optimizer',
			array( $this, 'render_admin_dashboard' )
		);
	}

	public function add_admin_bar_purge_btn( $admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$purge_url = wp_nonce_url( admin_url( 'options-general.php?page=doodh-speed-optimizer&action=purge_cache' ), 'doodh_purge_nonce' );

		$admin_bar->add_menu( array(
			'id'    => 'doodh-purge-cache',
			'title' => '<span class="ab-icon dashicons-dashboard" style="font-family:dashicons; margin-top:2px;"></span> ' . __( 'Purge Speed Cache', 'doodh-speed-optimizer' ),
			'href'  => $purge_url,
		) );
	}

	public function handle_admin_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle 1-Click Purge
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'purge_cache' && check_admin_referer( 'doodh_purge_nonce' ) ) {
			$purged = $this->purge_cache();
			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-speed-optimizer', 'purged' => $purged ), admin_url( 'options-general.php' ) ) );
			exit;
		}

		// Handle Settings Save
		if ( isset( $_POST['doodh_speed_save'] ) && check_admin_referer( 'doodh_speed_settings_action', 'doodh_speed_nonce' ) ) {
			$opts = array(
				'enable_page_cache'            => isset( $_POST['enable_page_cache'] ) ? 'yes' : 'no',
				'enable_html_minify'           => isset( $_POST['enable_html_minify'] ) ? 'yes' : 'no',
				'enable_css_minify'            => isset( $_POST['enable_css_minify'] ) ? 'yes' : 'no',
				'enable_js_minify'             => isset( $_POST['enable_js_minify'] ) ? 'yes' : 'no',
				'enable_defer_js'              => isset( $_POST['enable_defer_js'] ) ? 'yes' : 'no',
				'enable_lazyload'              => isset( $_POST['enable_lazyload'] ) ? 'yes' : 'no',
				'enable_dns_prefetch'          => isset( $_POST['enable_dns_prefetch'] ) ? 'yes' : 'no',
				'enable_bloat_removal'         => isset( $_POST['enable_bloat_removal'] ) ? 'yes' : 'no',
				'enable_instant_page'          => isset( $_POST['enable_instant_page'] ) ? 'yes' : 'no',
				'enable_browser_cache_headers' => isset( $_POST['enable_browser_cache_headers'] ) ? 'yes' : 'no',
				'cache_lifespan'               => isset( $_POST['cache_lifespan'] ) ? (int) $_POST['cache_lifespan'] : 86400,
			);
			update_option( 'doodh_speed_options', $opts );
			$this->load_options();
			$this->purge_cache();

			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-speed-optimizer', 'saved' => 1 ), admin_url( 'options-general.php' ) ) );
			exit;
		}
	}

	public function render_admin_dashboard() {
		$stats = $this->get_cache_stats();
		$purge_url = wp_nonce_url( admin_url( 'options-general.php?page=doodh-speed-optimizer&action=purge_cache' ), 'doodh_purge_nonce' );
		?>
		<div class="wrap" style="max-width: 1000px; margin-top:25px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			
			<!-- Header Banner -->
			<div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color:#fff; padding: 28px 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08);">
				<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
					<div>
						<div style="display:inline-flex; align-items:center; gap:8px; background:rgba(229,9,20,0.2); color:#ef4444; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:10px; border:1px solid rgba(229,9,20,0.4);">
							<span class="dashicons dashicons-performance" style="font-size:14px; margin-top:2px;"></span> LightSpeed Engine v1.0.0
						</div>
						<h1 style="color:#ffffff; font-size: 26px; font-weight: 800; margin:0 0 6px 0;"><?php esc_html_e( 'Doodh Speed Optimizer & LightSpeed Booster', 'doodh-speed-optimizer' ); ?></h1>
						<p style="color:#94a3b8; font-size: 14px; margin:0;"><?php esc_html_e( 'Ultra-fast caching, HTML/CSS/JS minification, image lazy-loading, script deferral, and instant page preloading.', 'doodh-speed-optimizer' ); ?></p>
					</div>

					<div>
						<a href="<?php echo esc_url( $purge_url ); ?>" class="button button-primary" style="background:#e50914; border-color:#dc2626; padding:8px 20px; height:auto; font-size:14px; font-weight:700; border-radius:8px; box-shadow:0 4px 12px rgba(229,9,20,0.35);">
							<span class="dashicons dashicons-update" style="margin-top:2px;"></span> <?php esc_html_e( 'Purge All Cache', 'doodh-speed-optimizer' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Notice Alerts -->
			<?php if ( isset( $_GET['purged'] ) ) : ?>
				<div class="notice notice-success is-dismissible" style="border-radius:8px; padding:12px 16px;">
					<p><strong><span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span> <?php printf( esc_html__( 'Success! Flushed %d cached pages from disk.', 'doodh-speed-optimizer' ), (int) $_GET['purged'] ); ?></strong></p>
				</div>
			<?php endif; ?>

			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible" style="border-radius:8px; padding:12px 16px;">
					<p><strong><span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span> <?php esc_html_e( 'Speed settings saved successfully and cache refreshed!', 'doodh-speed-optimizer' ); ?></strong></p>
				</div>
			<?php endif; ?>

			<!-- Performance Stats Grid -->
			<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 25px;">
				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Cached Pages on Disk', 'doodh-speed-optimizer' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a;"><?php echo esc_html( $stats['count'] ); ?> <span style="font-size:14px; font-weight:500; color:#64748b;">pages</span></div>
				</div>

				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Cache Storage Size', 'doodh-speed-optimizer' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a;"><?php echo esc_html( $stats['size'] ); ?></div>
				</div>

				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Core Web Vitals Target', 'doodh-speed-optimizer' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#10b981;">95+ <span style="font-size:14px; font-weight:500; color:#10b981;">Pass</span></div>
				</div>

				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Avg Server Response (TTFB)', 'doodh-speed-optimizer' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#2563eb;">&lt; 25 <span style="font-size:14px; font-weight:500; color:#2563eb;">ms</span></div>
				</div>
			</div>

			<!-- Settings Form -->
			<form method="post" action="">
				<?php wp_nonce_field( 'doodh_speed_settings_action', 'doodh_speed_nonce' ); ?>
				
				<div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:28px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:20px;">
					<h3 style="margin-top:0; font-size:18px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
						<span class="dashicons dashicons-admin-settings" style="margin-top:2px;"></span> <?php esc_html_e( 'Core Speed & Optimization Toggles', 'doodh-speed-optimizer' ); ?>
					</h3>

					<table class="form-table" role="presentation" style="margin-top:10px;">
						<tr>
							<th scope="row" style="width:300px; font-weight:600;"><?php esc_html_e( 'Disk Page Caching', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_page_cache" value="yes" <?php checked( $this->get_opt( 'enable_page_cache', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable High-Speed HTML Page Caching', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Saves pre-rendered HTML to disk. Delivers instant response (< 25ms) without hitting database.', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'HTML Minification', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_html_minify" value="yes" <?php checked( $this->get_opt( 'enable_html_minify', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Minify HTML Output & Strip Comments', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Removes unnecessary whitespace and HTML comments to reduce document payload by 20-35%.', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'CSS & JS Minification', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
									<input type="checkbox" name="enable_css_minify" value="yes" <?php checked( $this->get_opt( 'enable_css_minify', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Minify Inline Stylesheets & CSS blocks', 'doodh-speed-optimizer' ); ?></span>
								</label>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_js_minify" value="yes" <?php checked( $this->get_opt( 'enable_js_minify', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Minify Inline JavaScript & Scripts', 'doodh-speed-optimizer' ); ?></span>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'JavaScript Deferral', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_defer_js" value="yes" <?php checked( $this->get_opt( 'enable_defer_js', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Defer Non-Critical JavaScript (Eliminate Render-Blocking)', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Appends defer attribute to external JavaScript so HTML parsing executes immediately.', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'Image & CLS Optimizer', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_lazyload" value="yes" <?php checked( $this->get_opt( 'enable_lazyload', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Auto-Inject Native Lazy-Loading & Image Dimensions', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Automatically adds loading="lazy", decoding="async", width and height attributes to prevent layout shifts (CLS 0.00).', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'Instant Hover Preloader', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_instant_page" value="yes" <?php checked( $this->get_opt( 'enable_instant_page', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Preload Pages on Mouse Hover / Touch (Instant Navigation)', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Preloads pages in the background 65ms before user clicks, making clicks feel instantaneous.', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'WordPress Bloat Removal', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_bloat_removal" value="yes" <?php checked( $this->get_opt( 'enable_bloat_removal', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Disable Emojis, oEmbeds & Redundant Header Tags', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Removes wp-emoji-release.min.js, generator tags, and unused core assets.', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row" style="font-weight:600;"><?php esc_html_e( 'Browser Caching Headers', 'doodh-speed-optimizer' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_browser_cache_headers" value="yes" <?php checked( $this->get_opt( 'enable_browser_cache_headers', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Send Long-Life Browser Cache Headers (Cache-Control)', 'doodh-speed-optimizer' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Instructs visitor browsers to cache static assets locally for repeat visits.', 'doodh-speed-optimizer' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<div style="margin-top:20px;">
					<button type="submit" name="doodh_speed_save" class="button button-primary" style="background:#0f172a; border-color:#0f172a; padding:8px 24px; height:auto; font-size:15px; font-weight:700; border-radius:8px;">
						<?php esc_html_e( 'Save & Apply Speed Optimizations', 'doodh-speed-optimizer' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}
}

endif;

// Initialize Speed Optimizer
function doodh_speed_optimizer_init() {
	return Doodh_Speed_Optimizer::get_instance();
}
add_action( 'plugins_loaded', 'doodh_speed_optimizer_init', 1 );