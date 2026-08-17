/**
 * WRC Program & Project Data Architecture
 * Centralized data for the Our Work section
 * 
 * CONTENT NOTE: All content marked with [EDITABLE] can be updated 
 * with verified WRC information when available.
 */

const WRC_PROGRAMS = [
  {
    slug: 'health',
    title: 'Health',
    fullTitle: 'Health & Healthcare Services',
    icon: 'fa-solid fa-heart-pulse',
    heroImage: '../assets/images/our-work/health-hero.jpg',
    fallbackImage: '../assets/images/hero.jpg',
    description: 'Supporting access to essential healthcare and community wellbeing.',
    heroDescription: 'Supporting healthier and more resilient communities through accessible, community-focused health initiatives.',
    overview: 'WRC\'s Health program works to improve access to essential healthcare services in underserved communities. Through mobile health units, community health awareness campaigns, and targeted medical support, WRC aims to strengthen the health and wellbeing of vulnerable populations, particularly women, children, and those living in remote or crisis-affected areas.',
    whyItMatters: 'Access to basic healthcare remains a significant challenge for millions of people in underserved and remote areas. Limited health infrastructure, shortage of trained health workers, and barriers to access disproportionately affect women, children, and elderly populations. Community-focused health interventions can help bridge these gaps and strengthen the overall resilience of communities.',
    focusAreas: ['Primary Healthcare', 'Community Health', 'Health Awareness', 'Maternal & Child Health'],
    interventions: [
      { icon: 'fa-solid fa-truck-medical', title: 'Community Health Outreach', description: 'Organizing community-based health activities to bring essential services closer to underserved populations.' },
      { icon: 'fa-solid fa-person-breastfeeding', title: 'Maternal & Child Health', description: 'Supporting maternal and child health through awareness, screenings, and referral facilitation.' },
      { icon: 'fa-solid fa-pills', title: 'Medical Support', description: 'Facilitating access to essential medicines and medical supplies in communities with limited health infrastructure.' },
      { icon: 'fa-solid fa-shield-virus', title: 'Health Awareness', description: 'Conducting health education and awareness sessions on preventive healthcare practices and disease prevention.' },
      { icon: 'fa-solid fa-stethoscope', title: 'Referral Support', description: 'Establishing referral pathways to connect community members with appropriate health facilities and services.' },
      { icon: 'fa-solid fa-house-medical', title: 'Health Infrastructure', description: 'Supporting basic health infrastructure improvements to enhance service delivery in underserved areas.' }
    ],
    targetGroups: ['Children', 'Women', 'Elderly', 'Persons with Disabilities', 'Underserved Communities'],
    relatedPrograms: ['nutrition', 'wash', 'emergency-response'],
    sectorNumber: '01'
  },
  {
    slug: 'education',
    title: 'Education',
    fullTitle: 'Education & Youth Development',
    icon: 'fa-solid fa-graduation-cap',
    heroImage: '../assets/images/our-work/education-hero.jpg',
    fallbackImage: '../assets/images/hero.jpg',
    description: 'Enabling access to quality education and learning opportunities for all.',
    heroDescription: 'Empowering communities through inclusive, quality education and youth development initiatives that create lasting opportunities.',
    overview: 'WRC\'s Education program focuses on improving access to quality education, particularly for children and youth in underserved and crisis-affected communities. Through support for learning environments, educational materials, teacher capacity building, and community engagement, WRC works to create meaningful educational opportunities.',
    whyItMatters: 'Education is a fundamental right and one of the most effective tools for sustainable development. Yet millions of children, particularly girls, remain out of school due to poverty, displacement, or lack of infrastructure. Quality education empowers individuals, strengthens communities, and builds the foundation for long-term resilience and prosperity.',
    focusAreas: ['Access to Education', 'Learning Environments', 'Teacher Capacity', 'Youth Development'],
    interventions: [
      { icon: 'fa-solid fa-school', title: 'Learning Environment Support', description: 'Supporting improvements to learning spaces and providing essential educational infrastructure in underserved areas.' },
      { icon: 'fa-solid fa-book-open', title: 'Educational Materials', description: 'Distributing school supplies, learning kits, and educational resources to students who need them most.' },
      { icon: 'fa-solid fa-chalkboard-user', title: 'Teacher Capacity Building', description: 'Providing training and professional development opportunities for educators to improve teaching quality.' },
      { icon: 'fa-solid fa-children', title: 'Youth Development', description: 'Creating opportunities for youth engagement, skills development, and personal growth through targeted programs.' },
      { icon: 'fa-solid fa-users', title: 'Community Engagement', description: 'Working with parents and communities to promote the value of education and increase enrollment and retention.' },
      { icon: 'fa-solid fa-child', title: 'Orphan & Vulnerable Children', description: 'Providing educational support and care for orphans and vulnerable children to ensure continuity of learning.' }
    ],
    targetGroups: ['Children', 'Youth', 'Girls', 'Orphans', 'Teachers', 'Communities'],
    relatedPrograms: ['health', 'livelihood', 'nutrition'],
    sectorNumber: '02'
  },
  {
    slug: 'wash',
    title: 'WASH',
    fullTitle: 'Water, Sanitation & Hygiene',
    icon: 'fa-solid fa-faucet-drip',
    heroImage: '../assets/images/our-work/wash-hero.jpg',
    fallbackImage: '../assets/images/water.jpg',
    description: 'Providing clean water, sanitation facilities and hygiene promotion.',
    heroDescription: 'Ensuring communities have access to safe water, adequate sanitation, and improved hygiene practices for better health and dignity.',
    overview: 'WRC\'s WASH program addresses the critical need for clean water, safe sanitation, and hygiene promotion in vulnerable communities. Through infrastructure development, community-based management, and behavior change communication, WRC works to reduce waterborne diseases and improve overall community health and dignity.',
    whyItMatters: 'Clean water and adequate sanitation are essential for health, dignity, and development. Contaminated water and poor sanitation contribute significantly to disease, particularly among children. Community-level WASH interventions are among the most cost-effective health measures, preventing disease and supporting education, livelihoods, and overall community wellbeing.',
    focusAreas: ['Clean Water Access', 'Sanitation Facilities', 'Hygiene Promotion', 'Water Infrastructure'],
    interventions: [
      { icon: 'fa-solid fa-faucet', title: 'Water Supply Systems', description: 'Installing and rehabilitating water supply infrastructure including hand pumps, filtration plants, and community water points.' },
      { icon: 'fa-solid fa-toilet', title: 'Sanitation Facilities', description: 'Constructing and improving sanitation infrastructure including latrines and community sanitation blocks.' },
      { icon: 'fa-solid fa-hands-bubbles', title: 'Hygiene Promotion', description: 'Conducting hygiene awareness sessions and distributing hygiene kits to promote healthy practices in communities.' },
      { icon: 'fa-solid fa-solar-panel', title: 'Solar Water Systems', description: 'Implementing solar-powered water filtration and supply systems for sustainable, clean water access.' },
      { icon: 'fa-solid fa-people-group', title: 'Community Management', description: 'Training community water management committees to ensure long-term sustainability of WASH infrastructure.' },
      { icon: 'fa-solid fa-recycle', title: 'Waste Management', description: 'Supporting community-based waste management solutions and environmental hygiene improvements.' }
    ],
    targetGroups: ['Households', 'Schools', 'Communities', 'Women', 'Children'],
    relatedPrograms: ['health', 'nutrition', 'climate-resilience'],
    sectorNumber: '03'
  },
  {
    slug: 'food-security',
    title: 'Food Security',
    fullTitle: 'Food Security & Hunger Relief',
    icon: 'fa-solid fa-bowl-food',
    heroImage: '../assets/images/our-work/food-security-hero.jpg',
    fallbackImage: '../assets/images/food.jpg',
    description: 'Addressing food insecurity and hunger in vulnerable communities.',
    heroDescription: 'Working to ensure vulnerable communities have reliable access to sufficient, safe, and nutritious food through targeted interventions.',
    overview: 'WRC\'s Food Security program addresses immediate food needs and works toward longer-term food security for vulnerable communities. Through emergency food distributions, seasonal relief drives, and community-based food security initiatives, WRC aims to reduce hunger and improve nutritional outcomes for families affected by crisis and poverty.',
    whyItMatters: 'Food insecurity affects millions of families, particularly in areas impacted by natural disasters, conflict, and poverty. Hunger undermines health, education, and economic opportunity. Timely food assistance during emergencies saves lives, while longer-term food security interventions help communities build resilience against future shocks.',
    focusAreas: ['Emergency Food Aid', 'Seasonal Relief', 'Community Food Security', 'Household Support'],
    interventions: [
      { icon: 'fa-solid fa-box-open', title: 'Food Distribution', description: 'Organizing structured food distribution drives to provide family ration packages to food-insecure households.' },
      { icon: 'fa-solid fa-wheat-awn', title: 'Seasonal Food Relief', description: 'Conducting seasonal and emergency food relief operations during Ramadan, winter, and disaster periods.' },
      { icon: 'fa-solid fa-utensils', title: 'Emergency Meals', description: 'Providing ready-to-eat meals and hot food distribution during acute emergencies and disaster response.' },
      { icon: 'fa-solid fa-seedling', title: 'Community Food Systems', description: 'Supporting community-based food production initiatives such as kitchen gardens and seed distribution.' }
    ],
    targetGroups: ['Food-Insecure Households', 'Disaster-Affected Families', 'Children', 'Elderly', 'Vulnerable Communities'],
    relatedPrograms: ['nutrition', 'livelihood', 'emergency-response'],
    sectorNumber: '04'
  },
  {
    slug: 'nutrition',
    title: 'Nutrition',
    fullTitle: 'Nutrition & Wellbeing',
    icon: 'fa-solid fa-apple-whole',
    heroImage: '../assets/images/our-work/nutrition-hero.jpg',
    fallbackImage: '../assets/images/hero.jpg',
    description: 'Tackling malnutrition and improving nutritional wellbeing in communities.',
    heroDescription: 'Addressing malnutrition through screening, treatment support, and nutritional education for vulnerable children and mothers.',
    overview: 'WRC\'s Nutrition program focuses on preventing and addressing malnutrition, particularly among children under five and pregnant and lactating women. Through malnutrition screening, therapeutic feeding support, nutritional education, and integration with health services, WRC works to improve nutritional outcomes in vulnerable communities.',
    whyItMatters: 'Malnutrition remains one of the leading contributors to child mortality and long-term developmental challenges. Acute and chronic malnutrition affect millions of children, with lasting consequences on physical growth, cognitive development, and overall health. Early detection and community-based nutrition interventions are critical to saving lives and supporting healthy development.',
    focusAreas: ['Malnutrition Screening', 'Therapeutic Feeding', 'Nutritional Education', 'Maternal Nutrition'],
    interventions: [
      { icon: 'fa-solid fa-baby', title: 'Child Nutrition Screening', description: 'Conducting MUAC screening and growth monitoring to identify and refer malnourished children for appropriate treatment.' },
      { icon: 'fa-solid fa-jar', title: 'Therapeutic Feeding Support', description: 'Facilitating access to Ready-to-Use Therapeutic Food (RUTF) and supplementary feeding for acutely malnourished children.' },
      { icon: 'fa-solid fa-person-pregnant', title: 'Maternal Nutrition', description: 'Providing nutritional support and micronutrient supplementation for pregnant and lactating women.' },
      { icon: 'fa-solid fa-comments', title: 'Nutrition Education', description: 'Conducting community-based nutrition education sessions on infant and young child feeding practices.' }
    ],
    targetGroups: ['Children Under 5', 'Pregnant Women', 'Lactating Mothers', 'Malnourished Individuals', 'Vulnerable Families'],
    relatedPrograms: ['health', 'food-security', 'wash'],
    sectorNumber: '05'
  },
  {
    slug: 'livelihood',
    title: 'Livelihood',
    fullTitle: 'Livelihood & Economic Empowerment',
    icon: 'fa-solid fa-briefcase',
    heroImage: '../assets/images/our-work/livelihood-hero.jpg',
    fallbackImage: '../assets/images/our-work/livelihood-hero.jpg',
    description: 'Building sustainable livelihoods and economic resilience in communities.',
    heroDescription: 'Strengthening economic resilience through skills development, vocational training, and sustainable livelihood support for vulnerable communities.',
    overview: 'WRC\'s Livelihood program supports economic empowerment and sustainable income generation for vulnerable communities. Through vocational training, skills development, micro-enterprise support, and asset distribution, WRC helps individuals and families build sustainable livelihoods and reduce their dependence on humanitarian assistance.',
    whyItMatters: 'Sustainable livelihoods are essential for long-term community resilience. Without economic opportunities, families remain trapped in cycles of poverty and vulnerability. Skills development, vocational training, and support for small-scale enterprise creation help individuals gain economic independence, contribute to their communities, and build resilience against future shocks.',
    focusAreas: ['Vocational Training', 'Skills Development', 'Micro-Enterprise Support', 'Asset Distribution'],
    interventions: [
      { icon: 'fa-solid fa-screwdriver-wrench', title: 'Vocational Training', description: 'Providing market-relevant vocational and technical skills training to youth and adults for income generation.' },
      { icon: 'fa-solid fa-store', title: 'Micro-Enterprise Support', description: 'Supporting the establishment of small-scale businesses through training, mentorship, and start-up asset provision.' },
      { icon: 'fa-solid fa-tractor', title: 'Agricultural Livelihoods', description: 'Supporting farming communities with training, tools, seeds, and techniques to improve agricultural productivity.' },
      { icon: 'fa-solid fa-hand-holding-dollar', title: 'Asset Distribution', description: 'Providing productive assets and tools to help vulnerable households start or strengthen income-generating activities.' }
    ],
    targetGroups: ['Youth', 'Women', 'Displaced Households', 'Small-Scale Farmers', 'Vulnerable Families'],
    relatedPrograms: ['education', 'food-security', 'disaster-risk-reduction'],
    sectorNumber: '06'
  },
  {
    slug: 'emergency-response',
    title: 'Emergency Response',
    fullTitle: 'Emergency Response & Disaster Relief',
    icon: 'fa-solid fa-truck-medical',
    heroImage: '../assets/images/our-work/emergency-response-hero.jpg',
    fallbackImage: '../assets/images/our-work/emergency-response-hero.jpg',
    description: 'Providing rapid humanitarian assistance during emergencies and disasters.',
    heroDescription: 'Delivering rapid, life-saving humanitarian assistance to communities affected by natural disasters, crises, and emergencies.',
    overview: 'WRC\'s Emergency Response program provides rapid humanitarian assistance to communities affected by natural disasters, crises, and emergencies. Through coordinated relief operations, WRC delivers essential supplies including shelter, food, clean water, medical support, and non-food items to help affected communities survive and begin recovery.',
    whyItMatters: 'Natural disasters and emergencies can devastate communities within hours, destroying homes, infrastructure, and livelihoods. Rapid, coordinated humanitarian response is critical to saving lives and reducing suffering. Effective emergency response requires pre-positioning of supplies, trained response teams, and strong coordination with local authorities and partner organizations.',
    focusAreas: ['Rapid Relief Operations', 'Shelter & NFI Distribution', 'Emergency Health', 'Coordination'],
    interventions: [
      { icon: 'fa-solid fa-tent', title: 'Shelter & Relief', description: 'Distributing emergency shelter materials, tents, and non-food items to disaster-displaced families.' },
      { icon: 'fa-solid fa-kit-medical', title: 'Emergency Health', description: 'Providing emergency medical support and first aid during disaster response operations.' },
      { icon: 'fa-solid fa-bottle-water', title: 'Emergency WASH', description: 'Ensuring access to safe drinking water and emergency sanitation facilities in disaster-affected areas.' },
      { icon: 'fa-solid fa-people-carry-box', title: 'Relief Coordination', description: 'Coordinating with authorities, partner organizations, and communities for effective and timely humanitarian response.' }
    ],
    targetGroups: ['Disaster-Affected Communities', 'Displaced Families', 'Women & Children', 'Elderly', 'Persons with Disabilities'],
    relatedPrograms: ['health', 'food-security', 'disaster-risk-reduction'],
    sectorNumber: '07'
  },
  {
    slug: 'disaster-risk-reduction',
    title: 'Disaster Risk Reduction',
    fullTitle: 'Disaster Risk Reduction & Preparedness',
    icon: 'fa-solid fa-triangle-exclamation',
    heroImage: '../assets/images/our-work/disaster-risk-reduction-hero.jpg',
    fallbackImage: '../assets/images/our-work/disaster-risk-reduction-hero.jpg',
    description: 'Strengthening community preparedness and reducing disaster vulnerability.',
    heroDescription: 'Building community capacity to prepare for, respond to, and recover from disasters through awareness, training, and resilience-building.',
    overview: 'WRC\'s Disaster Risk Reduction program focuses on strengthening community resilience and reducing vulnerability to natural hazards. Through community-based disaster preparedness training, early warning systems, risk assessment, and capacity building, WRC works to help communities better prepare for and respond to potential disasters.',
    whyItMatters: 'Communities in disaster-prone areas face recurring threats from floods, earthquakes, droughts, and other natural hazards. Proactive investment in disaster preparedness and risk reduction significantly reduces loss of life and economic damage. Community-based DRR approaches empower local populations to take ownership of their resilience-building efforts.',
    focusAreas: ['Community Preparedness', 'Risk Assessment', 'Early Warning', 'Capacity Building'],
    interventions: [
      { icon: 'fa-solid fa-clipboard-list', title: 'Risk Assessment', description: 'Conducting community-based hazard, vulnerability, and capacity assessments to identify and prioritize disaster risks.' },
      { icon: 'fa-solid fa-bullhorn', title: 'Early Warning Systems', description: 'Supporting the development and strengthening of community-based early warning and communication systems.' },
      { icon: 'fa-solid fa-person-chalkboard', title: 'Preparedness Training', description: 'Conducting disaster preparedness training, simulation exercises, and first aid training for community members.' },
      { icon: 'fa-solid fa-shield', title: 'Community Resilience', description: 'Supporting community-level resilience planning, disaster contingency planning, and resource mobilization.' }
    ],
    targetGroups: ['At-Risk Communities', 'Community Leaders', 'Local Authorities', 'Schools', 'Vulnerable Populations'],
    relatedPrograms: ['emergency-response', 'climate-resilience', 'livelihood'],
    sectorNumber: '08'
  },
  {
    slug: 'climate-resilience',
    title: 'Climate Resilience',
    fullTitle: 'Climate Resilience & Environmental Sustainability',
    icon: 'fa-solid fa-leaf',
    heroImage: '../assets/images/our-work/climate-resilience-hero.jpg',
    fallbackImage: '../assets/images/our-work/climate-resilience-hero.jpg',
    description: 'Promoting environmental sustainability and climate-adaptive practices.',
    heroDescription: 'Supporting communities to adapt to climate change impacts through sustainable practices, green solutions, and environmental protection.',
    overview: 'WRC\'s Climate Resilience program supports communities in adapting to the impacts of climate change and promoting environmental sustainability. Through climate-adaptive agriculture, renewable energy solutions, environmental awareness, and ecosystem protection, WRC works to build community resilience against climate-related challenges.',
    whyItMatters: 'Climate change disproportionately affects the most vulnerable communities, threatening food security, water resources, livelihoods, and health. Building community resilience to climate impacts requires a combination of adaptive strategies, sustainable resource management, and awareness raising. Climate-resilient communities are better able to withstand environmental shocks and maintain their development gains.',
    focusAreas: ['Climate Adaptation', 'Renewable Energy', 'Environmental Awareness', 'Sustainable Practices'],
    interventions: [
      { icon: 'fa-solid fa-solar-panel', title: 'Renewable Energy', description: 'Promoting and supporting the adoption of renewable energy solutions including solar power for community use.' },
      { icon: 'fa-solid fa-tree', title: 'Environmental Protection', description: 'Supporting tree plantation, afforestation, and natural resource management initiatives in communities.' },
      { icon: 'fa-solid fa-seedling', title: 'Climate-Adaptive Agriculture', description: 'Introducing climate-resilient farming techniques and crop varieties suited to changing environmental conditions.' },
      { icon: 'fa-solid fa-lightbulb', title: 'Environmental Awareness', description: 'Conducting awareness campaigns on climate change, environmental protection, and sustainable practices.' }
    ],
    targetGroups: ['Farming Communities', 'Rural Households', 'Youth', 'Community Organizations', 'Local Authorities'],
    relatedPrograms: ['disaster-risk-reduction', 'wash', 'livelihood'],
    sectorNumber: '09'
  }
];

/**
 * Sample project template structure
 * [EDITABLE] Replace with verified WRC project data
 */
const WRC_SAMPLE_PROJECTS = [
  {
    slug: 'community-health-outreach',
    program: 'health',
    title: '[Project Title — Editable]',
    location: '[Location — Editable]',
    duration: '[Implementation Period — Editable]',
    status: 'Active',
    description: '[Project description to be updated with verified information.]',
    objectives: [
      '[Objective 1 — Editable]',
      '[Objective 2 — Editable]',
      '[Objective 3 — Editable]'
    ],
    activities: [
      'Community needs assessment',
      'Program planning and design',
      'Implementation and service delivery',
      'Monitoring and reporting'
    ],
    gallery: []
  }
];

// Helper to get program by slug
function getProgramBySlug(slug) {
  return WRC_PROGRAMS.find(p => p.slug === slug) || null;
}

// Helper to get related programs
function getRelatedPrograms(slugs) {
  return slugs.map(s => WRC_PROGRAMS.find(p => p.slug === s)).filter(Boolean);
}
