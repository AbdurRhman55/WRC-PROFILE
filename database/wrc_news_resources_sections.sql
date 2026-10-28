USE wrc_website;

-- NEWS HERO
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('news_hero', 'News & Media Center', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC News","subtitle":"Stay updated with the latest news, press releases, events, and media from Welfare Relief Committee across Pakistan.","button1_text":"Latest News","button1_link":"#news","button2_text":"Press Releases","button2_link":"#press"}',
 60, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- RESOURCES HERO
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('resources_hero', 'Resources & Publications', 'Knowledge Hub',
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Resources","subtitle":"Access our annual reports, financial statements, policies, and downloadable materials to learn more about our work and impact.","button1_text":"Browse Publications","button1_link":"#publications","button2_text":"Quick Downloads","button2_link":"#downloads","stats":[{"value":"12+","label":"Publications"},{"value":"5","label":"Policy Documents"},{"value":"4","label":"Report Categories"}]}',
 61, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- GET INVOLVED HERO
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('get_involved_hero', 'Get Involved', 'Join Our Mission',
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"Get Involved with WRC","subtitle":"Make a lasting difference in the lives of vulnerable communities across Pakistan. Whether you volunteer, work with us, partner, or become a member, your contribution matters.","button1_text":"Volunteer Now","button1_link":"#volunteer","button2_text":"View Careers","button2_link":"#careers"}',
 62, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- GET INVOLVED CTA
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('get_involved_cta', 'Ready to Make an Impact?', NULL,
 '{"description":"Join thousands of supporters making a real difference in communities across Pakistan.","button1_text":"Donate Now","button1_link":"get-involved/contribute-with-us.html","button1_icon":"fa-solid fa-hand-holding-heart","button2_text":"Volunteer","button2_link":"#volunteer","button2_icon":"fa-solid fa-hands-holding-child"}',
 63, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- CONTRIBUTE HERO
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('contribute_hero', 'Let’s Work Together', 'Make a Difference',
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"Contribute to WRC","subtitle":"Every donation brings clean water, food, education, and hope to families in need across Pakistan. Your generosity creates lasting change."}',
 64, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- CONTRIBUTE IMPACT STATS
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('contribute_impact', 'Your Donation Impact', NULL,
 '{"items":[{"amount":"PKR 5,000","description":"Provides clean water for 1 family for an entire month","icon":"fa-solid fa-droplet"},{"amount":"PKR 10,000","description":"Supplies school kits for 5 children for one academic year","icon":"fa-solid fa-graduation-cap"},{"amount":"PKR 25,000","description":"Delivers food rations for 10 families during emergency","icon":"fa-solid fa-bowl-food"},{"amount":"PKR 50,000","description":"Installs a solar water filtration unit for a rural community","icon":"fa-solid fa-solar-panel"}]}',
 65, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- CONTRIBUTE TRANSPARENCY
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('contribute_transparency', 'Our Commitment', NULL,
 '{"items":[{"icon":"fa-solid fa-hand-holding-heart","title":"100% Direct Aid","description":"Every rupee goes directly to community programs"},{"icon":"fa-solid fa-shield-halved","title":"Fully Audited","description":"Annual external audits ensure complete accountability"},{"icon":"fa-solid fa-certificate","title":"Registered NGO","description":"SECP registered and tax-exempt organization"}]}',
 66, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- CONTRIBUTE WHY PARTNER
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('contribute_why', 'Why Partner With Us', NULL,
 '{"propositions":[{"icon":"fa-solid fa-bullseye","title":"Project-Based Collaboration","description":"Work with us on specific, measurable humanitarian projects with clear timelines and outcomes."},{"icon":"fa-solid fa-eye","title":"Transparent Operations","description":"Full financial transparency with regular reporting and audit documentation."},{"icon":"fa-solid fa-chart-line","title":"Measurable Outcomes","description":"Data-driven approach with clear KPIs and impact metrics for every project."},{"icon":"fa-solid fa-handshake","title":"Institutional Partnerships","description":"Long-term strategic partnerships with government, donors, and civil society."}],"stats":[{"value":"45+","label":"Projects Completed"},{"value":"8+","label":"Active Partners"},{"value":"150K+","label":"Lives Impacted"},{"value":"4","label":"Provinces Covered"}]}',
 67, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);
