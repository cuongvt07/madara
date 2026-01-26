<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'madara' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '5xCu5VtoerhWlQHNYNbhqIHOQZK4yRilglXRQKD0tVenOjme56bsphBn4NAJFah9' );
define( 'SECURE_AUTH_KEY',  'QcsqqZB84dyy58GBTDZknu2jy7nAYa6MfFrOAA0nVF5ut3zhAmvRb1jzdmz9YboJ' );
define( 'LOGGED_IN_KEY',    '0qbjOk6G69Vff7043nuO9avYKajrAqTk3IR3pkLMl462UiEx4V70ghGrGqhFZmGU' );
define( 'NONCE_KEY',        'wiAl1YgFem78Yw7L7W73cAalhwNs772kEIHh3XMrL00Ia6t2na1obiq3EmdNmZLl' );
define( 'AUTH_SALT',        'LQC6A34O0YGCB67XlRuMEvti0ja0OXtqCNIs5P1BaP08KON3Y2jIrQqcDzGLZJih' );
define( 'SECURE_AUTH_SALT', '8I28MFeNhcRukv3Fpq71IW9FGFEalCBqeOyIGs2fAVdN1BCwigcXmQbNBvCSnlnI' );
define( 'LOGGED_IN_SALT',   'sL66yFyznMDMhIKK1z0RCucc4SmBp65HrkbrT9PM1FLURCzTHFsImue2j2Wd5f4w' );
define( 'NONCE_SALT',       '1Cxw9cMOPX0eCs6bMz6jx0oHA3x6EYc4xgeNTpZ0rUX2CZE7E1fNKG8JKyB6zdsI' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
