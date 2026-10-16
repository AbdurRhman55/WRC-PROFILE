USE wrc_website;

-- Our Work Hero Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('our_work_hero', 'Our Work', 'Creating Impact Through Community-Focused Action',
 '{"hero_image":"assets/images/our-work/our-work-hero.jpg","hero_alt":"Humanitarian Fieldwork in Pakistan","subtitle":"Through strategic community interventions and humanitarian partnership, WRC works across Pakistan to address vulnerability, build resilience, and deliver swift, dignified relief.","button1_text":"Explore Our Programs","button1_link":"#sectors","button2_text":"Let’s Work Together","button2_link":"get-involved/contribute-with-us.html"}',
 10, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Our Work Featured / Editorial Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('our_work_editorial', 'How We Ensure Effective Project Delivery', NULL,
 '{"label":"From Needs to Action","image":"assets/images/our-work/featured-work.jpg","image_alt":"WRC Field Team in action","description":"World Relief Committee applies a rigorous operational framework designed to ensure transparency, accountability, and maximum developmental impact in every community we serve.","features":[{"icon":"fa-solid fa-users-viewfinder","title":"Community-Centered","description":"Projects are co-designed and driven by community needs and local planning committees."},{"icon":"fa-solid fa-chart-line","title":"Evidence-Informed","description":"Continuous baseline assessments and field monitoring shape our intervention design."},{"icon":"fa-solid fa-handshake-angle","title":"Partnership-Driven","description":"We coordinate directly with local authorities, partners, and peer field agencies."},{"icon":"fa-solid fa-seedling","title":"Sustainable","description":"We establish community management committees to maintain local assets from day one."}]}',
 11, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Our Work Process Timeline Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('our_work_process', 'A Systematic Path to Lasting Change', 'Our Process',
 '{"subtitle":"WRC follows a structured seven-step lifecycle to manage and execute project activities successfully.","steps":[{"num":1,"title":"Assessment","description":"Vulnerability scans and local consultations."},{"num":2,"title":"Design","description":"Co-designing community intervention scope."},{"num":3,"title":"Partnership","description":"Formulating institutional alignments."},{"num":4,"title":"Implementation","description":"Mobilizing teams and executing field operations."},{"num":5,"title":"M&E","description":"Rigorous baseline tracking and field audits."},{"num":6,"title":"Learning","description":"Fostering adaptive updates and evaluations."},{"num":7,"title":"Impact","description":"Transition to community committee ownership."}]}',
 12, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Our Work 4 Pillars Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('our_work_pillars', 'Four Pillars of Sustainable Impact', 'Our Strategy',
 '{"subtitle":"We believe that meaningful humanitarian progress relies on structured development guidelines.","pillars":[{"num":"01","title":"People-First Focus","description":"Prioritizing the dignity and active voice of beneficiaries, ensuring that women, youth, and vulnerable populations are key co-designers."},{"num":"02","title":"Local Partnership","description":"Strengthening local committees, community elders, and public-private structures for shared responsibility and capacity transfer."},{"num":"03","title":"Structural Resilience","description":"Designing infrastructure and training models that endure seasonal disasters, environmental shifts, and economic challenges."},{"num":"04","title":"System Sustainability","description":"Establishing water user groups, maintenance funds, and technical handovers that ensure projects function for years ahead."}]}',
 13, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Our Work Featured Projects Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('our_work_featured_projects', 'Featured Program Projects', 'Action in the Field',
 '{"description":"Explore exemplary projects designed to showcase our operational capability. Real data and updates are added as verified."}',
 14, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Our Work CTA Banner
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('our_work_cta', 'Partner With Us for Lasting Change', NULL,
 '{"description":"Join hands with World Relief Committee to expand access to clean water, enhance health services, or support emergency relief campaigns across vulnerable communities in Pakistan.","button1_text":"Let’s Work Together","button1_link":"get-involved/contribute-with-us.html","button1_icon":"fa-solid fa-handshake","button2_text":"Contact Our Team","button2_link":"contact/index.html","button2_icon":"fa-solid fa-envelope"}',
 15, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);
