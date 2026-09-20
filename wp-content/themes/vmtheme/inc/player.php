<?php
/**
 * Video Streaming Player, Multi-Server Tabs, and Download Manager
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if Video Streaming Player (Multi-Server) is Enabled for a given Post or Post Type
 *
 * @param int|null $post_id Post ID or current post
 * @return bool True if enabled, false otherwise
 */
function doodhtheme_is_player_enabled( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( $post_id ) {
		// 1. Per-Post Specific Override
		$post_override = get_post_meta( $post_id, '_doodh_player_enabled', true );
		if ( $post_override === 'disabled' || $post_override === '0' || $post_override === 'no' || $post_override === 'off' ) {
			return false;
		}
		if ( $post_override === 'enabled' || $post_override === '1' || $post_override === 'yes' || $post_override === 'on' ) {
			return true;
		}

		// 2. Post-Type Specific Setting
		$post_type = get_post_type( $post_id );
		if ( $post_type ) {
			$default_by_type = ( $post_type === 'post' ) ? '0' : '1';
			$opt_key = 'doodh_enable_player_' . $post_type;
			$type_enabled = get_option( $opt_key, $default_by_type );
			if ( $type_enabled === '0' || $type_enabled === 'no' || $type_enabled === false || $type_enabled === 'off' ) {
				return false;
			}
		}
	}

	// 3. Global Master Switch (default: 1 / enabled)
	$global_enabled = get_option( 'doodh_enable_player_global', '1' );
	if ( $global_enabled === '0' || $global_enabled === 'no' || $global_enabled === false || $global_enabled === 'off' ) {
		return false;
	}

	return true;
}

/**
 * Render the multi-server video player for Movies, TV Shows, Episodes & Posts
 */
function doodhtheme_render_player( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// Master enabled check
	if ( ! doodhtheme_is_player_enabled( $post_id ) ) {
		return;
	}

	$servers      = get_post_meta( $post_id, '_doodh_servers', true );
	$trailer_url  = get_post_meta( $post_id, '_doodh_trailer_url', true );
	$backdrop     = doodhtheme_get_backdrop_url( $post_id );
	$empty_action = get_option( 'doodh_player_empty_action', 'placeholder' ); // 'hide' or 'placeholder'
	$show_lights  = get_option( 'doodh_player_show_lights', '1' );
	$show_theater = get_option( 'doodh_player_show_theater', '1' );
	$show_trailer = get_option( 'doodh_player_show_trailer_btn', '1' );

	// Filter valid servers with URLs
	$active_servers = array();
	if ( is_array( $servers ) ) {
		foreach ( $servers as $server ) {
			if ( ! empty( $server['url'] ) ) {
				$active_servers[] = $server;
			}
		}
	}

	// Fallback to trailer stream if no custom server is entered
	if ( empty( $active_servers ) && ! empty( $trailer_url ) ) {
		$active_servers[] = array(
			'name' => __( 'Official Trailer / Stream', 'vmtheme' ),
			'type' => 'iframe',
			'url'  => $trailer_url,
		);
	}

	// If empty and setting is to hide
	if ( empty( $active_servers ) && $empty_action === 'hide' ) {
		return;
	}
	?>
	<div class="doodh-player-wrapper" id="video-player-container">
		<!-- Player Controls / Server Selector Tabs -->
		<div class="doodh-player-header">
			<div class="doodh-server-tabs">
				<span class="doodh-server-label"><i class="fas fa-server"></i> <?php esc_html_e( 'Servers:', 'vmtheme' ); ?></span>
				<?php if ( ! empty( $active_servers ) ) : ?>
					<?php foreach ( $active_servers as $idx => $srv ) : ?>
						<button type="button" 
								class="doodh-server-btn <?php echo ( $idx === 0 ) ? 'active' : ''; ?>" 
								data-server-index="<?php echo esc_attr( $idx ); ?>"
								data-server-type="<?php echo esc_attr( $srv['type'] ?? 'iframe' ); ?>"
								data-server-url="<?php echo esc_attr( $srv['url'] ); ?>">
							<i class="fas fa-play-circle"></i> <?php echo esc_html( ! empty( $srv['name'] ) ? $srv['name'] : sprintf( __( 'Server %d', 'vmtheme' ), $idx + 1 ) ); ?>
						</button>
					<?php endforeach; ?>
				<?php else : ?>
					<span class="doodh-no-servers"><?php esc_html_e( 'Streaming links updating soon...', 'vmtheme' ); ?></span>
				<?php endif; ?>
			</div>

			<div class="doodh-player-actions">
				<?php if ( $show_lights === '1' ) : ?>
					<button type="button" class="doodh-action-btn" id="doodh-btn-lights" title="<?php esc_attr_e( 'Turn Off Lights', 'vmtheme' ); ?>">
						<i class="fas fa-lightbulb"></i> <span><?php esc_html_e( 'Lights Off', 'vmtheme' ); ?></span>
					</button>
				<?php endif; ?>
				<?php if ( $show_theater === '1' ) : ?>
					<button type="button" class="doodh-action-btn" id="doodh-btn-theater" title="<?php esc_attr_e( 'Theater Mode', 'vmtheme' ); ?>">
						<i class="fas fa-expand-arrows-alt"></i> <span><?php esc_html_e( 'Expand', 'vmtheme' ); ?></span>
					</button>
				<?php endif; ?>
				<?php if ( $show_trailer === '1' && ! empty( $trailer_url ) ) : ?>
					<button type="button" class="doodh-action-btn doodh-trailer-modal-btn" data-trailer="<?php echo esc_attr( doodhtheme_format_youtube_embed( $trailer_url ) ); ?>" title="<?php esc_attr_e( 'Watch Trailer', 'vmtheme' ); ?>">
						<i class="fab fa-youtube"></i> <span><?php esc_html_e( 'Trailer', 'vmtheme' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- Player Display Screen -->
		<div class="doodh-player-screen" id="doodh-screen" style="background-image: url('<?php echo esc_url( $backdrop ); ?>');">
			<div class="doodh-player-loader" id="player-loader" style="display:none;">
				<div class="doodh-spinner"></div>
				<span><?php esc_html_e( 'Connecting to high-speed stream...', 'vmtheme' ); ?></span>
			</div>

			<div class="doodh-iframe-container" id="player-iframe-box">
				<?php if ( ! empty( $active_servers ) ) : 
					$first_server = $active_servers[0];
					$embed_url    = doodhtheme_format_embed_url( $first_server['url'] );
					if ( ( $first_server['type'] ?? '' ) === 'mp4' ) : ?>
						<video controls preload="metadata" class="doodh-html5-video" id="main-video-element">
							<source src="<?php echo esc_url( $first_server['url'] ); ?>" type="video/mp4">
							<?php esc_html_e( 'Your browser does not support HTML5 video.', 'vmtheme' ); ?>
						</video>
					<?php else : ?>
						<iframe id="main-player-frame" 
								src="<?php echo esc_url( $embed_url ); ?>" 
								frameborder="0" 
								allowfullscreen 
								scrolling="no" 
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
						</iframe>
					<?php endif; ?>
				<?php else : ?>
					<div class="doodh-placeholder-player">
						<i class="fas fa-film"></i>
						<h3><?php esc_html_e( 'No Stream Available', 'vmtheme' ); ?></h3>
						<p><?php esc_html_e( 'Check back later or try our download links below.', 'vmtheme' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}



/**
 * Format YouTube URL to Embed URL
 */
function doodhtheme_format_youtube_embed( $url ) {
	if ( empty( $url ) ) {
		return '';
	}
	if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match ) ) {
		return 'https://www.youtube-nocookie.com/embed/' . $match[1] . '?autoplay=1';
	}
	return $url;
}

/**
 * Format Embed URL
 */
function doodhtheme_format_embed_url( $url ) {
	if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
		return doodhtheme_format_youtube_embed( $url );
	}
	return $url;
}
