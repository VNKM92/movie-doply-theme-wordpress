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
 * Render the multi-server video player for Movies & Episodes
 */
function doodhtheme_render_player( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$servers     = get_post_meta( $post_id, '_doodh_servers', true );
	$trailer_url = get_post_meta( $post_id, '_doodh_trailer_url', true );
	$backdrop    = doodhtheme_get_backdrop_url( $post_id );

	// Filter valid servers with URLs
	$active_servers = array();
	if ( is_array( $servers ) ) {
		foreach ( $servers as $server ) {
			if ( ! empty( $server['url'] ) ) {
				$active_servers[] = $server;
			}
		}
	}

	// Fallback if no server is set
	if ( empty( $active_servers ) && ! empty( $trailer_url ) ) {
		$active_servers[] = array(
			'name' => __( 'Official Trailer / Stream', 'vmtheme' ),
			'type' => 'iframe',
			'url'  => $trailer_url,
		);
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
							<i class="fas fa-play-circle"></i> <?php echo esc_html( $srv['name'] ?: sprintf( __( 'Server %d', 'vmtheme' ), $idx + 1 ) ); ?>
						</button>
					<?php endforeach; ?>
				<?php else : ?>
					<span class="doodh-no-servers"><?php esc_html_e( 'Streaming links updating soon...', 'vmtheme' ); ?></span>
				<?php endif; ?>
			</div>

			<div class="doodh-player-actions">
				<button type="button" class="doodh-action-btn" id="doodh-btn-lights" title="<?php esc_attr_e( 'Turn Off Lights', 'vmtheme' ); ?>">
					<i class="fas fa-lightbulb"></i> <span><?php esc_html_e( 'Lights Off', 'vmtheme' ); ?></span>
				</button>
				<button type="button" class="doodh-action-btn" id="doodh-btn-theater" title="<?php esc_attr_e( 'Theater Mode', 'vmtheme' ); ?>">
					<i class="fas fa-expand-arrows-alt"></i> <span><?php esc_html_e( 'Expand', 'vmtheme' ); ?></span>
				</button>
				<?php if ( ! empty( $trailer_url ) ) : ?>
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
