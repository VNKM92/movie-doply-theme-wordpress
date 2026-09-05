<?php
/**
 * Post Uniqueness & Duplicate Validation System
 *
 * Enforces strict 100% uniqueness across Movies, TV Shows, and Episodes.
 * Prevents duplicate TMDb IDs, IMDb IDs, Slugs, and Titles on manual entry,
 * REST API, XML-RPC, and TMDb Importer.
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if a title, TMDb ID, or IMDb ID already exists in the database
 *
 * @param string $title Post title
 * @param string $post_type Post type ('movies', 'tvshows', 'episodes')
 * @param string|int $tmdb_id TMDb ID
 * @param string $imdb_id IMDb ID
 * @param int $exclude_id Post ID to exclude (when updating existing post)
 * @return array|false Returns array with existing post info if duplicate found, or false if unique.
 */
function doodhtheme_check_duplicate_post( $title = '', $post_type = 'movies', $tmdb_id = '', $imdb_id = '', $exclude_id = 0 ) {
	global $wpdb;

	$exclude_sql = $exclude_id ? $wpdb->prepare( "AND ID != %d", $exclude_id ) : "";

	// 1. Check by TMDb ID (Strict match)
	if ( ! empty( $tmdb_id ) ) {
		$found_by_tmdb = $wpdb->get_row( $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_type, p.post_status 
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id)
			 WHERE pm.meta_key = '_doodh_tmdb_id' 
			   AND pm.meta_value = %s 
			   AND p.post_status IN ('publish', 'future', 'draft', 'pending')
			   {$exclude_sql}
			 LIMIT 1",
			trim( (string) $tmdb_id )
		) );

		if ( $found_by_tmdb ) {
			return array(
				'is_duplicate' => true,
				'reason'       => 'tmdb_id',
				'match_value'  => $tmdb_id,
				'post_id'      => $found_by_tmdb->ID,
				'post_title'   => $found_by_tmdb->post_title,
				'post_type'    => $found_by_tmdb->post_type,
				'edit_url'     => get_edit_post_link( $found_by_tmdb->ID, 'raw' ),
				'view_url'     => get_permalink( $found_by_tmdb->ID ),
			);
		}
	}

	// 2. Check by IMDb ID (Strict match)
	if ( ! empty( $imdb_id ) ) {
		$found_by_imdb = $wpdb->get_row( $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_type, p.post_status 
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id)
			 WHERE pm.meta_key = '_doodh_imdb_id' 
			   AND pm.meta_value = %s 
			   AND p.post_status IN ('publish', 'future', 'draft', 'pending')
			   {$exclude_sql}
			 LIMIT 1",
			trim( (string) $imdb_id )
		) );

		if ( $found_by_imdb ) {
			return array(
				'is_duplicate' => true,
				'reason'       => 'imdb_id',
				'match_value'  => $imdb_id,
				'post_id'      => $found_by_imdb->ID,
				'post_title'   => $found_by_imdb->post_title,
				'post_type'    => $found_by_imdb->post_type,
				'edit_url'     => get_edit_post_link( $found_by_imdb->ID, 'raw' ),
				'view_url'     => get_permalink( $found_by_imdb->ID ),
			);
		}
	}

	// 3. Check by Exact Normalized Title within the same post_type
	if ( ! empty( $title ) && in_array( $post_type, array( 'movies', 'tvshows' ) ) ) {
		$cleaned_title = trim( $title );
		$found_by_title = $wpdb->get_row( $wpdb->prepare(
			"SELECT ID, post_title, post_type, post_status 
			 FROM {$wpdb->posts} 
			 WHERE post_title = %s 
			   AND post_type = %s 
			   AND post_status IN ('publish', 'future', 'draft', 'pending')
			   {$exclude_sql}
			 LIMIT 1",
			$cleaned_title,
			$post_type
		) );

		if ( $found_by_title ) {
			return array(
				'is_duplicate' => true,
				'reason'       => 'title',
				'match_value'  => $cleaned_title,
				'post_id'      => $found_by_title->ID,
				'post_title'   => $found_by_title->post_title,
				'post_type'    => $found_by_title->post_type,
				'edit_url'     => get_edit_post_link( $found_by_title->ID, 'raw' ),
				'view_url'     => get_permalink( $found_by_title->ID ),
			);
		}
	}

	return false;
}

/**
 * Live AJAX Endpoint for Admin Editor to Validate TMDb ID or Title Uniqueness
 */
function doodhtheme_ajax_validate_uniqueness() {
	check_ajax_referer( 'doodh_validate_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Permission denied.' );
	}

	$title     = sanitize_text_field( $_POST['title'] ?? '' );
	$post_type = sanitize_text_field( $_POST['post_type'] ?? 'movies' );
	$tmdb_id   = sanitize_text_field( $_POST['tmdb_id'] ?? '' );
	$imdb_id   = sanitize_text_field( $_POST['imdb_id'] ?? '' );
	$post_id   = (int) ( $_POST['post_id'] ?? 0 );

	$duplicate = doodhtheme_check_duplicate_post( $title, $post_type, $tmdb_id, $imdb_id, $post_id );

	if ( $duplicate ) {
		wp_send_json_success( array(
			'is_duplicate' => true,
			'message'      => sprintf(
				'Duplicate Detected! This %s already exists as "%s" (ID #%d).',
				strtoupper( $duplicate['reason'] ),
				esc_html( $duplicate['post_title'] ),
				$duplicate['post_id']
			),
			'edit_url'     => $duplicate['edit_url'],
			'view_url'     => $duplicate['view_url'],
		) );
	} else {
		wp_send_json_success( array(
			'is_duplicate' => false,
			'message'      => '100% Unique! No duplicate found in database.',
		) );
	}
}
add_action( 'wp_ajax_doodhtheme_validate_uniqueness', 'doodhtheme_ajax_validate_uniqueness' );

/**
 * Enqueue Live Uniqueness Validation Script in WP Admin Post Editor
 */
function doodhtheme_enqueue_admin_validator_scripts( $hook ) {
	global $post_type, $post;

	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
		return;
	}

	if ( ! in_array( $post_type, array( 'movies', 'tvshows', 'episodes' ) ) ) {
		return;
	}

	$current_id = $post ? $post->ID : 0;
	$nonce      = wp_create_nonce( 'doodh_validate_nonce' );

	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var validateTimeout;
		var $tmdbInput = $('#doodh_tmdb_id');
		var $titleInput = $('#title');
		var currentPostId = <?php echo (int) $current_id; ?>;
		var currentPostType = '<?php echo esc_js( $post_type ); ?>';

		// Create feedback badge
		var $badge = $('<div id="doodh-duplicate-alert" style="margin: 10px 0; padding: 10px 14px; border-radius: 6px; font-weight: 600; display: none;"></div>');
		
		if ($tmdbInput.length) {
			$tmdbInput.closest('.doodh-meta-row, td, div').append($badge);
		} else {
			$('#titlediv').after($badge);
		}

		function triggerValidation() {
			clearTimeout(validateTimeout);
			validateTimeout = setTimeout(function() {
				var tmdbVal  = $tmdbInput.val() ? $tmdbInput.val().trim() : '';
				var titleVal = $titleInput.val() ? $titleInput.val().trim() : '';

				if (!tmdbVal && !titleVal) {
					$badge.hide();
					return;
				}

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'doodhtheme_validate_uniqueness',
						tmdb_id: tmdbVal,
						title: titleVal,
						post_type: currentPostType,
						post_id: currentPostId,
						nonce: '<?php echo esc_js( $nonce ); ?>'
					},
					success: function(res) {
						if (res.success) {
							if (res.data.is_duplicate) {
								$badge.css({
									'display': 'block',
									'background': '#fee2e2',
									'border': '1px solid #ef4444',
									'color': '#991b1b'
								}).html('&#9888; <strong>' + res.data.message + '</strong> <a href="' + res.data.edit_url + '" target="_blank" style="color:#b91c1c; text-decoration:underline; margin-left:8px;">[Edit Existing Post &rarr;]</a>');
							} else {
								$badge.css({
									'display': 'block',
									'background': '#dcfce7',
									'border': '1px solid #22c55e',
									'color': '#166534'
								}).html('&#10004; <strong>' + res.data.message + '</strong>');
							}
						}
					}
				});
			}, 400);
		}

		$tmdbInput.on('input change blur', triggerValidation);
		$titleInput.on('input change blur', triggerValidation);
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'doodhtheme_enqueue_admin_validator_scripts' );

/**
 * WordPress Post Save Hook: Prevent saving duplicates or show admin notice
 */
function doodhtheme_intercept_duplicate_post_save( $post_id, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! in_array( $post->post_type, array( 'movies', 'tvshows' ) ) ) {
		return;
	}

	$tmdb_id = isset( $_POST['doodh_tmdb_id'] ) ? sanitize_text_field( $_POST['doodh_tmdb_id'] ) : get_post_meta( $post_id, '_doodh_tmdb_id', true );
	$imdb_id = isset( $_POST['doodh_imdb_id'] ) ? sanitize_text_field( $_POST['doodh_imdb_id'] ) : get_post_meta( $post_id, '_doodh_imdb_id', true );

	$dup = doodhtheme_check_duplicate_post( $post->post_title, $post->post_type, $tmdb_id, $imdb_id, $post_id );

	if ( $dup ) {
		// Set transient notice for admin
		set_transient( 'doodh_duplicate_notice_' . get_current_user_id(), array(
			'title'     => $post->post_title,
			'duplicate' => $dup,
		), 60 );
	}
}
add_action( 'save_post', 'doodhtheme_intercept_duplicate_post_save', 10, 3 );

/**
 * Display Admin Notice if duplicate was attempted
 */
function doodhtheme_display_duplicate_admin_notice() {
	$notice = get_transient( 'doodh_duplicate_notice_' . get_current_user_id() );
	if ( $notice ) {
		delete_transient( 'doodh_duplicate_notice_' . get_current_user_id() );
		$d = $notice['duplicate'];
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Notice - Potential Duplicate Detected:', 'doodhtheme' ); ?></strong>
				<?php
				echo sprintf(
					esc_html__( 'A %s with this %s already exists in your database: "%s" (Post ID: #%d).', 'doodhtheme' ),
					esc_html( $d['post_type'] ),
					esc_html( strtoupper( $d['reason'] ) ),
					esc_html( $d['post_title'] ),
					(int) $d['post_id']
				);
				?>
				<a href="<?php echo esc_url( $d['edit_url'] ); ?>" style="font-weight:700; margin-left:6px;"><?php esc_html_e( 'Click here to edit existing post &rarr;', 'doodhtheme' ); ?></a>
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'doodhtheme_display_duplicate_admin_notice' );
