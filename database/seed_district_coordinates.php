<?php
/**
 * WRC - map marker coordinates for the Where We Work page.
 *
 * The public map plots one marker per district from the active project list.
 * Marker positions come from the district_coordinates table, which used to be
 * created by hand, so a fresh or migrated database could miss the rows for
 * districts that gained projects later and those markers disappeared.
 *
 * This seed keeps the table in step with the districts WRC reports against:
 *   - the table is created once if it does not exist,
 *   - any missing district is inserted without touching existing rows, so
 *     hand-tuned positions always win,
 *   - matching ignores punctuation and case, so "DI Khan" already covers
 *     "D.I. Khan" and no near-duplicate row is added.
 *
 * Safe to run on every deployment. Standalone: php database/seed_district_coordinates.php
 */

if ( !function_exists( 'wrcSetupDistrictCoordinates' ) ) {
    /**
     * @return int Number of rows inserted (0 when the map is already complete).
     */
    function wrcSetupDistrictCoordinates( PDO $pdo ): int {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS district_coordinates (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                district VARCHAR(100) NOT NULL,
                province VARCHAR(100) NOT NULL,
                svg_x INT NOT NULL DEFAULT 300,
                svg_y INT NOT NULL DEFAULT 350,
                lat DECIMAL(10,6) NULL,
                lng DECIMAL(10,6) NULL,
                sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_district (district, province)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // district, province, x, y on the schematic 600x700 map.
        $rows = [
            // Khyber Pakhtunkhwa
            [ 'Peshawar', 'Khyber Pakhtunkhwa', 315, 165 ],
            [ 'Nowshera', 'Khyber Pakhtunkhwa', 322, 178 ],
            [ 'Dir', 'Khyber Pakhtunkhwa', 282, 108 ],
            [ 'Buner', 'Khyber Pakhtunkhwa', 302, 132 ],
            [ 'Khyber', 'Khyber Pakhtunkhwa', 330, 158 ],
            [ 'Malakand', 'Khyber Pakhtunkhwa', 298, 122 ],
            [ 'Bajaur', 'Khyber Pakhtunkhwa', 312, 96 ],
            [ 'Swat', 'Khyber Pakhtunkhwa', 288, 118 ],
            [ 'Chitral', 'Khyber Pakhtunkhwa', 275, 90 ],
            [ 'Mardan', 'Khyber Pakhtunkhwa', 318, 170 ],
            [ 'Swabi', 'Khyber Pakhtunkhwa', 320, 150 ],
            [ 'D.I. Khan', 'Khyber Pakhtunkhwa', 342, 202 ],
            [ 'Kohat', 'Khyber Pakhtunkhwa', 328, 192 ],
            [ 'Tank', 'Khyber Pakhtunkhwa', 338, 210 ],
            [ 'Lakki Marwat', 'Khyber Pakhtunkhwa', 335, 205 ],
            [ 'Haripur', 'Khyber Pakhtunkhwa', 330, 185 ],
            [ 'Abbottabad', 'Khyber Pakhtunkhwa', 338, 175 ],
            [ 'Mansehra', 'Khyber Pakhtunkhwa', 340, 165 ],
            [ 'Shangla', 'Khyber Pakhtunkhwa', 310, 118 ],
            [ 'Kohistan', 'Khyber Pakhtunkhwa', 298, 100 ],
            [ 'Hangu', 'Khyber Pakhtunkhwa', 332, 195 ],
            [ 'Charsadda', 'Khyber Pakhtunkhwa', 320, 168 ],
            [ 'NMD', 'Khyber Pakhtunkhwa', 310, 135 ],

            // Combined coverage areas used by the portfolio sheet. They get
            // their own point so the markers for "Malakand" and "Buner, Swabi &
            // Nowshera" do not sit on top of a neighbouring district marker.
            [ 'Malakand & Swat', 'Khyber Pakhtunkhwa', 283, 112 ],
            [ 'Buner, Swabi & Nowshera', 'Khyber Pakhtunkhwa', 322, 178 ],

            // Punjab / ICT
            [ 'Lahore', 'Punjab', 455, 252 ],
            [ 'Faisalabad', 'Punjab', 425, 268 ],
            [ 'Rawalpindi', 'Punjab', 418, 208 ],
            [ 'Islamabad', 'Punjab', 414, 200 ],
            [ 'Multan', 'Punjab', 408, 325 ],
            [ 'Dera Ghazi Khan', 'Punjab', 380, 310 ],
            [ 'Bahawalpur', 'Punjab', 430, 350 ],

            // Sindh
            [ 'Karachi', 'Sindh', 495, 495 ],
            [ 'Hyderabad', 'Sindh', 515, 458 ],
            [ 'Sukkur', 'Sindh', 492, 388 ],
            [ 'Larkana', 'Sindh', 472, 418 ],
            [ 'Jacobabad', 'Sindh', 478, 385 ],
            [ 'Shaheed Benazirabad', 'Sindh', 505, 420 ],

            // Balochistan
            [ 'Quetta', 'Balochistan', 188, 340 ],
            [ 'Zhob', 'Balochistan', 278, 278 ],
            [ 'Sherani', 'Balochistan', 258, 308 ],
            [ 'Killa Saifullah', 'Balochistan', 228, 298 ],
            [ 'Turbat', 'Balochistan', 115, 455 ],
            [ 'Gwadar', 'Balochistan', 90, 470 ],
            [ 'Sibi', 'Balochistan', 220, 360 ],
            [ 'Chaman', 'Balochistan', 168, 308 ],

            // Azad Kashmir
            [ 'Muzaffarabad', 'Azad Kashmir', 348, 145 ],
            [ 'Mirpur', 'Azad Kashmir', 358, 155 ],
            [ 'Rawalakot', 'Azad Kashmir', 355, 148 ],
            [ 'Bagh', 'Azad Kashmir', 352, 142 ],
            [ 'Poonch', 'Azad Kashmir', 345, 138 ],
            [ 'Neelum', 'Azad Kashmir', 342, 130 ],

            // Gilgit-Baltistan
            [ 'Gilgit', 'Gilgit-Baltistan', 310, 60 ],
            [ 'Skardu', 'Gilgit-Baltistan', 325, 55 ],
        ];

        $existing = $pdo->query( 'SELECT district FROM district_coordinates' )->fetchAll( PDO::FETCH_COLUMN );
        $normalise = static function ( string $name ): string {
            return (string) preg_replace( '/[^a-z0-9]/', '', strtolower( trim( $name ) ) );
        };

        $seen = [];
        foreach ( $existing as $name ) {
            $seen[ $normalise( (string) $name ) ] = true;
        }

        $insert = $pdo->prepare(
            'INSERT IGNORE INTO district_coordinates (district, province, svg_x, svg_y, sort_order)
             VALUES (?, ?, ?, ?, ?)'
        );

        $added = 0;
        $order = 0;
        foreach ( $rows as $row ) {
            $order++;
            if ( isset( $seen[ $normalise( $row[0] ) ] ) ) {
                continue;
            }
            $insert->execute( [ $row[0], $row[1], (int) $row[2], (int) $row[3], min( 255, $order ) ] );
            $seen[ $normalise( $row[0] ) ] = true;
            $added++;
        }

        return $added;
    }
}

if ( !defined( 'WRC_DISTRICT_COORDINATES_AUTO_RAN' ) && isset( $pdo ) && $pdo instanceof PDO && PHP_SAPI === 'cli' ) {
    define( 'WRC_DISTRICT_COORDINATES_AUTO_RAN', true );
    try {
        $districtStats = wrcSetupDistrictCoordinates( $pdo );
        echo 'District map coordinates added: ' . $districtStats . "\n";
    } catch ( Throwable $error ) {
        fwrite( STDERR, 'WARNING: could not seed district coordinates: ' . $error->getMessage() . "\n" );
    }
}
