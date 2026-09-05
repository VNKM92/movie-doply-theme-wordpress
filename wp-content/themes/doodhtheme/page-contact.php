<?php
/**
 * Template Name: Contact Us Page
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle Form Submission
$contact_sent = false;
$contact_error = '';

if ( isset( $_POST['doodh_contact_submit'] ) && check_admin_referer( 'doodh_contact_nonce' ) ) {
	$c_name    = sanitize_text_field( $_POST['contact_name'] ?? '' );
	$c_email   = sanitize_email( $_POST['contact_email'] ?? '' );
	$c_subject = sanitize_text_field( $_POST['contact_subject'] ?? 'General Inquiry' );
	$c_msg     = sanitize_textarea_field( $_POST['contact_message'] ?? '' );

	if ( empty( $c_name ) || empty( $c_email ) || empty( $c_msg ) ) {
		$contact_error = __( 'Please fill in all required fields.', 'doodhtheme' );
	} elseif ( ! is_email( $c_email ) ) {
		$contact_error = __( 'Please enter a valid email address.', 'doodhtheme' );
	} else {
		$recipient = ( strpos( strtolower( $c_subject ), 'dmca' ) !== false ) 
			? doodhtheme_get_dmca_email() 
			: doodhtheme_get_support_email();

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . doodhtheme_get_brand_name() . ' <' . $recipient . '>',
			'Reply-To: ' . $c_name . ' <' . $c_email . '>',
		);

		$body = sprintf(
			"<h3>New Inquiry from %s</h3><p><strong>Name:</strong> %s</p><p><strong>Email:</strong> %s</p><p><strong>Subject:</strong> %s</p><hr><p><strong>Message:</strong></p><p>%s</p>",
			esc_html( doodhtheme_get_brand_name() ),
			esc_html( $c_name ),
			esc_html( $c_email ),
			esc_html( $c_subject ),
			nl2br( esc_html( $c_msg ) )
		);

		wp_mail( $recipient, '[' . doodhtheme_get_brand_name() . '] ' . $c_subject, $body, $headers );
		$contact_sent = true;
	}
}

get_header();
?>

<main class="container" style="padding-top: 30px; padding-bottom: 60px; min-height: 65vh; max-width: 1000px;">
	<?php
	if ( function_exists( 'doodhtheme_render_breadcrumbs' ) ) {
		doodhtheme_render_breadcrumbs();
	}
	?>

	<div style="background:var(--dt-bg-surface); padding: 40px; border-radius:var(--dt-radius); border:1px solid var(--dt-border); margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
		<header style="margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 20px;">
			<h1 style="font-size: 32px; font-weight: 800; color: #fff; margin: 0 0 10px; display:flex; align-items:center; gap:12px;">
				<i class="fas fa-paper-plane" style="color:var(--dt-primary);"></i> <?php the_title(); ?>
			</h1>
			<p style="color: #94a3b8; margin: 0; font-size: 15px;">
				<?php esc_html_e( 'Have a question, feedback, broken stream report, or copyright inquiry? Reach out to our technical support desk below.', 'doodhtheme' ); ?>
			</p>
		</header>

		<div style="display:grid; grid-template-columns: 1fr 1.3fr; gap: 35px;">
			<!-- Contact Information Sidebar -->
			<div>
				<div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); padding: 24px; border-radius: 10px; margin-bottom: 20px;">
					<h4 style="color:#fff; margin:0 0 15px; font-size:16px; display:flex; align-items:center; gap:8px;">
						<i class="fas fa-envelope-open-text" style="color:var(--dt-accent-yellow);"></i> <?php esc_html_e( 'Direct Inquiries', 'doodhtheme' ); ?>
					</h4>
					<p style="font-size:14px; color:#cbd5e1; margin:0 0 8px;">
						<strong><?php esc_html_e( 'General Support:', 'doodhtheme' ); ?></strong><br>
						<a href="mailto:<?php echo esc_attr( doodhtheme_get_support_email() ); ?>" style="color:var(--dt-primary);"><?php echo esc_html( doodhtheme_get_support_email() ); ?></a>
					</p>
					<p style="font-size:14px; color:#cbd5e1; margin:0;">
						<strong><?php esc_html_e( 'DMCA & Legal Notice:', 'doodhtheme' ); ?></strong><br>
						<a href="mailto:<?php echo esc_attr( doodhtheme_get_dmca_email() ); ?>" style="color:var(--dt-primary);"><?php echo esc_html( doodhtheme_get_dmca_email() ); ?></a>
					</p>
				</div>

				<div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); padding: 24px; border-radius: 10px;">
					<h4 style="color:#fff; margin:0 0 12px; font-size:16px; display:flex; align-items:center; gap:8px;">
						<i class="fas fa-clock" style="color:#10b981;"></i> <?php esc_html_e( 'Response Time SLA', 'doodhtheme' ); ?>
					</h4>
					<p style="font-size:13px; color:#94a3b8; line-height:1.6; margin:0;">
						<?php esc_html_e( 'Our dedicated web moderation team reviews all inquiries within 24 to 48 business hours. For copyright takedown notices, please reference the specific movie or episode URL.', 'doodhtheme' ); ?>
					</p>
				</div>
			</div>

			<!-- Interactive Contact Form -->
			<div>
				<?php if ( $contact_sent ) : ?>
					<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; padding: 20px; border-radius: 8px; font-size: 15px; margin-bottom: 20px;">
						<h4 style="margin:0 0 6px; color:#10b981;"><i class="fas fa-check-circle"></i> <?php esc_html_e( 'Thank You! Message Delivered', 'doodhtheme' ); ?></h4>
						<p style="margin:0; color:#cbd5e1;"><?php esc_html_e( 'Your inquiry has been successfully dispatched to our support team. We will get back to your email address shortly.', 'doodhtheme' ); ?></p>
					</div>
				<?php elseif ( ! empty( $contact_error ) ) : ?>
					<div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
						<i class="fas fa-exclamation-triangle"></i> <?php echo esc_html( $contact_error ); ?>
					</div>
				<?php endif; ?>

				<form method="post" action="" style="display:flex; flex-direction:column; gap:16px;">
					<?php wp_nonce_field( 'doodh_contact_nonce' ); ?>

					<div class="doodh-form-row">
						<div>
							<label style="display:block; font-size:13px; font-weight:600; color:#e2e8f0; margin-bottom:6px;"><?php esc_html_e( 'Your Full Name *', 'doodhtheme' ); ?></label>
							<input type="text" name="contact_name" class="doodh-input" placeholder="John Doe" required>
						</div>
						<div>
							<label style="display:block; font-size:13px; font-weight:600; color:#e2e8f0; margin-bottom:6px;"><?php esc_html_e( 'Email Address *', 'doodhtheme' ); ?></label>
							<input type="email" name="contact_email" class="doodh-input" placeholder="john@example.com" required>
						</div>
					</div>

					<div>
						<label style="display:block; font-size:13px; font-weight:600; color:#e2e8f0; margin-bottom:6px;"><?php esc_html_e( 'Inquiry Subject *', 'doodhtheme' ); ?></label>
						<select name="contact_subject" class="doodh-input" style="width:100%;">
							<option value="General Inquiry"><?php esc_html_e( 'General Inquiry / Question', 'doodhtheme' ); ?></option>
							<option value="Broken Video Stream Report"><?php esc_html_e( 'Report Broken Video Player / Dead Link', 'doodhtheme' ); ?></option>
							<option value="DMCA Copyright Notice"><?php esc_html_e( 'DMCA Copyright / Content Removal Request', 'doodhtheme' ); ?></option>
							<option value="Advertising & Partnership"><?php esc_html_e( 'Advertising & Partnership Opportunity', 'doodhtheme' ); ?></option>
							<option value="Feature Suggestion"><?php esc_html_e( 'Feature or Movie Title Suggestion', 'doodhtheme' ); ?></option>
						</select>
					</div>

					<div>
						<label style="display:block; font-size:13px; font-weight:600; color:#e2e8f0; margin-bottom:6px;"><?php esc_html_e( 'Message Details *', 'doodhtheme' ); ?></label>
						<textarea name="contact_message" rows="5" class="doodh-textarea" placeholder="<?php esc_attr_e( 'Describe your inquiry with as much detail as possible (e.g. Movie title, exact episode URL, browser name)...', 'doodhtheme' ); ?>" required></textarea>
					</div>

					<button type="submit" name="doodh_contact_submit" class="doodh-btn-primary" style="padding:14px 28px; width:fit-content;">
						<i class="fas fa-paper-plane"></i> <?php esc_html_e( 'Send Message', 'doodhtheme' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
