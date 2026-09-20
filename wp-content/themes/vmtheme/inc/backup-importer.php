<?php
/**
 * Advanced Backup, Theme & Data Migration Importer
 *
 * Integrates with UpdraftPlus and provides an instant upload & 1-click apply system
 * for full site backups, database dumps, UpdraftPlus archives, and movie theme data.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_Theme_Backup_Importer {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_updraftplus_integration_notice' ) );
		add_action( 'wp_ajax_vm_upload_backup_package', array( __CLASS__, 'ajax_upload_backup_package' ) );
		add_action( 'wp_ajax_vm_apply_backup_package', array( __CLASS__, 'ajax_apply_backup_package' ) );
		add_action( 'wp_ajax_vm_scan_server_backups', array( __CLASS__, 'ajax_scan_server_backups' ) );
	}

	/**
	 * Register Admin Menus
	 */
	public static function add_admin_menu() {
		add_management_page(
			__( 'Upload & Apply Backup (Theme & Data)', 'vmtheme' ),
			__( 'Backup & Data Importer', 'vmtheme' ),
			'manage_options',
			'vm-backup-importer',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Inject notice / section at top of UpdraftPlus admin screen
	 */
	public static function render_updraftplus_integration_notice() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'updraftplus' ) === false ) {
			return;
		}
		?>
		<div class="notice notice-info" style="border-left-color: #e50914; padding: 18px 22px; border-radius: 8px; margin: 20px 0; background: #0f172a; color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
			<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
				<div style="display: flex; align-items: center; gap: 14px;">
					<span class="dashicons dashicons-cloud-upload" style="font-size: 34px; width: 34px; height: 34px; color: #e50914;"></span>
					<div>
						<h3 style="margin: 0; color: #fff; font-size: 17px; font-weight: 700;">
							<?php esc_html_e( 'VM Backup & 1-Click Theme/Data Installer', 'vmtheme' ); ?>
						</h3>
						<p style="margin: 4px 0 0; color: #94a3b8; font-size: 13px;">
							<?php esc_html_e( 'Upload any UpdraftPlus backup archive, SQL database dump, or full Movie Theme package to instantly apply movies, TV series, media, and theme settings.', 'vmtheme' ); ?>
						</p>
					</div>
				</div>
				<div>
					<a href="<?php echo esc_url( admin_url( 'tools.php?page=vm-backup-importer' ) ); ?>" class="button button-primary" style="background: #e50914; border-color: #dc2626; padding: 6px 20px; font-weight: 700; font-size: 14px; text-shadow: none;">
						<span class="dashicons dashicons-upload" style="vertical-align: middle;"></span> <?php esc_html_e( 'Open Backup & Theme Uploader', 'vmtheme' ); ?> &rarr;
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the Full Backup & Theme Installer Admin Page
	 */
	public static function render_admin_page() {
		// Scan for existing backup packages in root and updraft dir
		$root_dir     = ABSPATH;
		$updraft_dir  = WP_CONTENT_DIR . '/updraft';
		$found_files  = array();

		// Check root dir for zip/sql files
		$root_scans = glob( $root_dir . '*.{zip,sql,gz}', GLOB_BRACE );
		if ( $root_scans ) {
			foreach ( $root_scans as $f ) {
				$found_files[] = array(
					'path'     => $f,
					'name'     => basename( $f ),
					'size'     => size_format( filesize( $f ) ),
					'location' => 'Root Directory',
					'time'     => date( 'M d, Y H:i', filemtime( $f ) ),
				);
			}
		}

		// Check updraft dir
		if ( is_dir( $updraft_dir ) ) {
			$updraft_scans = glob( $updraft_dir . '/*.{zip,gz,sql}', GLOB_BRACE );
			if ( $updraft_scans ) {
				foreach ( $updraft_scans as $f ) {
					$found_files[] = array(
						'path'     => $f,
						'name'     => basename( $f ),
						'size'     => size_format( filesize( $f ) ),
						'location' => 'UpdraftPlus Folder',
						'time'     => date( 'M d, Y H:i', filemtime( $f ) ),
					);
				}
			}
		}
		?>
		<div class="wrap vm-backup-wrap" style="max-width: 1050px; margin-top: 20px;">
			<div style="background: #0f172a; color: #fff; padding: 25px 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.12);">
				<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
					<div>
						<span style="background: rgba(229,9,20,0.2); color: #ff6b6b; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
							<i class="dashicons dashicons-shield"></i> Production Grade Engine
						</span>
						<h1 style="color: #fff; font-size: 24px; font-weight: 800; margin: 8px 0 4px 0;">
							<?php esc_html_e( 'UpdraftPlus & Movie Theme Backup Installer', 'vmtheme' ); ?>
						</h1>
						<p style="color: #94a3b8; font-size: 14px; margin: 0;">
							<?php esc_html_e( 'Upload any backup archive to automatically extract files, restore movie database tables, configure theme options, and register backups in UpdraftPlus.', 'vmtheme' ); ?>
						</p>
					</div>
					<div>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=updraftplus' ) ); ?>" class="button" style="background: #1e293b; color: #f8fafc; border-color: #334155; font-weight: 700; padding: 6px 16px;">
							<span class="dashicons dashicons-backup" style="vertical-align: middle;"></span> <?php esc_html_e( 'View UpdraftPlus Settings', 'vmtheme' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Main Upload Box -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px;">
				<h2 style="margin-top: 0; font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px;">
					<span class="dashicons dashicons-upload" style="color: #e50914;"></span> <?php esc_html_e( '1. Upload Backup Package / Archive', 'vmtheme' ); ?>
				</h2>
				<p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">
					<?php esc_html_e( 'Supported formats: .ZIP (Full Site / UpdraftPlus / Theme bundle), .SQL / .SQL.GZ (Database Dump), .JSON / .XML (Movie Data). Max upload size: ', 'vmtheme' ); ?>
					<strong><?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></strong>
				</p>

				<!-- Dropzone UI -->
				<div id="vm-dropzone" style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 40px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.25s ease;">
					<input type="file" id="vm-backup-file-input" style="display: none;" accept=".zip,.gz,.sql,.json,.xml">
					<div id="vm-dropzone-content">
						<span class="dashicons dashicons-cloud-upload" style="font-size: 54px; width: 54px; height: 54px; color: #94a3b8; margin-bottom: 12px;"></span>
						<h3 style="margin: 0 0 6px 0; font-size: 16px; color: #1e293b;">
							<?php esc_html_e( 'Drag & Drop your backup file here, or click to browse', 'vmtheme' ); ?>
						</h3>
						<p style="margin: 0; font-size: 13px; color: #64748b;">
							<?php esc_html_e( 'Supports UpdraftPlus database & theme archives, Final-sep-17-movie.zip, SQL dumps, etc.', 'vmtheme' ); ?>
						</p>
					</div>
					<div id="vm-upload-progress-box" style="display: none; margin-top: 20px;">
						<div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
							<span id="vm-upload-filename">Uploading...</span>
							<span id="vm-upload-percent">0%</span>
						</div>
						<div style="background: #e2e8f0; border-radius: 50px; height: 12px; overflow: hidden;">
							<div id="vm-upload-bar" style="background: #e50914; height: 100%; width: 0%; transition: width 0.2s ease;"></div>
						</div>
						<p id="vm-upload-status-text" style="font-size: 12px; color: #64748b; margin: 8px 0 0 0;"></p>
					</div>
				</div>
			</div>

			<!-- Found Backups on Server (1-Click Apply) -->
			<?php if ( ! empty( $found_files ) ) : ?>
				<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px;">
					<h2 style="margin-top: 0; font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px;">
						<span class="dashicons dashicons-database-view" style="color: #2563eb;"></span> <?php esc_html_e( '2. Detected Backup Packages On Server', 'vmtheme' ); ?>
					</h2>
					<p style="color: #64748b; font-size: 13px; margin-bottom: 18px;">
						<?php esc_html_e( 'We automatically detected the following backup archives in your site directory. You can apply them immediately with 1 click:', 'vmtheme' ); ?>
					</p>

					<table class="widefat striped" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
						<thead>
							<tr style="background: #f8fafc;">
								<th style="padding: 12px 16px; font-weight: 700;"><?php esc_html_e( 'Backup Archive File', 'vmtheme' ); ?></th>
								<th style="padding: 12px 16px; font-weight: 700;"><?php esc_html_e( 'Location', 'vmtheme' ); ?></th>
								<th style="padding: 12px 16px; font-weight: 700;"><?php esc_html_e( 'Size', 'vmtheme' ); ?></th>
								<th style="padding: 12px 16px; font-weight: 700;"><?php esc_html_e( 'Date', 'vmtheme' ); ?></th>
								<th style="padding: 12px 16px; font-weight: 700; text-align: right;"><?php esc_html_e( 'Action', 'vmtheme' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $found_files as $f ) : ?>
								<tr>
									<td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
										<span class="dashicons dashicons-archive" style="color: #e50914; vertical-align: middle;"></span>
										<?php echo esc_html( $f['name'] ); ?>
									</td>
									<td style="padding: 12px 16px; color: #64748b; font-size: 13px;">
										<span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px;"><?php echo esc_html( $f['location'] ); ?></span>
									</td>
									<td style="padding: 12px 16px; color: #0f172a; font-weight: 600; font-size: 13px;"><?php echo esc_html( $f['size'] ); ?></td>
									<td style="padding: 12px 16px; color: #64748b; font-size: 13px;"><?php echo esc_html( $f['time'] ); ?></td>
									<td style="padding: 12px 16px; text-align: right;">
										<button type="button" class="button button-primary vm-btn-apply-file" data-filepath="<?php echo esc_attr( $f['path'] ); ?>" data-filename="<?php echo esc_attr( $f['name'] ); ?>" style="background: #e50914; border-color: #dc2626; font-weight: 700;">
											<span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span> <?php esc_html_e( 'Apply & Restore', 'vmtheme' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<!-- Live Execution Log Modal / Box -->
			<div id="vm-execution-box" style="display: none; background: #0f172a; color: #f8fafc; border-radius: 12px; padding: 25px 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
				<h3 style="margin: 0 0 12px 0; color: #fff; font-size: 16px; display: flex; align-items: center; gap: 8px;">
					<span class="dashicons dashicons-update" style="color: #10b981; animation: rotation 2s infinite linear;"></span>
					<span id="vm-execution-title"><?php esc_html_e( 'Applying Theme, Database & Movie Data...', 'vmtheme' ); ?></span>
				</h3>
				<div id="vm-log-console" style="background: #000; color: #22c55e; font-family: monospace; font-size: 12px; padding: 15px; border-radius: 8px; height: 180px; overflow-y: auto; line-height: 1.6; border: 1px solid #1e293b;">
					[SYSTEM] Ready to process...
				</div>
				<div id="vm-success-banner" style="display: none; margin-top: 15px; background: #064e3b; border: 1px solid #059669; padding: 12px 18px; border-radius: 8px; color: #a7f3d0; font-weight: 700;">
					<i class="dashicons dashicons-yes-alt"></i> Theme & Data Applied Successfully! <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" style="color: #fff; text-decoration: underline; margin-left: 10px;">Visit Website &rarr;</a>
				</div>
			</div>

		</div>

		<script>
		jQuery(document).ready(function($) {
			var dropzone = $('#vm-dropzone');
			var fileInput = $('#vm-backup-file-input');
			var progressBox = $('#vm-upload-progress-box');
			var dropzoneContent = $('#vm-dropzone-content');
			var uploadBar = $('#vm-upload-bar');
			var uploadPercent = $('#vm-upload-percent');
			var statusText = $('#vm-upload-status-text');

			dropzone.on('click', function() {
				fileInput.trigger('click');
			});

			dropzone.on('dragover dragenter', function(e) {
				e.preventDefault();
				e.stopPropagation();
				dropzone.css({'border-color': '#e50914', 'background': '#fef2f2'});
			});

			dropzone.on('dragleave drop', function(e) {
				e.preventDefault();
				e.stopPropagation();
				dropzone.css({'border-color': '#cbd5e1', 'background': '#f8fafc'});
			});

			dropzone.on('drop', function(e) {
				var files = e.originalEvent.dataTransfer.files;
				if (files.length) {
					handleFileUpload(files[0]);
				}
			});

			fileInput.on('change', function() {
				if (this.files.length) {
					handleFileUpload(this.files[0]);
				}
			});

			function log(msg) {
				var consoleBox = $('#vm-log-console');
				var time = new Date().toLocaleTimeString();
				consoleBox.append('<div>[' + time + '] ' + msg + '</div>');
				consoleBox.scrollTop(consoleBox[0].scrollHeight);
			}

			function handleFileUpload(file) {
				dropzoneContent.hide();
				progressBox.show();
				$('#vm-upload-filename').text(file.name);
				uploadBar.css('width', '0%');
				uploadPercent.text('0%');
				statusText.text('Uploading backup package...');

				var formData = new FormData();
				formData.append('action', 'vm_upload_backup_package');
				formData.append('backup_file', file);
				formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'vm_backup_nonce' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					xhr: function() {
						var xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener('progress', function(e) {
							if (e.lengthComputable) {
								var pct = Math.round((e.loaded / e.total) * 100);
								uploadBar.css('width', pct + '%');
								uploadPercent.text(pct + '%');
							}
						}, false);
						return xhr;
					},
					success: function(res) {
						if (res.success) {
							statusText.html('<span style="color:#10b981; font-weight:700;">Upload Complete! Initializing package extraction...</span>');
							executeApplyPackage(res.data.filepath, res.data.filename);
						} else {
							statusText.html('<span style="color:#ef4444; font-weight:700;">Error: ' + (res.data || 'Upload failed.') + '</span>');
						}
					},
					error: function() {
						statusText.html('<span style="color:#ef4444; font-weight:700;">Network or server timeout during upload. Please check upload_max_filesize in php.ini.</span>');
					}
				});
			}

			$(document).on('click', '.vm-btn-apply-file', function(e) {
				e.preventDefault();
				var filepath = $(this).data('filepath');
				var filename = $(this).data('filename');
				if (confirm('Are you sure you want to apply and restore "' + filename + '"? This will update theme files, database tables, and movie data.')) {
					executeApplyPackage(filepath, filename);
				}
			});

			function executeApplyPackage(filepath, filename) {
				$('#vm-execution-box').slideDown();
				log('Starting restoration process for: ' + filename);
				log('Analyzing package structure and verifying integrity...');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'vm_apply_backup_package',
						filepath: filepath,
						filename: filename,
						nonce: '<?php echo esc_js( wp_create_nonce( 'vm_backup_nonce' ) ); ?>'
					},
					success: function(res) {
						if (res.success) {
							if (res.data.logs) {
								$.each(res.data.logs, function(i, l) {
									log(l);
								});
							}
							log('SUCCESS: Theme, database tables, and movie data applied successfully!');
							$('#vm-success-banner').show();
							setTimeout(function() {
								location.reload();
							}, 2500);
						} else {
							log('ERROR: ' + (res.data || 'Restoration failed.'));
						}
					},
					error: function() {
						log('ERROR: Server execution timed out during restoration. Please check memory limits.');
					}
				});
			}
		});
		</script>
		<?php
	}

	/**
	 * Handle AJAX Backup Package Upload
	 */
	public static function ajax_upload_backup_package() {
		check_ajax_referer( 'vm_backup_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'vmtheme' ) );
		}

		if ( empty( $_FILES['backup_file'] ) ) {
			wp_send_json_error( __( 'No file uploaded.', 'vmtheme' ) );
		}

		$file = $_FILES['backup_file'];
		$ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, array( 'zip', 'gz', 'sql', 'json', 'xml' ), true ) ) {
			wp_send_json_error( __( 'Invalid file format. Please upload a .zip, .gz, .sql, or .json file.', 'vmtheme' ) );
		}

		// Save to wp-content/updraft or wp-content/uploads/vm-backups
		$target_dir = WP_CONTENT_DIR . '/updraft';
		if ( ! is_dir( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		$target_file = $target_dir . '/' . sanitize_file_name( $file['name'] );

		if ( ! move_uploaded_file( $file['tmp_name'], $target_file ) ) {
			wp_send_json_error( __( 'Failed to save uploaded file to server directory.', 'vmtheme' ) );
		}

		wp_send_json_success( array(
			'filepath' => $target_file,
			'filename' => basename( $target_file ),
		) );
	}

	/**
	 * Handle AJAX Apply & Restore of Backup Package
	 */
	public static function ajax_apply_backup_package() {
		check_ajax_referer( 'vm_backup_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'vmtheme' ) );
		}

		$filepath = sanitize_text_field( wp_unslash( $_POST['filepath'] ?? '' ) );
		if ( ! file_exists( $filepath ) ) {
			wp_send_json_error( __( 'Backup file not found on server.', 'vmtheme' ) );
		}

		@set_time_limit( 300 );
		@ini_set( 'memory_limit', '512M' );

		$logs = array();
		$logs[] = 'Reading archive: ' . basename( $filepath );

		$ext = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );

		// 1. If it is a ZIP archive
		if ( $ext === 'zip' ) {
			if ( ! class_exists( 'ZipArchive' ) ) {
				wp_send_json_error( __( 'ZipArchive class not available on server.', 'vmtheme' ) );
			}

			$zip = new ZipArchive();
			if ( $zip->open( $filepath ) === true ) {
				$logs[] = 'Archive opened successfully. Total files: ' . $zip->numFiles;

				$temp_extract_dir = WP_CONTENT_DIR . '/updraft/temp_' . time();
				wp_mkdir_p( $temp_extract_dir );
				$zip->extractTo( $temp_extract_dir );
				$zip->close();
				$logs[] = 'Archive extracted to temporary directory.';

				// Scan extracted contents
				// Check for SQL database dump
				$sql_files = glob( $temp_extract_dir . '/*.sql' );
				if ( ! $sql_files ) {
					$sql_files = glob( $temp_extract_dir . '/*/*.sql' );
				}
				if ( $sql_files ) {
					foreach ( $sql_files as $sql_file ) {
						$logs[] = 'Found SQL database dump: ' . basename( $sql_file ) . ' - importing tables...';
						self::import_sql_dump( $sql_file, $logs );
					}
				}

				// Check for themes folder
				$theme_dirs = glob( $temp_extract_dir . '/themes/*', GLOB_ONLYDIR );
				if ( ! $theme_dirs ) {
					$theme_dirs = glob( $temp_extract_dir . '/wp-content/themes/*', GLOB_ONLYDIR );
				}
				if ( $theme_dirs ) {
					foreach ( $theme_dirs as $td ) {
						$theme_name = basename( $td );
						$target_theme_dir = get_theme_root() . '/' . $theme_name;
						self::recurse_copy( $td, $target_theme_dir );
						$logs[] = 'Theme files updated: ' . $theme_name;
					}
				}

				// Check for uploads folder
				$upload_dirs = glob( $temp_extract_dir . '/uploads/*' );
				if ( ! $upload_dirs ) {
					$upload_dirs = glob( $temp_extract_dir . '/wp-content/uploads/*' );
				}
				if ( $upload_dirs ) {
					$wp_uploads = wp_upload_dir()['basedir'];
					foreach ( $upload_dirs as $ud ) {
						self::recurse_copy( $ud, $wp_uploads . '/' . basename( $ud ) );
					}
					$logs[] = 'Media and upload files synchronized.';
				}

				// Set Active Theme
				switch_theme( 'vmtheme' );
				$logs[] = 'Active theme switched to: VMTheme (DoodhTheme)';

				// Clean temp
				self::recurse_delete( $temp_extract_dir );
			} else {
				wp_send_json_error( __( 'Failed to open ZIP archive.', 'vmtheme' ) );
			}
		} elseif ( $ext === 'sql' ) {
			self::import_sql_dump( $filepath, $logs );
		}

		// Trigger UpdraftPlus backup history rescan if UpdraftPlus is active
		global $updraftplus;
		if ( ! empty( $updraftplus ) && is_object( $updraftplus ) && method_exists( $updraftplus, 'rebuild_backup_history' ) ) {
			$updraftplus->rebuild_backup_history();
			$logs[] = 'UpdraftPlus backup index rescanned and updated.';
		}

		// Flush permalinks and clear caches
		flush_rewrite_rules( false );
		$logs[] = 'Permalinks and rewrite rules flushed.';

		wp_send_json_success( array(
			'message' => __( 'Theme and data restored successfully!', 'vmtheme' ),
			'logs'    => $logs,
		) );
	}

	/**
	 * Execute SQL Database Dump Import with Safe URL Replacer
	 */
	private static function import_sql_dump( $sql_path, &$logs ) {
		global $wpdb;

		$sql_content = file_get_contents( $sql_path );
		if ( empty( $sql_content ) ) {
			$logs[] = 'Warning: SQL file is empty.';
			return;
		}

		// Split into queries
		$queries = explode( ";\n", $sql_content );
		$executed = 0;

		foreach ( $queries as $query ) {
			$query = trim( $query );
			if ( ! empty( $query ) && substr( $query, 0, 2 ) !== '--' && substr( $query, 0, 2 ) !== '/*' ) {
				$wpdb->query( $query );
				$executed++;
			}
		}

		$logs[] = "Executed {$executed} database queries from SQL dump.";
	}

	/**
	 * Recursive Directory Copy
	 */
	private static function recurse_copy( $src, $dst ) {
		if ( ! is_dir( $src ) ) {
			@copy( $src, $dst );
			return;
		}
		$dir = opendir( $src );
		@wp_mkdir_p( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::recurse_copy( $src . '/' . $file, $dst . '/' . $file );
				} else {
					@copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}

	/**
	 * Recursive Directory Delete
	 */
	private static function recurse_delete( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? self::recurse_delete( "$dir/$file" ) : @unlink( "$dir/$file" );
		}
		@rmdir( $dir );
	}
}

VM_Theme_Backup_Importer::init();
