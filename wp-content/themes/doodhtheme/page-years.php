<?php
/**
 * Template Name: Release Years Timeline
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fetch Year terms from taxonomy
$year_terms = get_terms( array(
	'taxonomy'   => 'release-year',
	'hide_empty' => false,
	'orderby'    => 'name',
	'order'      => 'DESC',
) );

$year_counts = array();
if ( ! is_wp_error( $year_terms ) && ! empty( $year_terms ) ) {
	foreach ( $year_terms as $y ) {
		$link = get_term_link( $y );
		$year_counts[ $y->name ] = array(
			'count' => $y->count,
			'url'   => ! is_wp_error( $link ) ? $link : home_url( '/?filter=1&release_year=' . $y->slug ),
		);
		$year_counts[ $y->slug ] = $year_counts[ $y->name ];
	}
}
?>

<main class="container" style="padding-top: 35px;">
	<div class="doodh-section-header">
		<div>
			<h1 class="doodh-section-title"><i class="fas fa-calendar-alt" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Release Years Timeline (1990 - 2026)', 'vmtheme' ); ?></h1>
			<p style="color:var(--dt-text-muted); font-size:14px; margin-top:4px;"><?php esc_html_e( 'Browse your favorite movies and TV shows sorted year by year from modern classics to upcoming releases.', 'vmtheme' ); ?></p>
		</div>
	</div>

	<!-- Years Timeline Grid -->
	<div class="doodh-years-grid">
		<?php for ( $yr = 2026; $yr >= 1990; $yr-- ) : 
			$yr_str = (string) $yr;
			$count  = isset( $year_counts[ $yr_str ] ) ? $year_counts[ $yr_str ]['count'] : 0;
			$url    = isset( $year_counts[ $yr_str ] ) ? $year_counts[ $yr_str ]['url'] : home_url( '/?filter=1&release_year=' . $yr_str );
			$is_upcoming = ( $yr >= 2025 );
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="doodh-year-card <?php echo $is_upcoming ? 'doodh-year-upcoming' : ''; ?>">
				<div class="doodh-year-badge"><?php echo $is_upcoming ? esc_html__( 'Premiere', 'vmtheme' ) : esc_html__( 'Year', 'vmtheme' ); ?></div>
				<h2 class="doodh-year-num"><?php echo esc_html( $yr ); ?></h2>
				<span class="doodh-year-count">
					<i class="fas fa-film"></i> <?php printf( esc_html__( '%d Titles', 'vmtheme' ), $count ); ?>
				</span>
			</a>
		<?php endfor; ?>
	</div>
</main>

<?php
get_footer();
