<?php
define( 'WP_CACHE', true );

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
define( 'DB_NAME', 'lastprompt' );

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
define( 'AUTH_KEY',         'Bn$X56@5#Mt$-g+}07NT962LmbfxHCU._4N+ts;q;UzdM0&`kb_H<.c=eE|ZB0A}' );
define( 'SECURE_AUTH_KEY',  'YAe8ut;OS+~<V(-d$$D&t[yN>NI8UZH1GH]75+}2/(=I :?KgjX`JlE3R60kDi*H' );
define( 'LOGGED_IN_KEY',    '=Fs4=2@E.%@Hc7PN9JA?LhIY>]pn;Va1?CidOhPn*Au;W(g/[`I-{rc=;$aq0[U!' );
define( 'NONCE_KEY',        'l,l/9WpQ2Q^l!%18hay>+KQqLFFf2p3}x!An{Tj&~xYI4NLn-U:Q@q</,xYBoaIy' );
define( 'AUTH_SALT',        'o[%j$GnsAD0g^;qG>bp;NroD?FL>i3![k@:n($_4]=nYh[03OBM;VB]>_nEf/J3b' );
define( 'SECURE_AUTH_SALT', 'R9*4gFo_4iDsL2|ZikheS.%f)V@}FB~I+CL%GH`Bx9hVDt)sBEb@k;G-t/aiR]:(' );
define( 'LOGGED_IN_SALT',   ',A&?[.],OufS!rD,^:;*W[z+TP1A) o/,nh+|#|H;<tC_RT<@.O/=b3$L+6j4]-z' );
define( 'NONCE_SALT',       '5TLMv9+F|LSO>,Yz_FJ[Vt%}qiNI9`0__d0J0al22.fo:*&}$.gLX){k0 E6B1#x' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
