<?php
/**
 * WRC Website - Footer Include
 * Outputs: Footer section + JS includes + </body></html>
 * 
 * Usage: Set $base before including (e.g., $base = '../' for sub-pages)
 */

if (!isset($base)) $base = '';
?>

<!-- FOOTER -->
  <footer class="bg-slate-950 text-slate-300 text-sm mt-auto border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">

        <div class="xl:col-span-4 space-y-5">
          <a href="<?= $base ?>index.php" class="inline-flex items-center gap-3">
            <img src="<?= $base ?>assets/images/logo.png" alt="WRC logo" class="w-16 h-16 object-contain rounded-2xl bg-white p-2 border border-slate-800" />
            <div>
              <p class="text-lg font-semibold text-white font-heading">World Relief <br> Committee</p>
            </div>
          </a>
          <p class="max-w-md leading-relaxed text-slate-400">WRC partners with institutions, governments, and field agencies to deliver clean water, health services, education, and emergency relief across vulnerable communities.</p>
          <div class="flex flex-wrap gap-3">
            <a href="<?= $base ?>get-involved/contribute-with-us.php" class="inline-flex items-center justify-center rounded-full bg-brandRed px-5 py-3 text-white text-sm font-semibold shadow-lg shadow-brandRed/20 hover:bg-[#a30000] transition">Contribute With Us</a>
            <a href="<?= $base ?>contact/index.php" class="inline-flex items-center justify-center rounded-full border border-slate-700 px-5 py-3 text-slate-200 text-sm hover:bg-slate-800 transition">Contact Us</a>
          </div>
        </div>

        <div class="xl:col-span-2">
          <h4 class="text-white font-semibold uppercase tracking-[0.2em] text-xs mb-4">Explore</h4>
          <ul class="space-y-3 text-slate-400">
            <li><a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a></li>
            <li><a href="<?= $base ?>about/index.php" class="hover:text-white transition">About Us</a></li>
            <li><a href="<?= $base ?>our-work/index.php" class="hover:text-white transition">Our Work</a></li>
            <li><a href="<?= $base ?>projects/index.php" class="hover:text-white transition">Projects</a></li>
            <li><a href="<?= $base ?>news/index.php" class="hover:text-white transition">News</a></li>
          </ul>
        </div>

        <div class="xl:col-span-3">
          <h4 class="text-white font-semibold uppercase tracking-[0.2em] text-xs mb-4">Programs</h4>
          <ul class="space-y-3 text-slate-400">
            <li><a href="<?= $base ?>our-work/program.php?slug=wash" class="hover:text-white transition">WASH & Water</a></li>
            <li><a href="<?= $base ?>our-work/program.php?slug=health" class="hover:text-white transition">Healthcare</a></li>
            <li><a href="<?= $base ?>our-work/program.php?slug=education" class="hover:text-white transition">Education</a></li>
            <li><a href="<?= $base ?>our-work/program.php?slug=food-security" class="hover:text-white transition">Food Security</a></li>
            <li><a href="<?= $base ?>get-involved/index.php" class="hover:text-white transition">Get Involved</a></li>
          </ul>
        </div>

        <div class="xl:col-span-3">
          <h4 class="text-white font-semibold uppercase tracking-[0.2em] text-xs mb-4">Contact</h4>
          <ul class="space-y-4 text-slate-400 text-[13px] leading-relaxed">
            <li class="flex items-start gap-3">
              <i class="fa-solid fa-location-dot mt-1 text-brandRed"></i>
              <span><?= getSetting('address') ?></span>
            </li>
            <li class="flex items-center gap-3">
              <i class="fa-solid fa-phone text-brandRed"></i>
              <a href="tel:<?= str_replace(' ', '', getSetting('phone')) ?>" class="hover:text-white transition"><?= getSetting('phone') ?></a>
            </li>
            <li class="flex items-center gap-3">
              <i class="fa-solid fa-envelope text-brandRed"></i>
              <a href="mailto:<?= getSetting('email') ?>" class="hover:text-white transition"><?= getSetting('email') ?></a>
            </li>
          </ul>
        </div>

      </div>

      <div class="border-t border-slate-800/70 mt-10 pt-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between text-slate-500 text-xs">
        <p>&copy; <?= date('Y') ?> <?= getSetting('copyright') ?></p>
        <div class="flex flex-wrap items-center gap-3 justify-center md:justify-end text-slate-400">
          <a href="<?= $base ?>privacy.php" class="hover:text-white transition">Privacy Policy</a>
          <a href="<?= $base ?>terms.php" class="hover:text-white transition">Terms of Use</a>
          <a href="<?= $base ?>contact/index.php" class="hover:text-white transition">Support</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Main JavaScript File -->
  <script src="<?= $base ?>assets/js/main.js"></script>
  <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
