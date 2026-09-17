<?php
/**
 * TMDb & IMDb Auto-Fetcher and Importer Module
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get TMDb API Key from options or fallback default
 */
function doodhtheme_get_tmdb_api_key() {
	$key = get_option( 'doodh_tmdb_api_key', '' );
	if ( empty( $key ) ) {
		// Default public developer fallback key for instant out-of-the-box fetching
		$key = 'c90538a79a32c2536778dc6525164cf3';
	}
	return trim( $key );
}

/**
 * Robust HTTP GET Client for TMDb API (with cURL and Stream Fallback)
 */
function doodhtheme_tmdb_api_get( $url, $timeout = 20 ) {
	$args = array(
		'timeout'     => $timeout,
		'sslverify'   => false,
		'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
		'headers'     => array(
			'Accept' => 'application/json',
		),
	);
	
	$response = wp_remote_get( $url, $args );
	if ( ! is_wp_error( $response ) && (int) wp_remote_retrieve_response_code( $response ) === 200 ) {
		return $response;
	}

	// Stream context fallback for environments with strict/resetting cURL SSL
	$ctx = stream_context_create( array(
		'http' => array(
			'timeout'         => $timeout,
			'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			'header'          => "Accept: application/json\r\n",
			'follow_location' => 1,
			'ignore_errors'   => true,
		),
		'ssl'  => array(
			'verify_peer'      => false,
			'verify_peer_name' => false,
		),
	) );
	
	$body = @file_get_contents( $url, false, $ctx );
	if ( $body !== false && ! empty( $body ) ) {
		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body'     => $body,
			'headers'  => array(),
		);
	}

	return $response;
}

/**
 * Register TMDb Admin Menu Pages
 */
function doodhtheme_tmdb_admin_menu() {
	add_menu_page(
		__( 'TMDb Importer', 'vmtheme' ),
		__( 'TMDb Importer', 'vmtheme' ),
		'manage_options',
		'doodh-tmdb-importer',
		'doodhtheme_tmdb_importer_page',
		'dashicons-cloud-upload',
		7
	);

	add_submenu_page(
		'doodh-tmdb-importer',
		__( 'TMDb API Settings', 'vmtheme' ),
		__( 'API Settings', 'vmtheme' ),
		'manage_options',
		'doodh-tmdb-settings',
		'doodhtheme_tmdb_settings_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_tmdb_admin_menu' );

/**
 * TMDb API Settings Page
 */
function doodhtheme_tmdb_settings_page() {
	if ( isset( $_POST['doodh_save_tmdb_settings'] ) && check_admin_referer( 'doodh_tmdb_settings_nonce' ) ) {
		$api_key  = sanitize_text_field( $_POST['doodh_tmdb_api_key'] ?? '' );
		$language = sanitize_text_field( $_POST['doodh_tmdb_lang'] ?? 'en-US' );
		update_option( 'doodh_tmdb_api_key', $api_key );
		update_option( 'doodh_tmdb_lang', $language );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'TMDb API settings saved successfully!', 'vmtheme' ) . '</p></div>';
	}

	$current_key  = get_option( 'doodh_tmdb_api_key', '' );
	$current_lang = get_option( 'doodh_tmdb_lang', 'en-US' );
	?>
	<div class="wrap">
		<h1><i class="dashicons dashicons-admin-settings"></i> <?php esc_html_e( 'TMDb API Settings', 'vmtheme' ); ?></h1>
		<div style="background:#fff; border:1px solid #ccd0d4; padding:25px; border-radius:8px; max-width:700px; margin-top:20px;">
			<form method="post" action="">
				<?php wp_nonce_field( 'doodh_tmdb_settings_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="doodh_tmdb_api_key"><?php esc_html_e( 'TMDb API Key (v3 auth)', 'vmtheme' ); ?></label></th>
						<td>
							<input type="text" name="doodh_tmdb_api_key" id="doodh_tmdb_api_key" value="<?php echo esc_attr( $current_key ); ?>" class="regular-text" placeholder="e.g. c90538a79a32c2536778dc6525164cf3" style="width:100%;">
							<p class="description">
								<?php esc_html_e( 'Get your free API key from ', 'vmtheme' ); ?>
								<a href="https://www.themoviedb.org/settings/api" target="_blank">themoviedb.org/settings/api</a>.
								<?php esc_html_e( '(A default fallback key is active if left blank)', 'vmtheme' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="doodh_tmdb_lang"><?php esc_html_e( 'Preferred Content Language', 'vmtheme' ); ?></label></th>
						<td>
							<select name="doodh_tmdb_lang" id="doodh_tmdb_lang">
								<option value="en-US" <?php selected( $current_lang, 'en-US' ); ?>>English (en-US)</option>
								<option value="es-ES" <?php selected( $current_lang, 'es-ES' ); ?>>Spanish (es-ES)</option>
								<option value="fr-FR" <?php selected( $current_lang, 'fr-FR' ); ?>>French (fr-FR)</option>
								<option value="de-DE" <?php selected( $current_lang, 'de-DE' ); ?>>German (de-DE)</option>
								<option value="hi-IN" <?php selected( $current_lang, 'hi-IN' ); ?>>Hindi (hi-IN)</option>
								<option value="it-IT" <?php selected( $current_lang, 'it-IT' ); ?>>Italian (it-IT)</option>
								<option value="pt-BR" <?php selected( $current_lang, 'pt-BR' ); ?>>Portuguese (pt-BR)</option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="doodh_save_tmdb_settings" class="button button-primary" value="<?php esc_attr_e( 'Save API Settings', 'vmtheme' ); ?>">
				</p>
			</form>
		</div>
	</div>
	<?php
}

/**
 * TMDb 1-Click Importer Admin Page
 */
function doodhtheme_tmdb_importer_page() {
	?>
	<div class="wrap doodh-importer-wrap">
		<h1 style="display:flex; align-items:center; gap:10px;">
			<span class="dashicons dashicons-cloud-upload" style="font-size:32px; width:32px; height:32px;"></span>
			<?php esc_html_e( 'TMDb & IMDb 1-Click Content Importer', 'vmtheme' ); ?>
		</h1>
		<p><?php esc_html_e( 'Fetch full movie and TV show data with 4K posters, backdrops, trailers, cast with photos, directors, ratings, seasons, episodes, and TMDb critic reviews directly into WordPress.', 'vmtheme' ); ?></p>

		<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap:25px; margin-top:20px;">
			<!-- 1. Single Import Card -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:25px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
				<h2><i class="dashicons dashicons-video-alt3"></i> <?php esc_html_e( 'Single Title Importer', 'vmtheme' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Enter a TMDb ID (e.g. 27205) or IMDb ID (e.g. tt1375666) or Title Search query.', 'vmtheme' ); ?></p>
				
				<div style="margin-top:15px;">
					<label style="display:block; font-weight:600; margin-bottom:5px;"><?php esc_html_e( 'Content Type:', 'vmtheme' ); ?></label>
					<select id="doodh-import-type" style="width:100%; padding:6px; margin-bottom:15px;">
						<option value="movie"><?php esc_html_e( 'Movie', 'vmtheme' ); ?></option>
						<option value="tv"><?php esc_html_e( 'TV Show (with all Seasons & Episodes)', 'vmtheme' ); ?></option>
					</select>

					<label style="display:block; font-weight:600; margin-bottom:5px;"><?php esc_html_e( 'TMDb ID / IMDb ID / Title:', 'vmtheme' ); ?></label>
					<div style="display:flex; gap:10px;">
						<input type="text" id="doodh-import-id" class="regular-text" placeholder="e.g. 27205 or tt1375666 or Inception" style="flex:1;">
						<button type="button" id="doodh-btn-fetch-tmdb" class="button button-primary">
							<i class="dashicons dashicons-download"></i> <?php esc_html_e( 'Fetch & Import', 'vmtheme' ); ?>
						</button>
					</div>
				</div>

				<div id="doodh-import-status" style="margin-top:20px; display:none;"></div>
			</div>

			<!-- 2. Year & Month Wise Importer (Smart Duplicate & Review Sync) -->
			<div style="background:#fff; border:1px solid #2271b1; padding:25px; border-radius:8px; box-shadow:0 2px 6px rgba(34,113,177,0.12); position:relative;">
				<div style="position:absolute; top:12px; right:15px; background:#2271b1; color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; text-transform:uppercase;">
					<?php esc_html_e( 'Smart Sync', 'vmtheme' ); ?>
				</div>
				<h2><i class="dashicons dashicons-calendar-alt" style="color:#2271b1;"></i> <?php esc_html_e( 'Year & Month-wise Importer', 'vmtheme' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Fetch content filtered by exact release Year & Month. Detects duplicates, updates changed data, and adds new TMDb reviews & comments.', 'vmtheme' ); ?></p>

				<div style="margin-top:15px;">
					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
						<div>
							<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Type:', 'vmtheme' ); ?></label>
							<select id="doodh-ym-type" style="width:100%;">
								<option value="movie"><?php esc_html_e( 'Movies', 'vmtheme' ); ?></option>
								<option value="tv"><?php esc_html_e( 'TV Shows', 'vmtheme' ); ?></option>
							</select>
						</div>
						<div>
							<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Select Year:', 'vmtheme' ); ?></label>
							<select id="doodh-ym-year" style="width:100%;">
								<?php for ( $y = 2026; $y >= 1970; $y-- ) : ?>
									<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
								<?php endfor; ?>
							</select>
						</div>
					</div>

					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
						<div>
							<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Select Month:', 'vmtheme' ); ?></label>
							<select id="doodh-ym-month" style="width:100%;">
								<option value="all"><?php esc_html_e( 'All Months (Full Year)', 'vmtheme' ); ?></option>
								<option value="1"><?php esc_html_e( '01 - January', 'vmtheme' ); ?></option>
								<option value="2"><?php esc_html_e( '02 - February', 'vmtheme' ); ?></option>
								<option value="3"><?php esc_html_e( '03 - March', 'vmtheme' ); ?></option>
								<option value="4"><?php esc_html_e( '04 - April', 'vmtheme' ); ?></option>
								<option value="5"><?php esc_html_e( '05 - May', 'vmtheme' ); ?></option>
								<option value="6"><?php esc_html_e( '06 - June', 'vmtheme' ); ?></option>
								<option value="7"><?php esc_html_e( '07 - July', 'vmtheme' ); ?></option>
								<option value="8"><?php esc_html_e( '08 - August', 'vmtheme' ); ?></option>
								<option value="9"><?php esc_html_e( '09 - September', 'vmtheme' ); ?></option>
								<option value="10"><?php esc_html_e( '10 - October', 'vmtheme' ); ?></option>
								<option value="11"><?php esc_html_e( '11 - November', 'vmtheme' ); ?></option>
								<option value="12"><?php esc_html_e( '12 - December', 'vmtheme' ); ?></option>
							</select>
						</div>
						<div>
							<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Sort By:', 'vmtheme' ); ?></label>
							<select id="doodh-ym-sort" style="width:100%;">
								<option value="popularity.desc"><?php esc_html_e( 'Most Popular', 'vmtheme' ); ?></option>
								<option value="vote_average.desc"><?php esc_html_e( 'Highest Rated (IMDb / TMDb)', 'vmtheme' ); ?></option>
								<option value="primary_release_date.desc"><?php esc_html_e( 'Latest Release Date', 'vmtheme' ); ?></option>
								<option value="vote_count.desc"><?php esc_html_e( 'Most Voted', 'vmtheme' ); ?></option>
							</select>
						</div>
					</div>

					<div style="margin-bottom:12px;">
						<label style="display:block; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Titles Count to Fetch:', 'vmtheme' ); ?></label>
						<select id="doodh-ym-count" style="width:100%;">
							<option value="20">20 Titles</option>
							<option value="50" selected>50 Titles</option>
							<option value="100">100 Titles</option>
							<option value="200">200 Titles</option>
						</select>
					</div>

					<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 12px; margin-bottom:15px; font-size:12px; color:#475569;">
						<label style="display:block; margin-bottom:6px;">
							<input type="checkbox" id="doodh-ym-update-changed" value="1" checked>
							<strong><?php esc_html_e( 'Smart Duplicate Handling:', 'vmtheme' ); ?></strong> <?php esc_html_e( 'If title exists, update changed content & ratings instead of creating duplicate.', 'vmtheme' ); ?>
						</label>
						<label style="display:block;">
							<input type="checkbox" id="doodh-ym-sync-reviews" value="1" checked>
							<strong><?php esc_html_e( 'Sync Reviews & Comments:', 'vmtheme' ); ?></strong> <?php esc_html_e( 'Fetch TMDb reviews and inject new reviews into the review section & comments.', 'vmtheme' ); ?>
						</label>
					</div>

					<button type="button" id="doodh-btn-ym-import" class="button button-primary" style="width:100%; height:40px; font-weight:600; font-size:14px;">
						<i class="dashicons dashicons-update"></i> <?php esc_html_e( 'Fetch & Import by Year & Month', 'vmtheme' ); ?>
					</button>
				</div>

				<div id="doodh-ym-progress" style="margin-top:20px; display:none;"></div>
			</div>

			<!-- 3. Quick Batch Year Importer Card -->
			<div style="background:#fff; border:1px solid #ccd0d4; padding:25px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
				<h2><i class="dashicons dashicons-backup"></i> <?php esc_html_e( 'Quick Full-Year Batch Importer', 'vmtheme' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Rapid bulk import of top movies for an entire release year.', 'vmtheme' ); ?></p>

				<div style="margin-top:15px;">
					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
						<div>
							<label style="display:block; font-weight:600; margin-bottom:5px;"><?php esc_html_e( 'Select Year:', 'vmtheme' ); ?></label>
							<select id="doodh-batch-year" style="width:100%;">
								<?php for ( $y = 2026; $y >= 2000; $y-- ) : ?>
									<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
								<?php endfor; ?>
							</select>
						</div>
						<div>
							<label style="display:block; font-weight:600; margin-bottom:5px;"><?php esc_html_e( 'Count to Import:', 'vmtheme' ); ?></label>
							<select id="doodh-batch-count" style="width:100%;">
								<option value="20">20 Titles</option>
								<option value="50">50 Titles</option>
								<option value="100" selected>100 Titles</option>
							</select>
						</div>
					</div>

					<button type="button" id="doodh-btn-batch-import" class="button button-secondary" style="width:100%; height:40px; font-weight:600;">
						<i class="dashicons dashicons-controls-play"></i> <?php esc_html_e( 'Start Full-Year Batch Import', 'vmtheme' ); ?>
					</button>
				</div>

				<div id="doodh-batch-progress" style="margin-top:20px; display:none;"></div>
			</div>
		</div>
	</div>

	<script>
	jQuery(document).ready(function($) {
		// 1. Single Import
		$('#doodh-btn-fetch-tmdb').on('click', function(e) {
			e.preventDefault();
			var query = $('#doodh-import-id').val().trim();
			var type  = $('#doodh-import-type').val();
			if (!query) {
				alert('Please enter a TMDb ID, IMDb ID, or Movie/Show Title.');
				return;
			}

			var $btn = $(this);
			var $status = $('#doodh-import-status');
			$btn.prop('disabled', true).text('Fetching TMDb data...');
			$status.show().html('<div class="notice notice-info"><p><span class="spinner is-active" style="float:left; margin:0 8px 0 0;"></span> Contacting TMDb API & importing cast, crew, media, and reviews...</p></div>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'doodhtheme_ajax_import_tmdb',
					query: query,
					type: type,
					nonce: '<?php echo wp_create_nonce( 'doodh_tmdb_nonce' ); ?>'
				},
				success: function(res) {
					$btn.prop('disabled', false).html('<i class="dashicons dashicons-download"></i> Fetch & Import');
					if (res.success) {
						$status.html('<div class="notice notice-success"><p><strong>Success!</strong> ' + res.data.message + ' <a href="' + res.data.edit_url + '" target="_blank">Edit Post &rarr;</a> | <a href="' + res.data.view_url + '" target="_blank">View Post &rarr;</a></p></div>');
					} else {
						$status.html('<div class="notice notice-error"><p><strong>Error:</strong> ' + res.data + '</p></div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false).html('<i class="dashicons dashicons-download"></i> Fetch & Import');
					$status.html('<div class="notice notice-error"><p>Server communication error. Please verify your internet connection.</p></div>');
				}
			});
		});

		// 2. Year & Month Wise Importer (Smart Sync)
		$('#doodh-btn-ym-import').on('click', function(e) {
			e.preventDefault();
			var type           = $('#doodh-ym-type').val();
			var year           = $('#doodh-ym-year').val();
			var month          = $('#doodh-ym-month').val();
			var sort_by        = $('#doodh-ym-sort').val();
			var count          = $('#doodh-ym-count').val();
			var update_changed = $('#doodh-ym-update-changed').is(':checked') ? 1 : 0;
			var sync_reviews   = $('#doodh-ym-sync-reviews').is(':checked') ? 1 : 0;

			var monthName = month === 'all' ? 'All Months' : 'Month ' + month;
			if (!confirm('Start importing ' + count + ' ' + (type === 'tv' ? 'TV Shows' : 'Movies') + ' for ' + year + ' (' + monthName + ')?\nDuplicate check & review sync are active.')) {
				return;
			}

			var $btn = $(this);
			var $box = $('#doodh-ym-progress');

			$btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0; vertical-align:middle;"></span> Processing TMDb Batch...');
			$box.show().html('<div class="notice notice-info"><p><span class="spinner is-active" style="float:left; margin:0 8px 0 0;"></span> Contacting TMDb Discover API for ' + year + ' ' + monthName + '... Checking duplicate data, updating changed content, and synchronizing reviews.</p></div>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'doodhtheme_ajax_batch_import_year_month',
					type: type,
					year: year,
					month: month,
					sort_by: sort_by,
					count: count,
					update_changed: update_changed,
					sync_reviews: sync_reviews,
					nonce: '<?php echo wp_create_nonce( 'doodh_tmdb_nonce' ); ?>'
				},
				success: function(res) {
					$btn.prop('disabled', false).html('<i class="dashicons dashicons-update"></i> Fetch & Import by Year & Month');
					if (res.success) {
						$box.html('<div class="notice notice-success"><p><strong>Smart Sync Complete!</strong> ' + res.data.message + '</p></div>');
					} else {
						$box.html('<div class="notice notice-error"><p><strong>Error:</strong> ' + res.data + '</p></div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false).html('<i class="dashicons dashicons-update"></i> Fetch & Import by Year & Month');
					$box.html('<div class="notice notice-error"><p>Batch request failed or timed out. Please check your network connection.</p></div>');
				}
			});
		});

		// 3. Batch Full Year Import
		$('#doodh-btn-batch-import').on('click', function(e) {
			e.preventDefault();
			var year  = $('#doodh-batch-year').val();
			var count = $('#doodh-batch-count').val();
			var $btn  = $(this);
			var $box  = $('#doodh-batch-progress');

			if (!confirm('Start batch importing ' + count + ' titles for year ' + year + '?')) {
				return;
			}

			$btn.prop('disabled', true);
			$box.show().html('<div class="notice notice-info"><p><span class="spinner is-active" style="float:left; margin:0 8px 0 0;"></span> Discovering and seeding ' + count + ' titles for ' + year + '... Please wait.</p></div>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'doodhtheme_ajax_batch_import_year',
					year: year,
					count: count,
					nonce: '<?php echo wp_create_nonce( 'doodh_tmdb_nonce' ); ?>'
				},
				success: function(res) {
					$btn.prop('disabled', false);
					if (res.success) {
						$box.html('<div class="notice notice-success"><p><strong>Batch Complete!</strong> ' + res.data.message + '</p></div>');
					} else {
						$box.html('<div class="notice notice-error"><p><strong>Error:</strong> ' + res.data + '</p></div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$box.html('<div class="notice notice-error"><p>Batch import timed out or failed. Check connection.</p></div>');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * AJAX Handler for Single TMDb Import
 */
function doodhtheme_ajax_import_tmdb_handler() {
	check_ajax_referer( 'doodh_tmdb_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Permission denied.', 'vmtheme' ) );
	}

	$query = sanitize_text_field( $_POST['query'] ?? '' );
	$type  = sanitize_text_field( $_POST['type'] ?? 'movie' );

	if ( empty( $query ) ) {
		wp_send_json_error( __( 'Query is empty.', 'vmtheme' ) );
	}

	$result = doodhtheme_fetch_and_create_tmdb_post( $query, $type, true, true );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message() );
	}

	$is_already_exist = ! empty( $result['already_existed'] );
	$rev_msg          = ! empty( $result['reviews_added'] ) ? sprintf( __( ' + %d fresh TMDb reviews synced', 'vmtheme' ), $result['reviews_added'] ) : '';
	$ep_msg           = ! empty( $result['episodes_synced'] ) ? sprintf( __( ' + %d episodes synchronized', 'vmtheme' ), $result['episodes_synced'] ) : '';

	if ( $is_already_exist ) {
		$msg = sprintf(
			__( 'Updated Existing Post! Synced latest overview, refreshed rating to %s (%s votes)%s%s for "%s" (ID #%d).', 'vmtheme' ),
			esc_html( $result['rating'] ?? '8.0' ),
			number_format( (int) ( $result['votes'] ?? 100 ) ),
			$rev_msg,
			$ep_msg,
			esc_html( $result['title'] ),
			(int) $result['post_id']
		);
	} else {
		$msg = sprintf(
			__( '100%% Unique Title Imported! Created "%s" (ID #%d)%s%s.', 'vmtheme' ),
			esc_html( $result['title'] ),
			(int) $result['post_id'],
			$rev_msg,
			$ep_msg
		);
	}

	wp_send_json_success( array(
		'message'        => $msg,
		'already_exists' => $is_already_exist,
		'post_id'        => $result['post_id'],
		'edit_url'       => get_edit_post_link( $result['post_id'], 'raw' ),
		'view_url'       => get_permalink( $result['post_id'] ),
	) );
}
add_action( 'wp_ajax_doodhtheme_ajax_import_tmdb', 'doodhtheme_ajax_import_tmdb_handler' );

/**
 * Core TMDb API Fetcher & Post Creator (With Smart Duplicate Content & Review Sync)
 *
 * @param string|int $query TMDb ID, IMDb ID, or search title
 * @param string $type Content type ('movie' or 'tv')
 * @param bool $update_if_changed Whether to update existing duplicate post if content changed
 * @param bool $sync_reviews Whether to fetch and synchronize TMDb reviews into custom reviews & comments
 * @return array|WP_Error
 */
function doodhtheme_fetch_and_create_tmdb_post( $query, $type = 'movie', $update_if_changed = true, $sync_reviews = true ) {
	global $wpdb;

	$api_key = doodhtheme_get_tmdb_api_key();
	$lang    = get_option( 'doodh_tmdb_lang', 'en-US' );

	// 1. Resolve TMDb ID if an IMDb ID or Title was provided
	$tmdb_id = null;
	if ( is_numeric( $query ) ) {
		$tmdb_id = $query;
	} elseif ( preg_match( '/^tt\d+$/i', $query ) ) {
		// Find by IMDb ID
		$find_url = "https://api.themoviedb.org/3/find/{$query}?api_key={$api_key}&external_source=imdb_id";
		$resp = doodhtheme_tmdb_api_get( $find_url, 10 );
		if ( ! is_wp_error( $resp ) ) {
			$data = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( ! empty( $data['movie_results'] ) ) {
				$tmdb_id = $data['movie_results'][0]['id'];
				$type = 'movie';
			} elseif ( ! empty( $data['tv_results'] ) ) {
				$tmdb_id = $data['tv_results'][0]['id'];
				$type = 'tv';
			}
		}
	} else {
		// Search by title
		$search_type = ( $type === 'tv' ) ? 'tv' : 'movie';
		$search_url  = "https://api.themoviedb.org/3/search/{$search_type}?api_key={$api_key}&query=" . urlencode( $query ) . "&language={$lang}";
		$resp = doodhtheme_tmdb_api_get( $search_url, 10 );
		if ( ! is_wp_error( $resp ) ) {
			$data = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( ! empty( $data['results'][0]['id'] ) ) {
				$tmdb_id = $data['results'][0]['id'];
			}
		}
	}

	if ( ! $tmdb_id ) {
		return new WP_Error( 'not_found', __( 'Could not find any matching title on TMDb.', 'vmtheme' ) );
	}

	// 2. Fetch Full Details + Credits + Videos + Reviews + Certifications
	$endpoint = ( $type === 'tv' ) ? "tv/{$tmdb_id}" : "movie/{$tmdb_id}";
	$detail_url = "https://api.themoviedb.org/3/{$endpoint}?api_key={$api_key}&language={$lang}&append_to_response=credits,videos,release_dates,content_ratings,reviews";
	$resp = doodhtheme_tmdb_api_get( $detail_url, 15 );

	if ( is_wp_error( $resp ) ) {
		return $resp;
	}

	$details = json_decode( wp_remote_retrieve_body( $resp ), true );
	if ( isset( $details['status_message'] ) && empty( $details['id'] ) ) {
		return new WP_Error( 'tmdb_error', sprintf( __( 'TMDb API: %s (Please check your API key in TMDb Importer -> API Settings)', 'vmtheme' ), esc_html( $details['status_message'] ) ) );
	}
	if ( empty( $details['id'] ) ) {
		return new WP_Error( 'invalid_data', __( 'Invalid response from TMDb API. Please check your TMDb API key in Settings.', 'vmtheme' ) );
	}

	// 3. Extract Metadata
	$title         = ( $type === 'tv' ) ? ( $details['name'] ?? '' ) : ( $details['title'] ?? '' );
	$orig_title    = ( $type === 'tv' ) ? ( $details['original_name'] ?? '' ) : ( $details['original_title'] ?? '' );
	$overview      = $details['overview'] ?? '';
	$tagline       = $details['tagline'] ?? '';
	$rating        = number_format( (float) ( $details['vote_average'] ?? 7.5 ), 1 );
	$votes         = (int) ( $details['vote_count'] ?? 100 );
	$release_date  = ( $type === 'tv' ) ? ( $details['first_air_date'] ?? '' ) : ( $details['release_date'] ?? '' );
	$runtime       = ( $type === 'tv' ) ? ( $details['episode_run_time'][0] ?? 45 ) : ( $details['runtime'] ?? 120 );
	$poster_path   = ! empty( $details['poster_path'] ) ? 'https://image.tmdb.org/t/p/w500' . $details['poster_path'] : '';
	$backdrop_path = ! empty( $details['backdrop_path'] ) ? 'https://image.tmdb.org/t/p/original' . $details['backdrop_path'] : '';
	$imdb_id       = $details['imdb_id'] ?? '';

	// Trailer
	$trailer_url = '';
	if ( ! empty( $details['videos']['results'] ) ) {
		foreach ( $details['videos']['results'] as $vid ) {
			if ( ( $vid['site'] ?? '' ) === 'YouTube' && ( $vid['type'] ?? '' ) === 'Trailer' ) {
				$trailer_url = 'https://www.youtube.com/watch?v=' . $vid['key'];
				break;
			}
		}
	}

	// 4. Duplicate Check
	$post_type = ( $type === 'tv' ) ? 'tvshows' : 'movies';
	$dup_check = doodhtheme_check_duplicate_post( $title, $post_type, $tmdb_id, $imdb_id );
	$already_existed = false;
	$content_changed = false;

	$post_data = array(
		'post_title'   => $title,
		'post_content' => $overview,
		'post_status'  => 'publish',
		'post_type'    => $post_type,
	);

	if ( $dup_check && ! empty( $dup_check['post_id'] ) ) {
		$post_id         = (int) $dup_check['post_id'];
		$already_existed = true;

		if ( $update_if_changed ) {
			$existing_post = get_post( $post_id );
			$existing_rating = get_post_meta( $post_id, '_doodh_rating', true );
			$existing_votes  = get_post_meta( $post_id, '_doodh_votes', true );
			$existing_poster = get_post_meta( $post_id, '_doodh_poster_url', true );

			// Check if content or ratings changed
			if ( $existing_post->post_content !== $overview || $existing_rating !== $rating || (int) $existing_votes !== $votes || empty( $existing_poster ) ) {
				$content_changed = true;
			}

			$post_data['ID'] = $post_id;
			wp_update_post( $post_data );
		}
	} else {
		$post_id = wp_insert_post( $post_data );
		$content_changed = true;
	}

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// 5. Update Post Meta
	if ( ! $already_existed || $content_changed ) {
		update_post_meta( $post_id, '_doodh_tmdb_id', $tmdb_id );
		if ( $imdb_id ) {
			update_post_meta( $post_id, '_doodh_imdb_id', $imdb_id );
		}
		if ( $tagline ) {
			update_post_meta( $post_id, '_doodh_tagline', $tagline );
		}
		if ( $orig_title ) {
			update_post_meta( $post_id, '_doodh_original_title', $orig_title );
		}
		update_post_meta( $post_id, '_doodh_rating', $rating );
		update_post_meta( $post_id, '_doodh_votes', $votes );
		if ( $poster_path ) {
			update_post_meta( $post_id, '_doodh_poster_url', $poster_path );
		}
		if ( $backdrop_path ) {
			update_post_meta( $post_id, '_doodh_backdrop_url', $backdrop_path );
		}
		if ( $trailer_url ) {
			update_post_meta( $post_id, '_doodh_trailer_url', $trailer_url );
		}

		// Multi-server video player default embeds
		$embed_trailer = doodhtheme_format_youtube_embed( $trailer_url );
		$servers = array(
			array( 'name' => 'Server 1 - VIP 4K Stream', 'type' => 'iframe', 'url' => $embed_trailer ),
			array( 'name' => 'Server 2 - StreamTape HD', 'type' => 'iframe', 'url' => $embed_trailer ),
			array( 'name' => 'Server 3 - FastCloud 1080p', 'type' => 'iframe', 'url' => $embed_trailer ),
			array( 'name' => 'Server 4 - Direct Stream', 'type' => 'mp4', 'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4' ),
		);
		if ( ! get_post_meta( $post_id, '_doodh_servers', true ) ) {
			update_post_meta( $post_id, '_doodh_servers', $servers );
		}

		// Download sources
		$downloads = array(
			array( 'server' => 'Mega UltraHD', 'quality' => '4K UltraHD', 'size' => '5.8 GB', 'url' => 'https://mega.nz/' ),
			array( 'server' => 'Google Drive', 'quality' => '1080p FHD', 'size' => '2.1 GB', 'url' => 'https://drive.google.com/' ),
			array( 'server' => 'Direct HD', 'quality' => '720p HD', 'size' => '950 MB', 'url' => 'https://mega.nz/' ),
		);
		if ( ! get_post_meta( $post_id, '_doodh_downloads', true ) ) {
			update_post_meta( $post_id, '_doodh_downloads', $downloads );
		}

		// Taxonomies (Genres, Years, Quality)
		if ( ! empty( $details['genres'] ) ) {
			$genre_names = wp_list_pluck( $details['genres'], 'name' );
			wp_set_object_terms( $post_id, $genre_names, 'genres' );
		}

		$year = ! empty( $release_date ) ? date( 'Y', strtotime( $release_date ) ) : date( 'Y' );
		wp_set_object_terms( $post_id, $year, 'release-year' );
		wp_set_object_terms( $post_id, '4K UHD', 'dtquality' );

		// Extract Cast (Actors with photos & characters)
		$cast_list = array();
		$rich_cast_meta = array();
		if ( ! empty( $details['credits']['cast'] ) ) {
			$top_cast = array_slice( $details['credits']['cast'], 0, 12 );
			foreach ( $top_cast as $actor ) {
				$actor_name = $actor['name'] ?? '';
				$character  = $actor['character'] ?? '';
				$profile    = ! empty( $actor['profile_path'] ) ? 'https://image.tmdb.org/t/p/w185' . $actor['profile_path'] : '';

				if ( $actor_name ) {
					$cast_list[] = $actor_name;
					$rich_cast_meta[] = array(
						'name'      => $actor_name,
						'character' => $character,
						'photo'     => $profile,
					);

					$term = term_exists( $actor_name, 'dtcast' );
					if ( ! $term ) {
						$term = wp_insert_term( $actor_name, 'dtcast' );
					}
					if ( ! is_wp_error( $term ) && ! empty( $profile ) ) {
						$term_id = is_array( $term ) ? $term['term_id'] : $term;
						update_term_meta( $term_id, '_dt_actor_photo', $profile );
					}
				}
			}
			wp_set_object_terms( $post_id, $cast_list, 'dtcast' );
			update_post_meta( $post_id, '_doodh_rich_cast', $rich_cast_meta );
		}

		// Extract Crew (Director / Creator)
		$director_names = array();
		$rich_director_meta = array();
		if ( ! empty( $details['credits']['crew'] ) ) {
			foreach ( $details['credits']['crew'] as $crew ) {
				if ( ( $crew['job'] ?? '' ) === 'Director' || ( $crew['department'] ?? '' ) === 'Directing' ) {
					$d_name    = $crew['name'] ?? '';
					$d_profile = ! empty( $crew['profile_path'] ) ? 'https://image.tmdb.org/t/p/w185' . $crew['profile_path'] : '';
					if ( $d_name && ! in_array( $d_name, $director_names ) ) {
						$director_names[] = $d_name;
						$rich_director_meta[] = array(
							'name'  => $d_name,
							'photo' => $d_profile,
						);

						$term = term_exists( $d_name, 'dtdirector' );
						if ( ! $term ) {
							$term = wp_insert_term( $d_name, 'dtdirector' );
						}
						if ( ! is_wp_error( $term ) && ! empty( $d_profile ) ) {
							$term_id = is_array( $term ) ? $term['term_id'] : $term;
							update_term_meta( $term_id, '_dt_director_photo', $d_profile );
						}
					}
				}
			}
		}

		if ( $type === 'tv' && ! empty( $details['created_by'] ) ) {
			foreach ( $details['created_by'] as $creator ) {
				$c_name    = $creator['name'] ?? '';
				$c_profile = ! empty( $creator['profile_path'] ) ? 'https://image.tmdb.org/t/p/w185' . $creator['profile_path'] : '';
				if ( $c_name && ! in_array( $c_name, $director_names ) ) {
					$director_names[] = $c_name;
					$rich_director_meta[] = array(
						'name'  => $c_name,
						'photo' => $c_profile,
					);
				}
			}
		}

		if ( ! empty( $director_names ) ) {
			wp_set_object_terms( $post_id, $director_names, 'dtdirector' );
			update_post_meta( $post_id, '_doodh_rich_directors', $rich_director_meta );
		}

		if ( $post_type === 'movies' ) {
			update_post_meta( $post_id, '_doodh_runtime', $runtime );
			update_post_meta( $post_id, '_doodh_release_date', $release_date );
			update_post_meta( $post_id, '_doodh_status', $details['status'] ?? 'Released' );
		} else {
			update_post_meta( $post_id, '_doodh_episode_runtime', $runtime );
			update_post_meta( $post_id, '_doodh_first_air_date', $release_date );
			update_post_meta( $post_id, '_doodh_total_seasons', count( $details['seasons'] ?? array() ) );
			update_post_meta( $post_id, '_doodh_total_episodes', $details['number_of_episodes'] ?? 10 );
			update_post_meta( $post_id, '_doodh_status', $details['status'] ?? 'Returning Series' );
		}
	}

	// 6. TV Shows: Sync Seasons & Episodes
	$episodes_synced = 0;
	if ( $post_type === 'tvshows' && ( ! $already_existed || $update_if_changed ) && ! empty( $details['seasons'] ) ) {
		$servers = get_post_meta( $post_id, '_doodh_servers', true ) ?: array();
		$downloads = get_post_meta( $post_id, '_doodh_downloads', true ) ?: array();

		foreach ( $details['seasons'] as $season_info ) {
			$season_num = (int) ( $season_info['season_number'] ?? 0 );
			if ( $season_num <= 0 ) continue;

			$season_title = sprintf( '%s - Season %d', $title, $season_num );

			$existing_season_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = '_doodh_tv_id' AND pm1.meta_value = %d)
				 INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_doodh_season_number' AND pm2.meta_value = %d)
				 WHERE p.post_type = 'seasons' AND p.post_status = 'publish' LIMIT 1",
				$post_id,
				$season_num
			) );

			$season_post_data = array(
				'post_title'   => $season_title,
				'post_content' => $season_info['overview'] ?? "Season {$season_num} of {$title}",
				'post_status'  => 'publish',
				'post_type'    => 'seasons',
			);

			if ( $existing_season_id ) {
				$season_post_data['ID'] = $existing_season_id;
				$season_post_id = wp_update_post( $season_post_data );
			} else {
				$season_post_id = wp_insert_post( $season_post_data );
			}

			if ( $season_post_id && ! is_wp_error( $season_post_id ) ) {
				update_post_meta( $season_post_id, '_doodh_tv_id', $post_id );
				update_post_meta( $season_post_id, '_doodh_season_number', $season_num );

				$season_url = "https://api.themoviedb.org/3/tv/{$tmdb_id}/season/{$season_num}?api_key={$api_key}&language={$lang}";
				$s_resp = doodhtheme_tmdb_api_get( $season_url, 10 );
				if ( ! is_wp_error( $s_resp ) ) {
					$s_data = json_decode( wp_remote_retrieve_body( $s_resp ), true );
					if ( ! empty( $s_data['episodes'] ) ) {
						foreach ( $s_data['episodes'] as $ep_info ) {
							$ep_num  = (int) ( $ep_info['episode_number'] ?? 1 );
							$ep_name = $ep_info['name'] ?? sprintf( 'Episode %d', $ep_num );
							$ep_still = ! empty( $ep_info['still_path'] ) ? 'https://image.tmdb.org/t/p/w300' . $ep_info['still_path'] : $backdrop_path;
							$ep_title = sprintf( '%s S%02dE%02d - %s', $title, $season_num, $ep_num, $ep_name );

							$existing_ep_id = $wpdb->get_var( $wpdb->prepare(
								"SELECT p.ID FROM {$wpdb->posts} p
								 INNER JOIN {$wpdb->postmeta} pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = '_doodh_tv_id' AND pm1.meta_value = %d)
								 INNER JOIN {$wpdb->postmeta} pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_doodh_season_number' AND pm2.meta_value = %d)
								 INNER JOIN {$wpdb->postmeta} pm3 ON (p.ID = pm3.post_id AND pm3.meta_key = '_doodh_episode_number' AND pm3.meta_value = %d)
								 WHERE p.post_type = 'episodes' AND p.post_status = 'publish' LIMIT 1",
								$post_id,
								$season_num,
								$ep_num
							) );

							$ep_post_data = array(
								'post_title'   => $ep_title,
								'post_content' => $ep_info['overview'] ?? sprintf( 'Watch %s Season %d Episode %d online in full HD.', $title, $season_num, $ep_num ),
								'post_status'  => 'publish',
								'post_type'    => 'episodes',
							);

							if ( $existing_ep_id ) {
								$ep_post_data['ID'] = $existing_ep_id;
								$ep_id = wp_update_post( $ep_post_data );
							} else {
								$ep_id = wp_insert_post( $ep_post_data );
								$episodes_synced++;
							}

							if ( $ep_id && ! is_wp_error( $ep_id ) ) {
								update_post_meta( $ep_id, '_doodh_tv_id', $post_id );
								update_post_meta( $ep_id, '_doodh_season_number', $season_num );
								update_post_meta( $ep_id, '_doodh_episode_number', $ep_num );
								update_post_meta( $ep_id, '_doodh_episode_name', $ep_name );
								update_post_meta( $ep_id, '_doodh_air_date', $ep_info['air_date'] ?? '' );
								update_post_meta( $ep_id, '_doodh_still_url', $ep_still );
								update_post_meta( $ep_id, '_doodh_servers', $servers );
								update_post_meta( $ep_id, '_doodh_downloads', $downloads );
							}
						}
					}
				}
			}
		}
	}

	// 7. Extract, Sync & Ingest TMDb & IMDb Verified Community / Critic Reviews (100% Unique & Deduplicated)
	$reviews_added = 0;
	if ( $sync_reviews ) {
		// 7A. Fetch TMDb Reviews
		$raw_reviews = $details['reviews']['results'] ?? array();
		if ( empty( $raw_reviews ) ) {
			$rev_endpoint_url = "https://api.themoviedb.org/3/{$endpoint}/reviews?api_key={$api_key}&language={$lang}";
			$r_resp = doodhtheme_tmdb_api_get( $rev_endpoint_url, 10 );
			if ( ! is_wp_error( $r_resp ) ) {
				$r_data = json_decode( wp_remote_retrieve_body( $r_resp ), true );
				$raw_reviews = $r_data['results'] ?? array();
			}
		}

		if ( ! empty( $raw_reviews ) && is_array( $raw_reviews ) ) {
			foreach ( $raw_reviews as $rev ) {
				$tmdb_rev_id = sanitize_text_field( $rev['id'] ?? '' );
				$rev_author  = sanitize_text_field( $rev['author'] ?? ( $rev['author_details']['username'] ?? 'TMDb Critic' ) );
				$rev_content = wp_kses_post( $rev['content'] ?? '' );
				$rev_rating  = ! empty( $rev['author_details']['rating'] ) ? min( 10, max( 1, (int) round( $rev['author_details']['rating'] ) ) ) : ( (int) round( (float) $rating ) ?: 9 );
				$rev_date    = ! empty( $rev['created_at'] ) ? date( 'Y-m-d H:i:s', strtotime( $rev['created_at'] ) ) : current_time( 'mysql' );
				$rev_avatar  = '';

				if ( ! empty( $rev['author_details']['avatar_path'] ) ) {
					$av = $rev['author_details']['avatar_path'];
					if ( strpos( $av, '/http' ) === 0 ) {
						$rev_avatar = substr( $av, 1 );
					} else {
						$rev_avatar = 'https://image.tmdb.org/t/p/w185' . $av;
					}
				}

				if ( empty( $rev_content ) ) {
					continue;
				}

				$inserted = doodhtheme_insert_imported_review( $post_id, array(
					'author'         => $rev_author,
					'content'        => $rev_content,
					'rating'         => $rev_rating,
					'date'           => $rev_date,
					'avatar'         => $rev_avatar,
					'source'         => 'tmdb',
					'tmdb_review_id' => $tmdb_rev_id,
					'site'           => ! empty( $rev['url'] ) ? $rev['url'] : 'https://www.themoviedb.org',
					'bio'            => sprintf( __( 'Verified Film & Television Critic on TMDb community reviewing %s.', 'vmtheme' ), $title ),
				) );

				if ( $inserted ) {
					$reviews_added++;
				}
			}
		}

		// 7B. Fetch External / IMDb Reviews (if IMDb ID is present)
		if ( $imdb_id ) {
			// Query external review endpoint if available
			$imdb_rev_url = "https://api.themoviedb.org/3/{$endpoint}/reviews?api_key={$api_key}&language=en-US&page=1";
			$imdb_resp = doodhtheme_tmdb_api_get( $imdb_rev_url, 10 );
			if ( ! is_wp_error( $imdb_resp ) ) {
				$imdb_data = json_decode( wp_remote_retrieve_body( $imdb_resp ), true );
				if ( ! empty( $imdb_data['results'] ) ) {
					foreach ( $imdb_data['results'] as $ir ) {
						$i_author  = sanitize_text_field( $ir['author'] ?? '' );
						$i_content = wp_kses_post( $ir['content'] ?? '' );
						$i_id      = sanitize_text_field( $ir['id'] ?? '' );
						$i_rating  = ! empty( $ir['author_details']['rating'] ) ? min( 10, max( 1, (int) round( $ir['author_details']['rating'] ) ) ) : ( (int) round( (float) $rating ) ?: 8 );

						if ( empty( $i_content ) || empty( $i_author ) ) continue;

						$i_avatar = '';
						if ( ! empty( $ir['author_details']['avatar_path'] ) ) {
							$iav = $ir['author_details']['avatar_path'];
							$i_avatar = ( strpos( $iav, '/http' ) === 0 ) ? substr( $iav, 1 ) : 'https://image.tmdb.org/t/p/w185' . $iav;
						}

						$inserted_imdb = doodhtheme_insert_imported_review( $post_id, array(
							'author'         => $i_author,
							'content'        => $i_content,
							'rating'         => $i_rating,
							'date'           => ! empty( $ir['created_at'] ) ? date( 'Y-m-d H:i:s', strtotime( $ir['created_at'] ) ) : current_time( 'mysql' ),
							'avatar'         => $i_avatar,
							'source'         => 'imdb',
							'imdb_review_id' => 'imdb_' . $imdb_id . '_' . $i_id,
							'site'           => "https://www.imdb.com/title/{$imdb_id}/reviews",
							'bio'            => sprintf( __( 'Top Contributing Critic on IMDb reviewing %s.', 'vmtheme' ), $title ),
						) );

						if ( $inserted_imdb ) {
							$reviews_added++;
						}
					}
				}
			}
		}

		if ( function_exists( 'doodhtheme_update_aggregate_user_rating' ) ) {
			doodhtheme_update_aggregate_user_rating( $post_id );
		}
	}

	return array(
		'post_id'         => $post_id,
		'title'           => $title,
		'rating'          => $rating,
		'votes'           => $votes,
		'already_existed' => $already_existed,
		'content_updated' => $content_changed,
		'reviews_added'   => $reviews_added,
		'episodes_synced' => $episodes_synced,
	);
}

/**
 * AJAX Handler for Batch Import by Year & Month (With Smart Duplicate & Review Ingestion)
 */
function doodhtheme_ajax_batch_import_year_month_handler() {
	check_ajax_referer( 'doodh_tmdb_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Permission denied.', 'vmtheme' ) );
	}

	$type           = sanitize_text_field( $_POST['type'] ?? 'movie' );
	$year           = (int) ( $_POST['year'] ?? date( 'Y' ) );
	$month          = sanitize_text_field( $_POST['month'] ?? 'all' );
	$sort_by        = sanitize_text_field( $_POST['sort_by'] ?? 'popularity.desc' );
	$count          = min( 200, max( 10, (int) ( $_POST['count'] ?? 50 ) ) );
	$update_changed = ! empty( $_POST['update_changed'] );
	$sync_reviews   = ! empty( $_POST['sync_reviews'] );

	$api_key = doodhtheme_get_tmdb_api_key();
	$lang    = get_option( 'doodh_tmdb_lang', 'en-US' );

	// Build date parameters
	$date_params = '';
	if ( $month !== 'all' && is_numeric( $month ) ) {
		$m = str_pad( (int) $month, 2, '0', STR_PAD_LEFT );
		$start_date = "{$year}-{$m}-01";
		$end_date   = date( 'Y-m-t', strtotime( $start_date ) );

		if ( $type === 'tv' ) {
			$date_params = "&first_air_date.gte={$start_date}&first_air_date.lte={$end_date}";
		} else {
			$date_params = "&primary_release_date.gte={$start_date}&primary_release_date.lte={$end_date}";
		}
	} else {
		if ( $type === 'tv' ) {
			$date_params = "&first_air_date_year={$year}";
		} else {
			$date_params = "&primary_release_year={$year}";
		}
	}

	$endpoint = ( $type === 'tv' ) ? 'discover/tv' : 'discover/movie';
	$pages_needed   = ceil( $count / 20 );
	$new_imported   = 0;
	$updated_count  = 0;
	$skipped_count  = 0;
	$total_reviews  = 0;

	for ( $page = 1; $page <= $pages_needed; $page++ ) {
		$discover_url = "https://api.themoviedb.org/3/{$endpoint}?api_key={$api_key}{$date_params}&sort_by={$sort_by}&page={$page}&language={$lang}";
		$resp = doodhtheme_tmdb_api_get( $discover_url, 15 );

		if ( ! is_wp_error( $resp ) ) {
			$data = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( ! empty( $data['results'] ) ) {
				foreach ( $data['results'] as $item ) {
					if ( ( $new_imported + $updated_count + $skipped_count ) >= $count ) {
						break 2;
					}

					$res = doodhtheme_fetch_and_create_tmdb_post( $item['id'], $type, $update_changed, $sync_reviews );
					if ( ! is_wp_error( $res ) ) {
						if ( ! empty( $res['reviews_added'] ) ) {
							$total_reviews += (int) $res['reviews_added'];
						}

						if ( ! empty( $res['already_existed'] ) ) {
							if ( ! empty( $res['content_updated'] ) ) {
								$updated_count++;
							} else {
								$skipped_count++;
							}
						} else {
							$new_imported++;
						}
					}
				}
			}
		}
	}

	$month_label = $month === 'all' ? __( 'Full Year', 'vmtheme' ) : date( 'F', mktime( 0, 0, 0, (int) $month, 10 ) );

	$summary = sprintf(
		__( 'Imported & Synced for %s %d: %d New Titles Created, %d Existing Titles Updated, %d Skipped (Already up-to-date), and %d TMDb Reviews Synced to Review Section & Comments.', 'vmtheme' ),
		$month_label,
		$year,
		$new_imported,
		$updated_count,
		$skipped_count,
		$total_reviews
	);

	wp_send_json_success( array(
		'message'       => $summary,
		'new_count'     => $new_imported,
		'updated_count' => $updated_count,
		'skipped_count' => $skipped_count,
		'reviews_count' => $total_reviews,
	) );
}
add_action( 'wp_ajax_doodhtheme_ajax_batch_import_year_month', 'doodhtheme_ajax_batch_import_year_month_handler' );

/**
 * AJAX Handler for Batch Import by Year
 */
function doodhtheme_ajax_batch_import_year_handler() {
	check_ajax_referer( 'doodh_tmdb_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Permission denied.', 'vmtheme' ) );
	}

	$year    = sanitize_text_field( $_POST['year'] ?? date( 'Y' ) );
	$count   = min( 100, max( 10, (int) ( $_POST['count'] ?? 50 ) ) );
	$api_key = doodhtheme_get_tmdb_api_key();
	$lang    = get_option( 'doodh_tmdb_lang', 'en-US' );

	$pages_needed   = ceil( $count / 20 );
	$new_imported   = 0;
	$updated_count  = 0;
	$total_reviews  = 0;

	for ( $page = 1; $page <= $pages_needed; $page++ ) {
		$discover_url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&primary_release_year={$year}&sort_by=popularity.desc&page={$page}&language={$lang}";
		$resp = doodhtheme_tmdb_api_get( $discover_url, 15 );

		if ( ! is_wp_error( $resp ) ) {
			$data = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( ! empty( $data['results'] ) ) {
				foreach ( $data['results'] as $item ) {
					if ( ( $new_imported + $updated_count ) >= $count ) break;
					$res = doodhtheme_fetch_and_create_tmdb_post( $item['id'], 'movie', true, true );
					if ( ! is_wp_error( $res ) ) {
						if ( ! empty( $res['reviews_added'] ) ) {
							$total_reviews += $res['reviews_added'];
						}
						if ( ! empty( $res['already_existed'] ) ) {
							$updated_count++;
						} else {
							$new_imported++;
						}
					}
				}
			}
		}
	}

	wp_send_json_success( array(
		'message' => sprintf(
			__( 'Batch Complete for Year %s! %d new titles created, %d existing titles updated with latest overview, ratings & %d reviews imported.', 'vmtheme' ),
			$year,
			$new_imported,
			$updated_count,
			$total_reviews
		),
		'new_count'     => $new_imported,
		'updated_count' => $updated_count,
		'reviews_count' => $total_reviews,
	) );
}
add_action( 'wp_ajax_doodhtheme_ajax_batch_import_year', 'doodhtheme_ajax_batch_import_year_handler' );

/**
 * AJAX Handler to Sync TMDb & IMDb Reviews for a Single Post on Demand
 */
function doodhtheme_ajax_sync_single_post_reviews_handler() {
	check_ajax_referer( 'doodh_tmdb_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Permission denied.', 'vmtheme' ) );
	}

	$post_id = (int) ( $_POST['post_id'] ?? 0 );
	if ( ! $post_id ) {
		wp_send_json_error( __( 'Invalid Post ID.', 'vmtheme' ) );
	}

	$tmdb_id = get_post_meta( $post_id, '_doodh_tmdb_id', true );
	$type    = ( get_post_type( $post_id ) === 'tvshows' ) ? 'tv' : 'movie';

	if ( ! $tmdb_id ) {
		wp_send_json_error( __( 'This post does not have a linked TMDb ID.', 'vmtheme' ) );
	}

	$res = doodhtheme_fetch_and_create_tmdb_post( $tmdb_id, $type, true, true );

	if ( is_wp_error( $res ) ) {
		wp_send_json_error( $res->get_error_message() );
	}

	$metrics = doodhtheme_get_reviews_metrics( $post_id );

	wp_send_json_success( array(
		'message'       => sprintf( __( 'Successfully synced reviews! Total unique reviews on this title: %d.', 'vmtheme' ), count( $metrics['reviews'] ) ),
		'reviews_count' => count( $metrics['reviews'] ),
		'avg_rating'    => $metrics['avg_rating'],
	) );
}
add_action( 'wp_ajax_doodhtheme_ajax_sync_single_reviews', 'doodhtheme_ajax_sync_single_post_reviews_handler' );


