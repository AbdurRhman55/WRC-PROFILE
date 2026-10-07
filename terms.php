<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Terms of Use | World Relief Committee (WRC)';
$metaDescription = 'Read WRC\'s Terms of Use for website usage and institutional policies.';

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative bg-slate-900 py-16 overflow-hidden">
  <div class="absolute inset-0 opacity-25 bg-cover bg-center" style="background-image: url('<?= $base ?>assets/images/hero.jpg');"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 to-slate-900/70"></div>
  <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
    <nav class="text-xs text-slate-400 mb-4 flex items-center gap-2 justify-center">
      <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
      <i class="fa-solid fa-chevron-right text-[8px]"></i>
      <span class="text-white">Terms of Use</span>
    </nav>
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 text-white/90 border border-white/20 mb-4"><i class="fa-solid fa-file-contract"></i> Legal</span>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3 font-heading">Terms of Use</h1>
    <p class="text-slate-300 text-sm">Effective Date: January 1, 2026 &bull; Last Updated: January 1, 2026</p>
  </div>
</section>

<!-- TERMS CONTENT -->
<section class="py-16 bg-white">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-12 gap-12">

      <!-- Table of Contents -->
      <aside class="lg:col-span-4 hidden lg:block">
        <div class="sticky top-28 space-y-3">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">On this page</p>
          <a href="#acceptance" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">1. Acceptance of Terms</a>
          <a href="#eligibility" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">2. Eligibility</a>
          <a href="#ip" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">3. Intellectual Property</a>
          <a href="#acceptable-use" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">4. Acceptable Use</a>
          <a href="#disclaimer" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">5. Disclaimer of Warranties</a>
          <a href="#liability" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">6. Limitation of Liability</a>
          <a href="#privacy" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">7. Privacy Policy</a>
          <a href="#donations" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">8. Donations &amp; Payments</a>
          <a href="#third-party" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">9. Third-Party Links</a>
          <a href="#governing-law" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">10. Governing Law</a>
          <a href="#modifications" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">11. Modifications</a>
          <a href="#contact-terms" class="block text-sm text-slate-600 hover:text-brandRed hover:font-semibold transition-all py-1 border-l-2 border-slate-200 hover:border-brandRed pl-3">12. Contact Us</a>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="lg:col-span-8 prose prose-slate max-w-none">
        <p class="text-slate-600 text-sm leading-relaxed mb-8">Welcome to the World Relief Committee (WRC) website. By accessing or using this website, you agree to comply with and be bound by these Terms of Use. If you do not agree with any part of these terms, please do not use this website.</p>

        <div id="acceptance" class="mb-10">
          <h2 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2"><span class="text-brandRed">1.</span> Acceptance of Terms</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-3">These Terms of Use constitute a legally binding agreement between you and the World Relief Committee (WRC), a registered non-governmental organization operating in Pakistan. By browsing, accessing, or using any part of this website, you acknowledge that you have read, understood, and agree to be bound by these terms.</p>
          <p class="text-slate-600 text-sm leading-relaxed">WRC reserves the right to amend these terms at any time without prior notice. Continued use of the website following any changes constitutes acceptance of the revised terms.</p>
        </div>

        <div id="eligibility" class="mb-10">
          <h2 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2"><span class="text-brandRed">2.</span> Eligibility</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-3">This website is intended for use by individuals who are at least 18 years of age or accessing the site under the supervision of a parent or legal guardian. By using this website, you represent and warrant that you meet these eligibility requirements.</p>
          <p class="text-slate-600 text-sm leading-relaxed">WRC does not knowingly collect personal information from children under 13 years of age without verifiable parental consent.</p>
        </div>

        <div id="ip" class="mb-10">
          <h2 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2"><span class="text-brandRed">3.</span> Intellectual Property</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-3">All content on this website, including but not limited to text, graphics, logos, images, audio clips, video clips, data compilations, software, and the overall design and layout, is the property of WRC or its content suppliers and is protected by applicable intellectual property laws.</p>
          <ul class="list-disc pl-5 space-y-2 text-slate-600 text-sm leading-relaxed mb-3">
            <li>You may view and download a single copy of the material for your personal, non-commercial use.</li>
            <li>You may not reproduce, distribute, modify, create derivative works of, publicly display, or exploit any content without prior written consent from WRC.</li>
            <li>WRC, the WRC logo, and all related marks are trademarks of the World Relief Committee and may not be used without written permission.</li>
          </ul>
        </div>

        <div id="acceptable-use" class="mb-10">
          <h2 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2"><span class="text-brandRed">4.</span> Acceptable Use</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-3">You agree to use this website only for lawful purposes and in a manner that does not infringe the rights of others or restrict or inhibit their use of the website. Prohibited conduct includes but is not limited to:</p>
          <ul class="list-disc pl-5 space-y-2 text-slate-600 text-sm leading-relaxed">
            <li>Using the website for any fraudulent or illegal purpose.</li>
            <li>Uploading or transmitting viruses, malware, or other harmful code.</li>
            <li>Attempting to gain unauthorized access to the website or its related systems.</li>
            <li>Using automated systems (bots, scrapers) to access or collect data from the website without prior written consent.</li>
            <li>Interfering with the proper functioning of the website.</li>
          </ul>
        </div>

        <div id="disclaimer" class="mb-10">
          <h2 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2"><span class="text-brandRed">5.</span> Disclaimer of Warranties</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-3">This website and all content, materials, information, services, and products available through it are provided on an "as is" and "as available" basis. WRC makes no representations or warranties of any kind, express or implied, including but not limited to:</p>
          <ul class="list-disc pl-5 space-y-2 text-slate-600 text-sm leading-relaxed">
            <li>Uninterrupted, timely, secure, or error-free operation of the website.</li>
            <li>Accuracy, reliability, or completeness of any content on the website.</li>
            <li>Fitness of the website for any particular purpose.</li>
            <li>Absence of viruses or other harmful components.</li>
          </ul>
        </div>

        <div id="liability" class="mb-10">
          <h2 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2"><span class="text-brandRed">6.</span> Limitation of Liability</h2>
          <p class="text-slate-600 text-sm leading-relaxed mb-3">To the fullest extent permitted by applicable law, WRC shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or in connection with your use of this website, including but not limited to loss of data, revenue, or profits.</p>
          <p class="text-slate-600 text-sm leading-relaxed">In no event shall WRC's total aggregate liability exceed the amount you have paid to WRC, if any, for accessing this website during the twelve ( 12 ) months immediately preceding the claim.</p>
</div>

<div id = 'privacy' class = 'mb-10'>
<h2 class = 'text-xl font-bold text-slate-900 mb-3 flex items-center gap-2'><span class = 'text-brandRed'>7.</span> Privacy Policy</h2>
<p class = 'text-slate-600 text-sm leading-relaxed mb-3'>Your use of this website is also governed by our <a href = "<?= $base ?>privacy.php" class = 'text-brandRed font-semibold hover:underline'>Privacy Policy</a>, which is incorporated into these Terms of Use by reference. Please review our Privacy Policy to understand our practices regarding the collection, use, and disclosure of your personal information.</p>
</div>

<div id = 'donations' class = 'mb-10'>
<h2 class = 'text-xl font-bold text-slate-900 mb-3 flex items-center gap-2'><span class = 'text-brandRed'>8.</span> Donations &amp;
Payments</h2>
<p class = 'text-slate-600 text-sm leading-relaxed mb-3'>If you make a donation through this website, you agree to provide accurate and complete information. WRC uses third-party payment processors and does not store your credit card or banking information on our servers.</p>
<ul class = 'list-disc pl-5 space-y-2 text-slate-600 text-sm leading-relaxed'>
<li>All donations are final and non-refundable unless otherwise required by applicable law.</li>
<li>Tax receipts will be issued for eligible donations in accordance with Pakistani tax regulations.</li>
<li>WRC reserves the right to refuse or return any donation that does not comply with applicable laws or organizational policies.</li>
</ul>
</div>

<div id = 'third-party' class = 'mb-10'>
<h2 class = 'text-xl font-bold text-slate-900 mb-3 flex items-center gap-2'><span class = 'text-brandRed'>9.</span> Third-Party Links</h2>
<p class = 'text-slate-600 text-sm leading-relaxed mb-3'>This website may contain links to third-party websites or services that are not owned or controlled by WRC. WRC has no control over and assumes no responsibility for the content, privacy policies, or practices of any third-party sites or services.</p>
<p class = 'text-slate-600 text-sm leading-relaxed'>We strongly advise you to review the terms and privacy policies of any third-party websites you visit. WRC shall not be responsible or liable for any damage or loss caused by your use of any such third-party content, goods, or services.</p>
</div>

<div id = 'governing-law' class = 'mb-10'>
<h2 class = 'text-xl font-bold text-slate-900 mb-3 flex items-center gap-2'><span class = 'text-brandRed'>10.</span> Governing Law</h2>
<p class = 'text-slate-600 text-sm leading-relaxed mb-3'>These Terms of Use shall be governed by and construed in accordance with the laws of the Islamic Republic of Pakistan, without regard to its conflict of law principles. Any disputes arising out of or in connection with these terms shall be subject to the exclusive jurisdiction of the courts of Peshawar, Khyber Pakhtunkhwa, Pakistan.</p>
</div>

<div id = 'modifications' class = 'mb-10'>
<h2 class = 'text-xl font-bold text-slate-900 mb-3 flex items-center gap-2'><span class = 'text-brandRed'>11.</span> Modifications</h2>
<p class = 'text-slate-600 text-sm leading-relaxed mb-3'>WRC reserves the right to modify, suspend, or discontinue any part of this website or these Terms of Use at any time without prior notice. Changes will be effective immediately upon posting on this page with an updated 'Last Updated' date.</p>
<p class = 'text-slate-600 text-sm leading-relaxed'>It is your responsibility to review these terms periodically. Your continued use of the website after any modifications constitutes your acceptance of the updated terms.</p>
</div>

<div id = 'contact-terms' class = 'mb-10 bg-slate-50 rounded-2xl p-6 border border-slate-200'>
<h2 class = 'text-xl font-bold text-slate-900 mb-3 flex items-center gap-2'><span class = 'text-brandRed'>12.</span> Contact Us</h2>
<p class = 'text-slate-600 text-sm leading-relaxed mb-4'>If you have any questions about these Terms of Use, please contact us:</p>
<div class = 'space-y-3 text-sm'>
<div class = 'flex items-center gap-3'><div class = 'w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center shrink-0'><i class = 'fa-solid fa-building text-brandRed text-xs'></i></div><div><span class = 'text-slate-400'>Organization:</span> <span class = 'font-semibold text-slate-900'>World Relief Committee ( WRC )</span></div></div>
<div class = 'flex items-center gap-3'><div class = 'w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center shrink-0'><i class = 'fa-solid fa-location-dot text-brandRed text-xs'></i></div><div><span class = 'text-slate-400'>Address:</span> <span class = 'font-semibold text-slate-900'>Flat#1, 3rd Floor, Shami Flats near Marbella Wedding Hall, Warsak Road, Peshawar, Pakistan</span></div></div>
<div class = 'flex items-center gap-3'><div class = 'w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center shrink-0'><i class = 'fa-solid fa-envelope text-brandRed text-xs'></i></div><div><span class = 'text-slate-400'>Email:</span> <a href = 'mailto:info@wrc.org.pk' class = 'font-semibold text-brandRed hover:underline'>info@wrc.org.pk</a></div></div>
<div class = 'flex items-center gap-3'><div class = 'w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center shrink-0'><i class = 'fa-solid fa-phone text-brandRed text-xs'></i></div><div><span class = 'text-slate-400'>Phone:</span> <a href = 'tel:+923001234567' class = 'font-semibold text-brandRed hover:underline'>+92 300 1234567</a></div></div>
</div>
</div>

</div>
</div>
</div>
</section>

<?php include __DIR__ . '/includes/footer.php';
?>
