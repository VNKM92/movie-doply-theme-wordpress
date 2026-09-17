<?php
/**
 * User Authentication, Profile Management, and Auth Modal System
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get User Avatar URL (supports custom avatar meta with fallback to default)
 *
 * @param int|null $user_id User ID
 * @return string Avatar URL
 */
function vmtheme_get_user_avatar( $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return vmtheme_get_fallback_avatar_url();
	}

	$custom_avatar = get_user_meta( $user_id, '_vm_user_avatar', true );
	if ( empty( $custom_avatar ) ) {
		$custom_avatar = get_user_meta( $user_id, '_doodh_user_avatar', true );
	}
	if ( ! empty( $custom_avatar ) ) {
		return esc_url_raw( $custom_avatar );
	}

	$user_email = get_the_author_meta( 'user_email', $user_id );
	if ( ! empty( $user_email ) ) {
		$gravatar = get_avatar_url( $user_email, array( 'size' => 120, 'default' => '404' ) );
		if ( ! empty( $gravatar ) && strpos( $gravatar, '404' ) === false ) {
			return $gravatar;
		}
	}

	$username = get_the_author_meta( 'user_login', $user_id ) ?: 'User';
	return 'https://api.dicebear.com/9.x/adventurer/svg?seed=' . rawurlencode( $username );
}

if ( ! function_exists( 'doodhtheme_get_user_avatar' ) ) {
	function doodhtheme_get_user_avatar( $user_id = null ) {
		return vmtheme_get_user_avatar( $user_id );
	}
}

/**
 * Set User Custom Avatar URL
 *
 * @param int $user_id User ID
 * @param string $avatar_url Avatar URL
 * @return bool
 */
function vmtheme_set_user_avatar( $user_id, $avatar_url ) {
	if ( ! $user_id ) {
		return false;
	}
	update_user_meta( $user_id, '_doodh_user_avatar', esc_url_raw( $avatar_url ) );
	return (bool) update_user_meta( $user_id, '_vm_user_avatar', esc_url_raw( $avatar_url ) );
}

if ( ! function_exists( 'doodhtheme_set_user_avatar' ) ) {
	function doodhtheme_set_user_avatar( $user_id, $avatar_url ) {
		return vmtheme_set_user_avatar( $user_id, $avatar_url );
	}
}

/**
 * Handle AJAX User Registration
 */
function vmtheme_ajax_register() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	$username = isset( $_POST['username'] ) ? sanitize_user( trim( $_POST['username'] ) ) : '';
	$email    = isset( $_POST['email'] ) ? sanitize_email( trim( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? trim( $_POST['password'] ) : '';
	$avatar   = isset( $_POST['avatar'] ) ? esc_url_raw( trim( $_POST['avatar'] ) ) : '';

	if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'vmtheme' ) ) );
	}

	if ( ! validate_username( $username ) || strlen( $username ) < 3 ) {
		wp_send_json_error( array( 'message' => __( 'Username must be at least 3 characters and contain valid letters or numbers.', 'vmtheme' ) ) );
	}

	if ( username_exists( $username ) ) {
		wp_send_json_error( array( 'message' => __( 'This username is already taken. Please choose another one.', 'vmtheme' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'vmtheme' ) ) );
	}

	if ( email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'An account with this email already exists. Please sign in.', 'vmtheme' ) ) );
	}

	if ( strlen( $password ) < 6 ) {
		wp_send_json_error( array( 'message' => __( 'Password must be at least 6 characters long.', 'vmtheme' ) ) );
	}

	$user_id = wp_create_user( $username, $password, $email );
	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
	}

	// Assign Avatar (default to 3D avatar if empty)
	if ( empty( $avatar ) ) {
		$avatar = 'https://api.dicebear.com/9.x/adventurer/svg?seed=' . rawurlencode( $username );
	}
	vmtheme_set_user_avatar( $user_id, $avatar );

	// Auto-login the registered user
	wp_set_current_user( $user_id, $username );
	wp_set_auth_cookie( $user_id, true );
	do_action( 'wp_login', $username, get_user_by( 'id', $user_id ) );

	wp_send_json_success( array(
		'message'      => __( 'Account created successfully! Welcome to the community.', 'vmtheme' ),
		'user_id'      => $user_id,
		'username'     => $username,
		'display_name' => $username,
		'avatar'       => $avatar,
		'redirect_url' => home_url( '/' ),
	) );
}

add_action( 'wp_ajax_nopriv_vm_register', 'vmtheme_ajax_register' );
add_action( 'wp_ajax_nopriv_doodh_register', 'vmtheme_ajax_register' );

if ( ! function_exists( 'doodhtheme_ajax_register' ) ) {
	function doodhtheme_ajax_register() {
		vmtheme_ajax_register();
	}
}

/**
 * Handle AJAX User Login
 */
function vmtheme_ajax_login() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	$user_login = isset( $_POST['log'] ) ? sanitize_text_field( trim( $_POST['log'] ) ) : '';
	$user_pass  = isset( $_POST['pwd'] ) ? trim( $_POST['pwd'] ) : '';
	$remember   = ! empty( $_POST['rememberme'] );

	if ( empty( $user_login ) || empty( $user_pass ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter your username and password.', 'vmtheme' ) ) );
	}

	$credentials = array(
		'user_login'    => $user_login,
		'user_password' => $user_pass,
		'remember'      => $remember,
	);

	$user = wp_signon( $credentials, is_ssl() );

	if ( is_wp_error( $user ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid username or password. Please try again.', 'vmtheme' ) ) );
	}

	$avatar = vmtheme_get_user_avatar( $user->ID );

	wp_send_json_success( array(
		'message'      => sprintf( __( 'Welcome back, %s!', 'vmtheme' ), esc_html( $user->display_name ?: $user->user_login ) ),
		'user_id'      => $user->ID,
		'username'     => $user->user_login,
		'display_name' => $user->display_name ?: $user->user_login,
		'avatar'       => $avatar,
		'is_admin'     => user_can( $user, 'manage_options' ),
	) );
}

add_action( 'wp_ajax_nopriv_vm_login', 'vmtheme_ajax_login' );
add_action( 'wp_ajax_nopriv_doodh_login', 'vmtheme_ajax_login' );

if ( ! function_exists( 'doodhtheme_ajax_login' ) ) {
	function doodhtheme_ajax_login() {
		vmtheme_ajax_login();
	}
}

/**
 * Handle AJAX Password Reset Request
 */
function vmtheme_ajax_forgot_password() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	$user_input = isset( $_POST['user_login'] ) ? sanitize_text_field( trim( $_POST['user_login'] ) ) : '';

	if ( empty( $user_input ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter your username or email address.', 'vmtheme' ) ) );
	}

	$user_data = is_email( $user_input ) ? get_user_by( 'email', $user_input ) : get_user_by( 'login', $user_input );

	if ( ! $user_data ) {
		wp_send_json_error( array( 'message' => __( 'No account found with that username or email.', 'vmtheme' ) ) );
	}

	$reset_key = get_password_reset_key( $user_data );
	if ( is_wp_error( $reset_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Unable to process password reset. Please contact site administrator.', 'vmtheme' ) ) );
	}

	$site_name   = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$reset_url   = network_site_url( "wp-login.php?action=rp&key={$reset_key}&login=" . rawurlencode( $user_data->user_login ), 'login' );
	$message     = sprintf( __( 'Someone has requested a password reset for the following account on %s:', 'vmtheme' ), $site_name ) . "\r\n\r\n";
	$message    .= sprintf( __( 'Username: %s', 'vmtheme' ), $user_data->user_login ) . "\r\n\r\n";
	$message    .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'vmtheme' ) . "\r\n\r\n";
	$message    .= __( 'To reset your password, visit the following address:', 'vmtheme' ) . "\r\n\r\n";
	$message    .= $reset_url . "\r\n";

	$subject = sprintf( __( '[%s] Password Reset', 'vmtheme' ), $site_name );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( wp_mail( $user_data->user_email, $subject, $message, $headers ) ) {
		wp_send_json_success( array( 'message' => __( 'Password reset instructions have been sent to your email address.', 'vmtheme' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'The email could not be sent. Please check your server email configuration.', 'vmtheme' ) ) );
	}
}

add_action( 'wp_ajax_nopriv_vm_forgot_password', 'vmtheme_ajax_forgot_password' );
add_action( 'wp_ajax_nopriv_doodh_forgot_password', 'vmtheme_ajax_forgot_password' );

if ( ! function_exists( 'doodhtheme_ajax_forgot_password' ) ) {
	function doodhtheme_ajax_forgot_password() {
		vmtheme_ajax_forgot_password();
	}
}

/**
 * Handle AJAX Profile & Avatar Update
 */
function vmtheme_ajax_update_profile() {
	if ( ! check_ajax_referer( 'vmtheme_nonce', 'nonce', false ) && ! check_ajax_referer( 'doodhtheme_nonce', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'vmtheme' ) ) );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'You must be logged in to update your profile.', 'vmtheme' ) ) );
	}

	$user_id      = get_current_user_id();
	$display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( trim( $_POST['display_name'] ) ) : '';
	$avatar_url   = isset( $_POST['avatar'] ) ? esc_url_raw( trim( $_POST['avatar'] ) ) : '';
	$email        = isset( $_POST['email'] ) ? sanitize_email( trim( $_POST['email'] ) ) : '';

	$update_data = array( 'ID' => $user_id );
	if ( ! empty( $display_name ) ) {
		$update_data['display_name'] = $display_name;
	}
	if ( ! empty( $email ) && is_email( $email ) ) {
		$existing = email_exists( $email );
		if ( $existing && $existing !== $user_id ) {
			wp_send_json_error( array( 'message' => __( 'This email is already in use by another account.', 'vmtheme' ) ) );
		}
		$update_data['user_email'] = $email;
	}

	wp_update_user( $update_data );

	if ( ! empty( $avatar_url ) ) {
		vmtheme_set_user_avatar( $user_id, $avatar_url );
	}

	wp_send_json_success( array(
		'message'      => __( 'Profile updated successfully!', 'vmtheme' ),
		'display_name' => $display_name ?: wp_get_current_user()->display_name,
		'avatar'       => vmtheme_get_user_avatar( $user_id ),
	) );
}

add_action( 'wp_ajax_vm_update_profile', 'vmtheme_ajax_update_profile' );
add_action( 'wp_ajax_doodh_update_profile', 'vmtheme_ajax_update_profile' );

if ( ! function_exists( 'doodhtheme_ajax_update_profile' ) ) {
	function doodhtheme_ajax_update_profile() {
		vmtheme_ajax_update_profile();
	}
}

/**
 * Render Frontend Interactive Authentication Modal
 */
/**
 * Render Frontend Interactive Authentication Modal
 */
function vmtheme_render_auth_modal() {
	$preset_avatars = function_exists( 'vmtheme_get_preset_modern_avatars' ) ? vmtheme_get_preset_modern_avatars() : ( function_exists( 'doodhtheme_get_preset_modern_avatars' ) ? doodhtheme_get_preset_modern_avatars() : array(
		array( 'name' => 'Felix (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Felix' ),
		array( 'name' => 'Aneka (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Aneka' ),
		array( 'name' => 'Oliver (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Oliver' ),
		array( 'name' => 'Zoe (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Zoe' ),
		array( 'name' => 'Leo (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Leo' ),
		array( 'name' => 'Maya (3D Adventurer)', 'cat' => '3d', 'url' => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Maya' ),
		array( 'name' => 'Cinephile Critic', 'cat' => 'critics', 'url' => 'https://api.dicebear.com/9.x/personas/svg?seed=Cinephile' ),
		array( 'name' => 'Alexander', 'cat' => 'illustrated', 'url' => 'https://api.dicebear.com/9.x/avataaars/svg?seed=Alexander' ),
		array( 'name' => 'Popcorn Bot', 'cat' => 'notion', 'url' => 'https://api.dicebear.com/9.x/bottts/svg?seed=Popcorn' ),
	) );
	?>
	<div class="doodh-auth-modal-overlay" id="doodh-auth-modal" style="display:none;">
		<div class="doodh-auth-modal-dialog">
			<button type="button" class="doodh-auth-modal-close" id="doodh-auth-modal-close" aria-label="<?php esc_attr_e( 'Close Modal', 'vmtheme' ); ?>">&times;</button>

			<!-- Header Tabs -->
			<div class="doodh-auth-modal-header">
				<div class="doodh-auth-tabs">
					<button type="button" class="doodh-auth-tab-btn active" data-auth-tab="login">
						<i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Sign In', 'vmtheme' ); ?>
					</button>
					<button type="button" class="doodh-auth-tab-btn" data-auth-tab="register">
						<i class="fas fa-user-plus"></i> <?php esc_html_e( 'Create Account', 'vmtheme' ); ?>
					</button>
				</div>
			</div>

			<div class="doodh-auth-modal-body">
				<!-- Feedback Notice Box -->
				<div class="doodh-auth-alert" id="doodh-auth-feedback" style="display:none;"></div>

				<!-- TAB 1: LOGIN FORM -->
				<div class="doodh-auth-tab-pane active" id="doodh-tab-login">
					<div class="doodh-auth-intro">
						<h3><?php esc_html_e( 'Welcome Back!', 'vmtheme' ); ?></h3>
						<p><?php esc_html_e( 'Sign in to access your saved watchlist, post reviews & request movies.', 'vmtheme' ); ?></p>
					</div>

					<form id="doodh-login-form" class="doodh-auth-form" method="post">
						<div class="doodh-form-group">
							<label class="doodh-form-label"><i class="fas fa-user"></i> <?php esc_html_e( 'Username or Email', 'vmtheme' ); ?></label>
							<input type="text" name="log" class="doodh-input" placeholder="<?php esc_attr_e( 'e.g. moviebuff99', 'vmtheme' ); ?>" required autocomplete="username">
						</div>

						<div class="doodh-form-group">
							<div style="display:flex; justify-content:space-between; align-items:center;">
								<label class="doodh-form-label"><i class="fas fa-lock"></i> <?php esc_html_e( 'Password', 'vmtheme' ); ?></label>
								<a href="#" class="doodh-forgot-link" id="doodh-trigger-forgot"><?php esc_html_e( 'Forgot password?', 'vmtheme' ); ?></a>
							</div>
							<div class="doodh-input-pw-wrap">
								<input type="password" name="pwd" class="doodh-input" placeholder="••••••••" required autocomplete="current-password">
								<button type="button" class="doodh-pw-toggle"><i class="far fa-eye"></i></button>
							</div>
						</div>

						<div class="doodh-form-group doodh-form-checkbox">
							<label>
								<input type="checkbox" name="rememberme" value="forever" checked>
								<span><?php esc_html_e( 'Keep me signed in', 'vmtheme' ); ?></span>
							</label>
						</div>

						<button type="submit" class="doodh-btn-primary doodh-btn-full doodh-auth-submit-btn">
							<i class="fas fa-sign-in-alt"></i> <span><?php esc_html_e( 'Sign In', 'vmtheme' ); ?></span>
						</button>
					</form>
				</div>

				<!-- TAB 2: REGISTRATION FORM -->
				<div class="doodh-auth-tab-pane" id="doodh-tab-register">
					<div class="doodh-auth-intro">
						<h3><?php esc_html_e( 'Join the Community', 'vmtheme' ); ?></h3>
						<p><?php esc_html_e( 'Create your free account to unlock member reviews, requests, and cloud watchlist.', 'vmtheme' ); ?></p>
					</div>

					<form id="doodh-register-form" class="doodh-auth-form" method="post">
						<!-- Avatar Picker Preview Row -->
						<div class="doodh-reg-avatar-section">
							<label class="doodh-form-label"><i class="fas fa-smile"></i> <?php esc_html_e( 'Choose Your Avatar', 'vmtheme' ); ?></label>
							<div class="doodh-reg-avatar-picker">
								<div class="doodh-reg-avatar-preview">
									<img src="https://api.dicebear.com/9.x/adventurer/svg?seed=Felix" id="doodh-reg-avatar-preview-img" alt="Avatar" width="50" height="50">
								</div>
								<div class="doodh-reg-avatar-options">
									<?php foreach ( array_slice( $preset_avatars, 0, 8 ) as $p_av ) : ?>
										<button type="button" class="doodh-reg-av-thumb <?php echo $p_av['name'] === 'Felix (3D Adventurer)' ? 'selected' : ''; ?>" data-avatar="<?php echo esc_url( $p_av['url'] ); ?>" title="<?php echo esc_attr( $p_av['name'] ); ?>">
											<img src="<?php echo esc_url( $p_av['url'] ); ?>" alt="Avatar Preset" width="34" height="34" loading="lazy">
										</button>
									<?php endforeach; ?>
									<button type="button" class="doodh-reg-av-random" id="doodh-reg-random-avatar" title="<?php esc_attr_e( 'Randomize Avatar', 'vmtheme' ); ?>">
										<i class="fas fa-dice"></i>
									</button>
								</div>
								<input type="hidden" name="avatar" id="doodh-reg-avatar-input" value="https://api.dicebear.com/9.x/adventurer/svg?seed=Felix">
							</div>
						</div>

						<div class="doodh-form-group">
							<label class="doodh-form-label"><i class="fas fa-user"></i> <?php esc_html_e( 'Username *', 'vmtheme' ); ?></label>
							<input type="text" name="username" class="doodh-input" placeholder="<?php esc_attr_e( 'e.g. cinemafan22', 'vmtheme' ); ?>" required autocomplete="username">
						</div>

						<div class="doodh-form-group">
							<label class="doodh-form-label"><i class="fas fa-envelope"></i> <?php esc_html_e( 'Email Address *', 'vmtheme' ); ?></label>
							<input type="email" name="email" class="doodh-input" placeholder="<?php esc_attr_e( 'e.g. user@example.com', 'vmtheme' ); ?>" required autocomplete="email">
						</div>

						<div class="doodh-form-group">
							<label class="doodh-form-label"><i class="fas fa-lock"></i> <?php esc_html_e( 'Create Password * (min 6 chars)', 'vmtheme' ); ?></label>
							<div class="doodh-input-pw-wrap">
								<input type="password" name="password" class="doodh-input" placeholder="••••••••" required minlength="6" autocomplete="new-password">
								<button type="button" class="doodh-pw-toggle"><i class="far fa-eye"></i></button>
							</div>
						</div>

						<button type="submit" class="doodh-btn-primary doodh-btn-full doodh-auth-submit-btn">
							<i class="fas fa-user-plus"></i> <span><?php esc_html_e( 'Create Free Account', 'vmtheme' ); ?></span>
						</button>
					</form>
				</div>

				<!-- TAB 3: FORGOT PASSWORD FORM -->
				<div class="doodh-auth-tab-pane" id="doodh-tab-forgot">
					<div class="doodh-auth-intro">
						<h3><?php esc_html_e( 'Reset Password', 'vmtheme' ); ?></h3>
						<p><?php esc_html_e( 'Enter your username or email and we will send you instructions to reset your password.', 'vmtheme' ); ?></p>
					</div>

					<form id="doodh-forgot-form" class="doodh-auth-form" method="post">
						<div class="doodh-form-group">
							<label class="doodh-form-label"><i class="fas fa-envelope"></i> <?php esc_html_e( 'Username or Email Address', 'vmtheme' ); ?></label>
							<input type="text" name="user_login" class="doodh-input" placeholder="<?php esc_attr_e( 'e.g. user@example.com', 'vmtheme' ); ?>" required>
						</div>

						<button type="submit" class="doodh-btn-primary doodh-btn-full doodh-auth-submit-btn">
							<i class="fas fa-paper-plane"></i> <span><?php esc_html_e( 'Send Reset Instructions', 'vmtheme' ); ?></span>
						</button>

						<div style="text-align:center; margin-top:14px;">
							<a href="#" class="doodh-back-login-link" id="doodh-back-to-login"><i class="fas fa-arrow-left"></i> <?php esc_html_e( 'Back to Sign In', 'vmtheme' ); ?></a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
}

if ( ! function_exists( 'doodhtheme_render_auth_modal' ) ) {
	function doodhtheme_render_auth_modal() {
		vmtheme_render_auth_modal();
	}
}

