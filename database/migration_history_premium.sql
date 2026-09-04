-- History Premium Page Migration
-- Date: 2026-09-03
-- Purpose: Seed hero/stats/cta data, add milestone columns, create projects table

-- 1. Seed history_hero
UPDATE history_hero SET
  hero_image = 'assets/images/hero.jpg',
  section_title = 'Our History',
  section_subtitle = 'Two decades of dedicated service, growth, and unwavering commitment to vulnerable communities worldwide.',
  subtitle = 'Building Resilience, Restoring Hope',
  button1_text = 'Who We Are',
  button1_link = 'about/who-we-are.php',
  button2_text = 'Our Impact',
  button2_link = 'impact/index.php'
WHERE id = 1;

-- 2. Seed history_stats
UPDATE history_stats SET
  section_title = 'Key Statistics',
  section_subtitle = 'The measurable impact of our work across communities worldwide.'
WHERE id = 1;

-- 3. Fix history_cta
UPDATE history_cta SET
  badge = 'Get Involved',
  section_title = 'Be Part of Our Story',
  section_subtitle = 'Join us in creating lasting change for communities around the world.',
  button1_text = 'Donate Now',
  button1_link = 'get-involved/contribute-with-us.php'
WHERE id = 1;

-- 4. Seed history_milestones with images, tags, colors, stats
UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Foundation',
  tag_color = '#ca0000',
  stats_text = 'Founded in 2005 to serve vulnerable communities'
WHERE id = 1;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Emergency Response',
  tag_color = '#dc2626',
  stats_text = 'Emergency relief operations launched across Sindh and Balochistan'
WHERE id = 2;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Health',
  tag_color = '#2F6F62',
  stats_text = '15+ health facilities established in underserved areas'
WHERE id = 3;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Education',
  tag_color = '#2563eb',
  stats_text = '3,000+ students enrolled across 12 learning centers'
WHERE id = 4;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'WASH',
  tag_color = '#0891b2',
  stats_text = 'Clean water access for 50,000+ individuals'
WHERE id = 5;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Expansion',
  tag_color = '#C89B3C',
  stats_text = 'Operations expanded to 4 provinces covering 16 districts'
WHERE id = 6;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Livelihoods',
  tag_color = '#16a34a',
  stats_text = '2,000+ families supported with sustainable livelihood programs'
WHERE id = 7;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Partnerships',
  tag_color = '#7c3aed',
  stats_text = 'Strategic partnerships with UNHCR, WFP, and national government'
WHERE id = 8;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Humanitarian',
  tag_color = '#ca0000',
  stats_text = 'Flood response reaching 25,000+ families in need'
WHERE id = 9;

UPDATE history_milestones SET
  image = 'assets/images/hero.jpg',
  tag = 'Innovation',
  tag_color = '#ea580c',
  stats_text = '100,000+ individuals reached across 16 districts in 4 provinces',
  is_current = 1
WHERE id = 10;

-- 5. Seed history_projects (chronological project table)
INSERT INTO history_projects (year, title, sector, location, description, beneficiaries, status, program_slug, sort_order) VALUES
('2005', 'Community Emergency Response', 'Emergency', 'Karachi, Sindh', 'Emergency relief operations in flood-affected areas of Karachi providing food, shelter, and medical assistance to displaced families.', '5,000+ families', 'Completed', 'emergency', 1),
('2008', 'Rural Health Initiative', 'Health', 'Thatta District, Sindh', 'Establishment of primary health care facilities in remote villages with limited access to medical services, including maternal and child health programs.', '15,000+ patients', 'Completed', 'health', 2),
('2010', 'Flood Relief & Rehabilitation', 'Emergency', 'Balochistan & Sindh', 'Large-scale flood response providing emergency shelter, clean water, and food assistance to communities devastated by catastrophic flooding.', '25,000+ families', 'Completed', 'emergency', 3),
('2012', 'Education for All Program', 'Education', 'Multan, Punjab', 'Construction of learning centers and recruitment of trained teachers to provide quality education to out-of-school children in underserved areas.', '3,000+ students', 'Completed', 'education', 4),
('2014', 'Clean Water & Sanitation Project', 'WASH', 'Quetta, Balochistan', 'Installation of water purification systems, construction of latrines, and hygiene awareness campaigns in water-scarce regions.', '50,000+ individuals', 'Completed', 'wash', 5),
('2016', 'Livelihoods & Skills Development', 'Livelihoods', 'Peshawar, KPK', 'Vocational training and micro-enterprise support for vulnerable households to build economic resilience and self-sufficiency.', '2,000+ families', 'Completed', 'livelihoods', 6),
('2018', 'Maternal & Child Health Program', 'Health', 'Lahore, Punjab', 'Comprehensive maternal and child health services including prenatal care, immunization drives, and nutrition support for mothers and children.', '10,000+ beneficiaries', 'Completed', 'health', 7),
('2020', 'COVID-19 Emergency Response', 'Emergency', 'Nationwide', 'Rapid response to the pandemic providing hygiene kits, PPE, medical supplies, and awareness campaigns across all operational areas.', '30,000+ individuals', 'Completed', 'emergency', 8),
('2022', 'Flood Emergency Response 2022', 'Emergency', 'Sindh & Balochistan', 'Large-scale emergency response to 2022 floods providing shelter, food, clean water, and medical assistance to millions of displaced people.', '25,000+ families', 'Completed', 'emergency', 9),
('2024', 'Integrated Community Development', 'Multi-Sector', 'Multiple Districts', 'Comprehensive program integrating health, education, WASH, and livelihoods interventions for holistic community development across 16 districts.', '100,000+ individuals', 'Ongoing', 'community-development', 10);
