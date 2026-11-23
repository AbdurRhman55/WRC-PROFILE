-- =============================================
-- WRC Projects Migration Script
-- From: u817261572_db_OFOnJ2VM
-- To:   u817261572_wrcwebsite
-- Generated: 2026-11-23 07:43:20
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS `project_donors`;
DROP TABLE IF EXISTS `partner_projects`;
DROP TABLE IF EXISTS `project_media`;
DROP TABLE IF EXISTS `project_organizations`;
DROP TABLE IF EXISTS `project_categories`;
DROP TABLE IF EXISTS `project_subcategories`;
DROP TABLE IF EXISTS `projects_hero`;
DROP TABLE IF EXISTS `projects_dashboard`;
DROP TABLE IF EXISTS `projects_dashboard_metrics`;
DROP TABLE IF EXISTS `projects_dashboard_sectors`;
DROP TABLE IF EXISTS `projects_cta`;
DROP TABLE IF EXISTS `success_stories`;
DROP TABLE IF EXISTS `process_steps`;
DROP TABLE IF EXISTS `projects`;

CREATE TABLE `projects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','completed','upcoming') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget_amount` decimal(12,2) DEFAULT NULL,
  `budget_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PKR',
  `donors_count` int unsigned DEFAULT '0',
  `related_projects` json DEFAULT NULL,
  `beneficiaries_count` int unsigned DEFAULT '0',
  `progress_percent` tinyint unsigned DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `overview` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `objectives` json DEFAULT NULL,
  `target_groups` json DEFAULT NULL,
  `timeline` json DEFAULT NULL,
  `gallery_images` json DEFAULT NULL,
  `overview_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `hero_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `program_id` int unsigned DEFAULT NULL,
  `province_id` int unsigned DEFAULT NULL,
  `district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `organization_id` int unsigned DEFAULT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `subcategory_id` int unsigned DEFAULT NULL,
  `sort_order` tinyint unsigned DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_history` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `program_id` (`program_id`),
  KEY `province_id` (`province_id`),
  KEY `idx_projects_organization` (`organization_id`),
  KEY `idx_projects_category` (`category_id`),
  KEY `idx_projects_subcategory` (`subcategory_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`id`, `slug`, `title`, `status`, `start_date`, `end_date`, `budget_amount`, `budget_currency`, `donors_count`, `related_projects`, `beneficiaries_count`, `progress_percent`, `description`, `overview`, `objectives`, `target_groups`, `timeline`, `gallery_images`, `overview_image`, `location_details`, `hero_image`, `program_id`, `province_id`, `district`, `category`, `organization_id`, `category_id`, `subcategory_id`, `sort_order`, `is_active`, `show_in_history`, `created_at`, `updated_at`) VALUES
('87', 'kp-tevta-pc1-paramedic-training-nmd-2021', 'Paramedic Training in NMD – KP-TEVTA (PC-1)', 'completed', '2021-01-01', '2023-12-31', NULL, 'PKR', '0', NULL, '114', '100', 'Paramedic training delivered in NMD through KP-TEVTA (PC-1), serving 114 beneficiaries from 2021 to 2023.', 'Paramedic training delivered in NMD through KP-TEVTA (PC-1), serving 114 beneficiaries from 2021 to 2023.', NULL, NULL, NULL, NULL, '', 'NMD, Pakistan', 'assets/images/projects/health-bajaur.png', NULL, '1', 'NMD', 'Education', '1', '1', NULL, '1', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:39:52'),
('88', 'kp-tevta-nmd-paramedic-training-2021', 'Paramedic Training in NMD – KP-TEVTA (2021–2023)', 'completed', '2021-01-01', '2023-12-31', NULL, 'PKR', '0', NULL, '16', '100', 'Paramedic training delivered in NMD through KP-TEVTA (NMD), serving 16 beneficiaries from 2021 to 2023.', 'Paramedic training delivered in NMD through KP-TEVTA (NMD), serving 16 beneficiaries from 2021 to 2023.', NULL, NULL, NULL, NULL, NULL, 'NMD, Pakistan', 'assets/images/projects/health-services.jpg', NULL, '1', 'NMD', 'Education', '2', '2', NULL, '2', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('89', 'kp-tevta-nmd-paramedic-training-2022', 'Paramedic Training in NMD – KP-TEVTA (2022–2024)', 'completed', '2022-01-01', '2024-12-31', NULL, 'PKR', '0', NULL, '40', '100', 'Paramedic training delivered in NMD through KP-TEVTA (NMD), serving 40 beneficiaries from 2022 to 2024.', 'Paramedic training delivered in NMD through KP-TEVTA (NMD), serving 40 beneficiaries from 2022 to 2024.', NULL, NULL, NULL, NULL, NULL, 'NMD, Pakistan', 'assets/images/projects/health-dir.jpeg', NULL, '1', 'NMD', 'Education', '2', '2', NULL, '3', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('90', 'kp-tevta-skills-training-nmd-2023', 'Skills Training in NMD – KP-TEVTA (2023)', 'completed', '2023-01-01', '2023-12-31', NULL, 'PKR', '0', NULL, '140', '100', 'Skills training delivered in NMD through KP-TEVTA in 2023, serving 140 beneficiaries.', 'Skills training delivered in NMD through KP-TEVTA in 2023, serving 140 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'NMD, Pakistan', 'assets/images/projects/education-children.jpg', NULL, '1', 'NMD', 'Education', '3', '3', NULL, '4', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('91', 'fata-da-paramedic-training-nmd-2016-2017', 'Paramedic Training in NMD – FATA Development Authority (2016–2017)', 'completed', '2016-01-01', '2017-12-31', NULL, 'PKR', '0', NULL, '60', '100', 'Paramedic training delivered in NMD through FATA Development Authority from 2016 to 2017, serving 60 beneficiaries.', 'Paramedic training delivered in NMD through FATA Development Authority from 2016 to 2017, serving 60 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'NMD, Pakistan', 'assets/images/projects/medical-camp.png', NULL, '1', 'NMD', 'Education', '4', '4', NULL, '5', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('92', 'fata-da-paramedic-training-nmd-2016-2018', 'Paramedic Training in NMD – FATA Development Authority (2016–2018)', 'completed', '2016-01-01', '2018-12-31', NULL, 'PKR', '0', NULL, '70', '100', 'Paramedic training delivered in NMD through FATA Development Authority from 2016 to 2018, serving 70 beneficiaries.', 'Paramedic training delivered in NMD through FATA Development Authority from 2016 to 2018, serving 70 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'NMD, Pakistan', 'assets/images/projects/health-bajaur.png', NULL, '1', 'NMD', 'Education', '4', '4', NULL, '6', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('93', 'fata-da-paramedic-training-nmd-2018-2021', 'Paramedic Training in NMD – FATA Development Authority (2018–2021)', 'completed', '2018-01-01', '2021-12-31', NULL, 'PKR', '0', NULL, '30', '100', 'Paramedic training delivered in NMD through FATA Development Authority from 2018 to 2021, serving 30 beneficiaries.', 'Paramedic training delivered in NMD through FATA Development Authority from 2018 to 2021, serving 30 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'NMD, Pakistan', 'assets/images/projects/community-health.jpg', NULL, '1', 'NMD', 'Education', '4', '4', NULL, '7', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('94', 'islamic-relief-women-skills-khyber-2023', 'Women’s Empowerment Skills Training – Khyber (2023)', 'completed', '2023-01-01', '2023-12-31', NULL, 'PKR', '0', NULL, '50', '100', 'Skills training for women’s empowerment delivered in Khyber through Islamic Relief Pakistan in 2023, serving 50 beneficiaries.', 'Skills training for women’s empowerment delivered in Khyber through Islamic Relief Pakistan in 2023, serving 50 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Khyber, Pakistan', 'assets/images/projects/agriculture-kpk.jpg', NULL, '1', 'Khyber', 'Livelihood', '5', '5', NULL, '8', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('95', 'islamic-relief-women-skills-islamabad-rawalpindi-2024', 'Women’s Empowerment Skills Training – Islamabad & Rawalpindi (2024–2025)', 'completed', '2024-01-01', '2025-12-31', NULL, 'PKR', '0', NULL, '556', '100', 'Skills training for women’s empowerment delivered in Islamabad and Rawalpindi through Islamic Relief Pakistan from 2024 to 2025, serving 556 beneficiaries.', 'Skills training for women’s empowerment delivered in Islamabad and Rawalpindi through Islamic Relief Pakistan from 2024 to 2025, serving 556 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Islamabad & Rawalpindi, Pakistan', 'assets/images/projects/20140923_113150.jpg', NULL, '2', 'Islamabad & Rawalpindi', 'Livelihood', '5', '5', NULL, '9', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('96', 'islamic-relief-women-skills-islamabad-rawalpindi-2024-2', 'Women’s Empowerment Skills Training – Islamabad & Rawalpindi (Phase II, 2024–2025)', 'completed', '2024-01-01', '2025-12-31', NULL, 'PKR', '0', NULL, '738', '100', 'Skills training for women’s empowerment delivered in Islamabad and Rawalpindi through Islamic Relief Pakistan from 2024 to 2025, serving 738 beneficiaries.', 'Skills training for women’s empowerment delivered in Islamabad and Rawalpindi through Islamic Relief Pakistan from 2024 to 2025, serving 738 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Islamabad & Rawalpindi, Pakistan', 'assets/images/projects/20140923_113350.jpg', NULL, '2', 'Islamabad & Rawalpindi', 'Livelihood', '5', '5', NULL, '10', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('97', 'health-department-rhc-peshawar-2011-2013', 'Rural Health Centre – Peshawar (2011–2013)', 'completed', '2011-01-01', '2013-12-31', NULL, 'PKR', '0', NULL, '205000', '100', 'Health Department Rural Health Centre project in Peshawar from 2011 to 2013, serving 205,000 beneficiaries.', 'Health Department Rural Health Centre project in Peshawar from 2011 to 2013, serving 205,000 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Peshawar, Pakistan', 'assets/images/projects/medical-camp.png', NULL, '1', 'Peshawar', 'Health', '6', '6', NULL, '11', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('98', 'health-department-cat-d-hospital-malakand-2013-2024', 'Category-D Hospital – Malakand (2013–2024)', 'completed', '2013-01-01', '2024-12-31', NULL, 'PKR', '0', NULL, '652000', '100', 'Health Department Category-D Hospital project in Malakand from 2013 to 2024, serving 652,000 beneficiaries.', 'Health Department Category-D Hospital project in Malakand from 2013 to 2024, serving 652,000 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Malakand, Pakistan', 'assets/images/projects/hospital-malakand.jpg', NULL, '1', 'Malakand', 'Health', '6', '6', NULL, '12', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('99', 'health-department-rhc-swabi-2011-2024', 'Rural Health Centre – Swabi (2011–2024)', 'completed', '2011-01-01', '2024-12-31', NULL, 'PKR', '0', NULL, '143000', '100', 'Health Department Rural Health Centre project in Swabi from 2011 to 2024, serving 143,000 beneficiaries.', 'Health Department Rural Health Centre project in Swabi from 2011 to 2024, serving 143,000 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Swabi, Pakistan', 'assets/images/projects/health-services.jpg', NULL, '1', 'Swabi', 'Health', '6', '6', NULL, '13', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('100', 'smart-aid-emergency-response-kits-malakand-swat-2023', 'Emergency Response Kits – Malakand & Swat (2023)', 'completed', '2023-01-01', '2023-12-31', NULL, 'PKR', '0', NULL, '35000', '100', 'Emergency response kits distributed in Malakand and Swat through Smart Aid in 2023, serving 35,000 beneficiaries.', 'Emergency response kits distributed in Malakand and Swat through Smart Aid in 2023, serving 35,000 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Malakand & Swat, Pakistan', 'assets/images/projects/nowshera-flood.png', NULL, '1', 'Malakand & Swat', 'Emergency Response', '7', '7', NULL, '14', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('101', 'tdf-ramzan-food-distribution-khyber-2021', 'Ramzan Food Distribution – Khyber (2021)', 'completed', '2021-01-01', '2021-12-31', NULL, 'PKR', '0', NULL, '2000', '100', 'Ramzan food distribution delivered in Khyber through Turkiye Diyanet Foundation in 2021, serving 2,000 beneficiaries.', 'Ramzan food distribution delivered in Khyber through Turkiye Diyanet Foundation in 2021, serving 2,000 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Khyber, Pakistan', 'assets/images/projects/ramadan-packages.png', NULL, '1', 'Khyber', 'Food Security', '8', '8', NULL, '15', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:42:21'),
('102', 'tdf-ramzan-food-distribution-khyber-2022', 'Ramzan Food Distribution – Khyber (2022)', 'completed', '2022-01-01', '2022-12-31', NULL, 'PKR', '0', NULL, '2500', '100', 'Ramzan food distribution delivered in Khyber through Turkiye Diyanet Foundation in 2022, serving 2,500 beneficiaries.', 'Ramzan food distribution delivered in Khyber through Turkiye Diyanet Foundation in 2022, serving 2,500 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Khyber, Pakistan', 'assets/images/projects/ramadan-packages.png', NULL, '1', 'Khyber', 'Food Security', '8', '8', NULL, '16', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:42:21'),
('103', 'muslim-aid-hand-pump-mansehra-2016-2017', 'Hand Pump Project – Mansehra (2016–2017)', 'completed', '2016-01-01', '2017-12-31', NULL, 'PKR', '0', NULL, '2500', '100', 'Hand pump project delivered in Mansehra through Muslim Aid from 2016 to 2017, serving 2,500 beneficiaries.', 'Hand pump project delivered in Mansehra through Muslim Aid from 2016 to 2017, serving 2,500 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Mansehra, Pakistan', 'assets/images/projects/buner-wash.jpeg', NULL, '1', 'Mansehra', 'WASH', '9', '9', NULL, '17', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('104', 'phhsa-nfi-kits-di-khan-2022', 'Non-Food Item Kits – D.I. Khan (2022)', 'completed', '2022-01-01', '2022-12-31', NULL, 'PKR', '0', NULL, '500', '100', 'Non-food item kits delivered in D.I. Khan through PHHSA in 2022, serving 500 beneficiaries.', 'Non-food item kits delivered in D.I. Khan through PHHSA in 2022, serving 500 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'D.I. Khan, Pakistan', 'assets/images/projects/nowshera-flood.png', NULL, '1', 'D.I. Khan', 'Emergency Response', '10', '10', NULL, '18', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('105', 'islamic-relief-vocational-skills-islamabad-rawalpindi-2025', 'Vocational Skills Training – Islamabad & Rawalpindi (2025)', 'completed', '2025-01-01', '2025-12-31', NULL, 'PKR', '0', NULL, '1294', '100', 'Vocational skills training delivered in Islamabad and Rawalpindi through Islamic Relief Pakistan in 2025, serving 1,294 beneficiaries.', 'Vocational skills training delivered in Islamabad and Rawalpindi through Islamic Relief Pakistan in 2025, serving 1,294 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Islamabad & Rawalpindi, Pakistan', 'assets/images/projects/agriculture-kpk.jpg', NULL, '2', 'Islamabad & Rawalpindi', 'Livelihood', '5', '5', NULL, '19', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('106', 'zfa-ramadan-food-packs-buner-swabi-nowshera-2026', 'Ramadan Food Pack Distribution – Buner, Swabi & Nowshera (2026)', 'active', '2026-01-01', NULL, NULL, 'PKR', '0', NULL, '600', '0', 'Ramadan food pack distribution delivered in Buner, Swabi and Nowshera through Zakat Foundation of America in 2026, serving 600 beneficiaries.', 'Ramadan food pack distribution delivered in Buner, Swabi and Nowshera through Zakat Foundation of America in 2026, serving 600 beneficiaries.', NULL, NULL, NULL, NULL, NULL, 'Buner, Swabi & Nowshera, Pakistan', 'assets/images/projects/ramadan-packages.png', NULL, '1', 'Buner, Swabi & Nowshera', 'Food Security', '11', '11', NULL, '20', '1', '1', '2026-10-13 12:19:05', '2026-11-23 10:33:10'),
('108', 'smart-aid-solarization-cat-d-hospital-totakan-malakand-2023-2024', 'Solarization of Category-D Hospital Totakan – Malakand (2023–2024)', 'completed', '2023-01-01', '2024-12-31', NULL, 'PKR', '0', NULL, '1', '100', 'Solarization project implemented at Category-D Hospital Totakan, Malakand in partnership with Smart Aid (2023–2024), providing uninterrupted clean solar energy for hospital wards, emergency units, diagnostic services, and medical cold chains.', 'The Solarization of Category-D Hospital Totakan in Malakand represents a critical healthcare infrastructure initiative implemented by Welfare & Relief Committee (WRC) in partnership with Smart Aid from 2023 to 2024. The project installed a robust, reliable solar photovoltaic system designed to overcome recurring grid outages and ensure continuous 24/7 power for life-saving clinical equipment, emergency care units, labor rooms, and maternal and child healthcare services.', '[{\"title\": \"Uninterrupted Healthcare Delivery\", \"description\": \"Ensure continuous, 24/7 solar power backup for emergency wards, diagnostic laboratories, and essential life-support medical devices at Cat-D Hospital Totakan.\"}, {\"title\": \"Cold Chain & Vaccine Integrity\", \"description\": \"Guarantee unbroken solar-powered refrigeration for life-saving vaccines, serums, and temperature-sensitive emergency medicines.\"}, {\"title\": \"Clean Energy & Operational Sustainability\", \"description\": \"Reduce heavy reliance on expensive diesel generators and unstable utility grids, cutting operational fuel costs and lowering carbon emissions.\"}, {\"title\": \"Enhanced Emergency Readiness\", \"description\": \"Ensure lighting and critical equipment during nighttime emergencies, maternal deliveries, and local public health crises.\"}]', '[{\"icon\": \"fa-solid fa-hospital-user\", \"name\": \"Patients & Emergency Cases\"}, {\"icon\": \"fa-solid fa-person-breastfeeding\", \"name\": \"Mothers & Newborn Infants\"}, {\"icon\": \"fa-solid fa-user-doctor\", \"name\": \"Medical Officers & Hospital Staff\"}, {\"icon\": \"fa-solid fa-house-medical\", \"name\": \"Rural Communities of Totakan & Malakand\"}]', '[{\"date\": \"Q1 2023\", \"icon\": \"fa-solid fa-clipboard-check\", \"title\": \"Technical Energy Audit & Feasibility\", \"description\": \"Completed hospital load assessments, structural solar roof engineering reviews, and stakeholder coordination with health authorities.\"}, {\"date\": \"Q3 2023\", \"icon\": \"fa-solid fa-solar-panel\", \"title\": \"Procurement & Solar System Installation\", \"description\": \"Installed high-efficiency Tier-1 solar panels, industrial inverters, and durable battery storage systems tailored to hospital wards.\"}, {\"date\": \"Q1 2024\", \"icon\": \"fa-solid fa-bolt\", \"title\": \"Testing, Grid Integration & Commissioning\", \"description\": \"Conducted safety inspections, backup system load testing, and commissioned continuous solar power across emergency and clinical units.\"}, {\"date\": \"Q4 2024\", \"icon\": \"fa-solid fa-circle-check\", \"title\": \"Staff Training & Formal Handover\", \"description\": \"Trained hospital technicians on routine maintenance, completed monitoring audits, and successfully handed over the fully operational solar system.\"}]', NULL, NULL, 'Totakan, Malakand, Pakistan', 'assets/images/projects/solarization.png', NULL, '1', 'Malakand', 'Climate Resilience', '7', '12', NULL, '21', '1', '1', '2026-11-13 11:57:12', '2026-11-23 10:33:10');

-- -------------------------------------------
-- Table: project_donors
-- -------------------------------------------
DROP TABLE IF EXISTS `project_donors`;
CREATE TABLE `project_donors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int unsigned NOT NULL,
  `donor_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `donor_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `project_donors_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_donors` (`id`, `project_id`, `donor_name`, `donor_type`, `amount`, `sort_order`) VALUES
('26', '87', 'KP-TEVTA (PC-1)', 'Project Partner', NULL, '0'),
('27', '88', 'KP-TEVTA (NMD)', 'Project Partner', NULL, '0'),
('28', '89', 'KP-TEVTA (NMD)', 'Project Partner', NULL, '0'),
('30', '90', 'KP-TEVTA (Skills Training)', 'Project Partner', NULL, '0'),
('31', '91', 'FATA Development Authority', 'Project Partner', NULL, '0'),
('32', '92', 'FATA Development Authority', 'Project Partner', NULL, '0'),
('33', '93', 'FATA Development Authority', 'Project Partner', NULL, '0'),
('34', '105', 'Islamic Relief Pakistan', 'Project Partner', NULL, '0'),
('35', '95', 'Islamic Relief Pakistan', 'Project Partner', NULL, '0'),
('36', '96', 'Islamic Relief Pakistan', 'Project Partner', NULL, '0'),
('37', '94', 'Islamic Relief Pakistan', 'Project Partner', NULL, '0'),
('41', '98', 'Health Department', 'Project Partner', NULL, '0'),
('42', '97', 'Health Department', 'Project Partner', NULL, '0'),
('43', '99', 'Health Department', 'Project Partner', NULL, '0'),
('44', '100', 'Smart Aid', 'Project Partner', NULL, '0'),
('45', '101', 'Turkiye Diyanet Foundation', 'Project Partner', NULL, '0'),
('46', '102', 'Turkiye Diyanet Foundation', 'Project Partner', NULL, '0'),
('48', '103', 'Muslim Aid', 'Project Partner', NULL, '0'),
('49', '104', 'PHHSA', 'Project Partner', NULL, '0'),
('50', '106', 'Zakat Foundation of America (ZFA)', 'Project Partner', NULL, '0'),
('51', '108', 'Smart Aid', 'Project Partner', NULL, '0');

-- -------------------------------------------
-- Table: project_organizations
-- -------------------------------------------
DROP TABLE IF EXISTS `project_organizations`;
CREATE TABLE `project_organizations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_project_organizations_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_organizations` (`id`, `name`, `slug`, `contact_email`, `website_url`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', 'KP-TEVTA (PC-1)', 'kp-tevta-pc-1', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('2', 'KP-TEVTA (NMD)', 'kp-tevta-nmd', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('3', 'KP-TEVTA (Skills Training)', 'kp-tevta-skills-training', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('4', 'FATA Development Authority', 'fata-development-authority', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('5', 'Islamic Relief Pakistan', 'islamic-relief-pakistan', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('6', 'Health Department', 'health-department', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('7', 'Smart Aid', 'smart-aid', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('8', 'Turkiye Diyanet Foundation', 'turkiye-diyanet-foundation', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('9', 'Muslim Aid', 'muslim-aid', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('10', 'PHHSA', 'phhsa', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('11', 'Zakat Foundation of America (ZFA)', 'zakat-foundation-of-america-zfa', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('12', 'Welfare & Relief Committee (WRC)', 'welfare-relief-committee-wrc', NULL, NULL, '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10');

-- -------------------------------------------
-- Table: project_categories
-- -------------------------------------------
DROP TABLE IF EXISTS `project_categories`;
CREATE TABLE `project_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sector` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'General',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_project_categories_slug` (`organization_id`,`slug`),
  KEY `idx_project_categories_org` (`organization_id`),
  CONSTRAINT `fk_project_categories_org` FOREIGN KEY (`organization_id`) REFERENCES `project_organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_categories` (`id`, `organization_id`, `name`, `slug`, `sector`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', '1', 'Education', 'education', 'Education', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('2', '2', 'Education', 'education', 'Education', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('3', '3', 'Education', 'education', 'Education', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('4', '4', 'Education', 'education', 'Education', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('5', '5', 'Livelihood', 'livelihood', 'Livelihood', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('6', '6', 'Health', 'health', 'Health', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('7', '7', 'Emergency Response', 'emergency-response', 'Emergency Response', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('8', '8', 'Food Security', 'food-security', 'Food Security', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:42:21'),
('9', '9', 'WASH', 'wash', 'WASH', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('10', '10', 'Emergency Response', 'emergency-response', 'Emergency Response', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('11', '11', 'Food Security', 'food-security', 'Food Security', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10'),
('12', '7', 'Climate Resilience', 'climate-resilience', 'Climate Resilience', '1', '0', '2026-11-23 10:33:10', '2026-11-23 10:33:10');

-- -------------------------------------------
-- Table: project_subcategories
-- -------------------------------------------
DROP TABLE IF EXISTS `project_subcategories`;
CREATE TABLE `project_subcategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_project_subcategories_slug` (`category_id`,`slug`),
  KEY `idx_project_subcategories_category` (`category_id`),
  CONSTRAINT `fk_project_subcategories_category` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (empty table)

-- -------------------------------------------
-- Table: projects_hero
-- -------------------------------------------
DROP TABLE IF EXISTS `projects_hero`;
CREATE TABLE `projects_hero` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `hero_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `section_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `section_subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `button1_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button1_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button2_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button2_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `eyebrow` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro1` text COLLATE utf8mb4_unicode_ci,
  `intro2` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects_hero` (`id`, `hero_image`, `section_title`, `section_subtitle`, `subtitle`, `button1_text`, `button1_link`, `button2_text`, `button2_link`, `is_active`, `created_at`, `updated_at`, `eyebrow`, `intro1`, `intro2`) VALUES
('1', 'assets/images/hero/projects-hero.jpg', 'Delivering Projects, Creating Lasting Impact', '', '', 'Active Projects', '#active', 'View Dashboard', '#dashboard', '1', '2026-09-02 13:38:27', '2026-11-18 16:02:31', 'Our Portfolio', 'Explore WRC\'s portfolio of humanitarian and development initiatives implemented across Pakistan in collaboration with government departments, institutional partners, and development organizations.', 'Our portfolio highlights our experience in health, WASH, food and relief distribution, and skill development. Each project reflects our commitment to addressing community needs through practical interventions, effective coordination, and responsible implementation.');

-- -------------------------------------------
-- Table: projects_dashboard
-- -------------------------------------------
DROP TABLE IF EXISTS `projects_dashboard`;
CREATE TABLE `projects_dashboard` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `section_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `section_subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects_dashboard` (`id`, `section_title`, `section_subtitle`, `subtitle`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'Project Dashboard', '', 'Real-time overview of our humanitarian impact and operational reach across Pakistan.', '1', '2026-09-02 13:38:27', '2026-09-02 13:38:27');

-- -------------------------------------------
-- Table: projects_dashboard_metrics
-- -------------------------------------------
DROP TABLE IF EXISTS `projects_dashboard_metrics`;
CREATE TABLE `projects_dashboard_metrics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dashboard_id` int unsigned NOT NULL,
  `metric_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `metric_color` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `metric_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `metric_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `dashboard_id` (`dashboard_id`),
  CONSTRAINT `projects_dashboard_metrics_ibfk_1` FOREIGN KEY (`dashboard_id`) REFERENCES `projects_dashboard` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects_dashboard_metrics` (`id`, `dashboard_id`, `metric_icon`, `metric_color`, `metric_label`, `metric_value`, `sort_order`) VALUES
('1', '1', 'fa-solid fa-users', 'brandRed', 'Total Beneficiaries', '1,046,209', '0'),
('2', '1', 'fa-solid fa-dollar-sign', 'gold-400', 'Budget Utilized', '$2.4M', '1'),
('3', '1', 'fa-solid fa-handshake', 'teal-500', 'Partner Organizations', '12', '2'),
('4', '1', 'fa-solid fa-map-location-dot', 'blue-400', 'Districts Covered', '28', '3');

-- -------------------------------------------
-- Table: projects_dashboard_sectors
-- -------------------------------------------
DROP TABLE IF EXISTS `projects_dashboard_sectors`;
CREATE TABLE `projects_dashboard_sectors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dashboard_id` int unsigned NOT NULL,
  `sector_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sector_color` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sector_count` int DEFAULT '0',
  `sector_percent` int DEFAULT '0',
  `sector_gradient` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sort_order` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `dashboard_id` (`dashboard_id`),
  CONSTRAINT `projects_dashboard_sectors_ibfk_1` FOREIGN KEY (`dashboard_id`) REFERENCES `projects_dashboard` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects_dashboard_sectors` (`id`, `dashboard_id`, `sector_name`, `sector_color`, `sector_count`, `sector_percent`, `sector_gradient`, `sort_order`) VALUES
('1', '1', 'Emergency Response', 'brandRed', '8', '100', 'from-brandRed to-red-400', '0'),
('2', '1', 'WASH & Water', 'teal-500', '6', '75', 'from-teal-500 to-teal-400', '1'),
('3', '1', 'Health', 'blue-500', '5', '62', 'from-blue-500 to-blue-400', '2'),
('4', '1', 'Education', 'gold-400', '3', '37', 'from-gold-400 to-yellow-400', '3'),
('5', '1', 'Food Security', 'orange-500', '2', '25', 'from-orange-500 to-orange-400', '4');

-- -------------------------------------------
-- Table: projects_cta
-- -------------------------------------------
DROP TABLE IF EXISTS `projects_cta`;
CREATE TABLE `projects_cta` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `section_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `section_subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button1_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button1_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button2_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `button2_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects_cta` (`id`, `section_title`, `section_subtitle`, `description`, `icon`, `button1_text`, `button1_link`, `button2_text`, `button2_link`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'Have a Project Proposal?', '', 'We welcome collaboration with local organizations, donors, and government agencies to expand our humanitarian reach across Pakistan.', 'fa-solid fa-lightbulb', 'Let’s Work Together', 'get-involved/contribute-with-us.php', 'Contact Us', 'contact/index.html', '1', '2026-09-02 13:38:27', '2026-10-16 14:07:39');

-- -------------------------------------------
-- Table: project_media
-- -------------------------------------------
DROP TABLE IF EXISTS `project_media`;
CREATE TABLE `project_media` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project_slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `context` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_order` (`project_slug`,`sort_order`),
  KEY `active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_media` (`id`, `project_slug`, `image_path`, `caption`, `context`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'kp-tevta-pc1-paramedic-training-nmd-2021', 'assets/images/projects/health-bajaur.png', 'Field documentation — Paramedic Training in NMD – KP-TEVTA (PC-1)', 'This photograph documents medical services, health screening and patient care delivered under Paramedic Training in NMD – KP-TEVTA (PC-1) in NMD, in partnership with KP-TEVTA (PC-1).', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('2', 'kp-tevta-nmd-paramedic-training-2021', 'assets/images/projects/health-services.jpg', 'Field documentation — Paramedic Training in NMD – KP-TEVTA (2021–2023)', 'This photograph documents medical services, health screening and patient care delivered under Paramedic Training in NMD – KP-TEVTA (2021–2023) in NMD, in partnership with KP-TEVTA (NMD).', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('3', 'kp-tevta-nmd-paramedic-training-2022', 'assets/images/projects/health-dir.jpeg', 'Field documentation — Paramedic Training in NMD – KP-TEVTA (2022–2024)', 'This photograph documents medical services, health screening and patient care delivered under Paramedic Training in NMD – KP-TEVTA (2022–2024) in NMD, in partnership with KP-TEVTA (NMD).', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('4', 'kp-tevta-skills-training-nmd-2023', 'assets/images/projects/education-children.jpg', 'Field documentation — Skills Training in NMD – KP-TEVTA (2023)', 'This photograph documents education support, training sessions and learning activities delivered under Skills Training in NMD – KP-TEVTA (2023) in NMD, in partnership with KP-TEVTA (Skills Training).', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('5', 'fata-da-paramedic-training-nmd-2016-2017', 'assets/images/projects/medical-camp.png', 'Field documentation — Paramedic Training in NMD – FATA Development Authority (2016–2017)', 'This photograph documents medical services, health screening and patient care delivered under Paramedic Training in NMD – FATA Development Authority (2016–2017) in NMD, in partnership with FATA Development Authority.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('6', 'fata-da-paramedic-training-nmd-2016-2018', 'assets/images/projects/health-bajaur.png', 'Field documentation — Paramedic Training in NMD – FATA Development Authority (2016–2018)', 'This photograph documents medical services, health screening and patient care delivered under Paramedic Training in NMD – FATA Development Authority (2016–2018) in NMD, in partnership with FATA Development Authority.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('7', 'fata-da-paramedic-training-nmd-2018-2021', 'assets/images/projects/community-health.jpg', 'Field documentation — Paramedic Training in NMD – FATA Development Authority (2018–2021)', 'This photograph documents medical services, health screening and patient care delivered under Paramedic Training in NMD – FATA Development Authority (2018–2021) in NMD, in partnership with FATA Development Authority.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('8', 'islamic-relief-women-skills-khyber-2023', 'assets/images/projects/agriculture-kpk.jpg', 'Field documentation — Women’s Empowerment Skills Training – Khyber (2023)', 'This photograph documents skills training, enterprise support and livelihood activities delivered under Women’s Empowerment Skills Training – Khyber (2023) in Khyber, in partnership with Islamic Relief Pakistan.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('9', 'islamic-relief-women-skills-islamabad-rawalpindi-2024', 'assets/images/projects/20140923_113150.jpg', 'Field documentation — Women’s Empowerment Skills Training – Islamabad & Rawalpindi (2024–2025)', 'This photograph documents skills training, enterprise support and livelihood activities delivered under Women’s Empowerment Skills Training – Islamabad & Rawalpindi (2024–2025) in Islamabad & Rawalpindi, in partnership with Islamic Relief Pakistan.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('10', 'islamic-relief-women-skills-islamabad-rawalpindi-2024-2', 'assets/images/projects/20140923_113350.jpg', 'Field documentation — Women’s Empowerment Skills Training – Islamabad & Rawalpindi (Phase II, 2024–2025)', 'This photograph documents skills training, enterprise support and livelihood activities delivered under Women’s Empowerment Skills Training – Islamabad & Rawalpindi (Phase II, 2024–2025) in Islamabad & Rawalpindi, in partnership with Islamic Relief Pakistan.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('11', 'health-department-rhc-peshawar-2011-2013', 'assets/images/projects/medical-camp.png', 'Field documentation — Rural Health Centre – Peshawar (2011–2013)', 'This photograph documents medical services, health screening and patient care delivered under Rural Health Centre – Peshawar (2011–2013) in Peshawar, in partnership with Health Department.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('12', 'health-department-cat-d-hospital-malakand-2013-2024', 'assets/images/projects/hospital-malakand.jpg', 'Field documentation — Category-D Hospital – Malakand (2013–2024)', 'This photograph documents medical services, health screening and patient care delivered under Category-D Hospital – Malakand (2013–2024) in Malakand, in partnership with Health Department.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('13', 'health-department-rhc-swabi-2011-2024', 'assets/images/projects/health-services.jpg', 'Field documentation — Rural Health Centre – Swabi (2011–2024)', 'This photograph documents medical services, health screening and patient care delivered under Rural Health Centre – Swabi (2011–2024) in Swabi, in partnership with Health Department.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('14', 'smart-aid-emergency-response-kits-malakand-swat-2023', 'assets/images/projects/nowshera-flood.png', 'Field documentation — Emergency Response Kits – Malakand & Swat (2023)', 'This photograph documents emergency relief, protective kits and rapid response assistance delivered under Emergency Response Kits – Malakand & Swat (2023) in Malakand & Swat, in partnership with Smart Aid.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('15', 'tdf-ramzan-food-distribution-khyber-2021', 'assets/images/projects/ramadan-packages.png', 'Field documentation — Ramzan Food Distribution – Khyber (2021)', 'This photograph documents food distribution and nutrition support for families delivered under Ramzan Food Distribution – Khyber (2021) in Khyber, in partnership with Turkiye Diyanet Foundation.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('16', 'tdf-ramzan-food-distribution-khyber-2022', 'assets/images/projects/ramadan-packages.png', 'Field documentation — Ramzan Food Distribution – Khyber (2022)', 'This photograph documents food distribution and nutrition support for families delivered under Ramzan Food Distribution – Khyber (2022) in Khyber, in partnership with Turkiye Diyanet Foundation.', '1', '1', '2026-11-12 09:32:31', '2026-11-12 09:32:31'),
('17', 'muslim-aid-hand-pump-mansehra-2016-2017', 'assets/images/projects/buner-wash.jpeg', 'Field documentation — Hand Pump Project – Mansehra (2016–2017)', 'This photograph documents clean water, sanitation infrastructure and hygiene promotion delivered under Hand Pump Project – Mansehra (2016–2017) in Mansehra, in partnership with Muslim Aid.', '1', '1', '2026-11-12 09:32:32', '2026-11-12 09:32:32'),
('18', 'phhsa-nfi-kits-di-khan-2022', 'assets/images/projects/nowshera-flood.png', 'Field documentation — Non-Food Item Kits – D.I. Khan (2022)', 'This photograph documents emergency relief, protective kits and rapid response assistance delivered under Non-Food Item Kits – D.I. Khan (2022) in D.I. Khan, in partnership with PHHSA.', '1', '1', '2026-11-12 09:32:32', '2026-11-12 09:32:32'),
('19', 'islamic-relief-vocational-skills-islamabad-rawalpindi-2025', 'assets/images/projects/agriculture-kpk.jpg', 'Field documentation — Vocational Skills Training – Islamabad & Rawalpindi (2025)', 'This photograph documents skills training, enterprise support and livelihood activities delivered under Vocational Skills Training – Islamabad & Rawalpindi (2025) in Islamabad & Rawalpindi, in partnership with Islamic Relief Pakistan.', '1', '1', '2026-11-12 09:32:32', '2026-11-12 09:32:32'),
('20', 'zfa-ramadan-food-packs-buner-swabi-nowshera-2026', 'assets/images/projects/ramadan-packages.png', 'Field documentation — Ramadan Food Pack Distribution – Buner, Swabi & Nowshera (2026)', 'This photograph documents food distribution and nutrition support for families delivered under Ramadan Food Pack Distribution – Buner, Swabi & Nowshera (2026) in Buner, Swabi & Nowshera, in partnership with Zakat Foundation of America (ZFA).', '1', '1', '2026-11-12 09:32:32', '2026-11-12 09:32:32'),
('23', 'smart-aid-solarization-cat-d-hospital-totakan-malakand-2023-2024', 'assets/images/projects/solarization.png', 'Field documentation — Solarization of Category-D Hospital Totakan – Malakand (2023–2024)', 'This photograph documents medical services, health screening and patient care delivered under Solarization of Category-D Hospital Totakan – Malakand (2023–2024) in Malakand, in partnership with Smart Aid.', '1', '1', '2026-11-13 11:59:05', '2026-11-13 11:59:05');

-- -------------------------------------------
-- Table: partner_projects
-- -------------------------------------------
DROP TABLE IF EXISTS `partner_projects`;
CREATE TABLE `partner_projects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int unsigned NOT NULL,
  `project_slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_project` (`partner_id`,`project_slug`),
  KEY `project_slug` (`project_slug`),
  KEY `partner_id` (`partner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `partner_projects` (`id`, `partner_id`, `project_slug`, `sort_order`, `created_at`) VALUES
('1', '17', 'kp-tevta-pc1-paramedic-training-nmd-2021', '87', '2026-11-12 09:32:31'),
('2', '16', 'kp-tevta-nmd-paramedic-training-2021', '88', '2026-11-12 09:32:31'),
('3', '16', 'kp-tevta-nmd-paramedic-training-2022', '89', '2026-11-12 09:32:31'),
('4', '18', 'kp-tevta-skills-training-nmd-2023', '90', '2026-11-12 09:32:31'),
('5', '14', 'fata-da-paramedic-training-nmd-2016-2017', '91', '2026-11-12 09:32:31'),
('6', '14', 'fata-da-paramedic-training-nmd-2016-2018', '92', '2026-11-12 09:32:31'),
('7', '14', 'fata-da-paramedic-training-nmd-2018-2021', '93', '2026-11-12 09:32:31'),
('12', '5', 'health-department-cat-d-hospital-malakand-2013-2024', '98', '2026-11-12 09:32:31'),
('13', '5', 'health-department-rhc-peshawar-2011-2013', '97', '2026-11-12 09:32:31'),
('14', '5', 'health-department-rhc-swabi-2011-2024', '99', '2026-11-12 09:32:31'),
('15', '21', 'smart-aid-emergency-response-kits-malakand-swat-2023', '100', '2026-11-12 09:32:31'),
('16', '22', 'tdf-ramzan-food-distribution-khyber-2021', '101', '2026-11-12 09:32:31'),
('17', '22', 'tdf-ramzan-food-distribution-khyber-2022', '102', '2026-11-12 09:32:31'),
('18', '19', 'muslim-aid-hand-pump-mansehra-2016-2017', '103', '2026-11-12 09:32:31'),
('19', '20', 'phhsa-nfi-kits-di-khan-2022', '104', '2026-11-12 09:32:31'),
('20', '23', 'zfa-ramadan-food-packs-buner-swabi-nowshera-2026', '106', '2026-11-12 09:32:31'),
('22', '15', 'islamic-relief-women-skills-khyber-2023', '94', '2026-11-12 09:41:19'),
('23', '15', 'islamic-relief-women-skills-islamabad-rawalpindi-2024', '95', '2026-11-12 09:41:19'),
('24', '15', 'islamic-relief-women-skills-islamabad-rawalpindi-2024-2', '96', '2026-11-12 09:41:19'),
('25', '15', 'islamic-relief-vocational-skills-islamabad-rawalpindi-2025', '105', '2026-11-12 09:41:19'),
('29', '21', 'smart-aid-solarization-cat-d-hospital-totakan-malakand-2023-2024', '108', '2026-11-13 11:59:05');

-- -------------------------------------------
-- Table: success_stories
-- -------------------------------------------
DROP TABLE IF EXISTS `success_stories`;
CREATE TABLE `success_stories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `person_name` varchar(255) NOT NULL,
  `person_title` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `quote` text NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `author_image` varchar(500) DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `sector_badge` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `success_stories` (`id`, `person_name`, `person_title`, `location`, `quote`, `image_path`, `author_image`, `sector`, `sector_badge`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('1', 'Zainab Bibi', 'Community Beneficiary', 'Peshawar, KP', 'Before WRC installed the water well, my children walked 3km each day for water. Now they go to school instead. Our family\'s health has completely transformed.', 'assets/images/gallery/9.JPG', '', 'wash', 'WASH', '1', '1', '2026-08-31 15:59:39', '2026-10-29 09:12:38'),
('2', 'Ahmed Khan', 'Farmer, Sindh', 'Sukkur, Sindh', 'When the floods destroyed our crops, WRC food packs kept my family alive for three months. They didn\'t just bring food ?????? they brought hope back to our village.', 'assets/images/projects/flood-swat.jpg', NULL, 'Food Security', 'Food Security', '1', '2', '2026-08-31 15:59:39', '2026-10-29 09:08:37'),
('3', 'Fatima Noor', 'Mother of 3, Punjab', 'Lahore, Punjab', 'My daughter was about to drop out of school because we couldn\'t afford books. WRC gave her a complete kit and now she\'s top of her class. Education changes everything.', 'assets/images/gallery/11.JPG', NULL, 'Education', 'Education', '1', '3', '2026-08-31 15:59:39', '2026-10-29 09:08:37');

-- -------------------------------------------
-- Table: process_steps
-- -------------------------------------------
DROP TABLE IF EXISTS `process_steps`;
CREATE TABLE `process_steps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `step_number` tinyint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `color_class` varchar(50) DEFAULT 'text-brandRed',
  `dot_color` varchar(50) DEFAULT 'bg-brandRed',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `process_steps` (`id`, `step_number`, `title`, `description`, `color_class`, `dot_color`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('194', '1', 'Funding Opportunity Identification', 'We proactively identify and assess funding opportunities, including donor programmes, government grants, and strategic partnerships. Each opportunity is evaluated against community needs, organizational priorities, technical capacity, and eligibility requirements.', 'text-brandRed', 'bg-brandRed', '1', '1', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('195', '2', 'Proposal Development', 'Our technical and financial teams work collaboratively to develop evidence-based proposals with clearly defined objectives, measurable outcomes, implementation strategies, realistic budgets, and appropriate monitoring frameworks.', 'text-brandRed', 'bg-brandRed', '1', '2', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('196', '3', 'Project Award & Agreement', 'Upon project approval, we formalize agreements with donors and relevant stakeholders, establish roles and responsibilities, review contractual obligations, and ensure alignment with applicable regulatory and donor compliance requirements.', 'text-brandRed', 'bg-brandRed', '1', '3', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('197', '4', 'Project Planning & Mobilization', 'We translate approved proposals into actionable implementation plans, defining project scope, timelines, resource requirements, procurement arrangements, risk management measures, and stakeholder responsibilities. Project teams and operational resources are mobilized accordingly.', 'text-slate-700', 'bg-slate-700', '1', '4', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('198', '5', 'Project Implementation', 'We execute project activities through coordinated field operations, technical oversight, and active engagement with communities, government departments, and implementation partners. Our focus remains on quality delivery, efficient resource utilization, and achieving agreed objectives.', 'text-slate-700', 'bg-slate-700', '1', '5', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('199', '6', 'Monitoring, Evaluation & Quality Assurance', 'We systematically monitor project progress against established indicators, assess performance and outcomes, conduct field-level reviews, and identify areas requiring improvement. Regular assessments enable timely corrective actions and help maintain implementation quality.', 'text-slate-700', 'bg-slate-700', '1', '6', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('200', '7', 'Reporting, Financial Accountability & Transparency', 'We maintain comprehensive records of project activities, expenditures, achievements, and challenges. Periodic technical and financial reports, expenditure tracking, documentation, and audit-ready records ensure accountability to donors, partners, and relevant stakeholders.', 'text-slate-800', 'bg-slate-700', '1', '7', '2026-11-23 11:39:19', '2026-11-23 11:39:19'),
('201', '8', 'Project Closure & Sustainability', 'At project completion, we conduct final assessments, reconcile accounts, document lessons learned, finalize deliverables, and facilitate the formal handover of relevant assets and responsibilities. We prioritize sustainability measures to support the continued benefits of project interventions beyond the funding period.', 'text-slate-800', 'bg-slate-700', '1', '8', '2026-11-23 11:39:19', '2026-11-23 11:39:19');

SET FOREIGN_KEY_CHECKS = 1;
