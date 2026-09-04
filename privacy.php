<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$base = './';
$pageTitle = 'Privacy Policy | World Relief Committee (WRC)';
$metaDescription = 'Read the privacy policy of World Relief Committee (WRC) to understand how we collect, use, and protect your personal information.';

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative w-full bg-slate-900 text-white overflow-hidden" style="min-height:340px">
  <div class="absolute inset-0 opacity-30 bg-cover bg-center" style="background-image:url('<?= $base ?>assets/images/hero.jpg')"></div>
  <div class="absolute inset-0 bg-gradient-to-b from-slate-900/80 via-slate-900/70 to-slate-900"></div>
  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 flex flex-col items-center text-center">
    <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2">
      <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
      <i class="fa-solid fa-chevron-right text-[8px]"></i>
      <span class="text-white">Privacy Policy</span>
    </nav>
    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-4 py-1.5 text-xs font-medium text-white/90 mb-6">
      <i class="fa-solid fa-shield-halved text-brandRed"></i> Legal &amp; Policy
    </div>
    <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-semibold text-white leading-tight mb-4">Privacy Policy</h1>
    <p class="max-w-2xl text-slate-300 text-sm sm:text-base leading-relaxed">Your privacy matters to us. This policy explains how World Relief Committee collects, uses, and safeguards your personal information.</p>
    <p class="mt-3 text-slate-400 text-xs">Last updated: August 2026</p>
  </div>
</section>

<!-- TABLE OF CONTENTS -->
<section class="bg-white border-b border-slate-200">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">On This Page</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
      <a href="#information-collection" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Information Collection
      </a>
      <a href="#how-we-use-information" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> How We Use Information
      </a>
      <a href="#data-sharing" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Data Sharing
      </a>
      <a href="#data-security" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Data Security
      </a>
      <a href="#cookies" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Cookies
      </a>
      <a href="#third-party-links" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Third-Party Links
      </a>
      <a href="#childrens-privacy" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Children's Privacy
      </a>
      <a href="#changes-to-policy" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Changes to Policy
      </a>
      <a href="#contact-us" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brandRed transition-colors py-1.5">
        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i> Contact Us
      </a>
    </div>
  </div>
</section>

<!-- CONTENT -->
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">

  <!-- Information Collection -->
  <section id="information-collection">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-database text-brandRed text-xs"></i></span>
      Information Collection
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>World Relief Committee (WRC) collects personal information only when it is necessary to fulfil our humanitarian mandate, provide services, or comply with legal obligations. The types of information we may collect include:</p>
      <ul class="list-disc pl-6 space-y-2">
        <li><strong>Identity data</strong> &mdash; full name, date of birth, gender, and national identification details where required for programme registration.</li>
        <li><strong>Contact data</strong> &mdash; email address, telephone number, and mailing address submitted through forms, donations, or volunteer sign-ups.</li>
        <li><strong>Donation data</strong> &mdash; payment information processed through secure third-party gateways. WRC does not store credit or debit card numbers on its servers.</li>
        <li><strong>Technical data</strong> &mdash; IP address, browser type, operating system, and browsing behaviour collected automatically through cookies and server logs.</li>
        <li><strong>Programme data</strong> &mdash; beneficiary information gathered during assessments, monitoring visits, and project evaluations, handled in accordance with humanitarian data-protection standards.</li>
      </ul>
      <p>We collect data through online forms, email correspondence, telephone interactions, and in-person encounters at programme sites. In every case, we endeavour to collect only the minimum information necessary for the stated purpose.</p>
    </div>
  </section>

  <!-- How We Use Information -->
  <section id="how-we-use-information">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-gears text-brandRed text-xs"></i></span>
      How We Use Information
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>The personal information we collect is used for the following purposes:</p>
      <ul class="list-disc pl-6 space-y-2">
        <li>Delivering and improving humanitarian programmes, including health, education, WASH, food security, and emergency response initiatives.</li>
        <li>Processing donations and issuing tax-deductible receipts.</li>
        <li>Communicating programme updates, newsletters, and impact reports to supporters and stakeholders.</li>
        <li>Responding to enquiries, feedback, and volunteer or employment applications.</li>
        <li>Conducting internal monitoring, evaluation, and research to measure programme effectiveness.</li>
        <li>Meeting regulatory, legal, and reporting obligations required by government authorities and donor agencies.</li>
        <li>Ensuring the safety and security of beneficiaries, staff, and partner organisations.</li>
      </ul>
      <p>We do not sell, rent, or trade personal information to third parties for their marketing purposes.</p>
    </div>
  </section>

  <!-- Data Sharing -->
  <section id="data-sharing">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-share-nodes text-brandRed text-xs"></i></span>
      Data Sharing
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>WRC may share personal information in the following limited circumstances:</p>
      <ul class="list-disc pl-6 space-y-2">
        <li><strong>With service providers</strong> &mdash; trusted third-party vendors who assist with payment processing, IT infrastructure, email delivery, and analytics, bound by contractual obligations to protect your data.</li>
        <li><strong>With donor agencies</strong> &mdash; aggregated or anonymised programme data shared with institutional donors for reporting and accountability, in compliance with grant agreements.</li>
        <li><strong>With government authorities</strong> &mdash; when required by law, regulation, or lawful request from competent authorities.</li>
        <li><strong>For safety reasons</strong> &mdash; when disclosure is necessary to protect the rights, property, or safety of WRC, our beneficiaries, or the public.</li>
      </ul>
      <p>All third-party processors are required to handle personal data in accordance with applicable data-protection laws and WRC's internal data-protection policies.</p>
    </div>
  </section>

  <!-- Data Security -->
  <section id="data-security">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-lock text-brandRed text-xs"></i></span>
      Data Security
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>We implement robust technical and organisational measures to protect personal data against unauthorised access, alteration, disclosure, or destruction. These measures include:</p>
      <ul class="list-disc pl-6 space-y-2">
        <li>SSL/TLS encryption for all data transmitted through our website.</li>
        <li>Role-based access controls restricting who can view or modify personal information.</li>
        <li>Regular security audits, vulnerability assessments, and penetration testing.</li>
        <li>Secure backup and disaster-recovery procedures.</li>
        <li>Staff training on data protection, confidentiality, and incident-response protocols.</li>
      </ul>
      <p>While we take every reasonable precaution, no method of electronic transmission or storage is completely secure. If you become aware of a security vulnerability, please report it immediately through our <a href="<?= $base ?>contact/index.php#whistleblower" class="text-brandRed font-medium hover:underline">Report a Concern</a> channel.</p>
    </div>
  </section>

  <!-- Cookies -->
  <section id="cookies">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-cookie-bite text-brandRed text-xs"></i></span>
      Cookies
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>Our website uses cookies and similar tracking technologies to enhance your browsing experience and gather analytical data. Cookies are small text files stored on your device by your web browser.</p>
      <h3 class="text-base font-semibold text-slate-800 mt-6 mb-2">Types of Cookies We Use</h3>
      <ul class="list-disc pl-6 space-y-2">
        <li><strong>Essential cookies</strong> &mdash; required for core website functionality such as navigation, session management, and security features.</li>
        <li><strong>Analytics cookies</strong> &mdash; help us understand how visitors interact with our website, enabling us to improve content and performance.</li>
        <li><strong>Preference cookies</strong> &mdash; remember your settings and choices to provide a personalised experience on future visits.</li>
      </ul>
      <p>You can control or disable cookies through your browser settings. Please note that disabling essential cookies may affect website functionality.</p>
    </div>
  </section>

  <!-- Third-Party Links -->
  <section id="third-party-links">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-up-right-from-square text-brandRed text-xs"></i></span>
      Third-Party Links
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>Our website may contain links to external websites operated by partner organisations, government agencies, and other third parties. WRC is not responsible for the privacy practices or content of these external sites. We encourage you to review the privacy policies of any third-party website you visit through links on our site.</p>
      <p>The presence of a link does not imply endorsement by WRC of the linked website's operators or their practices.</p>
    </div>
  </section>

  <!-- Children's Privacy -->
  <section id="childrens-privacy">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-child text-brandRed text-xs"></i></span>
      Children's Privacy
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>WRC is committed to protecting the privacy of children. We do not knowingly collect personal information directly from children under the age of 13 without the explicit consent of a parent or legal guardian. In the case of child-protection and education programmes, data is collected through authorised caregivers, social workers, or institutional representatives in accordance with safeguarding policies.</p>
      <p>If we become aware that personal information has been collected from a child without appropriate consent, we will take prompt steps to delete that information. If you believe a child has provided us with personal data, please <a href="<?= $base ?>contact/index.php" class="text-brandRed font-medium hover:underline">contact us</a> immediately.</p>
    </div>
  </section>

  <!-- Changes to Policy -->
  <section id="changes-to-policy">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-clock-rotate-left text-brandRed text-xs"></i></span>
      Changes to This Policy
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>We may update this Privacy Policy from time to time to reflect changes in our practices, technologies, legal requirements, or other factors. When we make material changes, we will update the "Last updated" date at the top of this page and, where appropriate, notify registered users by email or through a prominent notice on our website.</p>
      <p>We encourage you to review this policy periodically to stay informed about how we protect your information.</p>
    </div>
  </section>

  <!-- Contact Us -->
  <section id="contact-us">
    <h2 class="font-display text-2xl font-semibold text-slate-900 mb-4 flex items-center gap-3">
      <span class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid fa-envelope text-brandRed text-xs"></i></span>
      Contact Us
    </h2>
    <div class="prose-custom space-y-4 text-slate-600 text-sm leading-relaxed">
      <p>If you have any questions, concerns, or requests regarding this Privacy Policy or how we handle your personal data, please reach out to us:</p>
      <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 mt-4">
        <ul class="space-y-3 text-sm">
          <li class="flex items-start gap-3">
            <i class="fa-solid fa-building mt-1 text-brandRed"></i>
            <span><strong>World Relief Committee</strong><br>Flat#1, 3rd Floor, Shami Flats near Marbella Wedding Hall, Warsak Road, Peshawar, Pakistan</span>
          </li>
          <li class="flex items-center gap-3">
            <i class="fa-solid fa-envelope text-brandRed"></i>
            <a href="mailto:info@wrc.org.pk" class="hover:text-brandRed transition">info@wrc.org.pk</a>
          </li>
          <li class="flex items-center gap-3">
            <i class="fa-solid fa-phone text-brandRed"></i>
            <a href="tel:+923001234567" class="hover:text-brandRed transition">+92 300 1234567</a>
          </li>
        </ul>
      </div>
      <p>We will respond to all data-related requests within 30 business days.</p>
    </div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
