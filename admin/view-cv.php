<?php
/**
 * Streams an application's CV to the logged-in administrator.
 *
 * The submissions table stores a relative path. Linking straight to that path
 * from the dashboard relied on the web server guessing the right content type
 * and on the browser treating a bare file as an inline document; in practice a
 * click could download the file, open a blank tab, or hit a 404 when the path
 * had been stored with a different separator. Serving the file through this
 * endpoint fixes all three: it authenticates the request, resolves and checks
 * the path on disk, sends the correct Content-Type and asks for inline display.
 */

require_once __DIR__ . '/config.php';
requireLogin();

$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
if ( $id <= 0 ) {
    http_response_code( 400 );
    exit( 'Invalid request.' );
}

global $pdo;

$statement = $pdo->prepare(
    'SELECT full_name, attachment FROM contact_submissions WHERE id = ? LIMIT 1'
);
$statement->execute( [ $id ] );
$submission = $statement->fetch( PDO::FETCH_ASSOC );

if ( !$submission ) {
    http_response_code( 404 );
    exit( 'Application not found.' );
}

$attachment = trim( (string) ( $submission['attachment'] ?? '' ) );
if ( $attachment === '' ) {
    http_response_code( 404 );
    exit( 'No CV is attached to this application.' );
}

$siteRoot      = realpath( dirname( __DIR__ ) );
$requestedPath = $siteRoot !== false
    ? realpath( $siteRoot . DIRECTORY_SEPARATOR . ltrim( str_replace( '\\', '/', $attachment ), '/' ) )
    : false;

// Only files inside an upload folder may be served. This blocks traversal
// attempts and accidental references to configuration files stored under the
// site root. The legacy assets/files/uploads folder is kept for applications
// that were received before uploads/ became the storage location.
$allowedRoots = [];
if ( $siteRoot !== false ) {
    foreach ( [ 'uploads', 'assets/files/uploads' ] as $relativeRoot ) {
        $resolvedRoot = realpath( $siteRoot . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relativeRoot ) );
        if ( $resolvedRoot !== false ) {
            $allowedRoots[] = $resolvedRoot;
        }
    }
}

$insideUploads = false;
if ( $requestedPath !== false ) {
    foreach ( $allowedRoots as $allowedRoot ) {
        if ( strpos( $requestedPath, $allowedRoot . DIRECTORY_SEPARATOR ) === 0 ) {
            $insideUploads = true;
            break;
        }
    }
}

if ( $requestedPath === false || !is_file( $requestedPath ) || !$insideUploads ) {
    http_response_code( 404 );
    exit( 'The CV file could not be found on the server.' );
}

$extension = strtolower( pathinfo( $requestedPath, PATHINFO_EXTENSION ) );

$contentTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'txt'  => 'text/plain; charset=UTF-8',
    'csv'  => 'text/csv; charset=UTF-8',
    'zip'  => 'application/zip',
];

$contentType = $contentTypes[ $extension ] ?? 'application/octet-stream';

// PDFs, images and plain text can be shown in the tab. Word documents and ZIPs
// cannot, so those are offered as a download with a readable file name.
$viewableInline = in_array( $extension, [ 'pdf', 'jpg', 'jpeg', 'png', 'txt', 'csv' ], true );

$personSlug = preg_replace( '/[^A-Za-z0-9]+/', '_', (string) $submission['full_name'] );
$personSlug = trim( $personSlug, '_' );
if ( $personSlug === '' ) {
    $personSlug = 'applicant';
}
$downloadName = $personSlug . '_CV.' . $extension;

// Never let PHP buffer this in memory; CVs can be several megabytes.
while ( ob_get_level() > 0 ) {
    ob_end_clean();
}

header( 'Content-Type: ' . $contentType );
header( 'Content-Length: ' . filesize( $requestedPath ) );
header(
    'Content-Disposition: ' . ( $viewableInline ? 'inline' : 'attachment' )
    . '; filename="' . $downloadName . '"'
);
header( 'X-Content-Type-Options: nosniff' );
header( 'Cache-Control: private, max-age=0, must-revalidate' );

readfile( $requestedPath );
exit;
