USE wrc_website;

-- ABOUT INDEX: Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('about_hero', 'About World Relief Committee', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Team","subtitle":"Dedicated to serving vulnerable communities through humanitarian assistance, sustainable development, and emergency relief across Pakistan since 2005.","button1_text":"Who We Are","button1_link":"who-we-are.html","button2_text":"Our Leadership","button2_link":"leadership.html"}',
 40, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- ABOUT INDEX: Mission/Vision/Values
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('about_mission', 'Our Mission', NULL,
 '{"items":[{"icon":"fa-solid fa-bullseye","title":"Our Mission","description":"To deliver effective humanitarian assistance and sustainable development programs that transform the lives of vulnerable communities across Pakistan through partnership, innovation, and local capacity building."},{"icon":"fa-solid fa-eye","title":"Our Vision","description":"A Pakistan where every community has access to clean water, quality healthcare, education, and the opportunity to build a dignified and resilient future for their families."},{"icon":"fa-solid fa-heart","title":"Our Values","description":"We are guided by compassion, accountability, transparency, and respect for human dignity. Our work is rooted in community participation and sustainable practices."}]}',
 41, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- ABOUT INDEX: Governance Preview
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('about_governance', 'People & Governance', NULL,
 '{"items":[{"icon":"fa-solid fa-user-tie","title":"Leadership","description":"Meet the experienced leaders guiding WRC strategic vision and operations.","link":"leadership.html","link_text":"View Leadership"},{"icon":"fa-solid fa-sitemap","title":"Organizational Structure","description":"Our transparent governance framework ensures accountability at every level.","link":"organizational-structure.html","link_text":"View Structure"},{"icon":"fa-solid fa-users","title":"Our Team","description":"Dedicated professionals and field staff making impact possible every day.","link":"team.html","link_text":"View Team"}]}',
 42, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- ABOUT INDEX: Transparency
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('about_transparency', 'Transparency & Trust', NULL,
 '{"tagline":"Building Trust Through Accountability","description":"WRC maintains the highest standards of transparency and compliance. We undergo annual external audits and publish detailed financial and activity reports.","stats":[{"value":"100%","label":"Commitment to Compliance"},{"value":"Annual","label":"External Audits"}],"button1_text":"View Policies","button1_link":"policies-compliance.html","button2_text":"View Annual Reports","button2_link":"annual-reports.html"}',
 43, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- ABOUT INDEX: CTA
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('about_cta', 'Together, we can turn community needs into meaningful action.', NULL,
 '{"button1_text":"Explore Our Projects","button1_link":"projects/index.html","button2_text":"Contact Us","button2_link":"contact/index.html"}',
 44, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- WHO WE ARE: Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('who_we_are_hero', 'Who We Are', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Field Work","subtitle":"WRC is a non-profit humanitarian organization committed to serving vulnerable communities across Pakistan through sustainable development and emergency relief."}',
 45, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- WHO WE ARE: Impact Areas
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('who_we_are_impact', 'Our Areas of Impact', NULL,
 '{"items":[{"icon":"fa-solid fa-hand-holding-heart","title":"Humanitarian Assistance","description":"Targeted interventions to support vulnerable populations in emergency and development contexts."},{"icon":"fa-solid fa-graduation-cap","title":"Education","description":"Targeted interventions to support vulnerable populations in this sector."},{"icon":"fa-solid fa-heart-pulse","title":"Health & Wellbeing","description":"Targeted interventions to support vulnerable populations in this sector."},{"icon":"fa-solid fa-people-roof","title":"Community Development","description":"Targeted interventions to support vulnerable populations in this sector."},{"icon":"fa-solid fa-briefcase","title":"Livelihoods","description":"Targeted interventions to support vulnerable populations in this sector."},{"icon":"fa-solid fa-truck-medical","title":"Emergency Response","description":"Targeted interventions to support vulnerable populations in this sector."}]}',
 46, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- WHO WE ARE: Why We Exist
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('why_we_exist', 'Why We Exist', NULL,
 '{"items":[{"icon":"fa-solid fa-scale-balanced","title":"Equal Opportunities","description":"We believe every individual deserves equal access to resources, services, and opportunities regardless of their background or circumstances."},{"icon":"fa-solid fa-seedling","title":"Sustainable Development","description":"Our programs are designed to create lasting change that communities can maintain and build upon long after our direct involvement ends."},{"icon":"fa-solid fa-hand-holding-heart","title":"Human Dignity","description":"We uphold the inherent dignity of every person we serve, ensuring our interventions are respectful, culturally sensitive, and empowering."}]}',
 47, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- HISTORY: Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('history_hero', 'Our Journey of Impact', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC History","subtitle":"From humble beginnings to serving over 150,000 lives across Pakistan, WRC has grown into one of the most trusted humanitarian organizations in the region."}',
 48, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- HISTORY: Stats
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('history_stats', 'Key Milestones', NULL,
 '{"stats":[{"value":"20+","label":"Years of Service","icon":"fa-solid fa-calendar-days","color":"brandRed"},{"value":"4","label":"Provinces Covered","icon":"fa-solid fa-map-location-dot","color":"teal-500"},{"value":"9","label":"Humanitarian Sectors","icon":"fa-solid fa-layer-group","color":"gold-400"},{"value":"150K+","label":"Lives Impacted","icon":"fa-solid fa-users","color":"brandRed"}]}',
 49, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- HISTORY: CTA
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('history_cta', 'Be Part of Our Story', 'Get Involved',
 '{"description":"Join us in creating the next chapter of community transformation across Pakistan. Your support can help us reach more families in need.","button1_text":"Contribute Now","button1_link":"get-involved/contribute-with-us.html","button2_text":"Contact Us","button2_link":"contact/index.html"}',
 50, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- TEAM: Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('team_hero', 'Our Team', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Team","subtitle":"The dedicated professionals and field staff who make our humanitarian work possible across Pakistan."}',
 51, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- LEADERSHIP: Hero
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('leadership_hero', 'Leadership', NULL,
 '{"hero_image":"assets/images/hero.jpg","hero_alt":"WRC Leadership","subtitle":"Meet the experienced leaders guiding WRC strategic vision and operations across Pakistan."}',
 52, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- LEADERSHIP: Chairman Message
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('chairman_message', 'Message from the Chairman', NULL,
 '{"name":"Mr. Asad Shah","title":"Chairman, WRC","message":"Welcome to the World Relief Committee. Since our establishment in 2005, we have been committed to serving the most vulnerable communities across Pakistan through humanitarian assistance, sustainable development, and emergency relief. Our journey has been one of dedication, resilience, and unwavering commitment to human dignity. Every project we undertake, every life we touch, and every community we serve reinforces our belief that meaningful change is possible when we work together. I invite you to join us in our mission to create a Pakistan where every community has access to clean water, quality healthcare, education, and the opportunity to build a dignified future."}',
 53, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);
