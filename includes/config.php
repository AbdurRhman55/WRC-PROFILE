<?php
/**
* WRC Website - Database Configuration
* Centralized PDO connection and site constants
*/

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// DATABASE CONFIGURATION
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

define( 'DB_HOST', 'localhost' );
define( 'DB_NAME', 'u817261572_db_OFOnJ2VM' );
define( 'DB_USER', 'u817261572_usr_OFOnJ2VM' );
define( 'DB_PASS', 'w^0IIH5q&' );
define( 'DB_CHARSET', 'utf8mb4' );

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// SITE PATHS
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

define( 'SITE_ROOT', dirname( __DIR__ ) );
define( 'INCLUDES_PATH', SITE_ROOT . '/includes' );
define( 'ASSETS_PATH', SITE_ROOT . '/assets' );
define( 'UPLOADS_PATH', SITE_ROOT . '/uploads' );

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// PDO CONNECTION
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

try {
    if ( !extension_loaded( 'pdo_mysql' ) ) {
        throw new RuntimeException( 'The PDO MySQL extension is not enabled.' );
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO( $dsn, DB_USER, DB_PASS, $options );
} catch ( Throwable $e ) {
    error_log( 'Database connection failed: ' . $e->getMessage() );
    http_response_code( 500 );
    die( 'Database connection failed. Please check configuration.' );
}

// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===
// SESSION
// ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===

if ( session_status() === PHP_SESSION_NONE ) {
    session_start();
}