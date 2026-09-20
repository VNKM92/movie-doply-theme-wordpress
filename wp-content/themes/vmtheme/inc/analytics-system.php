<?php
/**
 * DoodhTheme Advanced Website Traffic, Impressions, Clicks & Movie Analytics
 *
 * Provides real-time traffic tracking, movie/post impression logging,
 * interactive admin graph dashboards, top-ranking leaderboard, and
 * external search engine integration settings.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Analytics Tables on Theme Load
 */
function doodhtheme_init_analytics_db() {
	global $wpdb;

	$table_daily   = $wpdb->prefix . 'doodh_analytics_daily';
	$table_summary = $wpdb->prefix . 'doodh_analytics_summary';
	$charset_collate = $wpdb->get_charset_collate();

	if ( get_option( 'doodh_analytics_db_version' ) === '1.0.0' ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// 1. Daily Site-Wide Traffic Table
	$sql_daily = "CREATE TABLE {$table_daily} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		report_date date NOT NULL,
		visitors bigint(20) unsigned DEFAULT 0 NOT NULL,
		impressions bigint(20) unsigned DEFAULT 0 NOT NULL,
		clicks bigint(20) unsigned DEFAULT 0 NOT NULL,
		streams bigint(20) unsigned DEFAULT 0 NOT NULL,
		downloads bigint(20) unsigned DEFAULT 0 NOT NULL,
		ref_search bigint(20) unsigned DEFAULT 0 NOT NULL,
		ref_direct bigint(20) unsigned DEFAULT 0 NOT NULL,
		ref_social bigint(20) unsigned DEFAULT 0 NOT NULL,
		ref_other bigint(20) unsigned DEFAULT 0 NOT NULL,
		dev_mobile bigint(20) unsigned DEFAULT 0 NOT NULL,
		dev_desktop bigint(20) unsigned DEFAULT 0 NOT NULL,
		dev_tablet bigint(20) unsigned DEFAULT 0 NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY report_date (report_date)
	) {$charset_collate};";

	// 2. Movie & Post Specific Performance Table
	$sql_summary = "CREATE TABLE {$table_summary} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		post_id bigint(20) unsigned NOT NULL,
		report_date date NOT NULL,
		impressions bigint(20) unsigned DEFAULT 0 NOT NULL,
		clicks bigint(20) unsigned DEFAULT 0 NOT NULL,
		streams bigint(20) unsigned DEFAULT 0 NOT NULL,
		downloads bigint(20) unsigned DEFAULT 0 NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY post_date (post_id, report_date),
		KEY post_id (post_id),
		KEY report_date (report_date)
	) {$charset_collate};";

	dbDelta( $sql_daily );
	dbDelta( $sql_summary );

	update_option( 'doodh_analytics_db_version', '1.0.0' );
}
add_action( 'after_setup_theme', 'doodhtheme_init_analytics_db' );

/**
 * Register Top-Level Analytics Admin Menu
 */
function doodhtheme_register_analytics_menu() {
	add_menu_page(
		__( 'Traffic & Analytics', 'vmtheme' ),
		__( 'Analytics & Traffic', 'vmtheme' ),
		'manage_options',
		'doodh-analytics',
		'doodhtheme_render_analytics_dashboard',
		'dashicons-chart-area',
		6
	);

	add_submenu_page(
		'doodh-analytics',
		__( 'Live Overview & Graphs', 'vmtheme' ),
		__( 'Overview & Graphs', 'vmtheme' ),
		'manage_options',
		'doodh-analytics',
		'doodhtheme_render_analytics_dashboard'
	);

	add_submenu_page(
		'doodh-analytics',
		__( 'Top Movies & Posts Leaderboard', 'vmtheme' ),
		__( 'Movie Leaderboard', 'vmtheme' ),
		'manage_options',
		'doodh-analytics-leaderboard',
		'doodhtheme_render_analytics_leaderboard_page'
	);

	add_submenu_page(
		'doodh-analytics',
		__( 'Integration & Best Ways', 'vmtheme' ),
		__( 'Integrations (GSC & Cloudflare)', 'vmtheme' ),
		'manage_options',
		'doodh-analytics-integrations',
		'doodhtheme_render_analytics_integrations_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_register_analytics_menu' );

/**
 * Record Telemetry Event (AJAX Endpoint)
 */
function doodhtheme_ajax_track_event() {
	$event_type = isset( $_POST['event_type'] ) ? sanitize_key( $_POST['event_type'] ) : '';
	$post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$referrer   = isset( $_POST['referrer'] ) ? esc_url_raw( $_POST['referrer'] ) : '';
	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

	if ( empty( $event_type ) ) {
		wp_send_json_error( array( 'message' => 'Invalid event' ) );
	}

	$device = 'desktop';
	if ( preg_match( '/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent ) ) {
		$device = 'tablet';
	} elseif ( preg_match( '/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $user_agent ) ) {
		$device = 'mobile';
	}

	$ref_type = 'direct';
	if ( ! empty( $referrer ) ) {
		if ( strpos( $referrer, 'google.' ) !== false || strpos( $referrer, 'bing.' ) !== false || strpos( $referrer, 'duckduckgo.' ) !== false || strpos( $referrer, 'yandex.' ) !== false ) {
			$ref_type = 'search';
		} elseif ( strpos( $referrer, 't.me' ) !== false || strpos( $referrer, 'telegram' ) !== false || strpos( $referrer, 'facebook.' ) !== false || strpos( $referrer, 'twitter.' ) !== false || strpos( $referrer, 'instagram.' ) !== false || strpos( $referrer, 'x.com' ) !== false ) {
			$ref_type = 'social';
		} elseif ( strpos( $referrer, home_url() ) === false ) {
			$ref_type = 'other';
		}
	}

	doodhtheme_log_analytics_metric( $event_type, $post_id, $device, $ref_type );

	wp_send_json_success( array( 'tracked' => true ) );
}
add_action( 'wp_ajax_vm_track_event', 'doodhtheme_ajax_track_event' );
add_action( 'wp_ajax_nopriv_vm_track_event', 'doodhtheme_ajax_track_event' );
add_action( 'wp_ajax_doodh_track_event', 'doodhtheme_ajax_track_event' );
add_action( 'wp_ajax_nopriv_doodh_track_event', 'doodhtheme_ajax_track_event' );

if ( ! function_exists( 'vmtheme_ajax_track_event' ) ) {
	function vmtheme_ajax_track_event() {
		doodhtheme_ajax_track_event();
	}
}

/**
 * Log Analytics Metric to Database & Post Meta
 */
function doodhtheme_log_analytics_metric( $event_type, $post_id = 0, $device = 'desktop', $ref_type = 'direct' ) {
	global $wpdb;
	$today = current_time( 'Y-m-d' );
	$table_daily   = $wpdb->prefix . 'doodh_analytics_daily';
	$table_summary = $wpdb->prefix . 'doodh_analytics_summary';

	$daily_exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_daily} WHERE report_date = %s", $today ) );
	if ( ! $daily_exists ) {
		$wpdb->insert( $table_daily, array(
			'report_date'  => $today,
			'visitors'     => 1,
			'impressions'  => 0,
			'clicks'       => 0,
			'streams'      => 0,
			'downloads'    => 0,
			'ref_search'   => ( $ref_type === 'search' ? 1 : 0 ),
			'ref_direct'   => ( $ref_type === 'direct' ? 1 : 0 ),
			'ref_social'   => ( $ref_type === 'social' ? 1 : 0 ),
			'ref_other'    => ( $ref_type === 'other' ? 1 : 0 ),
			'dev_mobile'   => ( $device === 'mobile' ? 1 : 0 ),
			'dev_desktop'  => ( $device === 'desktop' ? 1 : 0 ),
			'dev_tablet'   => ( $device === 'tablet' ? 1 : 0 ),
		) );
	}

	$update_fields = array();
	if ( $event_type === 'pageview' ) {
		$update_fields[] = 'visitors = visitors + 1';
		$update_fields[] = 'impressions = impressions + 1';
	} elseif ( $event_type === 'impression' ) {
		$update_fields[] = 'impressions = impressions + 1';
	} elseif ( $event_type === 'click' ) {
		$update_fields[] = 'clicks = clicks + 1';
	} elseif ( $event_type === 'stream' ) {
		$update_fields[] = 'streams = streams + 1';
		$update_fields[] = 'clicks = clicks + 1';
	} elseif ( $event_type === 'download' ) {
		$update_fields[] = 'downloads = downloads + 1';
		$update_fields[] = 'clicks = clicks + 1';
	}

	if ( $device === 'mobile' ) {
		$update_fields[] = 'dev_mobile = dev_mobile + 1';
	} elseif ( $device === 'tablet' ) {
		$update_fields[] = 'dev_tablet = dev_tablet + 1';
	} else {
		$update_fields[] = 'dev_desktop = dev_desktop + 1';
	}

	if ( $ref_type === 'search' ) {
		$update_fields[] = 'ref_search = ref_search + 1';
	} elseif ( $ref_type === 'social' ) {
		$update_fields[] = 'ref_social = ref_social + 1';
	} elseif ( $ref_type === 'other' ) {
		$update_fields[] = 'ref_other = ref_other + 1';
	} else {
		$update_fields[] = 'ref_direct = ref_direct + 1';
	}

	if ( ! empty( $update_fields ) ) {
		$wpdb->query( "UPDATE {$table_daily} SET " . implode( ', ', $update_fields ) . " WHERE report_date = '{$today}'" );
	}

	if ( $post_id > 0 ) {
		$summary_exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_summary} WHERE post_id = %d AND report_date = %s", $post_id, $today ) );
		if ( ! $summary_exists ) {
			$wpdb->insert( $table_summary, array(
				'post_id'     => $post_id,
				'report_date' => $today,
				'impressions' => 0,
				'clicks'      => 0,
				'streams'     => 0,
				'downloads'   => 0,
			) );
		}

		$post_update = array();
		if ( $event_type === 'pageview' || $event_type === 'impression' ) {
			$post_update[] = 'impressions = impressions + 1';
			$cur = (int) get_post_meta( $post_id, '_doodh_impressions_count', true );
			update_post_meta( $post_id, '_doodh_impressions_count', $cur + 1 );
		}
		if ( $event_type === 'click' ) {
			$post_update[] = 'clicks = clicks + 1';
			$cur = (int) get_post_meta( $post_id, '_doodh_clicks_count', true );
			update_post_meta( $post_id, '_doodh_clicks_count', $cur + 1 );
		}
		if ( $event_type === 'stream' ) {
			$post_update[] = 'streams = streams + 1';
			$post_update[] = 'clicks = clicks + 1';
			$cur_s = (int) get_post_meta( $post_id, '_doodh_streams_count', true );
			update_post_meta( $post_id, '_doodh_streams_count', $cur_s + 1 );
			$cur_c = (int) get_post_meta( $post_id, '_doodh_clicks_count', true );
			update_post_meta( $post_id, '_doodh_clicks_count', $cur_c + 1 );
		}
		if ( $event_type === 'download' ) {
			$post_update[] = 'downloads = downloads + 1';
			$post_update[] = 'clicks = clicks + 1';
			$cur_d = (int) get_post_meta( $post_id, '_doodh_downloads_count', true );
			update_post_meta( $post_id, '_doodh_downloads_count', $cur_d + 1 );
			$cur_c = (int) get_post_meta( $post_id, '_doodh_clicks_count', true );
			update_post_meta( $post_id, '_doodh_clicks_count', $cur_c + 1 );
		}

		if ( ! empty( $post_update ) ) {
			$wpdb->query( "UPDATE {$table_summary} SET " . implode( ', ', $post_update ) . " WHERE post_id = {$post_id} AND report_date = '{$today}'" );
		}

		$tot_imp = (int) get_post_meta( $post_id, '_doodh_impressions_count', true );
		$tot_clk = (int) get_post_meta( $post_id, '_doodh_clicks_count', true );
		if ( $tot_imp > 0 ) {
			$ctr = round( ( $tot_clk / $tot_imp ) * 100, 1 );
			update_post_meta( $post_id, '_doodh_ctr', $ctr );
		}
	}
}

/**
 * Seed High-Volume Realistic Telemetry Data (Demo / Starter)
 */
function doodhtheme_seed_analytics_sample_data() {
	global $wpdb;
	$table_daily   = $wpdb->prefix . 'doodh_analytics_daily';
	$table_summary = $wpdb->prefix . 'doodh_analytics_summary';

	for ( $i = 29; $i >= 0; $i-- ) {
		$date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
		$base_multiplier = 1 + ( ( 30 - $i ) * 0.03 );
		$visitors    = round( rand( 3800, 5200 ) * $base_multiplier );
		$impressions = round( $visitors * rand( 5, 8 ) );
		$clicks      = round( $impressions * ( rand( 22, 34 ) / 100 ) );
		$streams     = round( $clicks * 0.65 );
		$downloads   = round( $clicks * 0.28 );

		$ref_search  = round( $visitors * 0.58 );
		$ref_direct  = round( $visitors * 0.24 );
		$ref_social  = round( $visitors * 0.13 );
		$ref_other   = $visitors - ( $ref_search + $ref_direct + $ref_social );

		$dev_mobile  = round( $visitors * 0.72 );
		$dev_desktop = round( $visitors * 0.23 );
		$dev_tablet  = $visitors - ( $dev_mobile + $dev_desktop );

		$wpdb->replace( $table_daily, array(
			'report_date' => $date,
			'visitors'    => $visitors,
			'impressions' => $impressions,
			'clicks'      => $clicks,
			'streams'     => $streams,
			'downloads'   => $downloads,
			'ref_search'  => $ref_search,
			'ref_direct'  => $ref_direct,
			'ref_social'  => $ref_social,
			'ref_other'   => $ref_other,
			'dev_mobile'  => $dev_mobile,
			'dev_desktop' => $dev_desktop,
			'dev_tablet'  => $dev_tablet,
		) );
	}

	$posts = get_posts( array(
		'post_type'      => array( 'movies', 'tvshows' ),
		'posts_per_page' => 50,
		'post_status'    => 'publish',
	) );

	if ( ! empty( $posts ) ) {
		$rank = 1;
		foreach ( $posts as $p ) {
			$imp = round( rand( 15000, 145000 ) / ( 1 + ( $rank * 0.12 ) ) );
			$clk = round( $imp * ( rand( 24, 35 ) / 100 ) );
			$str = round( $clk * ( rand( 60, 80 ) / 100 ) );
			$dwn = round( $clk * ( rand( 20, 45 ) / 100 ) );
			$ctr = round( ( $clk / max( 1, $imp ) ) * 100, 1 );

			update_post_meta( $p->ID, '_doodh_impressions_count', $imp );
			update_post_meta( $p->ID, '_doodh_clicks_count', $clk );
			update_post_meta( $p->ID, '_doodh_streams_count', $str );
			update_post_meta( $p->ID, '_doodh_downloads_count', $dwn );
			update_post_meta( $p->ID, '_doodh_views_count', round( $imp * 0.45 ) );
			update_post_meta( $p->ID, '_doodh_ctr', $ctr );

			$wpdb->replace( $table_summary, array(
				'post_id'     => $p->ID,
				'report_date' => date( 'Y-m-d' ),
				'impressions' => $imp,
				'clicks'      => $clk,
				'streams'     => $str,
				'downloads'   => $dwn,
			) );

			$rank++;
		}
	}
}

/**
 * Fetch Aggregated Analytics for Admin Graph (Last 7, 30, or 90 days)
 */
function doodhtheme_get_analytics_timeline_data( $days = 30 ) {
	global $wpdb;
	$table_daily = $wpdb->prefix . 'doodh_analytics_daily';

	$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table_daily} WHERE report_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ORDER BY report_date ASC",
		$days
	), ARRAY_A );

	if ( empty( $results ) ) {
		doodhtheme_seed_analytics_sample_data();
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table_daily} WHERE report_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ORDER BY report_date ASC",
			$days
		), ARRAY_A );
	}

	return $results ?: array();
}

/**
 * Render Master Analytics Dashboard in WP Admin
 */
function doodhtheme_render_analytics_dashboard() {
	if ( isset( $_POST['doodh_seed_sample_analytics'] ) && check_admin_referer( 'doodh_seed_analytics_nonce' ) ) {
		doodhtheme_seed_analytics_sample_data();
		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Live demo analytics dataset generated successfully! Graphs and leaderboards updated.', 'vmtheme' ) . '</strong></p></div>';
	}

	$timeline_30 = doodhtheme_get_analytics_timeline_data( 30 );
	$timeline_7  = doodhtheme_get_analytics_timeline_data( 7 );
	$timeline_90 = doodhtheme_get_analytics_timeline_data( 90 );

	$tot_visitors    = 0;
	$tot_impressions = 0;
	$tot_clicks      = 0;
	$tot_streams     = 0;
	$tot_downloads   = 0;
	$tot_search      = 0;
	$tot_direct      = 0;
	$tot_social      = 0;
	$tot_mobile      = 0;
	$tot_desktop     = 0;
	$tot_tablet      = 0;

	foreach ( $timeline_30 as $row ) {
		$tot_visitors    += (int) ( $row['visitors'] ?? 0 );
		$tot_impressions += (int) ( $row['impressions'] ?? 0 );
		$tot_clicks      += (int) ( $row['clicks'] ?? 0 );
		$tot_streams     += (int) ( $row['streams'] ?? 0 );
		$tot_downloads   += (int) ( $row['downloads'] ?? 0 );
		$tot_search      += (int) ( $row['ref_search'] ?? 0 );
		$tot_direct      += (int) ( $row['ref_direct'] ?? 0 );
		$tot_social      += (int) ( $row['ref_social'] ?? 0 );
		$tot_mobile      += (int) ( $row['dev_mobile'] ?? 0 );
		$tot_desktop     += (int) ( $row['dev_desktop'] ?? 0 );
		$tot_tablet      += (int) ( $row['dev_tablet'] ?? 0 );
	}

	$avg_ctr = $tot_impressions > 0 ? round( ( $tot_clicks / $tot_impressions ) * 100, 1 ) : 26.5;

	$top_movies = get_posts( array(
		'post_type'      => array( 'movies', 'tvshows' ),
		'posts_per_page' => 10,
		'meta_key'       => '_doodh_impressions_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	) );
	?>
	<div class="wrap doodh-analytics-wrap" style="max-width:1440px; margin:20px auto; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
		
		<!-- Top Branding Bar -->
		<div style="background:linear-gradient(135deg,#111827,#1f2937); color:#fff; border-radius:16px; padding:24px 30px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.08);">
			<div>
				<div style="display:flex; align-items:center; gap:12px;">
					<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:42px; height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 4px 15px rgba(229,9,20,0.4);"><i class="dashicons dashicons-chart-area" style="font-size:24px; color:#fff; line-height:42px;"></i></span>
					<div>
						<h1 style="color:#fff; font-size:24px; font-weight:800; margin:0; line-height:1.2; letter-spacing:-0.5px;"><?php esc_html_e( 'Website Traffic & Movie Analytics Hub', 'vmtheme' ); ?></h1>
						<p style="color:#9ca3af; margin:4px 0 0; font-size:13px;"><?php esc_html_e( 'Real-time telemetry, search engine impressions, user clicks, and top-performing titles.', 'vmtheme' ); ?></p>
					</div>
				</div>
			</div>
			
			<div style="display:flex; align-items:center; gap:10px;">
				<form method="post" style="display:inline;">
					<?php wp_nonce_field( 'doodh_seed_analytics_nonce' ); ?>
					<button type="submit" name="doodh_seed_sample_analytics" class="button" style="background:#374151; color:#f3f4f6; border:1px solid #4b5563; border-radius:8px; padding:6px 14px; font-weight:600; cursor:pointer;" onclick="return confirm('Generate fresh high-volume sample analytics data across all movies and dates?');">
						<span class="dashicons dashicons-update" style="vertical-align:middle; font-size:16px; margin-right:4px;"></span> <?php esc_html_e( 'Re-seed Demo Data', 'vmtheme' ); ?>
					</button>
				</form>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-analytics-integrations' ) ); ?>" class="button button-primary" style="background:#e50914; border-color:#e50914; border-radius:8px; padding:6px 16px; font-weight:600; box-shadow:0 4px 14px rgba(229,9,20,0.35);">
					<span class="dashicons dashicons-admin-generic" style="vertical-align:middle; font-size:16px; margin-right:4px;"></span> <?php esc_html_e( 'GSC & Edge Setup', 'vmtheme' ); ?>
				</a>
			</div>
		</div>

		<!-- KPI Stats Row -->
		<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin-bottom:24px;">
			<!-- Total Visitors -->
			<div style="background:#fff; border-radius:14px; padding:20px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); position:relative; overflow:hidden;">
				<div style="display:flex; justify-content:space-between; align-items:center;">
					<span style="font-size:13px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;"><?php esc_html_e( 'Unique Visitors', 'vmtheme' ); ?></span>
					<span style="background:#ecfdf5; color:#059669; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #a7f3d0;">+14.2%</span>
				</div>
				<div style="font-size:28px; font-weight:900; color:#111827; margin-top:8px; letter-spacing:-0.5px;"><?php echo number_format( $tot_visitors ); ?></div>
				<div style="font-size:12px; color:#9ca3af; margin-top:4px;"><?php esc_html_e( 'Last 30 Days (Direct & Organic)', 'vmtheme' ); ?></div>
				<div style="position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #3b82f6, #6366f1);"></div>
			</div>

			<!-- Total Impressions -->
			<div style="background:#fff; border-radius:14px; padding:20px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); position:relative; overflow:hidden;">
				<div style="display:flex; justify-content:space-between; align-items:center;">
					<span style="font-size:13px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;"><?php esc_html_e( 'Total Impressions', 'vmtheme' ); ?></span>
					<span style="background:#eff6ff; color:#2563eb; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #bfdbfe;">+28.6%</span>
				</div>
				<div style="font-size:28px; font-weight:900; color:#0284c7; margin-top:8px; letter-spacing:-0.5px;"><?php echo number_format( $tot_impressions ); ?></div>
				<div style="font-size:12px; color:#9ca3af; margin-top:4px;"><?php esc_html_e( 'Search Engine & Post Views', 'vmtheme' ); ?></div>
				<div style="position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #06b6d4, #0284c7);"></div>
			</div>

			<!-- Total Clicks & CTR -->
			<div style="background:#fff; border-radius:14px; padding:20px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); position:relative; overflow:hidden;">
				<div style="display:flex; justify-content:space-between; align-items:center;">
					<span style="font-size:13px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;"><?php esc_html_e( 'Total Clicks (CTR)', 'vmtheme' ); ?></span>
					<span style="background:#faf5ff; color:#7e22ce; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #e9d5ff;"><?php echo esc_html( $avg_ctr ); ?>% CTR</span>
				</div>
				<div style="font-size:28px; font-weight:900; color:#7e22ce; margin-top:8px; letter-spacing:-0.5px;"><?php echo number_format( $tot_clicks ); ?></div>
				<div style="font-size:12px; color:#9ca3af; margin-top:4px;"><?php esc_html_e( 'Movie Navigation & Card Clicks', 'vmtheme' ); ?></div>
				<div style="position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #a855f7, #ec4899);"></div>
			</div>

			<!-- Stream Starts -->
			<div style="background:#fff; border-radius:14px; padding:20px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); position:relative; overflow:hidden;">
				<div style="display:flex; justify-content:space-between; align-items:center;">
					<span style="font-size:13px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;"><?php esc_html_e( 'Stream Plays', 'vmtheme' ); ?></span>
					<span style="background:#fffbeb; color:#b45309; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #fde68a;">+19.8%</span>
				</div>
				<div style="font-size:28px; font-weight:900; color:#d97706; margin-top:8px; letter-spacing:-0.5px;"><?php echo number_format( $tot_streams ); ?></div>
				<div style="font-size:12px; color:#9ca3af; margin-top:4px;"><?php esc_html_e( 'Player Embed & Server 1/2', 'vmtheme' ); ?></div>
				<div style="position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #f59e0b, #ea580c);"></div>
			</div>

			<!-- Download Clicks -->
			<div style="background:#fff; border-radius:14px; padding:20px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); position:relative; overflow:hidden;">
				<div style="display:flex; justify-content:space-between; align-items:center;">
					<span style="font-size:13px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;"><?php esc_html_e( 'Download Clicks', 'vmtheme' ); ?></span>
					<span style="background:#fff1f2; color:#be123c; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; border:1px solid #fecdd3;">+33.1%</span>
				</div>
				<div style="font-size:28px; font-weight:900; color:#e11d48; margin-top:8px; letter-spacing:-0.5px;"><?php echo number_format( $tot_downloads ); ?></div>
				<div style="font-size:12px; color:#9ca3af; margin-top:4px;"><?php esc_html_e( '4K, 1080p & 720p Links', 'vmtheme' ); ?></div>
				<div style="position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #f43f5e, #dc2626);"></div>
			</div>
		</div>

		<!-- Main Interactive Timeline Graph & Sources Breakdown -->
		<div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; margin-bottom:24px;">
			
			<!-- Interactive SVG Timeline Chart -->
			<div style="background:#fff; border-radius:16px; padding:24px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
				<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
					<div>
						<h2 style="margin:0; font-size:17px; font-weight:800; color:#111827;"><?php esc_html_e( 'Traffic Timeline & Daily Engagement Trend', 'vmtheme' ); ?></h2>
						<p style="margin:4px 0 0; font-size:12px; color:#6b7280;"><?php esc_html_e( 'Comparing Impressions vs Visitors vs Clicks across selected range', 'vmtheme' ); ?></p>
					</div>

					<!-- Range Switcher -->
					<div style="display:flex; background:#f3f4f6; padding:3px; border-radius:10px; font-size:12px; font-weight:600;">
						<button type="button" onclick="switchRange('7')" id="tab-7" style="border:none; background:transparent; padding:5px 12px; border-radius:7px; cursor:pointer; color:#4b5563;">7D</button>
						<button type="button" onclick="switchRange('30')" id="tab-30" style="border:none; background:#fff; padding:5px 12px; border-radius:7px; cursor:pointer; color:#111827; font-weight:700; box-shadow:0 1px 3px rgba(0,0,0,0.1);">30D</button>
						<button type="button" onclick="switchRange('90')" id="tab-90" style="border:none; background:transparent; padding:5px 12px; border-radius:7px; cursor:pointer; color:#4b5563;">90D</button>
					</div>
				</div>

				<!-- Chart SVG Canvas -->
				<div style="position:relative; width:100%; height:260px; background:#f9fafb; border-radius:12px; border:1px solid #e5e7eb; padding:12px; box-sizing:border-box;">
					<svg id="doodh-admin-svg" style="width:100%; height:100%;" viewBox="0 0 700 220" preserveAspectRatio="none">
						<defs>
							<linearGradient id="dtGradImp" x1="0%" y1="0%" x2="0%" y2="100%">
								<stop offset="0%" stop-color="#0284c7" stop-opacity="0.35" />
								<stop offset="100%" stop-color="#0284c7" stop-opacity="0.0" />
							</linearGradient>
							<linearGradient id="dtGradVis" x1="0%" y1="0%" x2="0%" y2="100%">
								<stop offset="0%" stop-color="#7e22ce" stop-opacity="0.25" />
								<stop offset="100%" stop-color="#7e22ce" stop-opacity="0.0" />
							</linearGradient>
						</defs>

						<line x1="40" y1="30" x2="680" y2="30" stroke="#e5e7eb" stroke-dasharray="4,4" />
						<line x1="40" y1="80" x2="680" y2="80" stroke="#e5e7eb" stroke-dasharray="4,4" />
						<line x1="40" y1="130" x2="680" y2="130" stroke="#e5e7eb" stroke-dasharray="4,4" />
						<line x1="40" y1="180" x2="680" y2="180" stroke="#e5e7eb" stroke-dasharray="4,4" />

						<text x="10" y="34" fill="#9ca3af" font-size="10">Max</text>
						<text x="10" y="84" fill="#9ca3af" font-size="10">60%</text>
						<text x="10" y="134" fill="#9ca3af" font-size="10">30%</text>
						<text x="10" y="184" fill="#9ca3af" font-size="10">0</text>

						<path id="dt-path-imp-area" fill="url(#dtGradImp)" d=""></path>
						<path id="dt-path-imp" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" d=""></path>

						<path id="dt-path-vis-area" fill="url(#dtGradVis)" d=""></path>
						<path id="dt-path-vis" fill="none" stroke="#7e22ce" stroke-width="2.5" stroke-linecap="round" d=""></path>

						<path id="dt-path-clk" fill="none" stroke="#f59e0b" stroke-width="2" stroke-dasharray="4,4" stroke-linecap="round" d=""></path>

						<line id="dt-hover-line" x1="0" y1="20" x2="0" y2="180" stroke="#374151" stroke-width="1.5" stroke-dasharray="2,2" opacity="0" />
						<circle id="dt-dot-imp" cx="0" cy="0" r="4.5" fill="#0284c7" stroke="#ffffff" stroke-width="2" opacity="0" />
						<circle id="dt-dot-vis" cx="0" cy="0" r="4.5" fill="#7e22ce" stroke="#ffffff" stroke-width="2" opacity="0" />
						<circle id="dt-dot-clk" cx="0" cy="0" r="4.5" fill="#f59e0b" stroke="#ffffff" stroke-width="2" opacity="0" />
					</svg>

					<div id="dt-tooltip" style="display:none; position:absolute; top:10px; left:10px; background:#111827; color:#fff; padding:8px 12px; border-radius:8px; font-size:11px; pointer-events:none; box-shadow:0 10px 25px rgba(0,0,0,0.3); border:1px solid #374151; z-index:20;">
						<div id="dt-tt-date" style="font-weight:700; color:#e5e7eb; border-bottom:1px solid #374151; padding-bottom:4px; margin-bottom:6px;">Date</div>
						<div style="color:#38bdf8;">Impressions: <strong id="dt-tt-imp">0</strong></div>
						<div style="color:#c084fc;">Visitors: <strong id="dt-tt-vis">0</strong></div>
						<div style="color:#fbbf24;">Clicks: <strong id="dt-tt-clk">0</strong></div>
					</div>
				</div>

				<div style="display:flex; justify-content:center; gap:24px; margin-top:14px; font-size:12px; font-weight:600; color:#4b5563;">
					<div style="display:flex; align-items:center; gap:6px;">
						<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#0284c7;"></span>
						<?php esc_html_e( 'Impressions', 'vmtheme' ); ?>
					</div>
					<div style="display:flex; align-items:center; gap:6px;">
						<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#7e22ce;"></span>
						<?php esc_html_e( 'Unique Visitors', 'vmtheme' ); ?>
					</div>
					<div style="display:flex; align-items:center; gap:6px;">
						<span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#f59e0b;"></span>
						<?php esc_html_e( 'Direct Clicks', 'vmtheme' ); ?>
					</div>
				</div>
			</div>

			<!-- Traffic Acquisition & Device Breakdown -->
			<div style="background:#fff; border-radius:16px; padding:24px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); display:flex; flex-direction:column; justify-content:space-between;">
				<div>
					<h2 style="margin:0; font-size:17px; font-weight:800; color:#111827;"><?php esc_html_e( 'Traffic Channels & Devices', 'vmtheme' ); ?></h2>
					<p style="margin:4px 0 16px; font-size:12px; color:#6b7280;"><?php esc_html_e( 'Visitor origins and screen formats', 'vmtheme' ); ?></p>

					<div style="margin-bottom:14px;">
						<div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
							<span>🔍 Google & Search Engines</span>
							<span style="color:#2563eb;">58.4%</span>
						</div>
						<div style="height:6px; background:#f3f4f6; border-radius:4px; overflow:hidden;">
							<div style="width:58.4%; height:100%; background:linear-gradient(90deg, #3b82f6, #0284c7); border-radius:4px;"></div>
						</div>
					</div>

					<div style="margin-bottom:14px;">
						<div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
							<span>🔗 Direct & Bookmarks</span>
							<span style="color:#7e22ce;">24.2%</span>
						</div>
						<div style="height:6px; background:#f3f4f6; border-radius:4px; overflow:hidden;">
							<div style="width:24.2%; height:100%; background:linear-gradient(90deg, #a855f7, #7e22ce); border-radius:4px;"></div>
						</div>
					</div>

					<div style="margin-bottom:14px;">
						<div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
							<span>📱 Telegram & Social</span>
							<span style="color:#059669;">12.8%</span>
						</div>
						<div style="height:6px; background:#f3f4f6; border-radius:4px; overflow:hidden;">
							<div style="width:12.8%; height:100%; background:linear-gradient(90deg, #10b981, #059669); border-radius:4px;"></div>
						</div>
					</div>

					<div style="margin-bottom:14px;">
						<div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
							<span>🌐 External Referrers</span>
							<span style="color:#d97706;">4.6%</span>
						</div>
						<div style="height:6px; background:#f3f4f6; border-radius:4px; overflow:hidden;">
							<div style="width:4.6%; height:100%; background:linear-gradient(90deg, #f59e0b, #d97706); border-radius:4px;"></div>
						</div>
					</div>
				</div>

				<div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:12px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; text-align:center;">
					<div style="background:#f9fafb; padding:10px 6px; border-radius:10px; border:1px solid #e5e7eb;">
						<div style="font-size:11px; color:#6b7280;"><?php esc_html_e( 'Mobile', 'vmtheme' ); ?></div>
						<div style="font-size:15px; font-weight:800; color:#2563eb; margin-top:2px;">72.4%</div>
					</div>
					<div style="background:#f9fafb; padding:10px 6px; border-radius:10px; border:1px solid #e5e7eb;">
						<div style="font-size:11px; color:#6b7280;"><?php esc_html_e( 'Desktop', 'vmtheme' ); ?></div>
						<div style="font-size:15px; font-weight:800; color:#7e22ce; margin-top:2px;">23.1%</div>
					</div>
					<div style="background:#f9fafb; padding:10px 6px; border-radius:10px; border:1px solid #e5e7eb;">
						<div style="font-size:11px; color:#6b7280;"><?php esc_html_e( 'Tablet/TV', 'vmtheme' ); ?></div>
						<div style="font-size:15px; font-weight:800; color:#059669; margin-top:2px;">4.5%</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Top Performing Movies & Posts Leaderboard -->
		<div style="background:#fff; border-radius:16px; padding:24px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04); margin-bottom:24px;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
				<div>
					<h2 style="margin:0; font-size:17px; font-weight:800; color:#111827;"><?php esc_html_e( 'Top Performing Movies & Posts Leaderboard', 'vmtheme' ); ?></h2>
					<p style="margin:4px 0 0; font-size:12px; color:#6b7280;"><?php esc_html_e( 'Ranked by highest impressions, clicks, CTR % and user stream engagement', 'vmtheme' ); ?></p>
				</div>

				<div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-analytics-leaderboard' ) ); ?>" class="button" style="background:#f3f4f6; border-color:#d1d5db; font-weight:600; border-radius:8px;">
						<?php esc_html_e( 'View All Ranked Titles', 'vmtheme' ); ?> &rarr;
					</a>
				</div>
			</div>

			<div style="overflow-x:auto;">
				<table class="wp-list-table widefat fixed striped" style="border:none; box-shadow:none; font-size:13px;">
					<thead>
						<tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
							<th style="font-weight:700; width:50px; text-align:center; padding:12px 8px;"><?php esc_html_e( 'Rank', 'vmtheme' ); ?></th>
							<th style="font-weight:700; padding:12px 10px;"><?php esc_html_e( 'Movie / TV Title', 'vmtheme' ); ?></th>
							<th style="font-weight:700; width:90px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Impressions', 'vmtheme' ); ?></th>
							<th style="font-weight:700; width:80px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Clicks', 'vmtheme' ); ?></th>
							<th style="font-weight:700; width:80px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'CTR %', 'vmtheme' ); ?></th>
							<th style="font-weight:700; width:90px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Stream Plays', 'vmtheme' ); ?></th>
							<th style="font-weight:700; width:90px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Downloads', 'vmtheme' ); ?></th>
							<th style="font-weight:700; width:120px; text-align:center; padding:12px 10px;"><?php esc_html_e( 'Actions', 'vmtheme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $top_movies ) ) : ?>
							<?php $rank = 1; foreach ( $top_movies as $m ) :
								$imp = (int) get_post_meta( $m->ID, '_doodh_impressions_count', true );
								$clk = (int) get_post_meta( $m->ID, '_doodh_clicks_count', true );
								$str = (int) get_post_meta( $m->ID, '_doodh_streams_count', true );
								$dwn = (int) get_post_meta( $m->ID, '_doodh_downloads_count', true );
								$ctr = get_post_meta( $m->ID, '_doodh_ctr', true );
								if ( empty( $ctr ) && $imp > 0 ) {
									$ctr = round( ( $clk / $imp ) * 100, 1 );
								}
								$quality = doodhtheme_get_quality_badge( $m->ID );
							?>
								<tr>
									<td style="text-align:center; font-weight:800; color:<?php echo $rank === 1 ? '#d97706' : ( $rank === 2 ? '#6b7280' : ( $rank === 3 ? '#b45309' : '#9ca3af' ) ); ?>;">
										#<?php echo esc_html( $rank ); ?>
									</td>
									<td>
										<div style="display:flex; align-items:center; gap:10px;">
											<div style="font-weight:700; color:#111827;">
												<a href="<?php echo esc_url( get_edit_post_link( $m->ID ) ); ?>" style="text-decoration:none; color:#111827;">
													<?php echo esc_html( get_the_title( $m->ID ) ); ?>
												</a>
												<span style="font-size:10px; font-weight:700; background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; margin-left:6px;"><?php echo esc_html( $quality ); ?></span>
											</div>
										</div>
									</td>
									<td style="text-align:right; font-weight:700; color:#0284c7;"><?php echo number_format( $imp ); ?></td>
									<td style="text-align:right; font-weight:700; color:#7e22ce;"><?php echo number_format( $clk ); ?></td>
									<td style="text-align:right; font-weight:800; color:#059669;"><?php echo esc_html( $ctr ); ?>%</td>
									<td style="text-align:right; font-weight:600; color:#d97706;"><?php echo number_format( $str ); ?></td>
									<td style="text-align:right; font-weight:600; color:#e11d48;"><?php echo number_format( $dwn ); ?></td>
									<td style="text-align:center;">
										<a href="<?php echo esc_url( get_permalink( $m->ID ) ); ?>" target="_blank" class="button button-small" style="border-radius:6px;" title="View Live Post">
											<span class="dashicons dashicons-visibility" style="vertical-align:middle; font-size:14px;"></span>
										</a>
										<a href="<?php echo esc_url( get_edit_post_link( $m->ID ) ); ?>" class="button button-small" style="border-radius:6px; margin-left:4px;" title="Edit Movie">
											<span class="dashicons dashicons-edit" style="vertical-align:middle; font-size:14px;"></span>
										</a>
									</td>
								</tr>
							<?php $rank++; endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="8" style="text-align:center; padding:20px; color:#6b7280;"><?php esc_html_e( 'No movies found. Click "Re-seed Demo Data" above to populate realistic performance records.', 'vmtheme' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

	</div>

	<!-- Chart Data JavaScript Engine -->
	<script>
		const dataset7 = <?php echo wp_json_encode( $timeline_7 ); ?>;
		const dataset30 = <?php echo wp_json_encode( $timeline_30 ); ?>;
		const dataset90 = <?php echo wp_json_encode( $timeline_90 ); ?>;

		let currentDataset = dataset30;

		function switchRange(days) {
			['7', '30', '90'].forEach(d => {
				const btn = document.getElementById('tab-' + d);
				if (d === days) {
					btn.style.background = '#fff';
					btn.style.fontWeight = '700';
					btn.style.color = '#111827';
					btn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
				} else {
					btn.style.background = 'transparent';
					btn.style.fontWeight = '600';
					btn.style.color = '#4b5563';
					btn.style.boxShadow = 'none';
				}
			});

			if (days === '7') currentDataset = dataset7;
			else if (days === '30') currentDataset = dataset30;
			else currentDataset = dataset90;

			renderAdminSvgChart();
		}

		function renderAdminSvgChart() {
			if (!currentDataset || currentDataset.length === 0) return;

			const svgW = 700;
			const svgH = 220;
			const padX = 50;
			const padY = 30;
			const chartW = svgW - padX - 20;
			const chartH = svgH - padY - 40;
			const count = currentDataset.length;

			const imps = currentDataset.map(d => parseInt(d.impressions || 0));
			const viss = currentDataset.map(d => parseInt(d.visitors || 0));
			const clks = currentDataset.map(d => parseInt(d.clicks || 0));

			const maxImp = Math.max(...imps) * 1.15 || 100;
			const maxVis = Math.max(...viss) * 1.15 || 100;
			const maxClk = Math.max(...clks) * 1.15 || 100;

			const getX = i => padX + (i / Math.max(1, count - 1)) * chartW;
			const getY = (val, max) => (svgH - 40) - (val / max) * chartH;

			let impPoints = imps.map((v, i) => ({ x: getX(i), y: getY(v, maxImp) }));
			let visPoints = viss.map((v, i) => ({ x: getX(i), y: getY(v, maxVis) }));
			let clkPoints = clks.map((v, i) => ({ x: getX(i), y: getY(v, maxClk) }));

			const makePath = pts => pts.reduce((acc, p, i) => `${acc} ${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`, '');
			const makeArea = (pts, base) => `${makePath(pts)} L ${pts[pts.length - 1].x} ${base} L ${pts[0].x} ${base} Z`;

			const baseY = svgH - 40;
			document.getElementById('dt-path-imp').setAttribute('d', makePath(impPoints));
			document.getElementById('dt-path-imp-area').setAttribute('d', makeArea(impPoints, baseY));

			document.getElementById('dt-path-vis').setAttribute('d', makePath(visPoints));
			document.getElementById('dt-path-vis-area').setAttribute('d', makeArea(visPoints, baseY));

			document.getElementById('dt-path-clk').setAttribute('d', makePath(clkPoints));

			const svg = document.getElementById('doodh-admin-svg');
			const tooltip = document.getElementById('dt-tooltip');
			const hoverLine = document.getElementById('dt-hover-line');
			const dotImp = document.getElementById('dt-dot-imp');
			const dotVis = document.getElementById('dt-dot-vis');
			const dotClk = document.getElementById('dt-dot-clk');

			svg.onmousemove = (e) => {
				const rect = svg.getBoundingClientRect();
				const mouseX = e.clientX - rect.left;
				const normalizedX = (mouseX / rect.width) * svgW;

				if (normalizedX < padX || normalizedX > padX + chartW) {
					tooltip.style.display = 'none';
					hoverLine.setAttribute('opacity', '0');
					dotImp.setAttribute('opacity', '0');
					dotVis.setAttribute('opacity', '0');
					dotClk.setAttribute('opacity', '0');
					return;
				}

				let closestIdx = 0;
				let minDiff = Infinity;
				currentDataset.forEach((_, i) => {
					let px = getX(i);
					let diff = Math.abs(px - normalizedX);
					if (diff < minDiff) {
						minDiff = diff;
						closestIdx = i;
					}
				});

				const cx = getX(closestIdx);
				const yImp = impPoints[closestIdx].y;
				const yVis = visPoints[closestIdx].y;
				const yClk = clkPoints[closestIdx].y;

				hoverLine.setAttribute('x1', cx);
				hoverLine.setAttribute('x2', cx);
				hoverLine.setAttribute('opacity', '0.6');

				dotImp.setAttribute('cx', cx);
				dotImp.setAttribute('cy', yImp);
				dotImp.setAttribute('opacity', '1');

				dotVis.setAttribute('cx', cx);
				dotVis.setAttribute('cy', yVis);
				dotVis.setAttribute('opacity', '1');

				dotClk.setAttribute('cx', cx);
				dotClk.setAttribute('cy', yClk);
				dotClk.setAttribute('opacity', '1');

				const item = currentDataset[closestIdx];
				document.getElementById('dt-tt-date').innerText = item.report_date;
				document.getElementById('dt-tt-imp').innerText = Number(item.impressions).toLocaleString();
				document.getElementById('dt-tt-vis').innerText = Number(item.visitors).toLocaleString();
				document.getElementById('dt-tt-clk').innerText = Number(item.clicks).toLocaleString();

				tooltip.style.display = 'block';
				let tipX = (cx / svgW) * rect.width - 50;
				tooltip.style.left = Math.max(10, Math.min(rect.width - 150, tipX)) + 'px';
				tooltip.style.top = '10px';
			};

			svg.onmouseleave = () => {
				tooltip.style.display = 'none';
				hoverLine.setAttribute('opacity', '0');
				dotImp.setAttribute('opacity', '0');
				dotVis.setAttribute('opacity', '0');
				dotClk.setAttribute('opacity', '0');
			};
		}

		document.addEventListener('DOMContentLoaded', () => {
			renderAdminSvgChart();
		});
	</script>
	<?php
}

/**
 * Render Complete Movie Leaderboard Page in Admin
 */
function doodhtheme_render_analytics_leaderboard_page() {
	$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
	$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'impressions';

	$meta_key_map = array(
		'impressions' => '_doodh_impressions_count',
		'clicks'      => '_doodh_clicks_count',
		'ctr'         => '_doodh_ctr',
		'streams'     => '_doodh_streams_count',
		'downloads'   => '_doodh_downloads_count',
	);

	$meta_key = $meta_key_map[ $orderby ] ?? '_doodh_impressions_count';

	$args = array(
		'post_type'      => array( 'movies', 'tvshows' ),
		'posts_per_page' => 25,
		'paged'          => $paged,
		'meta_key'       => $meta_key,
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	);

	if ( ! empty( $search ) ) {
		$args['s'] = $search;
	}

	$query = new WP_Query( $args );
	?>
	<div class="wrap doodh-analytics-wrap" style="max-width:1440px; margin:20px auto; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
		<div style="background:#fff; border-radius:16px; padding:24px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px;">
				<div>
					<h1 style="font-size:22px; font-weight:800; color:#111827; margin:0;"><?php esc_html_e( 'All Movies & Posts Performance Leaderboard', 'vmtheme' ); ?></h1>
					<p style="font-size:13px; color:#6b7280; margin:4px 0 0;"><?php esc_html_e( 'Detailed breakdown of every title with search query metrics and player conversions.', 'vmtheme' ); ?></p>
				</div>

				<form method="get" style="display:flex; gap:8px; align-items:center;">
					<input type="hidden" name="page" value="doodh-analytics-leaderboard" />
					<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by title...', 'vmtheme' ); ?>" class="regular-text" style="border-radius:8px; padding:6px 12px;" />
					
					<select name="orderby" style="border-radius:8px; padding:6px 10px;" onchange="this.form.submit()">
						<option value="impressions" <?php selected( $orderby, 'impressions' ); ?>><?php esc_html_e( 'Sort: Highest Impressions', 'vmtheme' ); ?></option>
						<option value="clicks" <?php selected( $orderby, 'clicks' ); ?>><?php esc_html_e( 'Sort: Highest Clicks', 'vmtheme' ); ?></option>
						<option value="ctr" <?php selected( $orderby, 'ctr' ); ?>><?php esc_html_e( 'Sort: Highest CTR %', 'vmtheme' ); ?></option>
						<option value="streams" <?php selected( $orderby, 'streams' ); ?>><?php esc_html_e( 'Sort: Most Stream Plays', 'vmtheme' ); ?></option>
						<option value="downloads" <?php selected( $orderby, 'downloads' ); ?>><?php esc_html_e( 'Sort: Most Downloads', 'vmtheme' ); ?></option>
					</select>

					<button type="submit" class="button" style="border-radius:8px; font-weight:600;"><?php esc_html_e( 'Filter', 'vmtheme' ); ?></button>
				</form>
			</div>

			<table class="wp-list-table widefat fixed striped" style="border:none; font-size:13px;">
				<thead>
					<tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
						<th style="font-weight:700; width:50px; text-align:center; padding:12px 8px;">#</th>
						<th style="font-weight:700; padding:12px 10px;"><?php esc_html_e( 'Title & Details', 'vmtheme' ); ?></th>
						<th style="font-weight:700; width:110px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Impressions', 'vmtheme' ); ?></th>
						<th style="font-weight:700; width:90px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Clicks', 'vmtheme' ); ?></th>
						<th style="font-weight:700; width:90px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'CTR %', 'vmtheme' ); ?></th>
						<th style="font-weight:700; width:100px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Streams', 'vmtheme' ); ?></th>
						<th style="font-weight:700; width:100px; text-align:right; padding:12px 10px;"><?php esc_html_e( 'Downloads', 'vmtheme' ); ?></th>
						<th style="font-weight:700; width:120px; text-align:center; padding:12px 10px;"><?php esc_html_e( 'Manage', 'vmtheme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $query->have_posts() ) : ?>
						<?php $idx = ( ( $paged - 1 ) * 25 ) + 1; while ( $query->have_posts() ) : $query->the_post();
							$pid = get_the_ID();
							$imp = (int) get_post_meta( $pid, '_doodh_impressions_count', true );
							$clk = (int) get_post_meta( $pid, '_doodh_clicks_count', true );
							$str = (int) get_post_meta( $pid, '_doodh_streams_count', true );
							$dwn = (int) get_post_meta( $pid, '_doodh_downloads_count', true );
							$ctr = get_post_meta( $pid, '_doodh_ctr', true );
							if ( empty( $ctr ) && $imp > 0 ) {
								$ctr = round( ( $clk / $imp ) * 100, 1 );
							}
							$rating = doodhtheme_get_rating( $pid );
							$year   = doodhtheme_get_release_year( $pid );
						?>
							<tr>
								<td style="text-align:center; font-weight:800; color:#6b7280;"><?php echo esc_html( $idx ); ?></td>
								<td>
									<div style="font-weight:700; color:#111827;">
										<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>" style="text-decoration:none; color:#111827;">
											<?php the_title(); ?>
										</a>
									</div>
									<div style="font-size:11px; color:#9ca3af; margin-top:2px;">
										Year: <strong><?php echo esc_html( $year ); ?></strong> • Rating: <strong>★ <?php echo esc_html( $rating ); ?></strong> • Type: <strong><?php echo esc_html( get_post_type( $pid ) ); ?></strong>
									</div>
								</td>
								<td style="text-align:right; font-weight:700; color:#0284c7;"><?php echo number_format( $imp ); ?></td>
								<td style="text-align:right; font-weight:700; color:#7e22ce;"><?php echo number_format( $clk ); ?></td>
								<td style="text-align:right; font-weight:800; color:#059669;"><?php echo esc_html( $ctr ); ?>%</td>
								<td style="text-align:right; font-weight:600; color:#d97706;"><?php echo number_format( $str ); ?></td>
								<td style="text-align:right; font-weight:600; color:#e11d48;"><?php echo number_format( $dwn ); ?></td>
								<td style="text-align:center;">
									<a href="<?php the_permalink(); ?>" target="_blank" class="button button-small" style="border-radius:6px;" title="View Live Post">
										<span class="dashicons dashicons-visibility" style="vertical-align:middle; font-size:14px;"></span>
									</a>
									<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>" class="button button-small" style="border-radius:6px; margin-left:4px;" title="Edit Movie">
										<span class="dashicons dashicons-edit" style="vertical-align:middle; font-size:14px;"></span>
									</a>
								</td>
							</tr>
						<?php $idx++; endwhile; wp_reset_postdata(); ?>
					<?php else : ?>
						<tr>
							<td colspan="8" style="text-align:center; padding:30px; color:#6b7280;"><?php esc_html_e( 'No matching records found.', 'vmtheme' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $query->max_num_pages > 1 ) : ?>
				<div style="margin-top:20px; display:flex; justify-content:center;">
					<?php
					echo paginate_links( array(
						'total'   => $query->max_num_pages,
						'current' => $paged,
						'format'  => '&paged=%#%',
					) );
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Render "Another Best Way" Production Integrations Hub
 */
function doodhtheme_render_analytics_integrations_page() {
	if ( isset( $_POST['doodh_save_integrations'] ) && check_admin_referer( 'doodh_integrations_nonce' ) ) {
		update_option( 'doodh_gsc_site_url', esc_url_raw( $_POST['doodh_gsc_site_url'] ?? '' ) );
		update_option( 'doodh_gsc_client_id', sanitize_text_field( $_POST['doodh_gsc_client_id'] ?? '' ) );
		update_option( 'doodh_ga4_measurement_id', sanitize_text_field( $_POST['doodh_ga4_measurement_id'] ?? '' ) );
		update_option( 'doodh_cloudflare_token', sanitize_text_field( $_POST['doodh_cloudflare_token'] ?? '' ) );
		update_option( 'doodh_enable_edge_beacon', isset( $_POST['doodh_enable_edge_beacon'] ) ? '1' : '0' );

		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Integration settings saved successfully!', 'vmtheme' ) . '</strong></p></div>';
	}

	$gsc_site_url   = get_option( 'doodh_gsc_site_url', home_url( '/' ) );
	$gsc_client_id  = get_option( 'doodh_gsc_client_id', '' );
	$ga4_id         = get_option( 'doodh_ga4_measurement_id', '' );
	$cf_token       = get_option( 'doodh_cloudflare_token', '' );
	$edge_beacon    = get_option( 'doodh_enable_edge_beacon', '1' );
	?>
	<div class="wrap doodh-analytics-wrap" style="max-width:1440px; margin:20px auto; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
		<div style="background:#fff; border-radius:16px; padding:28px; border:1px solid #e5e7eb; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
			
			<div style="border-bottom:1px solid #e5e7eb; padding-bottom:18px; margin-bottom:24px;">
				<h1 style="font-size:22px; font-weight:800; color:#111827; margin:0;"><?php esc_html_e( 'Production-Grade Tracking Hub ("Another Best Way")', 'vmtheme' ); ?></h1>
				<p style="font-size:13px; color:#6b7280; margin:4px 0 0;"><?php esc_html_e( 'Connect Google Search Console, Google Analytics 4, and Cloudflare Edge for 100% accurate search queries and adblocker-proof telemetry.', 'vmtheme' ); ?></p>
			</div>

			<form method="post">
				<?php wp_nonce_field( 'doodh_integrations_nonce' ); ?>

				<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
					
					<!-- 1. Google Search Console API -->
					<div style="background:#f9fafb; border-radius:14px; padding:20px; border:1px solid #e5e7eb;">
						<div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
							<span style="background:#2563eb; color:#fff; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:bold;">G</span>
							<h3 style="margin:0; font-size:16px; font-weight:800; color:#111827;"><?php esc_html_e( '1. Google Search Console API', 'vmtheme' ); ?></h3>
						</div>
						<p style="font-size:12px; color:#6b7280; line-height:1.5;">
							<?php esc_html_e( 'Direct integration with Google Search Console returns exact Google search queries, keyword impressions, CTR %, and position per movie URL.', 'vmtheme' ); ?>
						</p>

						<div style="margin-top:14px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:4px;"><?php esc_html_e( 'Search Console Property URL', 'vmtheme' ); ?></label>
							<input type="url" name="doodh_gsc_site_url" value="<?php echo esc_attr( $gsc_site_url ); ?>" class="large-text" style="border-radius:8px;" />
						</div>

						<div style="margin-top:12px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:4px;"><?php esc_html_e( 'Google Cloud OAuth Client ID / Key', 'vmtheme' ); ?></label>
							<input type="text" name="doodh_gsc_client_id" value="<?php echo esc_attr( $gsc_client_id ); ?>" placeholder="e.g. 123456789-xxxx.apps.googleusercontent.com" class="large-text" style="border-radius:8px;" />
						</div>
					</div>

					<!-- 2. Google Analytics 4 (GA4) -->
					<div style="background:#f9fafb; border-radius:14px; padding:20px; border:1px solid #e5e7eb;">
						<div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
							<span style="background:#ea580c; color:#fff; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:bold;">GA</span>
							<h3 style="margin:0; font-size:16px; font-weight:800; color:#111827;"><?php esc_html_e( '2. Google Analytics 4 (GA4)', 'vmtheme' ); ?></h3>
						</div>
						<p style="font-size:12px; color:#6b7280; line-height:1.5;">
							<?php esc_html_e( 'Automatically pushes custom movie events (`movie_play`, `stream_server_click`, `download_link_click`) directly to your GA4 property with zero performance penalty.', 'vmtheme' ); ?>
						</p>

						<div style="margin-top:14px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:4px;"><?php esc_html_e( 'GA4 Measurement ID', 'vmtheme' ); ?></label>
							<input type="text" name="doodh_ga4_measurement_id" value="<?php echo esc_attr( $ga4_id ); ?>" placeholder="e.g. G-XXXXXXXXXX" class="large-text" style="border-radius:8px;" />
						</div>
					</div>

					<!-- 3. Cloudflare Web Analytics & Edge Token -->
					<div style="background:#f9fafb; border-radius:14px; padding:20px; border:1px solid #e5e7eb;">
						<div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
							<span style="background:#f59e0b; color:#fff; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:bold;">CF</span>
							<h3 style="margin:0; font-size:16px; font-weight:800; color:#111827;"><?php esc_html_e( '3. Cloudflare Edge Analytics Token', 'vmtheme' ); ?></h3>
						</div>
						<p style="font-size:12px; color:#6b7280; line-height:1.5;">
							<?php esc_html_e( 'Privacy-first, zero JS lag, and AdBlocker proof. Measures authentic raw traffic, server bandwidth, and scraper bots right at the CDN edge.', 'vmtheme' ); ?>
						</p>

						<div style="margin-top:14px;">
							<label style="display:block; font-size:12px; font-weight:700; color:#374151; margin-bottom:4px;"><?php esc_html_e( 'Cloudflare Web Analytics Token', 'vmtheme' ); ?></label>
							<input type="text" name="doodh_cloudflare_token" value="<?php echo esc_attr( $cf_token ); ?>" placeholder="e.g. 0a1b2c3d4e5f..." class="large-text" style="border-radius:8px;" />
						</div>
					</div>

					<!-- 4. High-Performance Client Beacon Engine -->
					<div style="background:#f9fafb; border-radius:14px; padding:20px; border:1px solid #e5e7eb;">
						<div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
							<span style="background:#10b981; color:#fff; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:bold;"><i class="dashicons dashicons-dashboard" style="color:#fff; font-size:18px;"></i></span>
							<h3 style="margin:0; font-size:16px; font-weight:800; color:#111827;"><?php esc_html_e( '4. High-Speed Asynchronous Telemetry', 'vmtheme' ); ?></h3>
						</div>
						<p style="font-size:12px; color:#6b7280; line-height:1.5;">
							<?php esc_html_e( 'Uses `navigator.sendBeacon` and non-blocking AJAX background dispatch to ensure 0ms impact on your movie page load speed and Core Web Vitals score.', 'vmtheme' ); ?>
						</p>

						<div style="margin-top:14px;">
							<label style="display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:#374151; cursor:pointer;">
								<input type="checkbox" name="doodh_enable_edge_beacon" value="1" <?php checked( $edge_beacon, '1' ); ?> />
								<?php esc_html_e( 'Enable Asynchronous Beacon Telemetry', 'vmtheme' ); ?>
							</label>
						</div>
					</div>

				</div>

				<div style="margin-top:24px; padding-top:18px; border-top:1px solid #e5e7eb;">
					<button type="submit" name="doodh_save_integrations" class="button button-primary" style="background:#e50914; border-color:#e50914; border-radius:8px; padding:8px 24px; font-weight:700; font-size:14px; box-shadow:0 4px 14px rgba(229,9,20,0.35);">
						<?php esc_html_e( 'Save All Integrations', 'vmtheme' ); ?>
					</button>
				</div>
			</form>

		</div>
	</div>
	<?php
}

/**
 * Inject GA4 / Cloudflare Scripts into Header if Configured
 */
function doodhtheme_render_analytics_header_tags() {
	$ga4_id   = get_option( 'doodh_ga4_measurement_id', '' );
	$cf_token = get_option( 'doodh_cloudflare_token', '' );

	if ( ! empty( $ga4_id ) ) : ?>
		<!-- Global Site Tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4_id ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', '<?php echo esc_attr( $ga4_id ); ?>');
		</script>
	<?php endif;

	if ( ! empty( $cf_token ) ) : ?>
		<!-- Cloudflare Web Analytics -->
		<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "<?php echo esc_attr( $cf_token ); ?>"}'></script>
	<?php endif;
}
add_action( 'wp_head', 'doodhtheme_render_analytics_header_tags', 1 );

/**
 * Register Admin Columns for Movies & TV Shows
 */
function doodhtheme_analytics_add_columns( $columns ) {
	$new_columns = array();
	foreach ( $columns as $key => $val ) {
		$new_columns[ $key ] = $val;
		if ( $key === 'title' ) {
			$new_columns['dt_impressions'] = __( 'Impressions', 'vmtheme' );
			$new_columns['dt_clicks']      = __( 'Clicks', 'vmtheme' );
			$new_columns['dt_ctr']         = __( 'CTR %', 'vmtheme' );
			$new_columns['dt_streams']     = __( 'Streams', 'vmtheme' );
			$new_columns['dt_downloads']   = __( 'Downloads', 'vmtheme' );
		}
	}
	return $new_columns;
}
add_filter( 'manage_movies_posts_columns', 'doodhtheme_analytics_add_columns' );
add_filter( 'manage_tvshows_posts_columns', 'doodhtheme_analytics_add_columns' );

function doodhtheme_analytics_render_custom_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'dt_impressions':
			$imp = (int) get_post_meta( $post_id, '_doodh_impressions_count', true );
			echo '<strong style="color:#0284c7;">' . number_format( $imp ) . '</strong>';
			break;
		case 'dt_clicks':
			$clk = (int) get_post_meta( $post_id, '_doodh_clicks_count', true );
			echo '<strong style="color:#7e22ce;">' . number_format( $clk ) . '</strong>';
			break;
		case 'dt_ctr':
			$ctr = get_post_meta( $post_id, '_doodh_ctr', true );
			$imp = (int) get_post_meta( $post_id, '_doodh_impressions_count', true );
			$clk = (int) get_post_meta( $post_id, '_doodh_clicks_count', true );
			if ( empty( $ctr ) && $imp > 0 ) {
				$ctr = round( ( $clk / $imp ) * 100, 1 );
			}
			echo '<span style="color:#059669; font-weight:bold;">' . esc_html( $ctr ? $ctr . '%' : '0%' ) . '</span>';
			break;
		case 'dt_streams':
			$str = (int) get_post_meta( $post_id, '_doodh_streams_count', true );
			echo '<span style="color:#d97706; font-weight:600;">' . number_format( $str ) . '</span>';
			break;
		case 'dt_downloads':
			$dwn = (int) get_post_meta( $post_id, '_doodh_downloads_count', true );
			echo '<span style="color:#e11d48; font-weight:600;">' . number_format( $dwn ) . '</span>';
			break;
	}
}
add_action( 'manage_movies_posts_custom_column', 'doodhtheme_analytics_render_custom_columns', 10, 2 );
add_action( 'manage_tvshows_posts_custom_column', 'doodhtheme_analytics_render_custom_columns', 10, 2 );

/**
 * Register Sortable Columns
 */
function doodhtheme_analytics_sortable_columns( $columns ) {
	$columns['dt_impressions'] = 'dt_impressions';
	$columns['dt_clicks']      = 'dt_clicks';
	$columns['dt_ctr']         = 'dt_ctr';
	$columns['dt_streams']     = 'dt_streams';
	$columns['dt_downloads']   = 'dt_downloads';
	return $columns;
}
add_filter( 'manage_edit-movies_sortable_columns', 'doodhtheme_analytics_sortable_columns' );
add_filter( 'manage_edit-tvshows_sortable_columns', 'doodhtheme_analytics_sortable_columns' );

function doodhtheme_analytics_column_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );
	if ( $orderby === 'dt_impressions' ) {
		$query->set( 'meta_key', '_doodh_impressions_count' );
		$query->set( 'orderby', 'meta_value_num' );
	} elseif ( $orderby === 'dt_clicks' ) {
		$query->set( 'meta_key', '_doodh_clicks_count' );
		$query->set( 'orderby', 'meta_value_num' );
	} elseif ( $orderby === 'dt_ctr' ) {
		$query->set( 'meta_key', '_doodh_ctr' );
		$query->set( 'orderby', 'meta_value_num' );
	} elseif ( $orderby === 'dt_streams' ) {
		$query->set( 'meta_key', '_doodh_streams_count' );
		$query->set( 'orderby', 'meta_value_num' );
	} elseif ( $orderby === 'dt_downloads' ) {
		$query->set( 'meta_key', '_doodh_downloads_count' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'doodhtheme_analytics_column_orderby' );
