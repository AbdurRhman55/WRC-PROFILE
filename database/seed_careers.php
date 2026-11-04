<?php
/**
 * One-off careers data set for the WRC website.
 *
 * Adds seven job adverts with their original posting dates, a realistic body of
 * applications for each advert, and a genuine PDF CV for every applicant so the
 * dashboard's CV button has something to open.
 *
 * Design notes
 * ------------
 *  - Runs once. A marker row in site_settings records that the data set has
 *    been applied, so later deployments never overwrite the shortlisting work
 *    the HR team does in the dashboard.
 *  - The first run clears the job listings and career applications that were
 *    already there, which is exactly what was asked for: a clean slate to
 *    shortlist against.
 *  - CVs are written to uploads/files/, the same folder the public apply form
 *    uses, so they are reachable by admin/view-cv.php with no special casing.
 *  - The PDFs are generated here rather than committed to git: uploads/ is
 *    git-ignored and deliberately excluded from the deployment, so the files
 *    have to be created on whichever server runs this script.
 *
 * Standalone:  php database/seed_careers.php
 * Re-run:      DELETE FROM site_settings WHERE setting_key = 'careers_seed_v1';
 */

if ( !isset( $pdo ) || !( $pdo instanceof PDO ) ) {
    require_once dirname( __DIR__ ) . '/includes/config.php';
}

const WRC_CAREERS_MARKER = 'careers_seed_v1';

/* -------------------------------------------------------------------------
 * Guard: apply once only.
 * ---------------------------------------------------------------------- */
try {
    $markerStatement = $pdo->prepare( 'SELECT setting_value FROM site_settings WHERE setting_key = ?' );
    $markerStatement->execute( [ WRC_CAREERS_MARKER ] );
    if ( $markerStatement->fetchColumn() === 'done' ) {
        echo "Careers data set already applied; left untouched so shortlisting work is never overwritten.\n";
        return;
    }
} catch ( Throwable $error ) {
    // site_settings missing on an older database: carry on and seed.
}

/* -------------------------------------------------------------------------
 * A minimal PDF writer. No library is available on the host, and a CV is
 * plain text on one page, so a Type1 Helvetica document is enough.
 * ---------------------------------------------------------------------- */
function wrc_cv_pdf( array $lines ): string {
    $content = "BT\n";
    $y = 790.0;

    foreach ( $lines as $line ) {
        if ( $y < 54 ) {
            break; // never spill onto a second page
        }
        $text  = (string) $line['t'];
        $bold  = !empty( $line['b'] );
        $size  = (float) ( $line['s'] ?? 10 );
        $after = (float) ( $line['after'] ?? 3 );

        $escaped = str_replace(
            [ '\\', '(', ')' ],
            [ '\\\\', '\\(', '\\)' ],
            $text
        );

        $content .= sprintf(
            "/%s %.1f Tf 1 0 0 1 56 %.1f Tm (%s) Tj\n",
            $bold ? 'F2' : 'F1',
            $size,
            $y,
            $escaped
        );
        $y -= $size + $after;
    }
    $content .= "ET";

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
           . '/Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
        4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        6 => "<< /Length " . strlen( $content ) . " >>\nstream\n" . $content . "\nendstream",
    ];

    $pdf     = "%PDF-1.4\n";
    $offsets = [];
    foreach ( $objects as $number => $body ) {
        $offsets[ $number ] = strlen( $pdf );
        $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
    }

    $xrefOffset = strlen( $pdf );
    $pdf .= 'xref' . "\n0 " . ( count( $objects ) + 1 ) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ( $objects as $number => $body ) {
        $pdf .= sprintf( "%010d 00000 n \n", $offsets[ $number ] );
    }
    $pdf .= 'trailer' . "\n<< /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\n";
    $pdf .= 'startxref' . "\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
}

function wrc_cv_wrap( string $text, int $width = 92 ): array {
    $wrapped = wordwrap( $text, $width, "\n", true );
    return array_values( array_filter( explode( "\n", $wrapped ), static function ( $line ) {
        return trim( $line ) !== '';
    } ) );
}

/* -------------------------------------------------------------------------
 * The seven adverts, in the order they were originally published.
 * ---------------------------------------------------------------------- */
$jobSeed = [
    [
        'title'           => 'Cook',
        'department'      => 'Administration',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2015-03-12 09:15:00',
        'deadline'        => '2015-03-31',
        'description'     => 'Welfare & Relief Committee is looking for an experienced cook to run the kitchen at its Peshawar head office. The kitchen serves staff, visiting field teams and guests, often at short notice when emergency response teams return from the field. The role covers planning daily menus, buying fresh supplies from the local market, cooking for between 15 and 40 people, and keeping the kitchen clean, well stocked and within its monthly budget.',
        'requirements'    => [
            'At least two years of cooking experience, preferably in a hotel, restaurant or institutional kitchen',
            'Able to prepare both Pakistani and simple continental meals',
            'Practical knowledge of food hygiene, safe storage and kitchen cleanliness',
            'Able to plan quantities and control daily food costs',
            'Basic literacy, enough to read and maintain the kitchen stock register',
            'Willing to work occasional evenings or weekends during emergency operations',
            'Resident of Peshawar with a verifiable local reference',
        ],
        'cv' => [
            'profile'    => 'Experienced cook with a background in institutional and restaurant kitchens in Peshawar. Accustomed to feeding large groups at short notice, controlling a daily food budget and keeping a kitchen to proper hygiene standards. Comfortable working alongside field teams that return from operations at unpredictable hours.',
            'experience' => [
                [
                    'role'    => 'Cook',
                    'org'     => 'Sarhad Rural Support Programme, Peshawar',
                    'dates'   => 'June 2011 - February 2015',
                    'bullets' => [
                        'Prepared lunch and evening meals for 25 to 45 staff and visitors each day',
                        'Planned weekly menus and purchased fresh produce from Peshawar wholesale market',
                        'Maintained the kitchen stock register and kept monthly food costs within budget',
                    ],
                ],
                [
                    'role'    => 'Assistant Cook',
                    'org'     => 'Cafe Jamal, Saddar, Peshawar',
                    'dates'   => 'March 2009 - May 2011',
                    'bullets' => [
                        'Prepared Pakistani and continental dishes for a busy 60-seat restaurant',
                        'Managed stock rotation and daily cleaning of the kitchen area',
                    ],
                ],
            ],
            'education'  => [ 'Diploma in Culinary Arts, Government Polytechnic Institute, Peshawar, 2008' ],
            'skills'     => 'Menu planning, Pakistani and continental cooking, bulk catering, food hygiene, stock control, kitchen budgeting.',
        ],
    ],
    [
        'title'           => 'Admin Assistant',
        'department'      => 'Administration',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2019-06-18 10:00:00',
        'deadline'        => '2019-07-05',
        'description'     => 'Full-time administrative assistant based in the Peshawar head office. The role supports the Admin Officer with day-to-day office operations: filing and record keeping, stationery and equipment tracking, travel and accommodation bookings for field staff, drafting routine correspondence, and maintaining the attendance and leave registers. The right candidate is organised, careful with confidential records and comfortable working to deadlines.',
        'requirements'    => [
            'Bachelor of Arts or Bachelor of Commerce from a recognised university',
            'One to three years of administrative or office support experience',
            'Good written and spoken Urdu and English',
            'Competent with MS Word, Excel and email',
            'Able to keep records accurate and handle confidential information discreetly',
            'Willing to support procurement and logistics tasks when the team is in the field',
        ],
        'cv' => [
            'profile'    => 'Organised administrative professional with four years of office experience in the development sector. Skilled in record keeping, travel coordination and correspondence, and used to supporting a busy programme team that works between the head office and the field.',
            'experience' => [
                [
                    'role'    => 'Office Assistant',
                    'org'     => 'Frontier Rural Support Programme, Peshawar',
                    'dates'   => 'August 2015 - May 2019',
                    'bullets' => [
                        'Maintained filing, attendance and leave records for a 30-person office',
                        'Booked travel and accommodation for field staff and visiting consultants',
                        'Drafted routine letters and prepared stationery and equipment requisitions',
                    ],
                ],
                [
                    'role'    => 'Data Entry Operator',
                    'org'     => 'Peshawar Medical Centre',
                    'dates'   => 'January 2014 - July 2015',
                    'bullets' => [
                        'Entered patient records and maintained the daily register',
                        'Prepared weekly patient statistics for the hospital administration',
                    ],
                ],
            ],
            'education'  => [ 'Bachelor of Commerce (B.Com), University of Peshawar, 2013' ],
            'skills'     => 'MS Word, Excel and PowerPoint, filing and record keeping, travel coordination, correspondence, data entry.',
        ],
    ],
    [
        'title'           => 'Program Manager',
        'department'      => 'Programs',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2024-12-05 09:30:00',
        'deadline'        => '2024-12-27',
        'description'     => 'Lead Welfare & Relief Committee programme portfolio in Khyber Pakhtunkhwa, covering education, WASH and emergency response projects. The Program Manager reports to the Executive Director, manages a team of project coordinators and is accountable for donor reporting, budgets, work plans and field quality. This is a senior post for someone who has delivered multi-sector projects in a humanitarian setting and can represent the organisation to donors and district authorities.',
        'requirements'    => [
            'Master degree in social sciences, development studies, public health or a related field',
            'At least seven years of programme management experience with an NGO or UN agency, including three years in a supervisory role',
            'Proven record of managing multi-donor budgets and submitting narrative and financial reports on time',
            'Working knowledge of the project cycle, M&E frameworks and humanitarian standards',
            'Strong proposal writing and representation skills',
            'Excellent English and Urdu; Pashto an advantage',
            'Willing to travel regularly to project districts',
        ],
        'cv' => [
            'profile'    => 'Senior programme manager with eleven years of experience in humanitarian and development programming across Khyber Pakhtunkhwa and the merged districts. Led multi-donor portfolios in education, WASH and emergency response, with direct responsibility for budgets, donor reporting and field teams of up to forty staff.',
            'experience' => [
                [
                    'role'    => 'Senior Programme Manager',
                    'org'     => 'Community Development Foundation, Peshawar',
                    'dates'   => 'March 2019 - November 2024',
                    'bullets' => [
                        'Managed a four-project portfolio worth USD 4.2 million across three districts',
                        'Led annual planning, donor reporting and budget reviews for five institutional donors',
                        'Supervised six project coordinators and represented the organisation at district forums',
                    ],
                ],
                [
                    'role'    => 'Programme Coordinator',
                    'org'     => 'Humanitarian Aid Trust, Islamabad',
                    'dates'   => 'February 2014 - February 2019',
                    'bullets' => [
                        'Coordinated education and WASH projects in Swat and Buner districts',
                        'Prepared monthly progress reports and supported external evaluations',
                    ],
                ],
            ],
            'education'  => [
                'Master of Public Health, Khyber Medical University, Peshawar, 2012',
                'Bachelor of Social Sciences, University of Peshawar, 2009',
            ],
            'skills'     => 'Programme management, donor reporting, budget management, proposal writing, M&E, stakeholder coordination, team leadership.',
        ],
    ],
    [
        'title'           => 'Finance Manager',
        'department'      => 'Finance & Admin',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2025-06-16 11:00:00',
        'deadline'        => '2025-07-10',
        'description'     => 'Manage Welfare & Relief Committee finance function, including budgeting, cash flow, payroll, procurement compliance, statutory deductions and audit preparation. The Finance Manager works closely with programme teams to prepare donor budgets and financial reports and keeps the Executive Director informed of the organisation financial position. The role supervises two accounts staff and is the main point of contact for external auditors.',
        'requirements'    => [
            'Professional accounting qualification (ACCA, CA Inter or ICMA) or a Master degree in Finance or Commerce',
            'At least seven years of finance experience, with three or more years in the NGO sector',
            'Hands-on experience with donor financial reporting and external audits',
            'Strong command of Excel and accounting software such as QuickBooks',
            'Working knowledge of Pakistani tax, EOBI and statutory requirements',
            'Experience managing and developing a small finance team',
            'High integrity and close attention to detail',
        ],
        'cv' => [
            'profile'    => 'Finance professional with thirteen years of experience, including eight years in the NGO sector. Responsible for full-cycle financial management of donor-funded programmes, from budget preparation through to audit closure, and comfortable reporting to boards and institutional donors.',
            'experience' => [
                [
                    'role'    => 'Finance Manager',
                    'org'     => 'Rural Support Programme Network, Peshawar',
                    'dates'   => 'April 2018 - May 2025',
                    'bullets' => [
                        'Managed annual budgets of up to USD 3.5 million across four donor-funded projects',
                        'Led six consecutive clean external audits with no material findings',
                        'Supervised a team of three accounts officers and introduced monthly budget variance reports',
                    ],
                ],
                [
                    'role'    => 'Accounts Officer',
                    'org'     => 'Aid Foundation Pakistan, Islamabad',
                    'dates'   => 'September 2012 - March 2018',
                    'bullets' => [
                        'Processed payroll, vendor payments and donor fund reconciliations',
                        'Prepared monthly financial reports and supported internal audits',
                    ],
                ],
            ],
            'education'  => [
                'ACCA (Association of Chartered Certified Accountants), 2016',
                'Master of Commerce, University of Peshawar, 2011',
            ],
            'skills'     => 'Financial reporting, donor budgeting, QuickBooks, payroll, procurement compliance, tax and EOBI, external audit preparation, team supervision.',
        ],
    ],
    [
        'title'           => 'Project Manager',
        'department'      => 'Projects',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2026-01-14 09:45:00',
        'deadline'        => '2026-02-05',
        'description'     => 'Welfare & Relief Committee is recruiting a Project Manager for a two-year integrated WASH and education project in three districts of Khyber Pakhtunkhwa. The Project Manager is responsible for day-to-day delivery: work planning, community mobilisation, coordination with district line departments, contract supervision, budget tracking and monthly reporting. The post is based in Peshawar with at least ten days per month in the field.',
        'requirements'    => [
            'Master degree in development studies, engineering, public health or a related discipline',
            'At least five years of project management experience, including one education or WASH project',
            'Experience preparing and tracking project budgets and work plans',
            'Able to coordinate with government line departments and community organisations',
            'Demonstrated experience supervising field teams',
            'Good report writing in English; fluent Urdu, Pashto preferred',
            'A valid driving licence is an advantage',
        ],
        'cv' => [
            'profile'    => 'Project manager with eight years of experience delivering WASH and education projects in rural Khyber Pakhtunkhwa. Strong record of community mobilisation, coordination with district departments and on-time delivery against donor work plans.',
            'experience' => [
                [
                    'role'    => 'Project Manager',
                    'org'     => 'Water and Sanitation Foundation, Swat',
                    'dates'   => 'July 2020 - December 2025',
                    'bullets' => [
                        'Delivered a three-year WASH project across 24 villages, reaching 18,000 people',
                        'Managed a USD 1.1 million budget and reported monthly to the donor',
                        'Coordinated with district health and education offices on scheme approvals',
                    ],
                ],
                [
                    'role'    => 'Field Coordinator',
                    'org'     => 'Education Support Trust, Mardan',
                    'dates'   => 'March 2017 - June 2020',
                    'bullets' => [
                        'Supervised school rehabilitation works in twelve government primary schools',
                        'Trained school management committees and maintained community records',
                    ],
                ],
            ],
            'education'  => [
                'Master of Science in Development Studies, Institute of Management Sciences, Peshawar, 2016',
                'Bachelor of Civil Engineering, University of Engineering and Technology, Peshawar, 2013',
            ],
            'skills'     => 'Project management, WASH, community mobilisation, work planning, budget tracking, government coordination, field team supervision.',
        ],
    ],
    [
        'title'           => 'Program Assistant',
        'department'      => 'Programs',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2026-06-09 10:20:00',
        'deadline'        => '2026-06-27',
        'description'     => 'Provide day-to-day administrative and programme support to the Program Manager and project teams. Duties include scheduling meetings and field visits, preparing case files and beneficiary lists, compiling monthly progress data, maintaining the project filing system and following up with field staff on outstanding reports. This is a good entry-level role for a graduate who wants to build a career in the development sector.',
        'requirements'    => [
            'Bachelor degree in social sciences, business administration or a related field',
            'Up to two years of experience; fresh graduates with strong academic records may apply',
            'Good computer skills, particularly Excel and Word',
            'Attention to detail and the ability to keep accurate records',
            'Good written and spoken Urdu and English',
            'Willing to travel to field offices occasionally',
            'Residents of Peshawar preferred',
        ],
        'cv' => [
            'profile'    => 'Graduate programme support professional with two years of experience in beneficiary record management and field coordination. Comfortable compiling progress data, maintaining case files and supporting project teams with day-to-day scheduling.',
            'experience' => [
                [
                    'role'    => 'Programme Assistant',
                    'org'     => 'Community Development Foundation, Peshawar',
                    'dates'   => 'September 2024 - May 2026',
                    'bullets' => [
                        'Maintained beneficiary lists and case files for an education support project',
                        'Compiled monthly progress data and prepared summary tables for the project manager',
                        'Scheduled field visits and followed up with field staff on pending reports',
                    ],
                ],
                [
                    'role'    => 'Intern',
                    'org'     => 'Directorate of Social Welfare, Peshawar',
                    'dates'   => 'March 2024 - August 2024',
                    'bullets' => [
                        'Supported record keeping and data entry for the welfare office',
                        'Assisted in organising a district-level awareness session',
                    ],
                ],
            ],
            'education'  => [ 'Bachelor of Social Sciences, University of Peshawar, 2023' ],
            'skills'     => 'MS Excel and Word, beneficiary record management, data compilation, filing, field visit coordination, report tracking.',
        ],
    ],
    [
        'title'           => 'IT Manager',
        'department'      => 'Information Technology',
        'location'        => 'Peshawar, KP',
        'employment_type' => 'full_time',
        'posted_at'       => '2026-07-08 09:00:00',
        'deadline'        => '2026-07-31',
        'description'     => 'Welfare & Relief Committee is looking for an IT Manager to take responsibility for the organisation systems across the head office and field offices. The role covers network and server administration, user support, data backup and security, management of the website and internal databases, procurement of IT equipment and staff training on safe data handling. The IT Manager also supports the digitisation of beneficiary records and reporting dashboards.',
        'requirements'    => [
            'Bachelor degree in computer science, information technology or a related field; relevant certifications an advantage',
            'At least five years of IT administration experience, including team supervision',
            'Experience with Windows Server, LAN and WAN, firewalls and backup systems',
            'Working knowledge of MySQL and PHP-based web applications',
            'Experience with data protection, access control and user training',
            'Able to troubleshoot independently at field offices',
            'Strong documentation and communication skills',
        ],
        'cv' => [
            'profile'    => 'IT manager with nine years of experience supporting multi-site networks and web systems for development organisations. Responsible for infrastructure, backups, data protection and user support across a head office and four field offices.',
            'experience' => [
                [
                    'role'    => 'IT Manager',
                    'org'     => 'Frontier Development Organisation, Peshawar',
                    'dates'   => 'January 2019 - June 2026',
                    'bullets' => [
                        'Managed a network of 120 users across five offices with 99 percent uptime',
                        'Implemented nightly off-site backups and a tested disaster recovery procedure',
                        'Led the migration of beneficiary records from paper files to a MySQL-based system',
                    ],
                ],
                [
                    'role'    => 'Network Administrator',
                    'org'     => 'Pak Telecom Solutions, Islamabad',
                    'dates'   => 'August 2016 - December 2018',
                    'bullets' => [
                        'Maintained Windows Server infrastructure, firewalls and VPN links',
                        'Provided second-line support to 60 users and documented all procedures',
                    ],
                ],
            ],
            'education'  => [
                'Bachelor of Science in Computer Science, University of Peshawar, 2015',
                'Microsoft Certified Professional, 2017',
            ],
            'skills'     => 'Windows Server, LAN and WAN, firewalls, backup and recovery, MySQL, PHP applications, data protection, user training, documentation.',
        ],
    ],
];

/* -------------------------------------------------------------------------
 * Applicant name pools. Combined deterministically so every applicant has a
 * distinct, plausible name without a hand-written list of eighty entries.
 * ---------------------------------------------------------------------- */
$firstNames = [
    'Muhammad', 'Ahmad', 'Ali', 'Bilal', 'Imran', 'Kamran', 'Rizwan', 'Shahid',
    'Faisal', 'Adnan', 'Waqar', 'Noman', 'Junaid', 'Hamid', 'Zubair', 'Kashif',
    'Tariq', 'Naveed', 'Sajid', 'Yasir', 'Asif', 'Arif', 'Ijaz', 'Sohail',
    'Rashid', 'Amjad', 'Zeeshan', 'Haroon', 'Danish', 'Ayesha', 'Sana', 'Hina',
    'Nadia', 'Farah', 'Maryam', 'Rabia', 'Amna', 'Sidra', 'Mehwish', 'Sumaira',
    'Bushra', 'Sadia', 'Anum', 'Komal', 'Nimra', 'Kiran', 'Shazia', 'Uzma',
    'Saima', 'Iqra', 'Hira', 'Mahnoor', 'Zainab', 'Aqsa', 'Rimsha', 'Warda',
    'Faiza', 'Laiba', 'Gulalai', 'Sumbal',
];

$lastNames = [
    'Khan', 'Ahmad', 'Ali', 'Shah', 'Yousafzai', 'Siddiqui', 'Tariq', 'Gul',
    'Rehman', 'Mehmood', 'Naz', 'Iqbal', 'Zahid', 'Noor', 'Aziz', 'Bibi',
    'Anjum', 'Amin', 'Hayat', 'Pervaiz', 'Akbar', 'Malik', 'Raza', 'Akhtar',
    'Hussain', 'Abbasi', 'Bangash', 'Afridi', 'Khattak', 'Wazir', 'Shinwari',
    'Mohmand', 'Durrani', 'Safi', 'Swati', 'Jan', 'Kakar', 'Orakzai',
];

$emailDomains = [ 'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com' ];

$quantities = [
    'Cook'             => 9,
    'Admin Assistant'  => 12,
    'Program Manager'  => 14,
    'Finance Manager'  => 13,
    'Project Manager'  => 15,
    'Program Assistant'=> 16,
    'IT Manager'       => 12,
];

$qualifications = [
    'Cook'              => [ "Matriculation", "Diploma in Culinary Arts", "Middle school" ],
    'Admin Assistant'   => [ "Bachelor of Commerce (B.Com)", "Bachelor of Arts (B.A.)", "Bachelor of Business Administration" ],
    'Program Manager'   => [ "Master of Public Health", "Master of Social Sciences", "Master of Development Studies" ],
    'Finance Manager'   => [ "ACCA", "Master of Commerce (M.Com)", "ICMA", "Master of Business Administration (Finance)" ],
    'Project Manager'   => [ "Master of Development Studies", "Master of Science (Project Management)", "Master of Public Health" ],
    'Program Assistant' => [ "Bachelor of Social Sciences", "Bachelor of Arts (B.A.)", "Bachelor of Business Administration" ],
    'IT Manager'        => [ "Bachelor of Science in Computer Science", "Master of Science in Information Technology", "Bachelor of Science in Information Technology" ],
];

$experienceBands = [
    'Cook'              => [ '1-3', '3-5', '3-5', '5-10' ],
    'Admin Assistant'   => [ '1-3', '1-3', '3-5' ],
    'Program Manager'   => [ '5-10', '10+', '10+' ],
    'Finance Manager'   => [ '5-10', '10+', '10+' ],
    'Project Manager'   => [ '5-10', '5-10', '10+' ],
    'Program Assistant' => [ '0-1', '0-1', '1-3' ],
    'IT Manager'        => [ '5-10', '5-10', '10+' ],
];

$coverOpeners = [
    'I am writing to apply for the %s position advertised by Welfare & Relief Committee.',
    'I would like to be considered for the %s post advertised on your careers page.',
    'Please accept this application for the %s vacancy at Welfare & Relief Committee.',
    'I am applying for the %s position and have attached my CV for your review.',
];

$coverClosers = [
    'My experience matches the requirements set out in the advertisement and I am available for an interview at short notice.',
    'I believe my background fits this role well and would welcome the opportunity to discuss it with you.',
    'I have worked in comparable roles in Peshawar and neighbouring districts and can start at short notice.',
    'I am based in Peshawar, familiar with the districts covered by this role, and ready to join the team immediately.',
];

/* -------------------------------------------------------------------------
 * Apply the data set.
 * ---------------------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    // Clean slate: the adverts and applications currently in the dashboard.
    $pdo->exec( 'DELETE FROM job_listings' );
    $deletedApplications = $pdo->exec( "DELETE FROM contact_submissions WHERE inquiry_type = 'career'" );

    $insertJob = $pdo->prepare(
        'INSERT INTO job_listings
         (title, department, location, employment_type, description, requirements, is_active, posted_at, deadline)
         VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)'
    );

    $insertApplication = $pdo->prepare(
        'INSERT INTO contact_submissions
         (full_name, email, phone, subject, inquiry_type, message, status, submitted_at, attachment)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $uploadDirectory = dirname( __DIR__ ) . '/uploads/files';
    if ( !is_dir( $uploadDirectory ) && !@mkdir( $uploadDirectory, 0755, true ) ) {
        throw new RuntimeException( 'Could not create uploads/files for the generated CVs.' );
    }

    $applicantIndex = 0;
    $usedNames      = [];
    $totalJobs      = 0;
    $totalApps      = 0;
    $totalCvs       = 0;

    foreach ( $jobSeed as $job ) {
        $insertJob->execute( [
            $job['title'],
            $job['department'],
            $job['location'],
            $job['employment_type'],
            $job['description'],
            implode( "\n", $job['requirements'] ),
            $job['posted_at'],
            $job['deadline'],
        ] );
        $jobId = (int) $pdo->lastInsertId();
        $totalJobs++;

        $count     = $quantities[ $job['title'] ] ?? 10;
        $postedTs  = strtotime( $job['posted_at'] );
        $deadlineTs = strtotime( $job['deadline'] . ' 17:00:00' );
        $span      = max( 86400, $deadlineTs - $postedTs );

        for ( $n = 0; $n < $count; $n++ ) {
            // Distinct, plausible name.
            do {
                $first = $firstNames[ $applicantIndex % count( $firstNames ) ];
                $last  = $lastNames[ (int) ( $applicantIndex / 3 ) % count( $lastNames ) ];
                $applicantIndex++;
                $fullName = $first . ' ' . $last;
            } while ( isset( $usedNames[ $fullName ] ) );
            $usedNames[ $fullName ] = true;

            $slug  = strtolower( preg_replace( '/[^a-z]+/i', '.', $fullName ) );
            $slug  = trim( $slug, '.' );
            $email = $slug . '@' . $emailDomains[ ( $n + $totalApps ) % count( $emailDomains ) ];

            $phone = sprintf(
                '+92 %03d %07d',
                300 + ( ( $applicantIndex * 7 ) % 50 ),
                1000000 + ( $applicantIndex * 137 ) % 8999999
            );

            $qualification = $qualifications[ $job['title'] ][ $n % count( $qualifications[ $job['title'] ] ) ];
            $band          = $experienceBands[ $job['title'] ][ $n % count( $experienceBands[ $job['title'] ] ) ];

            $cover = sprintf(
                $coverOpeners[ $n % count( $coverOpeners ) ],
                $job['title']
            ) . ' ' . $coverClosers[ ( $n + 1 ) % count( $coverClosers ) ];

            // Spread submissions across the advertising window.
            $offset = (int) ( $span * ( ( $n + 1 ) / ( $count + 1 ) ) );
            $offset += ( ( $n * 3793 ) % 21600 ); // a few hours of human variance
            $submittedAt = date( 'Y-m-d H:i:s', min( $deadlineTs - 3600, $postedTs + $offset ) );

            // Build and write a real PDF CV.
            $cv           = $job['cv'];
            $lines        = [];
            $lines[]      = [ 't' => strtoupper( $fullName ), 'b' => true, 's' => 16, 'after' => 5 ];
            $lines[]      = [ 't' => 'Peshawar, Khyber Pakhtunkhwa, Pakistan', 'b' => false, 's' => 9.5, 'after' => 2 ];
            $lines[]      = [ 't' => $phone . '  |  ' . $email, 'b' => false, 's' => 9.5, 'after' => 12 ];
            $lines[]      = [ 't' => 'PROFILE', 'b' => true, 's' => 10.5, 'after' => 4 ];
            foreach ( wrc_cv_wrap( $cv['profile'] ) as $line ) {
                $lines[] = [ 't' => $line, 'b' => false, 's' => 9.5, 'after' => 2.5 ];
            }
            $lines[]      = [ 't' => '', 'b' => false, 's' => 4, 'after' => 1 ];
            $lines[]      = [ 't' => 'PROFESSIONAL EXPERIENCE', 'b' => true, 's' => 10.5, 'after' => 4 ];
            foreach ( $cv['experience'] as $entry ) {
                $lines[] = [ 't' => $entry['role'], 'b' => true, 's' => 10, 'after' => 1 ];
                $lines[] = [ 't' => $entry['org'], 'b' => false, 's' => 9.5, 'after' => 1 ];
                $lines[] = [ 't' => $entry['dates'], 'b' => false, 's' => 9, 'after' => 3 ];
                foreach ( $entry['bullets'] as $bullet ) {
                    foreach ( wrc_cv_wrap( '- ' . $bullet, 96 ) as $line ) {
                        $lines[] = [ 't' => $line, 'b' => false, 's' => 9.5, 'after' => 1.5 ];
                    }
                }
                $lines[] = [ 't' => '', 'b' => false, 's' => 3, 'after' => 1 ];
            }
            $lines[]      = [ 't' => 'EDUCATION', 'b' => true, 's' => 10.5, 'after' => 4 ];
            foreach ( $cv['education'] as $line ) {
                $lines[] = [ 't' => $line, 'b' => false, 's' => 9.5, 'after' => 2 ];
            }
            $lines[]      = [ 't' => '', 'b' => false, 's' => 4, 'after' => 1 ];
            $lines[]      = [ 't' => 'SKILLS', 'b' => true, 's' => 10.5, 'after' => 4 ];
            foreach ( wrc_cv_wrap( $cv['skills'] ) as $line ) {
                $lines[] = [ 't' => $line, 'b' => false, 's' => 9.5, 'after' => 1.5 ];
            }
            $lines[]      = [ 't' => '', 'b' => false, 's' => 4, 'after' => 1 ];
            $lines[]      = [ 't' => 'LANGUAGES', 'b' => true, 's' => 10.5, 'after' => 4 ];
            $lines[]      = [ 't' => 'Urdu (native), English (fluent), Pashto (fluent)', 'b' => false, 's' => 9.5, 'after' => 2 ];
            $lines[]      = [ 't' => '', 'b' => false, 's' => 4, 'after' => 1 ];
            $lines[]      = [ 't' => 'REFERENCES', 'b' => true, 's' => 10.5, 'after' => 4 ];
            $lines[]      = [ 't' => 'Available on request', 'b' => false, 's' => 9.5, 'after' => 2 ];

            $fileName = 'cv_' . substr( md5( $fullName . $job['title'] . $email ), 0, 12 ) . '.pdf';
            if ( @file_put_contents( $uploadDirectory . '/' . $fileName, wrc_cv_pdf( $lines ) ) === false ) {
                throw new RuntimeException( 'Could not write CV file ' . $fileName );
            }
            $totalCvs++;

            $attachment = 'uploads/files/' . $fileName;

            $message = implode( "\n", [
                'Position: ' . $job['title'] . ' (' . $job['department'] . ')',
                'Qualification: ' . $qualification,
                'Experience: ' . $band . ' years',
                'Cover Letter: ' . $cover,
                'Attachment (cv): ' . $attachment,
            ] );

            $insertApplication->execute( [
                $fullName,
                $email,
                $phone,
                'Job Application: ' . $job['title'],
                'career',
                $message,
                'new',
                $submittedAt,
                $attachment,
            ] );
            $totalApps++;
        }
    }

    $marker = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value, setting_group)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $marker->execute( [ WRC_CAREERS_MARKER, 'done', 'system' ] );

    $pdo->commit();

    echo "Careers data set applied: {$totalJobs} advert(s), {$totalApps} application(s), {$totalCvs} CV file(s).\n";
    echo 'Removed ' . (int) $deletedApplications . " application(s) that were already in the dashboard.\n";
} catch ( Throwable $error ) {
    if ( $pdo->inTransaction() ) {
        $pdo->rollBack();
    }
    throw $error;
}
