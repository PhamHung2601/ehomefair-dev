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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
 
 //** Direct update plugin without FTP **//
 define('FS_METHOD','direct');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ehomefair-dev' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG', false );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ytcjqvghnf77iwpnnlowhye5eh9v9g7zstn2hcb3xvfye3runrqi6kg2sjjcw5py' );
define( 'SECURE_AUTH_KEY',  'qixxet8uj60eptv9qrlgjvxijnc6qf8psswdgwnyjya3b57fouu1fmxqgwzkrmku' );
define( 'LOGGED_IN_KEY',    'tbankangarwd8p96ypl3ppgy90tp9y1bsbjrqmggjsu0vw7zfcjthnq0r30irb7t' );
define( 'NONCE_KEY',        '981oxy8djf8usvglkelxsewsems5zfvreer0kbxgksy88bvffxgbkus1bqznx1mf' );
define( 'AUTH_SALT',        '6lgatez7j5lugdekfrt6udpmf9rjgvdarvr5iibrnkrhwyyzhzrl8halgkmshazt' );
define( 'SECURE_AUTH_SALT', 'sramnrwosgfzoewh1e8lqexgkbfa9flkcfp9xtbl4mzdwc8gt4gy8thbhlreiolg' );
define( 'LOGGED_IN_SALT',   'g6ywimbru1ybivj0byzzpyurqobmpykghauzexbgnfsp80yv4h9y3z9cw7fkr2h5' );
define( 'NONCE_SALT',       'csabp3wuetma1httfmkyvdaliqtsjbxhnmrigbagaok7ahcuzlqkjqu3umeczspm' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpnw_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
