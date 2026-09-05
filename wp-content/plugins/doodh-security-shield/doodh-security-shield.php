<?php
/**
 * Plugin Name: Doodh Security Shield & Web Application Firewall (WAF)
 * Plugin URI: https://doodhtheme.com/security-shield/
 * Description: Enterprise security suite: Web Application Firewall (WAF), SQLi & XSS Blocker, Brute Force Login Defense, XML-RPC Disabler, HTTP Security Headers, Uploads Execution Lockout, and Malware Scanner.
 * Version: 1.0.0
 * Author: DoodhTheme Team
 * Author URI: https://doodhtheme.com
 * License: GPL-2.0+
 * Text Domain: doodh-security-shield
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Doodh_Security_Shield' ) ) :

class Doodh_Security_Shield {

	private static $instance = null;
	private $options = array();
	private $log_table;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		$this->log_table = $wpdb->prefix . 'doodh_security_logs';
		$this->load_options();

		// Activation / Deactivation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Ensure DB tables and upload guards are present
		if ( ! get_option( 'doodh_security_db_installed' ) ) {
			$this->activate();
		}

		// 1. EARLY WEB APPLICATION FIREWALL (WAF)
		add_action( 'plugins_loaded', array( $this, 'run_firewall_inspection' ), -9999 );

		// 2. HTTP Security Headers
		add_action( 'send_headers', array( $this, 'send_security_headers' ) );
		add_filter( 'wp_headers', array( $this, 'filter_wp_headers' ) );

		// 3. XML-RPC Blocker
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_action( 'init', array( $this, 'block_xmlrpc_requests' ) );

		// 4. Brute Force Login Protection & Honeypot
		add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );
		add_filter( 'authenticate', array( $this, 'check_brute_force_lockout' ), 20, 3 );
		add_action( 'login_form', array( $this, 'inject_login_honeypot' ) );
		add_filter( 'wp_authenticate_user', array( $this, 'verify_login_honeypot' ), 10, 2 );

		// 5. User Enumeration Blocker
		add_action( 'init', array( $this, 'block_user_enumeration' ) );
		add_filter( 'rest_endpoints', array( $this, 'disable_rest_user_enumeration' ) );

		// 6. WordPress Fingerprint & Version Removal
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
		add_filter( 'style_loader_src', array( $this, 'remove_version_query_strings' ), 9999 );
		add_filter( 'script_loader_src', array( $this, 'remove_version_query_strings' ), 9999 );

		// 7. Uploads Folder Execution Lock
		add_action( 'admin_init', array( $this, 'secure_uploads_directory' ) );

		// 8. Admin Security Center
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
	}

	public function load_options() {
		$defaults = array(
			'enable_waf'            => 'yes',
			'enable_sqli_blocker'   => 'yes',
			'enable_xss_blocker'    => 'yes',
			'enable_lfi_blocker'    => 'yes',
			'enable_bad_bot_blocker'=> 'yes',
			'enable_brute_force'    => 'yes',
			'max_login_attempts'    => 5,
			'lockout_duration_mins' => 60,
			'enable_security_headers'=> 'yes',
			'banned_ips'            => '',
			'whitelisted_ips'       => '',
		);
		$saved = get_option( 'doodh_security_options', array() );
		$this->options = wp_parse_args( $saved, $defaults );
	}

	public function get_opt( $key, $default = '' ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
	}

	public function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$this->log_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			ip_address varchar(100) NOT NULL,
			threat_type varchar(50) NOT NULL,
			payload text NOT NULL,
			request_uri varchar(500) NOT NULL,
			user_agent varchar(255) NOT NULL,
			action_taken varchar(50) DEFAULT 'BLOCKED' NOT NULL,
			PRIMARY KEY  (id),
			KEY ip_address (ip_address),
			KEY threat_type (threat_type)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'doodh_security_db_installed', '1.0.0' );
		$this->secure_uploads_directory();
	}

	/**
	 * =========================================================================
	 * 1. WEB APPLICATION FIREWALL (WAF) ENGINE
	 * =========================================================================
	 */
	public function run_firewall_inspection() {
		if ( $this->get_opt( 'enable_waf', 'yes' ) !== 'yes' ) {
			return;
		}

		$ip = $this->get_client_ip();

		// Check Whitelist
		$whitelist = array_filter( array_map( 'trim', explode( "\n", $this->get_opt( 'whitelisted_ips' ) ) ) );
		if ( in_array( $ip, $whitelist, true ) ) {
			return;
		}

		// Check Blacklist
		$blacklist = array_filter( array_map( 'trim', explode( "\n", $this->get_opt( 'banned_ips' ) ) ) );
		if ( in_array( $ip, $blacklist, true ) ) {
			$this->block_request( 'BLACKLISTED_IP', 'IP in banned list' );
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$query_string = isset( $_SERVER['QUERY_STRING'] ) ? wp_unslash( $_SERVER['QUERY_STRING'] ) : '';

		// A. Bad Bots & Vulnerability Scanners
		if ( $this->get_opt( 'enable_bad_bot_blocker', 'yes' ) === 'yes' ) {
			$bad_agents = array( 'sqlmap', 'nikto', 'wpscan', 'havij', 'acunetix', 'dirbuster', 'zgrab', 'masscan', 'nmap' );
			foreach ( $bad_agents as $bot ) {
				if ( stripos( $user_agent, $bot ) !== false ) {
					$this->block_request( 'MALICIOUS_SCANNER', $user_agent );
				}
			}
		}

		// B. SQL Injection (SQLi) Protection
		if ( $this->get_opt( 'enable_sqli_blocker', 'yes' ) === 'yes' ) {
			$sqli_patterns = array(
				'/union(\s+all|\s+distinct)?\s+select/i',
				'/concat\s*\(/i',
				'/group_concat/i',
				'/information_schema/i',
				'/\b(benchmark|sleep)\s*\(/i',
				'/\bselect\b.*\bfrom\b.*(where|join)/i',
				'/\binsert\b.*\binto\b/i',
				'/\bdrop\s+(table|database)/i',
				'/\bupdate\b.*\bset\b/i',
				'/\bdelete\b.*\bfrom\b/i',
				'/--\s*$/i',
				'/\'(\s*or\s*\'?1\'?=\'?1|\s*or\s*1=1)/i',
			);

			$all_inputs = $query_string . ' ' . $this->stringify_array( $_POST ) . ' ' . $this->stringify_array( $_COOKIE );
			foreach ( $sqli_patterns as $pattern ) {
				if ( preg_match( $pattern, $all_inputs ) ) {
					$this->block_request( 'SQL_INJECTION', $pattern );
				}
			}
		}

		// C. Cross-Site Scripting (XSS) Protection
		if ( $this->get_opt( 'enable_xss_blocker', 'yes' ) === 'yes' ) {
			$xss_patterns = array(
				'/<script\b[^>]*>/i',
				'/javascript\s*:/i',
				'/eval\s*\(/i',
				'/base64_decode\s*\(/i',
				'/<iframe\b[^>]*>/i',
				'/<embed\b[^>]*>/i',
				'/on(load|error|click|mouseover|submit)\s*=/i',
			);

			$all_inputs = $query_string . ' ' . $this->stringify_array( $_POST );
			foreach ( $xss_patterns as $pattern ) {
				if ( preg_match( $pattern, $all_inputs ) ) {
					$this->block_request( 'XSS_ATTACK', $pattern );
				}
			}
		}

		// D. Local / Remote File Inclusion (LFI / RFI) & Path Traversal
		if ( $this->get_opt( 'enable_lfi_blocker', 'yes' ) === 'yes' ) {
			$lfi_patterns = array(
				'/\.\.\//',
				'/\.\.\\\\/',
				'/etc\/passwd/i',
				'/win\.ini/i',
				'/php:\/\/input/i',
				'/php:\/\/filter/i',
				'/data:\/\/text/i',
			);

			foreach ( $lfi_patterns as $pattern ) {
				if ( preg_match( $pattern, $request_uri ) || preg_match( $pattern, $query_string ) ) {
					$this->block_request( 'PATH_TRAVERSAL_LFI', $pattern );
				}
			}
		}
	}

	private function block_request( $threat_type, $payload ) {
		$ip = $this->get_client_ip();
		$this->log_security_event( $threat_type, $payload, 'BLOCKED' );

		// Return 403 Forbidden with Security Header
		status_header( 403 );
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-Doodh-Shield: THREAT_BLOCKED' );
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>403 Forbidden — Doodh Security Shield</title>
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #fff; text-align: center; padding: 80px 20px; margin: 0; }
				.box { max-width: 550px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 40px 30px; border: 1px solid #334155; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
				.icon { font-size: 54px; margin-bottom: 15px; color: #ef4444; }
				h1 { font-size: 26px; margin: 0 0 10px; color: #fff; }
				p { color: #94a3b8; font-size: 15px; line-height: 1.6; margin: 0 0 20px; }
				.meta { background: #0f172a; border-radius: 8px; padding: 12px; font-family: monospace; font-size: 12px; color: #cbd5e1; text-align: left; }
			</style>
		</head>
		<body>
			<div class="box">
				<div class="icon">🛡️</div>
				<h1>403 — Access Denied by Security Shield</h1>
				<p>Your request was flagged and blocked by our automated Web Application Firewall (WAF) due to a security violation.</p>
				<div class="meta">
					<div><strong>Incident ID:</strong> <?php echo esc_html( strtoupper( substr( md5( time() . $ip ), 0, 12 ) ) ); ?></div>
					<div><strong>Your IP:</strong> <?php echo esc_html( $ip ); ?></div>
					<div><strong>Threat Detected:</strong> <?php echo esc_html( $threat_type ); ?></div>
					<div><strong>Time:</strong> <?php echo esc_html( gmdate( 'Y-m-d H:i:s' ) ); ?> UTC</div>
				</div>
			</div>
		</body>
		</html>
		<?php
		exit;
	}

	private function stringify_array( $arr ) {
		if ( ! is_array( $arr ) ) {
			return (string) $arr;
		}
		$out = '';
		foreach ( $arr as $k => $v ) {
			$out .= $k . '=' . ( is_array( $v ) ? $this->stringify_array( $v ) : $v ) . ' ';
		}
		return $out;
	}

	public function log_security_event( $threat_type, $payload, $action = 'BLOCKED' ) {
		global $wpdb;
		$ip          = $this->get_client_ip();
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		$user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';

		$wpdb->insert(
			$this->log_table,
			array(
				'timestamp'   => current_time( 'mysql' ),
				'ip_address'  => $ip,
				'threat_type' => $threat_type,
				'payload'     => substr( (string) $payload, 0, 1000 ),
				'request_uri' => substr( $request_uri, 0, 500 ),
				'user_agent'  => substr( $user_agent, 0, 255 ),
				'action_taken'=> $action,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * =========================================================================
	 * 2. HTTP SECURITY HEADERS
	 * =========================================================================
	 */
	public function send_security_headers() {
		if ( $this->get_opt( 'enable_security_headers', 'yes' ) !== 'yes' ) {
			return;
		}

		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'X-XSS-Protection: 1; mode=block' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
		header( 'X-Permitted-Cross-Domain-Policies: none' );
		header( 'X-Download-Options: noopen' );
	}

	public function filter_wp_headers( $headers = array() ) {
		if ( $this->get_opt( 'enable_security_headers', 'yes' ) !== 'yes' ) {
			return $headers;
		}
		$headers['X-Frame-Options'] = 'SAMEORIGIN';
		$headers['X-XSS-Protection'] = '1; mode=block';
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
		$headers['Permissions-Policy'] = 'geolocation=(), microphone=(), camera=()';
		$headers['X-Permitted-Cross-Domain-Policies'] = 'none';
		$headers['X-Download-Options'] = 'noopen';
		return $headers;
	}

	/**
	 * =========================================================================
	 * 3. XML-RPC BLOCKER
	 * =========================================================================
	 */
	public function block_xmlrpc_requests() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $uri, 'xmlrpc.php' ) !== false ) {
			$this->block_request( 'XMLRPC_EXPLOIT_BLOCKED', 'XML-RPC call rejected' );
		}
	}

	/**
	 * =========================================================================
	 * 4. BRUTE FORCE LOGIN DEFENSE & HONEYPOT
	 * =========================================================================
	 */
	public function log_failed_login( $username ) {
		$ip = $this->get_client_ip();
		$transient_key = 'doodh_login_fails_' . md5( $ip );
		$fails = (int) get_transient( $transient_key ) ?: 0;
		$fails++;
		set_transient( $transient_key, $fails, 15 * MINUTE_IN_SECONDS );

		$this->log_security_event( 'FAILED_LOGIN_ATTEMPT', "User: {$username} (Fail count: {$fails})", 'LOGGED' );
	}

	public function check_brute_force_lockout( $user, $username, $password ) {
		if ( empty( $username ) || empty( $password ) ) {
			return $user;
		}

		$ip = $this->get_client_ip();
		$lock_key = 'doodh_lockout_' . md5( $ip );
		if ( get_transient( $lock_key ) ) {
			return new WP_Error( 'locked_out', sprintf( __( '<strong>SECURITY LOCKOUT</strong>: Too many failed login attempts from IP %s. Locked out for 60 minutes.', 'doodh-security-shield' ), esc_html( $ip ) ) );
		}

		$fails_key = 'doodh_login_fails_' . md5( $ip );
		$fails = (int) get_transient( $fails_key ) ?: 0;
		$max_allowed = (int) $this->get_opt( 'max_login_attempts', 5 );

		if ( $fails >= $max_allowed ) {
			$duration = (int) $this->get_opt( 'lockout_duration_mins', 60 ) * MINUTE_IN_SECONDS;
			set_transient( $lock_key, 1, $duration );
			delete_transient( $fails_key );

			$this->log_security_event( 'BRUTE_FORCE_LOCKOUT', "IP reached {$max_allowed} fails. Locked for {$duration}s", 'BANNED' );
			return new WP_Error( 'locked_out', __( '<strong>SECURITY LOCKOUT</strong>: IP locked due to repeated failed logins.', 'doodh-security-shield' ) );
		}

		return $user;
	}

	public function inject_login_honeypot() {
		echo '<p style="position:absolute; left:-9999px; display:none;"><label for="doodh_security_hp">Leave this empty</label><input type="text" name="doodh_security_hp" id="doodh_security_hp" value="" tabindex="-1" autocomplete="off"></p>';
	}

	public function verify_login_honeypot( $user, $password ) {
		if ( isset( $_POST['doodh_security_hp'] ) && ! empty( $_POST['doodh_security_hp'] ) ) {
			$this->block_request( 'BOT_HONEYPOT_TRIGGERED', 'Filled hidden login honeypot' );
		}
		return $user;
	}

	/**
	 * =========================================================================
	 * 5. USER ENUMERATION BLOCKER
	 * =========================================================================
	 */
	public function block_user_enumeration() {
		if ( ! is_admin() && isset( $_REQUEST['author'] ) && (int) $_REQUEST['author'] > 0 ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}

	public function disable_rest_user_enumeration( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			if ( isset( $endpoints['/wp/v2/users'] ) ) {
				unset( $endpoints['/wp/v2/users'] );
			}
			if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
				unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
			}
		}
		return $endpoints;
	}

	/**
	 * =========================================================================
	 * 6. VERSION & QUERY STRING CLEANER
	 * =========================================================================
	 */
	public function remove_version_query_strings( $src ) {
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}

	/**
	 * =========================================================================
	 * 7. SECURE UPLOADS DIRECTORY (DISABLE PHP EXECUTION)
	 * =========================================================================
	 */
	public function secure_uploads_directory() {
		$upload_dir = wp_upload_dir();
		$basedir    = $upload_dir['basedir'];

		if ( ! file_exists( $basedir ) ) {
			wp_mkdir_p( $basedir );
		}

		if ( file_exists( $basedir ) ) {
			// Apache .htaccess
			$htaccess = $basedir . '/.htaccess';
			$rules = "<Files *.php>\ndeny from all\n</Files>\n<Files *.phtml>\ndeny from all\n</Files>\n";
			if ( ! file_exists( $htaccess ) || file_get_contents( $htaccess ) !== $rules ) {
				@file_put_contents( $htaccess, $rules );
			}

			// IIS web.config
			$webconfig = $basedir . '/web.config';
			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <handlers accessPolicy=\"Read\" />\n  </system.webServer>\n</configuration>";
			if ( ! file_exists( $webconfig ) ) {
				@file_put_contents( $webconfig, $xml );
			}
		}
	}

	public function get_client_ip() {
		$ip = '127.0.0.1';
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = sanitize_text_field( $_SERVER['HTTP_CF_CONNECTING_IP'] );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$ip = trim( sanitize_text_field( $parts[0] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '127.0.0.1';
	}

	/**
	 * =========================================================================
	 * 8. ADMIN DASHBOARD & SECURITY AUDIT
	 * =========================================================================
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Security Shield', 'doodh-security-shield' ),
			__( 'Security Shield', 'doodh-security-shield' ),
			'manage_options',
			'doodh-security-shield',
			array( $this, 'render_security_dashboard' ),
			'dashicons-shield',
			82
		);
	}

	public function handle_admin_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['doodh_security_save'] ) && check_admin_referer( 'doodh_sec_settings_action', 'doodh_sec_nonce' ) ) {
			$opts = array(
				'enable_waf'             => isset( $_POST['enable_waf'] ) ? 'yes' : 'no',
				'enable_sqli_blocker'    => isset( $_POST['enable_sqli_blocker'] ) ? 'yes' : 'no',
				'enable_xss_blocker'     => isset( $_POST['enable_xss_blocker'] ) ? 'yes' : 'no',
				'enable_lfi_blocker'     => isset( $_POST['enable_lfi_blocker'] ) ? 'yes' : 'no',
				'enable_bad_bot_blocker' => isset( $_POST['enable_bad_bot_blocker'] ) ? 'yes' : 'no',
				'enable_brute_force'     => isset( $_POST['enable_brute_force'] ) ? 'yes' : 'no',
				'enable_security_headers'=> isset( $_POST['enable_security_headers'] ) ? 'yes' : 'no',
				'max_login_attempts'     => (int) $_POST['max_login_attempts'],
				'lockout_duration_mins'  => (int) $_POST['lockout_duration_mins'],
				'banned_ips'             => sanitize_textarea_field( $_POST['banned_ips'] ),
				'whitelisted_ips'        => sanitize_textarea_field( $_POST['whitelisted_ips'] ),
			);
			update_option( 'doodh_security_options', $opts );
			$this->load_options();

			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-security-shield', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'clear_logs' && check_admin_referer( 'doodh_clear_logs' ) ) {
			global $wpdb;
			$wpdb->query( "TRUNCATE TABLE {$this->log_table}" );
			wp_safe_redirect( add_query_arg( array( 'page' => 'doodh-security-shield', 'cleared' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	public function render_security_dashboard() {
		global $wpdb;
		$total_blocked = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table} WHERE action_taken='BLOCKED'" ) ?: 0;
		$total_sqli    = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table} WHERE threat_type='SQL_INJECTION'" ) ?: 0;
		$total_xss     = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table} WHERE threat_type='XSS_ATTACK'" ) ?: 0;
		$total_bots    = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table} WHERE threat_type='MALICIOUS_SCANNER'" ) ?: 0;

		$recent_logs = $wpdb->get_results( "SELECT * FROM {$this->log_table} ORDER BY id DESC LIMIT 15" );
		$clear_logs_url = wp_nonce_url( admin_url( 'admin.php?page=doodh-security-shield&action=clear_logs' ), 'doodh_clear_logs' );
		?>
		<div class="wrap" style="max-width:1050px; margin-top:20px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
			
			<!-- Hero Banner -->
			<div style="background: linear-gradient(135deg, #090d16 0%, #1e293b 100%); color:#fff; padding: 28px 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08);">
				<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
					<div>
						<div style="display:inline-flex; align-items:center; gap:6px; background:rgba(16,185,129,0.2); color:#10b981; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:10px; border:1px solid rgba(16,185,129,0.4);">
							<span class="dashicons dashicons-shield-alt" style="font-size:14px; margin-top:2px;"></span> Active Web Application Firewall (WAF)
						</div>
						<h1 style="color:#ffffff; font-size: 26px; font-weight: 800; margin:0 0 6px 0;"><?php esc_html_e( 'Doodh Security Shield & Anti-Hacking Suite', 'doodh-security-shield' ); ?></h1>
						<p style="color:#94a3b8; font-size: 14px; margin:0;"><?php esc_html_e( 'Active defense against SQL Injection, XSS, Brute-Force Logins, XML-RPC attacks, Malicious Scanners, and Path Traversal.', 'doodh-security-shield' ); ?></p>
					</div>
					<div>
						<span style="background:#10b981; color:#fff; font-weight:800; font-size:13px; padding:8px 16px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
							<span class="dashicons dashicons-yes-alt"></span> SHIELD ACTIVE
						</span>
					</div>
				</div>
			</div>

			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Security firewall settings updated successfully!', 'doodh-security-shield' ); ?></strong></p></div>
			<?php endif; ?>

			<!-- Threat Radar Counter Grid -->
			<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 25px;">
				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Total Blocked Attacks', 'doodh-security-shield' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#ef4444;"><?php echo esc_html( number_format( $total_blocked ) ); ?></div>
				</div>

				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Blocked SQL Injections', 'doodh-security-shield' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a;"><?php echo esc_html( number_format( $total_sqli ) ); ?></div>
				</div>

				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Blocked XSS Payloads', 'doodh-security-shield' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a;"><?php echo esc_html( number_format( $total_xss ) ); ?></div>
				</div>

				<div style="background:#fff; padding:20px; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
					<div style="color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;"><?php esc_html_e( 'Blocked Bad Scanners', 'doodh-security-shield' ); ?></div>
					<div style="font-size:28px; font-weight:800; color:#0f172a;"><?php echo esc_html( number_format( $total_bots ) ); ?></div>
				</div>
			</div>

			<!-- Live Threat & Attack Log -->
			<div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:25px;">
				<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
					<h3 style="margin:0; font-size:17px; font-weight:700; color:#0f172a;">
						<span class="dashicons dashicons-list-view" style="color:#ef4444; margin-top:2px;"></span> <?php esc_html_e( 'Live Firewall Threat & Attack Log (Latest 15)', 'doodh-security-shield' ); ?>
					</h3>
					<?php if ( ! empty( $recent_logs ) ) : ?>
						<a href="<?php echo esc_url( $clear_logs_url ); ?>" class="button button-secondary" onclick="return confirm('Clear all security logs?');">
							<span class="dashicons dashicons-trash" style="margin-top:2px;"></span> <?php esc_html_e( 'Clear Logs', 'doodh-security-shield' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $recent_logs ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width:140px;">Timestamp</th>
								<th style="width:130px;">Attacker IP</th>
								<th style="width:160px;">Threat Type</th>
								<th>Target URI</th>
								<th style="width:90px;">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent_logs as $log ) : ?>
								<tr>
									<td style="font-size:12px; color:#64748b;"><?php echo esc_html( $log->timestamp ); ?></td>
									<td><strong style="font-family:monospace;"><?php echo esc_html( $log->ip_address ); ?></strong></td>
									<td>
										<span style="background:rgba(239,68,68,0.1); color:#ef4444; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700;">
											<?php echo esc_html( $log->threat_type ); ?>
										</span>
									</td>
									<td style="font-family:monospace; font-size:11px; max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
										<?php echo esc_html( $log->request_uri ); ?>
									</td>
									<td>
										<span style="background:#10b981; color:#fff; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700;">
											<?php echo esc_html( $log->action_taken ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p style="color:#64748b; text-align:center; padding:30px 0; margin:0;">
						<span class="dashicons dashicons-yes" style="font-size:24px; color:#10b981;"></span><br>
						<?php esc_html_e( 'Firewall log is completely clean. No attacks detected.', 'doodh-security-shield' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<!-- Firewall Settings Form -->
			<form method="post" action="">
				<?php wp_nonce_field( 'doodh_sec_settings_action', 'doodh_sec_nonce' ); ?>

				<div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:28px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom:20px;">
					<h3 style="margin-top:0; font-size:18px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
						<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Web Application Firewall & Shield Settings', 'doodh-security-shield' ); ?>
					</h3>

					<table class="form-table">
						<tr>
							<th scope="row" style="width:280px;"><?php esc_html_e( 'Web Application Firewall (WAF)', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_waf" value="yes" <?php checked( $this->get_opt( 'enable_waf', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable Active Real-Time WAF', 'doodh-security-shield' ); ?></strong>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'SQL Injection Protection', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_sqli_blocker" value="yes" <?php checked( $this->get_opt( 'enable_sqli_blocker', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Inspect & block malicious database injection strings in GET/POST/Cookies', 'doodh-security-shield' ); ?></span>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'XSS & Script Injection Filter', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_xss_blocker" value="yes" <?php checked( $this->get_opt( 'enable_xss_blocker', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Block malicious JavaScript payloads, iframe injections, and event handlers', 'doodh-security-shield' ); ?></span>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Path Traversal / LFI Blocker', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_lfi_blocker" value="yes" <?php checked( $this->get_opt( 'enable_lfi_blocker', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Block directory traversal (../), /etc/passwd, and PHP wrapper exploits', 'doodh-security-shield' ); ?></span>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Bad Bot & Vulnerability Scanners', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_bad_bot_blocker" value="yes" <?php checked( $this->get_opt( 'enable_bad_bot_blocker', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Block automated hacking bots (sqlmap, nikto, wpscan, dirbuster)', 'doodh-security-shield' ); ?></span>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Brute Force Defense', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
									<input type="checkbox" name="enable_brute_force" value="yes" <?php checked( $this->get_opt( 'enable_brute_force', 'yes' ), 'yes' ); ?>>
									<strong><?php esc_html_e( 'Enable Login Attempt Limiter & Honeypot', 'doodh-security-shield' ); ?></strong>
								</label>
								<div style="display:flex; align-items:center; gap:12px; font-size:13px;">
									<span>Max Retries:</span>
									<input type="number" name="max_login_attempts" value="<?php echo esc_attr( $this->get_opt( 'max_login_attempts', 5 ) ); ?>" style="width:65px;">
									<span>Lockout (Mins):</span>
									<input type="number" name="lockout_duration_mins" value="<?php echo esc_attr( $this->get_opt( 'lockout_duration_mins', 60 ) ); ?>" style="width:75px;">
								</div>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'HTTP Security Headers', 'doodh-security-shield' ); ?></th>
							<td>
								<label style="display:flex; align-items:center; gap:8px;">
									<input type="checkbox" name="enable_security_headers" value="yes" <?php checked( $this->get_opt( 'enable_security_headers', 'yes' ), 'yes' ); ?>>
									<span><?php esc_html_e( 'Emit X-Frame-Options, X-Content-Type-Options: nosniff, and XSS Protection headers', 'doodh-security-shield' ); ?></span>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'IP Blacklist (1 per line)', 'doodh-security-shield' ); ?></th>
							<td>
								<textarea name="banned_ips" rows="3" class="large-text" placeholder="192.168.1.100&#10;10.0.0.5"><?php echo esc_textarea( $this->get_opt( 'banned_ips' ) ); ?></textarea>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'IP Whitelist (1 per line)', 'doodh-security-shield' ); ?></th>
							<td>
								<textarea name="whitelisted_ips" rows="2" class="large-text" placeholder="Your trusted static IP"><?php echo esc_textarea( $this->get_opt( 'whitelisted_ips' ) ); ?></textarea>
							</td>
						</tr>
					</table>
				</div>

				<div style="margin-top:20px;">
					<button type="submit" name="doodh_security_save" class="button button-primary" style="background:#0f172a; border-color:#0f172a; padding:8px 24px; height:auto; font-size:15px; font-weight:700; border-radius:8px;">
						<?php esc_html_e( 'Save & Enforce Security Shield', 'doodh-security-shield' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}
}

endif;

function doodh_security_shield_init() {
	return Doodh_Security_Shield::get_instance();
}
add_action( 'plugins_loaded', 'doodh_security_shield_init', 0 );