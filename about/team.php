<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Our Team | WRC About';

$teamHero = getRow('homepage_sections', ['section_key' => 'team_hero', 'is_active' => 1]);
$heroData = json_decode($teamHero['content'] ?? '{}', true);

$teamMembers = getRows('team_members', "role_type = 'team' AND is_active = 1 ORDER BY sort_order ASC");

if (empty($teamMembers)) {
    $teamMembers = getRows('team_members', 'is_active = 1 ORDER BY sort_order ASC');
}

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

    <!-- Hero Section -->
    <section class="relative w-full bg-ink-900 text-white min-h-[520px] flex items-center overflow-hidden">
      <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900/40"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/50"></div>
      </div>

      <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="max-w-7xl mx-auto">
          <nav class="flex items-center gap-2 text-sm text-white/60 mb-8" aria-label="Breadcrumb">
            <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a href="<?= $base ?>about/index.php" class="hover:text-white transition-colors">About Us</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-white font-medium">Our Team</span>
          </nav>

          <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 max-w-3xl">
            <?= (!empty($teamHero['section_title'])) ? sanitize($teamHero['section_title']) : (!empty($heroData['section_title']) ? sanitize($heroData['section_title']) : 'Our Team') ?>
          </h1>
          <p class="text-lg md:text-xl text-sand-50/80 max-w-2xl mb-10 leading-relaxed">
            <?= (!empty($teamHero['section_subtitle'])) ? sanitize($teamHero['section_subtitle']) : (!empty($heroData['subtitle']) ? sanitize($heroData['subtitle']) : (!empty($heroData['section_subtitle']) ? sanitize($heroData['section_subtitle']) : 'Meet the dedicated individuals driving our mission forward and making a lasting impact in communities around the world.')) ?>
          </p>
        </div>
      </div>
    </section>

    <!-- Team Grid -->
    <section class="py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mb-4">Our Team</h2>
          <p class="text-slate-600">Passionate professionals committed to delivering meaningful humanitarian impact.</p>
        </div>

        <?php if (!empty($teamMembers)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <?php foreach ($teamMembers as $member): ?>
          <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden hover:shadow-2xl hover:shadow-slate-900/10 hover:-translate-y-2 transition-all duration-500 group flex flex-col h-full relative">
            <!-- Top Accent Line -->
            <div class="h-1.5 w-full bg-gradient-to-r from-brandRed via-red-500 to-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

            <!-- Member Image -->
            <div class="relative aspect-[4/4.5] overflow-hidden bg-slate-100">
              <?php if (!empty($member['photo_path'])): ?>
                <img src="<?= $base ?><?= sanitize($member['photo_path']) ?>" alt="<?= sanitize($member['full_name'] ?? $member['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
              <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex flex-col items-center justify-center p-6 text-center">
                  <div class="w-20 h-20 rounded-full bg-brandRed/10 border border-brandRed/20 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-user text-3xl text-brandRed"></i>
                  </div>
                  <span class="text-xs text-slate-400 font-medium uppercase tracking-wider font-heading">WRC Team</span>
                </div>
              <?php endif; ?>
              
              <!-- Role Badge -->
              <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-slate-900/85 backdrop-blur-md text-[10px] font-bold tracking-widest uppercase text-white shadow-md border border-white/10">
                <?= sanitize(strtoupper($member['role_type'] ?? 'TEAM')) ?>
              </div>
            </div>

            <!-- Member Content -->
            <div class="p-6 flex flex-col flex-grow justify-between bg-white">
              <div>
                <h3 class="text-xl font-bold font-display text-slate-900 group-hover:text-brandRed transition-colors duration-300 leading-snug mb-1">
                  <?= sanitize($member['full_name'] ?? $member['name'] ?? '') ?>
                </h3>
                <p class="text-brandRed text-xs font-semibold uppercase tracking-wider mb-4 flex items-center gap-1.5">
                  <i class="fa-solid fa-id-badge text-[10px] opacity-70"></i>
                  <?= sanitize($member['role_title'] ?? $member['title'] ?? 'Team Member') ?>
                </p>
                <?php if (!empty($member['description'] ?? $member['bio'] ?? null)): ?>
                  <p class="text-slate-600 text-sm leading-relaxed mb-6 line-clamp-3">
                    <?= sanitize($member['description'] ?? $member['bio'] ?? '') ?>
                  </p>
                <?php endif; ?>
              </div>

              <!-- Footer Social Links -->
              <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                <div class="flex items-center gap-2">
                  <?php if (!empty($member['email'])): ?>
                    <a href="mailto:<?= sanitize($member['email']) ?>" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all duration-300" aria-label="Email">
                      <i class="fa-solid fa-envelope text-xs"></i>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($member['linkedin_url'])): ?>
                    <a href="<?= sanitize($member['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-500 flex items-center justify-center transition-all duration-300" aria-label="LinkedIn">
                      <i class="fa-brands fa-linkedin-in text-xs"></i>
                    </a>
                  <?php endif; ?>
                  <?php if (empty($member['email']) && empty($member['linkedin_url'])): ?>
                    <span class="text-xs text-slate-400 font-medium">World Relief Committee</span>
                  <?php endif; ?>
                </div>
                <span class="text-xs font-semibold text-slate-400 group-hover:text-brandRed flex items-center gap-1 transition-colors">
                  Profile <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12 text-slate-400">
          <i class="fa-solid fa-users text-4xl mb-4"></i>
          <p>Team information will be available soon.</p>
        </div>
        <?php endif; ?>

        <div class="mt-16 text-center">
          <p class="text-slate-500 text-sm italic">More team members will be updated here soon.</p>
        </div>
      </div>
    </section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>