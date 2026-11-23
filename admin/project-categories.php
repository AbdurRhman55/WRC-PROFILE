<?php
/**
 * WRC Admin - Project Categories
 *
 * Organisation -> Category -> Sub-category manager.
 *
 * The right-hand lists are driven by the selection on the left, so an
 * administrator always edits a category inside the organisation it belongs to
 * and a sub-category inside its category. Nothing is shared between
 * organisations: "Education" under KP-TEVTA and "Education" under ZFA are two
 * separate records.
 */
require_once __DIR__ . '/config.php';
requireLogin();

wrcEnsureProjectTaxonomy();

$pageTitle = 'Project Categories';

global $pdo;

$redirectUrl = static function ( int $orgId, int $catId = 0, array $extra = [] ): string {
    $params = [];
    if ( $orgId > 0 ) {
        $params[ 'org' ] = $orgId;
    }
    if ( $catId > 0 ) {
        $params[ 'cat' ] = $catId;
    }
    foreach ( $extra as $key => $value ) {
        $params[ $key ] = $value;
    }

    return 'project-categories.php' . ( $params ? '?' . http_build_query( $params ) : '' );
};

$sectorOptions = wrcProjectSectorOptions();

if ( isPost() ) {
    if ( !isset( $_POST[ 'csrf_token' ] ) || !verifyCSRF( (string) $_POST[ 'csrf_token' ] ) ) {
        setFlash( 'error', 'Invalid security token. Please try again.' );
        redirect( 'project-categories.php' );
    }

    $action      = getPost( 'action' );
    $returnOrg   = (int) getPost( 'return_org' );
    $returnCat   = (int) getPost( 'return_cat' );
    $back        = $redirectUrl( $returnOrg, $returnCat );

    /**
     * Counts how many rows of $table point at $id through $column.
     */
    $usageCount = static function ( string $table, string $column, int $id ) use ( $pdo ): int {
        if ( $id <= 0 ) {
            return 0;
        }
        $stmt = $pdo->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?" );
        $stmt->execute( [ $id ] );

        return (int) $stmt->fetchColumn();
    };

    try {
        switch ( $action ) {
            case 'add_organization':
                $name = getPost( 'name' );
                if ( $name === '' ) {
                    setFlash( 'error', 'Organisation name is required.' );
                    redirect( $back );
                }

                $exists = $pdo->prepare( 'SELECT id FROM project_organizations WHERE LOWER(name) = LOWER(?) LIMIT 1' );
                $exists->execute( [ $name ] );
                if ( (int) $exists->fetchColumn() > 0 ) {
                    setFlash( 'error', 'That organisation already exists.' );
                    redirect( $back );
                }

                $insert = $pdo->prepare(
                    'INSERT INTO project_organizations (name, slug, is_active, sort_order) VALUES (?, ?, 1, ?)'
                );
                $insert->execute( [
                    $name,
                    wrcUniqueTaxonomySlug( 'project_organizations', 0, '', $name ),
                    (int) getPost( 'sort_order' ),
                ] );

                setFlash( 'success', 'Organisation "' . $name . '" added. Now add its categories.' );
                redirect( $redirectUrl( (int) $pdo->lastInsertId() ) );

                break;

            case 'update_organization':
                $id   = (int) getPost( 'id' );
                $name = getPost( 'name' );

                if ( $id <= 0 || $name === '' ) {
                    setFlash( 'error', 'Organisation name is required.' );
                    redirect( $back );
                }

                $pdo->prepare( 'UPDATE project_organizations SET name = ?, sort_order = ?, is_active = ? WHERE id = ?' )
                    ->execute( [ $name, (int) getPost( 'sort_order' ), isset( $_POST[ 'is_active' ] ) ? 1 : 0, $id ] );

                setFlash( 'success', 'Organisation updated.' );
                redirect( $redirectUrl( $id, $returnCat ) );

                break;

            case 'delete_organization':
                $id = (int) getPost( 'id' );

                $categoryUse = $usageCount( 'project_categories', 'organization_id', $id );
                $projectUse  = $usageCount( 'projects', 'organization_id', $id );

                if ( $categoryUse > 0 || $projectUse > 0 ) {
                    setFlash(
                        'error',
                        'This organisation still holds ' . $categoryUse . ' categor' . ( $categoryUse === 1 ? 'y' : 'ies' )
                        . ' and ' . $projectUse . ' project(s). Move or remove them first.'
                    );
                    redirect( $back );
                }

                $pdo->prepare( 'DELETE FROM project_organizations WHERE id = ?' )->execute( [ $id ] );
                setFlash( 'success', 'Organisation deleted.' );
                redirect( 'project-categories.php' );

                break;

            case 'add_category':
                $orgId     = (int) getPost( 'organization_id' );
                $name      = getPost( 'name' );
                $sector    = getPost( 'sector' );
                $sector    = in_array( $sector, $sectorOptions, true ) ? $sector : ( $sectorOptions[ 0 ] ?? 'General' );

                if ( $orgId <= 0 || $name === '' ) {
                    setFlash( 'error', 'Choose an organisation and enter a category name.' );
                    redirect( $back );
                }

                $exists = $pdo->prepare(
                    'SELECT id FROM project_categories WHERE organization_id = ? AND LOWER(name) = LOWER(?) LIMIT 1'
                );
                $exists->execute( [ $orgId, $name ] );
                if ( (int) $exists->fetchColumn() > 0 ) {
                    setFlash( 'error', 'That organisation already has a category called "' . $name . '".' );
                    redirect( $redirectUrl( $orgId ) );
                }

                $insert = $pdo->prepare(
                    'INSERT INTO project_categories (organization_id, name, slug, sector, is_active, sort_order)
                     VALUES (?, ?, ?, ?, 1, ?)'
                );
                $insert->execute( [
                    $orgId,
                    $name,
                    wrcUniqueTaxonomySlug( 'project_categories', $orgId, 'organization_id', $name ),
                    $sector,
                    (int) getPost( 'sort_order' ),
                ] );

                setFlash( 'success', 'Category "' . $name . '" added. You can add sub-categories to it now.' );
                redirect( $redirectUrl( $orgId, (int) $pdo->lastInsertId() ) );

                break;

            case 'update_category':
                $id     = (int) getPost( 'id' );
                $name   = getPost( 'name' );
                $sector = getPost( 'sector' );
                $sector = in_array( $sector, $sectorOptions, true ) ? $sector : ( $sectorOptions[ 0 ] ?? 'General' );

                if ( $id <= 0 || $name === '' ) {
                    setFlash( 'error', 'Category name is required.' );
                    redirect( $back );
                }

                $pdo->prepare(
                    'UPDATE project_categories SET name = ?, sector = ?, sort_order = ?, is_active = ? WHERE id = ?'
                )->execute( [ $name, $sector, (int) getPost( 'sort_order' ), isset( $_POST[ 'is_active' ] ) ? 1 : 0, $id ] );

                // Keep the public sector of every project in this category in step.
                $pdo->prepare( 'UPDATE projects SET category = ? WHERE category_id = ?' )->execute( [ $sector, $id ] );

                setFlash( 'success', 'Category updated.' );
                redirect( $redirectUrl( $returnOrg, $id ) );

                break;

            case 'delete_category':
                $id = (int) getPost( 'id' );

                $subUse     = $usageCount( 'project_subcategories', 'category_id', $id );
                $projectUse = $usageCount( 'projects', 'category_id', $id );

                if ( $subUse > 0 || $projectUse > 0 ) {
                    setFlash(
                        'error',
                        'This category still holds ' . $subUse . ' sub-categor' . ( $subUse === 1 ? 'y' : 'ies' )
                        . ' and ' . $projectUse . ' project(s). Move or remove them first.'
                    );
                    redirect( $back );
                }

                $pdo->prepare( 'DELETE FROM project_categories WHERE id = ?' )->execute( [ $id ] );
                setFlash( 'success', 'Category deleted.' );
                redirect( $redirectUrl( $returnOrg ) );

                break;

            case 'add_subcategory':
                $categoryId = (int) getPost( 'category_id' );
                $name       = getPost( 'name' );

                if ( $categoryId <= 0 || $name === '' ) {
                    setFlash( 'error', 'Choose a category and enter a sub-category name.' );
                    redirect( $back );
                }

                $exists = $pdo->prepare(
                    'SELECT id FROM project_subcategories WHERE category_id = ? AND LOWER(name) = LOWER(?) LIMIT 1'
                );
                $exists->execute( [ $categoryId, $name ] );
                if ( (int) $exists->fetchColumn() > 0 ) {
                    setFlash( 'error', 'That category already has a sub-category called "' . $name . '".' );
                    redirect( $redirectUrl( $returnOrg, $categoryId ) );
                }

                $insert = $pdo->prepare(
                    'INSERT INTO project_subcategories (category_id, name, slug, is_active, sort_order)
                     VALUES (?, ?, ?, 1, ?)'
                );
                $insert->execute( [
                    $categoryId,
                    $name,
                    wrcUniqueTaxonomySlug( 'project_subcategories', $categoryId, 'category_id', $name ),
                    (int) getPost( 'sort_order' ),
                ] );

                setFlash( 'success', 'Sub-category "' . $name . '" added.' );
                redirect( $redirectUrl( $returnOrg, $categoryId ) );

                break;

            case 'update_subcategory':
                $id   = (int) getPost( 'id' );
                $name = getPost( 'name' );

                if ( $id <= 0 || $name === '' ) {
                    setFlash( 'error', 'Sub-category name is required.' );
                    redirect( $back );
                }

                $pdo->prepare( 'UPDATE project_subcategories SET name = ?, sort_order = ?, is_active = ? WHERE id = ?' )
                    ->execute( [ $name, (int) getPost( 'sort_order' ), isset( $_POST[ 'is_active' ] ) ? 1 : 0, $id ] );

                setFlash( 'success', 'Sub-category updated.' );
                redirect( $redirectUrl( $returnOrg, $returnCat ) );

                break;

            case 'delete_subcategory':
                $id = (int) getPost( 'id' );

                $projectUse = $usageCount( 'projects', 'subcategory_id', $id );
                if ( $projectUse > 0 ) {
                    setFlash(
                        'error',
                        'This sub-category is used by ' . $projectUse . ' project(s). Move or remove them first.'
                    );
                    redirect( $back );
                }

                $pdo->prepare( 'DELETE FROM project_subcategories WHERE id = ?' )->execute( [ $id ] );
                setFlash( 'success', 'Sub-category deleted.' );
                redirect( $redirectUrl( $returnOrg, $returnCat ) );

                break;

            default:
                redirect( 'project-categories.php' );
        }
    } catch ( Throwable $error ) {
        setFlash( 'error', 'Could not save the change: ' . $error->getMessage() );
        redirect( $back );
    }
}

// ---------------------------------------------------------------------------
// Read state
// ---------------------------------------------------------------------------

$organizations = getRows( 'project_organizations', [], 'sort_order ASC, name ASC' );

$selectedOrgId = (int) ( $_GET[ 'org' ] ?? 0 );
$organizationExists = false;
foreach ( $organizations as $organization ) {
    if ( (int) $organization[ 'id' ] === $selectedOrgId ) {
        $organizationExists = true;
        break;
    }
}
if ( !$organizationExists ) {
    $selectedOrgId = $organizations ? (int) $organizations[ 0 ][ 'id' ] : 0;
}

$categories = $selectedOrgId > 0
    ? getRows( 'project_categories', [ 'organization_id' => $selectedOrgId ], 'sort_order ASC, name ASC' )
    : [];

$selectedCatId = (int) ( $_GET[ 'cat' ] ?? 0 );
$categoryExists = false;
foreach ( $categories as $category ) {
    if ( (int) $category[ 'id' ] === $selectedCatId ) {
        $categoryExists = true;
        break;
    }
}
if ( !$categoryExists ) {
    $selectedCatId = $categories ? (int) $categories[ 0 ][ 'id' ] : 0;
}

$subcategories = $selectedCatId > 0
    ? getRows( 'project_subcategories', [ 'category_id' => $selectedCatId ], 'sort_order ASC, name ASC' )
    : [];

$editOrgId   = (int) ( $_GET[ 'edit_org' ] ?? 0 );
$editCatId   = (int) ( $_GET[ 'edit_cat' ] ?? 0 );
$editSubId   = (int) ( $_GET[ 'edit_sub' ] ?? 0 );

// Usage counters, gathered in four small queries instead of one per row.
$countBy = static function ( string $sql ) use ( $pdo ): array {
    $counts = [];
    foreach ( $pdo->query( $sql )->fetchAll( PDO::FETCH_ASSOC ) as $row ) {
        $counts[ (int) $row[ 'ref_id' ] ] = (int) $row[ 'total' ];
    }
    return $counts;
};

$categoryCounts    = $countBy( 'SELECT organization_id AS ref_id, COUNT(*) AS total FROM project_categories GROUP BY organization_id' );
$projectOrgCounts  = $countBy( 'SELECT organization_id AS ref_id, COUNT(*) AS total FROM projects WHERE organization_id IS NOT NULL GROUP BY organization_id' );
$projectCatCounts  = $countBy( 'SELECT category_id AS ref_id, COUNT(*) AS total FROM projects WHERE category_id IS NOT NULL GROUP BY category_id' );
$projectSubCounts  = $countBy( 'SELECT subcategory_id AS ref_id, COUNT(*) AS total FROM projects WHERE subcategory_id IS NOT NULL GROUP BY subcategory_id' );
$subCountsByCat    = $countBy( 'SELECT category_id AS ref_id, COUNT(*) AS total FROM project_subcategories GROUP BY category_id' );

$totalCategories    = (int) $pdo->query( 'SELECT COUNT(*) FROM project_categories' )->fetchColumn();
$totalSubcategories = (int) $pdo->query( 'SELECT COUNT(*) FROM project_subcategories' )->fetchColumn();

$currentOrganization = null;
foreach ( $organizations as $organization ) {
    if ( (int) $organization[ 'id' ] === $selectedOrgId ) {
        $currentOrganization = $organization;
        break;
    }
}

$currentCategory = null;
foreach ( $categories as $category ) {
    if ( (int) $category[ 'id' ] === $selectedCatId ) {
        $currentCategory = $category;
        break;
    }
}

$inputClass  = 'w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/15 focus:border-brandRed';
$selectClass = $inputClass;
$labelClass  = 'block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1';

include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Project Categories</h1>
    <p class="text-slate-500 text-sm mt-1">Every project belongs to an organisation, then a category, then an optional sub-category.</p>
  </div>
  <div class="flex items-center gap-2">
    <a href="project-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-plus text-xs"></i> Add Project
    </a>
    <a href="projects.php" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
      <i class="fa-solid fa-arrow-left text-xs"></i> Projects
    </a>
  </div>
</div>

<!-- How the hierarchy works -->
<div class="mb-6 rounded-2xl border border-slate-200 bg-white px-5 py-4">
  <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
    <div class="flex items-center gap-2.5">
      <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-900 text-[11px] font-bold text-white">1</span>
      <div>
        <p class="text-[13px] font-bold text-slate-900 leading-tight">Organisation</p>
        <p class="text-[11px] text-slate-500 leading-tight">Who funds or implements the project</p>
      </div>
      <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500"><?= count( $organizations ) ?></span>
    </div>
    <i class="fa-solid fa-arrow-right hidden text-[10px] text-slate-300 lg:block"></i>
    <div class="flex items-center gap-2.5">
      <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-900 text-[11px] font-bold text-white">2</span>
      <div>
        <p class="text-[13px] font-bold text-slate-900 leading-tight">Category</p>
        <p class="text-[11px] text-slate-500 leading-tight">Belongs to one organisation only</p>
      </div>
      <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500"><?= $totalCategories ?></span>
    </div>
    <i class="fa-solid fa-arrow-right hidden text-[10px] text-slate-300 lg:block"></i>
    <div class="flex items-center gap-2.5">
      <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-900 text-[11px] font-bold text-white">3</span>
      <div>
        <p class="text-[13px] font-bold text-slate-900 leading-tight">Sub-category</p>
        <p class="text-[11px] text-slate-500 leading-tight">Optional, belongs to one category</p>
      </div>
      <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500"><?= $totalSubcategories ?></span>
    </div>
    <p class="text-[11px] leading-relaxed text-slate-400 lg:ml-auto lg:max-w-[280px]">
      <i class="fa-solid fa-circle-info mr-1 text-slate-300"></i>
      A category's programme sector keeps the public project filters and sector counters accurate.
    </p>
  </div>
</div>

<div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-3">

  <!-- ================= ORGANISATIONS ================= -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <header class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3">
      <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-900 text-[11px] font-bold text-white">1</span>
      <div class="min-w-0">
        <h2 class="text-sm font-bold text-slate-900">Organisations</h2>
        <p class="text-[11px] text-slate-500">ZFA, Turkiye Diyanet, KP-TEVTA…</p>
      </div>
      <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200"><?= count( $organizations ) ?></span>
    </header>

    <div class="max-h-[520px] space-y-2 overflow-y-auto p-3">
      <?php if ( empty( $organizations ) ): ?>
        <p class="px-2 py-6 text-center text-[12px] text-slate-400">No organisations yet. Add the first one below.</p>
      <?php endif; ?>

      <?php foreach ( $organizations as $organization ): ?>
        <?php
          $orgId      = (int) $organization[ 'id' ];
          $isSelected = $orgId === $selectedOrgId;
          $catCount   = (int) ( $categoryCounts[ $orgId ] ?? 0 );
          $projCount  = (int) ( $projectOrgCounts[ $orgId ] ?? 0 );
        ?>
        <div class="rounded-xl border <?= $isSelected ? 'border-brandRed/30 bg-red-50/40' : 'border-slate-200 bg-white' ?>">
          <div class="flex items-center gap-1.5 p-2">
            <a href="<?= htmlspecialchars( $redirectUrl( $orgId, 0 ) ) ?>" class="min-w-0 flex-1 rounded-lg px-1.5 py-1">
              <p class="truncate text-[13px] font-semibold <?= $isSelected ? 'text-brandRed' : 'text-slate-800' ?>">
                <?= htmlspecialchars( $organization[ 'name' ] ) ?>
                <?php if ( !(int) $organization[ 'is_active' ] ): ?>
                  <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">Inactive</span>
                <?php endif; ?>
              </p>
              <p class="mt-0.5 text-[11px] text-slate-500">
                <?= $catCount ?> <?= $catCount === 1 ? 'category' : 'categories' ?> · <?= $projCount ?> <?= $projCount === 1 ? 'project' : 'projects' ?>
              </p>
            </a>
            <a href="<?= htmlspecialchars( $redirectUrl( $orgId, $selectedCatId, [ 'edit_org' => $orgId ] ) ) ?>"
               class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-slate-700" title="Edit organisation">
              <i class="fa-solid fa-pen text-[11px]"></i>
            </a>
            <form method="POST" class="inline" onsubmit="return confirm('Delete this organisation? Only possible when it has no categories or projects.')">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="action" value="delete_organization">
              <input type="hidden" name="id" value="<?= $orgId ?>">
              <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
              <input type="hidden" name="return_cat" value="<?= $selectedCatId ?>">
              <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-red-600" title="Delete organisation">
                <i class="fa-solid fa-trash text-[11px]"></i>
              </button>
            </form>
          </div>

          <?php if ( $editOrgId === $orgId ): ?>
            <form method="POST" class="space-y-2.5 border-t border-slate-200/70 bg-white p-3">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="action" value="update_organization">
              <input type="hidden" name="id" value="<?= $orgId ?>">
              <input type="hidden" name="return_org" value="<?= $orgId ?>">
              <input type="hidden" name="return_cat" value="<?= $selectedCatId ?>">
              <div>
                <label class="<?= $labelClass ?>">Name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars( $organization[ 'name' ] ) ?>" class="<?= $inputClass ?>">
              </div>
              <div class="flex items-end gap-2">
                <div class="w-20">
                  <label class="<?= $labelClass ?>">Order</label>
                  <input type="number" name="sort_order" value="<?= (int) $organization[ 'sort_order' ] ?>" class="<?= $inputClass ?>">
                </div>
                <label class="mb-2 flex items-center gap-1.5 text-[12px] text-slate-600">
                  <input type="checkbox" name="is_active" value="1" <?= (int) $organization[ 'is_active' ] ? 'checked' : '' ?> class="rounded border-slate-300 text-brandRed focus:ring-brandRed/30">
                  Active
                </label>
              </div>
              <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-slate-800">Save</button>
                <a href="<?= htmlspecialchars( $redirectUrl( $orgId, $selectedCatId ) ) ?>" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[12px] font-semibold text-slate-600 hover:bg-slate-50">Cancel</a>
              </div>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="border-t border-slate-100 bg-slate-50/60 p-3">
      <form method="POST" class="space-y-2">
        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
        <input type="hidden" name="action" value="add_organization">
        <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
        <div class="flex items-center gap-2">
          <input type="text" name="name" required placeholder="New organisation, e.g. UNICEF" class="<?= $inputClass ?>">
          <button type="submit" class="grid h-[38px] w-[38px] shrink-0 place-items-center rounded-lg bg-slate-900 text-white transition-colors hover:bg-slate-800" title="Add organisation">
            <i class="fa-solid fa-plus text-[11px]"></i>
          </button>
        </div>
      </form>
    </div>
  </section>

  <!-- ================= CATEGORIES ================= -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <header class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3">
      <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-900 text-[11px] font-bold text-white">2</span>
      <div class="min-w-0">
        <h2 class="truncate text-sm font-bold text-slate-900">
          Categories
          <?php if ( $currentOrganization ): ?>
            <span class="font-normal text-slate-400">· <?= htmlspecialchars( $currentOrganization[ 'name' ] ) ?></span>
          <?php endif; ?>
        </h2>
        <p class="text-[11px] text-slate-500">Only this organisation's categories</p>
      </div>
      <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200"><?= count( $categories ) ?></span>
    </header>

    <div class="max-h-[520px] space-y-2 overflow-y-auto p-3">
      <?php if ( !$currentOrganization ): ?>
        <p class="px-2 py-6 text-center text-[12px] text-slate-400">Select an organisation first.</p>
      <?php elseif ( empty( $categories ) ): ?>
        <p class="px-2 py-6 text-center text-[12px] text-slate-400">No categories for this organisation yet. Add the first one below.</p>
      <?php endif; ?>

      <?php foreach ( $categories as $category ): ?>
        <?php
          $catId      = (int) $category[ 'id' ];
          $isSelected = $catId === $selectedCatId;
          $projCount  = (int) ( $projectCatCounts[ $catId ] ?? 0 );
          $subCount   = (int) ( $subCountsByCat[ $catId ] ?? 0 );
        ?>
        <div class="rounded-xl border <?= $isSelected ? 'border-brandRed/30 bg-red-50/40' : 'border-slate-200 bg-white' ?>">
          <div class="flex items-center gap-1.5 p-2">
            <a href="<?= htmlspecialchars( $redirectUrl( $selectedOrgId, $catId ) ) ?>" class="min-w-0 flex-1 rounded-lg px-1.5 py-1">
              <p class="truncate text-[13px] font-semibold <?= $isSelected ? 'text-brandRed' : 'text-slate-800' ?>">
                <?= htmlspecialchars( $category[ 'name' ] ) ?>
                <?php if ( !(int) $category[ 'is_active' ] ): ?>
                  <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">Inactive</span>
                <?php endif; ?>
              </p>
              <p class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500">
                <span class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600"><?= htmlspecialchars( $category[ 'sector' ] ) ?></span>
                <span><?= $subCount ?> sub</span>
                <span class="text-slate-300">·</span>
                <span><?= $projCount ?> <?= $projCount === 1 ? 'project' : 'projects' ?></span>
              </p>
            </a>
            <a href="<?= htmlspecialchars( $redirectUrl( $selectedOrgId, $catId, [ 'edit_cat' => $catId ] ) ) ?>"
               class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-slate-700" title="Edit category">
              <i class="fa-solid fa-pen text-[11px]"></i>
            </a>
            <form method="POST" class="inline" onsubmit="return confirm('Delete this category? Only possible when it has no sub-categories or projects.')">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="action" value="delete_category">
              <input type="hidden" name="id" value="<?= $catId ?>">
              <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
              <input type="hidden" name="return_cat" value="<?= $selectedCatId ?>">
              <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-red-600" title="Delete category">
                <i class="fa-solid fa-trash text-[11px]"></i>
              </button>
            </form>
          </div>

          <?php if ( $editCatId === $catId ): ?>
            <form method="POST" class="space-y-2.5 border-t border-slate-200/70 bg-white p-3">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="action" value="update_category">
              <input type="hidden" name="id" value="<?= $catId ?>">
              <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
              <input type="hidden" name="return_cat" value="<?= $catId ?>">
              <div>
                <label class="<?= $labelClass ?>">Category name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars( $category[ 'name' ] ) ?>" class="<?= $inputClass ?>">
              </div>
              <div>
                <label class="<?= $labelClass ?>">Programme sector</label>
                <select name="sector" class="<?= $selectClass ?>">
                  <?php foreach ( $sectorOptions as $sector ): ?>
                    <option value="<?= htmlspecialchars( $sector ) ?>" <?= $category[ 'sector' ] === $sector ? 'selected' : '' ?>><?= htmlspecialchars( $sector ) ?></option>
                  <?php endforeach; ?>
                </select>
                <p class="mt-1 text-[10px] text-slate-400">Controls the public filter and sector counters.</p>
              </div>
              <div class="flex items-end gap-2">
                <div class="w-20">
                  <label class="<?= $labelClass ?>">Order</label>
                  <input type="number" name="sort_order" value="<?= (int) $category[ 'sort_order' ] ?>" class="<?= $inputClass ?>">
                </div>
                <label class="mb-2 flex items-center gap-1.5 text-[12px] text-slate-600">
                  <input type="checkbox" name="is_active" value="1" <?= (int) $category[ 'is_active' ] ? 'checked' : '' ?> class="rounded border-slate-300 text-brandRed focus:ring-brandRed/30">
                  Active
                </label>
              </div>
              <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-slate-800">Save</button>
                <a href="<?= htmlspecialchars( $redirectUrl( $selectedOrgId, $catId ) ) ?>" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[12px] font-semibold text-slate-600 hover:bg-slate-50">Cancel</a>
              </div>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="border-t border-slate-100 bg-slate-50/60 p-3">
      <?php if ( !$currentOrganization ): ?>
        <p class="px-1 py-2 text-[11px] text-slate-400">Select an organisation to add categories.</p>
      <?php else: ?>
        <form method="POST" class="space-y-2">
          <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
          <input type="hidden" name="action" value="add_category">
          <input type="hidden" name="organization_id" value="<?= $selectedOrgId ?>">
          <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
          <input type="text" name="name" required placeholder="New category, e.g. Ramadan Food Packs" class="<?= $inputClass ?>">
          <div class="flex items-center gap-2">
            <select name="sector" class="<?= $selectClass ?>" title="Programme sector used on the public site">
              <?php foreach ( $sectorOptions as $sector ): ?>
                <option value="<?= htmlspecialchars( $sector ) ?>"><?= htmlspecialchars( $sector ) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="grid h-[38px] w-[38px] shrink-0 place-items-center rounded-lg bg-slate-900 text-white transition-colors hover:bg-slate-800" title="Add category">
              <i class="fa-solid fa-plus text-[11px]"></i>
            </button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </section>

  <!-- ================= SUB-CATEGORIES ================= -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <header class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/70 px-4 py-3">
      <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-900 text-[11px] font-bold text-white">3</span>
      <div class="min-w-0">
        <h2 class="truncate text-sm font-bold text-slate-900">
          Sub-categories
          <?php if ( $currentCategory ): ?>
            <span class="font-normal text-slate-400">· <?= htmlspecialchars( $currentCategory[ 'name' ] ) ?></span>
          <?php endif; ?>
        </h2>
        <p class="text-[11px] text-slate-500">Optional, inside the selected category</p>
      </div>
      <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200"><?= count( $subcategories ) ?></span>
    </header>

    <div class="max-h-[520px] space-y-2 overflow-y-auto p-3">
      <?php if ( !$currentCategory ): ?>
        <p class="px-2 py-6 text-center text-[12px] text-slate-400">Select a category to see its sub-categories.</p>
      <?php elseif ( empty( $subcategories ) ): ?>
        <p class="px-2 py-6 text-center text-[12px] text-slate-400">No sub-categories yet. Add one below, or leave the category as it is.</p>
      <?php endif; ?>

      <?php foreach ( $subcategories as $subcategory ): ?>
        <?php
          $subId     = (int) $subcategory[ 'id' ];
          $projCount = (int) ( $projectSubCounts[ $subId ] ?? 0 );
        ?>
        <div class="rounded-xl border border-slate-200 bg-white">
          <div class="flex items-center gap-1.5 p-2">
            <div class="min-w-0 flex-1 rounded-lg px-1.5 py-1">
              <p class="truncate text-[13px] font-semibold text-slate-800">
                <?= htmlspecialchars( $subcategory[ 'name' ] ) ?>
                <?php if ( !(int) $subcategory[ 'is_active' ] ): ?>
                  <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">Inactive</span>
                <?php endif; ?>
              </p>
              <p class="mt-0.5 text-[11px] text-slate-500"><?= $projCount ?> <?= $projCount === 1 ? 'project' : 'projects' ?></p>
            </div>
            <a href="<?= htmlspecialchars( $redirectUrl( $selectedOrgId, $selectedCatId, [ 'edit_sub' => $subId ] ) ) ?>"
               class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-700" title="Edit sub-category">
              <i class="fa-solid fa-pen text-[11px]"></i>
            </a>
            <form method="POST" class="inline" onsubmit="return confirm('Delete this sub-category? Only possible when no project uses it.')">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="action" value="delete_subcategory">
              <input type="hidden" name="id" value="<?= $subId ?>">
              <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
              <input type="hidden" name="return_cat" value="<?= $selectedCatId ?>">
              <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-white hover:text-red-600" title="Delete sub-category">
                <i class="fa-solid fa-trash text-[11px]"></i>
              </button>
            </form>
          </div>

          <?php if ( $editSubId === $subId ): ?>
            <form method="POST" class="space-y-2.5 border-t border-slate-200/70 bg-white p-3">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="action" value="update_subcategory">
              <input type="hidden" name="id" value="<?= $subId ?>">
              <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
              <input type="hidden" name="return_cat" value="<?= $selectedCatId ?>">
              <div>
                <label class="<?= $labelClass ?>">Sub-category name</label>
                <input type="text" name="name" required value="<?= htmlspecialchars( $subcategory[ 'name' ] ) ?>" class="<?= $inputClass ?>">
              </div>
              <div class="flex items-end gap-2">
                <div class="w-20">
                  <label class="<?= $labelClass ?>">Order</label>
                  <input type="number" name="sort_order" value="<?= (int) $subcategory[ 'sort_order' ] ?>" class="<?= $inputClass ?>">
                </div>
                <label class="mb-2 flex items-center gap-1.5 text-[12px] text-slate-600">
                  <input type="checkbox" name="is_active" value="1" <?= (int) $subcategory[ 'is_active' ] ? 'checked' : '' ?> class="rounded border-slate-300 text-brandRed focus:ring-brandRed/30">
                  Active
                </label>
              </div>
              <div class="flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-slate-800">Save</button>
                <a href="<?= htmlspecialchars( $redirectUrl( $selectedOrgId, $selectedCatId ) ) ?>" class="rounded-lg border border-slate-200 px-3 py-1.5 text-[12px] font-semibold text-slate-600 hover:bg-slate-50">Cancel</a>
              </div>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="border-t border-slate-100 bg-slate-50/60 p-3">
      <?php if ( !$currentCategory ): ?>
        <p class="px-1 py-2 text-[11px] text-slate-400">Select a category to add sub-categories.</p>
      <?php else: ?>
        <form method="POST" class="flex items-center gap-2">
          <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
          <input type="hidden" name="action" value="add_subcategory">
          <input type="hidden" name="category_id" value="<?= $selectedCatId ?>">
          <input type="hidden" name="return_org" value="<?= $selectedOrgId ?>">
          <input type="hidden" name="return_cat" value="<?= $selectedCatId ?>">
          <input type="text" name="name" required placeholder="New sub-category" class="<?= $inputClass ?>">
          <button type="submit" class="grid h-[38px] w-[38px] shrink-0 place-items-center rounded-lg bg-slate-900 text-white transition-colors hover:bg-slate-800" title="Add sub-category">
            <i class="fa-solid fa-plus text-[11px]"></i>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
