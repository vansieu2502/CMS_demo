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
define( 'DB_NAME', 'wordress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         ':fZ:@87OxXa6Dr4@A$J<!]1RSo$0EG^gL6lfTb,=#)75<?tT> 8sLwGs#<Xt8_T]' );
define( 'SECURE_AUTH_KEY',  ',S-()n8~UiO6KAdY}Krf++}_K]?`u)*uA8Ay*gG4WNC!AN(.R(>5lA-:_.soeL-M' );
define( 'LOGGED_IN_KEY',    'tUH?>z<pW9dY/M;J{.P1$z_):K Fyy:Z5?}o;Zk*dNZzK {% k[(63]2eK.Fwf1}' );
define( 'NONCE_KEY',        'mPsCwOO||eTgcd?[ql1Qc|]CX(]bY9}YbV,?Xm[v]vq[SSicYo5sbNd2lf2{:FtO' );
define( 'AUTH_SALT',        '6FP+tIk>pFN^}2G|PHAeHrFc!BbE [zSVpO*qerbmyzNDhNSvMi[OD8IsxL16+(u' );
define( 'SECURE_AUTH_SALT', 'v}:pqCm?eqI8am(7`bS&#qIX-td]$;BsLY_@FCn@G<{4s1wN|VL`37{DzoSJ!*-(' );
define( 'LOGGED_IN_SALT',   'eK8/-ZWJ-tp~/AzC:TR;]G)cRiQ7A=.Dn{@,P)hpp-vPt;3Xjv-je{G_Ea<61B/y' );
define( 'NONCE_SALT',       '[Iu5xX=BIBqDec^i{[`;L+]l?]h>O1/+M{9p (e<.r1Y.#4@d8wvY/F+Xj3`n}r1' );

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
