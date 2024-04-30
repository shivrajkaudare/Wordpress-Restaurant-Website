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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

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
define( 'AUTH_KEY',         '`t(9);pDAnDr/#g9L2)xc2;wv:@?>`,iDKq4O88aV>8H@QdDnXi)!&_TY}7lZ@R.' );
define( 'SECURE_AUTH_KEY',  '=3Q>a^L4m%$CD:]?WP(DHEhBjr|#c[T`]* |(R|_!lo$e{hStoB(#B=9irZO>lF9' );
define( 'LOGGED_IN_KEY',    'u^O@2$cZH`ueWsScrI)=gQJbJAu)r(QB{6HAfO KEu!g;z8]:&Gs+IN3.sMb%,S*' );
define( 'NONCE_KEY',        'pg,#H2e b3`XxkfQoYQ*!^*Mi5iRme`9l:EDn$~Nbc4n~T53*|NdDpQh/&FZ$P`;' );
define( 'AUTH_SALT',        'OHFs@FA)0GL~{$$ny0{4&tPmM=urAF/.0<vJMR@)Br}@yI.z1H%lRY8*&1X%f16G' );
define( 'SECURE_AUTH_SALT', 'cf67<xu}tmM,Ka2@hz^=Xd Fc(6mnw;o}E1aiGE&{z<H7_6NV7/|(E|oMlIvFKk>' );
define( 'LOGGED_IN_SALT',   'C0vI$S>tU8k.zU3v-p;#]L,#wcMP*U.0I~#rJg&(Yz(Rt1`1~6e<EJtEb;T1u~B%' );
define( 'NONCE_SALT',       'UmHXB<|:I;@drM&>5L-5{o7H`*2<y*Y<>q/ e/xlA{^doa)NX2gB_o2T]SfQ8utd' );

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
