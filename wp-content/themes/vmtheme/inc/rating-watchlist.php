<?php
/**
 * Interactive Star Rating System & Watchlist / Bookmarks Handler
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX Rating Vote
 */
function vmtheme_ajax_rate_post() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$vote    = isset( $_POST['rating'] ) ? floatval( $_POST['rating'] ) : 0;

	if ( ! $post_id || $vote < 1 || $vote > 10 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid vote data.', 'vmtheme' ) ) );
	}

	// Cookie check to avoid repeat voting
	$cookie_key = 'vm_voted_' . $post_id;
	$legacy_cookie = 'doodh_voted_' . $post_id;
	if ( isset( $_COOKIE[ $cookie_key ] ) || isset( $_COOKIE[ $legacy_cookie ] ) ) {
		wp_send_json_error( array( 'message' => __( 'You have already voted for this title.', 'vmtheme' ) ) );
	}

	$current_rating = (float) get_post_meta( $post_id, '_vm_rating', true );
	if ( ! $current_rating ) {
		$current_rating = (float) get_post_meta( $post_id, '_doodh_rating', true );
	}
	$current_votes  = (int) get_post_meta( $post_id, '_vm_votes', true );
	if ( ! $current_votes ) {
		$current_votes  = (int) get_post_meta( $post_id, '_doodh_votes', true );
	}

	if ( $current_votes <= 0 ) {
		$new_rating = $vote;
		$new_votes  = 1;
	} else {
		$total_score = ( $current_rating * $current_votes ) + $vote;
		$new_votes   = $current_votes + 1;
		$new_rating  = round( $total_score / $new_votes, 1 );
	}

	update_post_meta( $post_id, '_vm_rating', $new_rating );
	update_post_meta( $post_id, '_doodh_rating', $new_rating );
	update_post_meta( $post_id, '_vm_votes', $new_votes );
	update_post_meta( $post_id, '_doodh_votes', $new_votes );

	// Set cookie for 30 days
	setcookie( $cookie_key, '1', time() + ( 86400 * 30 ), '/' );
	setcookie( $legacy_cookie, '1', time() + ( 86400 * 30 ), '/' );

	wp_send_json_success( array(
		'rating'  => number_format( $new_rating, 1 ),
		'votes'   => number_format( $new_votes ),
		'message' => __( 'Thank you for your rating!', 'vmtheme' ),
	) );
}

add_action( 'wp_ajax_vmtheme_rate_post', 'vmtheme_ajax_rate_post' );
add_action( 'wp_ajax_nopriv_vmtheme_rate_post', 'vmtheme_ajax_rate_post' );
add_action( 'wp_ajax_doodhtheme_rate_post', 'vmtheme_ajax_rate_post' );
add_action( 'wp_ajax_nopriv_doodhtheme_rate_post', 'vmtheme_ajax_rate_post' );

if ( ! function_exists( 'doodhtheme_ajax_rate_post' ) ) {
	function doodhtheme_ajax_rate_post() {
		vmtheme_ajax_rate_post();
	}
}

/**
 * Render Interactive Rating Widget
 */
function vmtheme_render_rating_widget( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$rating = vmtheme_get_rating( $post_id );
	$votes  = vmtheme_get_votes( $post_id );
	$voted  = isset( $_COOKIE[ 'vm_voted_' . $post_id ] ) || isset( $_COOKIE[ 'doodh_voted_' . $post_id ] );
	?>
	<div class="doodh-rating-box" id="doodh-rating-<?php echo esc_attr( $post_id ); ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
		<div class="doodh-rating-score-circle">
			<span class="doodh-score-val" id="doodh-current-score"><?php echo esc_html( number_format( (float) $rating, 1 ) ); ?></span>
			<span class="doodh-score-max">/10</span>
		</div>
		<div class="doodh-rating-content">
			<div class="doodh-stars" data-voted="<?php echo $voted ? 'true' : 'false'; ?>">
				<?php for ( $i = 1; $i <= 10; $i++ ) : ?>
					<span class="doodh-star <?php echo ( $i <= round( (float) $rating ) ) ? 'active' : ''; ?>" data-val="<?php echo esc_attr( $i ); ?>" title="<?php printf( esc_attr__( 'Rate %d / 10', 'vmtheme' ), $i ); ?>">
						<i class="fas fa-star"></i>
					</span>
				<?php endfor; ?>
			</div>
			<div class="doodh-rating-meta">
				<span class="doodh-votes-count"><span id="doodh-current-votes"><?php echo esc_html( number_format( (int) $votes ) ); ?></span> <?php esc_html_e( 'votes', 'vmtheme' ); ?></span>
				<span class="doodh-rate-notice" id="doodh-rate-feedback"><?php echo $voted ? esc_html__( 'Your vote has been counted', 'vmtheme' ) : esc_html__( 'Click stars to rate', 'vmtheme' ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

if ( ! function_exists( 'doodhtheme_render_rating_widget' ) ) {
	function doodhtheme_render_rating_widget( $post_id = null ) {
		vmtheme_render_rating_widget( $post_id );
	}
}

/**
 * DooPlay Rating Compatibility Helpers
 */
function doo_rating_average( $post_id = null ) {
	return vmtheme_get_rating( $post_id );
}

function doo_rating_votes( $post_id = null ) {
	return vmtheme_get_votes( $post_id );
}

function doo_rating_has( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	return isset( $_COOKIE[ 'vm_voted_' . $post_id ] ) || isset( $_COOKIE[ 'doodh_voted_' . $post_id ] );
}

function doo_rating_box( $post_id = null ) {
	vmtheme_render_rating_widget( $post_id );
}

/**
 * Render Watchlist Bookmark Button
 */
function vmtheme_render_watchlist_btn( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$title  = get_the_title( $post_id );
	$poster = vmtheme_get_poster_url( $post_id, 'medium' );
	$url    = get_permalink( $post_id );
	$type   = get_post_type( $post_id );
	?>
	<button type="button" 
			class="doodh-btn-watchlist" 
			data-id="<?php echo esc_attr( $post_id ); ?>"
			data-title="<?php echo esc_attr( $title ); ?>"
			data-poster="<?php echo esc_attr( $poster ); ?>"
			data-url="<?php echo esc_url( $url ); ?>"
			data-type="<?php echo esc_attr( $type ); ?>"
			title="<?php esc_attr_e( 'Add to Watchlist', 'vmtheme' ); ?>">
		<i class="far fa-bookmark"></i> <span><?php esc_html_e( 'Watchlist', 'vmtheme' ); ?></span>
	</button>
	<?php
}

if ( ! function_exists( 'doodhtheme_render_watchlist_btn' ) ) {
	function doodhtheme_render_watchlist_btn( $post_id = null ) {
		vmtheme_render_watchlist_btn( $post_id );
	}
}

/**
 * Retrieve User Watchlist Post IDs
 *
 * @param int|null $user_id User ID
 * @return array Array of Post IDs
 */
function vmtheme_get_user_watchlist_ids( $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return array();
	}
	$list = get_user_meta( $user_id, '_vm_user_watchlist', true );
	if ( empty( $list ) || ! is_array( $list ) ) {
		$list = get_user_meta( $user_id, '_doodh_user_watchlist', true );
	}
	return is_array( $list ) ? array_map( 'intval', $list ) : array();
}

if ( ! function_exists( 'doodhtheme_get_user_watchlist_ids' ) ) {
	function doodhtheme_get_user_watchlist_ids( $user_id = null ) {
		return vmtheme_get_user_watchlist_ids( $user_id );
	}
}

/**
 * Handle AJAX Toggle Watchlist for Logged-In Users
 */
function vmtheme_ajax_toggle_watchlist() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	if ( ! $post_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'vmtheme' ) ) );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_success( array(
			'is_logged_in' => false,
			'message'      => __( 'Updated in local storage.', 'vmtheme' ),
		) );
	}

	$user_id = get_current_user_id();
	$list    = vmtheme_get_user_watchlist_ids( $user_id );
	$idx     = array_search( $post_id, $list, true );

	if ( false !== $idx ) {
		unset( $list[ $idx ] );
		$in_watchlist = false;
		$msg          = __( 'Removed from your Watchlist.', 'vmtheme' );
	} else {
		array_unshift( $list, $post_id );
		$in_watchlist = true;
		$msg          = __( 'Added to your Watchlist!', 'vmtheme' );
	}

	$list = array_values( array_unique( $list ) );
	update_user_meta( $user_id, '_vm_user_watchlist', $list );
	update_user_meta( $user_id, '_doodh_user_watchlist', $list );

	wp_send_json_success( array(
		'is_logged_in' => true,
		'in_watchlist' => $in_watchlist,
		'count'        => count( $list ),
		'message'      => $msg,
	) );
}

add_action( 'wp_ajax_vm_toggle_watchlist', 'vmtheme_ajax_toggle_watchlist' );
add_action( 'wp_ajax_nopriv_vm_toggle_watchlist', 'vmtheme_ajax_toggle_watchlist' );
add_action( 'wp_ajax_doodh_toggle_watchlist', 'vmtheme_ajax_toggle_watchlist' );
add_action( 'wp_ajax_nopriv_doodh_toggle_watchlist', 'vmtheme_ajax_toggle_watchlist' );

if ( ! function_exists( 'doodhtheme_ajax_toggle_watchlist' ) ) {
	function doodhtheme_ajax_toggle_watchlist() {
		vmtheme_ajax_toggle_watchlist();
	}
}

/**
 * Handle AJAX Merge Client LocalStorage Watchlist into User Meta upon Login
 */
function vmtheme_ajax_merge_watchlist() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error();
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error();
	}

	$user_id      = get_current_user_id();
	$client_items = isset( $_POST['client_items'] ) && is_array( $_POST['client_items'] ) ? array_map( 'intval', $_POST['client_items'] ) : array();
	$server_list  = vmtheme_get_user_watchlist_ids( $user_id );

	$merged = array_values( array_unique( array_merge( $server_list, $client_items ) ) );
	update_user_meta( $user_id, '_vm_user_watchlist', $merged );
	update_user_meta( $user_id, '_doodh_user_watchlist', $merged );

	// Build full item objects to return
	$items_data = array();
	foreach ( $merged as $pid ) {
		if ( get_post_status( $pid ) === 'publish' ) {
			$items_data[] = array(
				'id'     => $pid,
				'title'  => get_the_title( $pid ),
				'poster' => vmtheme_get_poster_url( $pid, 'medium' ),
				'url'    => get_permalink( $pid ),
				'type'   => get_post_type( $pid ),
				'year'   => vmtheme_get_release_year( $pid ),
				'rating' => vmtheme_get_rating( $pid ),
			);
		}
	}

	wp_send_json_success( array(
		'merged_count' => count( $merged ),
		'items'        => $items_data,
	) );
}

add_action( 'wp_ajax_vm_merge_watchlist', 'vmtheme_ajax_merge_watchlist' );
add_action( 'wp_ajax_doodh_merge_watchlist', 'vmtheme_ajax_merge_watchlist' );

if ( ! function_exists( 'doodhtheme_ajax_merge_watchlist' ) ) {
	function doodhtheme_ajax_merge_watchlist() {
		vmtheme_ajax_merge_watchlist();
	}
}

