USE wrc_website;

-- Process Steps (seed if empty)
-- INSERT IGNORE alone could not honour "seed if empty": with no unique key on
-- this table MySQL inserted the rows again on every run, which is how the
-- homepage came to show each step twice. The WHERE NOT EXISTS guard is what
-- actually makes this statement conditional.
INSERT INTO process_steps (step_number, title, description, color_class, dot_color, sort_order)
SELECT s.step_number, s.title, s.description, s.color_class, s.dot_color, s.sort_order
FROM (
    SELECT 1 AS step_number, 'Funding Opportunity Identification' AS title, 'We proactively identify and assess funding opportunities, including donor programmes, government grants, and strategic partnerships. Each opportunity is evaluated against community needs, organizational priorities, technical capacity, and eligibility requirements.' AS description, 'text-brandRed' AS color_class, 'bg-brandRed' AS dot_color, 1 AS sort_order
    UNION ALL SELECT 2, 'Proposal Development', 'Our technical and financial teams work collaboratively to develop evidence-based proposals with clearly defined objectives, measurable outcomes, implementation strategies, realistic budgets, and appropriate monitoring frameworks.', 'text-brandRed', 'bg-brandRed', 2
    UNION ALL SELECT 3, 'Project Award & Agreement', 'Upon project approval, we formalize agreements with donors and relevant stakeholders, establish roles and responsibilities, review contractual obligations, and ensure alignment with applicable regulatory and donor compliance requirements.', 'text-brandRed', 'bg-brandRed', 3
    UNION ALL SELECT 4, 'Project Planning & Mobilization', 'We translate approved proposals into actionable implementation plans, defining project scope, timelines, resource requirements, procurement arrangements, risk management measures, and stakeholder responsibilities. Project teams and operational resources are mobilized accordingly.', 'text-slate-700', 'bg-slate-700', 4
    UNION ALL SELECT 5, 'Project Implementation', 'We execute project activities through coordinated field operations, technical oversight, and active engagement with communities, government departments, and implementation partners. Our focus remains on quality delivery, efficient resource utilization, and achieving agreed objectives.', 'text-slate-700', 'bg-slate-700', 5
    UNION ALL SELECT 6, 'Monitoring, Evaluation & Quality Assurance', 'We systematically monitor project progress against established indicators, assess performance and outcomes, conduct field-level reviews, and identify areas requiring improvement. Regular assessments enable timely corrective actions and help maintain implementation quality.', 'text-slate-700', 'bg-slate-700', 6
    UNION ALL SELECT 7, 'Reporting, Financial Accountability & Transparency', 'We maintain comprehensive records of project activities, expenditures, achievements, and challenges. Periodic technical and financial reports, expenditure tracking, documentation, and audit-ready records ensure accountability to donors, partners, and relevant stakeholders.', 'text-slate-800', 'bg-slate-700', 7
    UNION ALL SELECT 8, 'Project Closure & Sustainability', 'At project completion, we conduct final assessments, reconcile accounts, document lessons learned, finalize deliverables, and facilitate the formal handover of relevant assets and responsibilities. We prioritize sustainability measures to support the continued benefits of project interventions beyond the funding period.', 'text-slate-800', 'bg-slate-700', 8
) AS s
WHERE NOT EXISTS (SELECT 1 FROM process_steps);

-- Success Stories (seed if empty)
-- Same fix as the process steps above: INSERT IGNORE inserted regardless,
-- because nothing on this table is unique, so every run added a second copy
-- of each testimonial and the homepage began showing the same person and
-- photograph twice.
INSERT INTO success_stories (person_name, person_title, location, quote, image_path, author_image, sector, sector_badge, sort_order)
SELECT s.person_name, s.person_title, s.location, s.quote, s.image_path, s.author_image, s.sector, s.sector_badge, s.sort_order
FROM (
    SELECT 'Zainab Bibi' AS person_name, 'Community Beneficiary' AS person_title, 'Peshawar, KP' AS location, 'Before WRC installed the water well, my children walked 3km each day for water. Now they go to school instead. Our family''s health has completely transformed.' AS quote, 'assets/images/gallery/9.JPG' AS image_path, NULL AS author_image, 'WASH' AS sector, 'WASH' AS sector_badge, 1 AS sort_order
    UNION ALL SELECT 'Ahmed Khan', 'Farmer, Sindh', 'Sukkur, Sindh', 'When the floods destroyed our crops, WRC food packs kept my family alive for three months. They didn''t just bring food — they brought hope back to our village.', 'assets/images/projects/flood-swat.jpg', NULL, 'Food Security', 'Food Security', 2
    UNION ALL SELECT 'Fatima Noor', 'Mother of 3, Punjab', 'Lahore, Punjab', 'My daughter was about to drop out of school because we couldn''t afford books. WRC gave her a complete kit and now she''s top of her class. Education changes everything.', 'assets/images/gallery/11.JPG', NULL, 'Education', 'Education', 3
) AS s
WHERE NOT EXISTS (SELECT 1 FROM success_stories);

-- Homepage Sections (Chairman Message + CTA Banner)
-- Using actual column names: section_key, section_title, section_subtitle, content (JSON)
INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES
('chairman_message', 'Chairman''s Message', 'A thoughtful message from the chairman highlighting WRC''s mission, values, and commitment to community resilience.',
 '{"overlay_title":"Leading with compassion, transparency, and bold action.","note_label":"Chairman''s Note","paragraph1":"At WRC, every life touched is a promise kept. We deliver emergency relief and sustainable development with integrity, compassion, and respect for the communities we serve.","paragraph2":"Guided by accountability and long-term impact, our teams bring clean water, education, and hope to families rebuilding after crisis.","person_name":"Mr. Asad Shah","person_title":"Chairman, Welfare & Relief Committee","image_path":"assets/images/hero.jpg","vision_label":"Vision","vision_text":"Empowering communities through resilience, dignity, and sustainable change."}',
 1, 1),
('cta_banner', 'Ready to Make a Difference?', NULL,
 '{"description":"Every contribution helps us deliver clean water, food, education, and emergency relief to families who need it most.","button1_text":"Donate Now","button1_link":"get-involved/contribute-with-us.html","button1_icon":"fa-solid fa-hand-holding-heart","button2_text":"Volunteer","button2_link":"get-involved/index.html#volunteer","button2_icon":"fa-solid fa-hands-holding-child"}',
 2, 1)
ON DUPLICATE KEY UPDATE
  section_title = VALUES(section_title),
  section_subtitle = VALUES(section_subtitle),
  content = VALUES(content);
