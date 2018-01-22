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
define('DB_NAME', 'teste');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         '.UbMhgh0t:jAP:#C`Qo|[q3){f*4!IAa*=9*e#NN,^e:%zX;w(@KnstW`*;GjZXH');
define('SECURE_AUTH_KEY',  'ex$]2nygKz.==Sr: M=:VH+)0(u?Eqdh:5`3)5pRR?T>u+<(S>3ApP|<jv>v9Alu');
define('LOGGED_IN_KEY',    '7J;lBH*_$eJJuLPX%Aau+0JKv!b6HH0#Myj|dQL::Sd,O}^9PjdI#V3V(NS>zK^M');
define('NONCE_KEY',        ':94zj.d+u~H6],RU>m*=t< +4BZ>AHp.4%4YEwuR|en!rh;g]R.!m;Tj`Kbi9v^n');
define('AUTH_SALT',        '/G<CTddc#zI4y6_A`*B8p]murHS7@6..BKZ%eXj>qydT)6+|~gX@t4&)d]kUZ7Q4');
define('SECURE_AUTH_SALT', 'n;@Dnkzbz=Lh$XWAj:l=$#HXU+3j^:NQ{Zx(L42F>uK%j+rxBMZpRU$tKYhOkUjv');
define('LOGGED_IN_SALT',   ':u/CthXd*RzZ]-Ps[x:p2NFeSt/BB-RwFr}L].uv&uzQw>TLW$ J%rc7h%ZVLdwd');
define('NONCE_SALT',       ']v)(&zn;P|U5&+MJP=&HaFsHfmG|$nn+lCLE>1YM!S0kVG0CDlf!h*>K$k]W >6P');

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
