<?php
/**
 * WRC - Donation ledger, payment channels and admin view support.
 *
 * Creates the `donations` table used by the public donation form and the
 * Admin > Donations screen, adds the payment-channel columns the form needs to
 * `bank_accounts`, and seeds the international channels (Payoneer and
 * TapTap Send) once. Existing account rows are never overwritten, so details
 * updated in the dashboard always win.
 *
 * Safe to run on every deployment. Standalone: php database/seed_donations.php
 */

if ( !function_exists( 'wrcSetupDonations' ) ) {
    function wrcSetupDonations( PDO $pdo ): array {
        $applied = [];

        // ---------------------------------------------------------- ledger ----
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS donations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                currency VARCHAR(10) NOT NULL DEFAULT 'PKR',
                amount_pkr DECIMAL(14,2) NULL,
                frequency VARCHAR(20) NOT NULL DEFAULT 'one-time',
                sector_key VARCHAR(40) NULL,
                sector_name VARCHAR(120) NULL,
                project_id INT UNSIGNED NULL,
                project_title VARCHAR(255) NULL,
                payment_method VARCHAR(30) NOT NULL DEFAULT 'bank',
                payment_reference VARCHAR(120) NULL,
                message TEXT NULL,
                screenshot_path VARCHAR(255) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'new',
                admin_note TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_donation_status (status),
                KEY idx_donation_created (created_at),
                KEY idx_donation_sector (sector_key),
                KEY idx_donation_currency (currency)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // ------------------------------------------- payment channel columns --
        $columns = [];
        try {
            foreach ( $pdo->query( 'SHOW COLUMNS FROM bank_accounts' ) as $column ) {
                $columns[ $column['Field'] ] = true;
            }
        } catch ( Throwable $error ) {
            return $applied;
        }

        $newColumns = [
            'account_type' => "VARCHAR(20) NOT NULL DEFAULT 'bank' AFTER swift_code",
            'currency'     => "VARCHAR(10) NOT NULL DEFAULT 'PKR' AFTER account_type",
            'country'      => "VARCHAR(100) NULL DEFAULT 'Pakistan' AFTER currency",
            'notes'        => 'TEXT NULL AFTER country',
            'updated_at'   => 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ];

        foreach ( $newColumns as $name => $definition ) {
            if ( isset( $columns[ $name ] ) ) {
                continue;
            }
            try {
                $pdo->exec( "ALTER TABLE bank_accounts ADD COLUMN `{$name}` {$definition}" );
                $applied[] = 'bank account column: ' . $name;
            } catch ( Throwable $error ) {
                // A parallel request may have added it already.
            }
        }

        // Existing rows are Pakistani bank transfers unless tagged otherwise.
        try {
            $pdo->exec(
                "UPDATE bank_accounts
                    SET account_type = 'bank'
                  WHERE account_type IS NULL OR account_type = ''"
            );
        } catch ( Throwable $error ) {
            // Non-fatal.
        }

        // -------------------------------------- international payment channels
        // Only inserted when the channel is missing, so account details edited
        // in the dashboard are never replaced by a deployment.
        $channels = [
            [
                'bank_name'      => 'EasyPaisa / JazzCash',
                'account_title'  => 'Welfare & Relief Committee',
                'account_number' => '0300-1234567',
                'iban'           => '',
                'branch'         => '',
                'swift_code'     => '',
                'account_type'   => 'mobile',
                'currency'       => 'PKR',
                'country'        => 'Pakistan',
                'notes'          => 'Send from your EasyPaisa or JazzCash app, then upload the transaction screenshot.',
                'sort_order'     => 10,
            ],
            [
                'bank_name'      => 'Payoneer',
                'account_title'  => 'Welfare & Relief Committee',
                'account_number' => 'donations@wrc.org.pk',
                'iban'           => '',
                'branch'         => '',
                'swift_code'     => '',
                'account_type'   => 'payoneer',
                'currency'       => 'USD',
                'country'        => 'International',
                'notes'          => 'Send to this Payoneer email, then upload the payment screenshot with your donation.',
                'sort_order'     => 20,
            ],
            [
                'bank_name'      => 'TapTap Send',
                'account_title'  => 'Welfare & Relief Committee',
                'account_number' => '0300-1234567',
                'iban'           => '',
                'branch'         => '',
                'swift_code'     => '',
                'account_type'   => 'taptap',
                'currency'       => 'PKR',
                'country'        => 'International',
                'notes'          => 'Use TapTap Send to send to this Pakistan mobile number, then upload the screenshot.',
                'sort_order'     => 21,
            ],
        ];

        foreach ( $channels as $channel ) {
            try {
                $exists = $pdo->prepare( 'SELECT COUNT(*) FROM bank_accounts WHERE account_type = ?' );
                $exists->execute( [ $channel['account_type'] ] );
                if ( (int) $exists->fetchColumn() > 0 ) {
                    continue;
                }

                $pdo->prepare(
                    'INSERT INTO bank_accounts
                        (bank_name, account_title, account_number, iban, branch, swift_code,
                         account_type, currency, country, notes, is_active, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)'
                )->execute( [
                    $channel['bank_name'],
                    $channel['account_title'],
                    $channel['account_number'],
                    $channel['iban'],
                    $channel['branch'],
                    $channel['swift_code'],
                    $channel['account_type'],
                    $channel['currency'],
                    $channel['country'],
                    $channel['notes'],
                    $channel['sort_order'],
                ] );
                $applied[] = 'payment channel: ' . $channel['bank_name'];
            } catch ( Throwable $error ) {
                // Non-fatal: the public form falls back to the built-in details.
            }
        }

        return $applied;
    }
}

if ( PHP_SAPI === 'cli' && isset( $argv ) && realpath( $argv[0] ?? '' ) === realpath( __FILE__ ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
    require_once dirname( __DIR__ ) . '/includes/functions.php';

    global $pdo;
    $result = wrcSetupDonations( $pdo );
    echo 'Donation setup complete: ' . ( $result ? implode( '; ', $result ) : 'already up to date' ) . PHP_EOL;
}
