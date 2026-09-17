<?php
/**
 * Single Episode Template
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$ep_id      = get_the_ID();
	$tv_id      = (int) get_post_meta( $ep_id, '_doodh_tv_id', true );
	$season_num = (int) get_post_meta( $ep_id, '_doodh_season_number', true ) ?: 1;
	$ep_num     = (int) get_post_meta( $ep_id, '_doodh_episode_number', true ) ?: 1;
	$ep_name    = get_post_meta( $ep_id, '_doodh_episode_name', true ) ?: get_the_title();
	$tv_title   = $tv_id ? get_the_title( $tv_id ) : __( 'TV Series', 'vmtheme' );
	$tv_url     = $tv_id ? get_permalink( $tv_id ) : home_url( '/' );

	// Find Previous and Next Episode
	$prev_ep = get_posts( array(
		'post_type'      => 'episodes',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array( 'key' => '_doodh_tv_id', 'value' => $tv_id ),
			array( 'key' => '_doodh_season_number', 'value' => $season_num ),
			array( 'key' => '_doodh_episode_number', 'value' => $ep_num - 1 ),
		),
	) );

	$next_ep = get_posts( array(
		'post_type'      => 'episodes',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array( 'key' => '_doodh_tv_id', 'value' => $tv_id ),
			array( 'key' => '_doodh_season_number', 'value' => $season_num ),
			array( 'key' => '_doodh_episode_number', 'value' => $ep_num + 1 ),
		),
	) );
	?>

	<main class="container" style="padding-top: 30px;">
		<!-- Episode Title Bar & Breadcrumbs -->
		<div style="margin-bottom: 20px;">
			<div style="font-size: 13px; color: var(--dt-text-muted); margin-bottom: 8px;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i></a> &raquo; 
				<a href="<?php echo esc_url( $tv_url ); ?>"><?php echo esc_html( $tv_title ); ?></a> &raquo; 
				<span><?php printf( esc_html__( 'Season %d', 'vmtheme' ), $season_num ); ?></span> &raquo; 
				<span style="color:#fff;"><?php printf( esc_html__( 'Episode %d', 'vmtheme' ), $ep_num ); ?></span>
			</div>
			<h1 style="font-size: 28px; font-weight: 800; color:#fff;">
				<?php echo esc_html( $tv_title ); ?>: <span style="color:var(--dt-primary);"><?php printf( esc_html__( 'S%02dE%02d', 'vmtheme' ), $season_num, $ep_num ); ?></span> - <?php echo esc_html( $ep_name ); ?>
			</h1>
		</div>

		<!-- Ad Slot: Above Player -->
		<?php doodhtheme_display_ad( 'player_top' ); ?>

		<!-- Video Player -->
		<section class="doodh-section">
			<?php doodhtheme_render_player( $ep_id ); ?>

			<!-- Prev / Next Navigation Bar -->
			<div style="display:flex; justify-content:space-between; align-items:center; background:var(--dt-bg-surface); padding:12px 18px; border:1px solid var(--dt-border); border-radius:var(--dt-radius); margin-top:15px; flex-wrap:wrap; gap:10px;">
				<div>
					<?php if ( ! empty( $prev_ep ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( $prev_ep[0]->ID ) ); ?>" class="doodh-btn-secondary" style="padding:8px 16px; font-size:13px;">
							<i class="fas fa-step-backward"></i> <?php esc_html_e( 'Previous Episode', 'vmtheme' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div>
					<a href="<?php echo esc_url( $tv_url ); ?>" class="doodh-btn-secondary" style="padding:8px 16px; font-size:13px;">
						<i class="fas fa-th-list"></i> <?php esc_html_e( 'All Episodes', 'vmtheme' ); ?>
					</a>
				</div>

				<div>
					<?php if ( ! empty( $next_ep ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next_ep[0]->ID ) ); ?>" class="doodh-btn-primary" style="padding:8px 18px; font-size:13px;">
							<?php esc_html_e( 'Next Episode', 'vmtheme' ); ?> <i class="fas fa-step-forward"></i>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<!-- Ad Slot: Below Player -->
		<?php doodhtheme_display_ad( 'player_bottom' ); ?>

		<!-- Episode Synopsis -->
		<?php if ( get_the_content() ) : ?>
			<div style="background:var(--dt-bg-surface); border:1px solid var(--dt-border); border-radius:var(--dt-radius); padding:20px; margin-bottom:30px;">
				<h3 style="font-size:16px; font-weight:700; margin-bottom:10px; color:#fff;"><?php esc_html_e( 'Episode Overview', 'vmtheme' ); ?></h3>
				<p style="color:#cbd5e1; font-size:14px; line-height:1.7;"><?php the_content(); ?></p>
			</div>
		<?php endif; ?>

		<!-- Downloads Box -->
		<?php doodhtheme_render_downloads( $ep_id ); ?>

		<!-- Reviews Module -->
		<?php doodhtheme_render_reviews_section( $ep_id ); ?>

		<!-- ══════════════════════════════════════════════════════════════
		     Similar TV Series (Shown After Ratings & Reviews)
		     ══════════════════════════════════════════════════════════════ -->
		<?php
		$ep_genres = $tv_id ? get_the_terms( $tv_id, 'genres' ) : array();
		$ep_genre_ids = ! empty( $ep_genres ) && ! is_wp_error( $ep_genres ) ? wp_list_pluck( $ep_genres, 'term_id' ) : array();

		$related_ep_args = array(
			'post_type'      => 'tvshows',
			'posts_per_page' => 12,
			'post_status'    => 'publish',
			'post__not_in'   => $tv_id ? array( $tv_id ) : array(),
		);

		if ( ! empty( $ep_genre_ids ) ) {
			$related_ep_args['tax_query'] = array(
				array(
					'taxonomy' => 'genres',
					'field'    => 'term_id',
					'terms'    => $ep_genre_ids,
				),
			);
		} else {
			$related_ep_args['orderby'] = 'rand';
		}

		$related_ep_query = new WP_Query( $related_ep_args );

		// Fallback if genre results are fewer than 6
		if ( $related_ep_query->post_count < 6 ) {
			$fallback_args = array(
				'post_type'      => 'tvshows',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
				'post__not_in'   => $tv_id ? array( $tv_id ) : array(),
				'orderby'        => 'date',
				'order'          => 'DESC',
			);
			$related_ep_query = new WP_Query( $fallback_args );
		}

		if ( $related_ep_query->have_posts() ) :
			?>
			<section class="doodh-section doodh-similar-tvshows-section" style="margin-top:40px;">
				<div class="doodh-section-header">
					<h3 class="doodh-section-title">
						<i class="fas fa-layer-group" style="color:var(--dt-primary);"></i> 
						<?php esc_html_e( 'Similar TV Series You May Like', 'vmtheme' ); ?>
					</h3>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>" class="doodh-view-all">
						<?php esc_html_e( 'View All TV Series', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				</div>
				<div class="doodh-grid doodh-grid-movies">
					<?php
					while ( $related_ep_query->have_posts() ) :
						$related_ep_query->the_post();
						$rel_id      = get_the_ID();
						$rel_poster  = doodhtheme_get_poster_url( $rel_id );
						$rel_rating  = doodhtheme_get_rating( $rel_id );
						$rel_year    = doodhtheme_get_release_year( $rel_id );
						$rel_seasons = (int) get_post_meta( $rel_id, '_doodh_total_seasons', true ) ?: 1;
						?>
						<article class="doodh-card">
							<div class="doodh-poster">
								<img src="<?php echo esc_url( $rel_poster ); ?>" alt="<?php echo esc_attr( doodhtheme_get_poster_alt( $rel_id ) ); ?>" loading="lazy" decoding="async" width="300" height="450" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_poster_url() ); ?>';">
								<span class="doodh-badge-quality" style="background:#2563eb;"><?php printf( esc_html__( 'SS %d', 'vmtheme' ), $rel_seasons ); ?></span>
								<span class="doodh-badge-rating"><i class="fas fa-star"></i> <?php echo esc_html( $rel_rating ); ?></span>
								<div class="doodh-poster-overlay">
									<a href="<?php the_permalink(); ?>" class="doodh-play-btn" aria-label="<?php the_title_attribute(); ?>"><i class="fas fa-play"></i></a>
								</div>
							</div>
							<div class="doodh-card-body">
								<h4 class="doodh-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
								<div class="doodh-card-meta">
									<span><i class="far fa-calendar-alt"></i> <?php echo esc_html( $rel_year ); ?></span>
									<span><i class="fas fa-tv"></i> <?php esc_html_e( 'Series', 'vmtheme' ); ?></span>
								</div>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	</main>

	<?php
endwhile;

get_footer();
