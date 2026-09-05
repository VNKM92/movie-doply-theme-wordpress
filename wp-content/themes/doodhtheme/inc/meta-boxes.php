<?php
/**
 * Custom Meta Boxes for DoodhTheme (Movies, TV Shows, Seasons, Episodes, Video Players, Downloads)
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Meta Boxes
 */
function doodhtheme_add_meta_boxes() {
	// Movies Meta Box
	add_meta_box(
		'doodhtheme_movie_details',
		__( 'Movie Information & Metadata', 'doodhtheme' ),
		'doodhtheme_render_movie_metabox',
		'movies',
		'normal',
		'high'
	);

	// Streaming Servers Meta Box (Movies & Episodes)
	add_meta_box(
		'doodhtheme_streaming_servers',
		__( 'Video Streaming Player Servers (Multi-Server)', 'doodhtheme' ),
		'doodhtheme_render_servers_metabox',
		array( 'movies', 'episodes' ),
		'normal',
		'high'
	);

	// Download Links Meta Box (Movies & Episodes)
	add_meta_box(
		'doodhtheme_download_links',
		__( 'Download Links & File Sources', 'doodhtheme' ),
		'doodhtheme_render_downloads_metabox',
		array( 'movies', 'episodes' ),
		'normal',
		'default'
	);

	// TV Shows Meta Box
	add_meta_box(
		'doodhtheme_tv_details',
		__( 'TV Show Information & Metadata', 'doodhtheme' ),
		'doodhtheme_render_tv_metabox',
		'tvshows',
		'normal',
		'high'
	);

	// Seasons Meta Box
	add_meta_box(
		'doodhtheme_season_details',
		__( 'Season Details', 'doodhtheme' ),
		'doodhtheme_render_season_metabox',
		'seasons',
		'normal',
		'high'
	);

	// Episodes Meta Box
	add_meta_box(
		'doodhtheme_episode_details',
		__( 'Episode Details & Hierarchy', 'doodhtheme' ),
		'doodhtheme_render_episode_metabox',
		'episodes',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'doodhtheme_add_meta_boxes' );

/**
 * Render Movie Information Metabox
 */
function doodhtheme_render_movie_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_movie_meta', 'doodhtheme_movie_nonce' );

	$tmdb_id        = get_post_meta( $post->ID, '_doodh_tmdb_id', true );
	$imdb_id        = get_post_meta( $post->ID, '_doodh_imdb_id', true );
	$original_title = get_post_meta( $post->ID, '_doodh_original_title', true );
	$tagline        = get_post_meta( $post->ID, '_doodh_tagline', true );
	$release_date   = get_post_meta( $post->ID, '_doodh_release_date', true );
	$runtime        = get_post_meta( $post->ID, '_doodh_runtime', true );
	$rating         = get_post_meta( $post->ID, '_doodh_rating', true );
	$votes          = get_post_meta( $post->ID, '_doodh_votes', true );
	$trailer_url    = get_post_meta( $post->ID, '_doodh_trailer_url', true );
	$backdrop_url   = get_post_meta( $post->ID, '_doodh_backdrop_url', true );
	$poster_url     = get_post_meta( $post->ID, '_doodh_poster_url', true );
	$certification  = get_post_meta( $post->ID, '_doodh_certification', true );
	$status         = get_post_meta( $post->ID, '_doodh_status', true );
	?>
	<style>
		.doodh-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 10px; }
		.doodh-meta-field { display: flex; flex-direction: column; }
		.doodh-meta-field label { font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 13px; }
		.doodh-meta-field input, .doodh-meta-field select, .doodh-meta-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; }
		.doodh-full-width { grid-column: 1 / -1; }
	</style>

	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_original_title"><?php esc_html_e( 'Original Title', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_original_title" name="doodh_original_title" value="<?php echo esc_attr( $original_title ); ?>" placeholder="e.g. Inception">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tagline"><?php esc_html_e( 'Tagline', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_tagline" name="doodh_tagline" value="<?php echo esc_attr( $tagline ); ?>" placeholder="e.g. Your mind is the scene of the crime.">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_release_date"><?php esc_html_e( 'Release Date', 'doodhtheme' ); ?></label>
			<input type="date" id="doodh_release_date" name="doodh_release_date" value="<?php echo esc_attr( $release_date ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_runtime"><?php esc_html_e( 'Runtime (Minutes)', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_runtime" name="doodh_runtime" value="<?php echo esc_attr( $runtime ); ?>" placeholder="e.g. 148">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_rating"><?php esc_html_e( 'Rating Score (0 - 10)', 'doodhtheme' ); ?></label>
			<input type="number" step="0.1" min="0" max="10" id="doodh_rating" name="doodh_rating" value="<?php echo esc_attr( $rating ); ?>" placeholder="e.g. 8.8">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_votes"><?php esc_html_e( 'Vote Count', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_votes" name="doodh_votes" value="<?php echo esc_attr( $votes ); ?>" placeholder="e.g. 24000">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tmdb_id"><?php esc_html_e( 'TMDB ID', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_tmdb_id" name="doodh_tmdb_id" value="<?php echo esc_attr( $tmdb_id ); ?>" placeholder="e.g. 27205">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_imdb_id"><?php esc_html_e( 'IMDb ID', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_imdb_id" name="doodh_imdb_id" value="<?php echo esc_attr( $imdb_id ); ?>" placeholder="e.g. tt1375666">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_certification"><?php esc_html_e( 'Age Rating / Certificate', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_certification" name="doodh_certification" value="<?php echo esc_attr( $certification ); ?>" placeholder="e.g. PG-13, R, TV-MA">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_status"><?php esc_html_e( 'Status', 'doodhtheme' ); ?></label>
			<select id="doodh_status" name="doodh_status">
				<option value="Released" <?php selected( $status, 'Released' ); ?>>Released</option>
				<option value="In Production" <?php selected( $status, 'In Production' ); ?>>In Production</option>
				<option value="Post Production" <?php selected( $status, 'Post Production' ); ?>>Post Production</option>
				<option value="Upcoming" <?php selected( $status, 'Upcoming' ); ?>>Upcoming</option>
			</select>
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_trailer_url"><?php esc_html_e( 'YouTube Trailer URL / Embed', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_trailer_url" name="doodh_trailer_url" value="<?php echo esc_attr( $trailer_url ); ?>" placeholder="https://www.youtube.com/watch?v=YoHD9XEInc0">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_poster_url"><?php esc_html_e( 'Poster Image URL (External or Upload URL)', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_poster_url" name="doodh_poster_url" value="<?php echo esc_attr( $poster_url ); ?>" placeholder="https://image.tmdb.org/t/p/w500/...jpg">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_backdrop_url"><?php esc_html_e( 'Backdrop / Banner Image URL', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_backdrop_url" name="doodh_backdrop_url" value="<?php echo esc_attr( $backdrop_url ); ?>" placeholder="https://image.tmdb.org/t/p/original/...jpg">
		</div>
	</div>
	<?php
}

/**
 * Render Streaming Servers Metabox
 */
function doodhtheme_render_servers_metabox( $post ) {
	$servers = get_post_meta( $post->ID, '_doodh_servers', true );
	if ( ! is_array( $servers ) || empty( $servers ) ) {
		$servers = array(
			array( 'name' => 'Server 1 - VidCloud (HD)', 'type' => 'iframe', 'url' => '' ),
			array( 'name' => 'Server 2 - StreamTape', 'type' => 'iframe', 'url' => '' ),
			array( 'name' => 'Server 3 - FastEmbed', 'type' => 'iframe', 'url' => '' ),
			array( 'name' => 'Server 4 - Direct Stream', 'type' => 'mp4', 'url' => '' ),
		);
	}
	?>
	<div id="doodh-servers-container">
		<p class="description"><?php esc_html_e( 'Add streaming servers and embed codes for this title. Users can switch smoothly between servers on the frontend player.', 'doodhtheme' ); ?></p>
		<?php foreach ( $servers as $index => $server ) : ?>
			<div class="doodh-server-row" style="background:#f8fafc; padding:12px; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:10px;">
				<div style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
					<strong style="min-width:70px;"><?php printf( esc_html__( 'Server %d:', 'doodhtheme' ), $index + 1 ); ?></strong>
					<input type="text" name="doodh_server_name[]" value="<?php echo esc_attr( $server['name'] ?? '' ); ?>" placeholder="Server Name (e.g. VIP Server, StreamTape)" style="flex:1;">
					<select name="doodh_server_type[]" style="width:140px;">
						<option value="iframe" <?php selected( $server['type'] ?? '', 'iframe' ); ?>>Iframe Embed</option>
						<option value="mp4" <?php selected( $server['type'] ?? '', 'mp4' ); ?>>Direct MP4/Video</option>
						<option value="hls" <?php selected( $server['type'] ?? '', 'hls' ); ?>>HLS (m3u8)</option>
					</select>
				</div>
				<input type="text" name="doodh_server_url[]" value="<?php echo esc_attr( $server['url'] ?? '' ); ?>" placeholder="Embed URL or Video Stream URL (https://...)" style="width:100%;">
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render Download Links Metabox
 */
function doodhtheme_render_downloads_metabox( $post ) {
	$downloads = get_post_meta( $post->ID, '_doodh_downloads', true );
	if ( ! is_array( $downloads ) || empty( $downloads ) ) {
		$downloads = array(
			array( 'server' => 'Mega', 'quality' => '1080p 60FPS', 'size' => '2.4 GB', 'url' => '' ),
			array( 'server' => 'Google Drive', 'quality' => '720p HD', 'size' => '1.1 GB', 'url' => '' ),
			array( 'server' => 'Direct Download', 'quality' => '480p SD', 'size' => '450 MB', 'url' => '' ),
		);
	}
	?>
	<div id="doodh-downloads-container">
		<p class="description"><?php esc_html_e( 'Specify direct download links, quality tags, and file sizes.', 'doodhtheme' ); ?></p>
		<?php foreach ( $downloads as $index => $dl ) : ?>
			<div style="display:grid; grid-template-columns: 140px 140px 100px 1fr; gap:10px; margin-bottom:8px;">
				<input type="text" name="doodh_dl_server[]" value="<?php echo esc_attr( $dl['server'] ?? '' ); ?>" placeholder="Server (e.g. Mega)">
				<input type="text" name="doodh_dl_quality[]" value="<?php echo esc_attr( $dl['quality'] ?? '' ); ?>" placeholder="Quality (e.g. 1080p)">
				<input type="text" name="doodh_dl_size[]" value="<?php echo esc_attr( $dl['size'] ?? '' ); ?>" placeholder="Size (e.g. 2.1 GB)">
				<input type="url" name="doodh_dl_url[]" value="<?php echo esc_attr( $dl['url'] ?? '' ); ?>" placeholder="Download Link URL">
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render TV Shows Metabox
 */
function doodhtheme_render_tv_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_tv_meta', 'doodhtheme_tv_nonce' );

	$tmdb_id        = get_post_meta( $post->ID, '_doodh_tmdb_id', true );
	$imdb_id        = get_post_meta( $post->ID, '_doodh_imdb_id', true );
	$original_title = get_post_meta( $post->ID, '_doodh_original_title', true );
	$first_air_date = get_post_meta( $post->ID, '_doodh_first_air_date', true );
	$last_air_date  = get_post_meta( $post->ID, '_doodh_last_air_date', true );
	$seasons_count  = get_post_meta( $post->ID, '_doodh_total_seasons', true );
	$episodes_count = get_post_meta( $post->ID, '_doodh_total_episodes', true );
	$episode_runtime= get_post_meta( $post->ID, '_doodh_episode_runtime', true );
	$rating         = get_post_meta( $post->ID, '_doodh_rating', true );
	$votes          = get_post_meta( $post->ID, '_doodh_votes', true );
	$trailer_url    = get_post_meta( $post->ID, '_doodh_trailer_url', true );
	$backdrop_url   = get_post_meta( $post->ID, '_doodh_backdrop_url', true );
	$poster_url     = get_post_meta( $post->ID, '_doodh_poster_url', true );
	$status         = get_post_meta( $post->ID, '_doodh_status', true );
	?>
	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_tv_original_title"><?php esc_html_e( 'Original Title', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_tv_original_title" name="doodh_original_title" value="<?php echo esc_attr( $original_title ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_first_air_date"><?php esc_html_e( 'First Air Date', 'doodhtheme' ); ?></label>
			<input type="date" id="doodh_first_air_date" name="doodh_first_air_date" value="<?php echo esc_attr( $first_air_date ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_last_air_date"><?php esc_html_e( 'Last Air Date', 'doodhtheme' ); ?></label>
			<input type="date" id="doodh_last_air_date" name="doodh_last_air_date" value="<?php echo esc_attr( $last_air_date ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_total_seasons"><?php esc_html_e( 'Total Seasons', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_total_seasons" name="doodh_total_seasons" value="<?php echo esc_attr( $seasons_count ); ?>" placeholder="e.g. 4">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_total_episodes"><?php esc_html_e( 'Total Episodes', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_total_episodes" name="doodh_total_episodes" value="<?php echo esc_attr( $episodes_count ); ?>" placeholder="e.g. 32">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_episode_runtime"><?php esc_html_e( 'Avg Episode Runtime (Mins)', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_episode_runtime" name="doodh_episode_runtime" value="<?php echo esc_attr( $episode_runtime ); ?>" placeholder="e.g. 50">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_rating"><?php esc_html_e( 'Rating Score (0-10)', 'doodhtheme' ); ?></label>
			<input type="number" step="0.1" id="doodh_tv_rating" name="doodh_rating" value="<?php echo esc_attr( $rating ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_votes"><?php esc_html_e( 'Votes Count', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_tv_votes" name="doodh_votes" value="<?php echo esc_attr( $votes ); ?>">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_tv_status"><?php esc_html_e( 'Series Status', 'doodhtheme' ); ?></label>
			<select id="doodh_tv_status" name="doodh_status">
				<option value="Returning Series" <?php selected( $status, 'Returning Series' ); ?>>Returning Series</option>
				<option value="Ended" <?php selected( $status, 'Ended' ); ?>>Ended / Completed</option>
				<option value="Canceled" <?php selected( $status, 'Canceled' ); ?>>Canceled</option>
				<option value="In Production" <?php selected( $status, 'In Production' ); ?>>In Production</option>
			</select>
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_tv_trailer_url"><?php esc_html_e( 'YouTube Trailer URL', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_tv_trailer_url" name="doodh_trailer_url" value="<?php echo esc_attr( $trailer_url ); ?>">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_tv_poster_url"><?php esc_html_e( 'Poster Image URL', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_tv_poster_url" name="doodh_poster_url" value="<?php echo esc_attr( $poster_url ); ?>">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_tv_backdrop_url"><?php esc_html_e( 'Backdrop / Banner Image URL', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_tv_backdrop_url" name="doodh_backdrop_url" value="<?php echo esc_attr( $backdrop_url ); ?>">
		</div>
	</div>
	<?php
}

/**
 * Render Seasons Metabox
 */
function doodhtheme_render_season_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_season_meta', 'doodhtheme_season_nonce' );

	$tv_show_id    = get_post_meta( $post->ID, '_doodh_tv_id', true );
	$season_number = get_post_meta( $post->ID, '_doodh_season_number', true );
	$air_date      = get_post_meta( $post->ID, '_doodh_air_date', true );

	$tv_shows = get_posts( array(
		'post_type'      => 'tvshows',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	?>
	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_season_tv_id"><?php esc_html_e( 'Parent TV Show', 'doodhtheme' ); ?></label>
			<select id="doodh_season_tv_id" name="doodh_season_tv_id">
				<option value=""><?php esc_html_e( '-- Select TV Show --', 'doodhtheme' ); ?></option>
				<?php foreach ( $tv_shows as $show ) : ?>
					<option value="<?php echo esc_attr( $show->ID ); ?>" <?php selected( $tv_show_id, $show->ID ); ?>>
						<?php echo esc_html( $show->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_season_number"><?php esc_html_e( 'Season Number', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_season_number" name="doodh_season_number" value="<?php echo esc_attr( $season_number ); ?>" placeholder="e.g. 1">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_season_air_date"><?php esc_html_e( 'Air Date', 'doodhtheme' ); ?></label>
			<input type="date" id="doodh_season_air_date" name="doodh_season_air_date" value="<?php echo esc_attr( $air_date ); ?>">
		</div>
	</div>
	<?php
}

/**
 * Render Episodes Metabox
 */
function doodhtheme_render_episode_metabox( $post ) {
	wp_nonce_field( 'doodhtheme_save_episode_meta', 'doodhtheme_episode_nonce' );

	$tv_show_id     = get_post_meta( $post->ID, '_doodh_tv_id', true );
	$season_number  = get_post_meta( $post->ID, '_doodh_season_number', true );
	$episode_number = get_post_meta( $post->ID, '_doodh_episode_number', true );
	$episode_name   = get_post_meta( $post->ID, '_doodh_episode_name', true );
	$air_date       = get_post_meta( $post->ID, '_doodh_air_date', true );
	$still_url      = get_post_meta( $post->ID, '_doodh_still_url', true );

	$tv_shows = get_posts( array(
		'post_type'      => 'tvshows',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	?>
	<div class="doodh-meta-grid">
		<div class="doodh-meta-field">
			<label for="doodh_ep_tv_id"><?php esc_html_e( 'Parent TV Show', 'doodhtheme' ); ?></label>
			<select id="doodh_ep_tv_id" name="doodh_ep_tv_id">
				<option value=""><?php esc_html_e( '-- Select TV Show --', 'doodhtheme' ); ?></option>
				<?php foreach ( $tv_shows as $show ) : ?>
					<option value="<?php echo esc_attr( $show->ID ); ?>" <?php selected( $tv_show_id, $show->ID ); ?>>
						<?php echo esc_html( $show->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_season_num"><?php esc_html_e( 'Season Number', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_ep_season_num" name="doodh_ep_season_num" value="<?php echo esc_attr( $season_number ); ?>" placeholder="e.g. 1">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_number"><?php esc_html_e( 'Episode Number', 'doodhtheme' ); ?></label>
			<input type="number" id="doodh_ep_number" name="doodh_ep_number" value="<?php echo esc_attr( $episode_number ); ?>" placeholder="e.g. 1">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_name"><?php esc_html_e( 'Episode Title / Name', 'doodhtheme' ); ?></label>
			<input type="text" id="doodh_ep_name" name="doodh_ep_name" value="<?php echo esc_attr( $episode_name ); ?>" placeholder="e.g. Chapter One: The Vanishing">
		</div>

		<div class="doodh-meta-field">
			<label for="doodh_ep_air_date"><?php esc_html_e( 'Air Date', 'doodhtheme' ); ?></label>
			<input type="date" id="doodh_ep_air_date" name="doodh_ep_air_date" value="<?php echo esc_attr( $air_date ); ?>">
		</div>

		<div class="doodh-meta-field doodh-full-width">
			<label for="doodh_ep_still_url"><?php esc_html_e( 'Episode Thumbnail / Still Image URL', 'doodhtheme' ); ?></label>
			<input type="url" id="doodh_ep_still_url" name="doodh_ep_still_url" value="<?php echo esc_attr( $still_url ); ?>">
		</div>
	</div>
	<?php
}

/**
 * Save Meta Box Data
 */
function doodhtheme_save_post_meta( $post_id ) {
	// Autosave check
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Movie Meta
	if ( isset( $_POST['doodhtheme_movie_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_movie_nonce'], 'doodhtheme_save_movie_meta' ) ) {
		$fields = array(
			'_doodh_tmdb_id'        => 'sanitize_text_field',
			'_doodh_imdb_id'        => 'sanitize_text_field',
			'_doodh_original_title' => 'sanitize_text_field',
			'_doodh_tagline'        => 'sanitize_text_field',
			'_doodh_release_date'   => 'sanitize_text_field',
			'_doodh_runtime'        => 'intval',
			'_doodh_rating'         => 'sanitize_text_field',
			'_doodh_votes'          => 'intval',
			'_doodh_trailer_url'    => 'esc_url_raw',
			'_doodh_backdrop_url'   => 'esc_url_raw',
			'_doodh_poster_url'     => 'esc_url_raw',
			'_doodh_certification'  => 'sanitize_text_field',
			'_doodh_status'         => 'sanitize_text_field',
		);

		foreach ( $fields as $meta_key => $sanitizer ) {
			$post_key = str_replace( '_', '', $meta_key );
			if ( isset( $_POST[ $post_key ] ) ) {
				update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, $_POST[ $post_key ] ) );
			}
		}
	}

	// Streaming Servers
	if ( isset( $_POST['doodh_server_name'] ) && is_array( $_POST['doodh_server_name'] ) ) {
		$clean_servers = array();
		$names = $_POST['doodh_server_name'];
		$types = $_POST['doodh_server_type'] ?? array();
		$urls  = $_POST['doodh_server_url'] ?? array();

		for ( $i = 0; $i < count( $names ); $i++ ) {
			if ( ! empty( $urls[ $i ] ) || ! empty( $names[ $i ] ) ) {
				$clean_servers[] = array(
					'name' => sanitize_text_field( $names[ $i ] ),
					'type' => sanitize_text_field( $types[ $i ] ?? 'iframe' ),
					'url'  => esc_url_raw( $urls[ $i ] ?? '' ),
				);
			}
		}
		update_post_meta( $post_id, '_doodh_servers', $clean_servers );
	}

	// Download Links
	if ( isset( $_POST['doodh_dl_server'] ) && is_array( $_POST['doodh_dl_server'] ) ) {
		$clean_dls = array();
		$servers   = $_POST['doodh_dl_server'];
		$qualities = $_POST['doodh_dl_quality'] ?? array();
		$sizes     = $_POST['doodh_dl_size'] ?? array();
		$urls      = $_POST['doodh_dl_url'] ?? array();

		for ( $i = 0; $i < count( $servers ); $i++ ) {
			if ( ! empty( $urls[ $i ] ) || ! empty( $servers[ $i ] ) ) {
				$clean_dls[] = array(
					'server'  => sanitize_text_field( $servers[ $i ] ),
					'quality' => sanitize_text_field( $qualities[ $i ] ?? 'HD' ),
					'size'    => sanitize_text_field( $sizes[ $i ] ?? '' ),
					'url'     => esc_url_raw( $urls[ $i ] ?? '' ),
				);
			}
		}
		update_post_meta( $post_id, '_doodh_downloads', $clean_dls );
	}

	// TV Show Meta
	if ( isset( $_POST['doodhtheme_tv_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_tv_nonce'], 'doodhtheme_save_tv_meta' ) ) {
		$tv_fields = array(
			'_doodh_original_title'  => 'sanitize_text_field',
			'_doodh_first_air_date'  => 'sanitize_text_field',
			'_doodh_last_air_date'   => 'sanitize_text_field',
			'_doodh_total_seasons'   => 'intval',
			'_doodh_total_episodes'  => 'intval',
			'_doodh_episode_runtime' => 'intval',
			'_doodh_rating'          => 'sanitize_text_field',
			'_doodh_votes'           => 'intval',
			'_doodh_trailer_url'     => 'esc_url_raw',
			'_doodh_backdrop_url'    => 'esc_url_raw',
			'_doodh_poster_url'      => 'esc_url_raw',
			'_doodh_status'          => 'sanitize_text_field',
		);

		foreach ( $tv_fields as $meta_key => $sanitizer ) {
			$post_key = str_replace( '_', '', $meta_key );
			if ( isset( $_POST[ $post_key ] ) ) {
				update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, $_POST[ $post_key ] ) );
			}
		}
	}

	// Season Meta
	if ( isset( $_POST['doodhtheme_season_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_season_nonce'], 'doodhtheme_save_season_meta' ) ) {
		if ( isset( $_POST['doodh_season_tv_id'] ) ) {
			update_post_meta( $post_id, '_doodh_tv_id', intval( $_POST['doodh_season_tv_id'] ) );
		}
		if ( isset( $_POST['doodh_season_number'] ) ) {
			update_post_meta( $post_id, '_doodh_season_number', intval( $_POST['doodh_season_number'] ) );
		}
		if ( isset( $_POST['doodh_season_air_date'] ) ) {
			update_post_meta( $post_id, '_doodh_air_date', sanitize_text_field( $_POST['doodh_season_air_date'] ) );
		}
	}

	// Episode Meta
	if ( isset( $_POST['doodhtheme_episode_nonce'] ) && wp_verify_nonce( $_POST['doodhtheme_episode_nonce'], 'doodhtheme_save_episode_meta' ) ) {
		if ( isset( $_POST['doodh_ep_tv_id'] ) ) {
			update_post_meta( $post_id, '_doodh_tv_id', intval( $_POST['doodh_ep_tv_id'] ) );
		}
		if ( isset( $_POST['doodh_ep_season_num'] ) ) {
			update_post_meta( $post_id, '_doodh_season_number', intval( $_POST['doodh_ep_season_num'] ) );
		}
		if ( isset( $_POST['doodh_ep_number'] ) ) {
			update_post_meta( $post_id, '_doodh_episode_number', intval( $_POST['doodh_ep_number'] ) );
		}
		if ( isset( $_POST['doodh_ep_name'] ) ) {
			update_post_meta( $post_id, '_doodh_episode_name', sanitize_text_field( $_POST['doodh_ep_name'] ) );
		}
		if ( isset( $_POST['doodh_ep_air_date'] ) ) {
			update_post_meta( $post_id, '_doodh_air_date', sanitize_text_field( $_POST['doodh_ep_air_date'] ) );
		}
		if ( isset( $_POST['doodh_ep_still_url'] ) ) {
			update_post_meta( $post_id, '_doodh_still_url', esc_url_raw( $_POST['doodh_ep_still_url'] ) );
		}
	}
}
add_action( 'save_post', 'doodhtheme_save_post_meta' );
