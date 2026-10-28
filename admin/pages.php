<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Pages';

// Handle save
if ( isPost() && isset( $_POST[ 'save_sections' ] ) ) {
    if ( !isset( $_POST[ 'csrf_token' ] ) || !verifyCSRF( $_POST[ 'csrf_token' ] ) ) {
        setFlash( 'error', 'Invalid security token.' );
        redirect( 'pages.php' );
    }

    $pageKey = $_POST[ 'page_key' ] ?? '';
    $sectionsData = json_decode( $_POST[ 'sections_json' ] ?? '{}', true );

    if ( !$pageKey || !is_array( $sectionsData ) ) {
        setFlash( 'error', 'Invalid page or data.' );
        redirect( 'pages.php' );
    }

    global $pdo;

    // Dedicated table save functions for migrated sections

    function saveHeroSection( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( $sectionKey );
        $updateData = [
            'hero_image' => $content[ 'hero_image' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? ( $data[ 'section_subtitle' ] ?? '' ),
        ];
        // chairman_message has extra fields
        if ( $sectionKey === 'chairman_message' ) {
            $updateData[ 'image_path' ] = $content[ 'image_path' ] ?? $content[ 'hero_image' ] ?? '';
            $updateData[ 'overlay_title' ] = $content[ 'overlay_title' ] ?? '';
            $updateData[ 'note_label' ] = $content[ 'note_label' ] ?? '';
            $updateData[ 'paragraph1' ] = $content[ 'paragraph1' ] ?? '';
            $updateData[ 'paragraph2' ] = $content[ 'paragraph2' ] ?? '';
            $updateData[ 'person_name' ] = $content[ 'person_name' ] ?? '';
            $updateData[ 'person_title' ] = $content[ 'person_title' ] ?? '';
            $updateData[ 'vision_label' ] = $content[ 'vision_label' ] ?? '';
            $updateData[ 'vision_text' ] = $content[ 'vision_text' ] ?? '';
        } else {
            // about_hero. Every other hero table stores its heading in
            // section_title; about_hero was created without one, which is why
            // edits to the About page heading used to vanish. The columns are
            // created by database/run_project_import.php on deployment, and
            // tableHasColumn() keeps a save working during the deploy window
            // in which the new admin code is live but the columns are not.
            if ( tableHasColumn( $sectionKey, 'section_title' ) ) {
                $updateData[ 'section_title' ] = $data[ 'section_title' ] ?? '';
            }
            if ( tableHasColumn( $sectionKey, 'section_subtitle' ) ) {
                $updateData[ 'section_subtitle' ] = $data[ 'section_subtitle' ] ?? '';
            }
            $updateData[ 'button1_text' ] = $content[ 'button1_text' ] ?? '';
            $updateData[ 'button1_link' ] = $content[ 'button1_link' ] ?? '';
            $updateData[ 'button2_text' ] = $content[ 'button2_text' ] ?? '';
            $updateData[ 'button2_link' ] = $content[ 'button2_link' ] ?? '';
        }
        if ( $exists ) {
            updateRow( $sectionKey, $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( $sectionKey, $updateData );
        }

        // The dedicated chairman table stores message fields, while the
        // section heading and subtitle belong to homepage_sections.
        if ( $sectionKey === 'chairman_message' ) {
            $sectionMeta = getRow( 'homepage_sections', [ 'section_key' => $sectionKey ] );
            // Merge over whatever is stored instead of replacing the blob. The
            // public pages read the chairman table (index.php overrides the map
            // with it), but this mirror is still written on every save, and a
            // wholesale replace used to discard any key the editor does not
            // expose. Anything the form does post still wins.
            $storedMeta = [];
            if ( $sectionMeta && !empty( $sectionMeta[ 'content' ] ) ) {
                $decoded = json_decode( $sectionMeta[ 'content' ], true );
                if ( is_array( $decoded ) ) {
                    $storedMeta = $decoded;
                }
            }
            $sectionMetaData = [
                'section_title' => $data[ 'section_title' ] ?? ( $sectionMeta[ 'section_title' ] ?? "Chairman's Message" ),
                'section_subtitle' => $data[ 'section_subtitle' ] ?? ( $sectionMeta[ 'section_subtitle' ] ?? '' ),
                'content' => json_encode( array_merge( $storedMeta, $content ), JSON_UNESCAPED_UNICODE ),
            ];
            if ( $sectionMeta ) {
                updateRow( 'homepage_sections', $sectionMetaData, [ 'id' => $sectionMeta[ 'id' ] ] );
            } else {
                insertRow( 'homepage_sections', array_merge( [
                    'section_key' => $sectionKey,
                    'is_active' => 1,
                    'sort_order' => 1,
                ], $sectionMetaData ) );
            }
        }
    }

    function saveCtaSection( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $table = $sectionKey;
        // about_cta, cta_banner, etc.
        $exists = getRow( $table );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
            'button1_text' => $content[ 'button1_text' ] ?? '',
            'button1_link' => $content[ 'button1_link' ] ?? '',
            'button2_text' => $content[ 'button2_text' ] ?? '',
            'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( $table, $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( $table, $updateData );
        }
    }

    function saveMissionSection( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'about_mission' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'about_mission', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'about_mission', $updateData );
        }
        // Save items
        $mission = getRow( 'about_mission' );
        if ( $mission ) {
            $pdo->exec( "DELETE FROM about_mission_items WHERE mission_id = {$mission['id']}" );
            $items = $content[ 'items' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'about_mission_items', [
                    'mission_id' => $mission[ 'id' ],
                    'icon' => $item[ 'icon' ] ?? 'fa-bullseye',
                    'icon_bg' => $item[ 'icon_bg' ] ?? 'bg-brandRed/10',
                    'icon_color' => $item[ 'icon_color' ] ?? 'text-brandRed',
                    'title' => $item[ 'title' ] ?? '',
                    'description' => $item[ 'description' ] ?? '',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveGovernanceSection( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'about_governance' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'about_governance', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'about_governance', $updateData );
        }
        // Save items
        $gov = getRow( 'about_governance' );
        if ( $gov ) {
            $pdo->exec( "DELETE FROM about_governance_items WHERE governance_id = {$gov['id']}" );
            $items = $content[ 'items' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'about_governance_items', [
                    'governance_id' => $gov[ 'id' ],
                    'icon' => $item[ 'icon' ] ?? 'fa-circle-info',
                    'title' => $item[ 'title' ] ?? '',
                    'description' => $item[ 'description' ] ?? '',
                    'link' => $item[ 'link' ] ?? '#',
                    'link_text' => $item[ 'link_text' ] ?? 'Learn More',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveTransparencySection( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'about_transparency' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'badge' => $content[ 'badge' ] ?? '',
            'button1_text' => $content[ 'button1_text' ] ?? '',
            'button1_link' => $content[ 'button1_link' ] ?? '',
            'button2_text' => $content[ 'button2_text' ] ?? '',
            'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'about_transparency', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'about_transparency', $updateData );
        }
        // Save stats
        $trans = getRow( 'about_transparency' );
        if ( $trans ) {
            $pdo->exec( "DELETE FROM about_transparency_stats WHERE transparency_id = {$trans['id']}" );
            $stats = $content[ 'stats' ] ?? [];
            foreach ( $stats as $i => $stat ) {
                insertRow( 'about_transparency_stats', [
                    'transparency_id' => $trans[ 'id' ],
                    'stat_value' => $stat[ 'value' ] ?? '',
                    'stat_label' => $stat[ 'label' ] ?? '',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveGeographicPresence( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'geographic_presence' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'geographic_presence', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'geographic_presence', $updateData );
        }
        // Save stats
        $geo = getRow( 'geographic_presence' );
        if ( $geo ) {
            $pdo->exec( "DELETE FROM geographic_presence_stats WHERE geo_id = {$geo['id']}" );
            $stats = $content[ 'stats' ] ?? [];
            foreach ( $stats as $i => $stat ) {
                insertRow( 'geographic_presence_stats', [
                    'geo_id' => $geo[ 'id' ],
                    'stat_value' => $stat[ 'value' ] ?? '',
                    'stat_label' => $stat[ 'label' ] ?? '',
                    'stat_icon' => $stat[ 'icon' ] ?? '',
                    'stat_color' => $stat[ 'color' ] ?? '',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveOurWorkHero( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'our_work_hero' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'hero_image' => $content[ 'hero_image' ] ?? $content[ 'image_path' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? '',
            'button1_text' => $content[ 'button1_text' ] ?? '',
            'button1_link' => $content[ 'button1_link' ] ?? '',
            'button2_text' => $content[ 'button2_text' ] ?? '',
            'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'our_work_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'our_work_hero', $updateData );
        }
    }

    function saveOurWorkEditorial( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'our_work_editorial' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'image' => $content[ 'image' ] ?? '',
            'label' => $content[ 'label' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'our_work_editorial', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'our_work_editorial', $updateData );
        }
        $ed = getRow( 'our_work_editorial' );
        if ( $ed ) {
            $pdo->exec( "DELETE FROM our_work_editorial_features WHERE editorial_id = {$ed['id']}" );
            $features = $content[ 'features' ] ?? [];
            foreach ( $features as $i => $f ) {
                insertRow( 'our_work_editorial_features', [
                    'editorial_id' => $ed[ 'id' ],
                    'icon' => $f[ 'icon' ] ?? '',
                    'title' => $f[ 'title' ] ?? '',
                    'description' => $f[ 'description' ] ?? '',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveOurWorkProcess( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'our_work_process' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'our_work_process', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'our_work_process', $updateData );
        }
        $proc = getRow( 'our_work_process' );
        if ( $proc ) {
            $pdo->exec( "DELETE FROM our_work_process_steps WHERE process_id = {$proc['id']}" );
            $steps = $content[ 'steps' ] ?? [];
            foreach ( $steps as $i => $step ) {
                insertRow( 'our_work_process_steps', [
                    'process_id' => $proc[ 'id' ],
                    'step_num' => $step[ 'num' ] ?? '',
                    'title' => $step[ 'title' ] ?? '',
                    'description' => $step[ 'description' ] ?? '',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveOurWorkPillars( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'our_work_pillars' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'our_work_pillars', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'our_work_pillars', $updateData );
        }
        $pill = getRow( 'our_work_pillars' );
        if ( $pill ) {
            $pdo->exec( "DELETE FROM our_work_pillars_items WHERE pillars_id = {$pill['id']}" );
            // Admin form saves as 'items', frontend reads as 'pillars'
            $items = $content[ 'items' ] ?? $content[ 'pillars' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'our_work_pillars_items', [
                    'pillars_id' => $pill[ 'id' ],
                    'pillar_num' => $item[ 'num' ] ?? '',
                    'title' => $item[ 'title' ] ?? '',
                    'description' => $item[ 'description' ] ?? '',
                    'sort_order' => $i,
                ] );
            }
        }
    }

    function saveOurWorkFeatured( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'our_work_featured_projects' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'our_work_featured_projects', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'our_work_featured_projects', $updateData );
        }
    }

    function saveOurWorkCta( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'our_work_cta' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
            'button1_text' => $content[ 'button1_text' ] ?? '',
            'button1_link' => $content[ 'button1_link' ] ?? '',
            'button2_text' => $content[ 'button2_text' ] ?? '',
            'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'our_work_cta', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'our_work_cta', $updateData );
        }
    }

    function saveProjectsHero( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'projects_hero' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'hero_image' => $content[ 'hero_image' ] ?? $content[ 'image_path' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? '',
            'button1_text' => $content[ 'button1_text' ] ?? '',
            'button1_link' => $content[ 'button1_link' ] ?? '',
            'button2_text' => $content[ 'button2_text' ] ?? '',
            'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'projects_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'projects_hero', $updateData );
        }
    }

    function saveProjectsDashboard( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'projects_dashboard' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'subtitle' => $content[ 'subtitle' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'projects_dashboard', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'projects_dashboard', $updateData );
        }
        $dash = getRow( 'projects_dashboard' );
        if ( $dash ) {
            $pdo->exec( "DELETE FROM projects_dashboard_metrics WHERE dashboard_id = {$dash['id']}" );
            $metrics = $content[ 'metrics' ] ?? [];
            foreach ( $metrics as $i => $m ) {
                insertRow( 'projects_dashboard_metrics', [
                    'dashboard_id' => $dash[ 'id' ], 'metric_icon' => $m[ 'icon' ] ?? '', 'metric_color' => $m[ 'color' ] ?? '',
                    'metric_label' => $m[ 'label' ] ?? '', 'metric_value' => $m[ 'value' ] ?? '', 'sort_order' => $i,
                ] );
            }
            $pdo->exec( "DELETE FROM projects_dashboard_sectors WHERE dashboard_id = {$dash['id']}" );
            $sectors = $content[ 'sectors' ] ?? [];
            foreach ( $sectors as $i => $sec ) {
                insertRow( 'projects_dashboard_sectors', [
                    'dashboard_id' => $dash[ 'id' ], 'sector_name' => $sec[ 'name' ] ?? '', 'sector_color' => $sec[ 'color' ] ?? '',
                    'sector_count' => $sec[ 'count' ] ?? 0, 'sector_percent' => $sec[ 'percent' ] ?? 0,
                    'sector_gradient' => $sec[ 'gradient' ] ?? '', 'sort_order' => $i,
                ] );
            }
        }
    }

    function saveProjectsCta( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'projects_cta' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '', 'description' => $content[ 'description' ] ?? '',
            'icon' => $content[ 'icon' ] ?? '', 'button1_text' => $content[ 'button1_text' ] ?? '',
            'button1_link' => $content[ 'button1_link' ] ?? '', 'button2_text' => $content[ 'button2_text' ] ?? '',
            'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'projects_cta', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'projects_cta', $updateData );
        }
    }

    function saveCausesHero( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'causes_hero' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'hero_image' => $content[ 'hero_image' ] ?? $content[ 'image_path' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? '', 'badge' => $content[ 'badge' ] ?? '',
            'button1_text' => $content[ 'button1_text' ] ?? '', 'button1_link' => $content[ 'button1_link' ] ?? '',
            'button2_text' => $content[ 'button2_text' ] ?? '', 'button2_link' => $content[ 'button2_link' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'causes_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'causes_hero', $updateData );
        }
    }

    function saveCausesImpact( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'causes_impact' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'causes_impact', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'causes_impact', $updateData );
        }
        $imp = getRow( 'causes_impact' );
        if ( $imp ) {
            $pdo->exec( "DELETE FROM causes_impact_stats WHERE impact_id = {$imp['id']}" );
            $stats = $content[ 'stats' ] ?? [];
            foreach ( $stats as $i => $stat ) {
                insertRow( 'causes_impact_stats', [
                    'impact_id' => $imp[ 'id' ], 'stat_icon' => $stat[ 'icon' ] ?? '',
                    'stat_label' => $stat[ 'label' ] ?? '', 'stat_value' => $stat[ 'value' ] ?? '', 'sort_order' => $i,
                ] );
            }
        }
    }

    function saveWhoWeAreHero( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'who_we_are_hero' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
        'hero_image' => $content[ 'hero_image' ] ?? '', 'subtitle' => $content[ 'subtitle' ] ?? '',
        'button1_text' => $content[ 'button1_text' ] ?? '', 'button1_link' => $content[ 'button1_link' ] ?? '',
        'button2_text' => $content[ 'button2_text' ] ?? '', 'button2_link' => $content[ 'button2_link' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'who_we_are_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'who_we_are_hero', $updateData );
        }
    }

    function saveWhoWeAreImpact( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'who_we_are_impact' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'who_we_are_impact', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'who_we_are_impact', $updateData );
        }
        $imp = getRow( 'who_we_are_impact' );
        if ( $imp ) {
            $pdo->exec( "DELETE FROM who_we_are_impact_items WHERE impact_id = {$imp['id']}" );
            $items = $content[ 'items' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'who_we_are_impact_items', [ 'impact_id' => $imp[ 'id' ], 'icon' => $item[ 'icon' ] ?? '', 'title' => $item[ 'title' ] ?? '', 'description' => $item[ 'description' ] ?? '', 'sort_order' => $i ] );
            }
        }
    }

    function saveWhyWeExist( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'why_we_exist' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'why_we_exist', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'why_we_exist', $updateData );
        }
        $why = getRow( 'why_we_exist' );
        if ( $why ) {
            $pdo->exec( "DELETE FROM why_we_exist_items WHERE why_id = {$why['id']}" );
            $items = $content[ 'items' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'why_we_exist_items', [ 'why_id' => $why[ 'id' ], 'icon' => $item[ 'icon' ] ?? '', 'title' => $item[ 'title' ] ?? '', 'description' => $item[ 'description' ] ?? '', 'sort_order' => $i ] );
            }
        }
    }

    function saveHistoryHero( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'history_hero' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
        'hero_image' => $content[ 'hero_image' ] ?? '', 'subtitle' => $content[ 'subtitle' ] ?? '',
        'button1_text' => $content[ 'button1_text' ] ?? '', 'button1_link' => $content[ 'button1_link' ] ?? '',
        'button2_text' => $content[ 'button2_text' ] ?? '', 'button2_link' => $content[ 'button2_link' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'history_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'history_hero', $updateData );
        }
    }

    function saveHistoryStats( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'history_stats' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'history_stats', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'history_stats', $updateData );
        }
        $stats = getRow( 'history_stats' );
        if ( $stats ) {
            $pdo->exec( "DELETE FROM history_stats_items WHERE stats_id = {$stats['id']}" );
            $items = $content[ 'stats' ] ?? [];
            foreach ( $items as $i => $stat ) {
                insertRow( 'history_stats_items', [ 'stats_id' => $stats[ 'id' ], 'stat_icon' => $stat[ 'icon' ] ?? '', 'stat_color' => $stat[ 'color' ] ?? '', 'stat_label' => $stat[ 'label' ] ?? '', 'stat_value' => $stat[ 'value' ] ?? '', 'sort_order' => $i ] );
            }
        }
    }

    function saveHistoryCta( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'history_cta' );
        $updateData = [ 'badge' => $content[ 'badge' ] ?? '', 'section_title' => $data[ 'section_title' ] ?? $content[ 'section_title' ] ?? '',
        'section_subtitle' => $data[ 'section_subtitle' ] ?? $content[ 'section_subtitle' ] ?? '',
        'button1_text' => $content[ 'button1_text' ] ?? '', 'button1_link' => $content[ 'button1_link' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'history_cta', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'history_cta', $updateData );
        }
    }

    function saveGenericHero( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( $sectionKey );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'hero_image' => $content[ 'hero_image' ] ?? '' ];
        if ( $exists ) {
            updateRow( $sectionKey, $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( $sectionKey, $updateData );
        }
    }

    function saveGenericContent( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( $sectionKey );
        $jsonContent = json_encode( $content, JSON_UNESCAPED_UNICODE );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'content' => $jsonContent ];
        if ( $exists ) {
            updateRow( $sectionKey, $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( $sectionKey, $updateData );
        }
    }

    function saveNewsHero( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'news_hero' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'hero_image' => $content[ 'hero_image' ] ?? '', 'subtitle' => $content[ 'subtitle' ] ?? '', 'badge' => $content[ 'badge' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'news_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'news_hero', $updateData );
        }
    }

    function saveGetInvolvedHero( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'get_involved_hero' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'hero_image' => $content[ 'hero_image' ] ?? '', 'subtitle' => $content[ 'subtitle' ] ?? '',
        'badge' => $content[ 'badge' ] ?? '', 'button1_text' => $content[ 'button1_text' ] ?? '', 'button2_text' => $content[ 'button2_text' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'get_involved_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'get_involved_hero', $updateData );
        }
    }

    function saveContributeHero( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'contribute_hero' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'hero_image' => $content[ 'hero_image' ] ?? '', 'subtitle' => $content[ 'subtitle' ] ?? '',
        'badge' => $content[ 'badge' ] ?? '', 'title' => $content[ 'title' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'contribute_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'contribute_hero', $updateData );
        }
    }

    function saveImpactHero( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'impact_hero' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'hero_image' => $content[ 'hero_image' ] ?? '',
            'heading' => $content[ 'heading' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
            'cta_primary' => $content[ 'cta_primary' ] ?? 'View Impact Dashboard',
            'cta_secondary' => $content[ 'cta_secondary' ] ?? 'SDG Contributions',
        ];
        if ( $exists ) {
            updateRow( 'impact_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'impact_hero', $updateData );
        }
    }

    function saveImpactContent( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'impact_content' );
        $jsonContent = json_encode( $content, JSON_UNESCAPED_UNICODE );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'content' => $jsonContent ];
        if ( $exists ) {
            updateRow( 'impact_content', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'impact_content', $updateData );
        }
    }

    function saveImpactCta( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'homepage_sections', [ 'section_key' => 'impact_cta' ] );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'content' => json_encode( $content, JSON_UNESCAPED_UNICODE ),
        ];
        if ( $exists ) {
            updateRow( 'homepage_sections', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'homepage_sections', array_merge( [ 'section_key' => 'impact_cta', 'is_active' => 1, 'sort_order' => 50 ], $updateData ) );
        }
    }

    function saveWhereWeWorkHero( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'where_we_work_hero' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'hero_image' => $content[ 'hero_image' ] ?? '',
            'subtitle' => $content[ 'subtitle' ] ?? '',
            'cta_primary' => $content[ 'cta_primary' ] ?? 'Explore Our Map',
            'cta_secondary' => $content[ 'cta_secondary' ] ?? 'District Coverage',
        ];
        if ( $exists ) {
            updateRow( 'where_we_work_hero', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'where_we_work_hero', $updateData );
        }
    }

    function saveWhereWeWorkContent( $sectionKey, $data ) {
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'where_we_work_content' );
        $jsonContent = json_encode( $content, JSON_UNESCAPED_UNICODE );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '', 'content' => $jsonContent ];
        if ( $exists ) {
            updateRow( 'where_we_work_content', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'where_we_work_content', $updateData );
        }
    }

    function saveContributeImpact( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'contribute_impact' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'contribute_impact', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'contribute_impact', $updateData );
        }
        $imp = getRow( 'contribute_impact' );
        if ( $imp ) {
            $pdo->exec( "DELETE FROM contribute_impact_items WHERE impact_id = {$imp['id']}" );
            $items = $content[ 'items' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'contribute_impact_items', [ 'impact_id' => $imp[ 'id' ], 'icon' => $item[ 'icon' ] ?? '', 'amount' => $item[ 'amount' ] ?? '', 'description' => $item[ 'description' ] ?? '', 'sort_order' => $i ] );
            }
        }
    }

    function saveContributeTransparency( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'contribute_transparency' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'section_subtitle' => $data[ 'section_subtitle' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'contribute_transparency', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'contribute_transparency', $updateData );
        }
        $trans = getRow( 'contribute_transparency' );
        if ( $trans ) {
            $pdo->exec( "DELETE FROM contribute_transparency_items WHERE transparency_id = {$trans['id']}" );
            $items = $content[ 'items' ] ?? [];
            foreach ( $items as $i => $item ) {
                insertRow( 'contribute_transparency_items', [ 'transparency_id' => $trans[ 'id' ], 'icon' => $item[ 'icon' ] ?? '', 'title' => $item[ 'title' ] ?? '', 'description' => $item[ 'description' ] ?? '', 'sort_order' => $i ] );
            }
        }
    }

    function saveContributeWhy( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'contribute_why' );
        $updateData = [ 'section_title' => $data[ 'section_title' ] ?? '', 'description' => $content[ 'description' ] ?? '' ];
        if ( $exists ) {
            updateRow( 'contribute_why', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'contribute_why', $updateData );
        }
        $why = getRow( 'contribute_why' );
        if ( $why ) {
            $pdo->exec( "DELETE FROM contribute_why_stats WHERE why_id = {$why['id']}" );
            $stats = $content[ 'stats' ] ?? [];
            foreach ( $stats as $i => $stat ) {
                insertRow( 'contribute_why_stats', [ 'why_id' => $why[ 'id' ], 'stat_label' => $stat[ 'label' ] ?? '', 'stat_value' => $stat[ 'value' ] ?? '', 'sort_order' => $i ] );
            }
        }
    }

    function saveGetInvolvedVolunteer( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'get_involved_volunteer' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
            'requirements' => json_encode( $content[ 'requirements' ] ?? [], JSON_UNESCAPED_UNICODE ),
            'stat1_value' => $content[ 'stat1_value' ] ?? '',
            'stat1_label' => $content[ 'stat1_label' ] ?? '',
            'stat2_value' => $content[ 'stat2_value' ] ?? '',
            'stat2_label' => $content[ 'stat2_label' ] ?? '',
            'stat3_value' => $content[ 'stat3_value' ] ?? '',
            'stat3_label' => $content[ 'stat3_label' ] ?? '',
        ];
        if ( $exists ) {
            updateRow( 'get_involved_volunteer', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'get_involved_volunteer', $updateData );
        }
    }

    function saveGetInvolvedPartner( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'get_involved_partner' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
            'partner_types' => json_encode( $content[ 'partner_types' ] ?? [], JSON_UNESCAPED_UNICODE ),
        ];
        if ( $exists ) {
            updateRow( 'get_involved_partner', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'get_involved_partner', $updateData );
        }
    }

    function saveGetInvolvedMembership( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $exists = getRow( 'get_involved_membership' );
        $updateData = [
            'section_title' => $data[ 'section_title' ] ?? '',
            'section_subtitle' => $data[ 'section_subtitle' ] ?? '',
            'description' => $content[ 'description' ] ?? '',
            'benefits' => json_encode( $content[ 'benefits' ] ?? [], JSON_UNESCAPED_UNICODE ),
            'membership_types' => json_encode( $content[ 'membership_types' ] ?? [], JSON_UNESCAPED_UNICODE ),
        ];
        if ( $exists ) {
            updateRow( 'get_involved_membership', $updateData, [ 'id' => $exists[ 'id' ] ] );
        } else {
            insertRow( 'get_involved_membership', $updateData );
        }
    }

    function saveResourcesRegistration( $sectionKey, $data ) {
        global $pdo;
        $content = $data[ 'content' ] ?? [];
        $items = $content[ 'items' ] ?? [];
        $pdo->exec( 'DELETE FROM resources_registration' );
        foreach ( $items as $i => $item ) {
            insertRow( 'resources_registration', [
                'title' => $item[ 'title' ] ?? '',
                'description' => $item[ 'description' ] ?? '',
                'registration_number' => $item[ 'registration_number' ] ?? '',
                'issuing_authority' => $item[ 'issuing_authority' ] ?? '',
                'issue_date' => !empty( $item[ 'issue_date' ] ) ? $item[ 'issue_date' ] : null,
                'document_type' => $item[ 'document_type' ] ?? 'certificate',
                'icon' => $item[ 'icon' ] ?? 'fa-stamp',
                'badge' => $item[ 'badge' ] ?? '',
                'is_active' => 1,
                'sort_order' => $i,
            ] );
        }
    }

    // Dispatch: save each section to its dedicated table or homepage_sections
    $dedicatedSections = [
        'about_hero' => 'saveHeroSection',
        'about_mission' => 'saveMissionSection',
        'about_governance' => 'saveGovernanceSection',
        'about_transparency' => 'saveTransparencySection',
        'about_cta' => 'saveCtaSection',
        'chairman_message' => 'saveHeroSection',
        'cta_banner' => 'saveCtaSection',
        'geographic_presence' => 'saveGeographicPresence',
        'our_work_hero' => 'saveOurWorkHero',
        'our_work_editorial' => 'saveOurWorkEditorial',
        'our_work_process' => 'saveOurWorkProcess',
        'our_work_pillars' => 'saveOurWorkPillars',
        'our_work_featured_projects' => 'saveOurWorkFeatured',
        'our_work_cta' => 'saveOurWorkCta',
        'projects_hero' => 'saveProjectsHero',
        'projects_dashboard' => 'saveProjectsDashboard',
        'projects_cta' => 'saveProjectsCta',
        'causes_hero' => 'saveCausesHero',
        'causes_impact' => 'saveCausesImpact',
        'who_we_are_hero' => 'saveWhoWeAreHero',
        'who_we_are_impact' => 'saveWhoWeAreImpact',
        'why_we_exist' => 'saveWhyWeExist',
        'history_hero' => 'saveHistoryHero',
        'history_stats' => 'saveHistoryStats',
        'history_cta' => 'saveHistoryCta',
        'vision_mission_hero' => 'saveGenericHero',
        'vision_mission_content' => 'saveGenericContent',
        'org_structure_hero' => 'saveGenericHero',
        'org_structure_content' => 'saveGenericContent',
        'policies_hero' => 'saveGenericHero',
        'policies_content' => 'saveGenericContent',
        'annual_reports_hero' => 'saveGenericHero',
        'annual_reports_content' => 'saveGenericContent',
        'contact_hero' => 'saveGenericHero',
        'contact_content' => 'saveGenericContent',
        'impact_hero' => 'saveImpactHero',
        'impact_content' => 'saveImpactContent',
        'impact_cta' => 'saveImpactCta',
        'where_we_work_hero' => 'saveWhereWeWorkHero',
        'where_we_work_content' => 'saveWhereWeWorkContent',
        'news_hero' => 'saveNewsHero',
        'resources_hero' => 'saveGenericHero',
        'resources_registration' => 'saveResourcesRegistration',
        'get_involved_hero' => 'saveGetInvolvedHero',
        'get_involved_volunteer' => 'saveGetInvolvedVolunteer',
        'get_involved_partner' => 'saveGetInvolvedPartner',
        'get_involved_membership' => 'saveGetInvolvedMembership',
        'get_involved_cta' => 'saveCtaSection',
        'contribute_hero' => 'saveContributeHero',
        'contribute_impact' => 'saveContributeImpact',
        'contribute_transparency' => 'saveContributeTransparency',
        'contribute_why' => 'saveContributeWhy',
    ];

    foreach ( $sectionsData as $sectionKey => $data ) {
        if ( isset( $dedicatedSections[ $sectionKey ] ) ) {
            call_user_func( $dedicatedSections[ $sectionKey ], $sectionKey, $data );
        } else {
            // Fallback: save to homepage_sections
            $title = $data[ 'section_title' ] ?? null;
            $subtitle = $data[ 'section_subtitle' ] ?? null;
            $content = isset( $data[ 'content' ] ) ? json_encode( $data[ 'content' ], JSON_UNESCAPED_UNICODE ) : null;

            $exists = getRow( 'homepage_sections', [ 'section_key' => $sectionKey ] );
            if ( $exists ) {
                $updateData = [];
                if ( $title !== null ) $updateData[ 'section_title' ] = $title;
                if ( $subtitle !== null ) $updateData[ 'section_subtitle' ] = $subtitle;
                if ( $content !== null ) $updateData[ 'content' ] = $content;
                if ( !empty( $updateData ) ) {
                    updateRow( 'homepage_sections', $updateData, [ 'section_key' => $sectionKey ] );
                }
            } else {
                $insertData = [ 'section_key' => $sectionKey, 'is_active' => 1, 'sort_order' => 0 ];
                if ( $title !== null ) $insertData[ 'section_title' ] = $title;
                if ( $subtitle !== null ) $insertData[ 'section_subtitle' ] = $subtitle;
                if ( $content !== null ) $insertData[ 'content' ] = $content;
                insertRow( 'homepage_sections', $insertData );
            }
        }
    }

    setFlash( 'success', 'Sections saved successfully!' );
    redirect( 'pages.php?page=' . urlencode( $pageKey ) );
}

// Define pages and their sections
$pages = [
    'homepage' => [
        'label' => 'Homepage',
        'sections' => [
            'chairman_message' => 'Chairman\'s Message',
            'geographic_presence' => 'Geographic Presence',
            'cta_banner' => 'CTA Banner',
        ]
    ],
    'our-work' => [
        'label' => 'Our Work',
        'sections' => [
            'our_work_hero' => 'Hero Section',
            'our_work_editorial' => 'Editorial / Featured Work',
            'our_work_process' => 'Process Timeline',
            'our_work_pillars' => '4 Pillars of Impact',
            'our_work_featured_projects' => 'Featured Projects',
            'our_work_cta' => 'CTA Banner',
        ]
    ],
    'projects' => [
        'label' => 'Projects Listing',
        'sections' => [
            'projects_hero' => 'Hero Section',
            'projects_dashboard' => 'Project Dashboard',
            'projects_cta' => 'CTA Banner',
        ]
    ],
    'causes' => [
        'label' => 'Causes / Donate',
        'sections' => [
            'causes_hero' => 'Hero Section',
            'causes_impact' => 'Impact Stats',
        ]
    ],
    'project-detail' => [
        'label' => 'Project Detail',
        'sections' => [
            'project_detail_hero' => 'Hero Section',
        ]
    ],
    'about' => [
        'label' => 'About',
        'sections' => [
            'about_hero' => 'Hero Section',
            'about_mission' => 'Mission / Vision / Values',
            'about_governance' => 'Governance',
            'about_transparency' => 'Transparency & Trust',
            'about_cta' => 'CTA Banner',
        ]
    ],
    'who-we-are' => [
        'label' => 'Who We Are',
        'sections' => [
            'who_we_are_hero' => 'Hero Section',
            'who_we_are_impact' => 'Areas of Impact',
            'why_we_exist' => 'Why We Exist',
        ]
    ],
    'history' => [
        'label' => 'History / Timeline',
        'sections' => [
            'history_hero' => 'Hero Section',
            'history_stats' => 'Key Stats',
            'history_cta' => 'CTA Banner',
        ]
    ],
    'team' => [
        'label' => 'Team',
        'sections' => [
            'team_hero' => 'Hero Section',
        ]
    ],
    'leadership' => [
        'label' => 'Leadership',
        'sections' => [
            'leadership_hero' => 'Hero Section',
        ]
    ],
    'vision-mission-values' => [
        'label' => 'Vision, Mission & Values',
        'sections' => [
            'vision_mission_hero' => 'Hero Section',
            'vision_mission_content' => 'Vision, Mission & Core Values',
        ]
    ],
    'organizational-structure' => [
        'label' => 'Organizational Structure',
        'sections' => [
            'org_structure_hero' => 'Hero Section',
            'org_structure_content' => 'Organization Chart',
        ]
    ],
    'policies-compliance' => [
        'label' => 'Policies & Compliance',
        'sections' => [
            'policies_hero' => 'Hero Section',
            'policies_content' => 'Policy Categories',
        ]
    ],
    'annual-reports' => [
        'label' => 'Annual Reports',
        'sections' => [
            'annual_reports_hero' => 'Hero Section',
            'annual_reports_content' => 'Reports Archive',
        ]
    ],
    'news' => [
        'label' => 'News & Media',
        'sections' => [
            'news_hero' => 'Hero Section',
        ]
    ],
    'get-involved' => [
        'label' => 'Get Involved',
        'sections' => [
            'get_involved_hero' => 'Hero Section',
            'get_involved_volunteer' => 'Volunteer Registration',
            'get_involved_partner' => 'Partner Section',
            'get_involved_membership' => 'Membership Section',
            'get_involved_cta' => 'CTA Banner',
        ]
    ],
    'contribute' => [
        'label' => 'Let’s Work Together',
        'sections' => [
            'contribute_hero' => 'Hero Section',
            'contribute_impact' => 'Donation Impact',
            'contribute_transparency' => 'Transparency Strip',
            'contribute_why' => 'Why Partner With Us',
        ]
    ],
    'contact' => [
        'label' => 'Contact Us',
        'sections' => [
            'contact_hero' => 'Hero Section',
            'contact_content' => 'Contact Information',
        ]
    ],
    'impact' => [
        'label' => 'Impact',
        'sections' => [
            'impact_hero' => 'Hero Section',
            'impact_content' => 'Impact Dashboard',
            'impact_cta' => 'CTA Banner',
        ]
    ],
    'where-we-work' => [
        'label' => 'Where We Work',
        'sections' => [
            'where_we_work_hero' => 'Hero Section',
            'where_we_work_content' => 'Geographic Coverage',
        ]
    ],
];

$pageKey = $_GET['page'] ?? 'homepage';
if (!isset($pages[$pageKey])) $pageKey = 'homepage';

// Load sections for current page
$sectionKeys = array_keys($pages[$pageKey]['sections']);
$sections = [];

// Load from dedicated tables for migrated sections
function loadDedicatedSection($sectionKey, $defaultTitle) {
    switch ($sectionKey) {
        case 'about_hero':
            $row = getRow('about_hero');
            if ($row) return [
                'section_key' => $sectionKey,
                // Read the stored heading back instead of hardcoding a label.
                // The hardcoded 'Hero Section' here was what made an edit to
                // the About page heading look like it had reverted: the value
                // was never written, and the form reloaded with this placeholder.
                'section_title' => $row['section_title'] ?? 'About Welfare Relief Committee',
                'section_subtitle' => $row['section_subtitle'] ?? ($row['subtitle'] ?? ''),
                'content' => $row,
                'is_active' => 1,
            ];
            break;
        case 'about_mission':
            $row = getRow('about_mission');
            if ($row) {
                $items = getRows('about_mission_items', ['mission_id' => $row['id']], 'sort_order ASC');
                return [
                    'section_key' => $sectionKey,
                    'section_title' => $row['section_title'] ?? '',
                    'section_subtitle' => $row['section_subtitle'] ?? '',
                    'content' => ['items' => $items],
                    'is_active' => 1,
                ];
            }
            break;
        case 'about_governance':
            $row = getRow('about_governance');
            if ($row) {
                $items = getRows('about_governance_items', ['governance_id' => $row['id']], 'sort_order ASC');
                return [
                    'section_key' => $sectionKey,
                    'section_title' => $row['section_title'] ?? '',
                    'section_subtitle' => $row['section_subtitle'] ?? '',
                    'content' => ['items' => $items],
                    'is_active' => 1,
                ];
            }
            break;
        case 'about_transparency':
            $row = getRow('about_transparency');
            if ($row) {
                $statsRows = getRows('about_transparency_stats', ['transparency_id' => $row['id']], 'sort_order ASC');
                // Map column names to what JS expects (value/label)
                $stats = array_map(function($s) {
                    return ['value' => $s['stat_value'], 'label' => $s['stat_label'], 'icon' => '', 'color' => ''];
                }, $statsRows);
                return [
                    'section_key' => $sectionKey,
                    'section_title' => $row['section_title'] ?? '',
                    'section_subtitle' => $row['section_subtitle'] ?? '',
                    'content' => [
                        'badge' => $row['badge'] ?? '',
                        'button1_text' => $row['button1_text'] ?? '',
                        'button1_link' => $row['button1_link'] ?? '',
                        'button2_text' => $row['button2_text'] ?? '',
                        'button2_link' => $row['button2_link'] ?? '',
                        'stats' => $stats,
                    ],
                    'is_active' => 1,
                ];
            }
            break;
        case 'about_cta':
            $row = getRow('about_cta');
            if ($row) return [
                'section_key' => $sectionKey,
                'section_title' => $row['section_title'] ?? '',
                'section_subtitle' => '',
                'content' => $row,
                'is_active' => 1,
            ];
            break;
        case 'chairman_message':
            $row = getRow('chairman_message');
            if ($row) {
                $sectionMeta = getRow( 'homepage_sections', [ 'section_key' => $sectionKey ] );
                return [
                    'section_key' => $sectionKey,
                    'section_title' => $sectionMeta[ 'section_title' ] ?? 'Chairman\'s Message',
                    'section_subtitle' => $sectionMeta[ 'section_subtitle' ] ?? '',
                    'content' => $row,
                    'is_active' => 1,
                ];
            }
        break;
        case 'geographic_presence':
        $row = getRow( 'geographic_presence' );
        if ( $row ) {
            $statsRows = getRows( 'geographic_presence_stats', [ 'geo_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $stats = array_map( function( $s ) {
                return [ 'value' => $s[ 'stat_value' ], 'label' => $s[ 'stat_label' ], 'icon' => $s[ 'stat_icon' ], 'color' => $s[ 'stat_color' ] ];
            }
            , $statsRows );
            return [
                'section_key' => $sectionKey,
                'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'stats' => $stats ],
                'is_active' => 1,
            ];
        }
        break;
        case 'cta_banner':
        $row = getRow( 'cta_banner' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => '',
            'content' => $row,
            'is_active' => 1,
        ];
        break;
        case 'our_work_hero':
        $row = getRow( 'our_work_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
            'content' => $row,
            'is_active' => 1,
        ];
        break;
        case 'our_work_editorial':
        $row = getRow( 'our_work_editorial' );
        if ( $row ) {
            $features = getRows( 'our_work_editorial_features', [ 'editorial_id' => $row[ 'id' ] ], 'sort_order ASC' );
            return [
                'section_key' => $sectionKey,
                'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'image' => $row[ 'image' ], 'label' => $row[ 'label' ], 'description' => $row[ 'description' ], 'features' => $features ],
                'is_active' => 1,
            ];
        }
        break;
        case 'our_work_process':
        $row = getRow( 'our_work_process' );
        if ( $row ) {
            $steps = getRows( 'our_work_process_steps', [ 'process_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $stepsMapped = array_map( function( $s ) {
                return [ 'num' => $s[ 'step_num' ], 'title' => $s[ 'title' ], 'description' => $s[ 'description' ] ];
            }
            , $steps );
            return [
                'section_key' => $sectionKey,
                'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'subtitle' => $row[ 'subtitle' ], 'steps' => $stepsMapped ],
                'is_active' => 1,
            ];
        }
        break;
        case 'our_work_pillars':
        $row = getRow( 'our_work_pillars' );
        if ( $row ) {
            $pillars = getRows( 'our_work_pillars_items', [ 'pillars_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $pillarsMapped = array_map( function( $p ) {
                return [ 'num' => $p[ 'pillar_num' ], 'title' => $p[ 'title' ], 'description' => $p[ 'description' ] ];
            }
            , $pillars );
            return [
                'section_key' => $sectionKey,
                'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'subtitle' => $row[ 'subtitle' ], 'pillars' => $pillarsMapped ],
                'is_active' => 1,
            ];
        }
        break;
        case 'our_work_featured_projects':
        $row = getRow( 'our_work_featured_projects' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => '',
            'content' => [ 'description' => $row[ 'description' ] ],
            'is_active' => 1,
        ];
        break;
        case 'our_work_cta':
        $row = getRow( 'our_work_cta' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => '',
            'content' => $row,
            'is_active' => 1,
        ];
        break;
        case 'projects_hero':
        $row = getRow( 'projects_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
            'content' => $row,
            'is_active' => 1,
        ];
        break;
        case 'projects_dashboard':
        $row = getRow( 'projects_dashboard' );
        if ( $row ) {
            $metricsRows = getRows( 'projects_dashboard_metrics', [ 'dashboard_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $metrics = array_map( function( $m ) {
                return [ 'icon' => $m[ 'metric_icon' ], 'color' => $m[ 'metric_color' ], 'label' => $m[ 'metric_label' ], 'value' => $m[ 'metric_value' ] ];
            }
            , $metricsRows );
            $sectorsRows = getRows( 'projects_dashboard_sectors', [ 'dashboard_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $sectors = array_map( function( $s ) {
                return [ 'name' => $s[ 'sector_name' ], 'color' => $s[ 'sector_color' ], 'count' => $s[ 'sector_count' ], 'percent' => $s[ 'sector_percent' ], 'gradient' => $s[ 'sector_gradient' ] ];
            }
            , $sectorsRows );
            return [
                'section_key' => $sectionKey,
                'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'subtitle' ] ?? '',
                'content' => [ 'subtitle' => $row[ 'subtitle' ], 'metrics' => $metrics, 'sectors' => $sectors ],
                'is_active' => 1,
            ];
        }
        break;
        case 'projects_cta':
        $row = getRow( 'projects_cta' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => '',
            'content' => $row,
            'is_active' => 1,
        ];
        break;
        case 'causes_hero':
        $row = getRow( 'causes_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey,
            'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
            'content' => $row,
            'is_active' => 1,
        ];
        break;
        case 'causes_impact':
        $row = getRow( 'causes_impact' );
        if ( $row ) {
            $statsRows = getRows( 'causes_impact_stats', [ 'impact_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $stats = array_map( function( $s ) {
                return [ 'icon' => $s[ 'stat_icon' ], 'label' => $s[ 'stat_label' ], 'value' => $s[ 'stat_value' ] ];
            }
            , $statsRows );
            return [
                'section_key' => $sectionKey,
                'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => '',
                'content' => [ 'stats' => $stats ],
                'is_active' => 1,
            ];
        }
        break;
        case 'who_we_are_hero':
        $row = getRow( 'who_we_are_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'who_we_are_impact':
        $row = getRow( 'who_we_are_impact' );
        if ( $row ) {
            $items = getRows( 'who_we_are_impact_items', [ 'impact_id' => $row[ 'id' ] ], 'sort_order ASC' );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'items' => $items ], 'is_active' => 1,
            ];
        }
        break;
        case 'why_we_exist':
        $row = getRow( 'why_we_exist' );
        if ( $row ) {
            $items = getRows( 'why_we_exist_items', [ 'why_id' => $row[ 'id' ] ], 'sort_order ASC' );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'items' => $items ], 'is_active' => 1,
            ];
        }
        break;
        case 'history_hero':
        $row = getRow( 'history_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'history_stats':
        $row = getRow( 'history_stats' );
        if ( $row ) {
            $statsRows = getRows( 'history_stats_items', [ 'stats_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $stats = array_map( function( $s ) {
                return [ 'icon' => $s[ 'stat_icon' ], 'color' => $s[ 'stat_color' ], 'label' => $s[ 'stat_label' ], 'value' => $s[ 'stat_value' ] ];
            }
            , $statsRows );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => [ 'stats' => $stats ], 'is_active' => 1,
            ];
        }
        break;
        case 'history_cta':
        $row = getRow( 'history_cta' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
            'content' => [ 'badge' => $row[ 'badge' ], 'section_title' => $row[ 'section_title' ], 'section_subtitle' => $row[ 'section_subtitle' ], 'button1_text' => $row[ 'button1_text' ], 'button1_link' => $row[ 'button1_link' ] ],
            'is_active' => 1,
        ];
        break;
        case 'vision_mission_hero': case 'org_structure_hero': case 'policies_hero': case 'annual_reports_hero':
        $row = getRow( $sectionKey );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'vision_mission_content': case 'org_structure_content': case 'policies_content': case 'annual_reports_content':
        $row = getRow( $sectionKey );
        if ( $row ) {
            $decoded = json_decode( $row[ 'content' ] ?? '{}', true );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => is_array( $decoded ) ? $decoded : [], 'is_active' => 1,
            ];
        }
        break;
        case 'contact_hero': case 'impact_hero': case 'where_we_work_hero':
        $row = getRow( $sectionKey );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'contact_content': case 'impact_content': case 'where_we_work_content':
        $row = getRow( $sectionKey );
        if ( $row ) {
            $decoded = json_decode( $row[ 'content' ] ?? '{}', true );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => is_array( $decoded ) ? $decoded : [], 'is_active' => 1,
            ];
        }
        break;
        case 'news_hero':
        $row = getRow( 'news_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'resources_hero':
        $row = getRow( 'resources_hero' );
        if ( $row ) {
            $decoded = json_decode( $row[ 'content' ] ?? '{}', true );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => is_array( $decoded ) ? $decoded : [], 'is_active' => 1,
            ];
        }
        break;
        case 'resources_registration':
        $items = getRows( 'resources_registration', [], 'sort_order ASC' );
        return [
            'section_key' => $sectionKey, 'section_title' => 'Registration & Certifications',
            'section_subtitle' => '', 'content' => [ 'items' => $items ], 'is_active' => 1,
        ];
        break;
        case 'get_involved_hero':
        $row = getRow( 'get_involved_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'get_involved_volunteer':
        $row = getRow( 'get_involved_volunteer' );
        if ( $row ) {
            $requirements = json_decode( $row[ 'requirements' ] ?? '[]', true );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [
                    'description' => $row[ 'description' ] ?? '',
                    'requirements' => $requirements,
                    'stat1_value' => $row[ 'stat1_value' ] ?? '', 'stat1_label' => $row[ 'stat1_label' ] ?? '',
                    'stat2_value' => $row[ 'stat2_value' ] ?? '', 'stat2_label' => $row[ 'stat2_label' ] ?? '',
                    'stat3_value' => $row[ 'stat3_value' ] ?? '', 'stat3_label' => $row[ 'stat3_label' ] ?? '',
                ], 'is_active' => 1,
            ];
        }
        break;
        case 'get_involved_partner':
        $row = getRow( 'get_involved_partner' );
        if ( $row ) {
            $partnerTypes = json_decode( $row[ 'partner_types' ] ?? '[]', true );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [
                    'description' => $row[ 'description' ] ?? '',
                    'partner_types' => $partnerTypes,
                ], 'is_active' => 1,
            ];
        }
        break;
        case 'get_involved_membership':
        $row = getRow( 'get_involved_membership' );
        if ( $row ) {
            $benefits = json_decode( $row[ 'benefits' ] ?? '[]', true );
            $membershipTypes = json_decode( $row[ 'membership_types' ] ?? '[]', true );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [
                    'description' => $row[ 'description' ] ?? '',
                    'benefits' => $benefits,
                    'membership_types' => $membershipTypes,
                ], 'is_active' => 1,
            ];
        }
        break;
        case 'get_involved_cta':
        $row = getRow( 'get_involved_cta' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'contribute_hero':
        $row = getRow( 'contribute_hero' );
        if ( $row ) return [
            'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '', 'content' => $row, 'is_active' => 1,
        ];
        break;
        case 'contribute_impact':
        $row = getRow( 'contribute_impact' );
        if ( $row ) {
            $items = getRows( 'contribute_impact_items', [ 'impact_id' => $row[ 'id' ] ], 'sort_order ASC' );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'items' => $items ], 'is_active' => 1,
            ];
        }
        break;
        case 'contribute_transparency':
        $row = getRow( 'contribute_transparency' );
        if ( $row ) {
            $items = getRows( 'contribute_transparency_items', [ 'transparency_id' => $row[ 'id' ] ], 'sort_order ASC' );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
                'content' => [ 'items' => $items ], 'is_active' => 1,
            ];
        }
        break;
        case 'contribute_why':
        $row = getRow( 'contribute_why' );
        if ( $row ) {
            $statsRows = getRows( 'contribute_why_stats', [ 'why_id' => $row[ 'id' ] ], 'sort_order ASC' );
            $stats = array_map( function( $s ) {
                return [ 'label' => $s[ 'stat_label' ], 'value' => $s[ 'stat_value' ] ];
            }
            , $statsRows );
            return [
                'section_key' => $sectionKey, 'section_title' => $row[ 'section_title' ] ?? '',
                'section_subtitle' => '',
                'content' => [ 'stats' => $stats, 'description' => $row[ 'description' ] ], 'is_active' => 1,
            ];
        }
        break;
    }
    return null;
}

foreach ( $sectionKeys as $key ) {
    $dedicated = loadDedicatedSection( $key, $pages[ $pageKey ][ 'sections' ][ $key ] );
    if ( $dedicated ) {
        $sections[ $key ] = $dedicated;
    } else {
        $row = getRow( 'homepage_sections', [ 'section_key' => $key ] );
        $content = [];
        if ( $row && $row[ 'content' ] ) {
            $decoded = json_decode( $row[ 'content' ], true );
            if ( is_array( $decoded ) ) $content = $decoded;
        }
        $sections[ $key ] = [
            'section_key' => $key,
            'section_title' => $row[ 'section_title' ] ?? $pages[ $pageKey ][ 'sections' ][ $key ],
            'section_subtitle' => $row[ 'section_subtitle' ] ?? '',
            'content' => $content,
            'is_active' => $row[ 'is_active' ] ?? 1,
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
.section-card {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:24px;
    margin-bottom:20px}
    .section-card h3 {
        font-size:14px;
        font-weight:700;
        color:#0e1f27;
        margin-bottom:16px;
        display:flex;
        align-items:center;
        gap:8px}
        .section-card h3 .dot {
            width:8px;
            height:8px;
            border-radius:50%;
            background:#ca0000;
            flex-shrink:0}
            .field-group {
                margin-bottom:14px}
                .field-group label {
                    display:block;
                    font-size:12px;
                    font-weight:600;
                    color:#64748b;
                    margin-bottom:4px;
                    text-transform:uppercase;
                    letter-spacing:.5px}
                    .field-group input, .field-group textarea, .field-group select {
                        width:100%;
                        padding:9px 14px;
                        border:1px solid #e2e8f0;
                        border-radius:10px;
                        font-size:13px;
                        outline:none;
                        transition:all .15s}
                        .field-group input:focus, .field-group textarea:focus {
                            border-color:#ca0000;
                            box-shadow:0 0 0 3px rgba( 202, 0, 0, .08 )}
                            .field-group textarea {
                                resize:vertical;
                                min-height:60px}
                                .field-row {
                                    display:grid;
                                    grid-template-columns:1fr 1fr;
                                    gap:12px}
                                    .field-row-3 {
                                        display:grid;
                                        grid-template-columns:1fr 1fr 1fr;
                                        gap:12px}
                                        .field-row-4 {
                                            display:grid;
                                            grid-template-columns:1fr 1fr 1fr 1fr;
                                            gap:12px}
                                            .dynamic-item {
                                                background:#f8fafc;
                                                border:1px solid #e2e8f0;
                                                border-radius:12px;
                                                padding:12px;
                                                margin-bottom:8px;
                                                position:relative}
                                                .dynamic-item .remove-btn {
                                                    position:absolute;
                                                    top:8px;
                                                    right:8px;
                                                    width:24px;
                                                    height:24px;
                                                    border-radius:50%;
                                                    background:#fee2e2;
                                                    color:#991b1b;
                                                    border:none;
                                                    cursor:pointer;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    font-size:11px}
                                                    .dynamic-item .remove-btn:hover {
                                                        background:#fecaca}
                                                        .add-btn {
                                                            display:inline-flex;
                                                            align-items:center;
                                                            gap:4px;
                                                            padding:6px 14px;
                                                            border-radius:8px;
                                                            background:#f1f5f9;
                                                            color:#475569;
                                                            font-size:12px;
                                                            font-weight:600;
                                                            border:1px dashed #cbd5e1;
                                                            cursor:pointer;
                                                            transition:all .15s}
                                                            .add-btn:hover {
                                                                background:#e2e8f0;
                                                                border-color:#94a3b8}
                                                                .img-preview {
                                                                    width:80px;
                                                                    height:60px;
                                                                    object-fit:cover;
                                                                    border-radius:8px;
                                                                    border:1px solid #e2e8f0;
                                                                    margin-top:6px}
                                                                    </style>

                                                                    <div class = 'mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4'>
                                                                    <div>
                                                                    <div class = 'flex items-center gap-2 text-xs text-slate-400 mb-1'>
                                                                    <a href = 'pages.php' class = 'hover:text-brandRed transition-colors'>Pages</a>
                                                                    <i class = 'fa-solid fa-chevron-right text-[8px]'></i>
                                                                    <span class = 'text-slate-700 font-medium'><?= $pages[ $pageKey ][ 'label' ] ?></span>
                                                                    </div>
                                                                    <h1 class = 'text-2xl font-bold text-slate-900 font-heading'><?= $pages[ $pageKey ][ 'label' ] ?></h1>
                                                                    <p class = 'text-slate-500 text-sm mt-1'><?= count( $pages[ $pageKey ][ 'sections' ] ) ?> sections on this page</p>
                                                                    </div>
                                                                    <div class = 'flex items-center gap-2'>
                                                                    <a href = '../index.php' target = '_blank' class = 'inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-xs font-semibold hover:bg-slate-200 transition-colors'>
                                                                    <i class = 'fa-solid fa-external-link text-[10px]'></i> View Live
                                                                    </a>
                                                                    </div>
                                                                    </div>

                                                                    <?php if ( $flash = getFlash() ): ?>
                                                                    <div class = "mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash[ 'message' ] ?></div>
                                                                    <?php endif;
                                                                    ?>

                                                                    <!-- Section Forms -->
                                                                    <div id = 'sectionsContainer'>
                                                                    <form id = 'sectionsForm' method = 'POST'>
                                                                    <input type = 'hidden' name = 'csrf_token' value = '<?= generateCSRF() ?>'>
                                                                    <input type = 'hidden' name = 'save_sections' value = '1'>
                                                                    <input type = 'hidden' name = 'page_key' value = "<?= htmlspecialchars($pageKey) ?>">
                                                                    <input type = 'hidden' name = 'sections_json' id = 'sectionsJson'>

                                                                    <?php foreach ( $sections as $sKey => $sData ): ?>
                                                                    <div class = 'section-card' data-section = "<?= $sKey ?>">
                                                                    <h3><span class = 'dot'></span> <?= htmlspecialchars( $pages[ $pageKey ][ 'sections' ][ $sKey ] ) ?></h3>

                                                                    <?php
                                                                    // Render form based on section type
                                                                    $content = $sData[ 'content' ];

                                                                    // ===  == HERO SECTIONS ===  ==
                                                                    if ( preg_match( '/_hero$/', $sKey ) || $sKey === 'chairman_message' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title ( page heading )</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'About Welfare Relief Committee'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <input type = 'text' data-field = 'section_subtitle' value = "<?= htmlspecialchars($sData['section_subtitle'] ?? '') ?>" placeholder = 'Section subtitle text'>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Hero Image</label>
                                                                    <?php
                                                                    $heroImgId = 'hero_img_' . preg_replace( '/[^a-z0-9]/', '_', $sKey );
                                                                    ?>
                                                                    <input type = 'text' data-field = 'content.hero_image' id = "<?= $heroImgId ?>"
                                                                    value = "<?= htmlspecialchars($content['hero_image'] ?? $content['image_path'] ?? '') ?>"
                                                                    placeholder = 'assets/images/hero/about-hero.jpg'

                                                                    class = 'upload-path-input'>
                                                                    <div class = 'upload-widget' data-type = 'image' data-target = "<?= $heroImgId ?>" data-base-url = "<?= htmlspecialchars(siteBaseUrl(), ENT_QUOTES, 'UTF-8') ?>" style = 'margin-top:8px'>
                                                                    <div class = 'upload-zone'>
                                                                    <input type = 'file' accept = 'image/*'>
                                                                    <div class = 'upload-placeholder'>
                                                                    <i class = 'fa-solid fa-cloud-arrow-up'></i>
                                                                    <p>Click or drag to upload hero image</p>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle</label>
                                                                    <input type = 'text' data-field = 'content.subtitle' value = "<?= htmlspecialchars($content['subtitle'] ?? $content['hero_alt'] ?? '') ?>" placeholder = 'Section subtitle'>
                                                                    </div>
                                                                    </div>
                                                                    <?php if ( $sKey === 'impact_hero' ): ?>
                                                                    <!-- Impact Hero: Heading ( H1 ) -->
                                                                    <div class = 'field-group'>
                                                                    <label>Heading ( H1 ) — use &lt;
                                                                    br&gt;
                                                                    for line break</label>
                                                                    <input type = 'text' data-field = 'content.heading' value = "<?= htmlspecialchars($content['heading'] ?? '') ?>" placeholder = 'Measuring What Matters,<br>Changing What Counts.'>
                                                                    </div>
                                                                    <!-- Impact Hero: Description -->
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <!-- Impact Hero: CTA Buttons -->
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>CTA Primary ( button text )</label>
                                                                    <input type = 'text' data-field = 'content.cta_primary' value = "<?= htmlspecialchars($content['cta_primary'] ?? '') ?>" placeholder = 'View Impact Dashboard'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>CTA Secondary ( button text )</label>
                                                                    <input type = 'text' data-field = 'content.cta_secondary' value = "<?= htmlspecialchars($content['cta_secondary'] ?? '') ?>" placeholder = 'SDG Contributions'>
                                                                    </div>
                                                                    </div>
                                                                    <?php elseif ( $sKey === 'where_we_work_hero' ): ?>
                                                                    <!-- Where We Work Hero: Subtitle ( description paragraph ) -->
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle ( hero description paragraph )</label>
                                                                    <textarea data-field = 'content.subtitle' rows = '3'><?= htmlspecialchars( $content[ 'subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <!-- Where We Work Hero: CTA Buttons -->
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>CTA Primary ( button text )</label>
                                                                    <input type = 'text' data-field = 'content.cta_primary' value = "<?= htmlspecialchars($content['cta_primary'] ?? '') ?>" placeholder = 'Explore Our Map'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>CTA Secondary ( button text )</label>
                                                                    <input type = 'text' data-field = 'content.cta_secondary' value = "<?= htmlspecialchars($content['cta_secondary'] ?? '') ?>" placeholder = 'District Coverage'>
                                                                    </div>
                                                                    </div>
                                                                    <?php elseif ( $sKey === 'chairman_message' ): ?>
                                                                    <!-- Hero Image with Upload -->
                                                                    <div class = 'form-group'>
                                                                    <label style = 'display:block;font-weight:600;color:#334155;font-size:0.875rem;margin-bottom:0.375rem'>Hero Image</label>
                                                                    <input type = 'text' data-field = 'content.image_path' id = 'chairman_image_path'
                                                                    value = "<?= htmlspecialchars($content['image_path'] ?? $content['hero_image'] ?? '') ?>"

                                                                    class = 'w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all upload-path-input'
                                                                    placeholder = 'Path or upload an image'>
                                                                    <div class = 'upload-widget' data-type = 'image' data-target = 'chairman_image_path' data-base-url = "<?= htmlspecialchars(siteBaseUrl(), ENT_QUOTES, 'UTF-8') ?>">
                                                                    <div class = 'upload-zone'>
                                                                    <input type = 'file' accept = 'image/*'>
                                                                    <div class = 'upload-placeholder'>
                                                                    <i class = 'fa-solid fa-cloud-arrow-up'></i>
                                                                    <p>Click or drag to upload image</p>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    <!-- Overlay Title -->
                                                                    <div class = 'field-group'>
                                                                    <label>Overlay Title ( on image )</label>
                                                                    <input type = 'text' data-field = 'content.overlay_title' value = "<?= htmlspecialchars($content['overlay_title'] ?? '') ?>" placeholder = 'Leading with compassion, transparency, and bold action.'>
                                                                    </div>
                                                                    <!-- Note Label -->
                                                                    <div class = 'field-group'>
                                                                    <label>Note Label ( eyebrow text )</label>
                                                                    <input type = 'text' data-field = 'content.note_label' value = "<?= htmlspecialchars($content['note_label'] ?? '') ?>" placeholder = "Chairman's Note">
                                                                    </div>
                                                                    <!-- Paragraph 1 -->
                                                                    <div class = 'field-group'>
                                                                    <label>Paragraph 1</label>
                                                                    <textarea data-field = 'content.paragraph1' rows = '3' placeholder = "First paragraph of the chairman's message..."><?= htmlspecialchars( $content[ 'paragraph1' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <!-- Paragraph 2 -->
                                                                    <div class = 'field-group'>
                                                                    <label>Paragraph 2</label>
                                                                    <textarea data-field = 'content.paragraph2' rows = '3' placeholder = "Second paragraph of the chairman's message..."><?= htmlspecialchars( $content[ 'paragraph2' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <!-- Person Name + Title -->
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Person Name</label>
                                                                    <input type = 'text' data-field = 'content.person_name' value = "<?= htmlspecialchars($content['person_name'] ?? '') ?>" placeholder = 'Chairman name'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Person Title</label>
                                                                    <input type = 'text' data-field = 'content.person_title' value = "<?= htmlspecialchars($content['person_title'] ?? '') ?>" placeholder = 'Chairman title / position'>
                                                                    </div>
                                                                    </div>
                                                                    <!-- Vision Label + Text -->
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Vision Label</label>
                                                                    <input type = 'text' data-field = 'content.vision_label' value = "<?= htmlspecialchars($content['vision_label'] ?? '') ?>" placeholder = 'Vision'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Vision Text</label>
                                                                    <input type = 'text' data-field = 'content.vision_text' value = "<?= htmlspecialchars($content['vision_text'] ?? '') ?>" placeholder = 'Short vision statement'>
                                                                    </div>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <div class = 'field-row-4'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Text</label>
                                                                    <input type = 'text' data-field = 'content.button1_text' value = "<?= htmlspecialchars($content['button1_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Link</label>
                                                                    <input type = 'text' data-field = 'content.button1_link' value = "<?= htmlspecialchars($content['button1_link'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Text</label>
                                                                    <input type = 'text' data-field = 'content.button2_text' value = "<?= htmlspecialchars($content['button2_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Link</label>
                                                                    <input type = 'text' data-field = 'content.button2_link' value = "<?= htmlspecialchars($content['button2_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <?php endif;
                                                                    ?>

                                                                    <?php
                                                                    // ===  == CTA SECTIONS ===  ==
                                                                    elseif ( preg_match( '/_cta$/', $sKey ) || $sKey === 'cta_banner' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'CTA heading text'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '2'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Text</label>
                                                                    <input type = 'text' data-field = 'content.button1_text' value = "<?= htmlspecialchars($content['button1_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Link</label>
                                                                    <input type = 'text' data-field = 'content.button1_link' value = "<?= htmlspecialchars($content['button1_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Text</label>
                                                                    <input type = 'text' data-field = 'content.button2_text' value = "<?= htmlspecialchars($content['button2_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Link</label>
                                                                    <input type = 'text' data-field = 'content.button2_link' value = "<?= htmlspecialchars($content['button2_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == EDITORIAL SECTION ===  ==
                                                                    elseif ( $sKey === 'our_work_editorial' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Image</label>
                                                                    <input type = 'text' data-field = 'content.image' value = "<?= htmlspecialchars($content['image'] ?? '') ?>" placeholder = 'assets/images/...'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Label</label>
                                                                    <input type = 'text' data-field = 'content.label' value = "<?= htmlspecialchars($content['label'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Features</label>
                                                                    <div id = "featuresList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addFeature_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Feature</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == PROCESS / STEPS SECTIONS ===  ==
                                                                    elseif ( $sKey === 'our_work_process' || $sKey === 'program_implementation' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle</label>
                                                                    <input type = 'text' data-field = 'content.subtitle' value = "<?= htmlspecialchars($content['subtitle'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Steps</label>
                                                                    <div id = "stepsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addStep_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Step</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == PILLARS / ITEMS GRID SECTIONS ===  ==
                                                                    elseif ( in_array( $sKey, [ 'our_work_pillars', 'about_mission', 'about_governance', 'who_we_are_impact', 'why_we_exist', 'contribute_impact', 'contribute_transparency' ] ) ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'Section heading'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <input type = 'text' data-field = 'section_subtitle' value = "<?= htmlspecialchars($sData['section_subtitle'] ?? '') ?>" placeholder = 'Section subtitle'>
                                                                    </div>
                                                                    <?php if ( $sKey === 'our_work_pillars' ): ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle ( below heading )</label>
                                                                    <input type = 'text' data-field = 'content.subtitle' value = "<?= htmlspecialchars($content['subtitle'] ?? '') ?>">
                                                                    </div>
                                                                    <?php endif;
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Items</label>
                                                                    <div id = "itemsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addItem_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Item</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == IMPACT CONTENT ( complex: stats + M&E steps + SDGs + case studies ) ===  ==
                                                                    elseif ( $sKey === 'impact_content' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'Impact Dashboard'>
                                                                    </div>

                                                                    <!-- Impact Stats -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>Impact Metrics ( Counter Dashboard )</p>
                                                                    <div id = 'statsList_impact_content' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addStat_impact_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add Stat</button>
                                                                    </div>

                                                                    <!-- M&E Steps -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>Monitoring &amp;
                                                                    Evaluation Framework Steps</p>
                                                                    <div id = 'mneStepsList_impact_content' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addMneStep_impact_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add M&E Step</button>
                                                                    </div>

                                                                    <!-- SDGs -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>SDG Contributions</p>
                                                                    <div id = 'sdgsList_impact_content' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addSdg_impact_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add SDG</button>
                                                                    </div>

                                                                    <!-- Case Studies -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>Case Studies</p>
                                                                    <div id = 'caseStudiesList_impact_content' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addCaseStudy_impact_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add Case Study</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == WHERE WE WORK CONTENT ( complex: stats + provinces + quick links ) ===  ==
                                                                    elseif ( $sKey === 'where_we_work_content' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title ( map heading )</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'Pakistan Operations Map'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle ( map description )</label>
                                                                    <textarea data-field = 'section_subtitle' rows = '2'><?= htmlspecialchars( $sData[ 'section_subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>

                                                                    <!-- Stats Bar -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>Stats Overview Bar</p>
                                                                    <div id = 'statsList_where_we_work_content' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addStat_where_we_work_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add Stat</button>
                                                                    </div>

                                                                    <!-- Provinces ( with nested focus_areas + districts ) -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>Province Sections</p>
                                                                    <div id = 'provincesList_where_we_work_content' class = 'space-y-3'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addProvince_where_we_work_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add Province</button>
                                                                    </div>

                                                                    <!-- Quick Navigation Links -->
                                                                    <div style = 'margin-top:1rem;padding:1rem;background:#f8fafc;border-radius:0.75rem;border:1px solid #e2e8f0;'>
                                                                    <p style = 'font-size:0.75rem;font-weight:700;color:#ca0000;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;'>Quick Navigation Links ( sidebar )</p>
                                                                    <div id = 'quickLinksList_where_we_work_content' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addQuickLink_where_we_work_content()'><i class = 'fa-solid fa-plus text-xs'></i> Add Quick Link</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == STATS / DASHBOARD SECTIONS ===  ==
                                                                    elseif ( in_array( $sKey, [ 'projects_dashboard', 'causes_impact', 'history_stats', 'resources_hero', 'contribute_why' ] ) ):
                                                                    ?>
                                                                    <?php if ( $sKey === 'projects_dashboard' ): ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Sectors ( Progress Bars )</label>
                                                                    <div id = "sectorsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addSector_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Sector</button>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Metrics</label>
                                                                    <div id = "metricsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addMetric_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Metric</button>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <div class = 'field-group'>
                                                                    <label><?= $sKey === 'contribute_why' ? 'Values / Propositions' : 'Stats' ?></label>
                                                                    <div id = "statsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addStat_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Stat</button>
                                                                    </div>
                                                                    <?php endif;
                                                                    ?>
                                                                    <?php if ( $sKey === 'contribute_why' ): ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <?php endif;
                                                                    ?>

                                                                    <?php
                                                                    // ===  == SIMPLE TEXT SECTIONS ===  ==
                                                                    elseif ( $sKey === 'our_work_featured_projects' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == ABOUT TRANSPARENCY ( MIXED ) ===  ==
                                                                    elseif ( $sKey === 'about_transparency' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'Transparency & Trust'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <textarea data-field = 'section_subtitle' rows = '2'><?= htmlspecialchars( $sData[ 'section_subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Badge</label>
                                                                    <input type = 'text' data-field = 'content.badge' value = "<?= htmlspecialchars($content['badge'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '2'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Stats</label>
                                                                    <div id = "statsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addStat_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Stat</button>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Text</label>
                                                                    <input type = 'text' data-field = 'content.button1_text' value = "<?= htmlspecialchars($content['button1_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Link</label>
                                                                    <input type = 'text' data-field = 'content.button1_link' value = "<?= htmlspecialchars($content['button1_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Text</label>
                                                                    <input type = 'text' data-field = 'content.button2_text' value = "<?= htmlspecialchars($content['button2_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Link</label>
                                                                    <input type = 'text' data-field = 'content.button2_link' value = "<?= htmlspecialchars($content['button2_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == GEOGRAPHIC PRESENCE ===  ==
                                                                    elseif ( $sKey === 'geographic_presence' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Stats</label>
                                                                    <div id = "statsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addStat_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Stat</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == HISTORY CTA ===  ==
                                                                    elseif ( $sKey === 'history_cta' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Badge</label>
                                                                    <input type = 'text' data-field = 'content.badge' value = "<?= htmlspecialchars($content['badge'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'content.section_title' value = "<?= htmlspecialchars($content['section_title'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle</label>
                                                                    <textarea data-field = 'content.section_subtitle' rows = '2'><?= htmlspecialchars( $content[ 'section_subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Text</label>
                                                                    <input type = 'text' data-field = 'content.button1_text' value = "<?= htmlspecialchars($content['button1_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Link</label>
                                                                    <input type = 'text' data-field = 'content.button1_link' value = "<?= htmlspecialchars($content['button1_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == GET INVOLVED HERO ===  ==
                                                                    elseif ( $sKey === 'get_involved_hero' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Hero Image</label>
                                                                    <?php $heroImgId = 'hero_img_' . preg_replace( '/[^a-z0-9]/', '_', $sKey );
                                                                    ?>
                                                                    <input type = 'text' data-field = 'content.hero_image' id = "<?= $heroImgId ?>" value = "<?= htmlspecialchars($content['hero_image'] ?? '') ?>" class = 'upload-path-input'>
                                                                    <div class = 'upload-widget' data-type = 'image' data-target = "<?= $heroImgId ?>" data-base-url = "<?= htmlspecialchars(siteBaseUrl(), ENT_QUOTES, 'UTF-8') ?>" style = 'margin-top:8px'>
                                                                    <div class = 'upload-zone'>
                                                                    <input type = 'file' accept = 'image/*'>
                                                                    <div class = 'upload-placeholder'>
                                                                    <i class = 'fa-solid fa-cloud-arrow-up'></i>
                                                                    <p>Click or drag to upload hero image</p>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Badge</label>
                                                                    <input type = 'text' data-field = 'content.badge' value = "<?= htmlspecialchars($content['badge'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle</label>
                                                                    <textarea data-field = 'content.subtitle' rows = '2'><?= htmlspecialchars( $content[ 'subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Text</label>
                                                                    <input type = 'text' data-field = 'content.button1_text' value = "<?= htmlspecialchars($content['button1_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 2 Text</label>
                                                                    <input type = 'text' data-field = 'content.button2_text' value = "<?= htmlspecialchars($content['button2_text'] ?? '') ?>">
                                                                    </div>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == GET INVOLVED VOLUNTEER ===  ==
                                                                    elseif ( $sKey === 'get_involved_volunteer' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <input type = 'text' data-field = 'section_subtitle' value = "<?= htmlspecialchars($sData['section_subtitle'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Requirements</label>
                                                                    <div id = 'requirementsList_get_involved_volunteer' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addRequirement_get_involved_volunteer()'><i class = 'fa-solid fa-plus text-xs'></i> Add Requirement</button>
                                                                    </div>
                                                                    <div class = 'field-row-3'>
                                                                    <div class = 'field-group'>
                                                                    <label>Stat 1 Value</label>
                                                                    <input type = 'text' data-field = 'content.stat1_value' value = "<?= htmlspecialchars($content['stat1_value'] ?? '') ?>" placeholder = '500+'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Stat 1 Label</label>
                                                                    <input type = 'text' data-field = 'content.stat1_label' value = "<?= htmlspecialchars($content['stat1_label'] ?? '') ?>" placeholder = 'Active Volunteers'>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-row-3'>
                                                                    <div class = 'field-group'>
                                                                    <label>Stat 2 Value</label>
                                                                    <input type = 'text' data-field = 'content.stat2_value' value = "<?= htmlspecialchars($content['stat2_value'] ?? '') ?>" placeholder = '30+'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Stat 2 Label</label>
                                                                    <input type = 'text' data-field = 'content.stat2_label' value = "<?= htmlspecialchars($content['stat2_label'] ?? '') ?>" placeholder = 'Districts Covered'>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-row-3'>
                                                                    <div class = 'field-group'>
                                                                    <label>Stat 3 Value</label>
                                                                    <input type = 'text' data-field = 'content.stat3_value' value = "<?= htmlspecialchars($content['stat3_value'] ?? '') ?>" placeholder = '12k+'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Stat 3 Label</label>
                                                                    <input type = 'text' data-field = 'content.stat3_label' value = "<?= htmlspecialchars($content['stat3_label'] ?? '') ?>" placeholder = 'Hours Contributed'>
                                                                    </div>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == GET INVOLVED PARTNER ===  ==
                                                                    elseif ( $sKey === 'get_involved_partner' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <input type = 'text' data-field = 'section_subtitle' value = "<?= htmlspecialchars($sData['section_subtitle'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Partner Types</label>
                                                                    <div id = 'partnerTypesList_get_involved_partner' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addPartnerType_get_involved_partner()'><i class = 'fa-solid fa-plus text-xs'></i> Add Partner Type</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == GET INVOLVED MEMBERSHIP ===  ==
                                                                    elseif ( $sKey === 'get_involved_membership' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <input type = 'text' data-field = 'section_subtitle' value = "<?= htmlspecialchars($sData['section_subtitle'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Description</label>
                                                                    <textarea data-field = 'content.description' rows = '3'><?= htmlspecialchars( $content[ 'description' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Member Benefits</label>
                                                                    <div id = 'benefitsList_get_involved_membership' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addBenefit_get_involved_membership()'><i class = 'fa-solid fa-plus text-xs'></i> Add Benefit</button>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Membership Types</label>
                                                                    <div id = 'membershipTypesList_get_involved_membership' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addMembershipType_get_involved_membership()'><i class = 'fa-solid fa-plus text-xs'></i> Add Membership Type</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == CONTRIBUTE HERO ===  ==
                                                                    elseif ( $sKey === 'contribute_hero' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Hero Image</label>
                                                                    <?php $heroImgId = 'hero_img_' . preg_replace( '/[^a-z0-9]/', '_', $sKey );
                                                                    ?>
                                                                    <input type = 'text' data-field = 'content.hero_image' id = "<?= $heroImgId ?>" value = "<?= htmlspecialchars($content['hero_image'] ?? '') ?>" class = 'upload-path-input'>
                                                                    <div class = 'upload-widget' data-type = 'image' data-target = "<?= $heroImgId ?>" data-base-url = "<?= htmlspecialchars(siteBaseUrl(), ENT_QUOTES, 'UTF-8') ?>" style = 'margin-top:8px'>
                                                                    <div class = 'upload-zone'>
                                                                    <input type = 'file' accept = 'image/*'>
                                                                    <div class = 'upload-placeholder'>
                                                                    <i class = 'fa-solid fa-cloud-arrow-up'></i>
                                                                    <p>Click or drag to upload hero image</p>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Badge</label>
                                                                    <input type = 'text' data-field = 'content.badge' value = "<?= htmlspecialchars($content['badge'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Title</label>
                                                                    <input type = 'text' data-field = 'content.title' value = "<?= htmlspecialchars($content['title'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle</label>
                                                                    <input type = 'text' data-field = 'content.subtitle' value = "<?= htmlspecialchars($content['subtitle'] ?? '') ?>">
                                                                    </div>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == RESOURCES HERO ( SPECIAL ) ===  ==
                                                                    elseif ( $sKey === 'resources_hero' ):
                                                                    ?>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Hero Image</label>
                                                                    <?php $heroImgId = 'hero_img_' . preg_replace( '/[^a-z0-9]/', '_', $sKey );
                                                                    ?>
                                                                    <input type = 'text' data-field = 'content.hero_image' id = "<?= $heroImgId ?>" value = "<?= htmlspecialchars($content['hero_image'] ?? '') ?>" class = 'upload-path-input'>
                                                                    <div class = 'upload-widget' data-type = 'image' data-target = "<?= $heroImgId ?>" data-base-url = "<?= htmlspecialchars(siteBaseUrl(), ENT_QUOTES, 'UTF-8') ?>" style = 'margin-top:8px'>
                                                                    <div class = 'upload-zone'>
                                                                    <input type = 'file' accept = 'image/*'>
                                                                    <div class = 'upload-placeholder'>
                                                                    <i class = 'fa-solid fa-cloud-arrow-up'></i>
                                                                    <p>Click or drag to upload hero image</p>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Tagline</label>
                                                                    <input type = 'text' data-field = 'content.tagline' value = "<?= htmlspecialchars($content['tagline'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Title ( HTML )</label>
                                                                    <input type = 'text' data-field = 'content.title' value = "<?= htmlspecialchars($content['title'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Subtitle</label>
                                                                    <textarea data-field = 'content.subtitle' rows = '2'><?= htmlspecialchars( $content[ 'subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-row'>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Text</label>
                                                                    <input type = 'text' data-field = 'content.button1_text' value = "<?= htmlspecialchars($content['button1_text'] ?? '') ?>">
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Button 1 Link</label>
                                                                    <input type = 'text' data-field = 'content.button1_link' value = "<?= htmlspecialchars($content['button1_link'] ?? '') ?>">
                                                                    </div>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Stats</label>
                                                                    <div id = "statsList_<?= $sKey ?>" class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = "addStat_<?= $sKey ?>()"><i class = 'fa-solid fa-plus text-xs'></i> Add Stat</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == RESOURCES REGISTRATION ===  ==
                                                                    elseif ( $sKey === 'resources_registration' ):
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Registration Documents</label>
                                                                    <div id = 'itemsList_resources_registration' class = 'space-y-2'></div>
                                                                    <button type = 'button' class = 'add-btn' onclick = 'addItem_resources_registration()'><i class = 'fa-solid fa-plus text-xs'></i> Add Registration</button>
                                                                    </div>

                                                                    <?php
                                                                    // ===  == DEFAULT ( simple text fields ) ===  ==
                                                                    else:
                                                                    ?>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Title</label>
                                                                    <input type = 'text' data-field = 'section_title' value = "<?= htmlspecialchars($sData['section_title'] ?? '') ?>" placeholder = 'Section heading'>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Section Subtitle</label>
                                                                    <textarea data-field = 'section_subtitle' rows = '2'><?= htmlspecialchars( $sData[ 'section_subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>
                                                                    <div class = 'field-group'>
                                                                    <label>Content Subtitle</label>
                                                                    <textarea data-field = 'content.subtitle' rows = '2'><?= htmlspecialchars( $content[ 'subtitle' ] ?? '' ) ?></textarea>
                                                                    </div>

                                                                    <?php endif;
                                                                    ?>

                                                                    </div>
                                                                    <?php endforeach;
                                                                    ?>

                                                                    <!-- Page Navigation -->
                                                                    <div class = 'bg-white rounded-2xl border border-slate-200 p-4 mb-6'>
                                                                    <p class = 'text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3'>Switch to another page</p>
                                                                    <div class = 'flex flex-wrap gap-2'>
                                                                    <?php foreach ( $pages as $pKey => $pDef ): ?>
                                                                    <?php if ( $pKey !== $pageKey ): ?>
                                                                    <a href = "?page=<?= $pKey ?>" class = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all'>
                                                                    <?= $pDef[ 'label' ] ?>
                                                                    <span class = 'text-[10px] text-slate-400'>( <?= count( $pDef[ 'sections' ] ) ?> )</span>
                                                                    </a>
                                                                    <?php endif;
                                                                    ?>
                                                                    <?php endforeach;
                                                                    ?>
                                                                    </div>
                                                                    </div>

                                                                    <div class = 'flex items-center justify-end gap-3 mb-8'>
                                                                    <button type = 'submit' class = 'px-8 py-3 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-red-700 transition-colors shadow-lg shadow-brandRed/20'>
                                                                    <i class = 'fa-solid fa-check text-xs mr-1'></i> Save All Sections
                                                                    </button>
                                                                    </div>
                                                                    </form>
                                                                    </div>

                                                                    <script>
// ===== DATA STORE =====
// Stored state per section. The editor only posts the fields it actually
// renders, so this is what lets the submit handler put back everything the
// form does not expose instead of blanking it on save.
var pageData = <?= json_encode(array_map(function($s) {
    return [
        'section_title'    => $s['section_title'] ?? '',
        'section_subtitle' => $s['section_subtitle'] ?? '',
        'content'          => $s['content'] ?? [],
    ];
}, $sections), JSON_HEX_TAG) ?>;

// ===== HELPER: Build JSON from all data-field inputs =====
function collectData() {
  var data = {};
  document.querySelectorAll('[data-field]').forEach(function(el) {
    var path = el.getAttribute('data-field');
    var parts = path.split('.');
    var obj = data;
    for (var i = 0; i < parts.length - 1; i++) {
      if (!obj[parts[i]]) obj[parts[i]] = {};
      obj = obj[parts[i]];
    }
    obj[parts[parts.length - 1]] = el.value;
  });
  return data;
}

document.getElementById('sectionsForm').addEventListener('submit', function(e) {
  var allData = {};
  document.querySelectorAll('.section-card').forEach(function(card) {
    var sectionKey = card.getAttribute('data-section');
    var sectionData = {};
    var secTitle = null;
    var secSubtitle = null;
    card.querySelectorAll('[data-field]').forEach(function(el) {
      var path = el.getAttribute('data-field');
      if (path === 'section_title') { secTitle = el.value; return; }
      if (path === 'section_subtitle') { secSubtitle = el.value; return; }
      var cleanPath = path.replace('content.', '');
      sectionData[cleanPath] = el.value;
    });

    // Every save handler writes `?? ''` per key, so a key missing from this
    // payload used to blank the stored value. A key is missing only when the
    // form has no input for it, which means "not editable here" and never "the
    // user cleared it" - so seed from the stored section and let the inputs
    // win. Without this, saving wiped whole pages of content: the Contact page
    // offices/map/whistleblower block, all 12 Policies, the Annual Reports
    // archive, the Vision/Mission core values, plus fields like the Get
    // Involved hero badge and the Projects dashboard subtitle.
    var stored = pageData[sectionKey] || {};
    var entry = { content: Object.assign({}, stored.content || {}, sectionData) };

    // Headings behave the same way: an edited input wins, otherwise keep what
    // is stored. The card label is only a last resort for a section that has
    // never been saved, which is what stops a custom heading being overwritten
    // by that label on every save.
    entry.section_title = (secTitle !== null) ? secTitle
      : (stored.section_title || card.querySelector('h3').textContent.trim());
    if (secSubtitle !== null) {
      entry.section_subtitle = secSubtitle;
    } else if (stored.section_subtitle != null) {
      entry.section_subtitle = stored.section_subtitle;
    }

    allData[sectionKey] = entry;
  });

  // Deep clone
  allData = JSON.parse(JSON.stringify(allData));

  // Merge dynamic arrays from section-specific variables
  Object.keys(allData).forEach(function(key) {
    if (typeof window['itemsArr_' + key] !== 'undefined') {
      allData[key].content.items = window['itemsArr_' + key];
    }
    if (typeof window['statsArr_' + key] !== 'undefined') {
      allData[key].content.stats = window['statsArr_' + key];
    }
    if (typeof window['stepsArr_' + key] !== 'undefined') {
      allData[key].content.steps = window['stepsArr_' + key];
    }
    if (typeof window['featuresArr_' + key] !== 'undefined') {
      allData[key].content.features = window['featuresArr_' + key];
    }
    if (typeof window['sectorsArr_' + key] !== 'undefined') {
      allData[key].content.sectors = window['sectorsArr_' + key];
    }
    if (typeof window['metricsArr_' + key] !== 'undefined') {
      allData[key].content.metrics = window['metricsArr_' + key];
    }
    if (typeof window['mneStepsArr_' + key] !== 'undefined') {
      allData[key].content.mne_steps = window['mneStepsArr_' + key];
    }
    if (typeof window['sdgsArr_' + key] !== 'undefined') {
      allData[key].content.sdgs = window['sdgsArr_' + key];
    }
    if (typeof window['caseStudiesArr_' + key] !== 'undefined') {
      allData[key].content.case_studies = window['caseStudiesArr_' + key];
    }
    if (typeof window['provincesArr_' + key] !== 'undefined') {
      allData[key].content.provinces = window['provincesArr_' + key];
    }
    if (typeof window['quickLinksArr_' + key] !== 'undefined') {
      allData[key].content.quick_links = window['quickLinksArr_' + key];
    }
    if (typeof window['districtsArr_' + key] !== 'undefined') {
      allData[key].content.districts = window['districtsArr_' + key];
    }
    if (typeof window['requirementsArr_' + key] !== 'undefined') {
      allData[key].content.requirements = window['requirementsArr_' + key];
    }
    if (typeof window['partnerTypesArr_' + key] !== 'undefined') {
      allData[key].content.partner_types = window['partnerTypesArr_' + key];
    }
    if (typeof window['benefitsArr_' + key] !== 'undefined') {
      allData[key].content.benefits = window['benefitsArr_' + key];
    }
    if (typeof window['membershipTypesArr_' + key] !== 'undefined') {
      allData[key].content.membership_types = window['membershipTypesArr_' + key];
    }
    if (typeof window['projectsArr_' + key] !== 'undefined') {
      allData[key].content.projects = window['projectsArr_' + key];
    }
  });

  document.getElementById('sectionsJson').value = JSON.stringify(allData);
});

<?php foreach ($sections as $sKey => $sData): ?>
<?php $content = $sData['content']; ?>

// ===== ITEMS: <?= $sKey ?> =====
var itemsArr_<?= $sKey ?> = <?= json_encode($content['items'] ?? $content['pillars'] ?? [], JSON_HEX_TAG) ?>;
function renderItems_<?= $sKey ?>() {
  var el = document.getElementById('itemsList_<?= $sKey ?>');
  if (!el) return;
  el.innerHTML = '';
  var isPillars = '<?= $sKey ?>' === 'our_work_pillars';
  itemsArr_<?= $sKey ?>.forEach(function(item, i) {
    var numField = isPillars ? '<div class="field-group"><label>Number</label><input value="'+(item.num||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].num=this.value" placeholder="01"></div>' : '<div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].icon=this.value" placeholder="fa-solid fa-heart"></div>';
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="itemsArr_<?= $sKey ?>.splice('+i+',1);renderItems_<?= $sKey ?>()"><i class="fa-solid fa-xmark"></i></button><div class="field-row-3">'+numField+'<div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].title=this.value"></div><div class="field-group"><label>Description</label><input value="'+(item.description||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].description=this.value"></div></div>'+(item.amount !== undefined ? '<div class="field-group"><label>Amount</label><input value="'+(item.amount||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].amount=this.value"></div>' : '')+(item.link !== undefined ? '<div class="field-row"><div class="field-group"><label>Link</label><input value="'+(item.link||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].link=this.value"></div><div class="field-group"><label>Link Text</label><input value="'+(item.link_text||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_<?= $sKey ?>['+i+'].link_text=this.value"></div></div>' : '')+'</div>';
  });
}
function addItem_<?= $sKey ?>() { itemsArr_<?= $sKey ?>.push({icon:'',title:'',description:''}); renderItems_<?= $sKey ?>(); }
renderItems_<?= $sKey ?>();

// ===== STATS: <?= $sKey ?> =====
var statsArr_<?= $sKey ?> = <?= json_encode($content['stats'] ?? [], JSON_HEX_TAG) ?>;
function renderStats_<?= $sKey ?>() {
  var el = document.getElementById('statsList_<?= $sKey ?>');
  if (!el) return;
  el.innerHTML = '';
  statsArr_<?= $sKey ?>.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="statsArr_<?= $sKey ?>.splice('+i+',1);renderStats_<?= $sKey ?>()"><i class="fa-solid fa-xmark"></i></button><div class="field-row-4"><div class="field-group"><label>Value</label><input value="'+(item.value||'').replace(/"/g,'&quot;')+'" onchange="statsArr_<?= $sKey ?>['+i+'].value=this.value" placeholder="85,000+"></div><div class="field-group"><label>Label</label><input value="'+(item.label||'').replace(/"/g,'&quot;')+'" onchange="statsArr_<?= $sKey ?>['+i+'].label=this.value"></div><div class="field-group"><label>Icon</label><input value="'+(item.icon||item.icon_class||'').replace(/"/g,'&quot;')+'" onchange="statsArr_<?= $sKey ?>['+i+'].icon=this.value" placeholder="fa-solid fa-users"></div><div class="field-group"><label>Color</label><input value="'+(item.color||'').replace(/"/g,'&quot;')+'" onchange="statsArr_<?= $sKey ?>['+i+'].color=this.value" placeholder="brandRed"></div></div></div>';
  });
}
function addStat_<?= $sKey ?>() { statsArr_<?= $sKey ?>.push({value:'',label:'',icon:'',color:''}); renderStats_<?= $sKey ?>(); }
renderStats_<?= $sKey ?>();

<?php endforeach; ?>

<?php if (isset($sections['impact_content'])): ?>
// ===== OVERRIDE: impact_content STATS (custom fields: target/icon/color/label/sub) =====
statsArr_impact_content = <?= json_encode($sections['impact_content']['content']['stats'] ?? [], JSON_HEX_TAG) ?>;
function renderStats_impact_content() {
  var el = document.getElementById('statsList_impact_content');
  if (!el) return;
  el.innerHTML = '';
  statsArr_impact_content.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="statsArr_impact_content.splice('+i+',1);renderStats_impact_content()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Target (number)</label><input value="'+(item.target||'').replace(/"/g,'&quot;')+'" onchange="statsArr_impact_content['+i+'].target=this.value" placeholder="150000"></div><div class="field-group"><label>Label</label><input value="'+(item.label||'').replace(/"/g,'&quot;')+'" onchange="statsArr_impact_content['+i+'].label=this.value" placeholder="Lives Impacted"></div></div><div class="field-row"><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="statsArr_impact_content['+i+'].icon=this.value" placeholder="fa-solid fa-heart-pulse"></div><div class="field-group"><label>Color</label><input value="'+(item.color||'').replace(/"/g,'&quot;')+'" onchange="statsArr_impact_content['+i+'].color=this.value" placeholder="brandRed"></div></div><div class="field-group"><label>Description (sub text)</label><input value="'+(item.sub||'').replace(/"/g,'&quot;')+'" onchange="statsArr_impact_content['+i+'].sub=this.value" placeholder="Across 4 provinces since inception"></div></div>';
  });
}
function addStat_impact_content() { statsArr_impact_content.push({target:'',label:'',icon:'',color:'',sub:''}); renderStats_impact_content(); }
renderStats_impact_content();

// ===== M&E STEPS =====
var mneStepsArr_impact_content = <?= json_encode($sections['impact_content']['content']['mne_steps'] ?? [], JSON_HEX_TAG) ?>;
function renderMneSteps_impact_content() {
  var el = document.getElementById('mneStepsList_impact_content');
  if (!el) return;
  el.innerHTML = '';
  mneStepsArr_impact_content.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="mneStepsArr_impact_content.splice('+i+',1);renderMneSteps_impact_content()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Label</label><input value="'+(item.label||'').replace(/"/g,'&quot;')+'" onchange="mneStepsArr_impact_content['+i+'].label=this.value" placeholder="Step 01"></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="mneStepsArr_impact_content['+i+'].title=this.value"></div></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="mneStepsArr_impact_content['+i+'].desc=this.value">'+(item.desc||'').replace(/</g,'&lt;')+'</textarea></div><div class="field-row"><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="mneStepsArr_impact_content['+i+'].icon=this.value" placeholder="fa-stamp"></div><div class="field-group"><label>Side</label><select onchange="mneStepsArr_impact_content['+i+'].side=this.value"><option value="left"'+(item.side==='left'?' selected':'')+'>Left</option><option value="right"'+(item.side==='right'?' selected':'')+'>Right</option></select></div></div></div>';
  });
}
function addMneStep_impact_content() { mneStepsArr_impact_content.push({label:'Step '+(mneStepsArr_impact_content.length+1).toString().padStart(2,'0'),title:'',desc:'',icon:'',side:'left'}); renderMneSteps_impact_content(); }
renderMneSteps_impact_content();

// ===== SDGs =====
var sdgsArr_impact_content = <?= json_encode($sections['impact_content']['content']['sdgs'] ?? [], JSON_HEX_TAG) ?>;
function renderSdgs_impact_content() {
  var el = document.getElementById('sdgsList_impact_content');
  if (!el) return;
  el.innerHTML = '';
  sdgsArr_impact_content.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="sdgsArr_impact_content.splice('+i+',1);renderSdgs_impact_content()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>SDG Number</label><input value="'+(item.number||'').replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].number=this.value" placeholder="3"></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].title=this.value"></div><div class="field-group"><label>Progress %</label><input value="'+(item.progress||'').replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].progress=this.value" placeholder="78"></div></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="sdgsArr_impact_content['+i+'].desc=this.value">'+(item.desc||'').replace(/</g,'&lt;')+'</textarea></div><div class="field-row"><div class="field-group"><label>Text Color</label><input value="'+(item.color||'').replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].color=this.value" placeholder="green-600"></div><div class="field-group"><label>BG Color</label><input value="'+(item.bg||'').replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].bg=this.value" placeholder="green"></div><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].icon=this.value" placeholder="fa-heart-pulse"></div></div><div class="field-group"><label>Tags (comma-separated)</label><input value="'+((item.tags||[]).join(', ')).replace(/"/g,'&quot;')+'" onchange="sdgsArr_impact_content['+i+'].tags=this.value.split(\',\').map(function(t){return t.trim()})" placeholder="12,000+ Healthcare Visits, 45 Medical Camps"></div></div>';
  });
}
function addSdg_impact_content() { sdgsArr_impact_content.push({number:'',title:'',desc:'',color:'',bg:'',progress:'',tags:[],icon:''}); renderSdgs_impact_content(); }
renderSdgs_impact_content();

// ===== CASE STUDIES =====
var caseStudiesArr_impact_content = <?= json_encode($sections['impact_content']['content']['case_studies'] ?? [], JSON_HEX_TAG) ?>;
function renderCaseStudies_impact_content() {
  var el = document.getElementById('caseStudiesList_impact_content');
  if (!el) return;
  el.innerHTML = '';
  caseStudiesArr_impact_content.forEach(function(item, i) {
    var statsStr = (item.stats||[]).map(function(s){return (s.val||'')+':'+(s.label||'')}).join('|');
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="caseStudiesArr_impact_content.splice('+i+',1);renderCaseStudies_impact_content()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Image Path</label><input value="'+(item.image||'').replace(/"/g,'&quot;')+'" onchange="caseStudiesArr_impact_content['+i+'].image=this.value" placeholder="../assets/images/water.jpg"></div><div class="field-group"><label>Category</label><input value="'+(item.category||'').replace(/"/g,'&quot;')+'" onchange="caseStudiesArr_impact_content['+i+'].category=this.value" placeholder="WASH"></div><div class="field-group"><label>Category Color</label><input value="'+(item.categoryColor||'').replace(/"/g,'&quot;')+'" onchange="caseStudiesArr_impact_content['+i+'].categoryColor=this.value" placeholder="blue-500"></div></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="caseStudiesArr_impact_content['+i+'].title=this.value"></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="caseStudiesArr_impact_content['+i+'].desc=this.value">'+(item.desc||'').replace(/</g,'&lt;')+'</textarea></div><div class="field-group"><label>Stats (val:label | val:label)</label><input value="'+statsStr.replace(/"/g,'&quot;')+'" onchange="var parts=this.value.split(\'|\');caseStudiesArr_impact_content['+i+'].stats=parts.map(function(p){var kv=p.split(\':\');return{val:kv[0]||\'\',label:kv[1]||\'\'}})" placeholder="8,200:People Served|35:Wells Built|12:Villages"></div><div class="field-group"><label>Quote</label><textarea rows="2" onchange="caseStudiesArr_impact_content['+i+'].quote=this.value">'+(item.quote||'').replace(/</g,'&lt;')+'</textarea></div><div class="field-row"><div class="field-group"><label>Author</label><input value="'+(item.author||'').replace(/"/g,'&quot;')+'" onchange="caseStudiesArr_impact_content['+i+'].author=this.value"></div><div class="field-group"><label>Outcome</label><input value="'+(item.outcome||'').replace(/"/g,'&quot;')+'" onchange="caseStudiesArr_impact_content['+i+'].outcome=this.value"></div></div></div>';
  });
}
function addCaseStudy_impact_content() { caseStudiesArr_impact_content.push({image:'',category:'',categoryColor:'',title:'',desc:'',stats:[{val:'',label:''},{val:'',label:''},{val:'',label:''}],quote:'',author:'',outcome:''}); renderCaseStudies_impact_content(); }
renderCaseStudies_impact_content();
<?php endif; ?>

// ===== DYNAMIC ROWS: FEATURES (editorial only) =====
<?php if (isset($sections['our_work_editorial'])): ?>
var featuresArr_our_work_editorial = <?= json_encode($sections['our_work_editorial']['content']['features'] ?? [], JSON_HEX_TAG) ?>;
function renderFeatures_our_work_editorial() {
  var el = document.getElementById('featuresList_our_work_editorial');
  if (!el) return;
  el.innerHTML = '';
  featuresArr_our_work_editorial.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="featuresArr_our_work_editorial.splice('+i+',1);renderFeatures_our_work_editorial()"><i class="fa-solid fa-xmark"></i></button><div class="field-row-3"><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="featuresArr_our_work_editorial['+i+'].icon=this.value"></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="featuresArr_our_work_editorial['+i+'].title=this.value"></div><div class="field-group"><label>Description</label><input value="'+(item.description||'').replace(/"/g,'&quot;')+'" onchange="featuresArr_our_work_editorial['+i+'].description=this.value"></div></div></div>';
  });
}
function addFeature_our_work_editorial() { featuresArr_our_work_editorial.push({icon:'',title:'',description:''}); renderFeatures_our_work_editorial(); }
renderFeatures_our_work_editorial();
<?php endif; ?>

// ===== DYNAMIC ROWS: STEPS =====
<?php foreach (['our_work_process','program_implementation'] as $stepKey): ?>
<?php if (isset($sections[$stepKey])): ?>
var stepsArr_<?= $stepKey ?> = <?= json_encode($sections[$stepKey]['content']['steps'] ?? [], JSON_HEX_TAG) ?>;
function renderSteps_<?= $stepKey ?>() {
  var el = document.getElementById('stepsList_<?= $stepKey ?>');
  if (!el) return;
  el.innerHTML = '';
  stepsArr_<?= $stepKey ?>.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="stepsArr_<?= $stepKey ?>.splice('+i+',1);renderSteps_<?= $stepKey ?>()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Number</label><input value="'+(item.num||'').replace(/"/g,'&quot;')+'" onchange="stepsArr_<?= $stepKey ?>['+i+'].num=this.value" style="max-width:80px"></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="stepsArr_<?= $stepKey ?>['+i+'].title=this.value"></div></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="stepsArr_<?= $stepKey ?>['+i+'].description=this.value">'+(item.description||'').replace(/</g,'&lt;')+'</textarea></div></div>';
  });
}
function addStep_<?= $stepKey ?>() { stepsArr_<?= $stepKey ?>.push({num:stepsArr_<?= $stepKey ?>.length+1,title:'',description:''}); renderSteps_<?= $stepKey ?>(); }
renderSteps_<?= $stepKey ?>();
<?php endif; ?>
<?php endforeach; ?>

// ===== DYNAMIC ROWS: SECTORS =====
<?php if (isset($sections['projects_dashboard'])): ?>
var sectorsArr_projects_dashboard = <?= json_encode($sections['projects_dashboard']['content']['sectors'] ?? [], JSON_HEX_TAG) ?>;
function renderSectors_projects_dashboard() {
  var el = document.getElementById('sectorsList_projects_dashboard');
  if (!el) return;
  el.innerHTML = '';
  sectorsArr_projects_dashboard.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="sectorsArr_projects_dashboard.splice('+i+',1);renderSectors_projects_dashboard()"><i class="fa-solid fa-xmark"></i></button><div class="field-row-4"><div class="field-group"><label>Name</label><input value="'+(item.name||'').replace(/"/g,'&quot;')+'" onchange="sectorsArr_projects_dashboard['+i+'].name=this.value"></div><div class="field-group"><label>Count</label><input value="'+(item.count||'').replace(/"/g,'&quot;')+'" onchange="sectorsArr_projects_dashboard['+i+'].count=this.value"></div><div class="field-group"><label>Percent</label><input value="'+(item.percent||'').replace(/"/g,'&quot;')+'" onchange="sectorsArr_projects_dashboard['+i+'].percent=this.value"></div><div class="field-group"><label>Gradient</label><input value="'+(item.gradient||'').replace(/"/g,'&quot;')+'" onchange="sectorsArr_projects_dashboard['+i+'].gradient=this.value" placeholder="from-red-500 to-red-700"></div></div></div>';
  });
}
function addSector_projects_dashboard() { sectorsArr_projects_dashboard.push({name:'',count:'',percent:'',gradient:''}); renderSectors_projects_dashboard(); }
renderSectors_projects_dashboard();

// ===== DYNAMIC ROWS: METRICS (dashboard) =====
var metricsArr_projects_dashboard = <?= json_encode($sections['projects_dashboard']['content']['metrics'] ?? [], JSON_HEX_TAG) ?>;
function renderMetrics_projects_dashboard() {
  var el = document.getElementById('metricsList_projects_dashboard');
  if (!el) return;
  el.innerHTML = '';
  metricsArr_projects_dashboard.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="metricsArr_projects_dashboard.splice('+i+',1);renderMetrics_projects_dashboard()"><i class="fa-solid fa-xmark"></i></button><div class="field-row-4"><div class="field-group"><label>Value</label><input value="'+(item.value||'').replace(/"/g,'&quot;')+'" onchange="metricsArr_projects_dashboard['+i+'].value=this.value"></div><div class="field-group"><label>Label</label><input value="'+(item.label||'').replace(/"/g,'&quot;')+'" onchange="metricsArr_projects_dashboard['+i+'].label=this.value"></div><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="metricsArr_projects_dashboard['+i+'].icon=this.value" placeholder="fa-solid fa-users"></div><div class="field-group"><label>Color</label><input value="'+(item.color||'').replace(/"/g,'&quot;')+'" onchange="metricsArr_projects_dashboard['+i+'].color=this.value" placeholder="brandRed"></div></div></div>';
  });
}
function addMetric_projects_dashboard() { metricsArr_projects_dashboard.push({value:'',label:'',icon:'',color:''}); renderMetrics_projects_dashboard(); }
renderMetrics_projects_dashboard();
<?php endif; ?>

<?php if (isset($sections['where_we_work_content'])): ?>
// ===== WHERE WE WORK: STATS =====
var statsArr_where_we_work_content = <?= json_encode($sections['where_we_work_content']['content']['stats']['items'] ?? [], JSON_HEX_TAG) ?>;
function renderStats_where_we_work_content() {
  var el = document.getElementById('statsList_where_we_work_content');
  if (!el) return;
  el.innerHTML = '';
  statsArr_where_we_work_content.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="statsArr_where_we_work_content.splice('+i+',1);renderStats_where_we_work_content()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Value</label><input value="'+(item.value||'').replace(/"/g,'&quot;')+'" onchange="statsArr_where_we_work_content['+i+'].value=this.value" placeholder="4"></div><div class="field-group"><label>Label</label><input value="'+(item.label||'').replace(/"/g,'&quot;')+'" onchange="statsArr_where_we_work_content['+i+'].label=this.value" placeholder="Provinces Covered"></div></div></div>';
  });
}
function addStat_where_we_work_content() { statsArr_where_we_work_content.push({value:'',label:''}); renderStats_where_we_work_content(); }
renderStats_where_we_work_content();

// ===== WHERE WE WORK: PROVINCES (nested: focus_areas + districts) =====
var provincesArr_where_we_work_content = <?= json_encode($sections['where_we_work_content']['content']['provinces'] ?? [], JSON_HEX_TAG) ?>;
function renderProvinces_where_we_work_content() {
  var el = document.getElementById('provincesList_where_we_work_content');
  if (!el) return;
  el.innerHTML = '';
  provincesArr_where_we_work_content.forEach(function(p, i) {
    var html = '<div class="dynamic-item" style="border:1px solid #e2e8f0;border-radius:0.75rem;padding:1rem;background:white;">';
    html += '<button type="button" class="remove-btn" onclick="provincesArr_where_we_work_content.splice('+i+',1);renderProvinces_where_we_work_content()"><i class="fa-solid fa-xmark"></i></button>';
    html += '<div class="field-row"><div class="field-group"><label>Section ID</label><input value="'+(p.id||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].id=this.value" placeholder="kp"></div><div class="field-group"><label>Icon</label><input value="'+(p.icon||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].icon=this.value" placeholder="fa-mountain"></div><div class="field-group"><label>Name</label><input value="'+(p.name||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].name=this.value"></div><div class="field-group"><label>BG Color</label><input value="'+(p.bg||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].bg=this.value" placeholder="white or slate-50"></div></div>';
    html += '<div class="field-group"><label>Description</label><textarea rows="2" onchange="provincesArr_where_we_work_content['+i+'].description=this.value">'+(p.description||'').replace(/</g,'&lt;')+'</textarea></div>';
    // Focus Areas
    html += '<div style="margin-top:0.5rem;padding:0.5rem;background:#f1f5f9;border-radius:0.5rem;"><p style="font-size:0.7rem;font-weight:600;color:#64748b;margin-bottom:0.5rem;">Focus Areas</p>';
    (p.focus_areas||[]).forEach(function(fa, fi) {
      html += '<div class="field-row" style="margin-bottom:0.25rem;"><div class="field-group" style="flex:0 0 80px;"><input value="'+(fa.icon||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].focus_areas['+fi+'].icon=this.value" placeholder="Icon" style="font-size:0.75rem;"></div><div class="field-group" style="flex:1;"><input value="'+(fa.title||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].focus_areas['+fi+'].title=this.value" placeholder="Title" style="font-size:0.75rem;"></div><div class="field-group" style="flex:2;"><input value="'+(fa.description||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].focus_areas['+fi+'].description=this.value" placeholder="Description" style="font-size:0.75rem;"></div><button type="button" class="remove-btn" onclick="provincesArr_where_we_work_content['+i+'].focus_areas.splice('+fi+',1);renderProvinces_where_we_work_content()" style="flex:0;"><i class="fa-solid fa-xmark"></i></button></div>';
    });
    html += '<button type="button" class="add-btn" onclick="if(!provincesArr_where_we_work_content['+i+'].focus_areas)provincesArr_where_we_work_content['+i+'].focus_areas=[];provincesArr_where_we_work_content['+i+'].focus_areas.push({icon:\'\',title:\'\',description:\'\'});renderProvinces_where_we_work_content()" style="font-size:0.7rem;"><i class="fa-solid fa-plus text-xs"></i> Add Focus Area</button></div>';
    // Districts within province
    html += '<div style="margin-top:0.5rem;padding:0.5rem;background:#f1f5f9;border-radius:0.5rem;"><p style="font-size:0.7rem;font-weight:600;color:#64748b;margin-bottom:0.5rem;">Districts</p>';
    (p.districts||[]).forEach(function(d, di) {
      html += '<div class="field-row" style="margin-bottom:0.25rem;"><div class="field-group" style="flex:1;"><input value="'+(d.name||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].districts['+di+'].name=this.value" placeholder="Name" style="font-size:0.75rem;"></div><div class="field-group" style="flex:1.5;"><input value="'+(d.program||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].districts['+di+'].program=this.value" placeholder="Program" style="font-size:0.75rem;"></div><div class="field-group" style="flex:0 0 60px;"><input value="'+(d.status||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].districts['+di+'].status=this.value" placeholder="Status" style="font-size:0.75rem;"></div><div class="field-group" style="flex:0 0 80px;"><input value="'+(d.beneficiaries||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].districts['+di+'].beneficiaries=this.value" placeholder="Benef." style="font-size:0.75rem;"></div><div class="field-group" style="flex:0 0 50px;"><input value="'+(d.year||'').replace(/"/g,'&quot;')+'" onchange="provincesArr_where_we_work_content['+i+'].districts['+di+'].year=this.value" placeholder="Year" style="font-size:0.75rem;"></div><button type="button" class="remove-btn" onclick="provincesArr_where_we_work_content['+i+'].districts.splice('+di+',1);renderProvinces_where_we_work_content()" style="flex:0;"><i class="fa-solid fa-xmark"></i></button></div>';
    });
    html += '<button type="button" class="add-btn" onclick="if(!provincesArr_where_we_work_content['+i+'].districts)provincesArr_where_we_work_content['+i+'].districts=[];provincesArr_where_we_work_content['+i+'].districts.push({name:\'\',program:\'\',status:\'Active\',beneficiaries:\'\',year:\'\'});renderProvinces_where_we_work_content()" style="font-size:0.7rem;"><i class="fa-solid fa-plus text-xs"></i> Add District</button></div>';
    html += '</div>';
    el.innerHTML += html;
  });
}
function addProvince_where_we_work_content() { provincesArr_where_we_work_content.push({id:'',icon:'',name:'',description:'',bg:'white',focus_areas:[],districts:[]}); renderProvinces_where_we_work_content(); }
renderProvinces_where_we_work_content();

// ===== WHERE WE WORK: QUICK LINKS =====
var quickLinksArr_where_we_work_content = <?= json_encode($sections['where_we_work_content']['content']['quick_links'] ?? [], JSON_HEX_TAG) ?>;
function renderQuickLinks_where_we_work_content() {
  var el = document.getElementById('quickLinksList_where_we_work_content');
  if (!el) return;
  el.innerHTML = '';
  quickLinksArr_where_we_work_content.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="quickLinksArr_where_we_work_content.splice('+i+',1);renderQuickLinks_where_we_work_content()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>ID</label><input value="'+(item.id||'').replace(/"/g,'&quot;')+'" onchange="quickLinksArr_where_we_work_content['+i+'].id=this.value" placeholder="kp"></div><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="quickLinksArr_where_we_work_content['+i+'].icon=this.value" placeholder="fa-mountain"></div><div class="field-group"><label>Name</label><input value="'+(item.name||'').replace(/"/g,'&quot;')+'" onchange="quickLinksArr_where_we_work_content['+i+'].name=this.value"></div><div class="field-group"><label>Description</label><input value="'+(item.desc||'').replace(/"/g,'&quot;')+'" onchange="quickLinksArr_where_we_work_content['+i+'].desc=this.value"></div></div></div>';
  });
}
function addQuickLink_where_we_work_content() { quickLinksArr_where_we_work_content.push({id:'',icon:'',name:'',desc:''}); renderQuickLinks_where_we_work_content(); }
renderQuickLinks_where_we_work_content();
<?php endif; ?>

<?php if (isset($sections['resources_registration'])): ?>
// ===== RESOURCES: REGISTRATION ITEMS =====
var itemsArr_resources_registration = <?= json_encode($sections['resources_registration']['content']['items'] ?? [], JSON_HEX_TAG) ?>;
function renderItems_resources_registration() {
  var el = document.getElementById('itemsList_resources_registration');
  if (!el) return;
  el.innerHTML = '';
  itemsArr_resources_registration.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="itemsArr_resources_registration.splice('+i+',1);renderItems_resources_registration()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_resources_registration['+i+'].title=this.value" placeholder="NGO Registration Certificate"></div><div class="field-group"><label>Badge</label><input value="'+(item.badge||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_resources_registration['+i+'].badge=this.value" placeholder="Registered"></div></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="itemsArr_resources_registration['+i+'].description=this.value">'+(item.description||'').replace(/</g,'&lt;')+'</textarea></div><div class="field-row"><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_resources_registration['+i+'].icon=this.value" placeholder="fa-stamp"></div><div class="field-group"><label>Registration #</label><input value="'+(item.registration_number||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_resources_registration['+i+'].registration_number=this.value"></div></div><div class="field-row"><div class="field-group"><label>Issuing Authority</label><input value="'+(item.issuing_authority||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_resources_registration['+i+'].issuing_authority=this.value"></div><div class="field-group"><label>Issue Date</label><input type="date" value="'+(item.issue_date||'').replace(/"/g,'&quot;')+'" onchange="itemsArr_resources_registration['+i+'].issue_date=this.value"></div></div></div>';
  });
}
function addItem_resources_registration() { itemsArr_resources_registration.push({title:'',description:'',registration_number:'',issuing_authority:'',issue_date:'',icon:'fa-stamp',badge:'',document_type:'certificate'}); renderItems_resources_registration(); }
renderItems_resources_registration();
<?php endif; ?>

<?php if (isset($sections['get_involved_volunteer'])): ?>
// ===== GET INVOLVED: VOLUNTEER REQUIREMENTS =====
var requirementsArr_get_involved_volunteer = <?= json_encode($sections['get_involved_volunteer']['content']['requirements'] ?? [], JSON_HEX_TAG) ?>;
function renderRequirements_get_involved_volunteer() {
  var el = document.getElementById('requirementsList_get_involved_volunteer');
  if (!el) return;
  el.innerHTML = '';
  requirementsArr_get_involved_volunteer.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="requirementsArr_get_involved_volunteer.splice('+i+',1);renderRequirements_get_involved_volunteer()"><i class="fa-solid fa-xmark"></i></button><div class="field-group"><label>Requirement</label><input value="'+item.replace(/"/g,'&quot;')+'" onchange="requirementsArr_get_involved_volunteer['+i+']=this.value" placeholder="Must be 18 years or older"></div></div>';
  });
}
function addRequirement_get_involved_volunteer() { requirementsArr_get_involved_volunteer.push(''); renderRequirements_get_involved_volunteer(); }
renderRequirements_get_involved_volunteer();
<?php endif; ?>

<?php if (isset($sections['get_involved_partner'])): ?>
// ===== GET INVOLVED: PARTNER TYPES =====
var partnerTypesArr_get_involved_partner = <?= json_encode($sections['get_involved_partner']['content']['partner_types'] ?? [], JSON_HEX_TAG) ?>;
function renderPartnerTypes_get_involved_partner() {
  var el = document.getElementById('partnerTypesList_get_involved_partner');
  if (!el) return;
  el.innerHTML = '';
  partnerTypesArr_get_involved_partner.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="partnerTypesArr_get_involved_partner.splice('+i+',1);renderPartnerTypes_get_involved_partner()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="partnerTypesArr_get_involved_partner['+i+'].icon=this.value" placeholder="fa-building-columns"></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="partnerTypesArr_get_involved_partner['+i+'].title=this.value"></div></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="partnerTypesArr_get_involved_partner['+i+'].description=this.value">'+(item.description||'').replace(/</g,'&lt;')+'</textarea></div></div>';
  });
}
function addPartnerType_get_involved_partner() { partnerTypesArr_get_involved_partner.push({icon:'',title:'',description:''}); renderPartnerTypes_get_involved_partner(); }
renderPartnerTypes_get_involved_partner();
<?php endif; ?>

<?php if (isset($sections['get_involved_membership'])): ?>
// ===== GET INVOLVED: MEMBER BENEFITS =====
var benefitsArr_get_involved_membership = <?= json_encode($sections['get_involved_membership']['content']['benefits'] ?? [], JSON_HEX_TAG) ?>;
function renderBenefits_get_involved_membership() {
  var el = document.getElementById('benefitsList_get_involved_membership');
  if (!el) return;
  el.innerHTML = '';
  benefitsArr_get_involved_membership.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="benefitsArr_get_involved_membership.splice('+i+',1);renderBenefits_get_involved_membership()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Icon</label><input value="'+(item.icon||'').replace(/"/g,'&quot;')+'" onchange="benefitsArr_get_involved_membership['+i+'].icon=this.value" placeholder="fa-newspaper"></div><div class="field-group"><label>Title</label><input value="'+(item.title||'').replace(/"/g,'&quot;')+'" onchange="benefitsArr_get_involved_membership['+i+'].title=this.value"></div></div><div class="field-group"><label>Description</label><textarea rows="2" onchange="benefitsArr_get_involved_membership['+i+'].description=this.value">'+(item.description||'').replace(/</g,'&lt;')+'</textarea></div></div>';
  });
}
function addBenefit_get_involved_membership() { benefitsArr_get_involved_membership.push({icon:'',title:'',description:''}); renderBenefits_get_involved_membership(); }
renderBenefits_get_involved_membership();

// ===== GET INVOLVED: MEMBERSHIP TYPES =====
var membershipTypesArr_get_involved_membership = <?= json_encode($sections['get_involved_membership']['content']['membership_types'] ?? [], JSON_HEX_TAG) ?>;
function renderMembershipTypes_get_involved_membership() {
  var el = document.getElementById('membershipTypesList_get_involved_membership');
  if (!el) return;
  el.innerHTML = '';
  membershipTypesArr_get_involved_membership.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-item"><button type="button" class="remove-btn" onclick="membershipTypesArr_get_involved_membership.splice('+i+',1);renderMembershipTypes_get_involved_membership()"><i class="fa-solid fa-xmark"></i></button><div class="field-row"><div class="field-group"><label>Type</label><input value="'+(item.type||'').replace(/"/g,'&quot;')+'" onchange="membershipTypesArr_get_involved_membership['+i+'].type=this.value" placeholder="Individual"></div><div class="field-group"><label>Price</label><input value="'+(item.price||'').replace(/"/g,'&quot;')+'" onchange="membershipTypesArr_get_involved_membership['+i+'].price=this.value" placeholder="Free / Annual"></div></div></div>';
  });
}
function addMembershipType_get_involved_membership() { membershipTypesArr_get_involved_membership.push({type:'',price:''}); renderMembershipTypes_get_involved_membership(); }
renderMembershipTypes_get_involved_membership();
<?php endif; ?>

</script>

<?php include __DIR__ . '/includes/footer.php';
            ?>
