<?php
/**
 * The Header for DoodhTheme
 * Production-Grade Responsive Navigation & Core Web Vitals Optimization
 *
 * @package VMTheme
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

	<!-- Advanced SEO & AI Agent Discovery -->
	<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
	<link rel="alternate" type="text/markdown" title="LLM Agent Manifest" href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>">

	<?php
	$site_favicon = get_option( 'vm_site_favicon' );
	if ( ! empty( $site_favicon ) ) : ?>
		<link rel="icon" href="<?php echo esc_url( $site_favicon ); ?>">
		<link rel="apple-touch-icon" href="<?php echo esc_url( $site_favicon ); ?>">
	<?php endif; ?>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Header Navigation -->
<header class="doodh-header" id="site-header">
	<div class="container">
		<div class="doodh-nav-wrap">
			<!-- Mobile Hamburger Toggle -->
			<button type="button" class="doodh-mobile-toggle" id="doodh-mobile-menu-btn" aria-label="<?php esc_attr_e( 'Open Navigation Menu', 'vmtheme' ); ?>">
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
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'menu_class'     => 'doodh-nav-list',
						'container'      => false,
						'fallback_cb'    => false,
						'depth'          => 2,
					) );
				} else {
				?>
				<ul class="doodh-nav-list">
					<li class="<?php echo is_front_page() ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'vmtheme' ); ?></a>
					</li>
					<li class="<?php echo ( is_post_type_archive( 'movies' ) || is_singular( 'movies' ) ) ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>"><i class="fas fa-film"></i> <?php esc_html_e( 'Movies', 'vmtheme' ); ?></a>
					</li>
					<li class="<?php echo ( is_post_type_archive( 'tvshows' ) || is_singular( 'tvshows' ) || is_singular( 'episodes' ) ) ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>"><i class="fas fa-tv"></i> <?php esc_html_e( 'TV Shows', 'vmtheme' ); ?></a>
					</li>
					<li>
						<a href="<?php echo esc_url( home_url( '/top-imdb/' ) ); ?>"><i class="fas fa-trophy"></i> <?php esc_html_e( 'Top 100', 'vmtheme' ); ?></a>
					</li>

					<!-- Genres Dropdown -->
					<li class="doodh-has-dropdown">
						<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>">
							<i class="fas fa-tags"></i> <?php esc_html_e( 'Genres', 'vmtheme' ); ?> <i class="fas fa-chevron-down doodh-arrow"></i>
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
								<a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><?php esc_html_e( 'View All Genres &rarr;', 'vmtheme' ); ?></a>
							</div>
						</div>
					</li>

					<!-- Years Dropdown -->
					<li class="doodh-has-dropdown">
						<a href="<?php echo esc_url( home_url( '/years/' ) ); ?>">
							<i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Years', 'vmtheme' ); ?> <i class="fas fa-chevron-down doodh-arrow"></i>
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

					<!-- Blog Link -->
					<li class="<?php echo ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_page_template( 'page-blog.php' ) ) ? 'current-menu-item' : ''; ?>">
						<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
							<i class="fas fa-newspaper"></i> <?php esc_html_e( 'Blog', 'vmtheme' ); ?>
						</a>
					</li>
				</ul>
				<?php } ?>
			</nav>

			<!-- Live Instant Search Bar (Desktop & Tablet) -->
			<div class="doodh-search-box" id="doodh-header-search-box">
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-search-form">
					<input type="search" 
						   id="doodh-live-search-input" 
						   class="doodh-search-input" 
						   placeholder="<?php esc_attr_e( 'Search movies, TV shows, actors...', 'vmtheme' ); ?>" 
						   value="<?php echo get_search_query(); ?>" 
						   name="s" 
						   autocomplete="off">
					<button type="button" class="doodh-search-clear-btn doodh-header-clear-btn" id="doodh-header-search-clear" title="<?php esc_attr_e( 'Clear search', 'vmtheme' ); ?>" style="display:none;">
						<i class="fas fa-times"></i>
					</button>
					<span class="doodh-header-kbd-badge" title="Press Ctrl+K to search"><kbd>⌘K</kbd></span>
					<button type="submit" class="doodh-search-submit" aria-label="<?php esc_attr_e( 'Submit Search', 'vmtheme' ); ?>">
						<i class="fas fa-search"></i>
					</button>
				</form>
				<div class="doodh-live-results" id="doodh-live-results-box"></div>
			</div>

			<!-- Mobile Search Toggle Button -->
			<button type="button" class="doodh-mobile-search-toggle" id="doodh-mobile-search-btn" aria-label="<?php esc_attr_e( 'Toggle Search', 'vmtheme' ); ?>">
				<i class="fas fa-search"></i>
			</button>

			<!-- Header Action Buttons -->
			<div class="doodh-header-actions">
				<a href="<?php echo esc_url( home_url( '/request/' ) ); ?>" class="doodh-action-icon-btn doodh-hide-mobile" title="<?php esc_attr_e( 'Request Title', 'vmtheme' ); ?>">
					<i class="fas fa-plus-circle"></i>
					<span class="doodh-btn-text"><?php esc_html_e( 'Request', 'vmtheme' ); ?></span>
				</a>
				<a href="<?php echo esc_url( home_url( '/watchlist/' ) ); ?>" class="doodh-action-icon-btn" id="doodh-watchlist-nav-btn" title="<?php esc_attr_e( 'My Saved Watchlist', 'vmtheme' ); ?>">
					<i class="fas fa-bookmark"></i>
					<span class="doodh-btn-text"><?php esc_html_e( 'Watchlist', 'vmtheme' ); ?></span>
					<span class="doodh-watchlist-count-badge" id="doodh-nav-fav-count" style="display:none;">0</span>
				</a>

				<!-- User Account / Auth Trigger -->
				<?php if ( is_user_logged_in() ) : 
					$current_user = wp_get_current_user();
					$user_avatar  = function_exists( 'doodhtheme_get_user_avatar' ) ? doodhtheme_get_user_avatar( $current_user->ID ) : get_avatar_url( $current_user->ID );
					$user_name    = $current_user->display_name ?: $current_user->user_login;
					?>
					<div class="doodh-user-account-menu" id="doodh-user-menu-wrap">
						<button type="button" class="doodh-user-toggle-btn" id="doodh-user-dropdown-btn" aria-haspopup="true" aria-expanded="false">
							<img src="<?php echo esc_url( $user_avatar ); ?>" class="doodh-header-avatar" alt="<?php echo esc_attr( $user_name ); ?>" width="32" height="32">
							<span class="doodh-user-name-label doodh-hide-mobile"><?php echo esc_html( $user_name ); ?></span>
							<i class="fas fa-chevron-down doodh-user-arrow doodh-hide-mobile"></i>
						</button>

						<div class="doodh-user-dropdown" id="doodh-user-dropdown-menu">
							<div class="doodh-user-dropdown-head">
								<img src="<?php echo esc_url( $user_avatar ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" width="44" height="44" class="doodh-dropdown-avatar">
								<div class="doodh-dropdown-user-info">
									<strong class="doodh-dropdown-name"><?php echo esc_html( $user_name ); ?></strong>
									<span class="doodh-dropdown-email"><?php echo esc_html( $current_user->user_email ); ?></span>
								</div>
							</div>
							<div class="doodh-user-dropdown-divider"></div>
							<ul class="doodh-user-dropdown-links">
								<li>
									<a href="<?php echo esc_url( home_url( '/watchlist/' ) ); ?>">
										<i class="fas fa-bookmark"></i> <?php esc_html_e( 'My Watchlist', 'vmtheme' ); ?>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( home_url( '/request/' ) ); ?>">
										<i class="fas fa-paper-plane"></i> <?php esc_html_e( 'My Title Requests', 'vmtheme' ); ?>
									</a>
								</li>
								<?php if ( current_user_can( 'edit_posts' ) ) : ?>
									<li>
										<a href="<?php echo esc_url( admin_url() ); ?>" target="_blank">
											<i class="fas fa-tachometer-alt"></i> <?php esc_html_e( 'Admin Dashboard', 'vmtheme' ); ?>
										</a>
									</li>
								<?php endif; ?>
								<li class="doodh-logout-item">
									<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
										<i class="fas fa-sign-out-alt"></i> <?php esc_html_e( 'Sign Out', 'vmtheme' ); ?>
									</a>
								</li>
							</ul>
						</div>
					</div>
				<?php else : ?>
					<button type="button" class="doodh-btn-signin doodh-auth-trigger" id="doodh-header-signin-btn">
						<i class="fas fa-user-circle"></i>
						<span><?php esc_html_e( 'Sign In', 'vmtheme' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- Mobile Search Dropdown Bar -->
		<div class="doodh-mobile-search-bar" id="doodh-mobile-search-bar" style="display:none;">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-mobile-search-form">
				<input type="search" 
					   id="doodh-mobile-search-input" 
					   class="doodh-search-input" 
					   placeholder="<?php esc_attr_e( 'Search movies, shows...', 'vmtheme' ); ?>" 
					   value="<?php echo get_search_query(); ?>" 
					   name="s" 
					   autocomplete="off">
				<button type="button" class="doodh-search-clear-btn doodh-mobile-clear-btn" id="doodh-mobile-search-clear" title="<?php esc_attr_e( 'Clear search', 'vmtheme' ); ?>" style="display:none;">
					<i class="fas fa-times"></i>
				</button>
				<button type="submit" class="doodh-search-submit" aria-label="<?php esc_attr_e( 'Search', 'vmtheme' ); ?>">
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
		<!-- Drawer User Bar -->
		<div class="doodh-drawer-user-box" style="padding:15px 20px; border-bottom:1px solid rgba(255,255,255,0.08); margin-bottom:10px;">
			<?php if ( is_user_logged_in() ) : 
				$current_user = wp_get_current_user();
				$user_avatar  = function_exists( 'doodhtheme_get_user_avatar' ) ? doodhtheme_get_user_avatar( $current_user->ID ) : get_avatar_url( $current_user->ID );
				$user_name    = $current_user->display_name ?: $current_user->user_login;
				?>
				<div style="display:flex; align-items:center; gap:12px;">
					<img src="<?php echo esc_url( $user_avatar ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" width="40" height="40" style="border-radius:50%; border:2px solid var(--dt-primary);">
					<div style="min-width:0; flex:1;">
						<strong style="display:block; color:#fff; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo esc_html( $user_name ); ?></strong>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" style="color:#ef4444; font-size:12px; text-decoration:none;"><i class="fas fa-sign-out-alt"></i> <?php esc_html_e( 'Sign Out', 'vmtheme' ); ?></a>
					</div>
				</div>
			<?php else : ?>
				<button type="button" class="doodh-btn-primary doodh-btn-full doodh-auth-trigger" style="justify-content:center; padding:10px;">
					<i class="fas fa-user-circle"></i> <?php esc_html_e( 'Sign In / Register', 'vmtheme' ); ?>
				</button>
			<?php endif; ?>
		</div>

		<!-- Drawer Quick Search Form -->
		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="doodh-drawer-search-form">
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search title or actor...', 'vmtheme' ); ?>" class="doodh-drawer-search-input" autocomplete="off" required>
			<button type="submit" class="doodh-drawer-search-btn"><i class="fas fa-search"></i></button>
		</form>

		<ul class="doodh-drawer-nav">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fas fa-home"></i> <?php esc_html_e( 'Home', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'movies' ) ); ?>"><i class="fas fa-film"></i> <?php esc_html_e( 'Movies', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'tvshows' ) ); ?>"><i class="fas fa-tv"></i> <?php esc_html_e( 'TV Shows', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/top-imdb/' ) ); ?>"><i class="fas fa-trophy"></i> <?php esc_html_e( 'Top 100 IMDb', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/genres/' ) ); ?>"><i class="fas fa-tags"></i> <?php esc_html_e( 'Genres Directory', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/years/' ) ); ?>"><i class="fas fa-calendar-alt"></i> <?php esc_html_e( 'Release Years', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><i class="fas fa-newspaper"></i> <?php esc_html_e( 'Cinema Blog & News', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/request/' ) ); ?>"><i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Request Title', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/watchlist/' ) ); ?>"><i class="fas fa-bookmark"></i> <?php esc_html_e( 'My Watchlist', 'vmtheme' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/dmca/' ) ); ?>"><i class="fas fa-shield-alt"></i> <?php esc_html_e( 'DMCA Policy', 'vmtheme' ); ?></a></li>
		</ul>
	</div>
</div>

<!-- Header Top Banner Ad Slot -->
<?php doodhtheme_display_ad( 'header' ); ?>