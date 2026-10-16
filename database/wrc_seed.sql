-- =============================================
-- WRC Website Seed Data
-- All data extracted from current static HTML files
-- =============================================

USE wrc_website;

-- =============================================
-- 1. SITE SETTINGS
-- =============================================

INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES
('org_name', 'World Relief Committee', 'general'),
('org_abbreviation', 'WRC', 'general'),
('phone', '+92 300 1234567', 'contact'),
('email', 'info@wrc.org.pk', 'contact'),
('address', 'Flat#1, 3rd Floor, Shami Flats near Marbella Wedding Hall, Warsak Road, Peshawar, Pakistan', 'contact'),
('emergency_banner', 'Emergency Flood & Winter Aid Active', 'announcement'),
('copyright', '2026 World Relief Committee. All rights reserved.', 'footer'),
('logo_path', 'assets/images/logo.png', 'general'),
('hero_image', 'assets/images/hero.jpg', 'general'),
('footer_tagline', 'WRC partners with institutions, governments, and field agencies to deliver clean water, health services, education, and emergency relief across vulnerable communities.', 'footer'),
('vision', 'A world where every community is resilient, empowered, and equipped with the resources needed to thrive with dignity and self-reliance.', 'about'),
('mission', 'To deliver compassionate, transparent, and effective humanitarian assistance while building sustainable community capacity through health, education, WASH, and emergency response programs.', 'about');

-- =============================================
-- 2. HERO SLIDERS
-- =============================================

INSERT INTO hero_sliders (title, subtitle, image_path, cta_text, cta_link, sort_order) VALUES
('Delivering Hope Where It''s Needed Most', 'Emergency relief, sustainable development, and community empowerment across Pakistan.', 'assets/images/hero.jpg', 'Our Programs', 'our-work/index.html', 1),
('Building Resilient Communities Together', 'From clean water to quality education, we create lasting change in underserved regions.', 'assets/images/hero-slider-2.jpg', 'See Our Impact', 'impact/index.html', 2),
('Every Action Creates a Ripple of Change', 'Join thousands of donors, volunteers, and partners transforming lives.', 'assets/images/hero-slider-3.jpg', 'Let’s Work Together', 'get-involved/contribute-with-us.html', 3);

-- =============================================
-- 3. PROGRAMS (from data.js)
-- =============================================

INSERT INTO programs (slug, title, full_title, icon_class, hero_image, fallback_image, short_description, hero_description, description, overview, why_it_matters, focus_areas, target_groups, related_programs, sector_number, sort_order) VALUES
('health', 'Health', 'Health & Healthcare Services', 'fa-solid fa-heart-pulse', 'assets/images/our-work/health-hero.jpg', 'assets/images/hero.jpg', 'Supporting access to essential healthcare and community wellbeing.', 'Supporting healthier and more resilient communities through accessible, community-focused health initiatives.', 'Supporting access to essential healthcare and community wellbeing.', 'WRC\'s Health program works to improve access to essential healthcare services in underserved communities. Through mobile health units, community health awareness campaigns, and targeted medical support, WRC aims to strengthen the health and wellbeing of vulnerable populations, particularly women, children, and those living in remote or crisis-affected areas.', 'Access to basic healthcare remains a significant challenge for millions of people in underserved and remote areas. Limited health infrastructure, shortage of trained health workers, and barriers to access disproportionately affect women, children, and elderly populations. Community-focused health interventions can help bridge these gaps and strengthen the overall resilience of communities.', '["Primary Healthcare","Community Health","Health Awareness","Maternal & Child Health"]', '["Children","Women","Elderly","Persons with Disabilities","Underserved Communities"]', '["nutrition","wash","emergency-response"]', '01', 1),

('education', 'Education', 'Education & Youth Development', 'fa-solid fa-graduation-cap', 'assets/images/our-work/education-hero.jpg', 'assets/images/hero.jpg', 'Enabling access to quality education and learning opportunities for all.', 'Empowering communities through inclusive, quality education and youth development initiatives that create lasting opportunities.', 'Enabling access to quality education and learning opportunities for all.', 'WRC\'s Education program focuses on improving access to quality education, particularly for children and youth in underserved and crisis-affected communities. Through support for learning environments, educational materials, teacher capacity building, and community engagement, WRC works to create meaningful educational opportunities.', 'Education is a fundamental right and one of the most effective tools for sustainable development. Yet millions of children, particularly girls, remain out of school due to poverty, displacement, or lack of infrastructure. Quality education empowers individuals, strengthens communities, and builds the foundation for long-term resilience and prosperity.', '["Access to Education","Learning Environments","Teacher Capacity","Youth Development"]', '["Children","Youth","Girls","Orphans","Teachers","Communities"]', '["health","livelihood","nutrition"]', '02', 2),

('wash', 'WASH', 'Water, Sanitation & Hygiene', 'fa-solid fa-faucet-drip', 'assets/images/our-work/wash-hero.jpg', 'assets/images/water.jpg', 'Providing clean water, sanitation facilities and hygiene promotion.', 'Ensuring communities have access to safe water, adequate sanitation, and improved hygiene practices for better health and dignity.', 'Providing clean water, sanitation facilities and hygiene promotion.', 'WRC\'s WASH program addresses the critical need for clean water, safe sanitation, and hygiene promotion in vulnerable communities. Through infrastructure development, community-based management, and behavior change communication, WRC works to reduce waterborne diseases and improve overall community health and dignity.', 'Clean water and adequate sanitation are essential for health, dignity, and development. Contaminated water and poor sanitation contribute significantly to disease, particularly among children. Community-level WASH interventions are among the most cost-effective health measures, preventing disease and supporting education, livelihoods, and overall community wellbeing.', '["Clean Water Access","Sanitation Facilities","Hygiene Promotion","Water Infrastructure"]', '["Households","Schools","Communities","Women","Children"]', '["health","nutrition","climate-resilience"]', '03', 3),

('food-security', 'Food Security', 'Food Security & Hunger Relief', 'fa-solid fa-bowl-food', 'assets/images/our-work/food-security-hero.jpg', 'assets/images/food.jpg', 'Addressing food insecurity and hunger in vulnerable communities.', 'Working to ensure vulnerable communities have reliable access to sufficient, safe, and nutritious food through targeted interventions.', 'Addressing food insecurity and hunger in vulnerable communities.', 'WRC\'s Food Security program addresses immediate food needs and works toward longer-term food security for vulnerable communities. Through emergency food distributions, seasonal relief drives, and community-based food security initiatives, WRC aims to reduce hunger and improve nutritional outcomes for families affected by crisis and poverty.', 'Food insecurity affects millions of families, particularly in areas impacted by natural disasters, conflict, and poverty. Hunger undermines health, education, and economic opportunity. Timely food assistance during emergencies saves lives, while longer-term food security interventions help communities build resilience against future shocks.', '["Emergency Food Aid","Seasonal Relief","Community Food Security","Household Support"]', '["Food-Insecure Households","Disaster-Affected Families","Children","Elderly","Vulnerable Communities"]', '["nutrition","livelihood","emergency-response"]', '04', 4),

('nutrition', 'Nutrition', 'Nutrition & Wellbeing', 'fa-solid fa-apple-whole', 'assets/images/our-work/nutrition-hero.jpg', 'assets/images/hero.jpg', 'Tackling malnutrition and improving nutritional wellbeing in communities.', 'Addressing malnutrition through screening, treatment support, and nutritional education for vulnerable children and mothers.', 'Tackling malnutrition and improving nutritional wellbeing in communities.', 'WRC\'s Nutrition program focuses on preventing and addressing malnutrition, particularly among children under five and pregnant and lactating women. Through malnutrition screening, therapeutic feeding support, nutritional education, and integration with health services, WRC works to improve nutritional outcomes in vulnerable communities.', 'Malnutrition remains one of the leading contributors to child mortality and long-term developmental challenges. Acute and chronic malnutrition affect millions of children, with lasting consequences on physical growth, cognitive development, and overall health. Early detection and community-based nutrition interventions are critical to saving lives and supporting healthy development.', '["Malnutrition Screening","Therapeutic Feeding","Nutritional Education","Maternal Nutrition"]', '["Children Under 5","Pregnant Women","Lactating Mothers","Malnourished Individuals","Vulnerable Families"]', '["health","food-security","wash"]', '05', 5),

('livelihood', 'Livelihood', 'Livelihood & Economic Empowerment', 'fa-solid fa-briefcase', 'assets/images/our-work/livelihood-hero.jpg', 'assets/images/our-work/livelihood-hero.jpg', 'Building sustainable livelihoods and economic resilience in communities.', 'Strengthening economic resilience through skills development, vocational training, and sustainable livelihood support for vulnerable communities.', 'Building sustainable livelihoods and economic resilience in communities.', 'WRC\'s Livelihood program supports economic empowerment and sustainable income generation for vulnerable communities. Through vocational training, skills development, micro-enterprise support, and asset distribution, WRC helps individuals and families build sustainable livelihoods and reduce their dependence on humanitarian assistance.', 'Sustainable livelihoods are essential for long-term community resilience. Without economic opportunities, families remain trapped in cycles of poverty and vulnerability. Skills development, vocational training, and support for small-scale enterprise creation help individuals gain economic independence, contribute to their communities, and build resilience against future shocks.', '["Vocational Training","Skills Development","Micro-Enterprise Support","Asset Distribution"]', '["Youth","Women","Displaced Households","Small-Scale Farmers","Vulnerable Families"]', '["education","food-security","disaster-risk-reduction"]', '06', 6),

('emergency-response', 'Emergency Response', 'Emergency Response & Disaster Relief', 'fa-solid fa-truck-medical', 'assets/images/our-work/emergency-response-hero.jpg', 'assets/images/our-work/emergency-response-hero.jpg', 'Providing rapid humanitarian assistance during emergencies and disasters.', 'Delivering rapid, life-saving humanitarian assistance to communities affected by natural disasters, crises, and emergencies.', 'Providing rapid humanitarian assistance during emergencies and disasters.', 'WRC\'s Emergency Response program provides rapid humanitarian assistance to communities affected by natural disasters, crises, and emergencies. Through coordinated relief operations, WRC delivers essential supplies including shelter, food, clean water, medical support, and non-food items to help affected communities survive and begin recovery.', 'Natural disasters and emergencies can devastate communities within hours, destroying homes, infrastructure, and livelihoods. Rapid, coordinated humanitarian response is critical to saving lives and reducing suffering. Effective emergency response requires pre-positioning of supplies, trained response teams, and strong coordination with local authorities and partner organizations.', '["Rapid Relief Operations","Shelter & NFI Distribution","Emergency Health","Coordination"]', '["Disaster-Affected Communities","Displaced Families","Women & Children","Elderly","Persons with Disabilities"]', '["health","food-security","disaster-risk-reduction"]', '07', 7),

('disaster-risk-reduction', 'Disaster Risk Reduction', 'Disaster Risk Reduction & Preparedness', 'fa-solid fa-triangle-exclamation', 'assets/images/our-work/disaster-risk-reduction-hero.jpg', 'assets/images/our-work/disaster-risk-reduction-hero.jpg', 'Strengthening community preparedness and reducing disaster vulnerability.', 'Building community capacity to prepare for, respond to, and recover from disasters through awareness, training, and resilience-building.', 'Strengthening community preparedness and reducing disaster vulnerability.', 'WRC\'s Disaster Risk Reduction program focuses on strengthening community resilience and reducing vulnerability to natural hazards. Through community-based disaster preparedness training, early warning systems, risk assessment, and capacity building, WRC works to help communities better prepare for and respond to potential disasters.', 'Communities in disaster-prone areas face recurring threats from floods, earthquakes, droughts, and other natural hazards. Proactive investment in disaster preparedness and risk reduction significantly reduces loss of life and economic damage. Community-based DRR approaches empower local populations to take ownership of their resilience-building efforts.', '["Community Preparedness","Risk Assessment","Early Warning","Capacity Building"]', '["At-Risk Communities","Community Leaders","Local Authorities","Schools","Vulnerable Populations"]', '["emergency-response","climate-resilience","livelihood"]', '08', 8),

('climate-resilience', 'Climate Resilience', 'Climate Resilience & Environmental Sustainability', 'fa-solid fa-leaf', 'assets/images/our-work/climate-resilience-hero.jpg', 'assets/images/our-work/climate-resilience-hero.jpg', 'Promoting environmental sustainability and climate-adaptive practices.', 'Supporting communities to adapt to climate change impacts through sustainable practices, green solutions, and environmental protection.', 'Promoting environmental sustainability and climate-adaptive practices.', 'WRC\'s Climate Resilience program supports communities in adapting to the impacts of climate change and promoting environmental sustainability. Through climate-adaptive agriculture, renewable energy solutions, environmental awareness, and ecosystem protection, WRC works to build community resilience against climate-related challenges.', 'Climate change disproportionately affects the most vulnerable communities, threatening food security, water resources, livelihoods, and health. Building community resilience to climate impacts requires a combination of adaptive strategies, sustainable resource management, and awareness raising. Climate-resilient communities are better able to withstand environmental shocks and maintain their development gains.', '["Climate Adaptation","Renewable Energy","Environmental Awareness","Sustainable Practices"]', '["Farming Communities","Rural Households","Youth","Community Organizations","Local Authorities"]', '["disaster-risk-reduction","wash","livelihood"]', '09', 9);

-- =============================================
-- 4. PROGRAM INTERVENTIONS
-- =============================================

-- Health interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(1, 'Community Health Outreach', 'Organizing community-based health activities to bring essential services closer to underserved populations.', 'fa-solid fa-truck-medical', 1),
(1, 'Maternal & Child Health', 'Supporting maternal and child health through awareness, screenings, and referral facilitation.', 'fa-solid fa-person-breastfeeding', 2),
(1, 'Medical Support', 'Facilitating access to essential medicines and medical supplies in communities with limited health infrastructure.', 'fa-solid fa-pills', 3),
(1, 'Health Awareness', 'Conducting health education and awareness sessions on preventive healthcare practices and disease prevention.', 'fa-solid fa-shield-virus', 4),
(1, 'Referral Support', 'Establishing referral pathways to connect community members with appropriate health facilities and services.', 'fa-solid fa-stethoscope', 5),
(1, 'Health Infrastructure', 'Supporting basic health infrastructure improvements to enhance service delivery in underserved areas.', 'fa-solid fa-house-medical', 6);

-- Education interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(2, 'Learning Environment Support', 'Supporting improvements to learning spaces and providing essential educational infrastructure in underserved areas.', 'fa-solid fa-school', 1),
(2, 'Educational Materials', 'Distributing school supplies, learning kits, and educational resources to students who need them most.', 'fa-solid fa-book-open', 2),
(2, 'Teacher Capacity Building', 'Providing training and professional development opportunities for educators to improve teaching quality.', 'fa-solid fa-chalkboard-user', 3),
(2, 'Youth Development', 'Creating opportunities for youth engagement, skills development, and personal growth through targeted programs.', 'fa-solid fa-children', 4),
(2, 'Community Engagement', 'Working with parents and communities to promote the value of education and increase enrollment and retention.', 'fa-solid fa-users', 5),
(2, 'Orphan & Vulnerable Children', 'Providing educational support and care for orphans and vulnerable children to ensure continuity of learning.', 'fa-solid fa-child', 6);

-- WASH interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(3, 'Water Supply Systems', 'Installing and rehabilitating water supply infrastructure including hand pumps, filtration plants, and community water points.', 'fa-solid fa-faucet', 1),
(3, 'Sanitation Facilities', 'Constructing and improving sanitation infrastructure including latrines and community sanitation blocks.', 'fa-solid fa-toilet', 2),
(3, 'Hygiene Promotion', 'Conducting hygiene awareness sessions and distributing hygiene kits to promote healthy practices in communities.', 'fa-solid fa-hands-bubbles', 3),
(3, 'Solar Water Systems', 'Implementing solar-powered water filtration and supply systems for sustainable, clean water access.', 'fa-solid fa-solar-panel', 4),
(3, 'Community Management', 'Training community water management committees to ensure long-term sustainability of WASH infrastructure.', 'fa-solid fa-people-group', 5),
(3, 'Waste Management', 'Supporting community-based waste management solutions and environmental hygiene improvements.', 'fa-solid fa-recycle', 6);

-- Food Security interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(4, 'Food Distribution', 'Organizing structured food distribution drives to provide family ration packages to food-insecure households.', 'fa-solid fa-box-open', 1),
(4, 'Seasonal Food Relief', 'Conducting seasonal and emergency food relief operations during Ramadan, winter, and disaster periods.', 'fa-solid fa-wheat-awn', 2),
(4, 'Emergency Meals', 'Providing ready-to-eat meals and hot food distribution during acute emergencies and disaster response.', 'fa-solid fa-utensils', 3),
(4, 'Community Food Systems', 'Supporting community-based food production initiatives such as kitchen gardens and seed distribution.', 'fa-solid fa-seedling', 4);

-- Nutrition interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(5, 'Child Nutrition Screening', 'Conducting MUAC screening and growth monitoring to identify and refer malnourished children for appropriate treatment.', 'fa-solid fa-baby', 1),
(5, 'Therapeutic Feeding Support', 'Facilitating access to Ready-to-Use Therapeutic Food (RUTF) and supplementary feeding for acutely malnourished children.', 'fa-solid fa-jar', 2),
(5, 'Maternal Nutrition', 'Providing nutritional support and micronutrient supplementation for pregnant and lactating women.', 'fa-solid fa-person-pregnant', 3),
(5, 'Nutrition Education', 'Conducting community-based nutrition education sessions on infant and young child feeding practices.', 'fa-solid fa-comments', 4);

-- Livelihood interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(6, 'Vocational Training', 'Providing market-relevant vocational and technical skills training to youth and adults for income generation.', 'fa-solid fa-screwdriver-wrench', 1),
(6, 'Micro-Enterprise Support', 'Supporting the establishment of small-scale businesses through training, mentorship, and start-up asset provision.', 'fa-solid fa-store', 2),
(6, 'Agricultural Livelihoods', 'Supporting farming communities with training, tools, seeds, and techniques to improve agricultural productivity.', 'fa-solid fa-tractor', 3),
(6, 'Asset Distribution', 'Providing productive assets and tools to help vulnerable households start or strengthen income-generating activities.', 'fa-solid fa-hand-holding-dollar', 4);

-- Emergency Response interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(7, 'Shelter & Relief', 'Distributing emergency shelter materials, tents, and non-food items to disaster-displaced families.', 'fa-solid fa-tent', 1),
(7, 'Emergency Health', 'Providing emergency medical support and first aid during disaster response operations.', 'fa-solid fa-kit-medical', 2),
(7, 'Emergency WASH', 'Ensuring access to safe drinking water and emergency sanitation facilities in disaster-affected areas.', 'fa-solid fa-bottle-water', 3),
(7, 'Relief Coordination', 'Coordinating with authorities, partner organizations, and communities for effective and timely humanitarian response.', 'fa-solid fa-people-carry-box', 4);

-- DRR interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(8, 'Risk Assessment', 'Conducting community-based hazard, vulnerability, and capacity assessments to identify and prioritize disaster risks.', 'fa-solid fa-clipboard-list', 1),
(8, 'Early Warning Systems', 'Supporting the development and strengthening of community-based early warning and communication systems.', 'fa-solid fa-bullhorn', 2),
(8, 'Preparedness Training', 'Conducting disaster preparedness training, simulation exercises, and first aid training for community members.', 'fa-solid fa-person-chalkboard', 3),
(8, 'Community Resilience', 'Supporting community-level resilience planning, disaster contingency planning, and resource mobilization.', 'fa-solid fa-shield', 4);

-- Climate Resilience interventions
INSERT INTO program_interventions (program_id, title, description, icon_class, sort_order) VALUES
(9, 'Renewable Energy', 'Promoting and supporting the adoption of renewable energy solutions including solar power for community use.', 'fa-solid fa-solar-panel', 1),
(9, 'Environmental Protection', 'Supporting tree plantation, afforestation, and natural resource management initiatives in communities.', 'fa-solid fa-tree', 2),
(9, 'Climate-Adaptive Agriculture', 'Introducing climate-resilient farming techniques and crop varieties suited to changing environmental conditions.', 'fa-solid fa-seedling', 3),
(9, 'Environmental Awareness', 'Conducting awareness campaigns on climate change, environmental protection, and sustainable practices.', 'fa-solid fa-lightbulb', 4);

-- =============================================
-- 5. PROGRAM FOCUS AREAS
-- =============================================

INSERT INTO program_focus_areas (program_id, title, sort_order) VALUES
(1, 'Primary Healthcare', 1), (1, 'Community Health', 2), (1, 'Health Awareness', 3), (1, 'Maternal & Child Health', 4),
(2, 'Access to Education', 1), (2, 'Learning Environments', 2), (2, 'Teacher Capacity', 3), (2, 'Youth Development', 4),
(3, 'Clean Water Access', 1), (3, 'Sanitation Facilities', 2), (3, 'Hygiene Promotion', 3), (3, 'Water Infrastructure', 4),
(4, 'Emergency Food Aid', 1), (4, 'Seasonal Relief', 2), (4, 'Community Food Security', 3), (4, 'Household Support', 4),
(5, 'Malnutrition Screening', 1), (5, 'Therapeutic Feeding', 2), (5, 'Nutritional Education', 3), (5, 'Maternal Nutrition', 4),
(6, 'Vocational Training', 1), (6, 'Skills Development', 2), (6, 'Micro-Enterprise Support', 3), (6, 'Asset Distribution', 4),
(7, 'Rapid Relief Operations', 1), (7, 'Shelter & NFI Distribution', 2), (7, 'Emergency Health', 3), (7, 'Coordination', 4),
(8, 'Community Preparedness', 1), (8, 'Risk Assessment', 2), (8, 'Early Warning', 3), (8, 'Capacity Building', 4),
(9, 'Climate Adaptation', 1), (9, 'Renewable Energy', 2), (9, 'Environmental Awareness', 3), (9, 'Sustainable Practices', 4);

-- =============================================
-- 6. HISTORY MILESTONES
-- =============================================

INSERT INTO history_milestones (year, title, description, is_current, sort_order) VALUES
(2005, 'Founded', 'WRC established in Peshawar, Pakistan with a mission to serve flood-affected communities in the region.', FALSE, 1),
(2008, 'First Major Response', 'Emergency relief operations for earthquake-affected regions in Northern Pakistan, marking our first large-scale intervention.', FALSE, 2),
(2010, 'Flood Response Scale-Up', 'Large-scale flood relief reaching 25,000+ families across KPK and Sindh provinces during devastating monsoon floods.', FALSE, 3),
(2013, 'Education Program Launch', 'Inaugurated schools and literacy centers in underserved rural communities, bringing quality education to thousands of children.', FALSE, 4),
(2015, 'WASH Initiative', 'Installed the first 200 clean water wells in water-scarce regions, providing safe drinking water to remote communities.', FALSE, 5),
(2018, 'Health Sector Expansion', 'Deployed mobile health units and organized free medical camps serving 10,000+ patients annually across remote areas.', FALSE, 6),
(2020, 'COVID-19 Response', 'Rapid emergency response providing PPE, food packages, and hygiene kits to vulnerable communities during the global pandemic.', FALSE, 7),
(2022, 'Flood Emergency', 'Major flood response reaching 50,000+ families with shelter, food, and clean water during Pakistan''s catastrophic flooding.', FALSE, 8),
(2024, 'Climate Resilience Program', 'Launched green energy and environmental sustainability initiatives to build long-term climate resilience in vulnerable communities.', FALSE, 9),
(2026, 'Serving Across Pakistan', 'Operating across 4 provinces with 9 humanitarian sectors and 150,000+ lives impacted. Our journey continues with renewed resolve and expanded reach.', TRUE, 10);

-- =============================================
-- 7. TEAM MEMBERS
-- =============================================

INSERT INTO team_members (name, title, role_type, bio, photo_path, sort_order) VALUES
('Mr. Asad Shah', 'Chairman', 'chairman', 'Welcome to WRC. Our organization was founded on a simple yet profound belief: that sustainable development and community service are the cornerstones of a resilient society. Today, more than ever, accountability, transparency, and a deeply rooted commitment to the people we serve guide our every action.', NULL, 1),
('[Name]', 'Executive Director', 'executive', 'Strategic oversight and organizational management.', NULL, 2),
('[Name]', 'Program Director', 'leadership', 'Leading humanitarian and development program design.', NULL, 3),
('[Name]', 'Director of Finance & Admin', 'leadership', 'Ensuring financial accountability and operational efficiency.', NULL, 4),
('[Name]', 'Head of Operations', 'leadership', 'Managing field logistics and emergency deployments.', NULL, 5),
('[Name]', 'Program Manager', 'staff', 'Overseeing program implementation and community engagement.', NULL, 6),
('[Name]', 'M&E Officer', 'staff', 'Monitoring, evaluation, and learning across all programs.', NULL, 7),
('[Name]', 'Field Coordinator', 'staff', 'Coordinating field operations and community mobilization.', NULL, 8),
('[Name]', 'Finance Officer', 'staff', 'Managing financial operations and reporting.', NULL, 9);

-- =============================================
-- 8. ORGANIZATIONAL STRUCTURE
-- =============================================

INSERT INTO organizational_structure (position_title, department, tier, parent_id, description, icon_class, sort_order) VALUES
('Board of Directors', NULL, 1, NULL, 'Strategic Oversight & Governance', 'fa-solid fa-users-rectangle', 1),
('Chairman', NULL, 2, 1, 'Organizational Leadership & Representation', 'fa-solid fa-user-tie', 2),
('Executive Director', NULL, 3, 2, 'Day-to-Day Management & Program Oversight', 'fa-solid fa-briefcase', 3),
('Finance & Admin', 'Finance & Administration', 4, 3, 'Director of Finance & Administration', 'fa-solid fa-calculator', 4),
('Programs', 'Programs & Development', 4, 3, 'Director of Programs & Development', 'fa-solid fa-diagram-project', 5),
('Operations', 'Operations & Logistics', 4, 3, 'Head of Operations & Logistics', 'fa-solid fa-truck', 6),
('Field & Community Implementation', NULL, 5, 3, 'Regional Offices, Community Mobilizers, Field Staff', 'fa-solid fa-map-location-dot', 7);

-- =============================================
-- 9. HISTORY STATS (from history page)
-- =============================================

INSERT INTO impact_stats (stat_value, stat_label, description, icon_class, color, display_page, sort_order) VALUES
('150,000+', 'Lives Impacted', 'Across 4 provinces since inception', 'fa-solid fa-heart-pulse', 'brandRed', 'history', 1),
('1,200+', 'Water Wells Installed', 'Clean water access for rural families', 'fa-solid fa-faucet-drip', 'blue', 'history', 2),
('85,000+', 'Food Packages Distributed', 'Emergency food relief to families in need', 'fa-solid fa-bowl-food', 'amber', 'history', 3),
('12,000', 'Healthcare Visits', 'Free medical camps & clinical services', 'fa-solid fa-stethoscope', 'green', 'history', 4),
('45,000+', 'Children Enrolled', 'Schools & non-formal learning centers', 'fa-solid fa-graduation-cap', 'purple', 'history', 5),
('200+', 'Communities Served', 'Rural & underserved regions reached', 'fa-solid fa-map-location-dot', 'teal', 'history', 6),

-- Homepage stats
('150,000+', 'Lives Impacted', NULL, 'fa-solid fa-heart-pulse', 'brandRed', 'homepage', 10),
('1,200+', 'Water Wells Installed', NULL, 'fa-solid fa-faucet-drip', 'blue', 'homepage', 11),
('85,000+', 'Food Packages Distributed', NULL, 'fa-solid fa-bowl-food', 'amber', 'homepage', 12),
('45,000+', 'Children Enrolled', NULL, 'fa-solid fa-graduation-cap', 'purple', 'homepage', 13),
('9', 'Program Sectors', NULL, 'fa-solid fa-layer-group', 'teal', 'homepage', 14),
('4', 'Provinces Covered', NULL, 'fa-solid fa-map-location-dot', 'brandRed', 'homepage', 15),

-- Impact page stats
('150,000', 'Lives Impacted', 'Across 4 provinces since inception', 'fa-solid fa-heart-pulse', 'brandRed', 'impact', 20),
('1,200', 'Water Wells Installed', 'Clean water access for rural families', 'fa-solid fa-faucet-drip', 'blue', 'impact', 21),
('85,000', 'Food Packages Distributed', 'Emergency food relief to families in need', 'fa-solid fa-bowl-food', 'amber', 'impact', 22),
('12,000', 'Healthcare Visits', 'Free medical camps & clinical services', 'fa-solid fa-stethoscope', 'green', 'impact', 23),
('45,000', 'Children Enrolled', 'Schools & non-formal learning centers', 'fa-solid fa-graduation-cap', 'purple', 'impact', 24),
('200', 'Communities Served', 'Rural & underserved regions reached', 'fa-solid fa-map-location-dot', 'teal', 'impact', 25);

-- =============================================
-- 10. PARTNERS
-- =============================================

INSERT INTO partners (name, logo_path, partner_type, sort_order) VALUES
('Planning & Development Dept.', 'assets/images/partners/Planning & Development Dept..png', 'government', 1),
('Govt. of Khyber Pakhtunkhwa', 'assets/images/partners/Govt. of Khyber Pakhtunkhwa.png', 'government', 2),
('GIZ, Germany', 'assets/images/partners/GIZ, Germany.png', 'international', 3),
('World Health Organization', 'assets/images/partners/World Health Organization.png', 'un', 4),
('Department of Health', 'assets/images/partners/Department of Health.png', 'government', 5),
('Gates Foundation', 'assets/images/partners/gatesfoundation.png', 'foundation', 6),
('European Union', 'assets/images/partners/European Union.jpg', 'international', 7),
('FCDO, UK', 'assets/images/partners/FCDO, UK.jpg', 'international', 8),
('USAID', 'assets/images/partners/usaid-united-states-agency-for-international-logo-png_seeklogo-306631.png', 'international', 9),
('Canada', 'assets/images/partners/Canada Logo.png', 'international', 10),
('World Bank', 'assets/images/partners/world-bank-logo.jpg', 'international', 11),
('United Nations', 'assets/images/partners/un.png', 'un', 12);

-- =============================================
-- 11. PROVINCES
-- =============================================

INSERT INTO provinces (name, slug, description, icon_class, beneficiaries_count, sort_order) VALUES
('Khyber Pakhtunkhwa', 'khyber-pakhtunkhwa', 'From the mountain valleys of Swat to urban Peshawar, WRC delivers emergency flood relief, healthcare, and education programs across KP''s most vulnerable communities.', 'fa-solid fa-mountain', 85000, 1),
('Punjab', 'punjab', 'South Punjab WASH & food aid programs supporting communities affected by floods and drought.', 'fa-solid fa-wheat-awn', 45000, 2),
('Sindh', 'sindh', 'Drought relief & mobile health units serving remote and disaster-affected communities.', 'fa-solid fa-water', 35000, 3),
('Balochistan', 'balochistan', 'Clean water wells & solar energy initiatives for underserved rural communities.', 'fa-solid fa-sun', 20000, 4);

-- =============================================
-- 12. NEWS ARTICLES
-- =============================================

INSERT INTO news_articles (slug, title, excerpt, category, featured_image, author, published_at) VALUES
('emergency-relief-kits-sindh-2026', 'WRC Distributes Emergency Relief Kits to 2,500 Flood-Affected Families in Sindh', 'Our field teams deployed rapid-response units to flood-affected areas in Sindh, delivering essential relief supplies including food parcels, clean water, and medical kits.', 'story', 'assets/images/hero.jpg', 'Ahmed Khan', '2026-08-22'),
('clean-water-kpk-12-villages', 'Clean Water Initiative Reaches 12 Remote Villages in Khyber Pakhtunkhwa', 'WRC''s WASH program successfully installed 18 solar-powered water filtration systems, providing safe drinking water access to over 15,000 residents in underserved areas.', 'story', 'assets/images/water.jpg', 'Fatima Bibi', '2026-08-18'),
('free-medical-camp-peshawar', 'Free Medical Camp Provides 1,800 Consultations in Peshawar District', 'WRC health teams organized a three-day free medical camp in Peshawar, offering free consultations, medicines, and health awareness sessions to underserved communities.', 'story', 'assets/images/food.jpg', 'Dr. Hamza Ali', '2026-08-12'),
('back-to-school-balochistan', 'Back-to-School Campaign Enrolls 3,200 Out-of-School Children in Balochistan', 'Our education initiative partnered with local communities to identify and enroll out-of-school children, providing school kits, uniforms, and learning materials.', 'story', 'assets/images/hero-slider-2.jpg', 'Sara Malik', '2026-08-05'),
('emergency-food-punjab', 'Emergency Food Distribution Reaches 4,000 Families in South Punjab', 'In partnership with local authorities, WRC distributed emergency food rations to flood-hit families across southern Punjab, covering 14 villages in three districts.', 'story', 'assets/images/hero-slider-3.jpg', 'Bilal Ahmed', '2026-07-29'),
('maternal-health-8-districts', 'Maternal Health Program Expands to 8 New Districts Across KPK', 'WRC has expanded its maternal and child health program to eight additional districts in Khyber Pakhtunkhwa, aiming to reduce maternal mortality through skilled birth attendance.', 'story', 'assets/images/chairman.svg', 'Nadia Shah', '2026-07-21'),
('wrc-condemns-violence-humanitarian', 'WRC Condemns Violence Against Humanitarian Workers in Conflict Zones', 'The World Relief Committee strongly condemns the recent attacks on humanitarian convoys and calls upon all parties to respect the neutrality and safety of aid workers.', 'press_release', NULL, 'WRC Media', '2026-08-25'),
('wrc-2025-annual-impact-report', 'WRC Releases 2025 Annual Impact Report: 420,000+ Lives Reached', 'WRC''s annual report highlights a year of significant growth in program delivery, beneficiary reach, and financial transparency across all operational sectors.', 'report', NULL, 'WRC Communications', '2026-08-10'),
('wrc-unicef-mou-wash-balochistan', 'WRC Signs MoU with UNICEF for Joint WASH Programs in Balochistan', 'A new memorandum of understanding between WRC and UNICEF will bring sustainable water and sanitation solutions to 50 communities across underserved districts in Balochistan.', 'press_release', NULL, 'WRC Media', '2026-07-30');

-- =============================================
-- 13. RESOURCES
-- =============================================

INSERT INTO resources (title, description, resource_type, category, file_path, year, sort_order) VALUES
('WRC Annual Report 2023', 'A comprehensive overview of our humanitarian operations, programmatic achievements, beneficiary outreach, and audited financial statements for the fiscal year 2023.', 'annual_report', 'Annual Reports', NULL, 2023, 1),
('WRC Annual Report 2022', 'Financial & Programmatic Overview', 'annual_report', 'Annual Reports', NULL, 2022, 2),
('WRC Annual Report 2021', 'Financial & Programmatic Overview', 'annual_report', 'Annual Reports', NULL, 2021, 3),
('WRC Annual Report 2020', 'Financial & Programmatic Overview', 'annual_report', 'Annual Reports', NULL, 2020, 4),
('Governance Policy', 'Organizational governance framework and board management guidelines.', 'policy', 'Governance', NULL, NULL, 5),
('Code of Conduct', 'Ethical standards and professional behavior expectations for all staff.', 'policy', 'Governance', NULL, NULL, 6),
('Conflict of Interest Policy', 'Guidelines for identifying and managing conflicts of interest.', 'policy', 'Governance', NULL, NULL, 7),
('Child Safeguarding Policy', 'Procedures and standards for protecting children from harm.', 'policy', 'Safeguarding', NULL, NULL, 8),
('Protection Against Sexual Exploitation and Abuse', 'Prevention and response framework for PSEA.', 'policy', 'Safeguarding', NULL, NULL, 9),
('General Safeguarding Policy', 'Overall safeguarding standards for staff and beneficiaries.', 'policy', 'Safeguarding', NULL, NULL, 10),
('Financial Management Policy', 'Financial procedures, controls, and reporting standards.', 'policy', 'Financial Management', NULL, NULL, 11),
('Procurement Policy', 'Guidelines for transparent and fair procurement processes.', 'policy', 'Financial Management', NULL, NULL, 12),
('Anti-Fraud & Anti-Corruption Policy', 'Zero-tolerance framework for fraud and corruption.', 'policy', 'Financial Management', NULL, NULL, 13),
('HR Policy', 'Human resources management guidelines and procedures.', 'policy', 'Human Resources', NULL, NULL, 14),
('Workplace Policy', 'Workplace standards, safety, and employee wellbeing guidelines.', 'policy', 'Human Resources', NULL, NULL, 15);

-- =============================================
-- 14. PROJECTS
-- The authoritative 20-row portfolio is imported from
-- replace_projects_from_wrc_excel.sql; do not seed illustrative projects here.
-- =============================================

-- =============================================
-- 15. CAMPAIGNS
-- =============================================

INSERT INTO campaigns (title, description, goal_amount, raised_amount, image_path, status, sort_order) VALUES
('Emergency Flood Relief Fund', 'Help us provide immediate shelter, food, and clean water to families displaced by devastating floods across Pakistan.', 50000000.00, 32500000.00, 'assets/images/our-work/emergency-response-hero.jpg', 'active', 1),
('Build a Well Campaign', 'Every well provides clean water to 500+ families. Help us install solar-powered water wells in water-scarce communities.', 25000000.00, 18750000.00, 'assets/images/our-work/wash-hero.jpg', 'active', 2),
('Educate a Child Initiative', 'Support education for out-of-school children by providing school supplies, uniforms, and learning materials.', 10000000.00, 7500000.00, 'assets/images/our-work/education-hero.jpg', 'active', 3);

-- =============================================
-- 16. DONATION TIERS
-- =============================================

INSERT INTO donation_tiers (tier_name, amount, currency, description, icon_class, impact_description, sort_order) VALUES
('Basic Support', 500.00, 'PKR', 'Provides essential supplies for one family for a week.', 'fa-solid fa-hand-holding-heart', 'Feeds a family of 5 for one week', 1),
('Community Helper', 1000.00, 'PKR', 'Provides clean water access for a family for one month.', 'fa-solid fa-droplet', 'Provides clean water for a family for 1 month', 2),
('Health Champion', 2500.00, 'PKR', 'Covers medical consultations and medicine for 10 patients.', 'fa-solid fa-stethoscope', 'Covers medical consultations for 10 patients', 3),
('Education Champion', 5000.00, 'PKR', 'Provides school supplies and uniforms for 5 children for a year.', 'fa-solid fa-graduation-cap', 'School supplies for 5 children for 1 year', 4);

-- =============================================
-- 17. BANK ACCOUNTS
-- =============================================

INSERT INTO bank_accounts (bank_name, account_title, account_number, iban, branch, swift_code, sort_order) VALUES
('Habib Bank Limited (HBL)', 'World Relief Committee', '0123-4567-8901-2345', 'PK32HBLB0001234567890123', 'Warsak Road Branch, Peshawar', 'HABPPKKA', 1),
('United Bank Limited (UBL)', 'World Relief Committee', '1234-5678-9012-3456', 'PK45UBLB0012345678901234', 'University Road Branch, Peshawar', 'UNILPKKA', 2);

-- =============================================
-- 18. JOB LISTINGS
-- =============================================

INSERT INTO job_listings (title, department, location, employment_type, description, is_active, deadline) VALUES
('Program Manager', 'Programs', 'Peshawar, Pakistan', 'full_time', 'Lead and manage humanitarian programs across multiple sectors, ensuring effective implementation and compliance with donor requirements.', TRUE, '2026-09-30'),
('Monitoring & Evaluation Officer', 'M&E', 'Peshawar, Pakistan', 'full_time', 'Design and implement M&E frameworks, collect field data, and produce evidence-based reports for program improvement.', TRUE, '2026-09-30'),
('Field Coordinator', 'Operations', 'Swat, Pakistan', 'full_time', 'Coordinate field operations, manage community mobilizers, and oversee project implementation at the district level.', TRUE, '2026-09-30'),
('Finance Officer', 'Finance & Admin', 'Peshawar, Pakistan', 'full_time', 'Manage financial operations including budgeting, accounting, donor reporting, and compliance with financial policies.', TRUE, '2026-09-30');

-- =============================================
-- 19. SDG GOALS
-- =============================================

INSERT INTO sdg_goals (goal_number, title, description, color, sort_order) VALUES
(1, 'No Poverty', 'End poverty in all its forms everywhere', '#E5243B', 1),
(2, 'Zero Hunger', 'End hunger, achieve food security and improved nutrition and promote sustainable agriculture', '#DDA63A', 2),
(3, 'Good Health & Well-Being', 'Ensure healthy lives and promote well-being for all at all ages', '#4C9F38', 3),
(4, 'Quality Education', 'Ensure inclusive and equitable quality education and promote lifelong learning opportunities for all', '#C5192D', 4),
(5, 'Clean Water & Sanitation', 'Ensure availability and sustainable management of water and sanitation for all', '#26BDE2', 5),
(6, 'Affordable & Clean Energy', 'Ensure access to affordable, reliable, sustainable and modern energy for all', '#FCC30B', 6),
(8, 'Decent Work & Economic Growth', 'Promote sustained, inclusive and sustainable economic growth, full and productive employment and decent work for all', '#A21942', 7),
(11, 'Sustainable Cities & Communities', 'Make cities and human settlements inclusive, safe, resilient and sustainable', '#FD9D24', 8),
(13, 'Climate Action', 'Take urgent action to combat climate change and its impacts', '#3F7E44', 9);

-- =============================================
-- 20. SDG CONTRIBUTIONS
-- =============================================

INSERT INTO sdg_contributions (sdg_goal_id, contribution_text, progress_percent, program_id) VALUES
(3, '12,000+ healthcare visits through free medical camps and clinical services across 4 provinces.', 78, 1),
(4, '45,000+ children enrolled in schools and non-formal learning centers in underserved communities.', 72, 2),
(5, '1,200+ water wells installed providing clean water access to rural families.', 85, 3),
(2, '85,000+ food packages distributed to food-insecure families during emergencies and seasonal crises.', 70, 4),
(1, 'Livelihood programs supporting economic empowerment for vulnerable households through skills training.', 55, 6),
(9, 'Climate resilience initiatives including renewable energy and climate-adaptive agriculture.', 40, 9);
