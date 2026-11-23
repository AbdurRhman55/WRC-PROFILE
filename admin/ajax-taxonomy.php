<?php
/**
 * WRC Admin - Project taxonomy quick-add endpoint.
 *
 * Used by the "+" buttons on the project form, so a missing category or
 * sub-category can be created without leaving the page. Every new category is
 * created inside the chosen organisation and every new sub-category inside the
 * chosen category - the hierarchy can never be bypassed.
 */
require_once __DIR__ . '/config.php';
requireLogin();

wrcEnsureProjectTaxonomy();

global $pdo;

header( 'Content-Type: application/json; charset=utf-8' );
header( 'Cache-Control: no-store' );

if ( !isPost() ) {
    http_response_code( 405 );
    echo json_encode( [ 'success' => false, 'message' => 'Invalid request method.' ] );
    exit;
}

if ( !isset( $_POST[ 'csrf_token' ] ) || !verifyCSRF( (string) $_POST[ 'csrf_token' ] ) ) {
    http_response_code( 403 );
    echo json_encode( [ 'success' => false, 'message' => 'Your session expired. Reload the page and try again.' ] );
    exit;
}

$action        = getPost( 'action' );
$sectorOptions = wrcProjectSectorOptions();

try {
    switch ( $action ) {
        case 'add_organization':
            $name = getPost( 'name' );
            if ( $name === '' ) {
                echo json_encode( [ 'success' => false, 'message' => 'Organisation name is required.' ] );
                exit;
            }

            $exists = $pdo->prepare( 'SELECT id, name FROM project_organizations WHERE LOWER(name) = LOWER(?) LIMIT 1' );
            $exists->execute( [ $name ] );
            $existing = $exists->fetch( PDO::FETCH_ASSOC );

            if ( $existing ) {
                echo json_encode( [ 'success' => false, 'message' => 'That organisation already exists.', 'organization' => [
                    'id'   => (int) $existing[ 'id' ],
                    'name' => (string) $existing[ 'name' ],
                ] ] );
                exit;
            }

            $pdo->prepare( 'INSERT INTO project_organizations (name, slug, is_active, sort_order) VALUES (?, ?, 1, 0)' )
                ->execute( [ $name, wrcUniqueTaxonomySlug( 'project_organizations', 0, '', $name ) ] );

            echo json_encode( [ 'success' => true, 'organization' => [
                'id'   => (int) $pdo->lastInsertId(),
                'name' => $name,
            ], 'categories' => [] ] );
            exit;

        case 'add_category':
            $organizationId = (int) getPost( 'organization_id' );
            $organizationName = getPost( 'organization_name' );
            $name           = getPost( 'name' );
            $sector         = getPost( 'sector' );
            $sector         = in_array( $sector, $sectorOptions, true ) ? $sector : ( $sectorOptions[ 0 ] ?? 'General' );

            // An inline "new organisation" name wins over the dropdown. This is
            // how a brand-new donor gets its first category in one step.
            $organization = null;
            if ( $organizationName !== '' ) {
                $find = $pdo->prepare( 'SELECT * FROM project_organizations WHERE LOWER(name) = LOWER(?) LIMIT 1' );
                $find->execute( [ $organizationName ] );
                $organization = $find->fetch( PDO::FETCH_ASSOC );

                if ( !$organization ) {
                    $pdo->prepare( 'INSERT INTO project_organizations (name, slug, is_active, sort_order) VALUES (?, ?, 1, 0)' )
                        ->execute( [ $organizationName, wrcUniqueTaxonomySlug( 'project_organizations', 0, '', $organizationName ) ] );
                    $organizationId = (int) $pdo->lastInsertId();
                } else {
                    $organizationId = (int) $organization[ 'id' ];
                }
            }

            if ( $organizationId <= 0 ) {
                echo json_encode( [ 'success' => false, 'message' => 'Choose an organisation first.' ] );
                exit;
            }

            $findOrg = $pdo->prepare( 'SELECT id, name FROM project_organizations WHERE id = ? LIMIT 1' );
            $findOrg->execute( [ $organizationId ] );
            $organization = $findOrg->fetch( PDO::FETCH_ASSOC );

            if ( !$organization ) {
                echo json_encode( [ 'success' => false, 'message' => 'That organisation no longer exists.' ] );
                exit;
            }

            if ( $name === '' ) {
                echo json_encode( [ 'success' => false, 'message' => 'Category name is required.' ] );
                exit;
            }

            $duplicate = $pdo->prepare(
                'SELECT id FROM project_categories WHERE organization_id = ? AND LOWER(name) = LOWER(?) LIMIT 1'
            );
            $duplicate->execute( [ $organizationId, $name ] );
            if ( (int) $duplicate->fetchColumn() > 0 ) {
                echo json_encode( [ 'success' => false, 'message' => 'That organisation already has a category called "' . $name . '".' ] );
                exit;
            }

            $pdo->prepare(
                'INSERT INTO project_categories (organization_id, name, slug, sector, is_active, sort_order)
                 VALUES (?, ?, ?, ?, 1, 0)'
            )->execute( [
                $organizationId,
                $name,
                wrcUniqueTaxonomySlug( 'project_categories', $organizationId, 'organization_id', $name ),
                $sector,
            ] );

            echo json_encode( [
                'success'      => true,
                'organization' => [
                    'id'   => (int) $organization[ 'id' ],
                    'name' => (string) $organization[ 'name' ],
                ],
                'category'     => [
                    'id'     => (int) $pdo->lastInsertId(),
                    'name'   => $name,
                    'sector' => $sector,
                ],
            ] );
            exit;

        case 'add_subcategory':
            $categoryId = (int) getPost( 'category_id' );
            $name       = getPost( 'name' );

            if ( $categoryId <= 0 ) {
                echo json_encode( [ 'success' => false, 'message' => 'Choose a category first.' ] );
                exit;
            }
            if ( $name === '' ) {
                echo json_encode( [ 'success' => false, 'message' => 'Sub-category name is required.' ] );
                exit;
            }

            $category = wrcProjectCategoryById( $categoryId );
            if ( !$category ) {
                echo json_encode( [ 'success' => false, 'message' => 'That category no longer exists.' ] );
                exit;
            }

            $duplicate = $pdo->prepare(
                'SELECT id FROM project_subcategories WHERE category_id = ? AND LOWER(name) = LOWER(?) LIMIT 1'
            );
            $duplicate->execute( [ $categoryId, $name ] );
            if ( (int) $duplicate->fetchColumn() > 0 ) {
                echo json_encode( [ 'success' => false, 'message' => 'That category already has a sub-category called "' . $name . '".' ] );
                exit;
            }

            $pdo->prepare(
                'INSERT INTO project_subcategories (category_id, name, slug, is_active, sort_order)
                 VALUES (?, ?, ?, 1, 0)'
            )->execute( [
                $categoryId,
                $name,
                wrcUniqueTaxonomySlug( 'project_subcategories', $categoryId, 'category_id', $name ),
            ] );

            echo json_encode( [
                'success'        => true,
                'subcategory'    => [
                    'id'   => (int) $pdo->lastInsertId(),
                    'name' => $name,
                ],
            ] );
            exit;

        default:
            http_response_code( 400 );
            echo json_encode( [ 'success' => false, 'message' => 'Unknown action.' ] );
            exit;
    }
} catch ( Throwable $error ) {
    http_response_code( 500 );
    echo json_encode( [ 'success' => false, 'message' => 'Could not save: ' . $error->getMessage() ] );
    exit;
}
