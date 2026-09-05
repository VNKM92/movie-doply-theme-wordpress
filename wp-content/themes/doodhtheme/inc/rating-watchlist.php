<?php
/**
 * Interactive Star Rating System & Watchlist / Bookmarks Handler
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX Rating Vote
 */
function doodhtheme_ajax_rate_post() {
	check_ajax_referer( 'doodhtheme_nonce', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$vote    = isset( $_POST['rating'] ) ? floatval( $_POST['rating'] ) : 0;

	if ( ! $post_id || $vote < 1 || $vote > 10 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid vote data.', 'doodhtheme' ) ) );
	}

	// Cookie check to avoid repeat voting
	$cookie_key = 'doodh_voted_' . $post_id;
	if ( isset( $_COOKIE[ $cookie_key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'You have already voted for this title.', 'doodhtheme' ) ) );
	}

	$current_rating = (float) get_post_meta( $post_id, '_doodh_rating', true );
	$current_votes  = (int) get_post_meta( $post_id, '_doodh_votes', true );

	if ( $current_votes <= 0 ) {
		$new_rating = $vote;
		$new_votes  = 1;
	} else {
		$total_score = ( $current_rating * $current_votes ) + $vote;
		$new_votes   = $current_votes + 1;
		$new_rating  = round( $total_score / $new_votes, 1 );
	}

	update_post_meta( $post_id, '_doodh_rating', $new_rating );
	update_post_meta( $post_id, '_doodh_votes', $new_votes );

	// Set cookie for 30 days
	setcookie( $cookie_key, '1', time() + ( 86400 * 30 ), '/' );

	wp_send_json_success( array(
		'rating'  => number_format( $new_rating, 1 ),
		'votes'   => number_format( $new_votes ),
		'message' => __( 'Thank you for your rating!', 'doodhtheme' ),
	) );
}
add_action( 'wp_ajax_doodhtheme_rate_post', 'doodhtheme_ajax_rate_post' );
add_action( 'wp_ajax_nopriv_doodhtheme_rate_post', 'doodhtheme_ajax_rate_post' );

/**
 * Render Interactive Rating Widget
 */
function doodhtheme_render_rating_widget( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$rating = doodhtheme_get_rating( $post_id );
	$votes  = doodhtheme_get_votes( $post_id );
	$voted  = isset( $_COOKIE[ 'doodh_voted_' . $post_id ] );
	?>
	<div class="doodh-rating-box" id="doodh-rating-<?php echo esc_attr( $post_id ); ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
		<div class="doodh-rating-score-circle">
			<span class="doodh-score-val" id="doodh-current-score"><?php echo esc_html( number_format( (float) $rating, 1 ) ); ?></span>
			<span class="doodh-score-max">/10</span>
		</div>
		<div class="doodh-rating-content">
			<div class="doodh-stars" data-voted="<?php echo $voted ? 'true' : 'false'; ?>">
				<?php for ( $i = 1; $i <= 10; $i++ ) : ?>
					<span class="doodh-star <?php echo ( $i <= round( (float) $rating ) ) ? 'active' : ''; ?>" data-val="<?php echo esc_attr( $i ); ?>" title="<?php printf( esc_attr__( 'Rate %d / 10', 'doodhtheme' ), $i ); ?>">
						<i class="fas fa-star"></i>
					</span>
				<?php endfor; ?>
			</div>
			<div class="doodh-rating-meta">
				<span class="doodh-votes-count"><span id="doodh-current-votes"><?php echo esc_html( number_format( (int) $votes ) ); ?></span> <?php esc_html_e( 'votes', 'doodhtheme' ); ?></span>
				<span class="doodh-rate-notice" id="doodh-rate-feedback"><?php echo $voted ? esc_html__( 'Your vote has been counted', 'doodhtheme' ) : esc_html__( 'Click stars to rate', 'doodhtheme' ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

/**
 * DooPlay Rating Compatibility Helpers
 */
function doo_rating_average( $post_id = null ) {
	return doodhtheme_get_rating( $post_id );
}

function doo_rating_votes( $post_id = null ) {
	return doodhtheme_get_votes( $post_id );
}

function doo_rating_has( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	return isset( $_COOKIE[ 'doodh_voted_' . $post_id ] );
}

function doo_rating_box( $post_id = null ) {
	doodhtheme_render_rating_widget( $post_id );
}

/**
 * Render Watchlist Bookmark Button
 */
function doodhtheme_render_watchlist_btn( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$title  = get_the_title( $post_id );
	$poster = doodhtheme_get_poster_url( $post_id, 'medium' );
	$url    = get_permalink( $post_id );
	$type   = get_post_type( $post_id );
	?>
	<button type="button" 
			class="doodh-btn-watchlist" 
			data-id="<?php echo esc_attr( $post_id ); ?>"
			data-title="<?php echo esc_attr( $title ); ?>"
			data-poster="<?php echo esc_attr( $poster ); ?>"
			data-url="<?php echo esc_attr( $url ); ?>"
			data-type="<?php echo esc_attr( $type ); ?>"
			title="<?php esc_attr_e( 'Add to Watchlist', 'doodhtheme' ); ?>">
		<i class="far fa-bookmark"></i> <span><?php esc_html_e( 'Watchlist', 'doodhtheme' ); ?></span>
	</button>
	<?php
}
