/**
 * Main JavaScript File for World Relief Committee (WRC) NGO Website
 * Includes Mobile Nested Submenu Accordion, Navbar Scroll Shadow, and Preset Selectors.
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. Mobile Main Menu & Nested Submenus ---
  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  const mobileCloseBtn = document.getElementById('mobile-menu-close');
  const mobileAccordionBtns = document.querySelectorAll('.mobile-accordion-btn');

  const openMobileMenu = () => {
    document.body.classList.add('mobile-menu-open');
    if (menuBtn) menuBtn.classList.add('menu-open');
    if (mobileMenu) mobileMenu.classList.add('menu-open');
    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');

    const focusable = mobileMenu ? mobileMenu.querySelectorAll('button, a[href]') : [];
    if (focusable.length) {
      focusable[0].focus();
    }
  };

  const closeMobileMenu = () => {
    document.body.classList.remove('mobile-menu-open');
    if (menuBtn) menuBtn.classList.remove('menu-open');
    if (mobileMenu) mobileMenu.classList.remove('menu-open');
    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    if (menuBtn) menuBtn.focus();
  };

  const toggleMobileMenu = () => {
    if (mobileMenu?.classList.contains('menu-open')) {
      closeMobileMenu();
    } else {
      openMobileMenu();
    }
  };

  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', toggleMobileMenu);
  }

  if (mobileCloseBtn) {
    mobileCloseBtn.addEventListener('click', closeMobileMenu);
  }

  if (mobileMenu) {
    mobileMenu.addEventListener('click', (event) => {
      const target = event.target;
      if (target === mobileMenu || target.classList.contains('mobile-menu-backdrop')) {
        closeMobileMenu();
      }
    });

    mobileMenu.querySelectorAll('a[href]').forEach(link => {
      link.addEventListener('click', () => {
        closeMobileMenu();
      });
    });
  }

  // Close the drawer automatically when resized to a desktop viewport
  const desktopMq = window.matchMedia('(min-width: 768px)');
  const handleDesktopResize = (e) => {
    if (e.matches) closeMobileMenu();
  };
  if (desktopMq.addEventListener) {
    desktopMq.addEventListener('change', handleDesktopResize);
  } else {
    desktopMq.addListener(handleDesktopResize);
  }

  mobileAccordionBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const submenu = btn.nextElementSibling;
      const icon = btn.querySelector('.accordion-icon');

      if (submenu) {
        submenu.classList.toggle('open');
      }
      if (icon) {
        icon.classList.toggle('rotate-180');
      }
    });
  });

  document.addEventListener('keydown', (event) => {
    if (!mobileMenu?.classList.contains('menu-open')) return;

    if (event.key === 'Escape') {
      event.preventDefault();
      closeMobileMenu();
      return;
    }

    if (event.key === 'Tab') {
      const focusable = Array.from(mobileMenu.querySelectorAll('button:not([disabled]), a[href]')).filter(el => el.offsetParent !== null);
      if (!focusable.length) return;

      const currentIndex = focusable.indexOf(document.activeElement);
      if (event.shiftKey && currentIndex === 0) {
        event.preventDefault();
        focusable[focusable.length - 1].focus();
      } else if (!event.shiftKey && currentIndex === focusable.length - 1) {
        event.preventDefault();
        focusable[0].focus();
      }
    }
  });

  // --- 2. Active Navigation Link Highlighting ---
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.nav-link-item');

  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href && (href === currentPath || (currentPath === '' && href === 'index.html'))) {
      link.classList.add('text-brandRed', 'font-bold');
      link.classList.remove('text-slate-200');
    }
  });

  // --- 3. Sticky Navbar Shadow on Scroll ---
  const header = document.querySelector('header');
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        header.classList.add('shadow-md');
        header.classList.remove('shadow-sm');
      } else {
        header.classList.remove('shadow-md');
        header.classList.add('shadow-sm');
      }
    });
  }

  // --- 4. Counter Animation for Impact Stats ---
  const counters = document.querySelectorAll('.counter-val');
  if (counters.length > 0) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const counter = entry.target;
          const target = parseInt(counter.getAttribute('data-target') || '0', 10);
          let count = 0;
          const speed = Math.max(1, Math.floor(target / 50));
          
          const updateCount = () => {
            count += speed;
            if (count < target) {
              counter.innerText = count.toLocaleString() + '+';
              setTimeout(updateCount, 30);
            } else {
              counter.innerText = target.toLocaleString() + '+';
            }
          };
          updateCount();
          obs.unobserve(counter);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(c => observer.observe(c));
  }

  // --- 5. FAQ Accordion Toggle ---
  const faqButtons = document.querySelectorAll('.faq-btn');
  faqButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const content = btn.nextElementSibling;
      const icon = btn.querySelector('.faq-icon');

      if (content) {
        content.classList.toggle('hidden');
      }
      if (icon) {
        icon.classList.toggle('rotate-180');
      }
    });
  });

  // --- 6. Quick Donation Preset Selector ---
  const donationPills = document.querySelectorAll('.donate-preset');
  const customAmountInput = document.getElementById('custom-amount');

  if (donationPills.length > 0 && customAmountInput) {
    donationPills.forEach(pill => {
      pill.addEventListener('click', () => {
        donationPills.forEach(p => {
          p.classList.remove('bg-brandRed', 'text-white');
          p.classList.add('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
        });
        pill.classList.remove('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
        pill.classList.add('bg-brandRed', 'text-white');

        const val = pill.getAttribute('data-amount');
        if (val) {
          customAmountInput.value = val;
        }
      });
    });
  }

  // --- 7. Timeline Scroll Animations ---
  const lifecycleTargets = document.querySelectorAll('.fade-up, .reveal');
  if (lifecycleTargets.length > 0 && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
    const lifecycleObserver = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.18 });
    lifecycleTargets.forEach(target => lifecycleObserver.observe(target));
  } else {
    lifecycleTargets.forEach(target => target.classList.add('is-visible'));
  }

  const spine = document.getElementById('spine-fill');
  const track = document.querySelector('.timeline-track');
  function updateSpine() {
    if (!spine || !track || window.innerWidth < 768) return;
    const rect = track.getBoundingClientRect();
    const vh = window.innerHeight;
    const total = rect.height;
    const progressed = Math.min(Math.max(vh * 0.6 - rect.top, 0), total);
    spine.style.transform = `scaleY(${progressed / total})`;
  }
  window.addEventListener('scroll', updateSpine, { passive: true });
  window.addEventListener('resize', updateSpine);
  updateSpine();
});
