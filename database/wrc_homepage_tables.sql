-- =============================================
-- WRC Website - New Tables for Homepage Sections
-- =============================================

USE wrc_website;

-- 1. Process Steps (How We Work - 8 steps)
-- The seed below is idempotent: the schema uses CREATE TABLE IF NOT EXISTS,
-- so re-running this file against an existing database used to append a
-- second copy of every step instead of replacing them.
CREATE TABLE IF NOT EXISTS process_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    step_number TINYINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    color_class VARCHAR(50) DEFAULT 'text-brandRed',
    dot_color VARCHAR(50) DEFAULT 'bg-brandRed',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Success Stories / Testimonials
CREATE TABLE IF NOT EXISTS success_stories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_name VARCHAR(255) NOT NULL,
    person_title VARCHAR(255),
    location VARCHAR(255),
    quote TEXT NOT NULL,
    image_path VARCHAR(500),
    author_image VARCHAR(500),
    sector VARCHAR(100),
    sector_badge VARCHAR(50),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Homepage Sections (Chairman message, CTA banner, etc.)
CREATE TABLE IF NOT EXISTS homepage_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255),
    subtitle TEXT,
    description TEXT,
    person_name VARCHAR(255),
    person_title VARCHAR(255),
    image_path VARCHAR(500),
    cta_text VARCHAR(100),
    cta_link VARCHAR(500),
    extra_data JSON,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Seed Data
-- =============================================

-- Process Steps
-- Guard runs after the CREATE TABLE above, so a fresh install creates the
-- table first and only then replaces its contents.
DELETE FROM process_steps;
INSERT INTO process_steps (step_number, title, description, color_class, dot_color, sort_order) VALUES
(1, 'Funding Opportunity Identification', 'We proactively identify and assess funding opportunities, including donor programmes, government grants, and strategic partnerships. Each opportunity is evaluated against community needs, organizational priorities, technical capacity, and eligibility requirements.', 'text-brandRed', 'bg-brandRed', 1),
(2, 'Proposal Development', 'Our technical and financial teams work collaboratively to develop evidence-based proposals with clearly defined objectives, measurable outcomes, implementation strategies, realistic budgets, and appropriate monitoring frameworks.', 'text-brandRed', 'bg-brandRed', 2),
(3, 'Project Award & Agreement', 'Upon project approval, we formalize agreements with donors and relevant stakeholders, establish roles and responsibilities, review contractual obligations, and ensure alignment with applicable regulatory and donor compliance requirements.', 'text-brandRed', 'bg-brandRed', 3),
(4, 'Project Planning & Mobilization', 'We translate approved proposals into actionable implementation plans, defining project scope, timelines, resource requirements, procurement arrangements, risk management measures, and stakeholder responsibilities. Project teams and operational resources are mobilized accordingly.', 'text-slate-700', 'bg-slate-700', 4),
(5, 'Project Implementation', 'We execute project activities through coordinated field operations, technical oversight, and active engagement with communities, government departments, and implementation partners. Our focus remains on quality delivery, efficient resource utilization, and achieving agreed objectives.', 'text-slate-700', 'bg-slate-700', 5),
(6, 'Monitoring, Evaluation & Quality Assurance', 'We systematically monitor project progress against established indicators, assess performance and outcomes, conduct field-level reviews, and identify areas requiring improvement. Regular assessments enable timely corrective actions and help maintain implementation quality.', 'text-slate-700', 'bg-slate-700', 6),
(7, 'Reporting, Financial Accountability & Transparency', 'We maintain comprehensive records of project activities, expenditures, achievements, and challenges. Periodic technical and financial reports, expenditure tracking, documentation, and audit-ready records ensure accountability to donors, partners, and relevant stakeholders.', 'text-slate-800', 'bg-slate-700', 7),
(8, 'Project Closure & Sustainability', 'At project completion, we conduct final assessments, reconcile accounts, document lessons learned, finalize deliverables, and facilitate the formal handover of relevant assets and responsibilities. We prioritize sustainability measures to support the continued benefits of project interventions beyond the funding period.', 'text-slate-800', 'bg-slate-700', 8);

-- Success Stories
-- Same idempotency guard as the process steps above: without it, re-running
-- this file duplicated every testimonial, which made the homepage show the
-- same person and photograph twice and made admin edits look like no-ops.
DELETE FROM success_stories;
-- The photographs are WRC's own field photos: a WASH training session, flood
-- damage in Swat, and a government school prize distribution. author_image is
-- left NULL - the card falls back to the person's initials, so the portrait is
-- never a copy of the story photo.
INSERT INTO success_stories (person_name, person_title, location, quote, image_path, author_image, sector, sector_badge, sort_order) VALUES
('Zainab Bibi', 'Community Beneficiary', 'Peshawar, KP', 'Before WRC installed the water well, my children walked 3km each day for water. Now they go to school instead. Our family''s health has completely transformed.', 'assets/images/gallery/9.JPG', NULL, 'WASH', 'WASH', 1),
('Ahmed Khan', 'Farmer, Sindh', 'Sukkur, Sindh', 'When the floods destroyed our crops, WRC food packs kept my family alive for three months. They didn''t just bring food — they brought hope back to our village.', 'assets/images/projects/flood-swat.jpg', NULL, 'Food Security', 'Food Security', 2),
('Fatima Noor', 'Mother of 3, Punjab', 'Lahore, Punjab', 'My daughter was about to drop out of school because we couldn''t afford books. WRC gave her a complete kit and now she''s top of her class. Education changes everything.', 'assets/images/gallery/11.JPG', NULL, 'Education', 'Education', 3);

-- Homepage Sections (Chairman Message + CTA)
-- This one uses INSERT IGNORE rather than a DELETE guard: section_key is
-- UNIQUE, so nothing here can duplicate, and these rows are edited from the
-- admin dashboard - re-running the setup must not discard that work. IGNORE
-- simply fills the row in if it is missing and leaves existing content alone.
INSERT IGNORE INTO homepage_sections (section_key, title, subtitle, description, person_name, person_title, image_path, cta_text, cta_link, extra_data) VALUES
('chairman_message', 'Chairman''s Message', 'A thoughtful message from the chairman highlighting WRC''s mission, values, and commitment to community resilience.', NULL, 'Mr. Asad Shah', 'Chairman, Welfare & Relief Committee', 'assets/images/hero.jpg', NULL, NULL, '{"overlay_title": "Leading with compassion, transparency, and bold action.", "note_label": "Chairman''s Note", "paragraph1": "At WRC, every life touched is a promise kept. We deliver emergency relief and sustainable development with integrity, compassion, and respect for the communities we serve.", "paragraph2": "Guided by accountability and long-term impact, our teams bring clean water, education, and hope to families rebuilding after crisis.", "vision_label": "Vision", "vision_text": "Empowering communities through resilience, dignity, and sustainable change."}'),
('cta_banner', 'Ready to Make a Difference?', NULL, 'Every contribution helps us deliver clean water, food, education, and emergency relief to families who need it most.', NULL, NULL, NULL, 'Donate Now', 'get-involved/contribute-with-us.html', '{"button2_text": "Volunteer", "button2_link": "get-involved/index.html#volunteer", "button1_icon": "fa-solid fa-hand-holding-heart", "button2_icon": "fa-solid fa-hands-holding-child"}');
