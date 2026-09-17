<?php
/**
 * Advanced Dynamic Homepage Section Manager
 *
 * Enterprise-grade homepage layout engine featuring dynamic section controls,
 * custom curated collections, conditional rendering with zero empty space,
 * and an intuitive WP Admin control dashboard.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Default Homepage Settings
 *
 * @return array
 */
function vmtheme_get_default_home_settings() {
	return array(
		'spotlight_search' => array(
			'enabled'     => 1,
			'title'       => 'Search 10,000+ Movies, TV Shows & Stars',
			'subtitle'    => 'Instant real-time search with 4K quality filters, IMDb ratings, and smart taxonomy matching.',
			'quick_tags'  => 'Action, Sci-Fi, Avengers, Avatar, Anime, Horror, Christopher Nolan, 4K',
		),
		'hero_showcase' => array(
			'enabled'     => 1,
			'query_type'  => 'latest', // 'latest', 'top_rated', 'custom'
			'post_type'   => 'both',   // 'both', 'movies', 'tvshows'
			'count'       => 1,
			'custom_ids'  => '',
		),
		'trending_movies' => array(
			'enabled'     => 1,
			'title'       => 'Trending Movies',
			'subtitle'    => 'The most watched and popular movies streaming right now.',
			'count'       => 12,
			'genre'       => '',
			'orderby'     => 'date',   // 'date', 'rating', 'views', 'rand'
		),
		'popular_tvshows' => array(
			'enabled'     => 1,
			'title'       => 'Popular TV Shows & Series',
			'subtitle'    => 'Binge-worthy drama, action, and sci-fi series with all seasons.',
			'count'       => 12,
			'genre'       => '',
			'orderby'     => 'date',
		),
		'top_imdb' => array(
			'enabled'     => 1,
			'title'       => 'Top 100 IMDb Blockbusters',
			'subtitle'    => 'Critically acclaimed and highest rated titles of all time.',
			'count'       => 6,
			'min_rating'  => 8.0,
		),
		'genres_directory' => array(
			'enabled'     => 1,
			'title'       => 'Browse by Genre',
			'subtitle'    => 'Discover thousands of titles organized across your favorite cinema categories.',
			'count'       => 12,
		),
		'custom_collection_1' => array(
			'enabled'     => 1,
			'title'       => '4K Ultra HD Cinema Spotlight',
			'subtitle'    => 'Experience crystal-clear visual fidelity and immersive sound in native 4K UHD.',
			'badge'       => '4K UHD EXCLUSIVE',
			'post_type'   => 'movies',
			'genre'       => 'Action',
			'count'       => 6,
			'custom_ids'  => '',
		),
		'custom_collection_2' => array(
			'enabled'     => 1,
			'title'       => 'Anime & Sci-Fi Universe',
			'subtitle'    => 'Explore sensational animated adventures, futuristic sagas, and epic fantasies.',
			'badge'       => 'TRENDING ANIME',
			'post_type'   => 'both',
			'genre'       => 'Animation',
			'count'       => 6,
			'custom_ids'  => '',
		),
		'custom_collection_3' => array(
			'enabled'     => 0,
			'title'       => 'Oscar Winners & Critically Acclaimed',
			'subtitle'    => 'Award-winning performances, masterpieces, and cinematic history.',
			'badge'       => 'AWARD WINNERS',
			'post_type'   => 'movies',
			'genre'       => 'Drama',
			'count'       => 6,
			'custom_ids'  => '',
		),
		'faq_section' => array(
			'enabled'     => 1,
			'title'       => 'Frequently Asked Questions',
			'subtitle'    => 'Everything you need to know about streaming, resolutions, and multi-server playback.',
			'faqs'        => array(
				array(
					'q' => 'Is streaming and downloading completely free on VMTheme?',
					'a' => 'Yes, our platform provides completely free access to our movie and TV catalog in Ultra HD and Full HD resolutions without requiring any paid subscriptions.',
				),
				array(
					'q' => 'How do I switch streaming servers if a video is slow?',
					'a' => 'Simply click on the "Servers" tabs located directly above any movie or episode video player to switch instantly between SuperStream, CloudStream, AlphaFast, and VidNova.',
				),
				array(
					'q' => 'Can I save movies to watch later?',
					'a' => 'Yes! Click the "Watchlist" bookmark button on any movie or TV series. When logged in, your personal queue syncs across all your devices in real time.',
				),
				array(
					'q' => 'How can I request a missing movie or TV series?',
					'a' => 'Visit our Title Request page from the navbar or footer and enter the name of the movie or show. Our team reviews and indexes community requests daily.',
				),
			),
		),
		'rich_content_1' => array(
			'enabled'     => 1,
			'badge'       => 'EDITORIAL SPOTLIGHT',
			'title'       => 'About VMTheme Streaming Platform & High-Bitrate Index',
			'subtitle'    => 'Discover our mission, high-speed multi-server architecture, and cinematic catalog.',
			'content'     => '<p>Welcome to <strong>VMTheme</strong>, the ultimate next-generation streaming portal designed for movie enthusiasts and binge-watchers worldwide. We curate the highest-quality <strong>4K Ultra HD</strong>, <strong>1080p Full HD</strong>, and <strong>720p HD</strong> releases with multi-server failover redundancy.</p><p>Explore over 10,000+ indexed titles across Hollywood, European cinema, Anime, Korean Dramas, and Bollywood. Our streaming cluster ensures zero buffering, high bitrate transfers, and lightning-fast loading speeds on desktop, tablets, smart TVs, and mobile devices.</p>',
		),
		'rich_content_2' => array(
			'enabled'     => 1,
			'badge'       => 'STREAMING GUIDE & TIPS',
			'title'       => 'Supported Devices, Multi-Audio Subtitles & Playback Guide',
			'subtitle'    => 'Get the most out of your viewing experience with our multi-device guide.',
			'content'     => '<p>Our high-tech HTML5 video player supports smooth playback on <strong>Google Chrome, Mozilla Firefox, Apple Safari, Microsoft Edge, Android TV, and Apple AirPlay</strong>. Enjoy seamless switching between audio tracks and multiple subtitle options including English, Spanish, French, and Hindi.</p><p>For the best 4K HDR playback experience, we recommend a broadband connection of at least <strong>15 Mbps</strong> and enabling hardware acceleration in your browser settings.</p>',
		),
		'cta_banner' => array(
			'enabled'     => 1,
			'title'       => 'Can\'t Find Your Favorite Movie or TV Show?',
			'subtitle'    => 'Submit a title request to our indexing team. We upload and verify high-speed 4K streaming links within 24 hours.',
			'btn_text'    => 'Submit a Title Request',
			'btn_url'     => home_url( '/request/' ),
		),
	);
}

/**
 * Get Saved Homepage Settings (Merged with Defaults)
 *
 * @return array
 */
function vmtheme_get_home_settings() {
	$defaults = vmtheme_get_default_home_settings();
	$saved    = get_option( 'vm_homepage_settings', get_option( 'doodh_homepage_settings', array() ) );

	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	foreach ( $defaults as $key => $default_val ) {
		if ( ! isset( $saved[ $key ] ) ) {
			$saved[ $key ] = $default_val;
		} elseif ( is_array( $default_val ) && is_array( $saved[ $key ] ) ) {
			if ( $key === 'faq_section' && isset( $saved[ $key ]['faqs'] ) && is_array( $saved[ $key ]['faqs'] ) ) {
				$saved[ $key ]['enabled']  = $saved[ $key ]['enabled'] ?? $default_val['enabled'];
				$saved[ $key ]['title']    = $saved[ $key ]['title'] ?? $default_val['title'];
				$saved[ $key ]['subtitle'] = $saved[ $key ]['subtitle'] ?? $default_val['subtitle'];
			} else {
				$saved[ $key ] = array_merge( $default_val, $saved[ $key ] );
			}
		}
	}

	return $saved;
}

/**
 * Register Homepage Manager Admin Menu
 */
function vmtheme_register_home_manager_menu() {
	add_theme_page(
		__( 'Homepage Manager', 'vmtheme' ),
		__( 'Homepage Manager', 'vmtheme' ),
		'manage_options',
		'vmtheme-homepage-manager',
		'vmtheme_render_home_manager_page'
	);
}
add_action( 'admin_menu', 'vmtheme_register_home_manager_menu' );

/**
 * Render Homepage Manager Admin Dashboard
 */
function vmtheme_render_home_manager_page() {
	if ( isset( $_POST['vm_save_home_settings'] ) && check_admin_referer( 'vm_home_manager_nonce' ) ) {
		$settings = vmtheme_get_home_settings();

		// Spotlight Search
		$settings['spotlight_search']['enabled']    = ! empty( $_POST['spotlight_search_enabled'] ) ? 1 : 0;
		$settings['spotlight_search']['title']      = sanitize_text_field( $_POST['spotlight_search_title'] ?? '' );
		$settings['spotlight_search']['subtitle']   = sanitize_text_field( $_POST['spotlight_search_subtitle'] ?? '' );
		$settings['spotlight_search']['quick_tags'] = sanitize_text_field( $_POST['spotlight_search_quick_tags'] ?? '' );

		// Hero Showcase
		$settings['hero_showcase']['enabled']    = ! empty( $_POST['hero_showcase_enabled'] ) ? 1 : 0;
		$settings['hero_showcase']['query_type'] = sanitize_key( $_POST['hero_showcase_query_type'] ?? 'latest' );
		$settings['hero_showcase']['post_type']  = sanitize_key( $_POST['hero_showcase_post_type'] ?? 'both' );
		$settings['hero_showcase']['count']      = min( 5, max( 1, (int) ( $_POST['hero_showcase_count'] ?? 1 ) ) );
		$settings['hero_showcase']['custom_ids'] = sanitize_text_field( $_POST['hero_showcase_custom_ids'] ?? '' );

		// Trending Movies
		$settings['trending_movies']['enabled']  = ! empty( $_POST['trending_movies_enabled'] ) ? 1 : 0;
		$settings['trending_movies']['title']    = sanitize_text_field( $_POST['trending_movies_title'] ?? '' );
		$settings['trending_movies']['subtitle'] = sanitize_text_field( $_POST['trending_movies_subtitle'] ?? '' );
		$settings['trending_movies']['count']    = min( 24, max( 4, (int) ( $_POST['trending_movies_count'] ?? 12 ) ) );
		$settings['trending_movies']['genre']    = sanitize_text_field( $_POST['trending_movies_genre'] ?? '' );
		$settings['trending_movies']['orderby']  = sanitize_key( $_POST['trending_movies_orderby'] ?? 'date' );

		// Popular TV Series
		$settings['popular_tvshows']['enabled']  = ! empty( $_POST['popular_tvshows_enabled'] ) ? 1 : 0;
		$settings['popular_tvshows']['title']    = sanitize_text_field( $_POST['popular_tvshows_title'] ?? '' );
		$settings['popular_tvshows']['subtitle'] = sanitize_text_field( $_POST['popular_tvshows_subtitle'] ?? '' );
		$settings['popular_tvshows']['count']    = min( 24, max( 4, (int) ( $_POST['popular_tvshows_count'] ?? 12 ) ) );
		$settings['popular_tvshows']['genre']    = sanitize_text_field( $_POST['popular_tvshows_genre'] ?? '' );
		$settings['popular_tvshows']['orderby']  = sanitize_key( $_POST['popular_tvshows_orderby'] ?? 'date' );

		// Top 100 IMDb
		$settings['top_imdb']['enabled']     = ! empty( $_POST['top_imdb_enabled'] ) ? 1 : 0;
		$settings['top_imdb']['title']       = sanitize_text_field( $_POST['top_imdb_title'] ?? '' );
		$settings['top_imdb']['subtitle']    = sanitize_text_field( $_POST['top_imdb_subtitle'] ?? '' );
		$settings['top_imdb']['count']       = min( 12, max( 3, (int) ( $_POST['top_imdb_count'] ?? 6 ) ) );
		$settings['top_imdb']['min_rating']  = floatval( $_POST['top_imdb_min_rating'] ?? 8.0 );

		// Genres Directory
		$settings['genres_directory']['enabled']  = ! empty( $_POST['genres_directory_enabled'] ) ? 1 : 0;
		$settings['genres_directory']['title']    = sanitize_text_field( $_POST['genres_directory_title'] ?? '' );
		$settings['genres_directory']['subtitle'] = sanitize_text_field( $_POST['genres_directory_subtitle'] ?? '' );
		$settings['genres_directory']['count']    = min( 24, max( 6, (int) ( $_POST['genres_directory_count'] ?? 12 ) ) );

		// Custom Collection 1
		$settings['custom_collection_1']['enabled']    = ! empty( $_POST['custom_collection_1_enabled'] ) ? 1 : 0;
		$settings['custom_collection_1']['title']      = sanitize_text_field( $_POST['custom_collection_1_title'] ?? '' );
		$settings['custom_collection_1']['subtitle']   = sanitize_text_field( $_POST['custom_collection_1_subtitle'] ?? '' );
		$settings['custom_collection_1']['badge']      = sanitize_text_field( $_POST['custom_collection_1_badge'] ?? '' );
		$settings['custom_collection_1']['post_type']  = sanitize_key( $_POST['custom_collection_1_post_type'] ?? 'movies' );
		$settings['custom_collection_1']['genre']      = sanitize_text_field( $_POST['custom_collection_1_genre'] ?? '' );
		$settings['custom_collection_1']['count']      = min( 18, max( 3, (int) ( $_POST['custom_collection_1_count'] ?? 6 ) ) );
		$settings['custom_collection_1']['custom_ids'] = sanitize_text_field( $_POST['custom_collection_1_custom_ids'] ?? '' );

		// Custom Collection 2
		$settings['custom_collection_2']['enabled']    = ! empty( $_POST['custom_collection_2_enabled'] ) ? 1 : 0;
		$settings['custom_collection_2']['title']      = sanitize_text_field( $_POST['custom_collection_2_title'] ?? '' );
		$settings['custom_collection_2']['subtitle']   = sanitize_text_field( $_POST['custom_collection_2_subtitle'] ?? '' );
		$settings['custom_collection_2']['badge']      = sanitize_text_field( $_POST['custom_collection_2_badge'] ?? '' );
		$settings['custom_collection_2']['post_type']  = sanitize_key( $_POST['custom_collection_2_post_type'] ?? 'both' );
		$settings['custom_collection_2']['genre']      = sanitize_text_field( $_POST['custom_collection_2_genre'] ?? '' );
		$settings['custom_collection_2']['count']      = min( 18, max( 3, (int) ( $_POST['custom_collection_2_count'] ?? 6 ) ) );
		$settings['custom_collection_2']['custom_ids'] = sanitize_text_field( $_POST['custom_collection_2_custom_ids'] ?? '' );

		// Custom Collection 3
		$settings['custom_collection_3']['enabled']    = ! empty( $_POST['custom_collection_3_enabled'] ) ? 1 : 0;
		$settings['custom_collection_3']['title']      = sanitize_text_field( $_POST['custom_collection_3_title'] ?? '' );
		$settings['custom_collection_3']['subtitle']   = sanitize_text_field( $_POST['custom_collection_3_subtitle'] ?? '' );
		$settings['custom_collection_3']['badge']      = sanitize_text_field( $_POST['custom_collection_3_badge'] ?? '' );
		$settings['custom_collection_3']['post_type']  = sanitize_key( $_POST['custom_collection_3_post_type'] ?? 'movies' );
		$settings['custom_collection_3']['genre']      = sanitize_text_field( $_POST['custom_collection_3_genre'] ?? '' );
		$settings['custom_collection_3']['count']      = min( 18, max( 3, (int) ( $_POST['custom_collection_3_count'] ?? 6 ) ) );
		$settings['custom_collection_3']['custom_ids'] = sanitize_text_field( $_POST['custom_collection_3_custom_ids'] ?? '' );

		// FAQ Section
		$settings['faq_section']['enabled']  = ! empty( $_POST['faq_section_enabled'] ) ? 1 : 0;
		$settings['faq_section']['title']    = sanitize_text_field( $_POST['faq_section_title'] ?? 'Frequently Asked Questions' );
		$settings['faq_section']['subtitle'] = sanitize_text_field( $_POST['faq_section_subtitle'] ?? '' );

		$faq_items = array();
		if ( ! empty( $_POST['faq_q'] ) && is_array( $_POST['faq_q'] ) ) {
			foreach ( $_POST['faq_q'] as $idx => $q_text ) {
				$q = sanitize_text_field( trim( $q_text ) );
				$a = ! empty( $_POST['faq_a'][ $idx ] ) ? wp_kses_post( trim( $_POST['faq_a'][ $idx ] ) ) : '';
				if ( ! empty( $q ) && ! empty( $a ) ) {
					$faq_items[] = array(
						'q' => $q,
						'a' => $a,
					);
				}
			}
		}
		$settings['faq_section']['faqs'] = $faq_items;

		// Dynamic Rich Content Section 1 (WYSIWYG Rich Editor)
		$settings['rich_content_1']['enabled']  = ! empty( $_POST['rich_content_1_enabled'] ) ? 1 : 0;
		$settings['rich_content_1']['title']    = sanitize_text_field( $_POST['rich_content_1_title'] ?? '' );
		$settings['rich_content_1']['subtitle'] = sanitize_text_field( $_POST['rich_content_1_subtitle'] ?? '' );
		$settings['rich_content_1']['badge']    = sanitize_text_field( $_POST['rich_content_1_badge'] ?? '' );
		$settings['rich_content_1']['content']  = ! empty( $_POST['rich_content_1_content'] ) ? wp_kses_post( $_POST['rich_content_1_content'] ) : '';

		// Dynamic Rich Content Section 2 (WYSIWYG Rich Editor)
		$settings['rich_content_2']['enabled']  = ! empty( $_POST['rich_content_2_enabled'] ) ? 1 : 0;
		$settings['rich_content_2']['title']    = sanitize_text_field( $_POST['rich_content_2_title'] ?? '' );
		$settings['rich_content_2']['subtitle'] = sanitize_text_field( $_POST['rich_content_2_subtitle'] ?? '' );
		$settings['rich_content_2']['badge']    = sanitize_text_field( $_POST['rich_content_2_badge'] ?? '' );
		$settings['rich_content_2']['content']  = ! empty( $_POST['rich_content_2_content'] ) ? wp_kses_post( $_POST['rich_content_2_content'] ) : '';

		// CTA Banner
		$settings['cta_banner']['enabled']  = ! empty( $_POST['cta_banner_enabled'] ) ? 1 : 0;
		$settings['cta_banner']['title']    = sanitize_text_field( $_POST['cta_banner_title'] ?? '' );
		$settings['cta_banner']['subtitle'] = sanitize_text_field( $_POST['cta_banner_subtitle'] ?? '' );
		$settings['cta_banner']['btn_text'] = sanitize_text_field( $_POST['cta_banner_btn_text'] ?? '' );
		$settings['cta_banner']['btn_url']  = esc_url_raw( $_POST['cta_banner_btn_url'] ?? '' );

		update_option( 'vm_homepage_settings', $settings );
		update_option( 'doodh_homepage_settings', $settings );

		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Homepage section settings saved successfully! Your homepage has updated live.', 'vmtheme' ) . '</strong></p></div>';
	}

	$s = vmtheme_get_home_settings();

	// Fetch all genre terms for dropdowns
	$genres = get_terms( array( 'taxonomy' => 'genres', 'hide_empty' => false ) );
	?>
	<div class="wrap" style="max-width:1100px; margin-top:20px;">
		<!-- Header Banner -->
		<div style="background:linear-gradient(135deg,#111827,#1f2937); color:#fff; padding:24px 28px; border-radius:12px; margin-bottom:25px; box-shadow:0 8px 25px rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
			<div>
				<h1 style="color:#fff; margin:0 0 6px; font-size:26px; font-weight:800; display:flex; align-items:center; gap:12px;">
					<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:40px; height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(229,9,20,0.4);"><i class="dashicons dashicons-layout" style="color:#fff; font-size:22px; line-height:40px; height:40px; width:40px;"></i></span>
					<?php esc_html_e( 'Homepage Section Manager', 'vmtheme' ); ?>
				</h1>
				<p style="color:#94a3b8; margin:0; font-size:14px;">
					<?php esc_html_e( 'Enable, customize, and configure homepage sections and custom curated collections with intelligent conditional rendering.', 'vmtheme' ); ?>
				</p>
			</div>
			<div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button button-secondary" style="font-weight:600; padding:6px 16px; height:auto;">
					<span class="dashicons dashicons-external" style="vertical-align:middle; font-size:16px;"></span> <?php esc_html_e( 'View Live Homepage', 'vmtheme' ); ?>
				</a>
			</div>
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'vm_home_manager_nonce' ); ?>

			<style>
				.vm-section-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:20px 24px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.04); transition:all 0.2s ease; }
				.vm-section-card:hover { border-color:#cbd5e1; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
				.vm-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; }
				.vm-card-title { font-size:18px; font-weight:700; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px; }
				.vm-switch-label { display:inline-flex; align-items:center; gap:8px; font-weight:700; font-size:13.5px; cursor:pointer; }
				.vm-pill-badge { background:#e0f2fe; color:#0369a1; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; text-transform:uppercase; }
			</style>

			<!-- 1. SPOTLIGHT SEARCH BAR -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-search" style="color:#e50914;"></span> 1. Spotlight Search Bar (After Navbar)</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="spotlight_search_enabled" value="1" <?php checked( $s['spotlight_search']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Heading Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="spotlight_search_title" value="<?php echo esc_attr( $s['spotlight_search']['title'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Subtitle Description', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="spotlight_search_subtitle" value="<?php echo esc_attr( $s['spotlight_search']['subtitle'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Trending Tag Pills', 'vmtheme' ); ?></label></th>
						<td>
							<input type="text" name="spotlight_search_quick_tags" value="<?php echo esc_attr( $s['spotlight_search']['quick_tags'] ); ?>" class="large-text">
							<p class="description"><?php esc_html_e( 'Comma-separated search suggestions shown under the input bar.', 'vmtheme' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- 2. HERO SPOTLIGHT SHOWCASE -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-video-alt3" style="color:#e50914;"></span> 2. Featured Hero Showcase Banner</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="hero_showcase_enabled" value="1" <?php checked( $s['hero_showcase']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Query Strategy', 'vmtheme' ); ?></label></th>
						<td>
							<select name="hero_showcase_query_type">
								<option value="latest" <?php selected( $s['hero_showcase']['query_type'], 'latest' ); ?>><?php esc_html_e( 'Latest Published Content', 'vmtheme' ); ?></option>
								<option value="top_rated" <?php selected( $s['hero_showcase']['query_type'], 'top_rated' ); ?>><?php esc_html_e( 'Highest Rated Title (IMDb/Reviews)', 'vmtheme' ); ?></option>
								<option value="custom" <?php selected( $s['hero_showcase']['query_type'], 'custom' ); ?>><?php esc_html_e( 'Specific Post ID(s)', 'vmtheme' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Post Type Source', 'vmtheme' ); ?></label></th>
						<td>
							<select name="hero_showcase_post_type">
								<option value="both" <?php selected( $s['hero_showcase']['post_type'], 'both' ); ?>><?php esc_html_e( 'Both Movies & TV Shows', 'vmtheme' ); ?></option>
								<option value="movies" <?php selected( $s['hero_showcase']['post_type'], 'movies' ); ?>><?php esc_html_e( 'Movies Only', 'vmtheme' ); ?></option>
								<option value="tvshows" <?php selected( $s['hero_showcase']['post_type'], 'tvshows' ); ?>><?php esc_html_e( 'TV Series Only', 'vmtheme' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Custom Post IDs (Optional)', 'vmtheme' ); ?></label></th>
						<td>
							<input type="text" name="hero_showcase_custom_ids" value="<?php echo esc_attr( $s['hero_showcase']['custom_ids'] ); ?>" class="regular-text" placeholder="e.g. 105, 240, 89">
							<p class="description"><?php esc_html_e( 'Enter specific Post IDs if "Specific Post ID(s)" is selected above.', 'vmtheme' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- 3. TRENDING MOVIES SECTION -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-format-video" style="color:#e50914;"></span> 3. Trending Movies Section</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="trending_movies_enabled" value="1" <?php checked( $s['trending_movies']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="trending_movies_title" value="<?php echo esc_attr( $s['trending_movies']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Section Subtitle', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="trending_movies_subtitle" value="<?php echo esc_attr( $s['trending_movies']['subtitle'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Display Count', 'vmtheme' ); ?></label></th>
						<td><input type="number" name="trending_movies_count" value="<?php echo esc_attr( $s['trending_movies']['count'] ); ?>" min="4" max="24" step="2" class="small-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Order By', 'vmtheme' ); ?></label></th>
						<td>
							<select name="trending_movies_orderby">
								<option value="date" <?php selected( $s['trending_movies']['orderby'], 'date' ); ?>><?php esc_html_e( 'Latest Date Added', 'vmtheme' ); ?></option>
								<option value="rating" <?php selected( $s['trending_movies']['orderby'], 'rating' ); ?>><?php esc_html_e( 'Highest Rating', 'vmtheme' ); ?></option>
								<option value="rand" <?php selected( $s['trending_movies']['orderby'], 'rand' ); ?>><?php esc_html_e( 'Random Shuffle', 'vmtheme' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Genre Filter (Optional)', 'vmtheme' ); ?></label></th>
						<td>
							<select name="trending_movies_genre">
								<option value=""><?php esc_html_e( 'All Movie Genres', 'vmtheme' ); ?></option>
								<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : foreach ( $genres as $g ) : ?>
									<option value="<?php echo esc_attr( $g->slug ); ?>" <?php selected( $s['trending_movies']['genre'], $g->slug ); ?>><?php echo esc_html( $g->name ); ?></option>
								<?php endforeach; endif; ?>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<!-- 4. POPULAR TV SHOWS SECTION -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-desktop" style="color:#e50914;"></span> 4. Popular TV Shows & Series</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="popular_tvshows_enabled" value="1" <?php checked( $s['popular_tvshows']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="popular_tvshows_title" value="<?php echo esc_attr( $s['popular_tvshows']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Display Count', 'vmtheme' ); ?></label></th>
						<td><input type="number" name="popular_tvshows_count" value="<?php echo esc_attr( $s['popular_tvshows']['count'] ); ?>" min="4" max="24" step="2" class="small-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Order By', 'vmtheme' ); ?></label></th>
						<td>
							<select name="popular_tvshows_orderby">
								<option value="date" <?php selected( $s['popular_tvshows']['orderby'], 'date' ); ?>><?php esc_html_e( 'Latest Date Added', 'vmtheme' ); ?></option>
								<option value="rating" <?php selected( $s['popular_tvshows']['orderby'], 'rating' ); ?>><?php esc_html_e( 'Highest Rating', 'vmtheme' ); ?></option>
								<option value="rand" <?php selected( $s['popular_tvshows']['orderby'], 'rand' ); ?>><?php esc_html_e( 'Random Shuffle', 'vmtheme' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<!-- 5. TOP 100 IMDB SECTION -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-awards" style="color:#e50914;"></span> 5. Top 100 IMDb / Highly Rated Leaderboard</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="top_imdb_enabled" value="1" <?php checked( $s['top_imdb']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="top_imdb_title" value="<?php echo esc_attr( $s['top_imdb']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Minimum Score Filter', 'vmtheme' ); ?></label></th>
						<td><input type="number" name="top_imdb_min_rating" value="<?php echo esc_attr( $s['top_imdb']['min_rating'] ); ?>" min="1.0" max="10.0" step="0.1" class="small-text"> <span class="description">/ 10</span></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Display Count', 'vmtheme' ); ?></label></th>
						<td><input type="number" name="top_imdb_count" value="<?php echo esc_attr( $s['top_imdb']['count'] ); ?>" min="3" max="12" class="small-text"></td>
					</tr>
				</table>
			</div>

			<!-- 6. GENRES DIRECTORY QUICK NAV -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-category" style="color:#e50914;"></span> 6. Genre Directory Quick Nav</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="genres_directory_enabled" value="1" <?php checked( $s['genres_directory']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="genres_directory_title" value="<?php echo esc_attr( $s['genres_directory']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Max Genres Shown', 'vmtheme' ); ?></label></th>
						<td><input type="number" name="genres_directory_count" value="<?php echo esc_attr( $s['genres_directory']['count'] ); ?>" min="6" max="24" class="small-text"></td>
					</tr>
				</table>
			</div>

			<!-- 7. DYNAMIC CUSTOM COLLECTION 1 -->
			<div class="vm-section-card" style="border-left:4px solid #e50914;">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-star-filled" style="color:#e50914;"></span> 7. Curated Collection 1 <span class="vm-pill-badge">Custom Section</span></h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="custom_collection_1_enabled" value="1" <?php checked( $s['custom_collection_1']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_1_title" value="<?php echo esc_attr( $s['custom_collection_1']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Section Subtitle', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_1_subtitle" value="<?php echo esc_attr( $s['custom_collection_1']['subtitle'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Pill Badge Text', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_1_badge" value="<?php echo esc_attr( $s['custom_collection_1']['badge'] ); ?>" class="regular-text" placeholder="e.g. 4K ULTRA HD"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Genre Filter (Optional)', 'vmtheme' ); ?></label></th>
						<td>
							<select name="custom_collection_1_genre">
								<option value=""><?php esc_html_e( 'All Genres', 'vmtheme' ); ?></option>
								<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : foreach ( $genres as $g ) : ?>
									<option value="<?php echo esc_attr( $g->slug ); ?>" <?php selected( $s['custom_collection_1']['genre'], $g->slug ); ?>><?php echo esc_html( $g->name ); ?></option>
								<?php endforeach; endif; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Custom Post IDs (Optional)', 'vmtheme' ); ?></label></th>
						<td>
							<input type="text" name="custom_collection_1_custom_ids" value="<?php echo esc_attr( $s['custom_collection_1']['custom_ids'] ); ?>" class="regular-text" placeholder="e.g. 12, 45, 88">
							<p class="description"><?php esc_html_e( 'Provide specific post IDs if you want exact curation.', 'vmtheme' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- 8. DYNAMIC CUSTOM COLLECTION 2 -->
			<div class="vm-section-card" style="border-left:4px solid #3b82f6;">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-superhero-alt" style="color:#3b82f6;"></span> 8. Curated Collection 2 <span class="vm-pill-badge" style="background:#dbeafe; color:#1d4ed8;">Custom Section</span></h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="custom_collection_2_enabled" value="1" <?php checked( $s['custom_collection_2']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_2_title" value="<?php echo esc_attr( $s['custom_collection_2']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Pill Badge Text', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_2_badge" value="<?php echo esc_attr( $s['custom_collection_2']['badge'] ); ?>" class="regular-text" placeholder="e.g. ANIME SENSATION"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Genre Filter (Optional)', 'vmtheme' ); ?></label></th>
						<td>
							<select name="custom_collection_2_genre">
								<option value=""><?php esc_html_e( 'All Genres', 'vmtheme' ); ?></option>
								<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : foreach ( $genres as $g ) : ?>
									<option value="<?php echo esc_attr( $g->slug ); ?>" <?php selected( $s['custom_collection_2']['genre'], $g->slug ); ?>><?php echo esc_html( $g->name ); ?></option>
								<?php endforeach; endif; ?>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<!-- 9. DYNAMIC CUSTOM COLLECTION 3 -->
			<div class="vm-section-card" style="border-left:4px solid #10b981;">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-heart" style="color:#10b981;"></span> 9. Curated Collection 3 <span class="vm-pill-badge" style="background:#d1fae5; color:#065f46;">Custom Section</span></h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="custom_collection_3_enabled" value="1" <?php checked( $s['custom_collection_3']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Section Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_3_title" value="<?php echo esc_attr( $s['custom_collection_3']['title'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Pill Badge Text', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="custom_collection_3_badge" value="<?php echo esc_attr( $s['custom_collection_3']['badge'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Genre Filter (Optional)', 'vmtheme' ); ?></label></th>
						<td>
							<select name="custom_collection_3_genre">
								<option value=""><?php esc_html_e( 'All Genres', 'vmtheme' ); ?></option>
								<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : foreach ( $genres as $g ) : ?>
									<option value="<?php echo esc_attr( $g->slug ); ?>" <?php selected( $s['custom_collection_3']['genre'], $g->slug ); ?>><?php echo esc_html( $g->name ); ?></option>
								<?php endforeach; endif; ?>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<!-- 10. FAQ & SCHEMA SECTION -->
			<div class="vm-section-card" style="border-left:4px solid #8b5cf6;">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-editor-help" style="color:#8b5cf6;"></span> 10. Streaming FAQ & Dynamic Questions Manager <span class="vm-pill-badge" style="background:#ede9fe; color:#6d28d9;">Schema.org FAQPage</span></h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="faq_section_enabled" value="1" <?php checked( $s['faq_section']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0 0 16px;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'FAQ Heading Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="faq_section_title" value="<?php echo esc_attr( $s['faq_section']['title'] ); ?>" class="large-text" placeholder="Frequently Asked Questions"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'FAQ Subtitle / Tagline', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="faq_section_subtitle" value="<?php echo esc_attr( $s['faq_section']['subtitle'] ?? '' ); ?>" class="large-text" placeholder="Everything you need to know about streaming, servers, and playback."></td>
					</tr>
				</table>

				<!-- Interactive FAQ Item Builder -->
				<div style="background:#f8fafc; padding:20px; border-radius:10px; border:1px solid #e2e8f0;">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #e2e8f0; padding-bottom:10px; flex-wrap:wrap; gap:10px;">
						<strong style="color:#0f172a; font-size:15px; display:flex; align-items:center; gap:6px;">
							<i class="dashicons dashicons-list-view"></i> <?php esc_html_e( 'Manage FAQ Questions & Answers', 'vmtheme' ); ?>
						</strong>
						<button type="button" class="button button-primary" onclick="vmthemeAddFaqItem()" style="background:#e50914; border-color:#dc2626; font-weight:700;">
							<i class="dashicons dashicons-plus-alt2"></i> <?php esc_html_e( 'Add New FAQ Question', 'vmtheme' ); ?>
						</button>
					</div>

					<div id="vmtheme-faq-items-wrap" style="display:flex; flex-direction:column; gap:14px;">
						<?php
						$faqs = $s['faq_section']['faqs'] ?? array();
						if ( empty( $faqs ) ) {
							$faqs = vmtheme_get_default_home_settings()['faq_section']['faqs'];
						}
						foreach ( $faqs as $i => $item ) :
							?>
							<div class="vmtheme-faq-card-item" style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px 18px; position:relative; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
								<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
									<span class="vmtheme-faq-index" style="font-size:12px; font-weight:800; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px;">
										<?php echo sprintf( esc_html__( 'Question #%d', 'vmtheme' ), $i + 1 ); ?>
									</span>
									<div style="display:flex; gap:6px;">
										<button type="button" class="button button-small" onclick="vmthemeMoveFaqUp(this)" title="<?php esc_attr_e( 'Move Up', 'vmtheme' ); ?>">▲</button>
										<button type="button" class="button button-small" onclick="vmthemeMoveFaqDown(this)" title="<?php esc_attr_e( 'Move Down', 'vmtheme' ); ?>">▼</button>
										<button type="button" class="button button-small" style="color:#dc2626;" onclick="vmthemeDeleteFaq(this)" title="<?php esc_attr_e( 'Delete Question', 'vmtheme' ); ?>">
											<i class="dashicons dashicons-trash" style="margin-top:2px;"></i>
										</button>
									</div>
								</div>
								<div style="margin-bottom:10px;">
									<label style="font-size:12.5px; font-weight:700; color:#334155; display:block; margin-bottom:4px;"><?php esc_html_e( 'Question:', 'vmtheme' ); ?></label>
									<input type="text" name="faq_q[]" value="<?php echo esc_attr( $item['q'] ); ?>" class="large-text" placeholder="Enter question..." required>
								</div>
								<div>
									<label style="font-size:12.5px; font-weight:700; color:#334155; display:block; margin-bottom:4px;"><?php esc_html_e( 'Answer:', 'vmtheme' ); ?></label>
									<textarea name="faq_a[]" rows="2" class="large-text" placeholder="Enter answer..." required><?php echo esc_textarea( $item['a'] ); ?></textarea>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- 11. DYNAMIC RICH CONTENT SECTION 1 -->
			<div class="vm-section-card" style="border-left:4px solid #0284c7;">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-edit-page" style="color:#0284c7;"></span> 11. Custom Editorial Section 1 <span class="vm-pill-badge" style="background:#e0f2fe; color:#0369a1;">WYSIWYG Rich Editor</span></h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="rich_content_1_enabled" value="1" <?php checked( $s['rich_content_1']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0 0 15px;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Top Pill Badge', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="rich_content_1_badge" value="<?php echo esc_attr( $s['rich_content_1']['badge'] ?? '' ); ?>" class="regular-text" placeholder="e.g. EDITORIAL SPOTLIGHT"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Section Heading Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="rich_content_1_title" value="<?php echo esc_attr( $s['rich_content_1']['title'] ); ?>" class="large-text" placeholder="Enter section heading title..."></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Section Subtitle / Tagline', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="rich_content_1_subtitle" value="<?php echo esc_attr( $s['rich_content_1']['subtitle'] ); ?>" class="large-text" placeholder="Enter supporting subtitle..."></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Rich Description & Content', 'vmtheme' ); ?></label></th>
						<td>
							<?php
							wp_editor(
								$s['rich_content_1']['content'] ?? '',
								'rich_content_1_content',
								array(
									'textarea_name' => 'rich_content_1_content',
									'textarea_rows' => 8,
									'teeny'         => false,
									'media_buttons' => true,
									'quicktags'     => true,
								)
							);
							?>
							<p class="description" style="margin-top:6px; color:#64748b;">
								<?php esc_html_e( 'Use rich text formatting, paragraphs, lists, bold text, links, or embedded media.', 'vmtheme' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- 12. DYNAMIC RICH CONTENT SECTION 2 -->
			<div class="vm-section-card" style="border-left:4px solid #10b981;">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-media-document" style="color:#10b981;"></span> 12. Custom Editorial Section 2 <span class="vm-pill-badge" style="background:#d1fae5; color:#065f46;">WYSIWYG Rich Editor</span></h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="rich_content_2_enabled" value="1" <?php checked( $s['rich_content_2']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0 0 15px;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Top Pill Badge', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="rich_content_2_badge" value="<?php echo esc_attr( $s['rich_content_2']['badge'] ?? '' ); ?>" class="regular-text" placeholder="e.g. STREAMING GUIDE & TIPS"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Section Heading Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="rich_content_2_title" value="<?php echo esc_attr( $s['rich_content_2']['title'] ); ?>" class="large-text" placeholder="Enter section heading title..."></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Section Subtitle / Tagline', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="rich_content_2_subtitle" value="<?php echo esc_attr( $s['rich_content_2']['subtitle'] ); ?>" class="large-text" placeholder="Enter supporting subtitle..."></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Rich Description & Content', 'vmtheme' ); ?></label></th>
						<td>
							<?php
							wp_editor(
								$s['rich_content_2']['content'] ?? '',
								'rich_content_2_content',
								array(
									'textarea_name' => 'rich_content_2_content',
									'textarea_rows' => 8,
									'teeny'         => false,
									'media_buttons' => true,
									'quicktags'     => true,
								)
							);
							?>
							<p class="description" style="margin-top:6px; color:#64748b;">
								<?php esc_html_e( 'Use rich text formatting, paragraphs, lists, bold text, links, or embedded media.', 'vmtheme' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- 13. CTA COMMUNITY BANNER -->
			<div class="vm-section-card">
				<div class="vm-card-header">
					<h2 class="vm-card-title"><span class="dashicons dashicons-megaphone" style="color:#e50914;"></span> 13. Community Request CTA Banner</h2>
					<label class="vm-switch-label">
						<input type="checkbox" name="cta_banner_enabled" value="1" <?php checked( $s['cta_banner']['enabled'], 1 ); ?>>
						<span><?php esc_html_e( 'Enabled on Homepage', 'vmtheme' ); ?></span>
					</label>
				</div>
				<table class="form-table" style="margin:0;">
					<tr>
						<th scope="row" style="width:200px;"><label><?php esc_html_e( 'Banner Title', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="cta_banner_title" value="<?php echo esc_attr( $s['cta_banner']['title'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Banner Subtitle', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="cta_banner_subtitle" value="<?php echo esc_attr( $s['cta_banner']['subtitle'] ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Button Text', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="cta_banner_btn_text" value="<?php echo esc_attr( $s['cta_banner']['btn_text'] ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Button URL', 'vmtheme' ); ?></label></th>
						<td><input type="text" name="cta_banner_btn_url" value="<?php echo esc_attr( $s['cta_banner']['btn_url'] ); ?>" class="large-text"></td>
					</tr>
				</table>
			</div>

			<p class="submit" style="margin-top:25px;">
				<input type="submit" name="vm_save_home_settings" id="submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save All Homepage Settings', 'vmtheme' ); ?>" style="font-size:15px; font-weight:700; padding:6px 24px;">
			</p>
		</form>

		<script>
		function vmthemeAddFaqItem() {
			var wrap = document.getElementById('vmtheme-faq-items-wrap');
			if (!wrap) return;
			var count = wrap.children.length + 1;
			var div = document.createElement('div');
			div.className = 'vmtheme-faq-card-item';
			div.style.cssText = 'background:#fff; border:1px solid #cbd5e1; border-radius:8px; padding:16px 18px; position:relative; box-shadow:0 1px 3px rgba(0,0,0,0.04);';
			div.innerHTML = '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">' +
				'<span class="vmtheme-faq-index" style="font-size:12px; font-weight:800; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px;">Question #' + count + '</span>' +
				'<div style="display:flex; gap:6px;">' +
					'<button type="button" class="button button-small" onclick="vmthemeMoveFaqUp(this)" title="Move Up">▲</button>' +
					'<button type="button" class="button button-small" onclick="vmthemeMoveFaqDown(this)" title="Move Down">▼</button>' +
					'<button type="button" class="button button-small" style="color:#dc2626;" onclick="vmthemeDeleteFaq(this)" title="Delete Question"><span class="dashicons dashicons-trash" style="margin-top:2px;"></span></button>' +
				'</div>' +
			'</div>' +
			'<div style="margin-bottom:10px;">' +
				'<label style="font-size:12.5px; font-weight:700; color:#334155; display:block; margin-bottom:4px;">Question:</label>' +
				'<input type="text" name="faq_q[]" value="" class="large-text" placeholder="Enter question..." required>' +
			'</div>' +
			'<div>' +
				'<label style="font-size:12.5px; font-weight:700; color:#334155; display:block; margin-bottom:4px;">Answer:</label>' +
				'<textarea name="faq_a[]" rows="2" class="large-text" placeholder="Enter answer..." required></textarea>' +
			'</div>';
			wrap.appendChild(div);
			vmthemeReindexFaqs();
		}

		function vmthemeDeleteFaq(btn) {
			var item = btn.closest('.vmtheme-faq-card-item');
			if (!item) return;
			var wrap = document.getElementById('vmtheme-faq-items-wrap');
			if (wrap.querySelectorAll('.vmtheme-faq-card-item').length <= 1) {
				alert('You must keep at least one FAQ item or toggle the FAQ Section to Disabled.');
				return;
			}
			if (confirm('Are you sure you want to remove this FAQ item?')) {
				item.remove();
				vmthemeReindexFaqs();
			}
		}

		function vmthemeMoveFaqUp(btn) {
			var item = btn.closest('.vmtheme-faq-card-item');
			if (item && item.previousElementSibling) {
				item.parentNode.insertBefore(item, item.previousElementSibling);
				vmthemeReindexFaqs();
			}
		}

		function vmthemeMoveFaqDown(btn) {
			var item = btn.closest('.vmtheme-faq-card-item');
			if (item && item.nextElementSibling) {
				item.parentNode.insertBefore(item.nextElementSibling, item);
				vmthemeReindexFaqs();
			}
		}

		function vmthemeReindexFaqs() {
			var wrap = document.getElementById('vmtheme-faq-items-wrap');
			if (!wrap) return;
			var items = wrap.querySelectorAll('.vmtheme-faq-card-item');
			items.forEach(function(item, idx) {
				var badge = item.querySelector('.vmtheme-faq-index');
				if (badge) {
					badge.textContent = 'Question #' + (idx + 1);
				}
			});
		}
		</script>
	</div>
	<?php
}

/**
 * ══════════════════════════════════════════════════════════════
 * HOMEPAGE RENDERING PIPELINE WITH CONDITIONAL VALIDATION
 * ══════════════════════════════════════════════════════════════
 */

/**
 * Render Curated Dynamic Collection Section (Gracefully checks content existence)
 *
 * @param array $config
 */
function vmtheme_render_curated_collection( $config ) {
	if ( empty( $config['enabled'] ) ) {
		return;
	}

	$post_type = $config['post_type'] ?? 'movies';
	$types     = ( $post_type === 'both' ) ? array( 'movies', 'tvshows' ) : array( $post_type );
	$count     = (int) ( $config['count'] ?? 6 );
	$genre     = $config['genre'] ?? '';
	$ids_str   = $config['custom_ids'] ?? '';

	$args = array(
		'post_type'      => $types,
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( ! empty( $ids_str ) ) {
		$ids = array_map( 'intval', explode( ',', $ids_str ) );
		$args['post__in'] = $ids;
		$args['orderby']  = 'post__in';
	} elseif ( ! empty( $genre ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'genres',
				'field'    => 'slug',
				'terms'    => $genre,
			),
		);
	}

	$query = new WP_Query( $args );

	// Graceful conditional check: If no published items exist, skip completely
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return;
	}

	$badge = $config['badge'] ?? '';
	$title = $config['title'] ?? 'Featured Spotlight';
	?>
	<section class="doodh-home-section doodh-curated-section" style="margin-bottom:45px;">
		<div class="doodh-section-header" style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:20px;">
			<div>
				<div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
					<?php if ( ! empty( $badge ) ) : ?>
						<span class="doodh-badge-curated" style="background:var(--dt-primary); color:#fff; font-size:10px; font-weight:800; padding:2px 8px; border-radius:4px; letter-spacing:0.5px;"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
				</div>
				<h2 class="doodh-section-title" style="margin:0; font-size:22px; font-weight:800; color:#fff;">
					<i class="fas fa-sparkles" style="color:var(--dt-primary); margin-right:6px;"></i><?php echo esc_html( $title ); ?>
				</h2>
				<?php if ( ! empty( $config['subtitle'] ) ) : ?>
					<p style="color:var(--dt-text-muted); font-size:13.5px; margin:4px 0 0;"><?php echo esc_html( $config['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $genre ) ) : ?>
				<a href="<?php echo esc_url( home_url( '/genres/' . $genre . '/' ) ); ?>" class="doodh-view-all" style="font-size:13px; font-weight:700; color:var(--dt-primary); text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
					<?php esc_html_e( 'Explore All', 'vmtheme' ); ?> <i class="fas fa-chevron-right" style="font-size:11px;"></i>
				</a>
			<?php endif; ?>
		</div>

		<div class="doodh-grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$p_id    = get_the_ID();
				$p_type  = get_post_type();
				$poster  = function_exists( 'vmtheme_get_poster_url' ) ? vmtheme_get_poster_url( $p_id ) : '';
				$rating  = function_exists( 'vmtheme_get_rating' ) ? vmtheme_get_rating( $p_id ) : '7.5';
				$year    = function_exists( 'vmtheme_get_release_year' ) ? vmtheme_get_release_year( $p_id ) : '';
				$quality = function_exists( 'vmtheme_get_quality_badge' ) ? vmtheme_get_quality_badge( $p_id ) : 'HD';
				?>
				<article class="doodh-card">
					<div class="doodh-card-poster-wrap">
						<img src="<?php echo esc_url( $poster ); ?>" 
							 class="doodh-card-poster" 
							 alt="<?php echo esc_attr( function_exists( 'vmtheme_get_poster_alt' ) ? vmtheme_get_poster_alt( $p_id ) : get_the_title() ); ?>" 
							 loading="lazy" 
							 decoding="async" 
							 width="300" 
							 height="450" 
							 onerror="this.onerror=null;this.src='<?php echo esc_url( function_exists( 'vmtheme_get_fallback_poster_url' ) ? vmtheme_get_fallback_poster_url() : '' ); ?>';">
						
						<span class="doodh-badge-top-left doodh-badge-quality"><?php echo esc_html( $quality ); ?></span>
						<span class="doodh-badge-top-right"><i class="fas fa-star"></i> <?php echo esc_html( $rating ); ?></span>
						
						<a href="<?php the_permalink(); ?>" class="doodh-card-overlay" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
							<div class="doodh-play-circle"><i class="fas fa-play"></i></div>
						</a>
					</div>
					<div class="doodh-card-body">
						<h3 class="doodh-card-title">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
						</h3>
						<div class="doodh-card-meta">
							<span class="doodh-card-year"><?php echo esc_html( $year ); ?></span>
							<span class="doodh-card-type"><?php echo ( $p_type === 'movies' ) ? esc_html__( 'Movie', 'vmtheme' ) : esc_html__( 'TV Series', 'vmtheme' ); ?></span>
						</div>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

/**
 * Render Streaming Platform FAQ & Schema Section
 *
 * @param array $config
 */
function vmtheme_render_faq_section( $config ) {
	if ( empty( $config['enabled'] ) ) {
		return;
	}

	$faqs = $config['faqs'] ?? array();
	if ( empty( $faqs ) ) {
		return;
	}

	$title    = $config['title'] ?? 'Frequently Asked Questions';
	$subtitle = $config['subtitle'] ?? 'Everything you need to know about streaming, servers, and playback.';
	?>
	<section class="doodh-home-section doodh-faq-home-section" style="background:#0f172a; border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:36px 30px; margin-bottom:45px; box-shadow:0 10px 30px rgba(0,0,0,0.35);">
		<div style="text-align:center; max-width:720px; margin:0 auto 30px;">
			<span style="background:rgba(229,9,20,0.15); color:var(--dt-primary); font-size:11px; font-weight:800; padding:4px 12px; border-radius:20px; text-transform:uppercase; display:inline-flex; align-items:center; gap:6px; margin-bottom:10px; letter-spacing:0.5px;">
				<i class="fas fa-question-circle"></i> <?php esc_html_e( 'Help Center & Quick Answers', 'vmtheme' ); ?>
			</span>
			<h2 style="font-size:26px; font-weight:800; color:#fff; margin:0 0 8px; letter-spacing:-0.5px;"><?php echo esc_html( $title ); ?></h2>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p style="color:var(--dt-text-muted); font-size:14.5px; margin:0; line-height:1.5;"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>

		<div class="vm-faq-accordion-container" style="max-width:900px; margin:0 auto; display:flex; flex-direction:column; gap:12px;">
			<?php foreach ( $faqs as $index => $item ) : 
				$is_open = ( $index === 0 );
			?>
				<div class="vm-faq-accordion-item <?php echo $is_open ? 'is-active' : ''; ?>" style="background:#1e293b; border:1px solid <?php echo $is_open ? 'rgba(229,9,20,0.4)' : 'rgba(255,255,255,0.06)'; ?>; border-radius:10px; overflow:hidden; transition:all 0.25s ease;">
					<button type="button" class="vm-faq-toggle-btn" onclick="vmthemeToggleFaq(this)" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" style="width:100%; text-align:left; background:transparent; border:none; padding:18px 22px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; color:#fff; font-size:15.5px; font-weight:700; gap:16px; outline:none;">
						<span style="display:flex; align-items:center; gap:12px;">
							<span style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; background:rgba(229,9,20,0.15); color:var(--dt-primary); font-size:12px; font-weight:800; flex-shrink:0;">
								<?php echo esc_html( $index + 1 ); ?>
							</span>
							<span><?php echo esc_html( $item['q'] ); ?></span>
						</span>
						<span class="vm-faq-icon" style="color:var(--dt-primary); font-size:14px; transition:transform 0.3s ease; transform:<?php echo $is_open ? 'rotate(180deg)' : 'rotate(0deg)'; ?>; flex-shrink:0;">
							<i class="fas fa-chevron-down"></i>
						</span>
					</button>
					<div class="vm-faq-content-panel" style="<?php echo $is_open ? 'display:block;' : 'display:none;'; ?> padding:0 22px 20px 60px; color:#94a3b8; font-size:14px; line-height:1.65; border-top:1px solid rgba(255,255,255,0.04);">
						<div style="padding-top:12px;">
							<?php echo nl2br( esc_html( $item['a'] ) ); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<script>
		function vmthemeToggleFaq(btn) {
			var item = btn.closest('.vm-faq-accordion-item');
			if (!item) return;
			var panel = item.querySelector('.vm-faq-content-panel');
			var icon = item.querySelector('.vm-faq-icon');
			var isOpen = item.classList.contains('is-active');

			if (isOpen) {
				item.classList.remove('is-active');
				item.style.borderColor = 'rgba(255,255,255,0.06)';
				btn.setAttribute('aria-expanded', 'false');
				if (panel) panel.style.display = 'none';
				if (icon) icon.style.transform = 'rotate(0deg)';
			} else {
				item.classList.add('is-active');
				item.style.borderColor = 'rgba(229,9,20,0.4)';
				btn.setAttribute('aria-expanded', 'true');
				if (panel) panel.style.display = 'block';
				if (icon) icon.style.transform = 'rotate(180deg)';
			}
		}
		</script>

		<!-- Schema.org FAQPage Structured Data -->
		<script type="application/ld+json">
		<?php
		$faq_schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(),
		);
		foreach ( $faqs as $faq ) {
			if ( ! empty( $faq['q'] ) && ! empty( $faq['a'] ) ) {
				$faq_schema['mainEntity'][] = array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $faq['q'] ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $faq['a'] ),
					),
				);
			}
		}
		echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		?>
		</script>
	</section>
	<?php
}

/**
 * Render Dynamic Rich Content Editorial Section (Gracefully checks content existence)
 *
 * @param array  $config
 * @param string $section_id
 */
function vmtheme_render_rich_content_section( $config, $section_id = 'rich-content' ) {
	if ( empty( $config['enabled'] ) ) {
		return;
	}

	$content = $config['content'] ?? '';
	// Graceful conditional check: If content is empty or contains only whitespace/empty tags, skip completely
	if ( empty( trim( strip_tags( $content ) ) ) ) {
		return;
	}

	$title    = $config['title'] ?? '';
	$subtitle = $config['subtitle'] ?? '';
	$badge    = $config['badge'] ?? '';
	?>
	<section class="doodh-home-section doodh-rich-content-section" id="<?php echo esc_attr( $section_id ); ?>" style="background:#0f172a; border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:36px 32px; margin-bottom:45px; box-shadow:0 10px 30px rgba(0,0,0,0.35);">
		<?php if ( ! empty( $title ) || ! empty( $badge ) ) : ?>
			<div class="doodh-section-header" style="margin-bottom:22px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:18px;">
				<?php if ( ! empty( $badge ) ) : ?>
					<div style="margin-bottom:8px;">
						<span style="background:rgba(229,9,20,0.15); color:var(--dt-primary); font-size:11px; font-weight:800; padding:3px 10px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px;">
							<i class="fas fa-bookmark" style="margin-right:4px;"></i><?php echo esc_html( $badge ); ?>
						</span>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="doodh-section-title" style="margin:0; font-size:22px; font-weight:800; color:#fff; letter-spacing:-0.4px;">
						<?php echo esc_html( $title ); ?>
					</h2>
				<?php endif; ?>
				<?php if ( ! empty( $subtitle ) ) : ?>
					<p style="color:var(--dt-text-muted); font-size:14px; margin:6px 0 0; line-height:1.5;"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="vm-rich-content-body" style="color:#cbd5e1; font-size:15px; line-height:1.8;">
			<?php echo do_shortcode( wpautop( $content ) ); ?>
		</div>
	</section>
	<?php
}

/**
 * Render Request CTA Banner
 *
 * @param array $config
 */
function vmtheme_render_cta_banner( $config ) {
	if ( empty( $config['enabled'] ) ) {
		return;
	}

	$title    = $config['title'] ?? "Can't Find Your Favorite Movie or TV Show?";
	$subtitle = $config['subtitle'] ?? 'Submit a title request and our team will add streaming links within 24 hours.';
	$btn_text = $config['btn_text'] ?? 'Submit a Title Request';
	$btn_url  = $config['btn_url'] ?? home_url( '/request/' );
	?>
	<section class="doodh-home-section doodh-cta-banner-wrap" style="margin-bottom:45px;">
		<div style="background:linear-gradient(135deg,rgba(229,9,20,0.18),rgba(15,23,42,0.95)), #0f172a; border:1px solid rgba(229,9,20,0.3); border-radius:16px; padding:36px 32px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; box-shadow:0 10px 30px rgba(0,0,0,0.4);">
			<div style="max-width:620px;">
				<span style="background:var(--dt-primary); color:#fff; font-size:11px; font-weight:800; padding:2px 8px; border-radius:4px; text-transform:uppercase; display:inline-block; margin-bottom:8px;">
					<i class="fas fa-bolt"></i> <?php esc_html_e( '24/7 Fast Indexing', 'vmtheme' ); ?>
				</span>
				<h2 style="color:#fff; font-size:24px; font-weight:800; margin:0 0 6px; letter-spacing:-0.5px;"><?php echo esc_html( $title ); ?></h2>
				<p style="color:#cbd5e1; font-size:14px; margin:0; line-height:1.5;"><?php echo esc_html( $subtitle ); ?></p>
			</div>
			<div>
				<a href="<?php echo esc_url( $btn_url ); ?>" class="doodh-btn-primary" style="padding:12px 26px; font-size:14px; font-weight:700; border-radius:8px; display:inline-flex; align-items:center; gap:8px;">
					<i class="fas fa-plus-circle"></i> <span><?php echo esc_html( $btn_text ); ?></span>
				</a>
			</div>
		</div>
	</section>
	<?php
}
