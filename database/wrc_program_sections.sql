USE wrc_website;

-- Implementation Framework section
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('program_implementation', 'Our Implementation Framework', 'Implementation Path',
 '{"subtitle":"How we plan, execute, and monitor our activities for long-term success.","steps":[{"num":"01","title":"Assess","description":"Assessing community conditions, constraints, and baseline indicators prior to launch."},{"num":"02","title":"Plan","description":"Collaborating with municipal offices, elders, and leaders to establish local planning committees."},{"num":"03","title":"Implement","description":"Mobilizing technical resources and field staff to deliver high-quality project outputs."},{"num":"04","title":"Monitor & Learn","description":"Applying regular evaluations and field audits to sustain operations and verify results."}]}',
 20, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);

-- Program detail CTA Banner
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('program_cta', 'Partner With Us for Greater Impact', NULL,
 '{"description":"Join hands with Welfare Relief Committee to support localized development and relief activities under this program.","button1_text":"Support Initiatives","button1_link":"get-involved/contribute-with-us.html","button1_icon":"fa-solid fa-handshake","button2_text":"Contact Us","button2_link":"contact/index.html","button2_icon":"fa-solid fa-envelope"}',
 21, 1)
ON DUPLICATE KEY UPDATE section_title = VALUES(section_title), content = VALUES(content);
