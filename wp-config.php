<?php
/**
 * The base configuration for WordPress
 *
 * @package WordPress
 */

define( 'DB_NAME', 'doodhtheme' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '127.0.0.1:3306' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'bJ3$!8kX7%^zP9#qL2*vR5@mN1&wT4(yU0)eI6-oA8+sD3~fG5=hJ7?kL9!zX2#v' );
define( 'SECURE_AUTH_KEY',  'cK4%@9lY8&_aQ0$rM3(wS6!nO2*xU5)zV1-fJ7+pB9=tE4?gH6!iK8#lL0$aY3%w' );
define( 'LOGGED_IN_KEY',    'dL5&!0mZ9*_bR1%sN4)xT7@oP3(yV6-aW2+gK8=qC0?uF5!hI7$jL9%mK1&bZ4*x' );
define( 'NONCE_KEY',        'eM6(!1n_0+_cS2&tO5*yU8#pQ4)zW7=bX3?hL9!rD1$vG6%iJ8&kM0*nL2(c_5+y' );
define( 'AUTH_SALT',        'fN7)!2o-1=_dT3*uP6(zV9$qR5*aX8?cY4!iM0%sE2&wH7*jK9(oL1)d-6=z@6,a' );
define( 'SECURE_AUTH_SALT', 'gO8*#3p.2?+eU4(vQ7)aW0%rS6(bY9!dZ5$jN1&tF3*xI8)kL0*pM2+e.7?a#7-b' );
define( 'LOGGED_IN_SALT',   'hP9+$4q/3!*fV5)wR8*bX1&sT7)cZ0?e_6%kO2(uG4+yJ9*lM1+qN3,f/8!b$8.c' );
define( 'NONCE_SALT',       'iQ0-%5r:4?*gW6*xS9+cY2(tU8*d_1!f-7&lP3)vH5,zK0(mN2,rO4-g:9?c%9/d' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

define( 'WP_MEMORY_LIMIT', '512M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';