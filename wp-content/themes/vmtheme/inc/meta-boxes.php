<?php
/**
 * Custom Meta Boxes for DoodhTheme (Movies, TV Shows, Seasons, Episodes, Video Players, Downloads)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes
 */
function doodhtheme_add_meta_boxes() {
	// 0. In-Post TMDb / IMDb Live Auto-Fetcher & Form Filler (Movies & TV Shows)
	add_meta_box(
		'doodhtheme_inpost_tmdb_fetcher',
		__( '⚡ TMDb & IMDb Live Auto-Fetcher & Form Filler', 'vmtheme' ),
		'doodhtheme_render_inpost_fetcher_metabox',
		array( 'movies', 'tvshows' ),
		'normal',
		'high'
	);

	// Movies Meta Box
	add_meta_box(
		'doodhtheme_movie_details',
		__( 'Movie Information & Metadata', 'vmtheme' ),
		'doodhtheme_render_movie_metabox',
		'movies',
		'normal',
		'high'
	);

	// Streaming Servers Meta Box (Movies & Episodes)
	add_meta_box(
		'doodhtheme_streaming_servers',
		__( 'Video Streaming Player Servers (Multi-Server)', 'vmtheme' ),
		'doodhtheme_render_servers_metabox',
		array( 'movies', 'episodes' ),
		'normal',
		'high'
	);

	// Download Links Meta Box (Movies, TV Shows, Episodes & Posts)
	add_meta_box(
		'doodhtheme_download_links',
		__( 'Download Links & File Sources', 'vmtheme' ),
		'doodhtheme_render_downloads_metabox',
		array( 'movies', 'tvshows', 'episodes', 'post' ),
		'normal',
		'default'
	);

	// TV Shows Meta Box
	add_meta_box(
		'doodhtheme_tv_details',
		__( 'TV Show Information & Metadata', 'vmtheme' ),
		'doodhtheme_render_tv_metabox',
		'tvshows',
		'normal',
		'high'
	);

	// Seasons Meta Box
	add_meta_box(
		'doodhtheme_season_details',
		__( 'Season Details', 'vmtheme' ),
		'doodhtheme_render_season_metabox',
		'seasons',
		'normal',
		'high'
	);

	// Episodes Meta Box
	add_meta_box(
		'doodhtheme_episode_details',
		__( 'Episode Details & Hierarchy', 'vmtheme' ),
		'doodhtheme_render_episode_metabox',
		'episodes',
		'normal',
		'high'
	);

	// Reviews & Ratings Manager Meta Box (Movies, TV Shows, Episodes, Posts)
	add_meta_box(
		'doodhtheme_reviews_manager',
		__( 'Reviews & Ratings Manager (Add & Manage Multiple Reviews)', 'vmtheme' ),
		'doodhtheme_render_reviews_metabox',
		array( 'movies', 'tvshows', 'episodes', 'post' ),
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'doodhtheme_add_meta_boxes' );

/**
 * Render In-Post TMDb & IMDb Live Auto-Fetcher & Form Filler Metabox
 *
 * @param WP_Post $post Current post object
 */
function doodhtheme_render_inpost_fetcher_metabox( $post ) {
	$current_type = ( $post->post_type === 'tvshows' ) ? 'tv' : 'movie';
	$saved_tmdb   = get_post_meta( $post->ID, '_doodh_tmdb_id', true );
	$saved_imdb   = get_post_meta( $post->ID, '_doodh_imdb_id', true );
	$initial_val  = $saved_tmdb ?: ( $saved_imdb ?: '' );
	?>
	<div class="doodh-inpost-fetcher-wrapper" style="background:#ffffff; border:1px solid #bfdbfe; border-radius:8px; padding:18px; box-shadow:0 2px 8px rgba(37,99,235,0.06);">
		<?php wp_nonce_field( 'doodh_tmdb_nonce', 'doodh_inpost_fetcher_nonce' ); ?>

		<!-- Header & Info -->
		<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:15px;">
			<div style="display:flex; align-items:center; gap:10px;">
				<div style="background:#2563eb; color:#fff; width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center;">
					<span class="dashicons dashicons-cloud-download" style="font-size:20px; width:20px; height:20px;"></span>
				</div>
				<div>
					<h3 style="margin:0; font-size:15px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px;">
						<?php esc_html_e( 'TMDb & IMDb Instant Form Importer', 'vmtheme' ); ?>
						<span style="background:#dbeafe; color:#1e40af; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; text-transform:uppercase;">Live Fill & Edit</span>
					</h3>
					<p style="margin:2px 0 0 0; font-size:12px; color:#64748b;">
						<?php esc_html_e( 'Auto-fills Title, Story, Metadata, Taxonomies, Posters, Servers & Reviews into the form below. You can change any field anytime before saving.', 'vmtheme' ); ?>
					</p>
				</div>
			</div>

			<div style="display:flex; align-items:center; gap:8px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=doodh-tmdb-settings' ) ); ?>" target="_blank" style="font-size:11.5px; color:#2563eb; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
					<span class="dashicons dashicons-admin-generic" style="font-size:14px; width:14px; height:14px;"></span> <?php esc_html_e( 'API Settings', 'vmtheme' ); ?>
				</a>
			</div>
		</div>

		<!-- Input & Action Bar -->
		<div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
			<div style="width:140px;">
				<select id="doodh-inpost-type" style="width:100%; height:38px; font-weight:600; border-color:#cbd5e1; border-radius:6px;">
					<option value="movie" <?php selected( $current_type, 'movie' ); ?>><?php esc_html_e( '🎬 Movie', 'vmtheme' ); ?></option>
					<option value="tv" <?php selected( $current_type, 'tv' ); ?>><?php esc_html_e( '📺 TV Show', 'vmtheme' ); ?></option>
				</select>
			</div>

			<div style="flex:1; min-width:260px; position:relative;">
				<input type="text" id="doodh-inpost-query" value="<?php echo esc_attr( $initial_val ); ?>" placeholder="<?php esc_attr_e( 'Enter TMDb ID (e.g. 27205), IMDb ID (e.g. tt1375666), or Movie/TV Title...', 'vmtheme' ); ?>" style="width:100%; height:38px; padding:0 32px 0 12px; font-size:13.5px; border:1px solid #cbd5e1; border-radius:6px;">
				<button type="button" id="doodh-inpost-clear-btn" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:16px; display:none;" title="<?php esc_attr_e( 'Clear query', 'vmtheme' ); ?>">&times;</button>
			</div>

			<button type="button" id="doodh-btn-inpost-fetch" class="button button-primary" style="height:38px; padding:0 16px; font-weight:600; font-size:13px; background:#2563eb; border-color:#1d4ed8; display:inline-flex; align-items:center; gap:6px;">
				<span class="dashicons dashicons-update" style="font-size:16px; line-height:36px; width:16px; height:36px;"></span>
				<span><?php esc_html_e( 'Fetch & Auto-Fill Form', 'vmtheme' ); ?></span>
			</button>

			<button type="button" id="doodh-btn-inpost-search" class="button button-secondary" style="height:38px; padding:0 14px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:5px;" title="<?php esc_attr_e( 'Search TMDb for multiple title candidates to select from', 'vmtheme' ); ?>">
				<span class="dashicons dashicons-search" style="font-size:16px; line-height:36px; width:16px; height:36px;"></span>
				<span><?php esc_html_e( 'Search Matches...', 'vmtheme' ); ?></span>
			</button>
		</div>

		<!-- Status & Progress Container -->
		<div id="doodh-inpost-status-box" style="margin-top:14px; display:none;"></div>

		<!-- Live Search Candidates Modal Dropdown -->
		<div id="doodh-inpost-search-modal" style="display:none; margin-top:14px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:15px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
				<strong style="font-size:13px; color:#1e293b;">
					<span class="dashicons dashicons-list-view" style="vertical-align:middle; color:#2563eb;"></span> <?php esc_html_e( 'Matching Search Results (Click to Fill Form):', 'vmtheme' ); ?>
				</strong>
				<button type="button" id="doodh-inpost-close-search" style="background:none; border:none; color:#64748b; font-size:18px; cursor:pointer; line-height:1;">&times;</button>
			</div>
			<div id="doodh-inpost-search-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px; max-height:320px; overflow-y:auto; padding-right:4px;"></div>
		</div>

		<!-- Live Imported Preview Summary Card -->
		<div id="doodh-inpost-preview-card" style="display:none; margin-top:16px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:16px;">
			<div style="display:flex; gap:16px; flex-wrap:wrap; align-items:flex-start;">
				<!-- Thumbnail Poster -->
				<div style="width:90px; height:135px; border-radius:6px; overflow:hidden; background:#0f172a; flex-shrink:0; border:1px solid #cbd5e1; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
					<img id="doodh-prev-poster" src="" alt="Poster" style="width:100%; height:100%; object-fit:cover; display:block;">
				</div>

				<!-- Info Details -->
				<div style="flex:1; min-width:240px;">
					<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px; flex-wrap:wrap;">
						<div>
							<h4 id="doodh-prev-title" style="margin:0 0 4px 0; font-size:16px; font-weight:700; color:#0f172a;"></h4>
							<p id="doodh-prev-tagline" style="margin:0 0 6px 0; font-size:12px; font-style:italic; color:#64748b;"></p>
						</div>
						<div style="display:flex; gap:6px; align-items:center;">
							<span id="doodh-prev-rating" style="background:#fef08a; color:#854d0e; font-size:12px; font-weight:700; padding:2px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:3px;">
								<span class="dashicons dashicons-star-filled" style="font-size:14px; width:14px; height:14px;"></span> <span class="doodh-rating-val">8.5</span>
							</span>
							<span id="doodh-prev-cert" style="background:#e2e8f0; color:#334155; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px;">PG-13</span>
						</div>
					</div>

					<div style="font-size:12px; color:#475569; display:flex; flex-wrap:wrap; gap:12px; margin-bottom:8px;">
						<span><strong>Release:</strong> <span id="doodh-prev-date">-</span></span>
						<span><strong>Runtime:</strong> <span id="doodh-prev-runtime">-</span></span>
						<span><strong>TMDb ID:</strong> <span id="doodh-prev-tmid">-</span></span>
						<span><strong>IMDb ID:</strong> <span id="doodh-prev-imdb">-</span></span>
					</div>

					<div id="doodh-prev-genres" style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:8px;"></div>

					<p id="doodh-prev-overview" style="margin:0 0 10px 0; font-size:12px; color:#334155; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"></p>

					<!-- Action Buttons in Preview -->
					<div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
						<button type="button" id="doodh-btn-sideload-thumb" class="button button-secondary button-small" style="font-size:12px; display:inline-flex; align-items:center; gap:4px;">
							<span class="dashicons dashicons-format-image" style="font-size:14px; width:14px; height:14px;"></span>
							<span><?php esc_html_e( '📥 Sideload Poster as WordPress Featured Image', 'vmtheme' ); ?></span>
						</button>
						<span id="doodh-sideload-status" style="font-size:11.5px; color:#15803d; font-weight:600;"></span>
					</div>
				</div>
			</div>

			<div style="margin-top:12px; padding-top:10px; border-top:1px solid #bbf7d0; font-size:12px; color:#166534; display:flex; align-items:center; gap:6px;">
				<span class="dashicons dashicons-yes-alt" style="font-size:16px; color:#16a34a;"></span>
				<strong><?php esc_html_e( 'Form Auto-Filled Successfully!', 'vmtheme' ); ?></strong>
				<span><?php esc_html_e( 'All inputs below have been populated with fresh TMDb data. You can now edit any text, replace URLs, adjust servers, or modify reviews below before publishing.', 'vmtheme' ); ?></span>
			</div>
		</div>
	</div>

	<style>
		.doodh-field-highlight {
			animation: doodhPulseField 1.8s ease;
			border-color: #3b82f6 !important;
			background-color: #eff6ff !important;
		}
		@keyframes doodhPulseField {
			0% { background-color: #dbeafe; border-color: #2563eb; }
			100% { background-color: #ffffff; border-color: #cbd5e1; }
		}
		.doodh-search-result-card {
			display: flex;
			gap: 10px;
			background: #ffffff;
			border: 1px solid #cbd5e1;
			border-radius: 6px;
			padding: 8px;
			cursor: pointer;
			transition: all 0.15s ease;
		}
		.doodh-search-result-card:hover {
			border-color: #2563eb;
			background: #eff6ff;
			transform: translateY(-1px);
			box-shadow: 0 2px 6px rgba(37,99,235,0.15);
		}
	</style>

	<script>
	jQuery(document).ready(function($) {
		var currentPayload = null;
		var nonce = $('#doodh_inpost_fetcher_nonce').val() || '<?php echo wp_create_nonce( 'doodh_tmdb_nonce' ); ?>';

		// Toggle clear button
		$('#doodh-inpost-query').on('input', function() {
			if ($(this).val().trim().length > 0) {
				$('#doodh-inpost-clear-btn').show();
			} else {
				$('#doodh-inpost-clear-btn').hide();
			}
		}).trigger('input');

		$('#doodh-inpost-clear-btn').on('click', function() {
			$('#doodh-inpost-query').val('').focus();
			$(this).hide();
		});

		// 1. Direct Fetch & Auto-Fill Form
		$('#doodh-btn-inpost-fetch').on('click', function(e) {
			e.preventDefault();
			var query = $('#doodh-inpost-query').val().trim();
			var type  = $('#doodh-inpost-type').val();

			if (!query) {
				alert('Please enter a TMDb ID, IMDb ID, or Title.');
				$('#doodh-inpost-query').focus();
				return;
			}

			executeFetch(query, type);
		});

		// Allow Enter key to fetch
		$('#doodh-inpost-query').on('keydown', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				$('#doodh-btn-inpost-fetch').click();
			}
		});

		// 2. Search Matches Candidate Popup
		$('#doodh-btn-inpost-search').on('click', function(e) {
			e.preventDefault();
			var query = $('#doodh-inpost-query').val().trim();
			var type  = $('#doodh-inpost-type').val();

			if (!query) {
				alert('Please enter a search title keyword.');
				$('#doodh-inpost-query').focus();
				return;
			}

			var $btn = $(this);
			var $status = $('#doodh-inpost-status-box');
			var $modal = $('#doodh-inpost-search-modal');
			var $grid  = $('#doodh-inpost-search-grid');

			$btn.prop('disabled', true);
			$status.show().html('<div class="notice notice-info inline"><p><span class="spinner is-active" style="float:none; margin:0 6px 0 0; vertical-align:middle;"></span> Searching TMDb for "' + query + '"...</p></div>');
			$modal.hide();

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'doodhtheme_ajax_search_tmdb_titles',
					query: query,
					type: type,
					nonce: nonce
				},
				success: function(res) {
					$btn.prop('disabled', false);
					$status.hide();
					if (res.success && res.data && res.data.length) {
						$grid.empty();
						res.data.forEach(function(item) {
							var posterImg = item.poster ? '<img src="' + item.poster + '" style="width:45px; height:68px; object-fit:cover; border-radius:4px; flex-shrink:0;">' : '<div style="width:45px; height:68px; background:#0f172a; border-radius:4px; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:10px;">No Pic</div>';
							var card = $('<div class="doodh-search-result-card" data-id="' + item.id + '">' +
								posterImg +
								'<div style="flex:1; min-width:0;">' +
									'<div style="font-weight:700; font-size:13px; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + item.title + ' (' + item.year + ')</div>' +
									'<div style="font-size:11px; color:#d97706; font-weight:600; margin:2px 0;">★ ' + item.rating + ' / 10</div>' +
									'<div style="font-size:11px; color:#64748b; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">' + (item.overview || 'No synopsis available.') + '</div>' +
								'</div>' +
								'<button type="button" class="button button-small doodh-btn-pick-candidate" style="align-self:center; font-weight:600; font-size:11px; background:#2563eb; color:#fff; border:none;">Fill &rarr;</button>' +
							'</div>');
							$grid.append(card);
						});
						$modal.show();
					} else {
						$status.show().html('<div class="notice notice-warning inline"><p>' + (res.data || 'No matching titles found.') + '</p></div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$status.show().html('<div class="notice notice-error inline"><p>Failed to connect to search service.</p></div>');
				}
			});
		});

		$('#doodh-inpost-close-search').on('click', function() {
			$('#doodh-inpost-search-modal').hide();
		});

		// Pick candidate from search grid
		$(document).on('click', '.doodh-search-result-card', function(e) {
			e.preventDefault();
			var tmdbId = $(this).data('id');
			var type = $('#doodh-inpost-type').val();
			$('#doodh-inpost-search-modal').hide();
			$('#doodh-inpost-query').val(tmdbId);
			executeFetch(tmdbId, type);
		});

		// Core Execution Function: Fetch & Auto-Fill
		function executeFetch(query, type) {
			var $btn    = $('#doodh-btn-inpost-fetch');
			var $status = $('#doodh-inpost-status-box');
			var $prev   = $('#doodh-inpost-preview-card');

			$btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0; vertical-align:middle;"></span> Fetching & Filling...');
			$status.show().html('<div class="notice notice-info inline"><p><span class="spinner is-active" style="float:none; margin:0 6px 0 0; vertical-align:middle;"></span> Fetching complete details from TMDb & IMDb API... Auto-filling form fields in real-time.</p></div>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'doodhtheme_ajax_fetch_tmdb_raw_data',
					query: query,
					type: type,
					nonce: nonce
				},
				success: function(res) {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-update" style="font-size:16px; line-height:36px; width:16px; height:36px;"></span> <span>Fetch & Auto-Fill Form</span>');
					if (res.success && res.data) {
						currentPayload = res.data;
						$status.hide();
						populateAllFormFields(res.data, type);
						renderPreviewSummary(res.data);
					} else {
						$status.show().html('<div class="notice notice-error inline"><p><strong>Error:</strong> ' + (res.data || 'Could not fetch data from TMDb.') + '</p></div>');
					}
				},
				error: function() {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-update" style="font-size:16px; line-height:36px; width:16px; height:36px;"></span> <span>Fetch & Auto-Fill Form</span>');
					$status.show().html('<div class="notice notice-error inline"><p>Server communication error. Please verify your internet connection.</p></div>');
				}
			});
		}

		// Master Form Auto-Filler
		function populateAllFormFields(data, type) {
			// 1. Post Title
			if ($('#title').length) {
				$('#title').val(data.title).trigger('input').trigger('change').addClass('doodh-field-highlight');
			}
			// Gutenberg Title Support
			if (window.wp && wp.data && wp.data.dispatch && wp.data.select('core/editor')) {
				try {
					wp.data.dispatch('core/editor').editPost({ title: data.title });
				} catch (e) {}
			}

			// 2. Post Content / Overview
			if ($('#content').length) {
				$('#content').val(data.overview).trigger('change').addClass('doodh-field-highlight');
			}
			if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
				try {
					tinymce.get('content').setContent(data.overview);
				} catch (e) {}
			}
			// Gutenberg Content Support
			if (window.wp && wp.data && wp.data.dispatch && wp.data.select('core/editor')) {
				try {
					wp.data.dispatch('core/editor').editPost({ content: data.overview });
				} catch (e) {}
			}

			// 3. Metadata Fields
			var isTV = (type === 'tv');

			// Movie & Shared fields
			setAndHighlight('#doodh_original_title', data.original_title);
			setAndHighlight('#doodh_tv_original_title', data.original_title);
			setAndHighlight('#doodh_tagline', data.tagline);
			setAndHighlight('#doodh_tv_tagline', data.tagline);
			setAndHighlight('#doodh_release_date', data.release_date);
			setAndHighlight('#doodh_first_air_date', data.first_air_date || data.release_date);
			setAndHighlight('#doodh_last_air_date', data.last_air_date);
			setAndHighlight('#doodh_runtime', data.runtime);
			setAndHighlight('#doodh_episode_runtime', data.runtime);
			setAndHighlight('#doodh_rating', data.rating);
			setAndHighlight('#doodh_tv_rating', data.rating);
			setAndHighlight('#doodh_votes', data.votes);
			setAndHighlight('#doodh_tv_votes', data.votes);
			setAndHighlight('#doodh_tmdb_id', data.tmdb_id);
			setAndHighlight('#doodh_tv_tmdb_id', data.tmdb_id);
			setAndHighlight('#doodh_imdb_id', data.imdb_id);
			setAndHighlight('#doodh_tv_imdb_id', data.imdb_id);
			setAndHighlight('#doodh_certification', data.certification);
			setAndHighlight('#doodh_tv_certification', data.certification);
			setAndHighlight('#doodh_status', data.status);
			setAndHighlight('#doodh_tv_status', data.status);
			setAndHighlight('#doodh_trailer_url', data.trailer_url);
			setAndHighlight('#doodh_tv_trailer_url', data.trailer_url);
			setAndHighlight('#doodh_poster_url', data.poster_url);
			setAndHighlight('#doodh_tv_poster_url', data.poster_url);
			setAndHighlight('#doodh_backdrop_url', data.backdrop_url);
			setAndHighlight('#doodh_tv_backdrop_url', data.backdrop_url);
			setAndHighlight('#doodh_total_seasons', data.total_seasons);
			setAndHighlight('#doodh_total_episodes', data.total_episodes);

			// Update visual thumbnail previews next to poster & backdrop fields if available
			updateImageLiveThumb('#doodh_poster_url', data.poster_url);
			updateImageLiveThumb('#doodh_tv_poster_url', data.poster_url);
			updateImageLiveThumb('#doodh_backdrop_url', data.backdrop_url);
			updateImageLiveThumb('#doodh_tv_backdrop_url', data.backdrop_url);

			// 4. Taxonomies Auto-Tagging
			// Genres Checkbox matching
			if (data.genres && data.genres.length) {
				$('#genreschecklist input[type="checkbox"]').each(function() {
					var label = $(this).closest('label').text().trim().toLowerCase();
					var checkbox = this;
					data.genres.forEach(function(g) {
						if (label === g.toLowerCase()) {
							$(checkbox).prop('checked', true);
						}
					});
				});
				// If hierarchical tag box or flat input exists
				if ($('#new-tag-genres').length) {
					$('#new-tag-genres').val(data.genres.join(', '));
					$('#genres .tagadd').click();
				}
			}

			// Release Year
			if (data.year) {
				if ($('#new-tag-release-year').length) {
					$('#new-tag-release-year').val(data.year);
					$('#release-year .tagadd').click();
				}
				$('#release-yearchecklist input[type="checkbox"]').each(function() {
					var label = $(this).closest('label').text().trim();
					if (label === String(data.year)) {
						$(this).prop('checked', true);
					}
				});
			}

			// Quality (dtquality)
			if ($('#new-tag-dtquality').length) {
				$('#new-tag-dtquality').val('4K UHD');
				$('#dtquality .tagadd').click();
			}

			// Cast (dtcast)
			if (data.cast && data.cast.length && $('#new-tag-dtcast').length) {
				var topCast = data.cast.slice(0, 10).join(', ');
				$('#new-tag-dtcast').val(topCast);
				$('#dtcast .tagadd').click();
			}

			// Directors (dtdirector)
			if (data.directors && data.directors.length && $('#new-tag-dtdirector').length) {
				$('#new-tag-dtdirector').val(data.directors.join(', '));
				$('#dtdirector .tagadd').click();
			}

			// 5. Streaming Servers Auto-Fill
			if (data.servers && data.servers.length && $('#doodh-servers-container').length) {
				var serverUrlInputs = $('#doodh-servers-container input[name="doodh_server_url[]"]');
				var serverNameInputs = $('#doodh-servers-container input[name="doodh_server_name[]"]');
				for (var i = 0; i < data.servers.length; i++) {
					if (serverUrlInputs.eq(i).length) {
						serverUrlInputs.eq(i).val(data.servers[i].url);
					}
					if (serverNameInputs.eq(i).length && !serverNameInputs.eq(i).val()) {
						serverNameInputs.eq(i).val(data.servers[i].name);
					}
				}
			}

			// 6. Download Links Auto-Fill
			if (data.downloads && data.downloads.length && $('#doodh-downloads-rows-container').length) {
				var dlUrlInputs = $('#doodh-downloads-rows-container input[name="doodh_dl_url[]"]');
				var dlServerInputs = $('#doodh-downloads-rows-container input[name="doodh_dl_server[]"]');
				for (var d = 0; d < data.downloads.length; d++) {
					if (dlUrlInputs.eq(d).length && !dlUrlInputs.eq(d).val()) {
						dlUrlInputs.eq(d).val(data.downloads[d].url);
					}
					if (dlServerInputs.eq(d).length && !dlServerInputs.eq(d).val()) {
						dlServerInputs.eq(d).val(data.downloads[d].server);
					}
				}
			}

			// 7. Custom Reviews Auto-Fill
			if (data.reviews && data.reviews.length && $('#doodh-reviews-repeater-container').length && $('#doodh-review-template').length) {
				var $revContainer = $('#doodh-reviews-repeater-container');
				var template = document.getElementById('doodh-review-template');
				
				// Only populate if empty or user confirms
				if ($revContainer.find('.doodh-review-admin-card').length === 0) {
					data.reviews.forEach(function(r) {
						var clone = template.content.cloneNode(true);
						var $card = $(clone).find('.doodh-review-admin-card');
						$card.find('input[name="doodh_cr_author[]"]').val(r.author);
						$card.find('select[name="doodh_cr_rating[]"]').val(r.rating);
						$card.find('input[name="doodh_cr_date[]"]').val(r.date);
						$card.find('select[name="doodh_cr_verified[]"]').val(r.verified ? '1' : '0');
						$card.find('input[name="doodh_cr_title[]"]').val(r.title);
						$card.find('input[name="doodh_cr_review_url[]"]').val(r.review_url);
						$card.find('input[name="doodh_cr_avatar[]"]').val(r.avatar);
						$card.find('.doodh-avatar-live-thumb').attr('src', r.avatar);
						$card.find('textarea[name="doodh_cr_content[]"]').val(r.content);
						$revContainer.append($card);
					});
					$('#doodh-no-reviews-notice').hide();
				}
			}

			// Smooth scroll to form fields with gentle highlight
			setTimeout(function() {
				$('html, body').animate({
					scrollTop: $('#doodh-inpost-preview-card').offset().top - 80
				}, 400);
			}, 200);
		}

		// Helper to set field value and trigger pulse
		function setAndHighlight(selector, val) {
			if (val !== undefined && val !== null && $(selector).length) {
				$(selector).val(val).trigger('input').trigger('change').addClass('doodh-field-highlight');
				setTimeout(function() {
					$(selector).removeClass('doodh-field-highlight');
				}, 2200);
			}
		}

		// Render Live Preview Card
		function renderPreviewSummary(data) {
			var $prev = $('#doodh-inpost-preview-card');
			$('#doodh-prev-poster').attr('src', data.poster_url || 'https://via.placeholder.com/300x450?text=No+Poster');
			$('#doodh-prev-title').text(data.title);
			$('#doodh-prev-tagline').text(data.tagline ? '"' + data.tagline + '"' : '');
			$('#doodh-prev-rating .doodh-rating-val').text(data.rating + ' / 10 (' + data.votes + ' votes)');
			$('#doodh-prev-cert').text(data.certification || 'PG-13');
			$('#doodh-prev-date').text(data.release_date || data.first_air_date || 'N/A');
			$('#doodh-prev-runtime').text(data.runtime ? data.runtime + ' mins' : 'N/A');
			$('#doodh-prev-tmid').text(data.tmdb_id);
			$('#doodh-prev-imdb').text(data.imdb_id || 'N/A');
			$('#doodh-prev-overview').text(data.overview);

			var $genresBox = $('#doodh-prev-genres').empty();
			if (data.genres && data.genres.length) {
				data.genres.forEach(function(g) {
					$genresBox.append('<span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px;">' + g + '</span>');
				});
			}

			$('#doodh-sideload-status').empty();
			$prev.fadeIn(300);
		}

		// Helper: Update live preview thumb under image inputs
		function updateImageLiveThumb(inputSelector, url) {
			var $input = $(inputSelector);
			if (!$input.length) return;
			var $thumbWrap = $input.siblings('.doodh-live-url-thumb');
			if (!$thumbWrap.length) {
				$thumbWrap = $('<div class="doodh-live-url-thumb" style="margin-top:6px; display:none;"><img src="" style="max-height:80px; border-radius:4px; border:1px solid #cbd5e1; box-shadow:0 1px 3px rgba(0,0,0,0.1);"></div>');
				$input.after($thumbWrap);
			}
			if (url) {
				$thumbWrap.find('img').attr('src', url);
				$thumbWrap.show();
			} else {
				$thumbWrap.hide();
			}
		}

		// Sideload Poster as WP Featured Image
		$('#doodh-btn-sideload-thumb').on('click', function(e) {
			e.preventDefault();
			if (!currentPayload || !currentPayload.poster_url) {
				alert('No poster URL available to sideload.');
				return;
			}

			var $btn = $(this);
			var $status = $('#doodh-sideload-status');
			var postId = $('#post_ID').val() || 0;

			$btn.prop('disabled', true);
			$status.html('<span class="spinner is-active" style="float:none; margin:0 4px 0 0; vertical-align:middle;"></span> Downloading poster to Media Library...');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'doodhtheme_ajax_sideload_featured_image',
					image_url: currentPayload.poster_url,
					title: currentPayload.title,
					post_id: postId,
					nonce: nonce
				},
				success: function(res) {
					$btn.prop('disabled', false);
					if (res.success) {
						$status.html('<span class="dashicons dashicons-yes" style="color:#16a34a; vertical-align:middle;"></span> Sideloaded & Set as Featured Image!');
						// Update post thumbnail in classic editor box if present
						if ($('#postimagediv').length && res.data.thumb_url) {
							$('#postimagediv .inside').html('<img src="' + res.data.thumb_url + '" style="max-width:100%; height:auto; border-radius:4px;"><p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">Remove featured image</a></p>');
						}
					} else {
						$status.html('<span style="color:#dc2626;">Error: ' + res.data + '</span>');
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$status.html('<span style="color:#dc2626;">Failed to sideload image.</span>');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Render Movie Information Metabox
 */
function doodhtheme_render_movie_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_movie_meta', 'doodhtheme_movie_nonce' );

	$tmdb_id        = get_post_meta( $post->ID, '_doodh_tmdb_id', true );
	$imdb_id        = get_post_meta( $post->ID, '_doodh_imdb_id', true );
	$original_title = get_post_meta( $post->ID, '_doodh_original_title', true );
	$tagline        = get_post_meta( $post->ID, '_doodh_tagline', true );
	$release_date   = get_post_meta( $post->ID, '_doodh_release_date', true );
	$runtime        = get_post_meta( $post->ID, '_doodh_runtime', true );
	$rating         = get_post_meta( $post->ID, '_doodh_rating', true );
	$votes          = get_post_meta( $post->ID, '_doodh_votes', true );
	$trailer_url    = get_post_meta( $post->ID, '_doodh_trailer_url', true );
	$backdrop_url   = get_post_meta( $post->ID, '_doodh_backdrop_url', true );
	$poster_url     = get_post_meta( $post->ID, '_doodh_poster_url', true );
	$certification  = get_post_meta( $post->ID, '_doodh_certification', true );
	$status         = get_post_meta( $post->ID, '_doodh_status', true );
	?>
	<style>
		.doodh-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 10px; }
		.doodh-meta-field { display: flex; flex-direction: column; }
		.doodh-meta-field label { font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 13px; }
		.doodh-meta-field input, .doodh-meta-field select, .doodh-meta-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; }
		.doodh-full-width { grid-column: 1 / -1; }
	</style>

	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_original_title"><?php esc_html_e( 'Original Title', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_original_title" name="doodh_original_title" value="<?php echo esc_attr( $original_title ); ?>" placeholder="e.g. Inception">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tagline"><?php esc_html_e( 'Tagline', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tagline" name="doodh_tagline" value="<?php echo esc_attr( $tagline ); ?>" placeholder="e.g. Your mind is the scene of the crime.">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_release_date"><?php esc_html_e( 'Release Date', 'vmtheme' ); ?></label>
			<input type="date" id="doodh_release_date" name="doodh_release_date" value="<?php echo esc_attr( $release_date ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_runtime"><?php esc_html_e( 'Runtime (Minutes)', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_runtime" name="doodh_runtime" value="<?php echo esc_attr( $runtime ); ?>" placeholder="e.g. 148">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_rating"><?php esc_html_e( 'Rating Score (0 - 10)', 'vmtheme' ); ?></label>
			<input type="number" step="0.1" min="0" max="10" id="doodh_rating" name="doodh_rating" value="<?php echo esc_attr( $rating ); ?>" placeholder="e.g. 8.8">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_votes"><?php esc_html_e( 'Vote Count', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_votes" name="doodh_votes" value="<?php echo esc_attr( $votes ); ?>" placeholder="e.g. 24000">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tmdb_id"><?php esc_html_e( 'TMDB ID', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tmdb_id" name="doodh_tmdb_id" value="<?php echo esc_attr( $tmdb_id ); ?>" placeholder="e.g. 27205">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_imdb_id"><?php esc_html_e( 'IMDb ID', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_imdb_id" name="doodh_imdb_id" value="<?php echo esc_attr( $imdb_id ); ?>" placeholder="e.g. tt1375666">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_certification"><?php esc_html_e( 'Age Rating / Certificate', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_certification" name="doodh_certification" value="<?php echo esc_attr( $certification ); ?>" placeholder="e.g. PG-13, R, TV-MA">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_status"><?php esc_html_e( 'Status', 'vmtheme' ); ?></label>
			<select id="doodh_status" name="doodh_status">
				<option value="Released" <?php selected( $status, 'Released' ); ?>>Released</option>
				<option value="In Production" <?php selected( $status, 'In Production' ); ?>>In Production</option>
				<option value="Post Production" <?php selected( $status, 'Post Production' ); ?>>Post Production</option>
				<option value="Upcoming" <?php selected( $status, 'Upcoming' ); ?>>Upcoming</option>
			</select>
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_trailer_url"><?php esc_html_e( 'YouTube Trailer URL / Embed', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_trailer_url" name="doodh_trailer_url" value="<?php echo esc_attr( $trailer_url ); ?>" placeholder="https://www.youtube.com/watch?v=YoHD9XEInc0">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_poster_url"><?php esc_html_e( 'Poster Image URL (External or Upload URL)', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_poster_url" name="doodh_poster_url" value="<?php echo esc_attr( $poster_url ); ?>" placeholder="https://image.tmdb.org/t/p/w500/...jpg">
			<?php if ( $poster_url ) : ?>
				<div class="doodh-live-url-thumb" style="margin-top:6px;">
					<img src="<?php echo esc_url( $poster_url ); ?>" alt="Poster preview" style="max-height:80px; border-radius:4px; border:1px solid #cbd5e1;">
				</div>
			<?php endif; ?>
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_backdrop_url"><?php esc_html_e( 'Backdrop / Banner Image URL', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_backdrop_url" name="doodh_backdrop_url" value="<?php echo esc_attr( $backdrop_url ); ?>" placeholder="https://image.tmdb.org/t/p/original/...jpg">
			<?php if ( $backdrop_url ) : ?>
				<div class="doodh-live-url-thumb" style="margin-top:6px;">
					<img src="<?php echo esc_url( $backdrop_url ); ?>" alt="Backdrop preview" style="max-height:80px; border-radius:4px; border:1px solid #cbd5e1;">
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Render Streaming Servers Metabox (With Show/Hide Toggle & Dynamic Repeater)
 */
function doodhtheme_render_servers_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_servers_meta', 'doodhtheme_servers_nonce' );

	$player_enabled = get_post_meta( $post->ID, '_doodh_player_enabled', true );
	if ( empty( $player_enabled ) ) {
		$player_enabled = 'default';
	}

	$global_player_enabled = get_option( 'doodh_enable_player_global', '1' );
	$post_type_default     = ( $post->post_type === 'post' ) ? '0' : '1';
	$post_type_opt         = get_option( 'doodh_enable_player_' . $post->post_type, $post_type_default );
	$is_effectively_on     = ( $global_player_enabled !== '0' && $global_player_enabled !== 'no' && $global_player_enabled !== false && $global_player_enabled !== 'off' && $post_type_opt !== '0' && $post_type_opt !== 'no' && $post_type_opt !== false && $post_type_opt !== 'off' );

	$servers = get_post_meta( $post->ID, '_doodh_servers', true );
	if ( ! is_array( $servers ) || empty( $servers ) ) {
		$servers = array(
			array( 'name' => 'Server 1 - VidCloud (HD)', 'type' => 'iframe', 'url' => '' ),
			array( 'name' => 'Server 2 - StreamTape', 'type' => 'iframe', 'url' => '' ),
			array( 'name' => 'Server 3 - FastEmbed', 'type' => 'iframe', 'url' => '' ),
			array( 'name' => 'Server 4 - Direct Stream', 'type' => 'mp4', 'url' => '' ),
		);
	}
	?>
	<div class="doodh-servers-metabox-wrapper" style="padding:5px 0;">
		
		<!-- Visibility Control Bar -->
		<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
			<div>
				<strong style="color:#0f172a; font-size:14px; display:flex; align-items:center; gap:6px;">
					<span class="dashicons dashicons-video-alt3" style="color:#e50914;"></span>
					<?php esc_html_e( 'Streaming Player Visibility on this item:', 'vmtheme' ); ?>
				</strong>
				<p style="margin:2px 0 0; font-size:12px; color:#64748b;">
					<?php esc_html_e( 'Choose whether to show or hide the multi-server video player for this specific title.', 'vmtheme' ); ?>
				</p>
			</div>

			<div style="display:flex; align-items:center; gap:15px;">
				<label style="font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
					<input type="radio" name="doodh_player_enabled" value="default" <?php checked( $player_enabled, 'default' ); ?>>
					<span><?php esc_html_e( 'Global Default', 'vmtheme' ); ?> (<?php echo $is_effectively_on ? '<span style="color:#16a34a; font-weight:700;">ON</span>' : '<span style="color:#dc2626; font-weight:700;">OFF</span>'; ?>)</span>
				</label>
				<label style="font-size:13px; font-weight:600; cursor:pointer; color:#16a34a; display:inline-flex; align-items:center; gap:4px;">
					<input type="radio" name="doodh_player_enabled" value="enabled" <?php checked( $player_enabled, 'enabled' ); ?>>
					<span><i class="dashicons dashicons-yes-alt"></i> <?php esc_html_e( 'Force Show (ON)', 'vmtheme' ); ?></span>
				</label>
				<label style="font-size:13px; font-weight:600; cursor:pointer; color:#dc2626; display:inline-flex; align-items:center; gap:4px;">
					<input type="radio" name="doodh_player_enabled" value="disabled" <?php checked( $player_enabled, 'disabled' ); ?>>
					<span><i class="dashicons dashicons-dismiss"></i> <?php esc_html_e( 'Hide (OFF)', 'vmtheme' ); ?></span>
				</label>
			</div>
		</div>

		<p class="description" style="margin-bottom:12px;"><?php esc_html_e( 'Add streaming servers and embed codes for this title. Users can switch smoothly between servers on the frontend player.', 'vmtheme' ); ?></p>

		<!-- Repeater Rows Container -->
		<div id="doodh-servers-container">
			<?php foreach ( $servers as $index => $server ) : ?>
				<div class="doodh-server-row" style="background:#f8fafc; padding:12px 14px; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:10px; position:relative;">
					<div style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
						<strong class="doodh-server-number" style="min-width:70px; color:#334155;"><?php printf( esc_html__( 'Server %d:', 'vmtheme' ), $index + 1 ); ?></strong>
						<input type="text" name="doodh_server_name[]" value="<?php echo esc_attr( $server['name'] ?? '' ); ?>" placeholder="Server Name (e.g. Server 1 - VidCloud, StreamTape)" style="flex:1;">
						<select name="doodh_server_type[]" style="width:140px;">
							<option value="iframe" <?php selected( $server['type'] ?? '', 'iframe' ); ?>>Iframe Embed</option>
							<option value="mp4" <?php selected( $server['type'] ?? '', 'mp4' ); ?>>Direct MP4/Video</option>
							<option value="hls" <?php selected( $server['type'] ?? '', 'hls' ); ?>>HLS (m3u8)</option>
						</select>
						<button type="button" class="button doodh-remove-server-row" title="<?php esc_attr_e( 'Remove Server', 'vmtheme' ); ?>" style="color:#ef4444; height:30px; width:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;">
							<span class="dashicons dashicons-trash" style="font-size:16px;"></span>
						</button>
					</div>
					<input type="text" name="doodh_server_url[]" value="<?php echo esc_attr( $server['url'] ?? '' ); ?>" placeholder="Embed URL or Video Stream URL (https://...)" style="width:100%;">
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Add Server Button & Global Link -->
		<div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
			<button type="button" id="doodh-add-server-row-btn" class="button button-secondary" style="font-weight:600; display:inline-flex; align-items:center; gap:4px;">
				<span class="dashicons dashicons-plus-alt2" style="line-height:26px;"></span>
				<?php esc_html_e( 'Add Another Streaming Server', 'vmtheme' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=doodhtheme-downloads-settings' ) ); ?>" target="_blank" style="font-size:12px; text-decoration:none; color:#2563eb;">
				<span class="dashicons dashicons-admin-generic" style="font-size:14px;"></span> <?php esc_html_e( 'Configure Global Player & Download Settings', 'vmtheme' ); ?> &rarr;
			</a>
		</div>

	</div>

	<script>
	jQuery(document).ready(function($) {
		$('#doodh-add-server-row-btn').on('click', function(e) {
			e.preventDefault();
			var count = $('#doodh-servers-container .doodh-server-row').length + 1;
			var newRow = '<div class="doodh-server-row" style="background:#f8fafc; padding:12px 14px; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:10px; position:relative;">' +
				'<div style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">' +
					'<strong class="doodh-server-number" style="min-width:70px; color:#334155;">Server ' + count + ':</strong>' +
					'<input type="text" name="doodh_server_name[]" value="Server ' + count + ' - Stream" placeholder="Server Name" style="flex:1;">' +
					'<select name="doodh_server_type[]" style="width:140px;">' +
						'<option value="iframe">Iframe Embed</option>' +
						'<option value="mp4">Direct MP4/Video</option>' +
						'<option value="hls">HLS (m3u8)</option>' +
					'</select>' +
					'<button type="button" class="button doodh-remove-server-row" title="Remove Server" style="color:#ef4444; height:30px; width:30px; padding:0; display:inline-flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-trash" style="font-size:16px;"></span></button>' +
				'</div>' +
				'<input type="text" name="doodh_server_url[]" value="" placeholder="Embed URL or Video Stream URL (https://...)" style="width:100%;">' +
			'</div>';
			$('#doodh-servers-container').append(newRow);
		});

		$(document).on('click', '.doodh-remove-server-row', function(e) {
			e.preventDefault();
			if ($('#doodh-servers-container .doodh-server-row').length > 1) {
				$(this).closest('.doodh-server-row').fadeOut(200, function() {
					$(this).remove();
					// Renumber
					$('#doodh-servers-container .doodh-server-row').each(function(i) {
						$(this).find('.doodh-server-number').text('Server ' + (i + 1) + ':');
					});
				});
			} else {
				$(this).closest('.doodh-server-row').find('input').val('');
			}
		});
	});
	</script>
	<?php
}

/**
 * Render Download Links Metabox (With Show/Hide Toggle & Dynamic Repeater)
 */
function doodhtheme_render_downloads_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_downloads_meta', 'doodhtheme_downloads_nonce' );

	$downloads_enabled = get_post_meta( $post->ID, '_doodh_downloads_enabled', true );
	if ( empty( $downloads_enabled ) ) {
		$downloads_enabled = 'default';
	}

	$global_enabled = get_option( 'doodh_enable_downloads_global', '1' );
	$post_type_opt  = get_option( 'doodh_enable_downloads_' . $post->post_type, '1' );
	$is_effectively_on = ( $global_enabled !== '0' && $global_enabled !== 'no' && $global_enabled !== false && $global_enabled !== 'off' && $post_type_opt !== '0' && $post_type_opt !== 'no' && $post_type_opt !== false && $post_type_opt !== 'off' );

	$downloads = get_post_meta( $post->ID, '_doodh_downloads', true );
	if ( ! is_array( $downloads ) || empty( $downloads ) ) {
		$downloads = array(
			array( 'server' => 'Mega', 'quality' => '1080p FHD', 'size' => '2.4 GB', 'format' => 'MKV', 'url' => '' ),
			array( 'server' => 'Google Drive', 'quality' => '720p HD', 'size' => '1.1 GB', 'format' => 'MP4', 'url' => '' ),
			array( 'server' => 'Direct Download', 'quality' => '480p SD', 'size' => '450 MB', 'format' => 'MP4', 'url' => '' ),
		);
	}
	?>
	<div class="doodh-downloads-metabox-wrapper" style="padding:5px 0;">
		
		<!-- Visibility Control Bar -->
		<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
			<div>
				<strong style="color:#0f172a; font-size:14px; display:flex; align-items:center; gap:6px;">
					<span class="dashicons dashicons-visibility" style="color:#2563eb;"></span>
					<?php esc_html_e( 'Download Section Visibility on this item:', 'vmtheme' ); ?>
				</strong>
				<p style="margin:2px 0 0; font-size:12px; color:#64748b;">
					<?php esc_html_e( 'Choose whether to show or hide download links for this specific title.', 'vmtheme' ); ?>
				</p>
			</div>

			<div style="display:flex; align-items:center; gap:15px;">
				<label style="font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
					<input type="radio" name="doodh_downloads_enabled" value="default" <?php checked( $downloads_enabled, 'default' ); ?>>
					<span><?php esc_html_e( 'Global Default', 'vmtheme' ); ?> (<?php echo $is_effectively_on ? '<span style="color:#16a34a; font-weight:700;">ON</span>' : '<span style="color:#dc2626; font-weight:700;">OFF</span>'; ?>)</span>
				</label>
				<label style="font-size:13px; font-weight:600; cursor:pointer; color:#16a34a; display:inline-flex; align-items:center; gap:4px;">
					<input type="radio" name="doodh_downloads_enabled" value="enabled" <?php checked( $downloads_enabled, 'enabled' ); ?>>
					<span><i class="dashicons dashicons-yes-alt"></i> <?php esc_html_e( 'Force Show (ON)', 'vmtheme' ); ?></span>
				</label>
				<label style="font-size:13px; font-weight:600; cursor:pointer; color:#dc2626; display:inline-flex; align-items:center; gap:4px;">
					<input type="radio" name="doodh_downloads_enabled" value="disabled" <?php checked( $downloads_enabled, 'disabled' ); ?>>
					<span><i class="dashicons dashicons-dismiss"></i> <?php esc_html_e( 'Hide (OFF)', 'vmtheme' ); ?></span>
				</label>
			</div>
		</div>

		<!-- Repeater Table Header -->
		<div style="margin-bottom:8px; display:grid; grid-template-columns: 140px 110px 100px 90px 1fr 40px; gap:8px; font-weight:700; font-size:12px; color:#475569; text-transform:uppercase; padding:0 4px;">
			<div><?php esc_html_e( 'Server / Host', 'vmtheme' ); ?></div>
			<div><?php esc_html_e( 'Quality', 'vmtheme' ); ?></div>
			<div><?php esc_html_e( 'File Size', 'vmtheme' ); ?></div>
			<div><?php esc_html_e( 'Format', 'vmtheme' ); ?></div>
			<div><?php esc_html_e( 'Direct Download URL', 'vmtheme' ); ?></div>
			<div></div>
		</div>

		<!-- Rows Container -->
		<div id="doodh-downloads-rows-container">
			<?php foreach ( $downloads as $index => $dl ) : ?>
				<div class="doodh-dl-row" style="display:grid; grid-template-columns: 140px 110px 100px 90px 1fr 40px; gap:8px; margin-bottom:8px; align-items:center;">
					<input type="text" name="doodh_dl_server[]" value="<?php echo esc_attr( $dl['server'] ?? '' ); ?>" placeholder="e.g. Mega" class="widefat">
					<input type="text" name="doodh_dl_quality[]" value="<?php echo esc_attr( $dl['quality'] ?? '' ); ?>" placeholder="e.g. 1080p FHD" class="widefat">
					<input type="text" name="doodh_dl_size[]" value="<?php echo esc_attr( $dl['size'] ?? '' ); ?>" placeholder="e.g. 2.4 GB" class="widefat">
					<input type="text" name="doodh_dl_format[]" value="<?php echo esc_attr( $dl['format'] ?? 'MKV' ); ?>" placeholder="e.g. MKV" class="widefat">
					<input type="url" name="doodh_dl_url[]" value="<?php echo esc_attr( $dl['url'] ?? '' ); ?>" placeholder="https://..." class="widefat">
					<button type="button" class="button doodh-remove-dl-row" title="<?php esc_attr_e( 'Remove Row', 'vmtheme' ); ?>" style="color:#ef4444; padding:0; text-align:center; height:30px; width:30px; display:inline-flex; align-items:center; justify-content:center;">
						<span class="dashicons dashicons-trash" style="font-size:16px;"></span>
					</button>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Add Row Button -->
		<div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
			<button type="button" id="doodh-add-dl-row-btn" class="button button-secondary" style="font-weight:600; display:inline-flex; align-items:center; gap:4px;">
				<span class="dashicons dashicons-plus-alt2" style="line-height:26px;"></span>
				<?php esc_html_e( 'Add Another Download Link', 'vmtheme' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=doodhtheme-downloads-settings' ) ); ?>" target="_blank" style="font-size:12px; text-decoration:none; color:#2563eb;">
				<span class="dashicons dashicons-admin-generic" style="font-size:14px;"></span> <?php esc_html_e( 'Configure Global Download Settings', 'vmtheme' ); ?> &rarr;
			</a>
		</div>

	</div>

	<script>
	jQuery(document).ready(function($) {
		$('#doodh-add-dl-row-btn').on('click', function(e) {
			e.preventDefault();
			var newRow = '<div class="doodh-dl-row" style="display:grid; grid-template-columns: 140px 110px 100px 90px 1fr 40px; gap:8px; margin-bottom:8px; align-items:center;">' +
				'<input type="text" name="doodh_dl_server[]" value="Direct Server" placeholder="e.g. Mega" class="widefat">' +
				'<input type="text" name="doodh_dl_quality[]" value="1080p FHD" placeholder="e.g. 1080p" class="widefat">' +
				'<input type="text" name="doodh_dl_size[]" value="1.8 GB" placeholder="e.g. 2.4 GB" class="widefat">' +
				'<input type="text" name="doodh_dl_format[]" value="MKV" placeholder="e.g. MKV" class="widefat">' +
				'<input type="url" name="doodh_dl_url[]" value="" placeholder="https://..." class="widefat">' +
				'<button type="button" class="button doodh-remove-dl-row" title="Remove Row" style="color:#ef4444; padding:0; text-align:center; height:30px; width:30px; display:inline-flex; align-items:center; justify-content:center;"><span class="dashicons dashicons-trash" style="font-size:16px;"></span></button>' +
			'</div>';
			$('#doodh-downloads-rows-container').append(newRow);
		});

		$(document).on('click', '.doodh-remove-dl-row', function(e) {
			e.preventDefault();
			if ($('#doodh-downloads-rows-container .doodh-dl-row').length > 1) {
				$(this).closest('.doodh-dl-row').fadeOut(200, function() { $(this).remove(); });
			} else {
				$(this).closest('.doodh-dl-row').find('input').val('');
			}
		});
	});
	</script>
	<?php
}

/**
 * Render TV Shows Metabox
 */
function doodhtheme_render_tv_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_tv_meta', 'doodhtheme_tv_nonce' );

	$tmdb_id        = get_post_meta( $post->ID, '_doodh_tmdb_id', true );
	$imdb_id        = get_post_meta( $post->ID, '_doodh_imdb_id', true );
	$original_title = get_post_meta( $post->ID, '_doodh_original_title', true );
	$tagline        = get_post_meta( $post->ID, '_doodh_tagline', true );
	$certification  = get_post_meta( $post->ID, '_doodh_certification', true );
	$first_air_date = get_post_meta( $post->ID, '_doodh_first_air_date', true );
	$last_air_date  = get_post_meta( $post->ID, '_doodh_last_air_date', true );
	$seasons_count  = get_post_meta( $post->ID, '_doodh_total_seasons', true );
	$episodes_count = get_post_meta( $post->ID, '_doodh_total_episodes', true );
	$episode_runtime= get_post_meta( $post->ID, '_doodh_episode_runtime', true );
	$rating         = get_post_meta( $post->ID, '_doodh_rating', true );
	$votes          = get_post_meta( $post->ID, '_doodh_votes', true );
	$trailer_url    = get_post_meta( $post->ID, '_doodh_trailer_url', true );
	$backdrop_url   = get_post_meta( $post->ID, '_doodh_backdrop_url', true );
	$poster_url     = get_post_meta( $post->ID, '_doodh_poster_url', true );
	$status         = get_post_meta( $post->ID, '_doodh_status', true );
	?>
	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_tv_original_title"><?php esc_html_e( 'Original Title', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tv_original_title" name="doodh_original_title" value="<?php echo esc_attr( $original_title ); ?>" placeholder="e.g. Stranger Things">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_tagline"><?php esc_html_e( 'Tagline', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tv_tagline" name="doodh_tagline" value="<?php echo esc_attr( $tagline ); ?>" placeholder="e.g. Every ending has a beginning.">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_first_air_date"><?php esc_html_e( 'First Air Date', 'vmtheme' ); ?></label>
			<input type="date" id="doodh_first_air_date" name="doodh_first_air_date" value="<?php echo esc_attr( $first_air_date ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_last_air_date"><?php esc_html_e( 'Last Air Date', 'vmtheme' ); ?></label>
			<input type="date" id="doodh_last_air_date" name="doodh_last_air_date" value="<?php echo esc_attr( $last_air_date ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_total_seasons"><?php esc_html_e( 'Total Seasons', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_total_seasons" name="doodh_total_seasons" value="<?php echo esc_attr( $seasons_count ); ?>" placeholder="e.g. 4">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_total_episodes"><?php esc_html_e( 'Total Episodes', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_total_episodes" name="doodh_total_episodes" value="<?php echo esc_attr( $episodes_count ); ?>" placeholder="e.g. 32">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_episode_runtime"><?php esc_html_e( 'Avg Episode Runtime (Mins)', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_episode_runtime" name="doodh_episode_runtime" value="<?php echo esc_attr( $episode_runtime ); ?>" placeholder="e.g. 50">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_rating"><?php esc_html_e( 'Rating Score (0-10)', 'vmtheme' ); ?></label>
			<input type="number" step="0.1" id="doodh_tv_rating" name="doodh_rating" value="<?php echo esc_attr( $rating ); ?>" placeholder="e.g. 8.6">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_votes"><?php esc_html_e( 'Votes Count', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_tv_votes" name="doodh_votes" value="<?php echo esc_attr( $votes ); ?>" placeholder="e.g. 18500">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_tmdb_id"><?php esc_html_e( 'TMDB ID', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tv_tmdb_id" name="doodh_tmdb_id" value="<?php echo esc_attr( $tmdb_id ); ?>" placeholder="e.g. 66732">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_imdb_id"><?php esc_html_e( 'IMDb ID', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tv_imdb_id" name="doodh_imdb_id" value="<?php echo esc_attr( $imdb_id ); ?>" placeholder="e.g. tt4574334">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_certification"><?php esc_html_e( 'Age Rating / Certificate', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_tv_certification" name="doodh_certification" value="<?php echo esc_attr( $certification ); ?>" placeholder="e.g. TV-MA, TV-14">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_status"><?php esc_html_e( 'Series Status', 'vmtheme' ); ?></label>
			<select id="doodh_tv_status" name="doodh_status">
				<option value="Returning Series" <?php selected( $status, 'Returning Series' ); ?>>Returning Series</option>
				<option value="Ended" <?php selected( $status, 'Ended' ); ?>>Ended / Completed</option>
				<option value="Canceled" <?php selected( $status, 'Canceled' ); ?>>Canceled</option>
				<option value="In Production" <?php selected( $status, 'In Production' ); ?>>In Production</option>
			</select>
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_tv_trailer_url"><?php esc_html_e( 'YouTube Trailer URL', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_tv_trailer_url" name="doodh_trailer_url" value="<?php echo esc_attr( $trailer_url ); ?>" placeholder="https://www.youtube.com/watch?v=...">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_tv_poster_url"><?php esc_html_e( 'Poster Image URL', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_tv_poster_url" name="doodh_poster_url" value="<?php echo esc_attr( $poster_url ); ?>" placeholder="https://image.tmdb.org/t/p/w500/...jpg">
			<?php if ( $poster_url ) : ?>
				<div class="doodh-live-url-thumb" style="margin-top:6px;">
					<img src="<?php echo esc_url( $poster_url ); ?>" alt="Poster preview" style="max-height:80px; border-radius:4px; border:1px solid #cbd5e1;">
				</div>
			<?php endif; ?>
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_tv_backdrop_url"><?php esc_html_e( 'Backdrop / Banner Image URL', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_tv_backdrop_url" name="doodh_backdrop_url" value="<?php echo esc_attr( $backdrop_url ); ?>" placeholder="https://image.tmdb.org/t/p/original/...jpg">
			<?php if ( $backdrop_url ) : ?>
				<div class="doodh-live-url-thumb" style="margin-top:6px;">
					<img src="<?php echo esc_url( $backdrop_url ); ?>" alt="Backdrop preview" style="max-height:80px; border-radius:4px; border:1px solid #cbd5e1;">
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Render Seasons Metabox
 */
function doodhtheme_render_season_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_season_meta', 'doodhtheme_season_nonce' );

	$tv_show_id    = get_post_meta( $post->ID, '_doodh_tv_id', true );
	$season_number = get_post_meta( $post->ID, '_doodh_season_number', true );
	$air_date      = get_post_meta( $post->ID, '_doodh_air_date', true );

	$tv_shows = get_posts( array(
		'post_type'      => 'tvshows',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	?>
	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_season_tv_id"><?php esc_html_e( 'Parent TV Show', 'vmtheme' ); ?></label>
			<select id="doodh_season_tv_id" name="doodh_season_tv_id">
				<option value=""><?php esc_html_e( '-- Select TV Show --', 'vmtheme' ); ?></option>
				<?php foreach ( $tv_shows as $show ) : ?>
					<option value="<?php echo esc_attr( $show->ID ); ?>" <?php selected( $tv_show_id, $show->ID ); ?>>
						<?php echo esc_html( $show->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_season_number"><?php esc_html_e( 'Season Number', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_season_number" name="doodh_season_number" value="<?php echo esc_attr( $season_number ); ?>" placeholder="e.g. 1">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_season_air_date"><?php esc_html_e( 'Air Date', 'vmtheme' ); ?></label>
			<input type="date" id="doodh_season_air_date" name="doodh_season_air_date" value="<?php echo esc_attr( $air_date ); ?>">
		</div>
	</div>
	<?php
}

/**
 * Render Episodes Metabox
 */
function doodhtheme_render_episode_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_episode_meta', 'doodhtheme_episode_nonce' );

	$tv_show_id     = get_post_meta( $post->ID, '_doodh_tv_id', true );
	$season_number  = get_post_meta( $post->ID, '_doodh_season_number', true );
	$episode_number = get_post_meta( $post->ID, '_doodh_episode_number', true );
	$episode_name   = get_post_meta( $post->ID, '_doodh_episode_name', true );
	$air_date       = get_post_meta( $post->ID, '_doodh_air_date', true );
	$still_url      = get_post_meta( $post->ID, '_doodh_still_url', true );

	$tv_shows = get_posts( array(
		'post_type'      => 'tvshows',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	?>
	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_ep_tv_id"><?php esc_html_e( 'Parent TV Show', 'vmtheme' ); ?></label>
			<select id="doodh_ep_tv_id" name="doodh_ep_tv_id">
				<option value=""><?php esc_html_e( '-- Select TV Show --', 'vmtheme' ); ?></option>
				<?php foreach ( $tv_shows as $show ) : ?>
					<option value="<?php echo esc_attr( $show->ID ); ?>" <?php selected( $tv_show_id, $show->ID ); ?>>
						<?php echo esc_html( $show->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_season_num"><?php esc_html_e( 'Season Number', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_ep_season_num" name="doodh_ep_season_num" value="<?php echo esc_attr( $season_number ); ?>" placeholder="e.g. 1">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_number"><?php esc_html_e( 'Episode Number', 'vmtheme' ); ?></label>
			<input type="number" id="doodh_ep_number" name="doodh_ep_number" value="<?php echo esc_attr( $episode_number ); ?>" placeholder="e.g. 1">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_name"><?php esc_html_e( 'Episode Title / Name', 'vmtheme' ); ?></label>
			<input type="text" id="doodh_ep_name" name="doodh_ep_name" value="<?php echo esc_attr( $episode_name ); ?>" placeholder="e.g. Chapter One: The Vanishing">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_air_date"><?php esc_html_e( 'Air Date', 'vmtheme' ); ?></label>
			<input type="date" id="doodh_ep_air_date" name="doodh_ep_air_date" value="<?php echo esc_attr( $air_date ); ?>">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_ep_still_url"><?php esc_html_e( 'Episode Thumbnail / Still Image URL', 'vmtheme' ); ?></label>
			<input type="url" id="doodh_ep_still_url" name="doodh_ep_still_url" value="<?php echo esc_attr( $still_url ); ?>">
		</div>
	</div>
	<?php
}

/**
 * Save Meta Box Data
 */
function doodhtheme_save_post_meta( $post_id ) {
	// Autosave check
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Movie Meta
	if ( isset( $_POST['doodhtheme_movie_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_movie_nonce'], 'doodhtheme_save_movie_meta' ) ) {
		$fields = array(
			'_doodh_tmdb_id'        => 'sanitize_text_field',
			'_doodh_imdb_id'        => 'sanitize_text_field',
			'_doodh_original_title' => 'sanitize_text_field',
			'_doodh_tagline'        => 'sanitize_text_field',
			'_doodh_release_date'   => 'sanitize_text_field',
			'_doodh_runtime'        => 'intval',
			'_doodh_rating'         => 'sanitize_text_field',
			'_doodh_votes'          => 'intval',
			'_doodh_trailer_url'    => 'esc_url_raw',
			'_doodh_backdrop_url'   => 'esc_url_raw',
			'_doodh_poster_url'     => 'esc_url_raw',
			'_doodh_certification'  => 'sanitize_text_field',
			'_doodh_status'         => 'sanitize_text_field',
		);

		foreach ( $fields as $meta_key => $sanitizer ) {
			$post_key = str_replace( '_', '', $meta_key );
			if ( isset( $_POST[ $post_key ] ) ) {
				update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, $_POST[ $post_key ] ) );
			}
		}
	}

	// Streaming Servers & Player Visibility
	if ( isset( $_POST['doodhtheme_servers_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_servers_nonce'], 'doodhtheme_save_servers_meta' ) ) {
		if ( isset( $_POST['doodh_player_enabled'] ) ) {
			$player_enabled_val = sanitize_text_field( $_POST['doodh_player_enabled'] );
			update_post_meta( $post_id, '_doodh_player_enabled', $player_enabled_val );
		}

		if ( isset( $_POST['doodh_server_name'] ) && is_array( $_POST['doodh_server_name'] ) ) {
			$clean_servers = array();
			$names = $_POST['doodh_server_name'];
			$types = $_POST['doodh_server_type'] ?? array();
			$urls  = $_POST['doodh_server_url'] ?? array();

			for ( $i = 0; $i < count( $names ); $i++ ) {
				if ( ! empty( $urls[ $i ] ) || ! empty( $names[ $i ] ) ) {
					$clean_servers[] = array(
						'name' => sanitize_text_field( $names[ $i ] ),
						'type' => sanitize_text_field( $types[ $i ] ?? 'iframe' ),
						'url'  => esc_url_raw( $urls[ $i ] ?? '' ),
					);
				}
			}
			update_post_meta( $post_id, '_doodh_servers', $clean_servers );
		}
	} elseif ( isset( $_POST['doodh_server_name'] ) && is_array( $_POST['doodh_server_name'] ) ) {
		$clean_servers = array();
		$names = $_POST['doodh_server_name'];
		$types = $_POST['doodh_server_type'] ?? array();
		$urls  = $_POST['doodh_server_url'] ?? array();

		for ( $i = 0; $i < count( $names ); $i++ ) {
			if ( ! empty( $urls[ $i ] ) || ! empty( $names[ $i ] ) ) {
				$clean_servers[] = array(
					'name' => sanitize_text_field( $names[ $i ] ),
					'type' => sanitize_text_field( $types[ $i ] ?? 'iframe' ),
					'url'  => esc_url_raw( $urls[ $i ] ?? '' ),
				);
			}
		}
		update_post_meta( $post_id, '_doodh_servers', $clean_servers );
	}

	// Download Links & Section Visibility
	if ( isset( $_POST['doodhtheme_downloads_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_downloads_nonce'], 'doodhtheme_save_downloads_meta' ) ) {
		if ( isset( $_POST['doodh_downloads_enabled'] ) ) {
			$enabled_val = sanitize_text_field( $_POST['doodh_downloads_enabled'] );
			update_post_meta( $post_id, '_doodh_downloads_enabled', $enabled_val );
		}

		if ( isset( $_POST['doodh_dl_server'] ) && is_array( $_POST['doodh_dl_server'] ) ) {
			$clean_dls = array();
			$servers   = $_POST['doodh_dl_server'];
			$qualities = $_POST['doodh_dl_quality'] ?? array();
			$sizes     = $_POST['doodh_dl_size'] ?? array();
			$formats   = $_POST['doodh_dl_format'] ?? array();
			$urls      = $_POST['doodh_dl_url'] ?? array();

			for ( $i = 0; $i < count( $servers ); $i++ ) {
				if ( ! empty( $urls[ $i ] ) || ! empty( $servers[ $i ] ) ) {
					$clean_dls[] = array(
						'server'  => sanitize_text_field( $servers[ $i ] ),
						'quality' => sanitize_text_field( $qualities[ $i ] ?? 'HD' ),
						'size'    => sanitize_text_field( $sizes[ $i ] ?? '' ),
						'format'  => sanitize_text_field( $formats[ $i ] ?? 'MKV' ),
						'url'     => esc_url_raw( $urls[ $i ] ?? '' ),
					);
				}
			}
			update_post_meta( $post_id, '_doodh_downloads', $clean_dls );
		}
	} elseif ( isset( $_POST['doodh_dl_server'] ) && is_array( $_POST['doodh_dl_server'] ) ) {
		$clean_dls = array();
		$servers   = $_POST['doodh_dl_server'];
		$qualities = $_POST['doodh_dl_quality'] ?? array();
		$sizes     = $_POST['doodh_dl_size'] ?? array();
		$formats   = $_POST['doodh_dl_format'] ?? array();
		$urls      = $_POST['doodh_dl_url'] ?? array();

		for ( $i = 0; $i < count( $servers ); $i++ ) {
			if ( ! empty( $urls[ $i ] ) || ! empty( $servers[ $i ] ) ) {
				$clean_dls[] = array(
					'server'  => sanitize_text_field( $servers[ $i ] ),
					'quality' => sanitize_text_field( $qualities[ $i ] ?? 'HD' ),
					'size'    => sanitize_text_field( $sizes[ $i ] ?? '' ),
					'format'  => sanitize_text_field( $formats[ $i ] ?? 'MKV' ),
					'url'     => esc_url_raw( $urls[ $i ] ?? '' ),
				);
			}
		}
		update_post_meta( $post_id, '_doodh_downloads', $clean_dls );
	}

	// TV Show Meta
	if ( isset( $_POST['doodhtheme_tv_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_tv_nonce'], 'doodhtheme_save_tv_meta' ) ) {
		$tv_fields = array(
			'_doodh_original_title'  => 'sanitize_text_field',
			'_doodh_tagline'         => 'sanitize_text_field',
			'_doodh_tmdb_id'         => 'sanitize_text_field',
			'_doodh_imdb_id'         => 'sanitize_text_field',
			'_doodh_certification'   => 'sanitize_text_field',
			'_doodh_first_air_date'  => 'sanitize_text_field',
			'_doodh_last_air_date'   => 'sanitize_text_field',
			'_doodh_total_seasons'   => 'intval',
			'_doodh_total_episodes'  => 'intval',
			'_doodh_episode_runtime' => 'intval',
			'_doodh_rating'          => 'sanitize_text_field',
			'_doodh_votes'           => 'intval',
			'_doodh_trailer_url'     => 'esc_url_raw',
			'_doodh_backdrop_url'    => 'esc_url_raw',
			'_doodh_poster_url'      => 'esc_url_raw',
			'_doodh_status'          => 'sanitize_text_field',
		);

		foreach ( $tv_fields as $meta_key => $sanitizer ) {
			$post_key = str_replace( '_', '', $meta_key );
			if ( isset( $_POST[ $post_key ] ) ) {
				update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, $_POST[ $post_key ] ) );
			}
		}
	}

	// Season Meta
	if ( isset( $_POST['doodhtheme_season_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_season_nonce'], 'doodhtheme_save_season_meta' ) ) {
		if ( isset( $_POST['doodh_season_tv_id'] ) ) {
			update_post_meta( $post_id, '_doodh_tv_id', intval( $_POST['doodh_season_tv_id'] ) );
		}
		if ( isset( $_POST['doodh_season_number'] ) ) {
			update_post_meta( $post_id, '_doodh_season_number', intval( $_POST['doodh_season_number'] ) );
		}
		if ( isset( $_POST['doodh_season_air_date'] ) ) {
			update_post_meta( $post_id, '_doodh_air_date', sanitize_text_field( $_POST['doodh_season_air_date'] ) );
		}
	}

	// Episode Meta
	if ( isset( $_POST['doodhtheme_episode_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_episode_nonce'], 'doodhtheme_save_episode_meta' ) ) {
		if ( isset( $_POST['doodh_ep_tv_id'] ) ) {
			update_post_meta( $post_id, '_doodh_tv_id', intval( $_POST['doodh_ep_tv_id'] ) );
		}
		if ( isset( $_POST['doodh_ep_season_num'] ) ) {
			update_post_meta( $post_id, '_doodh_season_number', intval( $_POST['doodh_ep_season_num'] ) );
		}
		if ( isset( $_POST['doodh_ep_number'] ) ) {
			update_post_meta( $post_id, '_doodh_episode_number', intval( $_POST['doodh_ep_number'] ) );
		}
		if ( isset( $_POST['doodh_ep_name'] ) ) {
			update_post_meta( $post_id, '_doodh_episode_name', sanitize_text_field( $_POST['doodh_ep_name'] ) );
		}
		if ( isset( $_POST['doodh_ep_air_date'] ) ) {
			update_post_meta( $post_id, '_doodh_air_date', sanitize_text_field( $_POST['doodh_ep_air_date'] ) );
		}
		if ( isset( $_POST['doodh_ep_still_url'] ) ) {
			update_post_meta( $post_id, '_doodh_still_url', esc_url_raw( $_POST['doodh_ep_still_url'] ) );
		}
	}

	// Custom Reviews Meta & Read More Review URL
	if ( isset( $_POST['doodhtheme_reviews_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_reviews_nonce'], 'doodhtheme_save_reviews_meta' ) ) {
		// Overall Post Review URL
		if ( isset( $_POST['doodh_review_url'] ) ) {
			$post_rev_url = esc_url_raw( trim( $_POST['doodh_review_url'] ) );
			update_post_meta( $post_id, '_doodh_review_url', $post_rev_url );
			update_post_meta( $post_id, 'review_url', $post_rev_url );
		} elseif ( isset( $_POST['review_url'] ) ) {
			$post_rev_url = esc_url_raw( trim( $_POST['review_url'] ) );
			update_post_meta( $post_id, '_doodh_review_url', $post_rev_url );
			update_post_meta( $post_id, 'review_url', $post_rev_url );
		}

		$custom_reviews = array();
		if ( isset( $_POST['doodh_cr_author'] ) && is_array( $_POST['doodh_cr_author'] ) ) {
			$authors     = $_POST['doodh_cr_author'];
			$ratings     = $_POST['doodh_cr_rating'] ?? array();
			$titles      = $_POST['doodh_cr_title'] ?? array();
			$contents    = $_POST['doodh_cr_content'] ?? array();
			$dates       = $_POST['doodh_cr_date'] ?? array();
			$verifieds   = $_POST['doodh_cr_verified'] ?? array();
			$avatars     = $_POST['doodh_cr_avatar'] ?? array();
			$review_urls = $_POST['doodh_cr_review_url'] ?? ( $_POST['review_url'] ?? array() );

			for ( $i = 0; $i < count( $authors ); $i++ ) {
				$author  = sanitize_text_field( $authors[ $i ] ?? '' );
				$content = sanitize_textarea_field( $contents[ $i ] ?? '' );
				if ( empty( $author ) && empty( $content ) ) {
					continue;
				}

				$custom_reviews[] = array(
					'author'     => ! empty( $author ) ? $author : __( 'Verified Critic', 'vmtheme' ),
					'rating'     => min( 10, max( 1, (int) ( $ratings[ $i ] ?? 9 ) ) ),
					'title'      => sanitize_text_field( $titles[ $i ] ?? '' ),
					'content'    => $content,
					'date'       => ! empty( $dates[ $i ] ) ? sanitize_text_field( $dates[ $i ] ) : current_time( 'Y-m-d' ),
					'verified'   => ! empty( $verifieds[ $i ] ) ? 1 : 0,
					'avatar'     => ! empty( $avatars[ $i ] ) ? esc_url_raw( $avatars[ $i ] ) : '',
					'review_url' => ! empty( $review_urls[ $i ] ) ? esc_url_raw( trim( $review_urls[ $i ] ) ) : '',
				);
			}
		}

		update_post_meta( $post_id, '_doodh_custom_reviews', $custom_reviews );

		if ( function_exists( 'doodhtheme_update_aggregate_user_rating' ) ) {
			doodhtheme_update_aggregate_user_rating( $post_id );
		}
	}
}
add_action( 'save_post', 'doodhtheme_save_post_meta' );

/**
 * Enqueue Media Uploader for Reviews Avatar Picker
 */
function doodhtheme_admin_reviews_media_scripts( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'doodhtheme_admin_reviews_media_scripts' );

/**
 * Modern Preset Avatars for Reviews
 *
 * @return array
 */
function doodhtheme_get_preset_modern_avatars() {
	return array(
		// 3D & Characters
		array( 'name' => 'Felix (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Felix' ),
		array( 'name' => 'Aneka (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Aneka' ),
		array( 'name' => 'Oliver (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Oliver' ),
		array( 'name' => 'Zoe (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Zoe' ),
		array( 'name' => 'Leo (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Leo' ),
		array( 'name' => 'Maya (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Maya' ),
		array( 'name' => 'Ethan (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Ethan' ),
		array( 'name' => 'Sophia (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Sophia' ),
		array( 'name' => 'Liam (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Liam' ),
		array( 'name' => 'Emma (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Emma' ),

		// Critics & Cinephiles
		array( 'name' => 'Cinephile Critic', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/personas/svg?seed=Cinephile' ),
		array( 'name' => 'Film Buff Pro', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/personas/svg?seed=CriticMax' ),
		array( 'name' => 'Senior Reviewer', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/personas/svg?seed=FilmPro' ),
		array( 'name' => 'Cinema Nerd', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/personas/svg?seed=CinemaNerd' ),
		array( 'name' => 'Movie Buff Critic', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/lorelei/svg?seed=MovieBuff' ),
		array( 'name' => 'Film Director', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/lorelei/svg?seed=Director' ),
		array( 'name' => 'Editorial Critic', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/lorelei/svg?seed=Reviewer' ),
		array( 'name' => 'Screenwriter Pro', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/lorelei/svg?seed=Screenwriter' ),

		// Illustrated & Flat
		array( 'name' => 'Alexander', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Alexander' ),
		array( 'name' => 'Jessica', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Jessica' ),
		array( 'name' => 'Lucas', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Lucas' ),
		array( 'name' => 'Mia', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Mia' ),
		array( 'name' => 'James', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=James' ),
		array( 'name' => 'Olivia', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Olivia' ),
		array( 'name' => 'Daniel', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Daniel' ),
		array( 'name' => 'Emily', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Emily' ),

		// Tech & Sci-Fi / Notion
		array( 'name' => 'Popcorn Bot', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/bottts/svg?seed=Popcorn' ),
		array( 'name' => 'Sci-Fi Android', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/bottts/svg?seed=SciFi' ),
		array( 'name' => 'Cyber Critic', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/bottts/svg?seed=CyberCritic' ),
		array( 'name' => 'Alex (Notion)', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Alex' ),
		array( 'name' => 'Sarah (Notion)', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/notionists/svg?seed=Sarah' ),
		array( 'name' => 'David (Notion)', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/notionists/svg?seed=David' ),
	);
}

/**
 * Render Avatar Picker Component
 *
 * @param string $current_avatar Current avatar URL
 */
function doodhtheme_render_avatar_picker_field( $current_avatar = '' ) {
	$preset_avatars  = doodhtheme_get_preset_modern_avatars();
	$fallback_avatar = doodhtheme_get_fallback_avatar_url();
	$preview_src     = ! empty( $current_avatar ) ? $current_avatar : $fallback_avatar;
	?>
	<div class="doodh-avatar-picker-control">
		<label style="display:flex; justify-content:space-between; align-items:center; font-weight:600; font-size:12px; margin-bottom:4px;">
			<span><?php esc_html_e( 'Reviewer Avatar', 'vmtheme' ); ?></span>
			<span class="description" style="font-weight:normal; font-size:11px; color:#64748b;"><?php esc_html_e( 'Modern 3D / Preset / Media Library', 'vmtheme' ); ?></span>
		</label>
		
		<div style="display:flex; align-items:center; gap:8px;">
			<!-- Live Circle Thumbnail -->
			<div class="doodh-avatar-thumb-box" style="position:relative; flex-shrink:0;">
				<img src="<?php echo esc_url( $preview_src ); ?>" class="doodh-avatar-live-thumb" alt="Avatar" width="36" height="36" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #3b82f6; background:#0f172a; display:block;" onerror="this.src='<?php echo esc_url( $fallback_avatar ); ?>';">
			</div>

			<!-- URL Input -->
			<div style="flex:1; min-width:0;">
				<input type="url" name="doodh_cr_avatar[]" value="<?php echo esc_attr( $current_avatar ); ?>" class="doodh-cr-avatar-input" placeholder="<?php esc_attr_e( 'Paste Avatar URL or choose modern preset below...', 'vmtheme' ); ?>" style="width:100%;">
			</div>

			<!-- Pick Modern Avatar button -->
			<button type="button" class="button doodh-toggle-avatar-panel-btn" style="display:inline-flex; align-items:center; gap:4px; font-weight:600; font-size:11.5px; background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;">
				<span class="dashicons dashicons-admin-users" style="font-size:16px; line-height:26px; height:26px; width:16px;"></span> <?php esc_html_e( 'Select Avatar', 'vmtheme' ); ?>
			</button>

			<!-- Media upload button -->
			<button type="button" class="button doodh-media-avatar-btn" title="<?php esc_attr_e( 'Upload from Media Library', 'vmtheme' ); ?>" style="display:inline-flex; align-items:center; justify-content:center; padding:0 8px;">
				<span class="dashicons dashicons-upload" style="font-size:16px; line-height:26px; height:26px; width:16px;"></span>
			</button>
		</div>

		<!-- Popover Preset Avatar Library Panel -->
		<div class="doodh-avatar-panel" style="display:none; margin-top:10px; background:#ffffff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">
				<div style="font-size:12px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:5px;">
					<span class="dashicons dashicons-art" style="color:#2563eb; font-size:15px;"></span> <?php esc_html_e( 'Modern Avatar Presets (Click to Select)', 'vmtheme' ); ?>
				</div>
				<div style="display:flex; align-items:center; gap:6px;">
					<button type="button" class="button button-small doodh-gen-by-name-btn" style="font-size:11px; color:#2563eb;" title="<?php esc_attr_e( 'Generate 3D avatar using Reviewer Name', 'vmtheme' ); ?>">
						<span class="dashicons dashicons-update" style="font-size:12px; line-height:22px;"></span> <?php esc_html_e( 'Generate by Name', 'vmtheme' ); ?>
					</button>
					<button type="button" class="button button-small doodh-random-avatar-btn" style="font-size:11px;" title="<?php esc_attr_e( 'Pick a random modern avatar', 'vmtheme' ); ?>">
						<span class="dashicons dashicons-randomize" style="font-size:12px; line-height:22px;"></span> <?php esc_html_e( 'Random', 'vmtheme' ); ?>
					</button>
					<button type="button" class="doodh-close-panel-btn" style="background:none; border:none; color:#64748b; font-size:16px; cursor:pointer; line-height:1; padding:0 4px;" aria-label="<?php esc_attr_e( 'Close', 'vmtheme' ); ?>">&times;</button>
				</div>
			</div>

			<!-- Filter Tabs -->
			<div class="doodh-avatar-tabs" style="display:flex; gap:4px; margin-bottom:10px; flex-wrap:wrap;">
				<button type="button" class="button button-small doodh-avatar-tab is-active" data-cat="all" style="font-weight:700;"><?php esc_html_e( 'All', 'vmtheme' ); ?></button>
				<button type="button" class="button button-small doodh-avatar-tab" data-cat="3d"><?php esc_html_e( '🌟 3D Characters', 'vmtheme' ); ?></button>
				<button type="button" class="button button-small doodh-avatar-tab" data-cat="critics"><?php esc_html_e( '🎬 Critics', 'vmtheme' ); ?></button>
				<button type="button" class="button button-small doodh-avatar-tab" data-cat="illustrated"><?php esc_html_e( '✨ Illustrated', 'vmtheme' ); ?></button>
				<button type="button" class="button button-small doodh-avatar-tab" data-cat="notion"><?php esc_html_e( '🤖 Tech & Notion', 'vmtheme' ); ?></button>
			</div>

			<!-- Avatars Grid -->
			<div class="doodh-preset-avatars-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap:8px; max-height:165px; overflow-y:auto; padding:4px; background:#f8fafc; border-radius:6px; border:1px solid #f1f5f9;">
				<?php foreach ( $preset_avatars as $p_av ) : 
					$is_selected = ( $current_avatar === $p_av['url'] );
					?>
					<button type="button" class="doodh-preset-av-btn <?php echo $is_selected ? 'is-selected' : ''; ?>" data-url="<?php echo esc_url( $p_av['url'] ); ?>" data-cat="<?php echo esc_attr( $p_av['cat'] ); ?>" title="<?php echo esc_attr( $p_av['name'] ); ?>" style="position:relative; background:#ffffff; border:<?php echo $is_selected ? '2px solid #2563eb' : '1px solid #cbd5e1'; ?>; border-radius:50%; width:44px; height:44px; padding:2px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s ease; box-shadow:<?php echo $is_selected ? '0 0 0 2px rgba(37,99,235,0.25)' : 'none'; ?>;">
						<img src="<?php echo esc_url( $p_av['url'] ); ?>" alt="<?php echo esc_attr( $p_av['name'] ); ?>" width="38" height="38" style="width:38px; height:38px; border-radius:50%; display:block; object-fit:cover; pointer-events:none;" loading="lazy">
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render Reviews & Ratings Manager Metabox (Dynamic Multi-Review Repeater)
 */
function doodhtheme_render_reviews_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_reviews_meta', 'doodhtheme_reviews_nonce' );

	$overall_review_url = get_post_meta( $post->ID, '_doodh_review_url', true ) ?: get_post_meta( $post->ID, 'review_url', true );
	$reviews = get_post_meta( $post->ID, '_doodh_custom_reviews', true );
	if ( ! is_array( $reviews ) ) {
		$reviews = array();
	}
	?>
	<div class="doodh-admin-reviews-wrap" style="padding: 10px 0;">
		<!-- Overall Read More Review URL -->
		<div style="background:#ffffff; border:1px solid #cbd5e1; border-left:4px solid #2563eb; border-radius:8px; padding:15px; margin-bottom:18px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
			<label for="doodh_review_url" style="display:block; font-weight:700; font-size:13px; margin-bottom:4px; color:#1e293b;">
				<span class="dashicons dashicons-external" style="vertical-align:middle; color:#2563eb;"></span> <?php esc_html_e( 'Read More Review (Overall Review URL)', 'vmtheme' ); ?>
			</label>
			<input type="url" id="doodh_review_url" name="doodh_review_url" value="<?php echo esc_url( $overall_review_url ); ?>" placeholder="https://www.imdb.com/title/.../reviews or https://..." style="width:100%; padding:8px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:6px;">
			<p class="description" style="margin:5px 0 0 0; color:#64748b;">
				<?php esc_html_e( 'Paste full review article or external critique URL. When set, a prominent "Read More Review" button opening in a new tab will appear on the frontend Review section.', 'vmtheme' ); ?>
			</p>
		</div>

		<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid #ccd0d4;">
			<div>
				<strong style="font-size:14px; color:#1d2327;"><?php esc_html_e( 'Curated & Featured Reviews', 'vmtheme' ); ?></strong>
				<p class="description" style="margin:2px 0 0 0;"><?php esc_html_e( 'Add multiple editorial or featured user reviews with modern selectable avatars. These appear on the frontend and generate SEO Schema.', 'vmtheme' ); ?></p>
			</div>
			<button type="button" class="button button-primary" id="doodh-add-review-btn" style="display:flex; align-items:center; gap:5px;">
				<span class="dashicons dashicons-plus-alt2" style="line-height:26px;"></span> <?php esc_html_e( 'Add Review', 'vmtheme' ); ?>
			</button>
		</div>

		<div id="doodh-reviews-repeater-container" style="display:flex; flex-direction:column; gap:14px;">
			<?php if ( ! empty( $reviews ) ) : ?>
				<?php foreach ( $reviews as $idx => $rev ) : ?>
					<div class="doodh-review-admin-card" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:16px; position:relative; box-shadow:0 1px 4px rgba(0,0,0,0.03);">
						<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
							<strong style="font-size:13px; color:#334155;">
								<span class="dashicons dashicons-testimonial" style="color:#2271b1; vertical-align:middle;"></span>
								<span class="doodh-card-index-title"><?php printf( esc_html__( 'Review #%d - %s', 'vmtheme' ), $idx + 1, esc_html( $rev['author'] ?? 'Viewer' ) ); ?></span>
							</strong>
							<button type="button" class="button button-link-delete doodh-remove-review-btn" style="color:#b32d2e; text-decoration:none;">
								<span class="dashicons dashicons-trash" style="vertical-align:middle;"></span> <?php esc_html_e( 'Remove', 'vmtheme' ); ?>
							</button>
						</div>

						<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:12px;">
							<div>
								<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Reviewer Name *', 'vmtheme' ); ?></label>
								<input type="text" name="doodh_cr_author[]" value="<?php echo esc_attr( $rev['author'] ?? '' ); ?>" placeholder="e.g. Alex Harrison" style="width:100%;" required>
							</div>

							<div>
								<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Rating Score (1-10) *', 'vmtheme' ); ?></label>
								<select name="doodh_cr_rating[]" style="width:100%;">
									<?php for ( $s = 10; $s >= 1; $s-- ) : ?>
										<option value="<?php echo esc_attr( $s ); ?>" <?php selected( (int) ( $rev['rating'] ?? 9 ), $s ); ?>>
											<?php echo esc_html( $s ); ?> / 10 <?php echo $s >= 9 ? '★ ★ ★ ★ ★ (Masterpiece)' : ( $s >= 7 ? '★ ★ ★ ★ ☆ (Great)' : '★ ★ ★ ☆ ☆' ); ?>
										</option>
									<?php endfor; ?>
								</select>
							</div>

							<div>
								<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Review Date', 'vmtheme' ); ?></label>
								<input type="date" name="doodh_cr_date[]" value="<?php echo esc_attr( $rev['date'] ?? current_time( 'Y-m-d' ) ); ?>" style="width:100%;">
							</div>

							<div>
								<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Verified Badge', 'vmtheme' ); ?></label>
								<select name="doodh_cr_verified[]" style="width:100%;">
									<option value="1" <?php selected( ! empty( $rev['verified'] ), true ); ?>><?php esc_html_e( 'Yes (Verified Viewer)', 'vmtheme' ); ?></option>
									<option value="0" <?php selected( empty( $rev['verified'] ), true ); ?>><?php esc_html_e( 'No (Standard)', 'vmtheme' ); ?></option>
								</select>
							</div>
						</div>

						<div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
							<div>
								<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Review Headline / Title', 'vmtheme' ); ?></label>
								<input type="text" name="doodh_cr_title[]" value="<?php echo esc_attr( $rev['title'] ?? '' ); ?>" placeholder="e.g. Masterpiece storyline and visuals" style="width:100%;">
							</div>
							<div>
								<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Read More Review (Review URL)', 'vmtheme' ); ?></label>
								<input type="url" name="doodh_cr_review_url[]" value="<?php echo esc_url( $rev['review_url'] ?? '' ); ?>" placeholder="https://..." style="width:100%;">
							</div>
						</div>

						<!-- Reviewer Avatar Selector Block -->
						<div style="margin-bottom:12px;">
							<?php doodhtheme_render_avatar_picker_field( $rev['avatar'] ?? '' ); ?>
						</div>

						<div>
							<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Review Body / Comments *', 'vmtheme' ); ?></label>
							<textarea name="doodh_cr_content[]" rows="3" placeholder="<?php esc_attr_e( 'Detailed review commentary...', 'vmtheme' ); ?>" style="width:100%;" required><?php echo esc_textarea( $rev['content'] ?? '' ); ?></textarea>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div id="doodh-no-reviews-notice" style="<?php echo ! empty( $reviews ) ? 'display:none;' : ''; ?> background:#fff; border:1px dashed #cbd5e1; border-radius:6px; padding:25px; text-align:center; color:#64748b; margin-top:8px;">
			<span class="dashicons dashicons-star-half" style="font-size:32px; width:32px; height:32px; color:#94a3b8; margin-bottom:8px;"></span>
			<p style="margin:0; font-size:13px;"><?php esc_html_e( 'No custom reviews added yet. Click "+ Add Review" to add your first featured review.', 'vmtheme' ); ?></p>
		</div>

		<!-- Hidden Template for Dynamic Client-Side Review Insertion -->
		<template id="doodh-review-template">
			<div class="doodh-review-admin-card" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:16px; position:relative; box-shadow:0 1px 4px rgba(0,0,0,0.03); animation:doodhFadeIn 0.3s ease;">
				<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">
					<strong style="font-size:13px; color:#334155;">
						<span class="dashicons dashicons-testimonial" style="color:#2271b1; vertical-align:middle;"></span>
						<span class="doodh-card-index-title"><?php esc_html_e( 'New Review', 'vmtheme' ); ?></span>
					</strong>
					<button type="button" class="button button-link-delete doodh-remove-review-btn" style="color:#b32d2e; text-decoration:none;">
						<span class="dashicons dashicons-trash" style="vertical-align:middle;"></span> <?php esc_html_e( 'Remove', 'vmtheme' ); ?>
					</button>
				</div>

				<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Reviewer Name *', 'vmtheme' ); ?></label>
						<input type="text" name="doodh_cr_author[]" value="" placeholder="e.g. Alex Harrison" style="width:100%;" required>
					</div>

					<div>
						<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Rating Score (1-10) *', 'vmtheme' ); ?></label>
						<select name="doodh_cr_rating[]" style="width:100%;">
							<?php for ( $s = 10; $s >= 1; $s-- ) : ?>
								<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $s, 9 ); ?>>
									<?php echo esc_html( $s ); ?> / 10 <?php echo $s >= 9 ? '★ ★ ★ ★ ★ (Masterpiece)' : ( $s >= 7 ? '★ ★ ★ ★ ☆ (Great)' : '★ ★ ★ ☆ ☆' ); ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>

					<div>
						<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Review Date', 'vmtheme' ); ?></label>
						<input type="date" name="doodh_cr_date[]" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" style="width:100%;">
					</div>

					<div>
						<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Verified Badge', 'vmtheme' ); ?></label>
						<select name="doodh_cr_verified[]" style="width:100%;">
							<option value="1"><?php esc_html_e( 'Yes (Verified Viewer)', 'vmtheme' ); ?></option>
							<option value="0"><?php esc_html_e( 'No (Standard)', 'vmtheme' ); ?></option>
						</select>
					</div>
				</div>

				<div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
					<div>
						<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Review Headline / Title', 'vmtheme' ); ?></label>
						<input type="text" name="doodh_cr_title[]" value="" placeholder="e.g. Masterpiece storyline and visuals" style="width:100%;">
					</div>
					<div>
						<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Read More Review (Review URL)', 'vmtheme' ); ?></label>
						<input type="url" name="doodh_cr_review_url[]" value="" placeholder="https://..." style="width:100%;">
					</div>
				</div>

				<!-- Reviewer Avatar Selector Block -->
				<div style="margin-bottom:12px;">
					<?php doodhtheme_render_avatar_picker_field( '' ); ?>
				</div>

				<div>
					<label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;"><?php esc_html_e( 'Review Body / Comments *', 'vmtheme' ); ?></label>
					<textarea name="doodh_cr_content[]" rows="3" placeholder="<?php esc_attr_e( 'Detailed review commentary...', 'vmtheme' ); ?>" style="width:100%;" required></textarea>
				</div>
			</div>
		</template>
	</div>

	<style>
	.doodh-preset-av-btn:hover {
		transform: scale(1.15);
		border-color: #2563eb !important;
		box-shadow: 0 4px 12px rgba(37,99,235,0.3) !important;
		z-index: 5;
	}
	.doodh-preset-av-btn.is-selected {
		border-color: #2563eb !important;
		border-width: 2px !important;
		box-shadow: 0 0 0 2px rgba(37,99,235,0.35) !important;
	}
	.doodh-avatar-tab.is-active {
		background: #2563eb !important;
		color: #ffffff !important;
		border-color: #2563eb !important;
	}
	</style>

	<script>
	(function() {
		const addBtn = document.getElementById('doodh-add-review-btn');
		const container = document.getElementById('doodh-reviews-repeater-container');
		const notice = document.getElementById('doodh-no-reviews-notice');
		const template = document.getElementById('doodh-review-template');

		if (!addBtn || !container || !template) return;

		function updateIndexTitles() {
			const cards = container.querySelectorAll('.doodh-review-admin-card');
			cards.forEach((card, idx) => {
				const title = card.querySelector('.doodh-card-index-title');
				const authorInput = card.querySelector('input[name="doodh_cr_author[]"]');
				const authorName = authorInput && authorInput.value.trim() ? authorInput.value.trim() : 'Reviewer';
				if (title) {
					title.textContent = 'Review #' + (idx + 1) + ' - ' + authorName;
				}
			});
			if (notice) {
				notice.style.display = cards.length === 0 ? 'block' : 'none';
			}
		}

		addBtn.addEventListener('click', function(e) {
			e.preventDefault();
			const clone = template.content.cloneNode(true);
			container.appendChild(clone);
			updateIndexTitles();

			const lastCard = container.lastElementChild;
			if (lastCard) {
				const firstInput = lastCard.querySelector('input[name="doodh_cr_author[]"]');
				if (firstInput) firstInput.focus();
			}
		});

		// Delegate events within container
		container.addEventListener('click', function(e) {
			// 1. Remove Review
			const removeBtn = e.target.closest('.doodh-remove-review-btn');
			if (removeBtn) {
				e.preventDefault();
				if (confirm('Are you sure you want to remove this review?')) {
					const card = removeBtn.closest('.doodh-review-admin-card');
					if (card) {
						card.remove();
						updateIndexTitles();
					}
				}
				return;
			}

			// 2. Toggle Avatar Panel
			const togglePanelBtn = e.target.closest('.doodh-toggle-avatar-panel-btn');
			if (togglePanelBtn) {
				e.preventDefault();
				const card = togglePanelBtn.closest('.doodh-review-admin-card');
				const panel = card ? card.querySelector('.doodh-avatar-panel') : null;
				if (panel) {
					const isOpen = panel.style.display === 'block';
					// Close other panels inside container
					container.querySelectorAll('.doodh-avatar-panel').forEach(p => p.style.display = 'none');
					panel.style.display = isOpen ? 'none' : 'block';
				}
				return;
			}

			// 3. Close Avatar Panel
			const closePanelBtn = e.target.closest('.doodh-close-panel-btn');
			if (closePanelBtn) {
				e.preventDefault();
				const panel = closePanelBtn.closest('.doodh-avatar-panel');
				if (panel) panel.style.display = 'none';
				return;
			}

			// 4. Avatar Tab Filtering
			const tabBtn = e.target.closest('.doodh-avatar-tab');
			if (tabBtn) {
				e.preventDefault();
				const panel = tabBtn.closest('.doodh-avatar-panel');
				if (panel) {
					panel.querySelectorAll('.doodh-avatar-tab').forEach(b => b.classList.remove('is-active'));
					tabBtn.classList.add('is-active');
					const cat = tabBtn.dataset.cat;
					const avBtns = panel.querySelectorAll('.doodh-preset-av-btn');
					avBtns.forEach(btn => {
						if (cat === 'all' || btn.dataset.cat === cat) {
							btn.style.display = 'flex';
						} else {
							btn.style.display = 'none';
						}
					});
				}
				return;
			}

			// 5. Preset Avatar Selection
			const avBtn = e.target.closest('.doodh-preset-av-btn');
			if (avBtn) {
				e.preventDefault();
				const url = avBtn.dataset.url;
				const card = avBtn.closest('.doodh-review-admin-card');
				if (card && url) {
					const input = card.querySelector('input[name="doodh_cr_avatar[]"]');
					const thumb = card.querySelector('.doodh-avatar-live-thumb');
					if (input) input.value = url;
					if (thumb) thumb.src = url;

					// Highlight selected
					const panel = avBtn.closest('.doodh-avatar-panel');
					if (panel) {
						panel.querySelectorAll('.doodh-preset-av-btn').forEach(b => {
							b.classList.remove('is-selected');
							b.style.border = '1px solid #cbd5e1';
							b.style.boxShadow = 'none';
						});
						avBtn.classList.add('is-selected');
						avBtn.style.border = '2px solid #2563eb';
						avBtn.style.boxShadow = '0 0 0 2px rgba(37,99,235,0.35)';
					}
				}
				return;
			}

			// 6. Generate Avatar by Name
			const genByNameBtn = e.target.closest('.doodh-gen-by-name-btn');
			if (genByNameBtn) {
				e.preventDefault();
				const card = genByNameBtn.closest('.doodh-review-admin-card');
				if (card) {
					const authorInput = card.querySelector('input[name="doodh_cr_author[]"]');
					const name = authorInput && authorInput.value.trim() ? authorInput.value.trim() : 'User_' + Math.floor(Math.random() * 1000);
					const generatedUrl = 'https://api.dicebear.com/9.x/adventurer/svg?seed=' + encodeURIComponent(name);
					
					const input = card.querySelector('input[name="doodh_cr_avatar[]"]');
					const thumb = card.querySelector('.doodh-avatar-live-thumb');
					if (input) input.value = generatedUrl;
					if (thumb) thumb.src = generatedUrl;
				}
				return;
			}

			// 7. Pick Random Avatar
			const randomBtn = e.target.closest('.doodh-random-avatar-btn');
			if (randomBtn) {
				e.preventDefault();
				const panel = randomBtn.closest('.doodh-avatar-panel');
				const card = randomBtn.closest('.doodh-review-admin-card');
				if (panel && card) {
					const avBtns = Array.from(panel.querySelectorAll('.doodh-preset-av-btn'));
					if (avBtns.length) {
						const randomAv = avBtns[Math.floor(Math.random() * avBtns.length)];
						randomAv.click();
					}
				}
				return;
			}

			// 8. Media Library Upload
			const mediaBtn = e.target.closest('.doodh-media-avatar-btn');
			if (mediaBtn) {
				e.preventDefault();
				const card = mediaBtn.closest('.doodh-review-admin-card');
				if (card && typeof wp !== 'undefined' && wp.media) {
					const frame = wp.media({
						title: 'Select or Upload Reviewer Avatar',
						button: { text: 'Use this Avatar' },
						multiple: false,
						library: { type: 'image' }
					});

					frame.on('select', function() {
						const attachment = frame.state().get('selection').first().toJSON();
						if (attachment && attachment.url) {
							const input = card.querySelector('input[name="doodh_cr_avatar[]"]');
							const thumb = card.querySelector('.doodh-avatar-live-thumb');
							if (input) input.value = attachment.url;
							if (thumb) thumb.src = attachment.url;
						}
					});

					frame.open();
				}
				return;
			}
		});

		// Live update on input change
		container.addEventListener('input', function(e) {
			if (e.target && e.target.matches('input[name="doodh_cr_author[]"]')) {
				updateIndexTitles();
			}
			if (e.target && e.target.matches('input[name="doodh_cr_avatar[]"]')) {
				const card = e.target.closest('.doodh-review-admin-card');
				const thumb = card ? card.querySelector('.doodh-avatar-live-thumb') : null;
				if (thumb) {
					thumb.src = e.target.value.trim() || '<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';
				}
			}
		});
	})();
	</script>
	<?php
}

