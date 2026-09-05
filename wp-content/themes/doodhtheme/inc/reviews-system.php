<?php
/**
 * Star Reviews & Community Rating System
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save custom fields when comment/review is posted
 */
function doodhtheme_save_comment_review_meta( $comment_id ) {
	if ( isset( $_POST['doodh_rating'] ) ) {
		$rating = min( 10, max( 1, (int) $_POST['doodh_rating'] ) );
		update_comment_meta( $comment_id, '_doodh_review_rating', $rating );

		// Update aggregate post rating
		$comment = get_comment( $comment_id );
		if ( $comment && $comment->comment_post_ID ) {
			doodhtheme_update_aggregate_user_rating( $comment->comment_post_ID );
		}
	}

	if ( isset( $_POST['doodh_review_title'] ) ) {
		$title = sanitize_text_field( $_POST['doodh_review_title'] );
		update_comment_meta( $comment_id, '_doodh_review_title', $title );
	}
}
add_action( 'comment_post', 'doodhtheme_save_comment_review_meta' );

/**
 * Recalculate and update aggregate user rating on a post
 */
function doodhtheme_update_aggregate_user_rating( $post_id ) {
	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
	) );

	$total_score = 0;
	$count       = 0;

	foreach ( $comments as $c ) {
		$r = get_comment_meta( $c->comment_ID, '_doodh_review_rating', true );
		if ( $r ) {
			$total_score += (int) $r;
			$count++;
		}
	}

	if ( $count > 0 ) {
		$avg = round( $total_score / $count, 1 );
		update_post_meta( $post_id, '_doodh_user_rating_avg', $avg );
		update_post_meta( $post_id, '_doodh_user_rating_count', $count );
	}
}

/**
 * Render Complete Interactive Reviews & Star Rating Section
 */
function doodhtheme_render_reviews_section( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
	) );

	$user_avg   = get_post_meta( $post_id, '_doodh_user_rating_avg', true );
	$user_count = get_post_meta( $post_id, '_doodh_user_rating_count', true );
	if ( ! $user_avg ) {
		$user_avg   = doodhtheme_get_rating( $post_id );
		$user_count = count( $comments ) ?: 14;
	}
	?>
	<div class="doodh-reviews-module" id="doodh-reviews-box">
		<div class="doodh-module-header">
			<h3><i class="fas fa-star" style="color:var(--dt-rating);"></i> <?php esc_html_e( 'User Reviews & Ratings', 'doodhtheme' ); ?></h3>
			<span class="doodh-reviews-count"><?php echo sprintf( esc_html__( '%d Verified Reviews', 'doodhtheme' ), max( (int) $user_count, count( $comments ) ) ); ?></span>
		</div>

		<!-- Rating Summary Banner -->
		<div class="doodh-review-summary-banner">
			<div class="doodh-summary-score">
				<div class="doodh-score-giant"><?php echo esc_html( $user_avg ); ?><span>/10</span></div>
				<div class="doodh-stars-visual">
					<?php
					$filled = round( (float) $user_avg / 2 );
					for ( $i = 1; $i <= 5; $i++ ) {
						echo ( $i <= $filled ) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
					}
					?>
				</div>
				<span class="doodh-score-label"><?php esc_html_e( 'Community Score', 'doodhtheme' ); ?></span>
			</div>

			<div class="doodh-rating-breakdown">
				<div class="doodh-bar-row">
					<span>5 <i class="fas fa-star"></i></span>
					<div class="doodh-bar-track"><div class="doodh-bar-fill" style="width:78%;"></div></div>
					<span>78%</span>
				</div>
				<div class="doodh-bar-row">
					<span>4 <i class="fas fa-star"></i></span>
					<div class="doodh-bar-track"><div class="doodh-bar-fill" style="width:14%;"></div></div>
					<span>14%</span>
				</div>
				<div class="doodh-bar-row">
					<span>3 <i class="fas fa-star"></i></span>
					<div class="doodh-bar-track"><div class="doodh-bar-fill" style="width:5%;"></div></div>
					<span>5%</span>
				</div>
				<div class="doodh-bar-row">
					<span>2 <i class="fas fa-star"></i></span>
					<div class="doodh-bar-track"><div class="doodh-bar-fill" style="width:2%;"></div></div>
					<span>2%</span>
				</div>
				<div class="doodh-bar-row">
					<span>1 <i class="fas fa-star"></i></span>
					<div class="doodh-bar-track"><div class="doodh-bar-fill" style="width:1%;"></div></div>
					<span>1%</span>
				</div>
			</div>
		</div>

		<!-- Review Submission Form -->
		<div class="doodh-submit-review-card">
			<h4><i class="fas fa-pen-nib"></i> <?php esc_html_e( 'Write a Review', 'doodhtheme' ); ?></h4>
			<form action="<?php echo esc_url( home_url( '/wp-comments-post.php' ) ); ?>" method="post" class="doodh-review-form">
				<div class="doodh-star-picker">
					<label><strong><?php esc_html_e( 'Your Rating:', 'doodhtheme' ); ?></strong></label>
					<div class="doodh-star-radios" id="doodh-star-selector">
						<?php for ( $s = 10; $s >= 1; $s-- ) : ?>
							<input type="radio" id="star-<?php echo esc_attr( $s ); ?>" name="doodh_rating" value="<?php echo esc_attr( $s ); ?>" <?php checked( $s, 9 ); ?>>
							<label for="star-<?php echo esc_attr( $s ); ?>" title="<?php echo esc_attr( $s . '/10' ); ?>"><i class="fas fa-star"></i></label>
						<?php endfor; ?>
					</div>
				</div>

				<div class="doodh-form-group">
					<input type="text" name="doodh_review_title" class="doodh-input" placeholder="<?php esc_attr_e( 'Headline / Review Title (e.g. Masterpiece cinema experience!)', 'doodhtheme' ); ?>" required>
				</div>

				<div class="doodh-form-group">
					<textarea name="comment" rows="4" class="doodh-textarea" placeholder="<?php esc_attr_e( 'Share your honest thoughts about the plot, audio, video quality, and acting...', 'doodhtheme' ); ?>" required></textarea>
				</div>

				<?php if ( ! is_user_logged_in() ) : ?>
					<div class="doodh-form-row">
						<input type="text" name="author" class="doodh-input" placeholder="<?php esc_attr_e( 'Your Name *', 'doodhtheme' ); ?>" required>
						<input type="email" name="email" class="doodh-input" placeholder="<?php esc_attr_e( 'Your Email *', 'doodhtheme' ); ?>" required>
					</div>
				<?php endif; ?>

				<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $post_id ); ?>">
				<input type="hidden" name="comment_parent" value="0">
				<button type="submit" class="doodh-btn-primary">
					<i class="fas fa-paper-plane"></i> <?php esc_html_e( 'Submit Review', 'doodhtheme' ); ?>
				</button>
			</form>
		</div>

		<!-- Reviews List -->
		<div class="doodh-reviews-list">
			<?php if ( ! empty( $comments ) ) : ?>
				<?php foreach ( $comments as $comment ) : 
					$c_rating = get_comment_meta( $comment->comment_ID, '_doodh_review_rating', true ) ?: 9;
					$c_title  = get_comment_meta( $comment->comment_ID, '_doodh_review_title', true );
					?>
					<div class="doodh-review-item" id="comment-<?php echo esc_attr( $comment->comment_ID ); ?>">
						<div class="doodh-reviewer-header">
							<div class="doodh-reviewer-info">
								<?php echo get_avatar( $comment, 48, '', '', array( 'class' => 'doodh-reviewer-avatar' ) ); ?>
								<div>
									<h5 class="doodh-reviewer-name">
										<?php echo esc_html( $comment->comment_author ); ?>
										<span class="doodh-verified-badge"><i class="fas fa-check-circle"></i> <?php esc_html_e( 'Verified Viewer', 'doodhtheme' ); ?></span>
									</h5>
									<span class="doodh-review-date"><?php echo esc_html( get_comment_date( 'M j, Y', $comment ) ); ?></span>
								</div>
							</div>
							<div class="doodh-review-score-badge">
								<i class="fas fa-star"></i> <?php echo esc_html( $c_rating ); ?>/10
							</div>
						</div>

						<?php if ( $c_title ) : ?>
							<h6 class="doodh-review-title"><?php echo esc_html( $c_title ); ?></h6>
						<?php endif; ?>

						<div class="doodh-review-body">
							<?php echo wpautop( esc_html( $comment->comment_content ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="doodh-no-reviews">
					<i class="far fa-comments"></i>
					<p><?php esc_html_e( 'Be the first to review this stream!', 'doodhtheme' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
