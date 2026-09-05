<?php
/**
 * Single Episode Template
 *
 * @package DoodhTheme
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
	$tv_title   = $tv_id ? get_the_title( $tv_id ) : __( 'TV Series', 'doodhtheme' );
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
				<span><?php printf( esc_html__( 'Season %d', 'doodhtheme' ), $season_num ); ?></span> &raquo; 
				<span style="color:#fff;"><?php printf( esc_html__( 'Episode %d', 'doodhtheme' ), $ep_num ); ?></span>
			</div>
			<h1 style="font-size: 28px; font-weight: 800; color:#fff;">
				<?php echo esc_html( $tv_title ); ?>: <span style="color:var(--dt-primary);"><?php printf( esc_html__( 'S%02dE%02d', 'doodhtheme' ), $season_num, $ep_num ); ?></span> - <?php echo esc_html( $ep_name ); ?>
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
							<i class="fas fa-step-backward"></i> <?php esc_html_e( 'Previous Episode', 'doodhtheme' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div>
					<a href="<?php echo esc_url( $tv_url ); ?>" class="doodh-btn-secondary" style="padding:8px 16px; font-size:13px;">
						<i class="fas fa-th-list"></i> <?php esc_html_e( 'All Episodes', 'doodhtheme' ); ?>
					</a>
				</div>

				<div>
					<?php if ( ! empty( $next_ep ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next_ep[0]->ID ) ); ?>" class="doodh-btn-primary" style="padding:8px 18px; font-size:13px;">
							<?php esc_html_e( 'Next Episode', 'doodhtheme' ); ?> <i class="fas fa-step-forward"></i>
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
				<h3 style="font-size:16px; font-weight:700; margin-bottom:10px; color:#fff;"><?php esc_html_e( 'Episode Overview', 'doodhtheme' ); ?></h3>
				<p style="color:#cbd5e1; font-size:14px; line-height:1.7;"><?php the_content(); ?></p>
			</div>
		<?php endif; ?>

		<!-- Downloads Box -->
		<?php doodhtheme_render_downloads( $ep_id ); ?>

		<!-- Reviews Module -->
		<?php doodhtheme_render_reviews_section( $ep_id ); ?>
	</main>

	<?php
endwhile;

get_footer();
