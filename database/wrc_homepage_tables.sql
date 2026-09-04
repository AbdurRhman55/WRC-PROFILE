-- =============================================
-- WRC Website - New Tables for Homepage Sections
-- =============================================

USE wrc_website;

-- 1. Process Steps (How We Work - 8 steps)
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
INSERT INTO process_steps (step_number, title, description, color_class, dot_color, sort_order) VALUES
(1, 'Funding opportunity', 'We scan donor calls, government grants, and partnership openings, screening each against community needs and our technical capacity before pursuing it.', 'text-brandRed', 'bg-brandRed', 1),
(2, 'Proposal development', 'Technical and finance teams co-author a proposal with clear objectives, a realistic budget, and evidence drawn from prior assessments in the field.', 'text-brandRed', 'bg-brandRed', 2),
(3, 'Project award', 'Once selected, we formalize the agreement with the donor, confirm compliance requirements, and mobilize the core project team.', 'text-brandRed', 'bg-brandRed', 3),
(4, 'Planning', 'We map scope, timelines, and responsibilities with local stakeholders, turning the proposal into a workable operational plan.', 'text-slate-700', 'bg-slate-700', 4),
(5, 'Implementation', 'Activities move onto the ground, delivered with technical rigor and close coordination with the communities and institutions we serve.', 'text-slate-700', 'bg-slate-700', 5),
(6, 'Monitoring & evaluation', 'Indicators are tracked against the plan, so we can catch issues early and adjust course before they affect outcomes.', 'text-slate-700', 'bg-slate-700', 6),
(7, 'Reporting', 'Results, spending, and lessons learned are documented clearly for funders, partners, and the communities involved.', 'text-slate-800', 'bg-slate-700', 7),
(8, 'Project closure', 'Deliverables are finalized, accounts reconciled, and the project formally handed over to the communities and institutions that will sustain it.', 'text-slate-800', 'bg-slate-700', 8);

-- Success Stories
INSERT INTO success_stories (person_name, person_title, location, quote, image_path, sector, sector_badge, sort_order) VALUES
('Zainab Bibi', 'Community Beneficiary', 'Peshawar, KP', 'Before WRC installed the water well, my children walked 3km each day for water. Now they go to school instead. Our family''s health has completely transformed.', 'assets/images/water.jpg', 'WASH', 'WASH', 1),
('Ahmed Khan', 'Farmer, Sindh', 'Sukkur, Sindh', 'When the floods destroyed our crops, WRC food packs kept my family alive for three months. They didn''t just bring food — they brought hope back to our village.', 'assets/images/food.jpg', 'Food Security', 'Food Security', 2),
('Fatima Noor', 'Mother of 3, Punjab', 'Lahore, Punjab', 'My daughter was about to drop out of school because we couldn''t afford books. WRC gave her a complete kit and now she''s top of her class. Education changes everything.', 'assets/images/hero-slider-3.jpg', 'Education', 'Education', 3);

-- Homepage Sections (Chairman Message + CTA)
INSERT INTO homepage_sections (section_key, title, subtitle, description, person_name, person_title, image_path, cta_text, cta_link, extra_data) VALUES
('chairman_message', 'Chairman''s Message', 'A thoughtful message from the chairman highlighting WRC''s mission, values, and commitment to community resilience.', NULL, 'Mr. Asad Shah', 'Chairman, World Relief Committee', 'assets/images/hero.jpg', NULL, NULL, '{"overlay_title": "Leading with compassion, transparency, and bold action.", "note_label": "Chairman''s Note", "paragraph1": "At WRC, every life touched is a promise kept. We deliver emergency relief and sustainable development with integrity, compassion, and respect for the communities we serve.", "paragraph2": "Guided by accountability and long-term impact, our teams bring clean water, education, and hope to families rebuilding after crisis.", "vision_label": "Vision", "vision_text": "Empowering communities through resilience, dignity, and sustainable change."}'),
('cta_banner', 'Ready to Make a Difference?', NULL, 'Every contribution helps us deliver clean water, food, education, and emergency relief to families who need it most.', NULL, NULL, NULL, 'Donate Now', 'get-involved/contribute-with-us.html', '{"button2_text": "Volunteer", "button2_link": "get-involved/index.html#volunteer", "button1_icon": "fa-solid fa-hand-holding-heart", "button2_icon": "fa-solid fa-hands-holding-child"}');
