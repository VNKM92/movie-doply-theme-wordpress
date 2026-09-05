<?php
/**
 * The Header for DoodhTheme
 * Production-Grade Responsive Navigation & Core Web Vitals Optimization
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	
	<!-- Performance Resource Preconnects -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preconnect" href="https://image.tmdb.org" crossorigin>
	<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
	<link rel="dns-prefetch" href="https://image.tmdb.org">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Header Navigation -->
<header class="doodh-header" id="site-header">
	<div class="container">
		<div class="doodh-nav-wrap">
			<!-- Mobile Hamburger Toggle -->
			<button type="button" class="doodh-mobile-toggle" id="doodh-mobile-menu-btn" aria-label="<?php esc_attr_e( 'Open Navigation Menu', 'doodhtheme' ); ?>">
				<span></span>
				<span></span>
				<span></span>
			</button>

			<!-- Brand / Logo -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-brand">
				<?php doodhtheme_render_brand_logo(); ?>
			</a>

			<!-- Desktop Primary Menu with Rich Dropdowns -->
			<nav class="doodh-menu" id="doodh-primary-nav">
				<ul class="doodh-nav-list">
					<li class="<?php echo is_front_page() ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'doodhtheme' ); ?></a>
					</li>
					<li class="<?php echo ( is_post_type_archive( 'movies' ) || is_singular( 'movies' ) ) ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>"><i class="fas fa-film"></i> <?php esc_html_e( 'Movies', 'doodhtheme' ); ?></a>
					</li>
					<li class="<?php echo ( is_post_type_archive( 'tvshows' ) || is_singular( 'tvshows' ) || is_singular( 'episodes' ) ) ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>"><i class="fas fa-tv"></i> <?php esc_html_e( 'TV Shows', 'doodhtheme' ); ?></a>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/top-imdb/' ) ); ?>"><i class="fas fa-trophy"></i> <?php esc_html_e( 'Top 100', 'doodhtheme' ); ?></a>
					</li>

					<!-- Genres Dropdown -->
					<li class="doodh-has-dropdown">
						<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>">
							<i class="fas fa-tags"></i> <?php esc_html_e( 'Genres', 'doodhtheme' ); ?> <i class="fas fa-chevron-down doodh-arrow"></i>
						</a>
						<div class="doodh-dropdown-menu">
							<div class="doodh-dropdown-grid">
								<?php
								$menu_genres = get_terms( array( 'taxonomy' => 'genres', 'number' => 12, 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false ) );
								if ( ! empty( $menu_genres ) && ! is_wp_error( $menu_genres ) ) {
									foreach ( $menu_genres as $mg ) {
										echo '<a href="' . esc_url( get_term_link( $mg ) ) . '"><i class="fas fa-fire"></i> ' . esc_html( $mg->name ) . '</a>';
									}
								}
								?>
							</div>
							<div class="doodh-dropdown-footer">
								<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><?php esc_html_e( 'View All Genres &rarr;', 'doodhtheme' ); ?></a>
							</div>
						</div>
					</li>

					<!-- Years Dropdown -->
					<li class="doodh-has-dropdown">
						<a href="<?php echo esc_url( home_url( '/years/' ) ); ?>">
							<i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Years', 'doodhtheme' ); ?> <i class="fas fa-chevron-down doodh-arrow"></i>
						</a>
						<div class="doodh-dropdown-menu doodh-dropdown-years">
							<div class="doodh-years-pills">
								<?php
								for ( $yr = 2026; $yr >= 2010; $yr-- ) {
									$term_obj = get_term_by( 'slug', (string) $yr, 'release-year' );
									$link = ( $term_obj && ! is_wp_error( $term_obj ) ) ? get_term_link( $term_obj ) : home_url( "/?filter=1&release_year={$yr}" );
									echo '<a href="' . esc_url( $link ) . '" class="doodh-year-pill">' . esc_html( $yr ) . '</a>';
								}
								?>
							</div>
						</div>
					</li>
				</ul>
			</nav>

			<!-- Live Instant Search Bar (Desktop & Tablet) -->
			<div class="doodh-search-box" id="doodh-header-search-box">
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-search-form">
					<input type="search" 
						   id="doodh-live-search-input" 
						   class="doodh-search-input" 
						   placeholder="<?php esc_attr_e( 'Search movies, TV shows, actors...', 'doodhtheme' ); ?>" 
						   value="<?php echo get_search_query(); ?>" 
						   name="s" 
						   autocomplete="off">
					<button type="submit" class="doodh-search-submit" aria-label="<?php esc_attr_e( 'Submit Search', 'doodhtheme' ); ?>">
						<i class="fas fa-search"></i>
					</button>
				</form>
				<div class="doodh-live-results" id="doodh-live-results-box"></div>
			</div>

			<!-- Mobile Search Toggle Button -->
			<button type="button" class="doodh-mobile-search-toggle" id="doodh-mobile-search-btn" aria-label="<?php esc_attr_e( 'Toggle Search', 'doodhtheme' ); ?>">
				<i class="fas fa-search"></i>
			</button>

			<!-- Header Action Buttons -->
			<div class="doodh-header-actions">
				<a href="<?php echo esc_url( home_url( '/request/' ) ); ?>" class="doodh-action-icon-btn doodh-hide-mobile" title="<?php esc_attr_e( 'Request Title', 'doodhtheme' ); ?>">
					<i class="fas fa-plus-circle"></i>
					<span class="doodh-btn-text"><?php esc_html_e( 'Request', 'doodhtheme' ); ?></span>
				</a>
				<a href="<?php echo esc_url( home_url( '/watchlist/' ) ); ?>" class="doodh-action-icon-btn" id="doodh-watchlist-nav-btn" title="<?php esc_attr_e( 'My Saved Watchlist', 'doodhtheme' ); ?>">
					<i class="fas fa-bookmark"></i>
					<span class="doodh-btn-text"><?php esc_html_e( 'Watchlist', 'doodhtheme' ); ?></span>
					<span class="doodh-watchlist-count-badge" id="doodh-nav-fav-count" style="display:none;">0</span>
				</a>
			</div>
		</div>

		<!-- Mobile Search Dropdown Bar -->
		<div class="doodh-mobile-search-bar" id="doodh-mobile-search-bar" style="display:none;">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-mobile-search-form">
				<input type="search" 
					   id="doodh-mobile-search-input" 
					   class="doodh-search-input" 
					   placeholder="<?php esc_attr_e( 'Search movies, shows...', 'doodhtheme' ); ?>" 
					   value="<?php echo get_search_query(); ?>" 
					   name="s" 
					   autocomplete="off">
				<button type="submit" class="doodh-search-submit" aria-label="<?php esc_attr_e( 'Search', 'doodhtheme' ); ?>">
					<i class="fas fa-search"></i>
				</button>
			</form>
			<div class="doodh-live-results" id="doodh-mobile-live-results-box"></div>
		</div>
	</div>
</header>

<!-- Mobile Navigation Drawer Overlay -->
<div class="doodh-drawer-overlay" id="doodh-drawer-overlay"></div>
<div class="doodh-mobile-drawer" id="doodh-mobile-drawer">
	<div class="doodh-drawer-header">
		<div class="doodh-brand">
			<div class="doodh-logo-icon"><i class="fas fa-play"></i></div>
			<span><?php bloginfo( 'name' ); ?></span>
		</div>
		<button type="button" class="doodh-drawer-close" id="doodh-drawer-close">&times;</button>
	</div>

	<div class="doodh-drawer-body">
		<!-- Drawer Quick Search Form -->
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-drawer-search-form">
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search title or actor...', 'doodhtheme' ); ?>" class="doodh-drawer-search-input" autocomplete="off" required>
			<button type="submit" class="doodh-drawer-search-btn"><i class="fas fa-search"></i></button>
		</form>

		<ul class="doodh-drawer-nav">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>"><i class="fas fa-film"></i> <?php esc_html_e( 'Movies', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>"><i class="fas fa-tv"></i> <?php esc_html_e( 'TV Shows', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/top-imdb/' ) ); ?>"><i class="fas fa-trophy"></i> <?php esc_html_e( 'Top 100 IMDb', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-tags"></i> <?php esc_html_e( 'Genres Directory', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/years/' ) ); ?>"><i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Release Years', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/request/' ) ); ?>"><i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Request Title', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/watchlist/' ) ); ?>"><i class="fas fa-bookmark"></i> <?php esc_html_e( 'My Watchlist', 'doodhtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/dmca/' ) ); ?>"><i class="fas fa-shield-alt"></i> <?php esc_html_e( 'DMCA Policy', 'doodhtheme' ); ?></a></li>
		</ul>
	</div>
</div>

<!-- Header Top Banner Ad Slot -->
<?php doodhtheme_display_ad( 'header' ); ?>