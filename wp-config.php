<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'blog');

/** MySQL database username */
define('DB_USER', 'peta2');

/** MySQL database password */
define('DB_PASSWORD', 'K94679nM');

/** MySQL hostname */
define('DB_HOST', '192.168.0.202');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'm<70Cchp)br<6QDk`i5=!3@;AheWa~9xN6g2$NWiKsdT^XbN&,0*ma&Hwhn0cD:A');
define('SECURE_AUTH_KEY',  'R.D34** F?)l4:Q~cFRIiw&`T79sL(=rrhLY%/^sr-Hu55e5j*hdsU>K&.-UIOx<');
define('LOGGED_IN_KEY',    '8R|D((7K5.dS)NQ2&H 99N=W]Y=wq5K.KJJiv`2gX~pqZ7zceG0r!NTD_^;G(nb0');
define('NONCE_KEY',        '||jCnQgoF6HV@;(2b!Zgc56^?RzdLw7i:)82cST41sDp=b:w)~jinX2r12RptS)`');
define('AUTH_SALT',        '%3>](+KEU+]YIuA8QtK` 8#V|S3${C{++2}^pL<8$UMhQ@FkGi5>tNdIjJbqD2OQ');
define('SECURE_AUTH_SALT', 'Ecc(%j=li,6^O@EfOil93v#Z|4VBncg0CAh`/Tw 55)wgXuTe$}B&h# XM&M7f8b');
define('LOGGED_IN_SALT',   'hWm/R)6BKt[Y4ot=kU,~ 7rRm] %c,0&:V%MyNQoX-$mJ!_BZkFq lJM|B~~>q )');
define('NONCE_SALT',       '6Qza9_Rmdsaht-_0CwwCC]M@d9MMx$9J=I%#/W:S+b,;P. uSa3aPS+-U2-a,kkY');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

