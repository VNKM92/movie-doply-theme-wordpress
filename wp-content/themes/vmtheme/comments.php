<?php
/**
 * The template for displaying comments
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area doodh-comments-box">
	<?php if ( have_comments() ) : ?>
		<div class="doodh-comments-header">
			<h3 class="doodh-comments-title">
				<i class="fas fa-comments"></i> 
				<span>
					<?php
					$comment_count = get_comments_number();
					printf(
						esc_html( _n( '%1$s Discussion Comment', '%1$s Discussion Comments', $comment_count, 'vmtheme' ) ),
						number_format_i18n( $comment_count )
					);
					?>
				</span>
			</h3>
		</div>

		<ol class="comment-list doodh-comment-list-tree">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
				'callback'    => 'doodhtheme_custom_comment_format',
			) );
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<div class="doodh-comment-form-container">
		<?php if ( is_user_logged_in() ) : 
			$cur_user   = wp_get_current_user();
			$cur_avatar = function_exists( 'doodhtheme_get_user_avatar' ) ? doodhtheme_get_user_avatar( $cur_user->ID ) : get_avatar_url( $cur_user->ID );
			$cur_name   = $cur_user->display_name ?: $cur_user->user_login;
			
			comment_form( array(
				'class_container'     => 'doodh-comment-respond',
				'class_form'          => 'doodh-comment-form',
				'class_submit'        => 'doodh-btn-primary',
				'title_reply'         => '<i class="fas fa-comment-dots"></i> ' . esc_html__( 'Join the Discussion', 'vmtheme' ),
				'title_reply_to'      => '<i class="fas fa-reply"></i> ' . esc_html__( 'Reply to %s', 'vmtheme' ),
				'title_reply_before'  => '<h4 id="reply-title" class="comment-reply-title doodh-reply-title">',
				'title_reply_after'   => '</h4>',
				'cancel_reply_before' => ' <small class="doodh-cancel-reply">',
				'cancel_reply_after'  => '</small>',
				'logged_in_as'        => '<div class="doodh-logged-in-as" style="display:flex; align-items:center; gap:10px; margin-bottom:12px; font-size:13px; color:#94a3b8;"><img src="' . esc_url( $cur_avatar ) . '" width="28" height="28" style="border-radius:50%; border:2px solid var(--dt-primary); object-fit:cover;"><span>' . sprintf( esc_html__( 'Logged in as %1$s. %2$s', 'vmtheme' ), '<strong style="color:#fff;">' . esc_html( $cur_name ) . '</strong>', '<a href="' . esc_url( wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) ) . '" style="color:#ef4444; margin-left:6px;">' . esc_html__( 'Log out?', 'vmtheme' ) . '</a>' ) . '</span></div>',
				'comment_notes_before'=> '',
				'comment_notes_after' => '',
				'fields'              => array(),
				'comment_field'       => '<div class="doodh-form-group"><label class="doodh-form-label"><i class="fas fa-quote-left"></i> ' . esc_html__( 'Your Comment / Reply *', 'vmtheme' ) . '</label><textarea id="comment" name="comment" cols="45" rows="4" class="doodh-textarea" placeholder="' . esc_attr__( 'Write your response or discussion thoughts...', 'vmtheme' ) . '" aria-required="true" required></textarea></div>',
				'label_submit'        => __( 'Post Comment', 'vmtheme' ),
			) );
		else : ?>
			<div class="doodh-member-lock-wrap" style="text-align:center; padding:35px 20px; background:rgba(15,23,42,0.6); border:1px dashed rgba(255,255,255,0.12); border-radius:12px; margin-top:20px;">
				<div style="width:50px; height:50px; border-radius:50%; background:rgba(229,9,20,0.15); border:1px solid rgba(229,9,20,0.3); color:var(--dt-primary); display:inline-flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:12px;">
					<i class="fas fa-lock"></i>
				</div>
				<h4 style="font-size:17px; color:#fff; margin:0 0 6px; font-weight:700;"><?php esc_html_e( 'Sign in to join the discussion', 'vmtheme' ); ?></h4>
				<p style="color:#94a3b8; font-size:13px; max-width:400px; margin:0 auto 16px;"><?php esc_html_e( 'Only registered members can post comments and join discussions.', 'vmtheme' ); ?></p>
				<button type="button" class="doodh-btn-primary doodh-auth-trigger" style="display:inline-flex; align-items:center; gap:8px;">
					<i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Sign In to Comment', 'vmtheme' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>