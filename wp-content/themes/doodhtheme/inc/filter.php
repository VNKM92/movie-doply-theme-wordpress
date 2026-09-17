<?php
/**
 * Dynamic Filter and Archive Query Handler
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Frontend Filter Bar for Archives & Search
 */
function doodhtheme_render_filter_bar( $current_type = 'movies' ) {
	$genres    = get_terms( array( 'taxonomy' => 'genres', 'hide_empty' => false ) );
	$years     = get_terms( array( 'taxonomy' => 'release-year', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'DESC' ) );
	$qualities = get_terms( array( 'taxonomy' => 'dtquality', 'hide_empty' => false ) );

	$sel_type    = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : $current_type;
	$sel_genre   = isset( $_GET['genre'] ) ? sanitize_text_field( $_GET['genre'] ) : '';
	$sel_year    = isset( $_GET['year'] ) ? sanitize_text_field( $_GET['year'] ) : '';
	$sel_quality = isset( $_GET['quality'] ) ? sanitize_text_field( $_GET['quality'] ) : '';
	$sel_order   = isset( $_GET['order_by'] ) ? sanitize_text_field( $_GET['order_by'] ) : 'date_desc';
	?>
	<div class="doodh-filter-bar">
		<form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-filter-form" id="doodh-filter-form">
			<input type="hidden" name="filter" value="1">
			
			<!-- Type Selector -->
			<div class="doodh-filter-group">
				<label><i class="fas fa-layer-group"></i> <?php esc_html_e( 'Type', 'vmtheme' ); ?></label>
				<select name="post_type" class="doodh-filter-select">
					<option value="movies" <?php selected( $sel_type, 'movies' ); ?>><?php esc_html_e( 'Movies', 'vmtheme' ); ?></option>
					<option value="tvshows" <?php selected( $sel_type, 'tvshows' ); ?>><?php esc_html_e( 'TV Shows', 'vmtheme' ); ?></option>
				</select>
			</div>

			<!-- Genre Selector -->
			<div class="doodh-filter-group">
				<label><i class="fas fa-tags"></i> <?php esc_html_e( 'Genre', 'vmtheme' ); ?></label>
				<select name="genre" class="doodh-filter-select">
					<option value=""><?php esc_html_e( 'All Genres', 'vmtheme' ); ?></option>
					<?php if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) : ?>
						<?php foreach ( $genres as $g ) : ?>
							<option value="<?php echo esc_attr( $g->slug ); ?>" <?php selected( $sel_genre, $g->slug ); ?>>
								<?php echo esc_html( $g->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>

			<!-- Year Selector -->
			<div class="doodh-filter-group">
				<label><i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Year', 'vmtheme' ); ?></label>
				<select name="release_year" class="doodh-filter-select">
					<option value=""><?php esc_html_e( 'All Years', 'vmtheme' ); ?></option>
					<?php if ( ! is_wp_error( $years ) && ! empty( $years ) ) : ?>
						<?php foreach ( $years as $y ) : ?>
							<option value="<?php echo esc_attr( $y->slug ); ?>" <?php selected( $sel_year, $y->slug ); ?>>
								<?php echo esc_html( $y->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php else : ?>
						<?php for ( $yr = (int) date( 'Y' ); $yr >= 1990; $yr-- ) : ?>
							<option value="<?php echo esc_attr( $yr ); ?>" <?php selected( $sel_year, (string) $yr ); ?>><?php echo esc_html( $yr ); ?></option>
						<?php endfor; ?>
					<?php endif; ?>
				</select>
			</div>

			<!-- Quality Selector -->
			<div class="doodh-filter-group">
				<label><i class="fas fa-tv"></i> <?php esc_html_e( 'Quality', 'vmtheme' ); ?></label>
				<select name="quality" class="doodh-filter-select">
					<option value=""><?php esc_html_e( 'All Qualities', 'vmtheme' ); ?></option>
					<?php if ( ! is_wp_error( $qualities ) && ! empty( $qualities ) ) : ?>
						<?php foreach ( $qualities as $q ) : ?>
							<option value="<?php echo esc_attr( $q->slug ); ?>" <?php selected( $sel_quality, $q->slug ); ?>>
								<?php echo esc_html( $q->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>

			<!-- Order / Sort -->
			<div class="doodh-filter-group">
				<label><i class="fas fa-sort-amount-down"></i> <?php esc_html_e( 'Sort By', 'vmtheme' ); ?></label>
				<select name="order_by" class="doodh-filter-select">
					<option value="date_desc" <?php selected( $sel_order, 'date_desc' ); ?>><?php esc_html_e( 'Recently Added', 'vmtheme' ); ?></option>
					<option value="rating_desc" <?php selected( $sel_order, 'rating_desc' ); ?>><?php esc_html_e( 'Highest Rating', 'vmtheme' ); ?></option>
					<option value="title_asc" <?php selected( $sel_order, 'title_asc' ); ?>><?php esc_html_e( 'Title (A - Z)', 'vmtheme' ); ?></option>
					<option value="title_desc" <?php selected( $sel_order, 'title_desc' ); ?>><?php esc_html_e( 'Title (Z - A)', 'vmtheme' ); ?></option>
				</select>
			</div>

			<!-- Submit Button -->
			<div class="doodh-filter-action">
				<button type="submit" class="doodh-btn-filter">
					<i class="fas fa-sliders-h"></i> <?php esc_html_e( 'Filter', 'vmtheme' ); ?>
				</button>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Handle Custom Filter and Search Queries
 */
function doodhtheme_handle_filter_query( $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		// Include both movies & tvshows in search results
		if ( $query->is_search() ) {
			$query->set( 'post_type', array( 'movies', 'tvshows' ) );
			return;
		}

		if ( isset( $_GET['filter'] ) || isset( $_GET['filter_type'] ) ) {
			$post_type = sanitize_text_field( $_GET['post_type'] ?? ( $_GET['type'] ?? 'movies' ) );
			if ( in_array( $post_type, array( 'movies', 'tvshows' ), true ) ) {
				$query->set( 'post_type', $post_type );
			} else {
				$query->set( 'post_type', array( 'movies', 'tvshows' ) );
			}

			// Prevent front-page 404 on custom queries
			if ( $query->is_home() ) {
				$query->is_home = false;
				$query->is_archive = true;
			}

			$tax_query = array( 'relation' => 'AND' );

			$genre = sanitize_text_field( $_GET['genre'] ?? '' );
			if ( ! empty( $genre ) ) {
				$tax_query[] = array(
					'taxonomy' => 'genres',
					'field'    => 'slug',
					'terms'    => $genre,
				);
			}

			$year = sanitize_text_field( $_GET['release_year'] ?? ( $_GET['year'] ?? '' ) );
			if ( ! empty( $year ) ) {
				$tax_query[] = array(
					'taxonomy' => 'release-year',
					'field'    => 'slug',
					'terms'    => $year,
				);
			}

			$quality = sanitize_text_field( $_GET['quality'] ?? '' );
			if ( ! empty( $quality ) ) {
				$tax_query[] = array(
					'taxonomy' => 'dtquality',
					'field'    => 'slug',
					'terms'    => $quality,
				);
			}

			if ( count( $tax_query ) > 1 ) {
				$query->set( 'tax_query', $tax_query );
			}

			$order_by = sanitize_text_field( $_GET['order_by'] ?? ( $_GET['order'] ?? 'date_desc' ) );
			switch ( $order_by ) {
				case 'rating_desc':
				case 'popular':
					$query->set( 'meta_key', '_doodh_rating' );
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'order', 'DESC' );
					break;
				case 'title_asc':
					$query->set( 'orderby', 'title' );
					$query->set( 'order', 'ASC' );
					break;
				case 'title_desc':
					$query->set( 'orderby', 'title' );
					$query->set( 'order', 'DESC' );
					break;
				case 'date_desc':
				default:
					$query->set( 'orderby', 'date' );
					$query->set( 'order', 'DESC' );
					break;
			}
		}
	}
}
add_action( 'pre_get_posts', 'doodhtheme_handle_filter_query' );

/**
 * Route Filter Queries to Archive Template & Prevent False 404s
 */
function doodhtheme_filter_template_include( $template ) {
	if ( ! is_admin() && isset( $_GET['filter'] ) ) {
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );

		$post_type = sanitize_text_field( $_GET['post_type'] ?? ( $_GET['type'] ?? 'movies' ) );
		if ( $post_type === 'tvshows' ) {
			$archive_template = locate_template( 'archive-tvshows.php' );
		} else {
			$archive_template = locate_template( 'archive-movies.php' );
		}

		if ( $archive_template ) {
			return $archive_template;
		}

		$fallback = locate_template( 'archive.php' );
		if ( $fallback ) {
			return $fallback;
		}
	}
	return $template;
}
add_filter( 'template_include', 'doodhtheme_filter_template_include', 99 );
