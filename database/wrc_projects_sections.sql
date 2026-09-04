USE wrc_website;

-- Projects Listing Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('projects_hero', 'Our Projects Portfolio', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Projects","subtitle":"Discover our active and completed humanitarian projects transforming communities across Pakistan.","button1_text":"Active Projects","button1_link":"#active","button2_text":"View Dashboard","button2_link":"#dashboard"}',
 30, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Projects Dashboard Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('projects_dashboard', 'Project Dashboard', NULL,
 '{"subtitle":"Real-time overview of our humanitarian impact and operational reach across Pakistan.","metrics":[{"icon":"fa-solid fa-users","color":"brandRed","value":"85,000+","label":"Total Beneficiaries"},{"icon":"fa-solid fa-dollar-sign","color":"gold-400","value":"$2.4M","label":"Budget Utilized"},{"icon":"fa-solid fa-handshake","color":"teal-500","value":"12","label":"Partner Organizations"},{"icon":"fa-solid fa-map-location-dot","color":"blue-400","value":"28","label":"Districts Covered"}],"sectors":[{"name":"Emergency Response","count":8,"percent":100,"color":"brandRed","gradient":"from-brandRed to-red-400"},{"name":"WASH & Water","count":6,"percent":75,"color":"teal-500","gradient":"from-teal-500 to-teal-400"},{"name":"Health","count":5,"percent":62,"color":"blue-500","gradient":"from-blue-500 to-blue-400"},{"name":"Education","count":3,"percent":37,"color":"gold-400","gradient":"from-gold-400 to-yellow-400"},{"name":"Food Security","count":2,"percent":25,"color":"orange-500","gradient":"from-orange-500 to-orange-400"}]}',
 31, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Projects CTA Section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('projects_cta', 'Have a Project Proposal?', NULL,
 '{"description":"We welcome collaboration with local organizations, donors, and government agencies to expand our humanitarian reach across Pakistan.","icon":"fa-solid fa-lightbulb","button1_text":"Contribute Now","button1_link":"get-involved/contribute-with-us.html","button1_icon":"fa-solid fa-handshake","button2_text":"Contact Us","button2_link":"contact/index.html","button2_icon":"fa-solid fa-envelope"}',
 32, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Causes / Campaigns Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('causes_hero', 'Your Donation Changes Lives', 'Live Campaigns',
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Projects","subtitle":"Join thousands of donors supporting clean water, emergency food, and orphan education across Pakistan. Every rupee counts.","button1_text":"Donate Now","button1_link":"#campaigns","button1_icon":"fa-solid fa-heart","button2_text":"See Our Impact","button2_link":"#impact","button2_icon":"fa-solid fa-chart-line"}',
 33, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Causes / Campaigns Impact Stats
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('causes_impact', 'Campaign Impact', 'Our Reach',
 '{"subtitle":"Together with our donors, we are making a measurable difference in communities across Pakistan.","stats":[{"icon":"fa-solid fa-coins","value":"PKR 10M+","label":"Total Raised"},{"icon":"fa-solid fa-hand-holding-heart","value":"4,500+","label":"Total Donors"},{"icon":"fa-solid fa-users","value":"950+","label":"Families Helped"},{"icon":"fa-solid fa-child","value":"200+","label":"Children Educated"}]}',
 34, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Single Project Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('project_detail_hero', 'Project Detail', NULL,
 '{"subtitle":"Providing immediate life-saving assistance to flood-affected communities across Swat Valley, Khyber Pakhtunkhwa, Pakistan."}',
 35, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);
