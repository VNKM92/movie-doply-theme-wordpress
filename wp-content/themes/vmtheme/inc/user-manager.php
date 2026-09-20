<?php
/**
 * Advanced User Manager & Brand/Logo Management Suite
 *
 * Provides a comprehensive WP Admin suite for:
 * 1. User Management (CRUD, Avatar upload/presets, Soft-Delete/Restore, Hard-Delete, Role controls, Activity metrics).
 * 2. Logo & Brand Management (Header logo, independent Footer logo, dynamic tagline, footer about content, badges, social links).
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register User & Brand Manager Admin Menus
 */
function vmtheme_register_user_manager_menu() {
	// Top-level or sub-menu under Users and Theme
	add_menu_page(
		__( 'User & Brand Manager', 'vmtheme' ),
		__( 'User Manager', 'vmtheme' ),
		'manage_options',
		'vmtheme-user-manager',
		'vmtheme_render_user_manager_dashboard',
		'dashicons-admin-users',
		59
	);

	add_submenu_page(
		'vmtheme-user-manager',
		__( 'Manage Users', 'vmtheme' ),
		__( 'All Users', 'vmtheme' ),
		'manage_options',
		'vmtheme-user-manager',
		'vmtheme_render_user_manager_dashboard'
	);

	add_submenu_page(
		'vmtheme-user-manager',
		__( 'Logo & Brand Settings', 'vmtheme' ),
		__( 'Logo & Brand Manage', 'vmtheme' ),
		'manage_options',
		'vmtheme-user-manager&tab=logo_brand',
		'vmtheme_render_user_manager_dashboard'
	);
}
add_action( 'admin_menu', 'vmtheme_register_user_manager_menu' );

/**
 * Enqueue Media Library and Admin Scripts for User Manager
 */
function vmtheme_admin_user_manager_scripts( $hook ) {
	if ( strpos( $hook, 'vmtheme-user-manager' ) !== false || strpos( $hook, 'vmtheme-brand-settings' ) !== false ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'vmtheme_admin_user_manager_scripts' );

/**
 * Intercept Login for Soft-Deleted (Deactivated) Users
 */
function vmtheme_check_soft_deleted_user( $user, $username, $password ) {
	if ( is_a( $user, 'WP_User' ) ) {
		$status = get_user_meta( $user->ID, '_vmtheme_user_status', true );
		if ( $status === 'soft_deleted' ) {
			return new WP_Error(
				'account_deactivated',
				__( '<strong>Account Deactivated:</strong> Your account has been temporarily disabled or moved to trash by an administrator.', 'vmtheme' )
			);
		}
	}
	return $user;
}
add_filter( 'authenticate', 'vmtheme_check_soft_deleted_user', 30, 3 );

/**
 * Handle User Action Handlers (Soft Delete, Restore, Hard Delete, Save User, Save Logo/Brand)
 */
function vmtheme_handle_user_manager_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// 1. Soft Delete User
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'soft_delete' && isset( $_GET['user_id'] ) ) {
		check_admin_referer( 'vmtheme_user_action_' . $_GET['user_id'] );
		$target_id = (int) $_GET['user_id'];
		if ( $target_id !== get_current_user_id() ) {
			update_user_meta( $target_id, '_vmtheme_user_status', 'soft_deleted' );
			update_user_meta( $target_id, '_vmtheme_soft_deleted_at', time() );
			wp_safe_redirect( admin_url( 'admin.php?page=vmtheme-user-manager&msg=soft_deleted' ) );
			exit;
		}
	}

	// 2. Restore User
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'restore' && isset( $_GET['user_id'] ) ) {
		check_admin_referer( 'vmtheme_user_action_' . $_GET['user_id'] );
		$target_id = (int) $_GET['user_id'];
		delete_user_meta( $target_id, '_vmtheme_user_status' );
		delete_user_meta( $target_id, '_vmtheme_soft_deleted_at' );
		wp_safe_redirect( admin_url( 'admin.php?page=vmtheme-user-manager&tab=trashed&msg=restored' ) );
		exit;
	}

	// 3. Hard Delete User
	if ( isset( $_POST['vmtheme_hard_delete_user'] ) ) {
		check_admin_referer( 'vmtheme_hard_delete_nonce' );
		$target_id = (int) $_POST['target_user_id'];
		if ( $target_id && $target_id !== get_current_user_id() ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			$reassign = ! empty( $_POST['reassign_user'] ) ? (int) $_POST['reassign_user'] : null;
			wp_delete_user( $target_id, $reassign );
			wp_safe_redirect( admin_url( 'admin.php?page=vmtheme-user-manager&msg=hard_deleted' ) );
			exit;
		}
	}

	// 4. Save / Edit User Form
	if ( isset( $_POST['vmtheme_save_user'] ) ) {
		check_admin_referer( 'vmtheme_save_user_nonce' );
		$user_id      = ! empty( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
		$email        = sanitize_email( $_POST['user_email'] ?? '' );
		$display_name = sanitize_text_field( $_POST['display_name'] ?? '' );
		$role         = sanitize_key( $_POST['role'] ?? 'subscriber' );
		$avatar       = esc_url_raw( $_POST['user_avatar'] ?? '' );
		$bio          = sanitize_textarea_field( $_POST['description'] ?? '' );
		$password     = trim( $_POST['user_pass'] ?? '' );

		if ( $user_id > 0 ) {
			// Update Existing User
			$userdata = array(
				'ID'           => $user_id,
				'user_email'   => $email,
				'display_name' => $display_name,
				'role'         => $role,
				'description'  => $bio,
			);
			if ( ! empty( $password ) ) {
				$userdata['user_pass'] = $password;
			}
			wp_update_user( $userdata );
			update_user_meta( $user_id, '_vm_user_avatar', $avatar );
			update_user_meta( $user_id, '_doodh_user_avatar', $avatar );

			wp_safe_redirect( admin_url( 'admin.php?page=vmtheme-user-manager&msg=user_updated' ) );
			exit;
		} else {
			// Create New User
			$username = sanitize_user( $_POST['user_login'] ?? '' );
			if ( ! username_exists( $username ) && ! email_exists( $email ) && ! empty( $password ) ) {
				$new_id = wp_create_user( $username, $password, $email );
				if ( ! is_wp_error( $new_id ) ) {
					wp_update_user( array(
						'ID'           => $new_id,
						'display_name' => $display_name ?: $username,
						'role'         => $role,
						'description'  => $bio,
					) );
					update_user_meta( $new_id, '_vm_user_avatar', $avatar );
					update_user_meta( $new_id, '_doodh_user_avatar', $avatar );
					wp_safe_redirect( admin_url( 'admin.php?page=vmtheme-user-manager&msg=user_created' ) );
					exit;
				}
			}
		}
	}

	// 5. Save Logo & Brand Settings
	if ( isset( $_POST['vmtheme_save_brand_settings'] ) ) {
		check_admin_referer( 'vmtheme_brand_save_nonce' );

		$brand_name   = sanitize_text_field( $_POST['vm_brand_name'] ?? '' );
		$tagline      = sanitize_text_field( $_POST['vm_brand_tagline'] ?? '' );
		$badge        = sanitize_text_field( $_POST['vm_brand_badge_text'] ?? 'PRO' );
		$header_logo  = esc_url_raw( $_POST['vm_brand_logo'] ?? '' );
		$logo_height  = absint( $_POST['vm_header_logo_height'] ?? 42 );
		$footer_logo  = esc_url_raw( $_POST['vm_footer_logo'] ?? '' );
		$footer_about = wp_kses_post( $_POST['vm_footer_about_text'] ?? '' );
		$feat_pills   = sanitize_text_field( $_POST['vm_footer_feat_pills'] ?? '4K UltraHD, 4 Servers, Direct DL, Multi-Sub' );
		$copyright    = wp_kses_post( $_POST['vm_footer_copyright'] ?? '© %YEAR% %BRAND_NAME%. All rights reserved.' );
		$favicon      = esc_url_raw( $_POST['vm_site_favicon'] ?? '' );

		// Socials
		$tg  = esc_url_raw( $_POST['vm_social_telegram'] ?? '' );
		$dc  = esc_url_raw( $_POST['vm_social_discord'] ?? '' );
		$tw  = esc_url_raw( $_POST['vm_social_twitter'] ?? '' );
		$rd  = esc_url_raw( $_POST['vm_social_reddit'] ?? '' );
		$yt  = esc_url_raw( $_POST['vm_social_youtube'] ?? '' );
		$ig  = esc_url_raw( $_POST['vm_social_instagram'] ?? '' );

		update_option( 'vm_brand_name', $brand_name );
		update_option( 'doodh_brand_name', $brand_name );
		update_option( 'vm_brand_tagline', $tagline );
		update_option( 'doodh_brand_tagline', $tagline );
		update_option( 'vm_brand_badge_text', $badge );
		update_option( 'doodh_brand_badge_text', $badge );
		update_option( 'vm_brand_logo', $header_logo );
		update_option( 'doodh_brand_logo', $header_logo );
		update_option( 'vm_header_logo_height', $logo_height );
		update_option( 'vm_footer_logo', $footer_logo );
		update_option( 'vm_footer_about_text', $footer_about );
		update_option( 'vm_footer_feat_pills', $feat_pills );
		update_option( 'vm_footer_copyright', $copyright );
		update_option( 'doodh_footer_copyright', $copyright );
		update_option( 'vm_site_favicon', $favicon );

		update_option( 'vm_social_telegram', $tg );
		update_option( 'vm_social_discord', $dc );
		update_option( 'vm_social_twitter', $tw );
		update_option( 'vm_social_reddit', $rd );
		update_option( 'vm_social_youtube', $yt );
		update_option( 'vm_social_instagram', $ig );

		wp_safe_redirect( admin_url( 'admin.php?page=vmtheme-user-manager&tab=logo_brand&msg=brand_saved' ) );
		exit;
	}
}
add_action( 'admin_init', 'vmtheme_handle_user_manager_actions' );

/**
 * Render Master User & Logo Manager Dashboard
 */
function vmtheme_render_user_manager_dashboard() {
	$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'users';
	$msg         = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
	?>
	<div class="wrap vmtheme-admin-wrap" style="max-width:1280px; margin-top:20px;">
		<!-- Header Banner -->
		<div style="background:linear-gradient(135deg, #0f172a, #1e1b4b); color:#fff; padding:28px 32px; border-radius:12px; margin-bottom:25px; box-shadow:0 8px 25px rgba(0,0,0,0.25); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
			<div>
				<h1 style="color:#fff; margin:0 0 6px; font-size:26px; font-weight:800; display:flex; align-items:center; gap:12px;">
					<span style="background:linear-gradient(135deg,#e50914,#b91c1c); width:40px; height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(229,9,20,0.4);"><i class="dashicons dashicons-admin-users" style="color:#fff; font-size:22px; height:auto; width:auto;"></i></span>
					<?php esc_html_e( 'VMTheme User & Brand Management Suite', 'vmtheme' ); ?>
				</h1>
				<p style="color:#94a3b8; margin:0; font-size:14px;">
					<?php esc_html_e( 'Manage member accounts, custom avatars, soft/hard deletions, header & footer logos, brand tagline, and custom content.', 'vmtheme' ); ?>
				</p>
			</div>

			<?php if ( $current_tab === 'users' || $current_tab === 'trashed' ) : ?>
				<button type="button" class="button button-primary button-hero" onclick="vmthemeOpenUserModal(0)" style="background:#e50914; border-color:#dc2626; display:inline-flex; align-items:center; gap:6px; font-weight:700; height:42px; line-height:40px; padding:0 20px;">
					<i class="dashicons dashicons-plus-alt2" style="line-height:40px;"></i> <?php esc_html_e( 'Add New Member', 'vmtheme' ); ?>
				</button>
			<?php endif; ?>
		</div>

		<!-- Feedback Notices -->
		<?php if ( $msg === 'user_updated' ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'User account and avatar updated successfully!', 'vmtheme' ); ?></strong></p></div>
		<?php elseif ( $msg === 'user_created' ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'New user account created successfully with custom avatar!', 'vmtheme' ); ?></strong></p></div>
		<?php elseif ( $msg === 'soft_deleted' ) : ?>
			<div class="notice notice-warning is-dismissible"><p><strong><?php esc_html_e( 'User moved to trash / soft-deleted. User can no longer log in until restored.', 'vmtheme' ); ?></strong></p></div>
		<?php elseif ( $msg === 'restored' ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'User account successfully restored and activated!', 'vmtheme' ); ?></strong></p></div>
		<?php elseif ( $msg === 'hard_deleted' ) : ?>
			<div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( 'User account permanently deleted from database.', 'vmtheme' ); ?></strong></p></div>
		<?php elseif ( $msg === 'brand_saved' ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Header & Footer Logos, Tagline, and Content updated globally!', 'vmtheme' ); ?></strong></p></div>
		<?php endif; ?>

		<!-- Nav Tabs -->
		<h2 class="nav-tab-wrapper" style="margin-bottom:20px; border-bottom:2px solid #cbd5e1;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager' ) ); ?>" class="nav-tab <?php echo ( $current_tab === 'users' ) ? 'nav-tab-active' : ''; ?>">
				<i class="dashicons dashicons-admin-users"></i> <?php esc_html_e( 'Active Users', 'vmtheme' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager&tab=trashed' ) ); ?>" class="nav-tab <?php echo ( $current_tab === 'trashed' ) ? 'nav-tab-active' : ''; ?>">
				<i class="dashicons dashicons-trash"></i> <?php esc_html_e( 'Soft-Deleted (Trash)', 'vmtheme' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager&tab=logo_brand' ) ); ?>" class="nav-tab <?php echo ( $current_tab === 'logo_brand' ) ? 'nav-tab-active' : ''; ?>">
				<i class="dashicons dashicons-format-image"></i> <?php esc_html_e( 'Logo & Brand Manage', 'vmtheme' ); ?>
			</a>
		</h2>

		<?php
		if ( $current_tab === 'logo_brand' ) {
			vmtheme_render_logo_brand_tab_view();
		} elseif ( $current_tab === 'trashed' ) {
			vmtheme_render_user_table_view( true );
		} else {
			vmtheme_render_user_table_view( false );
		}
		?>
	</div>
	<?php
}

/**
 * Render Users Table View (Active or Soft-Deleted)
 *
 * @param bool $is_trash
 */
function vmtheme_render_user_table_view( $is_trash = false ) {
	$search = isset( $_GET['s'] ) ? sanitize_text_field( trim( $_GET['s'] ) ) : '';
	$role   = isset( $_GET['user_role'] ) ? sanitize_key( $_GET['user_role'] ) : '';
	$paged  = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
	$number = 20;
	$offset = ( $paged - 1 ) * $number;

	$args = array(
		'number' => $number,
		'offset' => $offset,
		'search' => $search ? '*' . $search . '*' : '',
		'role'   => $role,
	);

	if ( $is_trash ) {
		$args['meta_key']   = '_vmtheme_user_status';
		$args['meta_value'] = 'soft_deleted';
	} else {
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => '_vmtheme_user_status',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_vmtheme_user_status',
				'value'   => 'soft_deleted',
				'compare' => '!=',
			),
		);
	}

	$user_query = new WP_User_Query( $args );
	$users      = $user_query->get_results();
	$total_users= $user_query->get_total();
	$total_pages= ceil( $total_users / $number );

	// Pre-build preset avatars
	$preset_avatars = array(
		'https://api.dicebear.com/9.x/adventurer/svg?seed=CyberHero',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=DarkKnight',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=NeonViper',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=AnimeStar',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=CinemaKing',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=StreamQueen',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=MatrixPilot',
		'https://api.dicebear.com/9.x/adventurer/svg?seed=GalacticVanguard',
	);
	?>

	<!-- Search & Filter Controls -->
	<div style="background:#fff; padding:16px 20px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
			<input type="hidden" name="page" value="vmtheme-user-manager">
			<?php if ( $is_trash ) : ?>
				<input type="hidden" name="tab" value="trashed">
			<?php endif; ?>

			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by username, name, or email...', 'vmtheme' ); ?>" style="width:280px; height:36px;">
			
			<select name="user_role" style="height:36px;">
				<option value=""><?php esc_html_e( 'All Roles', 'vmtheme' ); ?></option>
				<option value="administrator" <?php selected( $role, 'administrator' ); ?>><?php esc_html_e( 'Administrator', 'vmtheme' ); ?></option>
				<option value="editor" <?php selected( $role, 'editor' ); ?>><?php esc_html_e( 'Editor', 'vmtheme' ); ?></option>
				<option value="author" <?php selected( $role, 'author' ); ?>><?php esc_html_e( 'Author', 'vmtheme' ); ?></option>
				<option value="subscriber" <?php selected( $role, 'subscriber' ); ?>><?php esc_html_e( 'Subscriber / Member', 'vmtheme' ); ?></option>
			</select>

			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter Users', 'vmtheme' ); ?>">
			<?php if ( $search || $role ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager' . ( $is_trash ? '&tab=trashed' : '' ) ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'vmtheme' ); ?></a>
			<?php endif; ?>
		</form>

		<div style="font-size:13px; color:#64748b;">
			<strong><?php echo esc_html( $total_users ); ?></strong> <?php echo ( $is_trash ) ? esc_html__( 'Soft-Deleted Users in Trash', 'vmtheme' ) : esc_html__( 'Total Registered Users', 'vmtheme' ); ?>
		</div>
	</div>

	<!-- Users Table -->
	<table class="wp-list-table widefat fixed striped" style="border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
		<thead>
			<tr>
				<th style="width:70px;"><?php esc_html_e( 'Avatar', 'vmtheme' ); ?></th>
				<th><?php esc_html_e( 'Username & Name', 'vmtheme' ); ?></th>
				<th><?php esc_html_e( 'Email', 'vmtheme' ); ?></th>
				<th style="width:130px;"><?php esc_html_e( 'Role', 'vmtheme' ); ?></th>
				<th style="width:110px;"><?php esc_html_e( 'Status', 'vmtheme' ); ?></th>
				<th style="width:130px;"><?php esc_html_e( 'Registered', 'vmtheme' ); ?></th>
				<th style="width:180px; text-align:right;"><?php esc_html_e( 'Actions', 'vmtheme' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $users ) ) : ?>
				<?php foreach ( $users as $u ) : 
					$user_id   = $u->ID;
					$avatar    = vmtheme_get_user_avatar( $user_id );
					$role_name = ! empty( $u->roles ) ? ucfirst( $u->roles[0] ) : 'Subscriber';
					$status    = get_user_meta( $user_id, '_vmtheme_user_status', true );
					$is_soft   = ( $status === 'soft_deleted' );
					$reg_date  = date( 'M j, Y', strtotime( $u->user_registered ) );
					$bio       = get_the_author_meta( 'description', $user_id );
					?>
					<tr id="user-row-<?php echo esc_attr( $user_id ); ?>">
						<td>
							<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $u->display_name ); ?>" width="46" height="46" style="border-radius:50%; object-fit:cover; border:2px solid #e2e8f0; background:#0f172a;">
						</td>
						<td>
							<strong><a href="javascript:void(0);" onclick="vmthemeEditUser(<?php echo esc_js( $user_id ); ?>)"><?php echo esc_html( $u->user_login ); ?></a></strong>
							<?php if ( $u->display_name && $u->display_name !== $u->user_login ) : ?>
								<div style="font-size:12px; color:#64748b;"><?php echo esc_html( $u->display_name ); ?></div>
							<?php endif; ?>
						</td>
						<td>
							<a href="mailto:<?php echo esc_attr( $u->user_email ); ?>"><?php echo esc_html( $u->user_email ); ?></a>
						</td>
						<td>
							<span class="badge" style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-weight:700; font-size:12px;">
								<?php echo esc_html( $role_name ); ?>
							</span>
						</td>
						<td>
							<?php if ( $is_soft ) : ?>
								<span style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:4px; font-weight:700; font-size:11px;">
									<i class="dashicons dashicons-hidden" style="font-size:13px; line-height:16px;"></i> <?php esc_html_e( 'Trashed', 'vmtheme' ); ?>
								</span>
							<?php else : ?>
								<span style="background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:4px; font-weight:700; font-size:11px;">
									<i class="dashicons dashicons-yes-alt" style="font-size:13px; line-height:16px;"></i> <?php esc_html_e( 'Active', 'vmtheme' ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td style="font-size:12.5px; color:#64748b;">
							<?php echo esc_html( $reg_date ); ?>
						</td>
						<td style="text-align:right;">
							<!-- Edit Trigger -->
							<button type="button" class="button button-small" onclick="vmthemeEditUser(<?php echo esc_js( $user_id ); ?>)" title="<?php esc_attr_e( 'Edit User & Avatar', 'vmtheme' ); ?>">
								<i class="dashicons dashicons-edit" style="margin-top:2px;"></i> <?php esc_html_e( 'Edit', 'vmtheme' ); ?>
							</button>

							<?php if ( $user_id !== get_current_user_id() ) : ?>
								<?php if ( $is_soft ) : ?>
									<!-- Restore Action -->
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=vmtheme-user-manager&action=restore&user_id=' . $user_id ), 'vmtheme_user_action_' . $user_id ) ); ?>" class="button button-small" style="color:#15803d; border-color:#86efac;" title="<?php esc_attr_e( 'Restore User Account', 'vmtheme' ); ?>">
										<i class="dashicons dashicons-undo" style="margin-top:2px;"></i> <?php esc_html_e( 'Restore', 'vmtheme' ); ?>
									</a>
								<?php else : ?>
									<!-- Soft Delete Action -->
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=vmtheme-user-manager&action=soft_delete&user_id=' . $user_id ), 'vmtheme_user_action_' . $user_id ) ); ?>" class="button button-small" style="color:#b45309;" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to soft-delete (trash) this user? They will be unable to log in until restored.', 'vmtheme' ); ?>');" title="<?php esc_attr_e( 'Soft Delete / Deactivate', 'vmtheme' ); ?>">
										<i class="dashicons dashicons-trash" style="margin-top:2px;"></i> <?php esc_html_e( 'Trash', 'vmtheme' ); ?>
									</a>
								<?php endif; ?>

								<!-- Hard Delete Trigger -->
								<button type="button" class="button button-small" style="color:#dc2626;" onclick="vmthemeConfirmHardDelete(<?php echo esc_js( $user_id ); ?>, '<?php echo esc_js( $u->user_login ); ?>')" title="<?php esc_attr_e( 'Permanently Delete User', 'vmtheme' ); ?>">
									<i class="dashicons dashicons-no-alt" style="margin-top:2px;"></i>
								</button>
							<?php endif; ?>

							<!-- Hidden JSON data for instant edit popup -->
							<textarea id="user-data-<?php echo esc_attr( $user_id ); ?>" style="display:none;"><?php echo esc_textarea( wp_json_encode( array(
								'id'           => $user_id,
								'login'        => $u->user_login,
								'email'        => $u->user_email,
								'display_name' => $u->display_name,
								'role'         => ! empty( $u->roles ) ? $u->roles[0] : 'subscriber',
								'avatar'       => $avatar,
								'bio'          => $bio,
							) ) ); ?></textarea>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="7" style="text-align:center; padding:30px; color:#64748b;">
						<?php esc_html_e( 'No users found matching your query.', 'vmtheme' ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav" style="margin-top:15px;">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo sprintf( _n( '%d item', '%d items', $total_users, 'vmtheme' ), $total_users ); ?></span>
				<span class="pagination-links">
					<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
						<a class="page-numbers <?php echo ( $paged === $i ) ? 'current' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" style="padding:4px 10px; text-decoration:none; margin:0 2px; <?php echo ( $paged === $i ) ? 'background:#0284c7; color:#fff; font-weight:bold; border-radius:3px;' : ''; ?>"><?php echo esc_html( $i ); ?></a>
					<?php endfor; ?>
				</span>
			</div>
		</div>
	<?php endif; ?>

	<!-- Modal: Edit / Add User -->
	<div id="vmtheme-user-modal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
		<div style="background:#fff; border-radius:12px; max-width:620px; width:92%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,0.4); padding:28px 32px; position:relative;">
			<button type="button" onclick="vmthemeCloseUserModal()" style="position:absolute; top:18px; right:20px; background:none; border:none; font-size:24px; cursor:pointer; color:#64748b;">&times;</button>
			
			<h2 id="vmtheme-modal-title" style="margin-top:0; font-size:22px; font-weight:800; color:#0f172a; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
				<?php esc_html_e( 'Edit User & Avatar', 'vmtheme' ); ?>
			</h2>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager' ) ); ?>">
				<?php wp_nonce_field( 'vmtheme_save_user_nonce' ); ?>
				<input type="hidden" name="user_id" id="modal_user_id" value="0">

				<div id="modal_username_row" style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px; color:#334155;"><?php esc_html_e( 'Username', 'vmtheme' ); ?> *</label>
					<input type="text" name="user_login" id="modal_user_login" class="regular-text" style="width:100%;" required>
				</div>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px; color:#334155;"><?php esc_html_e( 'Display Name', 'vmtheme' ); ?></label>
					<input type="text" name="display_name" id="modal_display_name" class="regular-text" style="width:100%;">
				</div>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px; color:#334155;"><?php esc_html_e( 'Email Address', 'vmtheme' ); ?> *</label>
					<input type="email" name="user_email" id="modal_user_email" class="regular-text" style="width:100%;" required>
				</div>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px; color:#334155;"><?php esc_html_e( 'User Role', 'vmtheme' ); ?></label>
					<select name="role" id="modal_user_role" style="width:100%;">
						<option value="subscriber"><?php esc_html_e( 'Subscriber (Free Member)', 'vmtheme' ); ?></option>
						<option value="author"><?php esc_html_e( 'Author (Writer)', 'vmtheme' ); ?></option>
						<option value="editor"><?php esc_html_e( 'Editor (Moderator)', 'vmtheme' ); ?></option>
						<option value="administrator"><?php esc_html_e( 'Administrator (Full Access)', 'vmtheme' ); ?></option>
					</select>
				</div>

				<!-- Avatar Customizer Section -->
				<div style="margin-bottom:20px; background:#f8fafc; padding:16px; border-radius:8px; border:1px solid #e2e8f0;">
					<label style="display:block; font-weight:700; margin-bottom:8px; color:#0f172a; font-size:14px;">
						<i class="dashicons dashicons-camera" style="margin-top:2px;"></i> <?php esc_html_e( 'Custom Avatar Photo', 'vmtheme' ); ?>
					</label>
					
					<div style="display:flex; align-items:center; gap:16px; margin-bottom:12px;">
						<img src="" id="modal_avatar_preview" width="60" height="60" style="border-radius:50%; object-fit:cover; border:2px solid #0284c7; background:#0f172a;">
						<div style="flex:1;">
							<input type="url" name="user_avatar" id="modal_user_avatar" placeholder="https://..." style="width:100%; margin-bottom:6px;" oninput="document.getElementById('modal_avatar_preview').src = this.value;">
							<button type="button" class="button button-secondary" onclick="vmthemeUploadAvatar()" style="font-size:12px;">
								<i class="dashicons dashicons-upload"></i> <?php esc_html_e( 'Upload from Computer / Media', 'vmtheme' ); ?>
							</button>
						</div>
					</div>

					<div style="font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;"><?php esc_html_e( 'Or select a Cinema Hero Avatar preset:', 'vmtheme' ); ?></div>
					<div style="display:flex; gap:8px; flex-wrap:wrap;">
						<?php foreach ( $preset_avatars as $preset ) : ?>
							<img src="<?php echo esc_url( $preset ); ?>" width="34" height="34" style="border-radius:50%; cursor:pointer; border:1px solid #cbd5e1; background:#0f172a;" onclick="document.getElementById('modal_user_avatar').value='<?php echo esc_js( $preset ); ?>'; document.getElementById('modal_avatar_preview').src='<?php echo esc_js( $preset ); ?>';" title="Click to pick preset">
						<?php endforeach; ?>
					</div>
				</div>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px; color:#334155;"><?php esc_html_e( 'Password', 'vmtheme' ); ?> <span id="modal_pass_note" style="font-weight:normal; color:#64748b;">(Leave blank to keep current)</span></label>
					<input type="password" name="user_pass" id="modal_user_pass" class="regular-text" style="width:100%;">
				</div>

				<div style="margin-bottom:20px;">
					<label style="display:block; font-weight:700; margin-bottom:6px; color:#334155;"><?php esc_html_e( 'Bio / Description', 'vmtheme' ); ?></label>
					<textarea name="description" id="modal_user_bio" rows="3" style="width:100%;"></textarea>
				</div>

				<div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e2e8f0; padding-top:16px;">
					<button type="button" class="button" onclick="vmthemeCloseUserModal()"><?php esc_html_e( 'Cancel', 'vmtheme' ); ?></button>
					<input type="submit" name="vmtheme_save_user" class="button button-primary" value="<?php esc_attr_e( 'Save User Account', 'vmtheme' ); ?>" style="background:#e50914; border-color:#dc2626;">
				</div>
			</form>
		</div>
	</div>

	<!-- Modal: Hard Delete Confirmation -->
	<div id="vmtheme-hard-delete-modal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
		<div style="background:#fff; border-radius:12px; max-width:480px; width:90%; padding:28px; box-shadow:0 20px 40px rgba(0,0,0,0.4);">
			<h3 style="color:#b91c1c; margin-top:0; font-size:20px;">
				<i class="dashicons dashicons-warning"></i> <?php esc_html_e( 'Confirm Permanent Hard Delete', 'vmtheme' ); ?>
			</h3>
			<p style="color:#334155; font-size:14px; line-height:1.5;">
				<?php esc_html_e( 'Are you sure you want to permanently delete user', 'vmtheme' ); ?> <strong id="hard_delete_username"></strong>? <?php esc_html_e( 'This action cannot be undone and will erase their user profile.', 'vmtheme' ); ?>
			</p>
			
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager' ) ); ?>">
				<?php wp_nonce_field( 'vmtheme_hard_delete_nonce' ); ?>
				<input type="hidden" name="target_user_id" id="hard_delete_user_id" value="0">

				<div style="margin:16px 0;">
					<label style="font-size:13px; font-weight:600; display:block; margin-bottom:6px;"><?php esc_html_e( 'Reassign content to Administrator:', 'vmtheme' ); ?></label>
					<select name="reassign_user" style="width:100%;">
						<?php
						$admins = get_users( array( 'role' => 'administrator' ) );
						foreach ( $admins as $adm ) {
							echo '<option value="' . esc_attr( $adm->ID ) . '">' . esc_html( $adm->display_name ) . ' (' . esc_html( $adm->user_login ) . ')</option>';
						}
						?>
					</select>
				</div>

				<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
					<button type="button" class="button" onclick="document.getElementById('vmtheme-hard-delete-modal').style.display='none'"><?php esc_html_e( 'Cancel', 'vmtheme' ); ?></button>
					<input type="submit" name="vmtheme_hard_delete_user" class="button button-primary" value="<?php esc_attr_e( 'Yes, Permanently Delete', 'vmtheme' ); ?>" style="background:#dc2626; border-color:#b91c1c;">
				</div>
			</form>
		</div>
	</div>

	<!-- JavaScript for Modals & Media Upload -->
	<script>
	function vmthemeOpenUserModal(userId) {
		var modal = document.getElementById('vmtheme-user-modal');
		var title = document.getElementById('vmtheme-modal-title');
		var rowLogin = document.getElementById('modal_username_row');
		var passNote = document.getElementById('modal_pass_note');

		if (userId === 0) {
			title.innerText = '<?php echo esc_js( __( 'Add New Member Account', 'vmtheme' ) ); ?>';
			document.getElementById('modal_user_id').value = 0;
			document.getElementById('modal_user_login').value = '';
			document.getElementById('modal_user_login').readOnly = false;
			rowLogin.style.display = 'block';
			document.getElementById('modal_display_name').value = '';
			document.getElementById('modal_user_email').value = '';
			document.getElementById('modal_user_role').value = 'subscriber';
			document.getElementById('modal_user_avatar').value = '<?php echo esc_js( $preset_avatars[0] ); ?>';
			document.getElementById('modal_avatar_preview').src = '<?php echo esc_js( $preset_avatars[0] ); ?>';
			document.getElementById('modal_user_pass').value = '';
			document.getElementById('modal_user_pass').required = true;
			passNote.innerText = '(Required for new accounts)';
			document.getElementById('modal_user_bio').value = '';
		}
		modal.style.display = 'flex';
	}

	function vmthemeEditUser(userId) {
		var dataEl = document.getElementById('user-data-' + userId);
		if (!dataEl) return;
		var u = JSON.parse(dataEl.value);

		var modal = document.getElementById('vmtheme-user-modal');
		var title = document.getElementById('vmtheme-modal-title');
		var rowLogin = document.getElementById('modal_username_row');
		var passNote = document.getElementById('modal_pass_note');

		title.innerText = '<?php echo esc_js( __( 'Edit User & Avatar', 'vmtheme' ) ); ?>: ' + u.login;
		document.getElementById('modal_user_id').value = u.id;
		document.getElementById('modal_user_login').value = u.login;
		document.getElementById('modal_user_login').readOnly = true;
		document.getElementById('modal_display_name').value = u.display_name;
		document.getElementById('modal_user_email').value = u.email;
		document.getElementById('modal_user_role').value = u.role;
		document.getElementById('modal_user_avatar').value = u.avatar;
		document.getElementById('modal_avatar_preview').src = u.avatar;
		document.getElementById('modal_user_pass').value = '';
		document.getElementById('modal_user_pass').required = false;
		passNote.innerText = '(Leave blank to keep current)';
		document.getElementById('modal_user_bio').value = u.bio || '';

		modal.style.display = 'flex';
	}

	function vmthemeCloseUserModal() {
		document.getElementById('vmtheme-user-modal').style.display = 'none';
	}

	function vmthemeConfirmHardDelete(userId, username) {
		document.getElementById('hard_delete_user_id').value = userId;
		document.getElementById('hard_delete_username').innerText = username;
		document.getElementById('vmtheme-hard-delete-modal').style.display = 'flex';
	}

	function vmthemeUploadAvatar() {
		var customUploader = wp.media({
			title: '<?php echo esc_js( __( 'Select or Upload User Avatar', 'vmtheme' ) ); ?>',
			button: { text: '<?php echo esc_js( __( 'Set as Avatar', 'vmtheme' ) ); ?>' },
			multiple: false
		}).on('select', function() {
			var attachment = customUploader.state().get('selection').first().toJSON();
			document.getElementById('modal_user_avatar').value = attachment.url;
			document.getElementById('modal_avatar_preview').src = attachment.url;
		}).open();
	}
	</script>
	<?php
}

/**
 * Render Logo & Brand Settings Tab View
 */
function vmtheme_render_logo_brand_tab_view() {
	$brand_name   = vmtheme_get_brand_name();
	$tagline      = vmtheme_get_brand_tagline();
	$badge_text   = vmtheme_get_brand_badge();
	$header_logo  = get_option( 'vm_brand_logo', get_option( 'doodh_brand_logo', '' ) );
	$logo_height  = get_option( 'vm_header_logo_height', 42 );
	$footer_logo  = get_option( 'vm_footer_logo', '' );
	$footer_about = get_option( 'vm_footer_about_text', '' );
	$feat_pills   = get_option( 'vm_footer_feat_pills', '4K UltraHD, 4 Servers, Direct DL, Multi-Sub' );
	$copyright    = get_option( 'vm_footer_copyright', get_option( 'doodh_footer_copyright', '© %YEAR% %BRAND_NAME%. All rights reserved.' ) );
	$favicon      = get_option( 'vm_site_favicon', '' );

	// Socials
	$tg = get_option( 'vm_social_telegram', '#' );
	$dc = get_option( 'vm_social_discord', '#' );
	$tw = get_option( 'vm_social_twitter', '#' );
	$rd = get_option( 'vm_social_reddit', '#' );
	$yt = get_option( 'vm_social_youtube', '#' );
	$ig = get_option( 'vm_social_instagram', '#' );
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=vmtheme-user-manager&tab=logo_brand' ) ); ?>">
		<?php wp_nonce_field( 'vmtheme_brand_save_nonce' ); ?>

		<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
			<!-- Left Column: Header Logo & Identity -->
			<div style="background:#fff; padding:24px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
				<h3 style="margin-top:0; font-size:18px; color:#0f172a; border-bottom:2px solid #e2e8f0; padding-bottom:10px; display:flex; align-items:center; gap:8px;">
					<i class="dashicons dashicons-desktop" style="color:#e50914;"></i> <?php esc_html_e( 'Header Logo & Brand Identity', 'vmtheme' ); ?>
				</h3>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Website Brand Name', 'vmtheme' ); ?> *</label>
					<input type="text" name="vm_brand_name" value="<?php echo esc_attr( $brand_name ); ?>" class="large-text" required>
					<p class="description"><?php esc_html_e( 'Updates website title, SEO schema, OpenGraph, and navbar typography.', 'vmtheme' ); ?></p>
				</div>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Brand Tagline / Slogan', 'vmtheme' ); ?></label>
					<input type="text" name="vm_brand_tagline" value="<?php echo esc_attr( $tagline ); ?>" class="large-text">
				</div>

				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Navbar Brand Badge Text', 'vmtheme' ); ?></label>
					<input type="text" name="vm_brand_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" style="width:120px; font-weight:bold;">
					<span class="description"><?php esc_html_e( 'Pill badge next to logo (e.g. PRO, 4K, VIP, HD).', 'vmtheme' ); ?></span>
				</div>

				<!-- Header Logo Upload -->
				<div style="margin-bottom:20px; background:#f8fafc; padding:16px; border-radius:8px; border:1px solid #e2e8f0;">
					<label style="display:block; font-weight:700; margin-bottom:8px; color:#0f172a;">
						<?php esc_html_e( 'Header Custom Logo Image', 'vmtheme' ); ?>
					</label>
					
					<div style="margin-bottom:10px; min-height:50px; background:#0f172a; padding:12px; border-radius:6px; display:flex; align-items:center; justify-content:center;">
						<img src="<?php echo esc_url( $header_logo ?: get_template_directory_uri() . '/screenshot.png' ); ?>" id="header_logo_preview" style="max-height:<?php echo esc_attr( $logo_height ); ?>px; width:auto; <?php echo empty( $header_logo ) ? 'display:none;' : ''; ?>">
						<span id="header_logo_fallback_text" style="<?php echo ! empty( $header_logo ) ? 'display:none;' : ''; ?> color:#94a3b8; font-size:13px;">
							<i class="dashicons dashicons-format-image"></i> <?php esc_html_e( 'Using Dynamic SVG/Text Logo with Brand Name', 'vmtheme' ); ?>
						</span>
					</div>

					<input type="url" name="vm_brand_logo" id="vm_brand_logo" value="<?php echo esc_attr( $header_logo ); ?>" class="large-text" placeholder="https://..." style="margin-bottom:8px;">
					
					<div style="display:flex; gap:10px; align-items:center;">
						<button type="button" class="button" onclick="vmthemeUploadMedia('vm_brand_logo', 'header_logo_preview', 'header_logo_fallback_text')">
							<i class="dashicons dashicons-upload"></i> <?php esc_html_e( 'Upload Logo Image', 'vmtheme' ); ?>
						</button>
						<?php if ( $header_logo ) : ?>
							<button type="button" class="button" onclick="document.getElementById('vm_brand_logo').value=''; document.getElementById('header_logo_preview').style.display='none'; document.getElementById('header_logo_fallback_text').style.display='block';">
								<?php esc_html_e( 'Clear (Use Text)', 'vmtheme' ); ?>
							</button>
						<?php endif; ?>
					</div>

					<div style="margin-top:12px;">
						<label style="font-size:13px; font-weight:600;"><?php esc_html_e( 'Header Logo Display Height (px):', 'vmtheme' ); ?></label>
						<input type="number" name="vm_header_logo_height" value="<?php echo esc_attr( $logo_height ); ?>" min="24" max="90" style="width:80px;">
					</div>
				</div>

				<!-- Favicon -->
				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Browser Favicon / Web Icon', 'vmtheme' ); ?></label>
					<div style="display:flex; align-items:center; gap:10px;">
						<img src="<?php echo esc_url( $favicon ?: get_template_directory_uri() . '/assets/images/avatar-placeholder.svg' ); ?>" id="favicon_preview" width="32" height="32" style="border-radius:4px; border:1px solid #cbd5e1; background:#0f172a;">
						<input type="url" name="vm_site_favicon" id="vm_site_favicon" value="<?php echo esc_attr( $favicon ); ?>" class="regular-text" placeholder="https://.../favicon.png">
						<button type="button" class="button" onclick="vmthemeUploadMedia('vm_site_favicon', 'favicon_preview')">
							<?php esc_html_e( 'Upload', 'vmtheme' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Right Column: Footer Logo & Content Customizer -->
			<div style="background:#fff; padding:24px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
				<h3 style="margin-top:0; font-size:18px; color:#0f172a; border-bottom:2px solid #e2e8f0; padding-bottom:10px; display:flex; align-items:center; gap:8px;">
					<i class="dashicons dashicons-art" style="color:#e50914;"></i> <?php esc_html_e( 'Footer Logo, Badges & Custom Content', 'vmtheme' ); ?>
				</h3>

				<!-- Footer Logo Upload -->
				<div style="margin-bottom:20px; background:#f8fafc; padding:16px; border-radius:8px; border:1px solid #e2e8f0;">
					<label style="display:block; font-weight:700; margin-bottom:8px; color:#0f172a;">
						<?php esc_html_e( 'Footer Separate Custom Logo (Optional)', 'vmtheme' ); ?>
					</label>
					<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Leave blank to automatically use your Header Logo.', 'vmtheme' ); ?></p>
					
					<div style="margin-bottom:10px; min-height:50px; background:#0f172a; padding:12px; border-radius:6px; display:flex; align-items:center; justify-content:center;">
						<img src="<?php echo esc_url( $footer_logo ?: ( $header_logo ?: get_template_directory_uri() . '/screenshot.png' ) ); ?>" id="footer_logo_preview" style="max-height:40px; width:auto;">
					</div>

					<input type="url" name="vm_footer_logo" id="vm_footer_logo" value="<?php echo esc_attr( $footer_logo ); ?>" class="large-text" placeholder="https://..." style="margin-bottom:8px;">
					
					<div style="display:flex; gap:10px; align-items:center;">
						<button type="button" class="button" onclick="vmthemeUploadMedia('vm_footer_logo', 'footer_logo_preview')">
							<i class="dashicons dashicons-upload"></i> <?php esc_html_e( 'Upload Footer Logo', 'vmtheme' ); ?>
						</button>
						<?php if ( $footer_logo ) : ?>
							<button type="button" class="button" onclick="document.getElementById('vm_footer_logo').value=''; document.getElementById('footer_logo_preview').src=document.getElementById('vm_brand_logo').value;">
								<?php esc_html_e( 'Reset to Header Logo', 'vmtheme' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<!-- Footer Content / Description -->
				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Footer About Synopsis Content', 'vmtheme' ); ?></label>
					<textarea name="vm_footer_about_text" rows="3" class="large-text" placeholder="<?php echo esc_attr( sprintf( __( '%s is your premier streaming portal to watch and discover thousands of movies...', 'vmtheme' ), $brand_name ) ); ?>"><?php echo esc_textarea( $footer_about ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Custom description displayed under the footer logo. Leave blank for default blurb.', 'vmtheme' ); ?></p>
				</div>

				<!-- Footer Feature Badges -->
				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Footer Feature Badges (Comma Separated)', 'vmtheme' ); ?></label>
					<input type="text" name="vm_footer_feat_pills" value="<?php echo esc_attr( $feat_pills ); ?>" class="large-text">
					<p class="description"><?php esc_html_e( 'e.g. 4K UltraHD, 4 Servers, Direct DL, Multi-Sub, Fast CDN', 'vmtheme' ); ?></p>
				</div>

				<!-- Copyright -->
				<div style="margin-bottom:16px;">
					<label style="display:block; font-weight:700; margin-bottom:6px;"><?php esc_html_e( 'Footer Copyright Text', 'vmtheme' ); ?></label>
					<input type="text" name="vm_footer_copyright" value="<?php echo esc_attr( $copyright ); ?>" class="large-text">
					<p class="description"><?php esc_html_e( 'Supports %YEAR% (auto year) and %BRAND_NAME% (auto brand name).', 'vmtheme' ); ?></p>
				</div>

				<!-- Social Media Links -->
				<div style="background:#f8fafc; padding:16px; border-radius:8px; border:1px solid #e2e8f0;">
					<label style="display:block; font-weight:700; margin-bottom:10px; color:#0f172a;">
						<i class="dashicons dashicons-share" style="margin-top:2px;"></i> <?php esc_html_e( 'Social Media Community Links', 'vmtheme' ); ?>
					</label>
					<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
						<input type="url" name="vm_social_telegram" value="<?php echo esc_attr( $tg ); ?>" placeholder="Telegram URL (https://t.me/...)">
						<input type="url" name="vm_social_discord" value="<?php echo esc_attr( $dc ); ?>" placeholder="Discord Invite URL">
						<input type="url" name="vm_social_twitter" value="<?php echo esc_attr( $tw ); ?>" placeholder="Twitter / X URL">
						<input type="url" name="vm_social_reddit" value="<?php echo esc_attr( $rd ); ?>" placeholder="Reddit Community URL">
						<input type="url" name="vm_social_youtube" value="<?php echo esc_attr( $yt ); ?>" placeholder="YouTube Channel URL">
						<input type="url" name="vm_social_instagram" value="<?php echo esc_attr( $ig ); ?>" placeholder="Instagram URL">
					</div>
				</div>
			</div>
		</div>

		<p class="submit" style="margin-top:24px;">
			<input type="submit" name="vmtheme_save_brand_settings" class="button button-primary button-large" value="<?php esc_attr_e( 'Save Brand & Logo Settings', 'vmtheme' ); ?>" style="background:#e50914; border-color:#dc2626; font-size:16px; font-weight:700; height:46px; line-height:44px; padding:0 28px;">
		</p>
	</form>

	<script>
	function vmthemeUploadMedia(inputId, previewId, fallbackId) {
		var customUploader = wp.media({
			title: '<?php echo esc_js( __( 'Choose or Upload Logo Image', 'vmtheme' ) ); ?>',
			button: { text: '<?php echo esc_js( __( 'Use This Logo', 'vmtheme' ) ); ?>' },
			multiple: false
		}).on('select', function() {
			var attachment = customUploader.state().get('selection').first().toJSON();
			document.getElementById(inputId).value = attachment.url;
			if (previewId) {
				var prev = document.getElementById(previewId);
				prev.src = attachment.url;
				prev.style.display = 'block';
			}
			if (fallbackId) {
				var fb = document.getElementById(fallbackId);
				if (fb) fb.style.display = 'none';
			}
		}).open();
	}
	</script>
	<?php
}

/**
 * Helper: Render Footer Brand Logo
 */
function vmtheme_render_footer_logo() {
	$footer_logo = get_option( 'vm_footer_logo' );
	$header_logo = get_option( 'vm_brand_logo', get_option( 'doodh_brand_logo' ) );
	$logo_url    = ! empty( $footer_logo ) ? $footer_logo : $header_logo;
	$brand_name  = vmtheme_get_brand_name();

	if ( ! empty( $logo_url ) ) {
		echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="doodh-footer-brand-link">';
		echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $brand_name ) . '" class="doodh-footer-brand-img" style="max-height:42px; width:auto;">';
		echo '</a>';
	} else {
		echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="doodh-brand">';
		echo '<div class="doodh-logo-icon"><i class="fas fa-play"></i></div>';
		echo '<span>' . esc_html( $brand_name ) . '</span>';
		echo '</a>';
	}
}

if ( ! function_exists( 'doodhtheme_render_footer_logo' ) ) {
	function doodhtheme_render_footer_logo() {
		vmtheme_render_footer_logo();
	}
}

/**
 * Helper: Get Footer Custom About Content
 */
function vmtheme_get_footer_about_text() {
	$custom = get_option( 'vm_footer_about_text' );
	if ( ! empty( $custom ) ) {
		return wp_kses_post( $custom );
	}
	$brand_name = vmtheme_get_brand_name();
	return sprintf( __( '%s is your premier streaming portal to watch and discover thousands of movies, TV shows, and anime in crystal-clear 4K and Full HD resolution with multi-server playback.', 'vmtheme' ), esc_html( $brand_name ) );
}

if ( ! function_exists( 'doodhtheme_get_footer_about_text' ) ) {
	function doodhtheme_get_footer_about_text() {
		return vmtheme_get_footer_about_text();
	}
}

/**
 * Helper: Render Footer Feature Badges
 */
function vmtheme_render_footer_feature_pills() {
	$pills_str = get_option( 'vm_footer_feat_pills', '4K UltraHD, 4 Servers, Direct DL, Multi-Sub' );
	$pills     = array_map( 'trim', explode( ',', $pills_str ) );
	$icons     = array( 'fas fa-tv', 'fas fa-server', 'fas fa-download', 'fas fa-closed-captioning', 'fas fa-bolt', 'fas fa-shield-alt' );

	echo '<div class="doodh-footer-features-pills">';
	foreach ( $pills as $index => $pill ) {
		if ( empty( $pill ) ) continue;
		$icon = $icons[ $index % count( $icons ) ];
		echo '<span class="doodh-footer-feat-pill"><i class="' . esc_attr( $icon ) . '"></i> ' . esc_html( $pill ) . '</span>';
	}
	echo '</div>';
}

if ( ! function_exists( 'doodhtheme_render_footer_feature_pills' ) ) {
	function doodhtheme_render_footer_feature_pills() {
		vmtheme_render_footer_feature_pills();
	}
}

/**
 * Helper: Render Footer Dynamic Social Media Links
 */
function vmtheme_render_footer_socials() {
	$socials = array(
		'tg' => array( 'url' => get_option( 'vm_social_telegram' ), 'icon' => 'fab fa-telegram-plane', 'title' => 'Telegram' ),
		'dc' => array( 'url' => get_option( 'vm_social_discord' ),  'icon' => 'fab fa-discord',        'title' => 'Discord' ),
		'tw' => array( 'url' => get_option( 'vm_social_twitter' ),  'icon' => 'fab fa-x-twitter',      'title' => 'Twitter / X' ),
		'rd' => array( 'url' => get_option( 'vm_social_reddit' ),   'icon' => 'fab fa-reddit-alien',   'title' => 'Reddit' ),
		'yt' => array( 'url' => get_option( 'vm_social_youtube' ),  'icon' => 'fab fa-youtube',        'title' => 'YouTube' ),
		'ig' => array( 'url' => get_option( 'vm_social_instagram' ),'icon' => 'fab fa-instagram',      'title' => 'Instagram' ),
	);

	echo '<div class="doodh-footer-socials">';
	foreach ( $socials as $key => $s ) {
		$url = ! empty( $s['url'] ) ? $s['url'] : '#';
		echo '<a href="' . esc_url( $url ) . '" class="doodh-social-icon ' . esc_attr( $key ) . '" title="' . esc_attr( $s['title'] ) . '" aria-label="' . esc_attr( $s['title'] ) . '" target="_blank" rel="noopener noreferrer"><i class="' . esc_attr( $s['icon'] ) . '"></i></a>';
	}
	echo '</div>';
}

if ( ! function_exists( 'doodhtheme_render_footer_socials' ) ) {
	function doodhtheme_render_footer_socials() {
		vmtheme_render_footer_socials();
	}
}
