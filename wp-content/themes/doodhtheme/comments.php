<?php
/**
 * The template for displaying comments
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area" style="background:var(--dt-bg-surface); padding:25px; border-radius:var(--dt-radius); border:1px solid var(--dt-border); margin-top:30px;">
	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title" style="font-size:18px; font-weight:700; color:#fff; margin-bottom:20px;">
			<i class="fas fa-comments" style="color:var(--dt-primary);"></i> 
			<?php
			$comment_count = get_comments_number();
			printf(
				esc_html( _n( '%1$s Comment', '%1$s Comments', $comment_count, 'doodhtheme' ) ),
				number_format_i18n( $comment_count )
			);
			?>
		</h3>

		<ol class="comment-list" style="list-style:none; padding:0;">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size'=> 48,
			) );
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	comment_form( array(
		'class_submit'  => 'doodh-btn-primary',
		'title_reply'   => __( 'Leave a Review / Comment', 'doodhtheme' ),
		'title_reply_to'=> __( 'Reply to %s', 'doodhtheme' ),
	) );
	?>
</div>