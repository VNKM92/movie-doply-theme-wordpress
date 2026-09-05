<?php
/**
 * Doodh SEO - Dedicated Admin Settings Dashboard
 *
 * @package DoodhSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Doodh_SEO_Admin_Dashboard {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_seo_menu_pages' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_save_settings' ) );
	}

	public static function add_seo_menu_pages() {
		add_menu_page(
			__( 'Doodh SEO', 'doodh-seo' ),
			__( 'Doodh SEO', 'doodh-seo' ),
			'manage_options',
			'doodh-seo',
			array( __CLASS__, 'render_general_dashboard' ),
			'dashicons-chart-area',
			81
		);

		add_submenu_page(
			'doodh-seo',
			__( 'Titles & Meta', 'doodh-seo' ),
			__( 'Titles & Meta', 'doodh-seo' ),
			'manage_options',
			'doodh-seo-titles',
			array( __CLASS__, 'render_titles_meta_page' )
		);

		add_submenu_page(
			'doodh-seo',
			__( 'Social Media', 'doodh-seo' ),
			__( 'Social Media', 'doodh-seo' ),
			'manage_options',
			'doodh-seo-social',
			array( __CLASS__, 'render_social_page' )
		);

		add_submenu_page(
			'doodh-seo',
			__( 'Webmaster Tools', 'doodh-seo' ),
			__( 'Webmaster Tools', 'doodh-seo' ),
			'manage_options',
			'doodh-seo-webmaster',
			array( __CLASS__, 'render_webmaster_page' )
		);

		add_submenu_page(
			'doodh-seo',
			__( 'File Editor (robots.txt)', 'doodh-seo' ),
			__( 'File Editor', 'doodh-seo' ),
			'manage_options',
			'doodh-seo-tools',
			array( __CLASS__, 'render_tools_page' )
		);
	}

	public static function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Save General / Titles
		if ( isset( $_POST['doodh_seo_save_titles'] ) && check_admin_referer( 'doodh_seo_titles_action', 'doodh_seo_nonce' ) ) {
			$options = get_option( 'doodh_seo_options', array() );
			$options['title_separator']        = sanitize_text_field( $_POST['title_separator'] );
			$options['site_title_template']    = sanitize_text_field( $_POST['site_title_template'] );
			$options['movies_title_template']  = sanitize_text_field( $_POST['movies_title_template'] );
			$options['tvshows_title_template'] = sanitize_text_field( $_POST['tvshows_title_template'] );
			$options['episodes_title_template']= sanitize_text_field( $_POST['episodes_title_template'] );
			$options['home_meta_desc']         = sanitize_textarea_field( $_POST['home_meta_desc'] );

			update_option( 'doodh_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-seo-titles', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Social
		if ( isset( $_POST['doodh_seo_save_social'] ) && check_admin_referer( 'doodh_seo_social_action', 'doodh_seo_nonce' ) ) {
			$options = get_option( 'doodh_seo_options', array() );
			$options['twitter_handle']    = sanitize_text_field( $_POST['twitter_handle'] );
			$options['twitter_card_type'] = sanitize_text_field( $_POST['twitter_card_type'] );
			$options['og_default_image']  = esc_url_raw( $_POST['og_default_image'] );

			update_option( 'doodh_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-seo-social', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Webmaster Tools
		if ( isset( $_POST['doodh_seo_save_webmaster'] ) && check_admin_referer( 'doodh_seo_webmaster_action', 'doodh_seo_nonce' ) ) {
			$options = get_option( 'doodh_seo_options', array() );
			$options['google_verify']    = sanitize_text_field( $_POST['google_verify'] );
			$options['bing_verify']      = sanitize_text_field( $_POST['bing_verify'] );
			$options['yandex_verify']    = sanitize_text_field( $_POST['yandex_verify'] );
			$options['pinterest_verify'] = sanitize_text_field( $_POST['pinterest_verify'] );

			update_option( 'doodh_seo_options', $options );
			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-seo-webmaster', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		// Save Robots.txt
		if ( isset( $_POST['doodh_seo_save_robots'] ) && check_admin_referer( 'doodh_seo_robots_action', 'doodh_seo_nonce' ) ) {
			$robots_content = wp_unslash( $_POST['robots_content'] );
			file_put_contents( ABSPATH . 'robots.txt', $robots_content );
			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-seo-tools', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	public static function render_general_dashboard() {
		$opts = Doodh_SEO_Master::get_instance()->options;
		?>
		<div class="wrap doodh-seo-admin-page">
			<div class="doodh-admin-hero">
				<div class="doodh-badge-pill"><span class="dashicons dashicons-shield"></span> Yoast-Style SEO Suite v1.0.0</div>
				<h1><?php esc_html_e( 'Doodh SEO Master Dashboard', 'doodh-seo' ); ?></h1>
				<p><?php esc_html_e( 'Manage global search engine optimization, snippet templates, OpenGraph tags, XML sitemaps, and webmaster verification.', 'doodh-seo' ); ?></p>
			</div>

			<div class="doodh-dash-cards-grid">
				<div class="doodh-dash-card">
					<div class="card-icon" style="background:#fee2e2; color:#ef4444;"><span class="dashicons dashicons-search"></span></div>
					<h3><?php esc_html_e( 'Titles & Meta', 'doodh-seo' ); ?></h3>
					<p><?php esc_html_e( 'Configure dynamic %%title%%, %%sitename%%, %%year%% templates for all post types.', 'doodh-seo' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-seo-titles' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Edit Templates &rarr;', 'doodh-seo' ); ?></a>
				</div>

				<div class="doodh-dash-card">
					<div class="card-icon" style="background:#dbeafe; color:#2563eb;"><span class="dashicons dashicons-share"></span></div>
					<h3><?php esc_html_e( 'Social OpenGraph', 'doodh-seo' ); ?></h3>
					<p><?php esc_html_e( 'Set up Twitter Cards and Facebook share thumbnails for maximum click-through rates.', 'doodh-seo' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-seo-social' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Social Settings &rarr;', 'doodh-seo' ); ?></a>
				</div>

				<div class="doodh-dash-card">
					<div class="card-icon" style="background:#d1fae5; color:#10b981;"><span class="dashicons dashicons-networking"></span></div>
					<h3><?php esc_html_e( 'Webmaster Tools', 'doodh-seo' ); ?></h3>
					<p><?php esc_html_e( 'Verify Google Search Console, Bing Webmaster, and Pinterest domains in one click.', 'doodh-seo' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-seo-webmaster' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Verify Domains &rarr;', 'doodh-seo' ); ?></a>
				</div>

				<div class="doodh-dash-card">
					<div class="card-icon" style="background:#fef3c7; color:#d97706;"><span class="dashicons dashicons-media-code"></span></div>
					<h3><?php esc_html_e( 'Robots.txt Editor', 'doodh-seo' ); ?></h3>
					<p><?php esc_html_e( 'Edit robots.txt crawl directives directly from the WordPress dashboard.', 'doodh-seo' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-seo-tools' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Edit Files &rarr;', 'doodh-seo' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	public static function render_titles_meta_page() {
		$opts = Doodh_SEO_Master::get_instance()->options;
		?>
		<div class="wrap doodh-seo-admin-page">
			<h1><span class="dashicons dashicons-search" style="color:#e50914;"></span> <?php esc_html_e( 'Titles & Meta Templates', 'doodh-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'SEO Title & Meta settings saved successfully!', 'doodh-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:850px; background:#fff; padding:25px 30px; border-radius:10px; border:1px solid #e2e8f0; margin-top:20px;">
				<?php wp_nonce_field( 'doodh_seo_titles_action', 'doodh_seo_nonce' ); ?>

				<h3><?php esc_html_e( 'Global Variables & Separators', 'doodh-seo' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Title Separator', 'doodh-seo' ); ?></th>
						<td>
							<input type="text" name="title_separator" value="<?php echo esc_attr( $opts['title_separator'] ); ?>" style="width:80px; font-weight:700; text-align:center;">
							<p class="description"><?php esc_html_e( 'Symbol used between titles (e.g. |, -, &bull;, &raquo;)', 'doodh-seo' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Homepage Meta Description', 'doodh-seo' ); ?></th>
						<td>
							<textarea name="home_meta_desc" rows="3" class="large-text"><?php echo esc_textarea( $opts['home_meta_desc'] ); ?></textarea>
						</td>
					</tr>
				</table>

				<h3 style="margin-top:30px;"><?php esc_html_e( 'Post Types Title Templates', 'doodh-seo' ); ?></h3>
				<p class="description" style="margin-bottom:15px;"><?php esc_html_e( 'Available tags: %%title%%, %%sitename%%, %%tagline%%, %%sep%%, %%year%%, %%category%%', 'doodh-seo' ); ?></p>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Movies Title Template', 'doodh-seo' ); ?></th>
						<td><input type="text" name="movies_title_template" value="<?php echo esc_attr( $opts['movies_title_template'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'TV Shows Title Template', 'doodh-seo' ); ?></th>
						<td><input type="text" name="tvshows_title_template" value="<?php echo esc_attr( $opts['tvshows_title_template'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Episodes Title Template', 'doodh-seo' ); ?></th>
						<td><input type="text" name="episodes_title_template" value="<?php echo esc_attr( $opts['episodes_title_template'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Site Title Template', 'doodh-seo' ); ?></th>
						<td><input type="text" name="site_title_template" value="<?php echo esc_attr( $opts['site_title_template'] ); ?>" class="large-text"></td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="doodh_seo_save_titles" class="button button-primary" style="background:#e50914; border-color:#dc2626; padding:6px 20px; font-weight:700;">
						<?php esc_html_e( 'Save Title Templates', 'doodh-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	public static function render_social_page() {
		$opts = Doodh_SEO_Master::get_instance()->options;
		?>
		<div class="wrap doodh-seo-admin-page">
			<h1><span class="dashicons dashicons-share" style="color:#2563eb;"></span> <?php esc_html_e( 'Social Media & OpenGraph', 'doodh-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Social settings saved!', 'doodh-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:850px; background:#fff; padding:25px 30px; border-radius:10px; border:1px solid #e2e8f0; margin-top:20px;">
				<?php wp_nonce_field( 'doodh_seo_social_action', 'doodh_seo_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Twitter / X Handle', 'doodh-seo' ); ?></th>
						<td><input type="text" name="twitter_handle" value="<?php echo esc_attr( $opts['twitter_handle'] ); ?>" class="regular-text" placeholder="@YourBrand"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Default Twitter Card Type', 'doodh-seo' ); ?></th>
						<td>
							<select name="twitter_card_type">
								<option value="summary_large_image" <?php selected( $opts['twitter_card_type'], 'summary_large_image' ); ?>>Summary with Large Image (Recommended)</option>
								<option value="summary" <?php selected( $opts['twitter_card_type'], 'summary' ); ?>>Summary</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Default Social Share Image URL', 'doodh-seo' ); ?></th>
						<td>
							<input type="url" name="og_default_image" value="<?php echo esc_attr( $opts['og_default_image'] ); ?>" class="large-text" placeholder="https://example.com/banner.jpg">
							<p class="description"><?php esc_html_e( 'Used as a fallback when a shared post has no featured image.', 'doodh-seo' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="doodh_seo_save_social" class="button button-primary" style="background:#2563eb; border-color:#1d4ed8; padding:6px 20px; font-weight:700;">
						<?php esc_html_e( 'Save Social Settings', 'doodh-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	public static function render_webmaster_page() {
		$opts = Doodh_SEO_Master::get_instance()->options;
		?>
		<div class="wrap doodh-seo-admin-page">
			<h1><span class="dashicons dashicons-networking" style="color:#10b981;"></span> <?php esc_html_e( 'Webmaster Tools Verification', 'doodh-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Verification codes saved!', 'doodh-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:850px; background:#fff; padding:25px 30px; border-radius:10px; border:1px solid #e2e8f0; margin-top:20px;">
				<?php wp_nonce_field( 'doodh_seo_webmaster_action', 'doodh_seo_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Search Console', 'doodh-seo' ); ?></th>
						<td>
							<input type="text" name="google_verify" value="<?php echo esc_attr( $opts['google_verify'] ); ?>" class="large-text" placeholder="google-site-verification code">
							<p class="description"><?php esc_html_e( 'Enter the verification code provided by Google Search Console.', 'doodh-seo' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Bing Webmaster Tools', 'doodh-seo' ); ?></th>
						<td>
							<input type="text" name="bing_verify" value="<?php echo esc_attr( $opts['bing_verify'] ); ?>" class="large-text" placeholder="msvalidate.01 code">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Yandex Webmaster', 'doodh-seo' ); ?></th>
						<td><input type="text" name="yandex_verify" value="<?php echo esc_attr( $opts['yandex_verify'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Pinterest Verification', 'doodh-seo' ); ?></th>
						<td><input type="text" name="pinterest_verify" value="<?php echo esc_attr( $opts['pinterest_verify'] ); ?>" class="large-text"></td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="doodh_seo_save_webmaster" class="button button-primary" style="background:#10b981; border-color:#059669; padding:6px 20px; font-weight:700;">
						<?php esc_html_e( 'Save Verification Codes', 'doodh-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}

	public static function render_tools_page() {
		$robots_file = ABSPATH . 'robots.txt';
		$content = file_exists( $robots_file ) ? file_get_contents( $robots_file ) : "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . home_url( '/sitemap.xml' );
		?>
		<div class="wrap doodh-seo-admin-page">
			<h1><span class="dashicons dashicons-media-code" style="color:#d97706;"></span> <?php esc_html_e( 'In-Admin Robots.txt Editor', 'doodh-seo' ); ?></h1>
			
			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'robots.txt updated successfully!', 'doodh-seo' ); ?></strong></p></div>
			<?php endif; ?>

			<form method="post" action="" style="max-width:850px; background:#fff; padding:25px 30px; border-radius:10px; border:1px solid #e2e8f0; margin-top:20px;">
				<?php wp_nonce_field( 'doodh_seo_robots_action', 'doodh_seo_nonce' ); ?>
				
				<p><?php esc_html_e( 'Directly edit your site\'s robots.txt file below to instruct search crawlers:', 'doodh-seo' ); ?></p>
				<textarea name="robots_content" rows="12" class="large-text" style="font-family:monospace;"><?php echo esc_textarea( $content ); ?></textarea>

				<p class="submit">
					<button type="submit" name="doodh_seo_save_robots" class="button button-primary" style="background:#d97706; border-color:#b45309; padding:6px 20px; font-weight:700;">
						<?php esc_html_e( 'Save robots.txt File', 'doodh-seo' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}
}

Doodh_SEO_Admin_Dashboard::init();