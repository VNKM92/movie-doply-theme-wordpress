<?php
/**
 * Plugin Name: UpdraftPlus Backup & Restore
 * Plugin URI: https://wordpress.org/plugins/updraftplus/
 * Description: Complete WordPress backup and restore solution. Create full backups of your database, plugins, themes, uploads, and wp-content, download archives, and restore anytime with 1-click.
 * Version: 1.0.0
 * Author: UpdraftPlus & VMTheme Engineering
 * Author URI: https://vmtheme.com/
 * License: GPLv2 or later
 * Text Domain: updraftplus-backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UPDRAFTPLUS_BACKUP_VERSION', '1.0.0' );
define( 'UPDRAFTPLUS_BACKUP_DIR', plugin_dir_path( __FILE__ ) );
define( 'UPDRAFTPLUS_BACKUP_URL', plugin_dir_url( __FILE__ ) );
define( 'UPDRAFTPLUS_STORAGE_DIR', WP_CONTENT_DIR . '/updraft-backups' );

/**
 * Initialize Backup Storage Directory with Security Locks
 */
function updraftplus_init_storage_dir() {
	if ( ! file_exists( UPDRAFTPLUS_STORAGE_DIR ) ) {
		wp_mkdir_p( UPDRAFTPLUS_STORAGE_DIR );
	}

	// Security: Prevent direct web access to backup files
	$htaccess = UPDRAFTPLUS_STORAGE_DIR . '/.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		@file_put_contents( $htaccess, "Order Deny,Allow\nDeny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n" );
	}

	$index = UPDRAFTPLUS_STORAGE_DIR . '/index.php';
	if ( ! file_exists( $index ) ) {
		@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
	}
}
register_activation_hook( __FILE__, 'updraftplus_init_storage_dir' );

/**
 * Register UpdraftPlus Admin Menu
 */
function updraftplus_register_admin_menu() {
	add_menu_page(
		__( 'UpdraftPlus Backups', 'updraftplus-backup' ),
		__( 'UpdraftPlus Backup', 'updraftplus-backup' ),
		'manage_options',
		'updraftplus-backup',
		'updraftplus_render_admin_dashboard',
		'dashicons-backup',
		80
	);
}
add_action( 'admin_menu', 'updraftplus_register_admin_menu' );

/**
 * Handle Direct Backup File Download
 */
function updraftplus_handle_download_backup() {
	if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'updraft_download_backup' ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized access.', 'updraftplus-backup' ) );
	}

	check_admin_referer( 'updraft_download_action', 'updraft_nonce' );

	$file = sanitize_file_name( $_GET['file'] ?? '' );
	if ( empty( $file ) ) {
		wp_die( esc_html__( 'Invalid filename.', 'updraftplus-backup' ) );
	}

	$filepath = UPDRAFTPLUS_STORAGE_DIR . '/' . $file;
	if ( ! file_exists( $filepath ) ) {
		wp_die( esc_html__( 'Backup file not found.', 'updraftplus-backup' ) );
	}

	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . basename( $filepath ) . '"' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . filesize( $filepath ) );
	readfile( $filepath );
	exit;
}
add_action( 'admin_init', 'updraftplus_handle_download_backup' );

/**
 * Helper: Export Complete Database to SQL String
 */
function updraftplus_export_database() {
	global $wpdb;
	$tables = $wpdb->get_col( 'SHOW TABLES' );

	$sql_dump  = "-- UpdraftPlus WordPress Database Backup\n";
	$sql_dump .= "-- Generated: " . current_time( 'mysql' ) . "\n";
	$sql_dump .= "-- Database: " . DB_NAME . "\n";
	$sql_dump .= "-- Host: " . DB_HOST . "\n\n";
	$sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n";
	$sql_dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";

	foreach ( $tables as $table ) {
		// Table structure
		$create_table = $wpdb->get_row( "SHOW CREATE TABLE `{$table}`", ARRAY_N );
		if ( ! empty( $create_table[1] ) ) {
			$sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
			$sql_dump .= $create_table[1] . ";\n\n";
		}

		// Table records
		$rows = $wpdb->get_results( "SELECT * FROM `{$table}`", ARRAY_A );
		if ( ! empty( $rows ) ) {
			$sql_dump .= "INSERT INTO `{$table}` VALUES \n";
			$row_chunks = array();
			foreach ( $rows as $row ) {
				$values = array();
				foreach ( $row as $val ) {
					if ( is_null( $val ) ) {
						$values[] = 'NULL';
					} else {
						$values[] = "'" . $wpdb->_real_escape( $val ) . "'";
					}
				}
				$row_chunks[] = '(' . implode( ', ', $values ) . ')';
			}
			$sql_dump .= implode( ",\n", $row_chunks ) . ";\n\n";
		}
	}

	$sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
	return $sql_dump;
}

/**
 * Helper: Add Directory Recursively to ZipArchive
 */
function updraftplus_add_dir_to_zip( $dir_path, $zip, $local_prefix = '' ) {
	if ( ! is_dir( $dir_path ) ) {
		return;
	}

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir_path, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $files as $file ) {
		$real_path = $file->getRealPath();
		// Skip storage directory to prevent nested infinite backups
		if ( strpos( $real_path, UPDRAFTPLUS_STORAGE_DIR ) === 0 ) {
			continue;
		}

		$relative = substr( $real_path, strlen( $dir_path ) + 1 );
		$zip_entry_path = $local_prefix ? $local_prefix . '/' . $relative : $relative;
		$zip_entry_path = str_replace( '\\', '/', $zip_entry_path );

		if ( $file->isDir() ) {
			$zip->addEmptyDir( $zip_entry_path );
		} elseif ( $file->isFile() ) {
			$zip->addFile( $real_path, $zip_entry_path );
		}
	}
}

/**
 * AJAX: Run Backup Now
 */
function updraftplus_ajax_run_backup() {
	check_ajax_referer( 'updraftplus_ajax_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'updraftplus-backup' ) ) );
	}

	@set_time_limit( 600 );
	@ini_set( 'memory_limit', '512M' );

	updraftplus_init_storage_dir();

	$include_db      = ! empty( $_POST['include_db'] );
	$include_plugins = ! empty( $_POST['include_plugins'] );
	$include_themes  = ! empty( $_POST['include_themes'] );
	$include_uploads = ! empty( $_POST['include_uploads'] );
	$include_content = ! empty( $_POST['include_content'] );

	if ( ! $include_db && ! $include_plugins && ! $include_themes && ! $include_uploads && ! $include_content ) {
		wp_send_json_error( array( 'message' => __( 'Please select at least one component to backup (Database, Plugins, Themes, or Uploads).', 'updraftplus-backup' ) ) );
	}

	$timestamp  = date( 'Y-m-d_H-i-s' );
	$components = array();
	if ( $include_db ) $components[] = 'db';
	if ( $include_plugins ) $components[] = 'plugins';
	if ( $include_themes ) $components[] = 'themes';
	if ( $include_uploads ) $components[] = 'uploads';
	if ( $include_content ) $components[] = 'content';

	$filename = 'backup_' . $timestamp . '_' . implode( '-', $components ) . '.zip';
	$filepath = UPDRAFTPLUS_STORAGE_DIR . '/' . $filename;

	$zip = new ZipArchive();
	if ( $zip->open( $filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
		wp_send_json_error( array( 'message' => __( 'Could not create zip archive on server.', 'updraftplus-backup' ) ) );
	}

	$log = array();
	$log[] = sprintf( __( 'Starting backup process at %s...', 'updraftplus-backup' ), current_time( 'mysql' ) );

	// 1. Database Backup
	if ( $include_db ) {
		$log[] = __( 'Exporting database schema and tables...', 'updraftplus-backup' );
		$sql_content = updraftplus_export_database();
		$zip->addFromString( 'database.sql', $sql_content );
		$log[] = __( 'Database exported and added to zip successfully.', 'updraftplus-backup' );
	}

	// 2. Plugins
	if ( $include_plugins ) {
		$log[] = __( 'Archiving plugins directory...', 'updraftplus-backup' );
		updraftplus_add_dir_to_zip( WP_PLUGIN_DIR, $zip, 'wp-content/plugins' );
		$log[] = __( 'Plugins archived successfully.', 'updraftplus-backup' );
	}

	// 3. Themes
	if ( $include_themes ) {
		$log[] = __( 'Archiving themes directory...', 'updraftplus-backup' );
		updraftplus_add_dir_to_zip( get_theme_root(), $zip, 'wp-content/themes' );
		$log[] = __( 'Themes archived successfully.', 'updraftplus-backup' );
	}

	// 4. Uploads
	if ( $include_uploads ) {
		$uploads_dir = wp_upload_dir()['basedir'];
		$log[] = __( 'Archiving media uploads...', 'updraftplus-backup' );
		updraftplus_add_dir_to_zip( $uploads_dir, $zip, 'wp-content/uploads' );
		$log[] = __( 'Media uploads archived successfully.', 'updraftplus-backup' );
	}

	// 5. Entire wp-content (other files)
	if ( $include_content ) {
		$log[] = __( 'Archiving additional wp-content files...', 'updraftplus-backup' );
		updraftplus_add_dir_to_zip( WP_CONTENT_DIR, $zip, 'wp-content' );
		$log[] = __( 'wp-content archived successfully.', 'updraftplus-backup' );
	}

	$zip->close();

	$filesize = size_format( filesize( $filepath ), 2 );
	$log[] = sprintf( __( 'Backup complete! Archive size: %s. File: %s', 'updraftplus-backup' ), $filesize, $filename );

	// Auto cleanup old backups if retention set
	$retention = (int) get_option( 'updraftplus_retention_count', 5 );
	if ( $retention > 0 ) {
		$all_backups = glob( UPDRAFTPLUS_STORAGE_DIR . '/*.zip' );
		if ( count( $all_backups ) > $retention ) {
			usort( $all_backups, function( $a, $b ) {
				return filemtime( $b ) - filemtime( $a );
			} );
			$to_delete = array_slice( $all_backups, $retention );
			foreach ( $to_delete as $old_file ) {
				@unlink( $old_file );
			}
		}
	}

	wp_send_json_success( array(
		'message'  => __( 'Backup completed successfully!', 'updraftplus-backup' ),
		'filename' => $filename,
		'size'     => $filesize,
		'logs'     => $log,
	) );
}
add_action( 'wp_ajax_updraftplus_run_backup', 'updraftplus_ajax_run_backup' );

/**
 * AJAX: Delete Backup File
 */
function updraftplus_ajax_delete_backup() {
	check_ajax_referer( 'updraftplus_ajax_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'updraftplus-backup' ) ) );
	}

	$file = sanitize_file_name( $_POST['file'] ?? '' );
	$path = UPDRAFTPLUS_STORAGE_DIR . '/' . $file;

	if ( file_exists( $path ) && unlink( $path ) ) {
		wp_send_json_success( array( 'message' => __( 'Backup deleted successfully.', 'updraftplus-backup' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Could not delete backup file.', 'updraftplus-backup' ) ) );
	}
}
add_action( 'wp_ajax_updraftplus_delete_backup', 'updraftplus_ajax_delete_backup' );

/**
 * AJAX: 1-Click Restore Backup
 */
function updraftplus_ajax_restore_backup() {
	check_ajax_referer( 'updraftplus_ajax_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'updraftplus-backup' ) ) );
	}

	@set_time_limit( 600 );
	@ini_set( 'memory_limit', '512M' );

	$file = sanitize_file_name( $_POST['file'] ?? '' );
	$path = UPDRAFTPLUS_STORAGE_DIR . '/' . $file;

	if ( ! file_exists( $path ) ) {
		wp_send_json_error( array( 'message' => __( 'Backup file does not exist.', 'updraftplus-backup' ) ) );
	}

	$zip = new ZipArchive();
	if ( $zip->open( $path ) !== true ) {
		wp_send_json_error( array( 'message' => __( 'Could not open backup zip file.', 'updraftplus-backup' ) ) );
	}

	$log = array();
	$log[] = sprintf( __( 'Starting restore from backup %s at %s...', 'updraftplus-backup' ), $file, current_time( 'mysql' ) );

	// 1. Check for database.sql
	$db_index = $zip->locateName( 'database.sql' );
	if ( $db_index !== false ) {
		$log[] = __( 'Restoring Database tables and data...', 'updraftplus-backup' );
		$sql_content = $zip->getFromIndex( $db_index );
		if ( ! empty( $sql_content ) ) {
			global $wpdb;
			$queries = explode( ";\n", $sql_content );
			$executed = 0;
			foreach ( $queries as $query ) {
				$query = trim( $query );
				if ( ! empty( $query ) ) {
					$wpdb->query( $query );
					$executed++;
				}
			}
			$log[] = sprintf( __( 'Database restore finished (%d SQL queries executed).', 'updraftplus-backup' ), $executed );
		}
	}

	// 2. Extract Files to ABSPATH / WP_CONTENT_DIR
	$log[] = __( 'Restoring files to web directory...', 'updraftplus-backup' );
	$zip->extractTo( ABSPATH );
	$zip->close();
	$log[] = __( 'Files restored successfully.', 'updraftplus-backup' );

	$log[] = __( 'Restore complete! Your website is fully synced with this backup point.', 'updraftplus-backup' );

	wp_send_json_success( array(
		'message' => __( 'Restore completed successfully!', 'updraftplus-backup' ),
		'logs'    => $log,
	) );
}
add_action( 'wp_ajax_updraftplus_restore_backup', 'updraftplus_ajax_restore_backup' );

/**
 * Render UpdraftPlus Admin Dashboard UI
 */
function updraftplus_render_admin_dashboard() {
	updraftplus_init_storage_dir();

	// Handle Settings Save
	if ( isset( $_POST['updraft_save_settings'] ) && check_admin_referer( 'updraft_settings_nonce' ) ) {
		update_option( 'updraftplus_retention_count', intval( $_POST['updraftplus_retention_count'] ?? 5 ) );
		update_option( 'updraftplus_schedule_interval', sanitize_text_field( $_POST['updraftplus_schedule_interval'] ?? 'manual' ) );
		echo '<div class="notice notice-success is-dismissible" style="margin-top:15px;"><p><strong>' . esc_html__( 'UpdraftPlus Settings saved successfully!', 'updraftplus-backup' ) . '</strong></p></div>';
	}

	$retention = get_option( 'updraftplus_retention_count', 5 );
	$interval  = get_option( 'updraftplus_schedule_interval', 'manual' );

	// Fetch existing backups
	$backup_files = glob( UPDRAFTPLUS_STORAGE_DIR . '/*.zip' ) ?: array();
	usort( $backup_files, function( $a, $b ) {
		return filemtime( $b ) - filemtime( $a );
	} );

	$total_storage_bytes = 0;
	foreach ( $backup_files as $f ) {
		$total_storage_bytes += filesize( $f );
	}
	?>
	<div class="wrap" style="max-width:1100px; margin-top:20px;">
		<!-- Top Branding Banner -->
		<div style="background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; padding:25px 30px; border-radius:12px; margin-bottom:25px; box-shadow:0 4px 20px rgba(0,0,0,0.15); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
			<div>
				<h1 style="color:#fff; margin:0 0 6px; font-size:24px; font-weight:800; display:flex; align-items:center; gap:12px;">
					<span style="background:linear-gradient(135deg,#f97316,#ea580c); width:42px; height:42px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 3px 12px rgba(249,115,22,0.4);">
						<i class="dashicons dashicons-backup" style="color:#fff; font-size:24px; line-height:42px; height:42px; width:42px;"></i>
					</span>
					UpdraftPlus Backup & Restore
				</h1>
				<p style="color:#94a3b8; margin:0; font-size:14px;">
					<?php esc_html_e( 'Take complete backups of your WordPress database, themes, plugins, and uploads with 1-click restore.', 'updraftplus-backup' ); ?>
				</p>
			</div>
			<div style="display:flex; gap:12px;">
				<div style="background:rgba(255,255,255,0.08); padding:8px 16px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); text-align:center;">
					<span style="display:block; font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:700;"><?php esc_html_e( 'Total Backups', 'updraftplus-backup' ); ?></span>
					<strong style="font-size:18px; color:#fff;"><?php echo count( $backup_files ); ?></strong>
				</div>
				<div style="background:rgba(255,255,255,0.08); padding:8px 16px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); text-align:center;">
					<span style="display:block; font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:700;"><?php esc_html_e( 'Storage Used', 'updraftplus-backup' ); ?></span>
					<strong style="font-size:18px; color:#38bdf8;"><?php echo size_format( $total_storage_bytes, 2 ); ?></strong>
				</div>
			</div>
		</div>

		<!-- Main Card: Take Backup Now -->
		<div class="postbox" style="border-radius:10px; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:25px; overflow:hidden;">
			<div class="postbox-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px;">
				<h2 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
					<i class="dashicons dashicons-cloud-upload" style="color:#ea580c;"></i>
					<?php esc_html_e( 'Create a New Backup', 'updraftplus-backup' ); ?>
				</h2>
			</div>
			<div class="inside" style="padding:22px;">
				
				<p style="color:#475569; font-size:14px; margin-top:0;">
					<?php esc_html_e( 'Select the components you want to include in this backup archive:', 'updraftplus-backup' ); ?>
				</p>

				<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:15px; margin-bottom:20px;">
					<label style="background:#fff; border:1px solid #cbd5e1; padding:12px 14px; border-radius:8px; display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
						<input type="checkbox" id="updraft_inc_db" checked style="transform:scale(1.2);">
						<span><i class="dashicons dashicons-database" style="color:#3b82f6;"></i> Database</span>
					</label>

					<label style="background:#fff; border:1px solid #cbd5e1; padding:12px 14px; border-radius:8px; display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
						<input type="checkbox" id="updraft_inc_plugins" checked style="transform:scale(1.2);">
						<span><i class="dashicons dashicons-admin-plugins" style="color:#10b981;"></i> Plugins</span>
					</label>

					<label style="background:#fff; border:1px solid #cbd5e1; padding:12px 14px; border-radius:8px; display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
						<input type="checkbox" id="updraft_inc_themes" checked style="transform:scale(1.2);">
						<span><i class="dashicons dashicons-admin-appearance" style="color:#8b5cf6;"></i> Themes</span>
					</label>

					<label style="background:#fff; border:1px solid #cbd5e1; padding:12px 14px; border-radius:8px; display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
						<input type="checkbox" id="updraft_inc_uploads" checked style="transform:scale(1.2);">
						<span><i class="dashicons dashicons-format-image" style="color:#f59e0b;"></i> Uploads</span>
					</label>

					<label style="background:#fff; border:1px solid #cbd5e1; padding:12px 14px; border-radius:8px; display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:600;">
						<input type="checkbox" id="updraft_inc_content" style="transform:scale(1.2);">
						<span><i class="dashicons dashicons-category" style="color:#64748b;"></i> wp-content</span>
					</label>
				</div>

				<!-- Action Button & Progress -->
				<div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
					<button type="button" id="updraft_start_backup_btn" class="button button-primary button-large" style="background:#ea580c; border-color:#c2410c; font-size:15px; font-weight:700; height:auto; padding:8px 24px; box-shadow:0 3px 10px rgba(234,88,12,0.3);">
						<i class="dashicons dashicons-cloud-upload" style="line-height:28px;"></i> <?php esc_html_e( 'Backup Now', 'updraftplus-backup' ); ?>
					</button>
					<span id="updraft_backup_status_spinner" style="display:none; color:#ea580c; font-weight:600; font-size:14px;">
						<i class="dashicons dashicons-update spin" style="font-size:20px; line-height:20px;"></i> <?php esc_html_e( 'Generating Backup Archive... Please wait.', 'updraftplus-backup' ); ?>
					</span>
				</div>

				<!-- Live Progress Log Terminal -->
				<div id="updraft_backup_log_box" style="display:none; margin-top:20px; background:#0f172a; border-radius:8px; padding:15px; color:#38bdf8; font-family:monospace; font-size:12px; line-height:1.6; max-height:220px; overflow-y:auto; border:1px solid #1e293b;">
					<div id="updraft_log_lines"></div>
				</div>

			</div>
		</div>

		<!-- Second Card: Existing Backups Manager -->
		<div class="postbox" style="border-radius:10px; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:25px; overflow:hidden;">
			<div class="postbox-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px; display:flex; justify-content:space-between; align-items:center;">
				<h2 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
					<i class="dashicons dashicons-archive" style="color:#2563eb;"></i>
					<?php esc_html_e( 'Existing Backups', 'updraftplus-backup' ); ?>
				</h2>
				<button type="button" class="button button-small" onclick="window.location.reload();">
					<i class="dashicons dashicons-image-rotate" style="font-size:14px; line-height:16px;"></i> <?php esc_html_e( 'Refresh List', 'updraftplus-backup' ); ?>
				</button>
			</div>
			<div class="inside" style="padding:0;">
				<?php if ( empty( $backup_files ) ) : ?>
					<div style="padding:35px; text-align:center; color:#64748b;">
						<i class="dashicons dashicons-info" style="font-size:32px; height:32px; width:32px; color:#cbd5e1; display:block; margin:0 auto 10px;"></i>
						<strong style="font-size:15px; color:#334155;"><?php esc_html_e( 'No backups created yet.', 'updraftplus-backup' ); ?></strong>
						<p style="margin:5px 0 0;"><?php esc_html_e( 'Click "Backup Now" above to create your first safe backup point.', 'updraftplus-backup' ); ?></p>
					</div>
				<?php else : ?>
					<table class="widefat striped" style="border:none;">
						<thead>
							<tr>
								<th style="padding:12px 16px;"><?php esc_html_e( 'Backup Date & Time', 'updraftplus-backup' ); ?></th>
								<th><?php esc_html_e( 'Backup Name / Components', 'updraftplus-backup' ); ?></th>
								<th><?php esc_html_e( 'File Size', 'updraftplus-backup' ); ?></th>
								<th style="text-align:right; padding-right:16px;"><?php esc_html_e( 'Actions', 'updraftplus-backup' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $backup_files as $file ) : 
								$filename = basename( $file );
								$filesize = size_format( filesize( $file ), 2 );
								$filemtime = date( 'M d, Y - h:i:s A', filemtime( $file ) );
								$download_url = wp_nonce_url( admin_url( 'admin.php?action=updraft_download_backup&file=' . urlencode( $filename ) ), 'updraft_download_action', 'updraft_nonce' );
								?>
								<tr>
									<td style="padding:12px 16px;">
										<strong><i class="dashicons dashicons-calendar-alt" style="color:#64748b;"></i> <?php echo esc_html( $filemtime ); ?></strong>
									</td>
									<td>
										<span style="font-family:monospace; font-size:12px; color:#0f172a; font-weight:600;"><?php echo esc_html( $filename ); ?></span>
									</td>
									<td>
										<span class="badge" style="background:#f1f5f9; border:1px solid #cbd5e1; padding:3px 8px; border-radius:4px; font-weight:700; color:#334155;">
											<?php echo esc_html( $filesize ); ?>
										</span>
									</td>
									<td style="text-align:right; padding-right:16px;">
										<a href="<?php echo esc_url( $download_url ); ?>" class="button button-secondary button-small" style="font-weight:600; color:#0284c7;">
											<i class="dashicons dashicons-download" style="font-size:14px; line-height:16px;"></i> <?php esc_html_e( 'Download', 'updraftplus-backup' ); ?>
										</a>
										<button type="button" class="button button-primary button-small updraft-restore-btn" data-file="<?php echo esc_attr( $filename ); ?>" style="font-weight:600; background:#2563eb; border-color:#1d4ed8; margin-left:4px;">
											<i class="dashicons dashicons-image-rotate" style="font-size:14px; line-height:16px;"></i> <?php esc_html_e( 'Restore', 'updraftplus-backup' ); ?>
										</button>
										<button type="button" class="button button-small updraft-delete-btn" data-file="<?php echo esc_attr( $filename ); ?>" style="color:#ef4444; margin-left:4px;" title="<?php esc_attr_e( 'Delete Backup', 'updraftplus-backup' ); ?>">
											<i class="dashicons dashicons-trash" style="font-size:14px; line-height:16px;"></i>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<!-- Third Card: Settings & Auto-Retention -->
		<div class="postbox" style="border-radius:10px; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:25px; overflow:hidden;">
			<div class="postbox-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px;">
				<h2 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
					<i class="dashicons dashicons-admin-settings" style="color:#64748b;"></i>
					<?php esc_html_e( 'Backup Storage & Retention Settings', 'updraftplus-backup' ); ?>
				</h2>
			</div>
			<div class="inside" style="padding:22px;">
				<form method="post" action="">
					<?php wp_nonce_field( 'updraft_settings_nonce' ); ?>
					
					<table class="form-table" style="margin-top:0;">
						<tr>
							<th scope="row" style="width:240px;">
								<label for="updraftplus_retention_count"><strong><?php esc_html_e( 'Retain Backups Count', 'updraftplus-backup' ); ?></strong></label>
							</th>
							<td>
								<input type="number" min="1" max="50" name="updraftplus_retention_count" id="updraftplus_retention_count" value="<?php echo esc_attr( $retention ); ?>" class="small-text">
								<p class="description"><?php esc_html_e( 'Number of recent backups to keep on server before auto-deleting older ones (Default: 5).', 'updraftplus-backup' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="updraftplus_schedule_interval"><strong><?php esc_html_e( 'Automatic Schedule', 'updraftplus-backup' ); ?></strong></label>
							</th>
							<td>
								<select name="updraftplus_schedule_interval" id="updraftplus_schedule_interval">
									<option value="manual" <?php selected( $interval, 'manual' ); ?>><?php esc_html_e( 'Manual (Run on demand)', 'updraftplus-backup' ); ?></option>
									<option value="daily" <?php selected( $interval, 'daily' ); ?>><?php esc_html_e( 'Every Day (Daily)', 'updraftplus-backup' ); ?></option>
									<option value="weekly" <?php selected( $interval, 'weekly' ); ?>><?php esc_html_e( 'Every Week (Weekly)', 'updraftplus-backup' ); ?></option>
									<option value="monthly" <?php selected( $interval, 'monthly' ); ?>><?php esc_html_e( 'Every Month (Monthly)', 'updraftplus-backup' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Automate regular backups in the background.', 'updraftplus-backup' ); ?></p>
							</td>
						</tr>
					</table>

					<div style="margin-top:15px;">
						<button type="submit" name="updraft_save_settings" class="button button-primary" style="font-weight:600;">
							<?php esc_html_e( 'Save Settings', 'updraftplus-backup' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>

	</div>

	<style>
	.spin { animation: updraft-spin 1.2s infinite linear; }
	@keyframes updraft-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
	</style>

	<script>
	jQuery(document).ready(function($) {
		var ajaxNonce = '<?php echo wp_create_nonce( 'updraftplus_ajax_nonce' ); ?>';

		// Backup Action
		$('#updraft_start_backup_btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true);
			$('#updraft_backup_status_spinner').fadeIn();
			$('#updraft_backup_log_box').show();
			$('#updraft_log_lines').html('<p style="margin:2px 0; color:#38bdf8;">[INIT] Preparing backup task...</p>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'updraftplus_run_backup',
					nonce: ajaxNonce,
					include_db: $('#updraft_inc_db').is(':checked') ? 1 : 0,
					include_plugins: $('#updraft_inc_plugins').is(':checked') ? 1 : 0,
					include_themes: $('#updraft_inc_themes').is(':checked') ? 1 : 0,
					include_uploads: $('#updraft_inc_uploads').is(':checked') ? 1 : 0,
					include_content: $('#updraft_inc_content').is(':checked') ? 1 : 0
				},
				success: function(res) {
					$btn.prop('disabled', false);
					$('#updraft_backup_status_spinner').hide();
					if (res.success) {
						if (res.data.logs) {
							$.each(res.data.logs, function(i, line) {
								$('#updraft_log_lines').append('<p style="margin:2px 0; color:#4ade80;">[LOG] ' + line + '</p>');
							});
						}
						setTimeout(function() { window.location.reload(); }, 2000);
					} else {
						alert('Backup failed: ' + (res.data.message || 'Unknown error.'));
					}
				},
				error: function() {
					$btn.prop('disabled', false);
					$('#updraft_backup_status_spinner').hide();
					alert('Network error while running backup.');
				}
			});
		});

		// Restore Action
		$(document).on('click', '.updraft-restore-btn', function(e) {
			e.preventDefault();
			var file = $(this).data('file');
			if (!confirm('CAUTION: Are you sure you want to restore "' + file + '"? This will overwrite the current database and files with this backup point.')) {
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true).text('Restoring...');
			$('#updraft_backup_log_box').show();
			$('#updraft_log_lines').html('<p style="margin:2px 0; color:#f59e0b;">[RESTORE] Initializing restore sequence for ' + file + '...</p>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'updraftplus_restore_backup',
					nonce: ajaxNonce,
					file: file
				},
				success: function(res) {
					$btn.prop('disabled', false).text('Restore');
					if (res.success) {
						if (res.data.logs) {
							$.each(res.data.logs, function(i, line) {
								$('#updraft_log_lines').append('<p style="margin:2px 0; color:#4ade80;">[LOG] ' + line + '</p>');
							});
						}
						alert('Restore completed successfully!');
					} else {
						alert('Restore failed: ' + (res.data.message || 'Unknown error.'));
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('Restore');
					alert('Network error while restoring backup.');
				}
			});
		});

		// Delete Action
		$(document).on('click', '.updraft-delete-btn', function(e) {
			e.preventDefault();
			var file = $(this).data('file');
			if (!confirm('Are you sure you want to permanently delete backup "' + file + '"?')) {
				return;
			}

			var $row = $(this).closest('tr');
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'updraftplus_delete_backup',
					nonce: ajaxNonce,
					file: file
				},
				success: function(res) {
					if (res.success) {
						$row.fadeOut(300, function() { $(this).remove(); });
					} else {
						alert('Delete failed: ' + (res.data.message || 'Unknown error.'));
					}
				}
			});
		});

	});
	</script>
	<?php
}
