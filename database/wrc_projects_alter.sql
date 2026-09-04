USE wrc_website;

-- Add missing columns to projects table for detail page
ALTER TABLE projects
  ADD COLUMN objectives JSON AFTER overview,
  ADD COLUMN target_groups JSON AFTER objectives,
  ADD COLUMN timeline JSON AFTER target_groups,
  ADD COLUMN gallery_images JSON AFTER timeline,
  ADD COLUMN overview_image VARCHAR(500) AFTER gallery_images,
  ADD COLUMN donors_count INT UNSIGNED DEFAULT 0 AFTER budget_currency,
  ADD COLUMN related_projects JSON AFTER donors_count;

-- Update seed data with objectives, target_groups, timeline, gallery for existing projects
UPDATE projects SET
  objectives = '[{"title":"Emergency Shelter & Food","description":"Deliver emergency relief kits including tents, blankets, food rations, and clean drinking water to displaced families within the first 30 days."},{"title":"Restore Healthcare","description":"Establish mobile health units and temporary medical camps to provide primary healthcare and disease prevention."},{"title":"Rebuild Livelihoods","description":"Support the reconstruction of damaged homes, schools, and water systems while providing livelihood grants."},{"title":"Community Resilience","description":"Train local volunteers in disaster preparedness, early warning systems, and emergency response protocols."}]',
  target_groups = '[{"name":"Flood-affected Families","icon":"fa-solid fa-house-crack"},{"name":"Women & Children","icon":"fa-solid fa-person-pregnant"},{"name":"Elderly Persons","icon":"fa-solid fa-person-cane"},{"name":"Disabled Persons","icon":"fa-solid fa-wheelchair-move"}]',
  timeline = '[{"date":"January 2025","title":"Rapid Needs Assessment","description":"Comprehensive needs assessments across affected villages.","icon":"fa-solid fa-magnifying-glass"},{"date":"February 2025","title":"Planning & Resource Mobilization","description":"Developed implementation plan, secured donor funding.","icon":"fa-solid fa-clipboard-list"},{"date":"March - November 2025","title":"Implementation Phase","description":"Full-scale relief distribution, mobile health operations.","icon":"fa-solid fa-truck-medical"},{"date":"December 2025","title":"Monitoring & Evaluation","description":"Final evaluation, impact assessment, lessons learned.","icon":"fa-solid fa-flag-checkered"}]',
  gallery_images = '[{"path":"assets/images/hero.jpg","caption":"Relief distribution in progress"}]',
  donors_count = 3,
  related_projects = '[2,3]'
WHERE id = 1;

UPDATE projects SET
  objectives = '[{"title":"Install Water Wells","description":"Drill and install sustainable water wells in underserved rural communities."},{"title":"Water Purification","description":"Deploy solar-powered water purification systems for clean drinking water."},{"title":"Community Training","description":"Train local water committees on maintenance and management."}]',
  target_groups = '[{"name":"Rural Communities","icon":"fa-solid fa-house"},{"name":"Women & Children","icon":"fa-solid fa-person-pregnant"}]',
  timeline = '[{"date":"Q1 2025","title":"Site Assessment","description":"Identified optimal well locations through hydrogeological surveys.","icon":"fa-solid fa-magnifying-glass"},{"date":"Q2-Q3 2025","title":"Installation","description":"Drilling and installing water well infrastructure.","icon":"fa-solid fa-wrench"},{"date":"Q4 2025","title":"Handover","description":"Community training and official handover ceremonies.","icon":"fa-solid fa-handshake"}]',
  gallery_images = '[{"path":"assets/images/water.jpg","caption":"Clean water well installation"}]',
  donors_count = 2,
  related_projects = '[1,3]'
WHERE id = 2;

UPDATE projects SET
  objectives = '[{"title":"Train Health Workers","description":"Train 200+ community health workers in primary healthcare delivery."},{"title":"Health Camps","description":"Establish regular mobile health camps in remote areas."},{"title":"Maternal Health","description":"Provide maternal and child health services."}]',
  target_groups = '[{"name":"Remote Communities","icon":"fa-solid fa-mountain"},{"name":"Mothers & Children","icon":"fa-solid fa-baby"}]',
  timeline = '[{"date":"January 2025","title":"Recruitment","description":"Selected and enrolled 200 community health worker candidates.","icon":"fa-solid fa-users"},{"date":"March 2025","title":"Training","description":"Completed intensive healthcare training program.","icon":"fa-solid fa-graduation-cap"},{"date":"June 2025","title":"Deployment","description":"Deployed health workers across target communities.","icon":"fa-solid fa-truck-medical"}]',
  gallery_images = '[{"path":"assets/images/health.jpg","caption":"Community health training session"}]',
  donors_count = 2,
  related_projects = '[1,2]'
WHERE id = 3;

UPDATE projects SET
  objectives = '[{"title":"Distribute Food Packs","description":"Distribute monthly food ration packs to low-income families."},{"title":"Nutrition Support","description":"Provide nutritional supplements for children and pregnant women."}]',
  target_groups = '[{"name":"Low-income Families","icon":"fa-solid fa-house"},{"name":"Children","icon":"fa-solid fa-child"}]',
  timeline = '[{"date":"Ramadan 2025","title":"Distribution","description":"Mass food distribution across Punjab province.","icon":"fa-solid fa-bowl-food"}]',
  gallery_images = '[{"path":"assets/images/food.jpg","caption":"Food ration distribution"}]',
  donors_count = 2,
  related_projects = '[1,2]'
WHERE id = 4;

UPDATE projects SET
  objectives = '[{"title":"Rebuild Schools","description":"Rebuild and renovate flood-damaged schools."},{"title":"Education Access","description":"Restore education access for affected students."}]',
  target_groups = '[{"name":"Students","icon":"fa-solid fa-user-graduate"},{"name":"Teachers","icon":"fa-solid fa-chalkboard-user"}]',
  timeline = '[{"date":"Q2 2025","title":"Assessment","description":"Assessed damage across 15 schools.","icon":"fa-solid fa-magnifying-glass"},{"date":"Q3 2025","title":"Reconstruction","description":"Completed renovation of all 15 schools.","icon":"fa-solid fa-hammer"}]',
  gallery_images = '[{"path":"assets/images/hero.jpg","caption":"School rehabilitation"}]',
  donors_count = 1,
  related_projects = '[1,2]'
WHERE id = 5;

UPDATE projects SET
  objectives = '[{"title":"Winter Shelter","description":"Provide warm winter shelters to displaced families."},{"title":"Heating Essentials","description":"Distribute heating fuel and warm clothing."}]',
  target_groups = '[{"name":"Displaced Families","icon":"fa-solid fa-house-crack"},{"name":"Elderly","icon":"fa-solid fa-person-cane"}]',
  timeline = '[{"date":"November 2025","title":"Distribution","description":"Distributed winter relief kits across KPK.","icon":"fa-solid fa-snowflake"}]',
  gallery_images = '[{"path":"assets/images/hero.jpg","caption":"Winter shelter distribution"}]',
  donors_count = 1,
  related_projects = '[1,2]'
WHERE id = 6;
