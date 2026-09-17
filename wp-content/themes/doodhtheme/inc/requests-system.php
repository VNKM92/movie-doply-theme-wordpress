<?php
/**
 * Movie & TV Show Request Management System
 * Custom Post Type, Admin Management, Frontend Submissions, and Live User Status Tracking
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register 'movie_requests' Custom Post Type
 */
function vmtheme_register_requests_cpt() {
	$labels = array(
		'name'               => _x( 'Movie & TV Requests', 'Post Type General Name', 'vmtheme' ),
		'singular_name'      => _x( 'Request', 'Post Type Singular Name', 'vmtheme' ),
		'menu_name'          => __( 'Title Requests', 'vmtheme' ),
		'name_admin_bar'     => __( 'Request', 'vmtheme' ),
		'all_items'          => __( 'All Requests', 'vmtheme' ),
		'add_new_item'       => __( 'Add New Request', 'vmtheme' ),
		'edit_item'          => __( 'Edit Request', 'vmtheme' ),
		'view_item'          => __( 'View Request', 'vmtheme' ),
		'search_items'       => __( 'Search Requests', 'vmtheme' ),
		'not_found'          => __( 'No requests found.', 'vmtheme' ),
		'not_found_in_trash' => __( 'No requests found in Trash.', 'vmtheme' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=movies',
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-format-chat',
		'supports'           => array( 'title', 'editor', 'author' ),
		'show_in_rest'       => false,
	);

	register_post_type( 'movie_requests', $args );
}
add_action( 'init', 'vmtheme_register_requests_cpt' );

if ( ! function_exists( 'doodhtheme_register_requests_cpt' ) ) {
	function doodhtheme_register_requests_cpt() {
		vmtheme_register_requests_cpt();
	}
}

/**
 * Add Custom Columns in Admin Requests List
 */
function vmtheme_requests_columns( $columns ) {
	$new_columns = array(
		'cb'             => $columns['cb'],
		'title'          => __( 'Requested Title', 'vmtheme' ),
		'request_type'   => __( 'Type', 'vmtheme' ),
		'request_year'   => __( 'Year', 'vmtheme' ),
		'request_status' => __( 'Status', 'vmtheme' ),
		'requested_by'   => __( 'Requested By', 'vmtheme' ),
		'request_imdb'   => __( 'IMDb Link', 'vmtheme' ),
		'date'           => __( 'Date', 'vmtheme' ),
	);
	return $new_columns;
}
add_filter( 'manage_movie_requests_posts_columns', 'vmtheme_requests_columns' );

/**
 * Render Custom Column Content
 */
function doodhtheme_render_requests_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'request_type':
			echo esc_html( get_post_meta( $post_id, '_doodh_req_type', true ) ?: 'Movie' );
			break;

		case 'request_year':
			$year = get_post_meta( $post_id, '_doodh_req_year', true );
			echo esc_html( $year ?: '-' );
			break;

		case 'request_status':
			$status = get_post_meta( $post_id, '_doodh_req_status', true ) ?: 'pending';
			switch ( $status ) {
				case 'completed':
					$linked_id = get_post_meta( $post_id, '_doodh_req_linked_post', true );
					$link_html = $linked_id ? ' <a href="' . esc_url( get_permalink( $linked_id ) ) . '" target="_blank" title="View Title"><span class="dashicons dashicons-external" style="font-size:14px; vertical-align:middle;"></span></a>' : '';
					echo '<span class="badge" style="background:#10b981; color:#fff; padding:3px 8px; border-radius:4px; font-weight:700; font-size:11px;">' . esc_html__( 'Added / Available', 'vmtheme' ) . '</span>' . $link_html;
					break;
				case 'in_progress':
					echo '<span class="badge" style="background:#2563eb; color:#fff; padding:3px 8px; border-radius:4px; font-weight:700; font-size:11px;">' . esc_html__( 'In Progress', 'vmtheme' ) . '</span>';
					break;
				case 'declined':
					echo '<span class="badge" style="background:#ef4444; color:#fff; padding:3px 8px; border-radius:4px; font-weight:700; font-size:11px;">' . esc_html__( 'Declined / Unavailable', 'vmtheme' ) . '</span>';
					break;
				default:
					echo '<span class="badge" style="background:#f59e0b; color:#fff; padding:3px 8px; border-radius:4px; font-weight:700; font-size:11px;">' . esc_html__( 'Pending Review', 'vmtheme' ) . '</span>';
					break;
			}
			break;

		case 'requested_by':
			$user_id = get_post_meta( $post_id, '_doodh_req_user_id', true );
			if ( $user_id && $user = get_user_by( 'id', $user_id ) ) {
				echo '<div style="display:flex; align-items:center; gap:6px;">' .
				     '<img src="' . esc_url( doodhtheme_get_user_avatar( $user_id ) ) . '" style="width:22px; height:22px; border-radius:50%;" /> ' .
				     esc_html( $user->display_name ?: $user->user_login ) . '</div>';
			} else {
				$guest_name = get_post_meta( $post_id, '_doodh_req_guest_name', true );
				echo esc_html( $guest_name ?: __( 'Guest User', 'vmtheme' ) );
			}
			break;

		case 'request_imdb':
			$imdb = get_post_meta( $post_id, '_doodh_req_imdb', true );
			if ( $imdb ) {
				echo '<a href="' . esc_url( $imdb ) . '" target="_blank" rel="noopener noreferrer" style="color:#2271b1;"><span class="dashicons dashicons-external"></span> ' . esc_html__( 'View Link', 'vmtheme' ) . '</a>';
			} else {
				echo '-';
			}
			break;
	}
}
add_action( 'manage_movie_requests_posts_custom_column', 'doodhtheme_render_requests_columns', 10, 2 );

/**
 * Register Metabox for Movie Requests
 */
function doodhtheme_add_request_metabox() {
	add_meta_box(
		'doodhtheme_request_meta',
		__( 'Request Information & Status Control', 'vmtheme' ),
		'doodhtheme_render_request_metabox',
		'movie_requests',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'doodhtheme_add_request_metabox' );

/**
 * Render Request Metabox
 */
function doodhtheme_render_request_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_request_meta', 'doodhtheme_request_nonce' );

	$type       = get_post_meta( $post->ID, '_doodh_req_type', true ) ?: 'Movie';
	$year       = get_post_meta( $post->ID, '_doodh_req_year', true );
	$imdb       = get_post_meta( $post->ID, '_doodh_req_imdb', true );
	$status     = get_post_meta( $post->ID, '_doodh_req_status', true ) ?: 'pending';
	$linked_id  = get_post_meta( $post->ID, '_doodh_req_linked_post', true );
	$admin_note = get_post_meta( $post->ID, '_doodh_req_admin_note', true );
	$user_id    = get_post_meta( $post->ID, '_doodh_req_user_id', true );
	?>
	<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; padding:10px 0;">
		<div>
			<label style="display:block; font-weight:700; margin-bottom:4px;"><?php esc_html_e( 'Request Status', 'vmtheme' ); ?></label>
			<select name="doodh_req_status" style="width:100%; padding:6px 10px; font-weight:600;">
				<option value="pending" <?php selected( $status, 'pending' ); ?>>🟡 <?php esc_html_e( 'Pending Review', 'vmtheme' ); ?></option>
				<option value="in_progress" <?php selected( $status, 'in_progress' ); ?>>🔵 <?php esc_html_e( 'In Progress / Encoding', 'vmtheme' ); ?></option>
				<option value="completed" <?php selected( $status, 'completed' ); ?>>🟢 <?php esc_html_e( 'Completed / Added to Site', 'vmtheme' ); ?></option>
				<option value="declined" <?php selected( $status, 'declined' ); ?>>🔴 <?php esc_html_e( 'Declined / Unavailable', 'vmtheme' ); ?></option>
			</select>
		</div>

		<div>
			<label style="display:block; font-weight:700; margin-bottom:4px;"><?php esc_html_e( 'Content Type', 'vmtheme' ); ?></label>
			<select name="doodh_req_type" style="width:100%; padding:6px 10px;">
				<option value="Movie" <?php selected( $type, 'Movie' ); ?>><?php esc_html_e( 'Movie', 'vmtheme' ); ?></option>
				<option value="TV Show / Series" <?php selected( $type, 'TV Show / Series' ); ?>><?php esc_html_e( 'TV Show / Series', 'vmtheme' ); ?></option>
				<option value="Anime" <?php selected( $type, 'Anime' ); ?>><?php esc_html_e( 'Anime', 'vmtheme' ); ?></option>
			</select>
		</div>

		<div>
			<label style="display:block; font-weight:700; margin-bottom:4px;"><?php esc_html_e( 'Release Year', 'vmtheme' ); ?></label>
			<input type="number" name="doodh_req_year" value="<?php echo esc_attr( $year ); ?>" placeholder="e.g. 2024" style="width:100%;">
		</div>

		<div>
			<label style="display:block; font-weight:700; margin-bottom:4px;"><?php esc_html_e( 'TMDb / IMDb Reference Link', 'vmtheme' ); ?></label>
			<input type="url" name="doodh_req_imdb" value="<?php echo esc_url( $imdb ); ?>" placeholder="https://..." style="width:100%;">
		</div>

		<div>
			<label style="display:block; font-weight:700; margin-bottom:4px;"><?php esc_html_e( 'Link to Published Post (Optional)', 'vmtheme' ); ?></label>
			<input type="number" name="doodh_req_linked_post" value="<?php echo esc_attr( $linked_id ); ?>" placeholder="Post ID (e.g. 124)" style="width:100%;">
			<p class="description"><?php esc_html_e( 'When entered, the user can click directly to stream the title once completed.', 'vmtheme' ); ?></p>
		</div>

		<div>
			<label style="display:block; font-weight:700; margin-bottom:4px;"><?php esc_html_e( 'Admin Note / Response to User', 'vmtheme' ); ?></label>
			<input type="text" name="doodh_req_admin_note" value="<?php echo esc_attr( $admin_note ); ?>" placeholder="e.g. Added in 1080p Dual Audio!" style="width:100%;">
		</div>
	</div>
	<?php
}

/**
 * Save Request Metabox Data
 */
function doodhtheme_save_request_meta( $post_id ) {
	if ( ! isset( $_POST['doodhtheme_request_nonce'] ) || ! wp_verify_nonce( $_POST['doodhtheme_request_nonce'], 'doodhtheme_save_request_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['doodh_req_status'] ) ) {
		update_post_meta( $post_id, '_doodh_req_status', sanitize_text_field( $_POST['doodh_req_status'] ) );
	}
	if ( isset( $_POST['doodh_req_type'] ) ) {
		update_post_meta( $post_id, '_doodh_req_type', sanitize_text_field( $_POST['doodh_req_type'] ) );
	}
	if ( isset( $_POST['doodh_req_year'] ) ) {
		update_post_meta( $post_id, '_doodh_req_year', sanitize_text_field( $_POST['doodh_req_year'] ) );
	}
	if ( isset( $_POST['doodh_req_imdb'] ) ) {
		update_post_meta( $post_id, '_doodh_req_imdb', esc_url_raw( $_POST['doodh_req_imdb'] ) );
	}
	if ( isset( $_POST['doodh_req_linked_post'] ) ) {
		update_post_meta( $post_id, '_doodh_req_linked_post', intval( $_POST['doodh_req_linked_post'] ) );
	}
	if ( isset( $_POST['doodh_req_admin_note'] ) ) {
		update_post_meta( $post_id, '_doodh_req_admin_note', sanitize_text_field( $_POST['doodh_req_admin_note'] ) );
	}
}
add_action( 'save_post_movie_requests', 'doodhtheme_save_request_meta' );

/**
 * Handle AJAX Request Submission from Frontend
 */
function vmtheme_ajax_submit_request() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	$title = isset( $_POST['request_title'] ) ? sanitize_text_field( trim( $_POST['request_title'] ) ) : '';
	$type  = isset( $_POST['request_type'] ) ? sanitize_text_field( trim( $_POST['request_type'] ) ) : 'Movie';
	$year  = isset( $_POST['request_year'] ) ? sanitize_text_field( trim( $_POST['request_year'] ) ) : '';
	$imdb  = isset( $_POST['request_imdb'] ) ? esc_url_raw( trim( $_POST['request_imdb'] ) ) : '';
	$notes = isset( $_POST['request_notes'] ) ? sanitize_textarea_field( trim( $_POST['request_notes'] ) ) : '';

	if ( empty( $title ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter the title name of the movie or TV show.', 'vmtheme' ) ) );
	}

	$user_id = get_current_user_id();

	$post_data = array(
		'post_title'   => $title,
		'post_content' => $notes,
		'post_status'  => 'publish',
		'post_type'    => 'movie_requests',
		'post_author'  => $user_id ?: 1,
	);

	$request_id = wp_insert_post( $post_data );

	if ( is_wp_error( $request_id ) || ! $request_id ) {
		wp_send_json_error( array( 'message' => __( 'Failed to save request. Please try again.', 'vmtheme' ) ) );
	}

	update_post_meta( $request_id, '_doodh_req_type', $type );
	update_post_meta( $request_id, '_doodh_req_year', $year );
	update_post_meta( $request_id, '_doodh_req_imdb', $imdb );
	update_post_meta( $request_id, '_doodh_req_status', 'pending' );
	update_post_meta( $request_id, '_doodh_req_user_id', $user_id );
	update_post_meta( $request_id, '_vm_req_type', $type );
	update_post_meta( $request_id, '_vm_req_year', $year );
	update_post_meta( $request_id, '_vm_req_imdb', $imdb );
	update_post_meta( $request_id, '_vm_req_status', 'pending' );
	update_post_meta( $request_id, '_vm_req_user_id', $user_id );

	if ( ! $user_id ) {
		$guest_name = isset( $_POST['guest_name'] ) ? sanitize_text_field( $_POST['guest_name'] ) : 'Guest';
		update_post_meta( $request_id, '_doodh_req_guest_name', $guest_name );
		update_post_meta( $request_id, '_vm_req_guest_name', $guest_name );
	}

	wp_send_json_success( array(
		'message' => __( 'Your request has been submitted successfully! Our team will process it shortly.', 'vmtheme' ),
		'req_id'  => $request_id,
	) );
}
add_action( 'wp_ajax_vm_submit_request', 'vmtheme_ajax_submit_request' );
add_action( 'wp_ajax_nopriv_vm_submit_request', 'vmtheme_ajax_submit_request' );
add_action( 'wp_ajax_doodh_submit_request', 'vmtheme_ajax_submit_request' );
add_action( 'wp_ajax_nopriv_doodh_submit_request', 'vmtheme_ajax_submit_request' );

if ( ! function_exists( 'doodhtheme_ajax_submit_request' ) ) {
	function doodhtheme_ajax_submit_request() {
		vmtheme_ajax_submit_request();
	}
}

/**
 * Handle AJAX Get User's Submitted Requests
 */
function vmtheme_ajax_get_user_requests() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'You must be logged in to view your requests.', 'vmtheme' ) ) );
	}

	$user_id = get_current_user_id();

	$query = new WP_Query( array(
		'post_type'      => 'movie_requests',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'   => '_vm_req_user_id',
				'value' => $user_id,
			),
			array(
				'key'   => '_doodh_req_user_id',
				'value' => $user_id,
			),
		),
	) );

	$results = array();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$req_id    = get_the_ID();
			$status    = get_post_meta( $req_id, '_vm_req_status', true ) ?: get_post_meta( $req_id, '_doodh_req_status', true ) ?: 'pending';
			$linked_id = get_post_meta( $req_id, '_vm_req_linked_post', true ) ?: get_post_meta( $req_id, '_doodh_req_linked_post', true );
			$link_url  = $linked_id ? get_permalink( $linked_id ) : '';

			$results[] = array(
				'id'         => $req_id,
				'title'      => get_the_title(),
				'type'       => get_post_meta( $req_id, '_vm_req_type', true ) ?: get_post_meta( $req_id, '_doodh_req_type', true ) ?: 'Movie',
				'year'       => get_post_meta( $req_id, '_vm_req_year', true ) ?: get_post_meta( $req_id, '_doodh_req_year', true ) ?: '',
				'status'     => $status,
				'date'       => get_the_date( 'M j, Y' ),
				'link_url'   => $link_url,
				'admin_note' => get_post_meta( $req_id, '_vm_req_admin_note', true ) ?: get_post_meta( $req_id, '_doodh_req_admin_note', true ) ?: '',
			);
		}
		wp_reset_postdata();
	}

	wp_send_json_success( array( 'requests' => $results ) );
}
add_action( 'wp_ajax_vm_get_user_requests', 'vmtheme_ajax_get_user_requests' );
add_action( 'wp_ajax_doodh_get_user_requests', 'vmtheme_ajax_get_user_requests' );

if ( ! function_exists( 'doodhtheme_ajax_get_user_requests' ) ) {
	function doodhtheme_ajax_get_user_requests() {
		vmtheme_ajax_get_user_requests();
	}
}
