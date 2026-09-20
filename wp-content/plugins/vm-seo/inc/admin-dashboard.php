<?php
/**
 * VM SEO - Comprehensive Yoast-Grade Admin Settings Dashboard
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Admin_Dashboard {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_seo_menu_pages' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_save_settings' ) );
	}

	public static function add_seo_menu_pages() {
		add_menu_page(
			__( 'VM SEO', 'vm-seo' ),
			__( 'VM SEO', 'vm-seo' ),
			'manage_options',
			'vm-seo',
			array( __CLASS__, 'render_master_dashboard' ),
			'dashicons-chart-area',
			80
		);

		add_submenu_page(
			'vm-seo',
			__( 'Dashboard & Health', 'vm-seo' ),
			__( 'Dashboard', 'vm-seo' ),
			'manage_options',
			'vm-seo',
			array( __CLASS__, 'render_master_dashboard' )
		);

		add_submenu_page(
			'vm-seo',
			__( 'Search Appearance', 'vm-seo' ),
			__( 'Search Appearance', 'vm-seo' ),
			'manage_options',
			'vm-seo-appearance',
			array( __CLASS__, 'render_appearance_page' )
		);

		add_submenu_page(
			'vm-seo',
			__( 'Social Media', 'vm-seo' ),
			__( 'Social Media', 'vm-seo' ),
			'manage_options',
			'vm-seo-social',
			array( __CLASS__, 'render_social_page' )
		);

		add_submenu_page(
			'vm-seo',
			__( 'XML Sitemaps', 'vm-seo' ),
			__( 'XML Sitemaps', 'vm-seo' ),
			'manage_options',
			'vm-seo-sitemaps',
			array( __CLASS__, 'render_sitemaps_page' )
		);

		add_submenu_page(
			'vm-seo',
			__( 'Webmaster Tools', 'vm-seo' ),
			__( 'Webmaster Tools', 'vm-seo' ),
			'manage_options',
			'vm-seo-webmaster',
			array( __CLASS__, 'render_webmaster_page' )
		);

		add_submenu_page(
			'vm-seo',
			__( 'Bulk SEO Editor', 'vm-seo' ),
			__( 'Bulk Editor', 'vm-seo' ),
			'manage_options',
			'vm-seo-bulk',
			array( 'VM_SEO_Bulk_Editor', 'render_page' )
		);

		add_submenu_page(
			'vm-seo',
			__( 'Tools & Yoast Importer', 'vm-seo' ),
			__( 'Tools & Importers', 'vm-seo' ),
			'manage_options',
			'vm-seo-tools',
			array( __CLASS__, 'render_tools_page' )
		);
	}

	public static function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Save General Features
		if ( isset( $_POST['vm_seo_save_general'] ) && check_admin_referer( 'vm_seo_general_action', 'vm_seo_nonce' ) ) {
			$options = get_option( 'vm_seo_options', array() );
			$options['enable_json_ld']     = isset( $_POST['enable_json_ld'] ) ? 'yes' : 'no';
			$options['enable_breadcrumbs'] = isset( $_POST['enable_breadcrumbs'] ) ? 'yes' : 'no';
			$options['enable_sitemaps']    = isset( $_POST['enable_sitemaps'] ) ? 'yes' : 'no';
			$options['enable_opengraph']   = isset( $_POST['enable_opengraph'] ) ? 'yes' : 'no';
			update_option( 'vm_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'vm-seo', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Search Appearance
		if ( isset( $_POST['vm_seo_save_appearance'] ) && check_admin_referer( 'vm_seo_appearance_action', 'vm_seo_nonce' ) ) {
			$options = get_option( 'vm_seo_options', array() );
			$options['title_separator']        = sanitize_text_field( $_POST['title_separator'] );
			$options['site_title_template']    = sanitize_text_field( $_POST['site_title_template'] );
			$options['home_meta_desc']         = sanitize_textarea_field( $_POST['home_meta_desc'] );

			// Title Templates
			$options['movies_title_template']  = sanitize_text_field( $_POST['movies_title_template'] );
			$options['tvshows_title_template'] = sanitize_text_field( $_POST['tvshows_title_template'] );
			$options['episodes_title_template']= sanitize_text_field( $_POST['episodes_title_template'] );
			$options['posts_title_template']   = sanitize_text_field( $_POST['posts_title_template'] );
			$options['pages_title_template']   = sanitize_text_field( $_POST['pages_title_template'] );
			$options['tax_title_template']     = sanitize_text_field( $_POST['tax_title_template'] );
			$options['cast_title_template']    = sanitize_text_field( $_POST['cast_title_template'] ?? '' );
			$options['director_title_template']= sanitize_text_field( $_POST['director_title_template'] ?? '' );
			$options['quality_title_template'] = sanitize_text_field( $_POST['quality_title_template'] ?? '' );

			// Meta Description Templates
			$options['movies_desc_template']   = sanitize_textarea_field( $_POST['movies_desc_template'] ?? '' );
			$options['tvshows_desc_template']  = sanitize_textarea_field( $_POST['tvshows_desc_template'] ?? '' );
			$options['episodes_desc_template'] = sanitize_textarea_field( $_POST['episodes_desc_template'] ?? '' );
			$options['posts_desc_template']    = sanitize_textarea_field( $_POST['posts_desc_template'] ?? '' );
			$options['pages_desc_template']    = sanitize_textarea_field( $_POST['pages_desc_template'] ?? '' );
			$options['tax_desc_template']      = sanitize_textarea_field( $_POST['tax_desc_template'] ?? '' );
			$options['cast_desc_template']     = sanitize_textarea_field( $_POST['cast_desc_template'] ?? '' );
			$options['director_desc_template'] = sanitize_textarea_field( $_POST['director_desc_template'] ?? '' );
			$options['quality_desc_template']  = sanitize_textarea_field( $_POST['quality_desc_template'] ?? '' );

			update_option( 'vm_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'vm-seo-appearance', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Social
		if ( isset( $_POST['vm_seo_save_social'] ) && check_admin_referer( 'vm_seo_social_action', 'vm_seo_nonce' ) ) {
			$options = get_option( 'vm_seo_options', array() );
			$options['twitter_handle']    = sanitize_text_field( $_POST['twitter_handle'] );
			$options['twitter_card_type'] = sanitize_text_field( $_POST['twitter_card_type'] );
			$options['fb_app_id']         = sanitize_text_field( $_POST['fb_app_id'] ?? '' );
			$options['og_default_image']  = esc_url_raw( $_POST['og_default_image'] );

			update_option( 'vm_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'vm-seo-social', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Webmaster
		if ( isset( $_POST['vm_seo_save_webmaster'] ) && check_admin_referer( 'vm_seo_webmaster_action', 'vm_seo_nonce' ) ) {
			$options = get_option( 'vm_seo_options', array() );
			$options['google_verify']    = sanitize_text_field( $_POST['google_verify'] );
			$options['bing_verify']      = sanitize_text_field( $_POST['bing_verify'] );
			$options['yandex_verify']    = sanitize_text_field( $_POST['yandex_verify'] );
			$options['pinterest_verify'] = sanitize_text_field( $_POST['pinterest_verify'] );
			$options['baidu_verify']     = sanitize_text_field( $_POST['baidu_verify'] ?? '' );

			update_option( 'vm_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'vm-seo-webmaster', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Sitemaps
		if ( isset( $_POST['vm_seo_save_sitemaps'] ) && check_admin_referer( 'vm_seo_sitemaps_action', 'vm_seo_nonce' ) ) {
			$options = get_option( 'vm_seo_options', array() );
			$options['enable_sitemaps']        = isset( $_POST['enable_sitemaps'] ) ? 'yes' : 'no';
			$options['sitemap_post_types']     = isset( $_POST['sitemap_post_types'] ) ? array_map( 'sanitize_text_field', (array) $_POST['sitemap_post_types'] ) : array();
			$options['sitemap_taxonomies']     = isset( $_POST['sitemap_taxonomies'] ) ? array_map( 'sanitize_text_field', (array) $_POST['sitemap_taxonomies'] ) : array();
			$options['sitemap_include_images'] = isset( $_POST['sitemap_include_images'] ) ? 'yes' : 'no';
			$options['sitemap_exclude_ids']    = sanitize_text_field( $_POST['sitemap_exclude_ids'] ?? '' );
			$options['sitemap_max_entries']    = max( 10, intval( $_POST['sitemap_max_entries'] ?? 1000 ) );

			update_option( 'vm_seo_options', $options );
			VM_SEO_Sitemap::add_rewrite_rules();
			flush_rewrite_rules( false );
			wp_safe_redirect( add_query_arg( array( 'page' => 'vm-seo-sitemaps', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Robots
		if ( isset( $_POST['vm_seo_save_robots'] ) && check_admin_referer( 'vm_seo_robots_action', 'vm_seo_nonce' ) ) {
			$robots_content = wp_unslash( $_POST['robots_content'] );
			file_put_contents( ABSPATH . 'robots.txt', $robots_content );
			wp_safe_redirect( add_query_arg( array( 'page' => 'vm-seo-tools', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	/**
	 * 1. Render Master General Dashboard
	 */
	public static function render_master_dashboard() {
		$opts = VM_SEO_Master::get_instance()->options;
		$sources = VM_SEO_Importer::detect_sources();

		global $wpdb;
		$total_movies   = (int) wp_count_posts( 'movies' )->publish;
		$total_tv       = (int) wp_count_posts( 'tvshows' )->publish;
		$total_posts    = (int) wp_count_posts( 'post' )->publish;
		$kw_posts_count = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_vm_focus_keyword' AND meta_value != ''" );
		$good_seo_count = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_vm_seo_score' AND CAST(meta_value AS UNSIGNED) >= 80" );
		?>
		<div class="wrap vm-seo-admin-page">
			<div class="vm-admin-hero">
				<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
					<div>
						<div class="vm-badge-pill"><span class="dashicons dashicons-shield"></span> Yoast-Grade SEO Suite v2.0.0</div>
						<h1><?php esc_html_e( 'VM SEO Master Dashboard', 'vm-seo' ); ?></h1>
						<p><?php esc_html_e( 'Professional search engine optimization, live SERP analysis, XML sitemaps, rich JSON-LD schema, and automated social metadata.', 'vm-seo' ); ?></p>
					</div>
					<div style="display:flex; gap:10px;">
						<a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" class="button button-secondary" style="color:#2563eb; font-weight:700;">
							<span class="dashicons dashicons-networking" style="vertical-align:middle;"></span> <?php esc_html_e( 'View XML Sitemap', 'vm-seo' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=vm-seo-bulk' ) ); ?>" class="button button-primary" style="background:#e50914; border-color:#dc2626; font-weight:700;">
							<span class="dashicons dashicons-edit-large" style="vertical-align:middle;"></span> <?php esc_html_e( 'Bulk SEO Editor', 'vm-seo' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Quick Notice if Yoast SEO is detected -->
			<?php if ( $sources['yoast_count'] > 0 ) : ?>
				<div class="notice notice-info" style="border-left-color:#2563eb; padding:15px 20px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
					<div>
						<strong style="font-size:14px; color:#1e40af;"><span class="dashicons dashicons-migrate" style="vertical-align:middle;"></span> <?php esc_html_e( 'Yoast SEO Data Detected!', 'vm-seo' ); ?></strong>
						<p style="margin:4px 0 0 0; color:#334155;">
							<?php printf( esc_html__( 'Found %d posts with Yoast SEO metadata in your database. You can migrate them all into VM SEO with 1 click without losing any rankings.', 'vm-seo' ), $sources['yoast_count'] ); ?>
						</p>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=vm-seo-tools' ) ); ?>" class="button button-primary" style="background:#2563eb; font-weight:700;">
						<?php esc_html_e( 'Migrate from Yoast SEO &rarr;', 'vm-seo' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<!-- SEO Health & Stats Metrics Grid -->
			<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin:20px 0;">
				<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
					<div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e( 'Total Movies Indexed', 'vm-seo' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a; margin:6px 0;"><?php echo esc_html( number_format( $total_movies ) ); ?></div>
					<div style="font-size:12px; color:#10b981; font-weight:600;"><span class="dashicons dashicons-yes"></span> Included in Sitemap</div>
				</div>

				<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
					<div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e( 'TV Shows Indexed', 'vm-seo' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a; margin:6px 0;"><?php echo esc_html( number_format( $total_tv ) ); ?></div>
					<div style="font-size:12px; color:#10b981; font-weight:600;"><span class="dashicons dashicons-yes"></span> Included in Sitemap</div>
				</div>

				<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
					<div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e( 'Focus Keyword Optimized', 'vm-seo' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#2563eb; margin:6px 0;"><?php echo esc_html( number_format( $kw_posts_count ) ); ?></div>
					<div style="font-size:12px; color:#64748b;"><?php esc_html_e( 'Target keyphrase configured', 'vm-seo' ); ?></div>
				</div>

				<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
					<div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e( 'High SEO Score (80+)', 'vm-seo' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#10b981; margin:6px 0;"><?php echo esc_html( number_format( $good_seo_count ) ); ?></div>
					<div style="font-size:12px; color:#10b981; font-weight:600;"><span class="dashicons dashicons-awards"></span> 🟢 Green Traffic Light</div>
				</div>
			</div>

			<!-- Core Features Toggle Form -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:25px; margin-top:20px;">
				<h3 style="margin-top:0; font-size:16px; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
					<span class="dashicons dashicons-admin-settings" style="color:#2563eb;"></span> <?php esc_html_e( 'Active SEO Features (Yoast Suite)', 'vm-seo' ); ?>
				</h3>
				
				<form method="post" action="">
					<?php wp_nonce_field( 'vm_seo_general_action', 'vm_seo_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'XML Sitemaps Generator', 'vm-seo' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="enable_sitemaps" value="yes" <?php checked( $opts['enable_sitemaps'] ?? 'yes', 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable native dynamic XML sitemaps (/sitemap.xml)', 'vm-seo' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Generates high-speed XML sitemaps with image indexing and automatic Google/Bing pinging.', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Schema.org JSON-LD Graph', 'vm-seo' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="enable_json_ld" value="yes" <?php checked( $opts['enable_json_ld'] ?? 'yes', 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable Rich Snippets & JSON-LD Structured Graph', 'vm-seo' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Outputs structured Movie, TVSeries, WebSite, and BreadcrumbList schemas for Google Rich Results.', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Social OpenGraph & Twitter', 'vm-seo' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="enable_opengraph" value="yes" <?php checked( $opts['enable_opengraph'] ?? 'yes', 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable OpenGraph and Twitter Cards metadata', 'vm-seo' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Ensures beautiful rich previews when movies and shows are shared on Facebook, WhatsApp, X (Twitter), and Pinterest.', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Breadcrumbs System', 'vm-seo' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="enable_breadcrumbs" value="yes" <?php checked( $opts['enable_breadcrumbs'] ?? 'yes', 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable Breadcrumbs navigation with Microdata', 'vm-seo' ); ?></strong>
								</label>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" name="vm_seo_save_general" class="button button-primary" style="background:#2563eb; font-weight:700;">
							<?php esc_html_e( 'Save Feature Settings', 'vm-seo' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * 2. Render Search Appearance Page
	 */
	public static function render_appearance_page() {
		$opts = VM_SEO_Master::get_instance()->options;
		?>
		<div class="wrap vm-seo-admin-page">
			<h1><span class="dashicons dashicons-search" style="color:#e50914;"></span> <?php esc_html_e( 'Search Appearance — Title & Meta Description Templates', 'vm-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Search appearance settings saved successfully!', 'vm-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:980px; margin-top:20px;">
				<?php wp_nonce_field( 'vm_seo_appearance_action', 'vm_seo_nonce' ); ?>

				<!-- Global Separator & Homepage -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:16px; display:flex; align-items:center; gap:8px; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
						<span class="dashicons dashicons-admin-site-alt3" style="color:#2563eb;"></span> <?php esc_html_e( 'Global Separator & Homepage SEO', 'vm-seo' ); ?>
					</h3>
					
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Title Separator', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="title_separator" value="<?php echo esc_attr( $opts['title_separator'] ); ?>" style="width:70px; font-weight:700; text-align:center; font-size:16px;">
								<p class="description"><?php esc_html_e( 'Separator symbol placed between title elements (e.g. |, -, &bull;, &raquo;)', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Homepage Title Template', 'vm-seo' ); ?></th>
							<td>
								<div class="vm-var-pills-bar" style="margin-bottom:6px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
									<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
									<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
									<button type="button" class="vm-var-pill-btn" data-var="%%tagline%%">+ Tagline</button>
									<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
								</div>
								<input type="text" name="site_title_template" value="<?php echo esc_attr( $opts['site_title_template'] ); ?>" class="large-text">
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Homepage Meta Description', 'vm-seo' ); ?></th>
							<td>
								<textarea name="home_meta_desc" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Enter the meta description for your homepage search results...', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['home_meta_desc'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Displayed in Google SERP results for your front page. Recommended 140–160 characters.', 'vm-seo' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<h2 style="font-size:18px; margin:28px 0 8px 0; color:#0f172a; font-weight:700; display:flex; align-items:center; gap:8px;">
					<span class="dashicons dashicons-admin-post" style="color:#e50914;"></span> <?php esc_html_e( 'Content Types Title & Meta Description Templates', 'vm-seo' ); ?>
				</h2>
				<p style="color:#64748b; margin-bottom:20px;">
					<?php esc_html_e( 'These templates automatically generate high-converting SEO Titles and Meta Descriptions when a single post does not have a custom description manually set in the editor.', 'vm-seo' ); ?>
				</p>

				<!-- 1. MOVIES TEMPLATE -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>🎬 <?php esc_html_e( 'Movies (Post Type: movies)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:12px; font-weight:600;">Custom Post Type</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Title</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%year%%">+ Year</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%director%%" style="background:#fef2f2; border-color:#fecaca; color:#dc2626; font-weight:700;">+ Director</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%cast%%" style="background:#fef2f2; border-color:#fecaca; color:#dc2626; font-weight:700;">+ Cast/Actors</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%genres%%">+ Genre</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%quality%%">+ Quality</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%rating%%">+ Rating</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%excerpt%%">+ Excerpt/Plot</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="movies_title_template" value="<?php echo esc_attr( $opts['movies_title_template'] ); ?>" class="large-text" placeholder="%%title%% (%%year%%) Full Movie HD Stream %%sep%% %%sitename%%">
								<p class="description"><?php esc_html_e( 'Example: %%title%% (%%year%%) Directed by %%director%% Starring %%cast%% %%sep%% %%sitename%%', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="movies_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Watch %%title%% (%%year%%) directed by %%director%% starring %%cast%% online in HD with subtitles. %%excerpt%% Stream in 1080p on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['movies_desc_template'] ?? '' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Fallback meta description for movie pages when no manual description is entered. Automatically replaces %%director%% and %%cast%%.', 'vm-seo' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<!-- 2. TV SHOWS TEMPLATE -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>📺 <?php esc_html_e( 'TV Shows (Post Type: tvshows)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:12px; font-weight:600;">Custom Post Type</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Title</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%year%%">+ Year</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%director%%" style="background:#fef2f2; border-color:#fecaca; color:#dc2626; font-weight:700;">+ Creator/Director</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%cast%%" style="background:#fef2f2; border-color:#fecaca; color:#dc2626; font-weight:700;">+ Cast/Actors</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%genres%%">+ Genre</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%rating%%">+ Rating</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%excerpt%%">+ Excerpt/Plot</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="tvshows_title_template" value="<?php echo esc_attr( $opts['tvshows_title_template'] ); ?>" class="large-text" placeholder="%%title%% (%%year%%) TV Series HD Online %%sep%% %%sitename%%">
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="tvshows_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Stream %%title%% (%%year%%) TV series all episodes and seasons online in HD. %%excerpt%% Watch now on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['tvshows_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<!-- 3. EPISODES TEMPLATE -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>🎞️ <?php esc_html_e( 'Episodes (Post Type: episodes)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:12px; font-weight:600;">Custom Post Type</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Title</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%excerpt%%">+ Excerpt</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="episodes_title_template" value="<?php echo esc_attr( $opts['episodes_title_template'] ); ?>" class="large-text" placeholder="%%title%% %%sep%% %%sitename%%">
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="episodes_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Watch %%title%% full episode online in HD. %%excerpt%% Stream latest episodes and seasons on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['episodes_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<!-- 4. BLOG POSTS TEMPLATE -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>✍️ <?php esc_html_e( 'Blog Posts (Post Type: post)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:12px; font-weight:600;">Standard WordPress Post</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Title</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%category%%">+ Category</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%excerpt%%">+ Excerpt</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="posts_title_template" value="<?php echo esc_attr( $opts['posts_title_template'] ); ?>" class="large-text" placeholder="%%title%% %%sep%% %%sitename%%">
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="posts_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( '%%excerpt%% Read full article on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['posts_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<!-- 5. PAGES TEMPLATE -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>📄 <?php esc_html_e( 'Pages (Post Type: page)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:12px; font-weight:600;">Standard WordPress Page</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Title</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%excerpt%%">+ Excerpt</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="pages_title_template" value="<?php echo esc_attr( $opts['pages_title_template'] ); ?>" class="large-text" placeholder="%%title%% %%sep%% %%sitename%%">
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="pages_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( '%%excerpt%% Learn more on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['pages_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<h2 style="font-size:18px; margin:28px 0 8px 0; color:#0f172a; font-weight:700; display:flex; align-items:center; gap:8px;">
					<span class="dashicons dashicons-category" style="color:#2563eb;"></span> <?php esc_html_e( 'Taxonomies & Archives SEO Templates', 'vm-seo' ); ?>
				</h2>
				<p style="color:#64748b; margin-bottom:20px;">
					<?php esc_html_e( 'Configure dedicated SEO titles and meta descriptions for individual Cast & Actor profiles, Director portfolios, Qualities, and Genres archives.', 'vm-seo' ); ?>
				</p>

				<!-- 6. ACTORS & CAST (dtcast) -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>🎭 <?php esc_html_e( 'Actors & Cast (Taxonomy: dtcast)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#fef2f2; color:#b91c1c; padding:3px 8px; border-radius:12px; font-weight:600;">Actor Profile Page</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Actor Name</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%tagline%%">+ Tagline</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="cast_title_template" value="<?php echo esc_attr( $opts['cast_title_template'] ?? '' ); ?>" class="large-text" placeholder="%%title%% Movies, TV Shows & Filmography %%sep%% %%sitename%%">
								<p class="description"><?php esc_html_e( 'Example: Leonardo DiCaprio Movies, TV Shows & Filmography | ' . get_bloginfo( 'name' ), 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="cast_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Watch all movies and TV shows starring %%title%% in full HD online on %%sitename%%. Explore complete biography and filmography.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['cast_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<!-- 7. DIRECTORS & CREW (dtdirector) -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>🎬 <?php esc_html_e( 'Directors & Crew (Taxonomy: dtdirector)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#fef2f2; color:#b91c1c; padding:3px 8px; border-radius:12px; font-weight:600;">Director Portfolio Page</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Director Name</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%tagline%%">+ Tagline</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="director_title_template" value="<?php echo esc_attr( $opts['director_title_template'] ?? '' ); ?>" class="large-text" placeholder="%%title%% Directed Movies & TV Series %%sep%% %%sitename%%">
								<p class="description"><?php esc_html_e( 'Example: Christopher Nolan Directed Movies & TV Series | ' . get_bloginfo( 'name' ), 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="director_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Explore all movies and TV shows directed by %%title%% in full HD online on %%sitename%%. Browse complete filmography and latest releases.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['director_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<!-- 8. QUALITIES (dtquality) -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>💎 <?php esc_html_e( 'Qualities (Taxonomy: dtquality)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:12px; font-weight:600;">Quality Archive</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Quality Name</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="quality_title_template" value="<?php echo esc_attr( $opts['quality_title_template'] ?? '' ); ?>" class="large-text" placeholder="%%title%% Movies & TV Shows HD Stream %%sep%% %%sitename%%">
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="quality_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Watch all movies and TV series available in %%title%% quality in full HD with subtitles on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['quality_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<!-- 9. GENRES & GENERAL TAXONOMIES TEMPLATE -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
						<span>🏷️ <?php esc_html_e( 'Genres & Other Taxonomies (Genres, Release Years, Categories)', 'vm-seo' ); ?></span>
						<span style="font-size:11px; background:#eff6ff; color:#1d4ed8; padding:3px 8px; border-radius:12px; font-weight:600;">Taxonomy Archive</span>
					</h3>
					
					<div class="vm-var-pills-bar" style="margin:10px 0 12px 0; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
						<strong style="font-size:11px; color:#64748b;"><?php esc_html_e( 'Insert Tag:', 'vm-seo' ); ?></strong>
						<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Term Name</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
						<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					</div>

					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'SEO Title Template', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="tax_title_template" value="<?php echo esc_attr( $opts['tax_title_template'] ); ?>" class="large-text" placeholder="%%title%% Movies & TV Shows %%sep%% %%sitename%%">
							</td>
						</tr>
						<tr>
							<th scope="row" style="width:200px;"><?php esc_html_e( 'Meta Description Template', 'vm-seo' ); ?></th>
							<td>
								<textarea name="tax_desc_template" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Browse and watch full collection of %%title%% movies and TV series online in HD on %%sitename%%.', 'vm-seo' ); ?>"><?php echo esc_textarea( $opts['tax_desc_template'] ?? '' ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<p class="submit" style="position:sticky; bottom:15px; background:rgba(255,255,255,0.95); backdrop-filter:blur(6px); padding:12px 20px; border-radius:8px; border:1px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); z-index:99;">
					<button type="submit" name="vm_seo_save_appearance" class="button button-primary" style="background:#e50914; border-color:#dc2626; padding:6px 24px; font-weight:700; font-size:14px;">
						<?php esc_html_e( 'Save Appearance & Meta Templates', 'vm-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * 3. Render Social Media Page
	 */
	public static function render_social_page() {
		$opts = VM_SEO_Master::get_instance()->options;
		?>
		<div class="wrap vm-seo-admin-page">
			<h1><span class="dashicons dashicons-share" style="color:#2563eb;"></span> <?php esc_html_e( 'Social Media & OpenGraph Profiles', 'vm-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Social settings saved!', 'vm-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:850px; background:#fff; padding:25px 30px; border-radius:10px; border:1px solid #e2e8f0; margin-top:20px;">
				<?php wp_nonce_field( 'vm_seo_social_action', 'vm_seo_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Twitter / X Handle', 'vm-seo' ); ?></th>
						<td><input type="text" name="twitter_handle" value="<?php echo esc_attr( $opts['twitter_handle'] ); ?>" class="regular-text" placeholder="@YourBrand"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Twitter Card Type', 'vm-seo' ); ?></th>
						<td>
							<select name="twitter_card_type">
								<option value="summary_large_image" <?php selected( $opts['twitter_card_type'], 'summary_large_image' ); ?>>Summary with Large Image (Recommended for Movies/TV)</option>
								<option value="summary" <?php selected( $opts['twitter_card_type'], 'summary' ); ?>>Standard Summary</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Facebook App ID', 'vm-seo' ); ?></th>
						<td><input type="text" name="fb_app_id" value="<?php echo esc_attr( $opts['fb_app_id'] ?? '' ); ?>" class="regular-text" placeholder="1234567890"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Default Social Share Image (Fallback)', 'vm-seo' ); ?></th>
						<td>
							<input type="url" name="og_default_image" value="<?php echo esc_attr( $opts['og_default_image'] ); ?>" class="large-text" placeholder="https://example.com/banner.jpg">
							<p class="description"><?php esc_html_e( 'Used when a shared post or page does not have its own featured image/backdrop.', 'vm-seo' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="vm_seo_save_social" class="button button-primary" style="background:#2563eb; border-color:#1d4ed8; padding:6px 20px; font-weight:700;">
						<?php esc_html_e( 'Save Social Settings', 'vm-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * 4. Render XML Sitemaps Page
	 */
	public static function render_sitemaps_page() {
		$opts = VM_SEO_Master::get_instance()->options;
		$enabled_sitemaps = ( $opts['enable_sitemaps'] ?? 'yes' ) === 'yes';
		$enabled_pts      = (array) ( $opts['sitemap_post_types'] ?? array( 'movies', 'tvshows', 'episodes', 'post', 'page' ) );
		$enabled_taxs     = (array) ( $opts['sitemap_taxonomies'] ?? array( 'genres', 'release-year', 'category', 'post_tag' ) );
		$include_images   = ( $opts['sitemap_include_images'] ?? 'yes' ) === 'yes';
		$exclude_ids      = $opts['sitemap_exclude_ids'] ?? '';
		$max_entries      = (int) ( $opts['sitemap_max_entries'] ?? 1000 );

		$all_post_types = array(
			'movies'   => array( 'label' => __( 'Movies', 'vm-seo' ), 'icon' => '🎬', 'badge' => 'Movie CPT' ),
			'tvshows'  => array( 'label' => __( 'TV Shows', 'vm-seo' ), 'icon' => '📺', 'badge' => 'TV Series CPT' ),
			'episodes' => array( 'label' => __( 'Episodes', 'vm-seo' ), 'icon' => '🎞️', 'badge' => 'Episode CPT' ),
			'post'     => array( 'label' => __( 'Blog Posts', 'vm-seo' ), 'icon' => '✍️', 'badge' => 'Core Post' ),
			'page'     => array( 'label' => __( 'Pages', 'vm-seo' ), 'icon' => '📄', 'badge' => 'Core Page' ),
		);

		$all_taxonomies = array(
			'genres'       => array( 'label' => __( 'Movie & TV Genres', 'vm-seo' ), 'icon' => '🏷️' ),
			'release-year' => array( 'label' => __( 'Release Years', 'vm-seo' ), 'icon' => '📅' ),
			'category'     => array( 'label' => __( 'Blog Categories', 'vm-seo' ), 'icon' => '📁' ),
			'post_tag'     => array( 'label' => __( 'Post Tags', 'vm-seo' ), 'icon' => '🏷️' ),
			'dtcast'       => array( 'label' => __( 'Cast & Actors', 'vm-seo' ), 'icon' => '🎭' ),
			'dtdirector'   => array( 'label' => __( 'Directors', 'vm-seo' ), 'icon' => '🎬' ),
			'dtquality'    => array( 'label' => __( 'Video Quality', 'vm-seo' ), 'icon' => '📺' ),
		);
		?>
		<div class="wrap vm-seo-admin-page">
			<div class="vm-admin-hero">
				<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
					<div>
						<div class="vm-badge-pill" style="background:rgba(16,185,129,0.15); color:#059669; border-color:rgba(16,185,129,0.3);">
							<span class="dashicons dashicons-networking"></span> High-Speed XML Sitemap Engine
						</div>
						<h1><?php esc_html_e( 'XML Sitemaps Suite & Index Controls', 'vm-seo' ); ?></h1>
						<p><?php esc_html_e( 'Select exactly which content types, taxonomies, and archives are indexed and submitted to search engines.', 'vm-seo' ); ?></p>
					</div>
					<div style="display:flex; gap:10px;">
						<a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" class="button button-secondary" style="color:#059669; font-weight:700;">
							<span class="dashicons dashicons-external" style="vertical-align:middle;"></span> <?php esc_html_e( 'Open sitemap.xml', 'vm-seo' ); ?>
						</a>
						<button type="button" id="vm-btn-ping-sitemaps" class="button button-primary" style="background:#10b981; border-color:#059669; font-weight:700;">
							<span class="dashicons dashicons-rss" style="vertical-align:middle;"></span> <?php esc_html_e( 'Ping Google & Bing Now', 'vm-seo' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div id="vm-sitemap-ping-notice" style="display:none; margin:15px 0;"></div>

			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible" style="margin-top:15px;"><p><strong><?php esc_html_e( 'XML Sitemap configuration saved and rewrite rules flushed!', 'vm-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<!-- 1. Live Sitemap Overview Box -->
			<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:22px 25px; margin:20px 0; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
				<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
					<div>
						<h3 style="margin:0; font-size:16px; color:#0f172a; display:flex; align-items:center; gap:8px;">
							<span class="dashicons dashicons-admin-links" style="color:#10b981;"></span> <?php esc_html_e( 'Master Index Sitemap', 'vm-seo' ); ?>
						</h3>
						<div style="margin-top:4px; font-size:13px; color:#475569;">
							<strong>URL:</strong> <a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" style="color:#2563eb; text-decoration:underline;"><?php echo esc_url( home_url( '/sitemap.xml' ) ); ?></a>
						</div>
					</div>
					<div>
						<?php if ( $enabled_sitemaps ) : ?>
							<span style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#15803d; padding:6px 12px; border-radius:20px; font-weight:700; font-size:12px;">
								<span style="width:8px; height:8px; border-radius:50%; background:#16a34a; display:inline-block;"></span> 🟢 Active & Serving Search Engines
							</span>
						<?php else : ?>
							<span style="display:inline-flex; align-items:center; gap:6px; background:#fee2e2; color:#b91c1c; padding:6px 12px; border-radius:20px; font-weight:700; font-size:12px;">
								<span style="width:8px; height:8px; border-radius:50%; background:#dc2626; display:inline-block;"></span> 🔴 Disabled
							</span>
						<?php endif; ?>
					</div>
				</div>

				<!-- Live Data Breakdown Table -->
				<h4 style="margin:20px 0 10px 0; font-size:14px; font-weight:700; color:#334155;"><?php esc_html_e( 'Live Content & Taxonomy Sitemaps Breakdown:', 'vm-seo' ); ?></h4>
				<table class="widefat striped" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
					<thead>
						<tr style="background:#f8fafc;">
							<th style="font-weight:700;"><?php esc_html_e( 'Content Type / Taxonomy', 'vm-seo' ); ?></th>
							<th style="font-weight:700;"><?php esc_html_e( 'Direct Sitemap URL', 'vm-seo' ); ?></th>
							<th style="font-weight:700; text-align:center;"><?php esc_html_e( 'Items Count', 'vm-seo' ); ?></th>
							<th style="font-weight:700;"><?php esc_html_e( 'Last Modified', 'vm-seo' ); ?></th>
							<th style="font-weight:700; text-align:center;"><?php esc_html_e( 'Sitemap Status', 'vm-seo' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<!-- Post Types -->
						<?php foreach ( $all_post_types as $pt_key => $pt_data ) : 
							if ( ! post_type_exists( $pt_key ) ) continue;
							$count_obj  = wp_count_posts( $pt_key );
							$pub_count  = (int) ( $count_obj->publish ?? 0 );
							$is_in      = in_array( $pt_key, $enabled_pts, true ) && $enabled_sitemaps;
							$latest     = get_posts( array( 'post_type' => $pt_key, 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'modified', 'order' => 'DESC' ) );
							$last_date  = ! empty( $latest ) ? get_the_modified_date( 'Y-m-d H:i', $latest[0]->ID ) : '—';
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $pt_data['icon'] . ' ' . $pt_data['label'] ); ?></strong>
								<span style="font-size:11px; color:#64748b; margin-left:4px;">(<code><?php echo esc_html( $pt_key ); ?></code>)</span>
							</td>
							<td>
								<?php if ( $is_in && $pub_count > 0 ) : ?>
									<a href="<?php echo esc_url( home_url( "/sitemap-{$pt_key}.xml" ) ); ?>" target="_blank" style="color:#2563eb; font-weight:600;">
										/sitemap-<?php echo esc_html( $pt_key ); ?>.xml <span class="dashicons dashicons-external" style="font-size:14px; vertical-align:middle;"></span>
									</a>
								<?php else : ?>
									<span style="color:#94a3b8;">/sitemap-<?php echo esc_html( $pt_key ); ?>.xml</span>
								<?php endif; ?>
							</td>
							<td style="text-align:center; font-weight:700; color:#0f172a;"><?php echo esc_html( number_format( $pub_count ) ); ?></td>
							<td style="color:#475569; font-size:12px;"><?php echo esc_html( $last_date ); ?></td>
							<td style="text-align:center;">
								<?php if ( $is_in && $pub_count > 0 ) : ?>
									<span style="background:#dcfce7; color:#15803d; font-size:11px; padding:3px 8px; border-radius:10px; font-weight:700;">🟢 Included</span>
								<?php elseif ( ! $is_in ) : ?>
									<span style="background:#f1f5f9; color:#64748b; font-size:11px; padding:3px 8px; border-radius:10px; font-weight:600;">⚪ Excluded</span>
								<?php else : ?>
									<span style="background:#fef3c7; color:#b45309; font-size:11px; padding:3px 8px; border-radius:10px; font-weight:600;">🟡 0 Published</span>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>

						<!-- Taxonomies -->
						<?php foreach ( $all_taxonomies as $tax_key => $tax_data ) : 
							if ( ! taxonomy_exists( $tax_key ) ) continue;
							$term_count = wp_count_terms( array( 'taxonomy' => $tax_key, 'hide_empty' => true ) );
							$term_count = is_wp_error( $term_count ) ? 0 : (int) $term_count;
							$is_in_tax  = in_array( $tax_key, $enabled_taxs, true ) && $enabled_sitemaps;
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $tax_data['icon'] . ' ' . $tax_data['label'] ); ?></strong>
								<span style="font-size:11px; color:#64748b; margin-left:4px;">(<code><?php echo esc_html( $tax_key ); ?></code>)</span>
							</td>
							<td>
								<?php if ( $is_in_tax && $term_count > 0 ) : ?>
									<a href="<?php echo esc_url( home_url( "/sitemap-{$tax_key}.xml" ) ); ?>" target="_blank" style="color:#2563eb; font-weight:600;">
										/sitemap-<?php echo esc_html( $tax_key ); ?>.xml <span class="dashicons dashicons-external" style="font-size:14px; vertical-align:middle;"></span>
									</a>
								<?php else : ?>
									<span style="color:#94a3b8;">/sitemap-<?php echo esc_html( $tax_key ); ?>.xml</span>
								<?php endif; ?>
							</td>
							<td style="text-align:center; font-weight:700; color:#0f172a;"><?php echo esc_html( number_format( $term_count ) ); ?></td>
							<td style="color:#475569; font-size:12px;"><?php esc_html_e( 'Auto-updated', 'vm-seo' ); ?></td>
							<td style="text-align:center;">
								<?php if ( $is_in_tax && $term_count > 0 ) : ?>
									<span style="background:#dcfce7; color:#15803d; font-size:11px; padding:3px 8px; border-radius:10px; font-weight:700;">🟢 Included</span>
								<?php elseif ( ! $is_in_tax ) : ?>
									<span style="background:#f1f5f9; color:#64748b; font-size:11px; padding:3px 8px; border-radius:10px; font-weight:600;">⚪ Excluded</span>
								<?php else : ?>
									<span style="background:#fef3c7; color:#b45309; font-size:11px; padding:3px 8px; border-radius:10px; font-weight:600;">🟡 0 Terms</span>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- 2. Sitemap Settings Form -->
			<form method="post" action="" style="max-width:980px;">
				<?php wp_nonce_field( 'vm_seo_sitemaps_action', 'vm_seo_nonce' ); ?>

				<!-- General XML Sitemaps Control -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:16px; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px; display:flex; align-items:center; gap:8px;">
						<span class="dashicons dashicons-admin-settings" style="color:#2563eb;"></span> <?php esc_html_e( 'General Sitemap Configuration', 'vm-seo' ); ?>
					</h3>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable XML Sitemaps', 'vm-seo' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="enable_sitemaps" value="yes" <?php checked( $enabled_sitemaps, true ); ?>>
									<strong><?php esc_html_e( 'Enable native XML sitemaps functionality (/sitemap.xml)', 'vm-seo' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Turn this on to allow Google, Bing, and other crawlers to automatically index your site structure.', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Include Image Tags', 'vm-seo' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="sitemap_include_images" value="yes" <?php checked( $include_images, true ); ?>>
									<strong><?php esc_html_e( 'Include movie posters, backdrops, and featured images in sitemap', 'vm-seo' ); ?></strong>
								</label>
								<p class="description"><?php esc_html_e( 'Adds <image:image> tags to help your movie and TV show artwork rank in Google Image Search.', 'vm-seo' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Max Entries per Sitemap', 'vm-seo' ); ?></th>
							<td>
								<input type="number" name="sitemap_max_entries" value="<?php echo esc_attr( $max_entries ); ?>" min="10" max="50000" style="width:120px;">
								<p class="description"><?php esc_html_e( 'Recommended limit per sitemap page (default: 1000).', 'vm-seo' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<!-- Post Types in Sitemap -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:16px; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px; display:flex; align-items:center; gap:8px;">
						<span class="dashicons dashicons-video-alt3" style="color:#e50914;"></span> <?php esc_html_e( 'Post Types in Sitemap (Choose which show in sitemap)', 'vm-seo' ); ?>
					</h3>
					<p style="color:#64748b; margin-top:6px;"><?php esc_html_e( 'Select which post types should be indexed in your XML sitemap. Unchecked post types will be completely excluded from the sitemap.', 'vm-seo' ); ?></p>

					<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; margin-top:16px;">
						<?php foreach ( $all_post_types as $pt_key => $pt_data ) : 
							if ( ! post_type_exists( $pt_key ) ) continue;
							$count_obj = wp_count_posts( $pt_key );
							$pub_count = (int) ( $count_obj->publish ?? 0 );
							$checked   = in_array( $pt_key, $enabled_pts, true );
						?>
						<div style="border:1px solid <?php echo $checked ? '#bfdbfe' : '#e2e8f0'; ?>; background:<?php echo $checked ? '#f8fafc' : '#ffffff'; ?>; border-radius:8px; padding:14px 16px; transition:all 0.15s ease;">
							<label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
								<input type="checkbox" name="sitemap_post_types[]" value="<?php echo esc_attr( $pt_key ); ?>" <?php checked( $checked, true ); ?> style="margin-top:3px;">
								<div>
									<strong style="font-size:14px; color:#0f172a;"><?php echo esc_html( $pt_data['icon'] . ' ' . $pt_data['label'] ); ?></strong>
									<div style="font-size:12px; color:#64748b; margin-top:2px;">
										Post Type: <code><?php echo esc_html( $pt_key ); ?></code> &bull; <strong><?php echo esc_html( number_format( $pub_count ) ); ?></strong> published
									</div>
								</div>
							</label>
						</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Taxonomies in Sitemap -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:16px; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px; display:flex; align-items:center; gap:8px;">
						<span class="dashicons dashicons-category" style="color:#d97706;"></span> <?php esc_html_e( 'Taxonomies & Archives in Sitemap', 'vm-seo' ); ?>
					</h3>
					<p style="color:#64748b; margin-top:6px;"><?php esc_html_e( 'Choose which taxonomy archives should have their own dedicated XML sitemap.', 'vm-seo' ); ?></p>

					<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; margin-top:16px;">
						<?php foreach ( $all_taxonomies as $tax_key => $tax_data ) : 
							if ( ! taxonomy_exists( $tax_key ) ) continue;
							$term_count = wp_count_terms( array( 'taxonomy' => $tax_key, 'hide_empty' => true ) );
							$term_count = is_wp_error( $term_count ) ? 0 : (int) $term_count;
							$checked    = in_array( $tax_key, $enabled_taxs, true );
						?>
						<div style="border:1px solid <?php echo $checked ? '#fde68a' : '#e2e8f0'; ?>; background:<?php echo $checked ? '#fffbeb' : '#ffffff'; ?>; border-radius:8px; padding:14px 16px; transition:all 0.15s ease;">
							<label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
								<input type="checkbox" name="sitemap_taxonomies[]" value="<?php echo esc_attr( $tax_key ); ?>" <?php checked( $checked, true ); ?> style="margin-top:3px;">
								<div>
									<strong style="font-size:14px; color:#0f172a;"><?php echo esc_html( $tax_data['icon'] . ' ' . $tax_data['label'] ); ?></strong>
									<div style="font-size:12px; color:#64748b; margin-top:2px;">
										Taxonomy: <code><?php echo esc_html( $tax_key ); ?></code> &bull; <strong><?php echo esc_html( number_format( $term_count ) ); ?></strong> terms
									</div>
								</div>
							</label>
						</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Exclude Specific Posts by ID -->
				<div class="vm-card-section" style="background:#fff; padding:22px 25px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
					<h3 style="margin-top:0; font-size:16px; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px; display:flex; align-items:center; gap:8px;">
						<span class="dashicons dashicons-hidden" style="color:#64748b;"></span> <?php esc_html_e( 'Exclude Posts by ID', 'vm-seo' ); ?>
					</h3>

					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Excluded Post IDs', 'vm-seo' ); ?></th>
							<td>
								<input type="text" name="sitemap_exclude_ids" value="<?php echo esc_attr( $exclude_ids ); ?>" class="large-text" placeholder="e.g. 12, 45, 108">
								<p class="description"><?php esc_html_e( 'Comma-separated list of Post or Movie IDs you want to exclude from all sitemaps.', 'vm-seo' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<p class="submit" style="position:sticky; bottom:15px; background:rgba(255,255,255,0.95); backdrop-filter:blur(6px); padding:12px 20px; border-radius:8px; border:1px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); z-index:99;">
					<button type="submit" name="vm_seo_save_sitemaps" class="button button-primary" style="background:#10b981; border-color:#059669; padding:6px 24px; font-weight:700; font-size:14px;">
						<?php esc_html_e( 'Save Sitemap Settings', 'vm-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * 5. Render Webmaster Tools Page
	 */
	public static function render_webmaster_page() {
		$opts = VM_SEO_Master::get_instance()->options;
		?>
		<div class="wrap vm-seo-admin-page">
			<h1><span class="dashicons dashicons-admin-site" style="color:#10b981;"></span> <?php esc_html_e( 'Webmaster Tools Verification', 'vm-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Verification codes saved!', 'vm-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:850px; background:#fff; padding:25px 30px; border-radius:10px; border:1px solid #e2e8f0; margin-top:20px;">
				<?php wp_nonce_field( 'vm_seo_webmaster_action', 'vm_seo_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Search Console', 'vm-seo' ); ?></th>
						<td>
							<input type="text" name="google_verify" value="<?php echo esc_attr( $opts['google_verify'] ); ?>" class="large-text" placeholder="google-site-verification code">
							<p class="description"><?php esc_html_e( 'Get your verification HTML tag from Google Search Console.', 'vm-seo' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Bing Webmaster Tools', 'vm-seo' ); ?></th>
						<td><input type="text" name="bing_verify" value="<?php echo esc_attr( $opts['bing_verify'] ); ?>" class="large-text" placeholder="msvalidate.01 code"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Yandex Webmaster', 'vm-seo' ); ?></th>
						<td><input type="text" name="yandex_verify" value="<?php echo esc_attr( $opts['yandex_verify'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Pinterest Verification', 'vm-seo' ); ?></th>
						<td><input type="text" name="pinterest_verify" value="<?php echo esc_attr( $opts['pinterest_verify'] ); ?>" class="large-text"></td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="vm_seo_save_webmaster" class="button button-primary" style="background:#10b981; border-color:#059669; padding:6px 20px; font-weight:700;">
						<?php esc_html_e( 'Save Verification Codes', 'vm-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * 6. Render Tools & 1-Click Importer Page
	 */
	public static function render_tools_page() {
		$sources = VM_SEO_Importer::detect_sources();
		$robots_file = ABSPATH . 'robots.txt';
		$content = file_exists( $robots_file ) ? file_get_contents( $robots_file ) : "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . home_url( '/sitemap.xml' );
		?>
		<div class="wrap vm-seo-admin-page">
			<h1><span class="dashicons dashicons-admin-tools" style="color:#d97706;"></span> <?php esc_html_e( 'VM SEO Tools & 1-Click Importers', 'vm-seo' ); ?></h1>

			<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap:25px; margin-top:20px;">
				
				<!-- 1-Click Yoast SEO Importer Card -->
				<div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:25px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
					<h3 style="margin-top:0; color:#1e40af; display:flex; align-items:center; gap:8px;">
						<span class="dashicons dashicons-migrate"></span> <?php esc_html_e( '1-Click Yoast SEO Importer', 'vm-seo' ); ?>
					</h3>
					<p><?php esc_html_e( 'Import all custom SEO Titles, Meta Descriptions, Focus Keyphrases, Canonical URLs, Noindex tags, and Social OpenGraph data from Yoast SEO into VM SEO.', 'vm-seo' ); ?></p>
					
					<div style="background:#f1f5f9; padding:12px; border-radius:6px; margin:15px 0; font-size:13px;">
						<strong><?php esc_html_e( 'Detected Status:', 'vm-seo' ); ?></strong>
						<span style="color:#2563eb; font-weight:700;"><?php printf( esc_html__( '%d titles with Yoast SEO data found', 'vm-seo' ), $sources['yoast_count'] ); ?></span>
					</div>

					<button type="button" id="vm-btn-import-yoast" class="button button-primary" style="background:#2563eb; font-weight:700; height:36px;">
						<span class="dashicons dashicons-download" style="line-height:34px;"></span> <?php esc_html_e( 'Start Yoast SEO Migration', 'vm-seo' ); ?>
					</button>

					<div id="vm-yoast-import-status" style="margin-top:15px; display:none;"></div>
				</div>

				<!-- 1-Click Rank Math Importer Card -->
				<div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:25px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
					<h3 style="margin-top:0; color:#10b981; display:flex; align-items:center; gap:8px;">
						<span class="dashicons dashicons-update"></span> <?php esc_html_e( '1-Click Rank Math Importer', 'vm-seo' ); ?>
					</h3>
					<p><?php esc_html_e( 'Migrate all SEO metadata, focus keywords, and canonical links from Rank Math SEO plugin into VM SEO.', 'vm-seo' ); ?></p>
					
					<div style="background:#f1f5f9; padding:12px; border-radius:6px; margin:15px 0; font-size:13px;">
						<strong><?php esc_html_e( 'Detected Status:', 'vm-seo' ); ?></strong>
						<span style="color:#10b981; font-weight:700;"><?php printf( esc_html__( '%d titles with Rank Math data found', 'vm-seo' ), $sources['rankmath_count'] ); ?></span>
					</div>

					<button type="button" id="vm-btn-import-rankmath" class="button button-secondary" style="font-weight:700; height:36px;">
						<span class="dashicons dashicons-download" style="line-height:34px;"></span> <?php esc_html_e( 'Start Rank Math Migration', 'vm-seo' ); ?>
					</button>

					<div id="vm-rankmath-import-status" style="margin-top:15px; display:none;"></div>
				</div>
			</div>

			<!-- Robots.txt Editor -->
			<div style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:25px; margin-top:25px; max-width:850px;">
				<h3><span class="dashicons dashicons-media-code" style="color:#d97706;"></span> <?php esc_html_e( 'Robots.txt Editor', 'vm-seo' ); ?></h3>
				<form method="post" action="">
					<?php wp_nonce_field( 'vm_seo_robots_action', 'vm_seo_nonce' ); ?>
					<textarea name="robots_content" rows="10" class="large-text" style="font-family:monospace;"><?php echo esc_textarea( $content ); ?></textarea>
					<p class="submit">
						<button type="submit" name="vm_seo_save_robots" class="button button-primary" style="background:#d97706; border-color:#b45309; font-weight:700;">
							<?php esc_html_e( 'Save robots.txt File', 'vm-seo' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#vm-btn-import-yoast').on('click', function(e) {
				e.preventDefault();
				if (!confirm('Start migrating all Yoast SEO metadata into VM SEO?')) return;
				var $btn = $(this);
				var $status = $('#vm-yoast-import-status');
				$btn.prop('disabled', true).text('Migrating...');
				$status.show().html('<div class="notice notice-info inline"><p><span class="spinner is-active" style="float:none; margin:0 5px 0 0; vertical-align:middle;"></span> Reading Yoast SEO metadata and importing...</p></div>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'vm_seo_import_yoast',
						nonce: '<?php echo wp_create_nonce( 'vm_seo_import_nonce' ); ?>'
					},
					success: function(res) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-download" style="line-height:34px;"></span> Start Yoast SEO Migration');
						if (res.success) {
							$status.html('<div class="notice notice-success inline"><p><strong>Migration Successful!</strong> ' + res.data.message + '</p></div>');
						} else {
							$status.html('<div class="notice notice-error inline"><p><strong>Error:</strong> ' + res.data + '</p></div>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-download" style="line-height:34px;"></span> Start Yoast SEO Migration');
						$status.html('<div class="notice notice-error inline"><p>Server communication error.</p></div>');
					}
				});
			});

			$('#vm-btn-import-rankmath').on('click', function(e) {
				e.preventDefault();
				if (!confirm('Start migrating all Rank Math metadata into VM SEO?')) return;
				var $btn = $(this);
				var $status = $('#vm-rankmath-import-status');
				$btn.prop('disabled', true).text('Migrating...');
				$status.show().html('<div class="notice notice-info inline"><p><span class="spinner is-active" style="float:none; margin:0 5px 0 0; vertical-align:middle;"></span> Reading Rank Math metadata and importing...</p></div>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'vm_seo_import_rankmath',
						nonce: '<?php echo wp_create_nonce( 'vm_seo_import_nonce' ); ?>'
					},
					success: function(res) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-download" style="line-height:34px;"></span> Start Rank Math Migration');
						if (res.success) {
							$status.html('<div class="notice notice-success inline"><p><strong>Migration Successful!</strong> ' + res.data.message + '</p></div>');
						} else {
							$status.html('<div class="notice notice-error inline"><p><strong>Error:</strong> ' + res.data + '</p></div>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-download" style="line-height:34px;"></span> Start Rank Math Migration');
						$status.html('<div class="notice notice-error inline"><p>Server communication error.</p></div>');
					}
				});
			});
		});
		</script>
		<?php
	}
}

VM_SEO_Admin_Dashboard::init();