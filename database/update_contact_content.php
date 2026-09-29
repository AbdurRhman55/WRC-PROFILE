<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$content = [
    'offices' => [
        [
            'icon' => 'fa-building',
            'name' => 'Head Office',
            'badge' => 'Main',
            'address' => 'Flat#1, 3rd Floor, Shami Flats near Marbella Wedding Hall, Warsak Road, Peshawar, Pakistan',
            'phone' => '+92-91-1234567',
            'email' => 'info@wrc.org.pk',
            'hours' => 'Mon-Fri: 9:00 AM - 5:00 PM'
        ],
        [
            'icon' => 'fa-building',
            'name' => 'Regional Office - Islamabad',
            'badge' => '',
            'address' => 'Block F, G-8/1, Islamabad, Pakistan',
            'phone' => '+92-51-1234567',
            'email' => 'islamabad@wrc.org.pk',
            'hours' => 'Mon-Fri: 9:00 AM - 5:00 PM'
        ],
        [
            'icon' => 'fa-building',
            'name' => 'Field Office - Swat',
            'badge' => '',
            'address' => 'Green Chowk, Mingora, Swat, KPK, Pakistan',
            'phone' => '+92-943-1234567',
            'email' => 'swat@wrc.org.pk',
            'hours' => 'Mon-Fri: 8:00 AM - 4:00 PM'
        ]
    ],
    'subjects' => ['General Inquiry', 'Partnership Proposal', 'Donation Inquiry', 'Media Request', 'Program Information', 'Other'],
    'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3319.4678854446416!2d71.5818228152063!3d34.02264878069695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38dfbfd07891722f%3A0x6059515c3bdb02b6!2sPeshawar%2C%20Khyber%20Pakhtunkhwa%2C%20Pakistan!5e0!3m2!1sen!2s!4v1683058882431!5m2!1sen!2s" class="w-full h-full border-0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
    'contact_info' => [
        ['icon' => 'fa-phone', 'label' => 'Phone', 'value' => '+92-91-1234567'],
        ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => 'info@wrc.org.pk'],
        ['icon' => 'fa-location-dot', 'label' => 'Head Office', 'value' => 'Flat#1, 3rd Floor, Shami Flats, Warsak Road, Peshawar']
    ],
    'offices_title' => 'Our Offices',
    'offices_subtitle' => 'Visit Us',
    'offices_description' => 'Visit us at any of our offices across Pakistan. Our doors are open for inquiries, partnerships, and community engagement.',
    'form_title' => 'Send a Message',
    'form_subtitle' => 'Contact Us',
    'form_description' => 'Have a question or want to learn more about our work? Fill out the form and our team will get back to you within 2 business days.',
    'feedback' => [
        'title' => 'Feedback & Complaints',
        'subtitle' => 'Your Voice Matters',
        'description' => 'Help us improve our services by sharing your experience or filing a complaint.',
        'categories' => ['General Feedback', 'Service Quality', 'Staff Conduct', 'Suggestion for Improvement', 'Complaint']
    ],
    'whistleblower' => [
        'title' => 'Confidential Reporting',
        'description' => 'If you witness corruption, fraud, harassment, or any unethical behavior within WRC, report it securely. Your identity is protected under our Whistleblower Protection Policy.',
        'notice' => 'All reports are encrypted and handled confidentially by our Compliance Team.',
        'report_types' => ['Corruption', 'Fraud', 'Harassment', 'Misuse of Funds', 'Child Abuse / Exploitation', 'Other Ethical Concern']
    ]
];

global $pdo;
$stmt = $pdo->prepare("UPDATE contact_content SET section_title = ?, section_subtitle = ?, content = ? WHERE id = 1");
$stmt->execute(['Contact Information', 'Get in touch with our team', json_encode($content)]);

echo "Contact content updated successfully.\n";
echo "JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
