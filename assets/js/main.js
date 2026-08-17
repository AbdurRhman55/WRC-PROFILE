/**
 * Main JavaScript File for World Relief Committee (WRC) NGO Website
 * Includes Mobile Nested Submenu Accordion, Navbar Scroll Shadow, and Preset Selectors.
 */

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. Mobile Side Tray ---
  const menuBtn = document.getElementById('mobile-menu-btn');
  const tray = document.getElementById('mobile-tray');
  const trayOverlay = document.getElementById('mobile-tray-overlay');
  const trayClose = document.getElementById('mobile-tray-close');
  const accordionBtns = document.querySelectorAll('.mobile-tray-accordion-btn');
  const trayLinks = tray ? tray.querySelectorAll('.mobile-tray-link, .mobile-tray-accordion-btn') : [];

  const staggerLinks = () => {
    trayLinks.forEach((item, i) => {
      item.style.opacity = '0';
      item.style.transform = 'translateX(20px)';
      item.style.transitionDelay = `${0.12 + i * 0.035}s`;
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          item.style.opacity = '1';
          item.style.transform = 'translateX(0)';
        });
      });
    });
  };

  const resetLinks = () => {
    trayLinks.forEach(item => {
      item.style.opacity = '0';
      item.style.transform = 'translateX(20px)';
      item.style.transitionDelay = '0s';
    });
  };

  const openTray = () => {
    if (!tray) return;
    document.body.classList.add('tray-open');
    if (menuBtn) {
      menuBtn.classList.add('menu-open');
      menuBtn.setAttribute('aria-expanded', 'true');
    }
    tray.classList.add('active');
    if (trayOverlay) trayOverlay.classList.add('active');
    staggerLinks();
  };

  const closeTray = () => {
    if (!tray) return;
    document.body.classList.remove('tray-open');
    if (menuBtn) {
      menuBtn.classList.remove('menu-open');
      menuBtn.setAttribute('aria-expanded', 'false');
    }
    tray.classList.remove('active');
    if (trayOverlay) trayOverlay.classList.remove('active');
    // Close any open accordions
    accordionBtns.forEach(b => {
      b.classList.remove('open');
      if (b.nextElementSibling) b.nextElementSibling.classList.remove('open');
    });
    // Reset after tray finishes closing
    setTimeout(() => {
      resetLinks();
      if (menuBtn) menuBtn.focus();
    }, 500);
  };

  if (menuBtn) menuBtn.addEventListener('click', openTray);
  if (trayClose) trayClose.addEventListener('click', closeTray);
  if (trayOverlay) trayOverlay.addEventListener('click', closeTray);

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && tray && tray.classList.contains('active')) {
      closeTray();
    }
  });

  // Close on link click
  if (tray) {
    tray.querySelectorAll('a[href]').forEach(link => {
      link.addEventListener('click', closeTray);
    });
  }

  // Accordion toggle
  accordionBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const submenu = btn.nextElementSibling;
      const wasOpen = btn.classList.contains('open');

      // Close others
      accordionBtns.forEach(b => {
        if (b !== btn) {
          b.classList.remove('open');
          if (b.nextElementSibling) b.nextElementSibling.classList.remove('open');
        }
      });

      // Toggle current
      if (wasOpen) {
        btn.classList.remove('open');
        if (submenu) submenu.classList.remove('open');
      } else {
        btn.classList.add('open');
        if (submenu) submenu.classList.add('open');
      }
    });
  });

  // Close tray on resize to desktop
  const desktopMq = window.matchMedia('(min-width: 1024px)');
  if (desktopMq.addEventListener) {
    desktopMq.addEventListener('change', (e) => { if (e.matches) closeTray(); });
  }

  // Initialize: hide links
  resetLinks();

  // --- 2. Active Navigation Link Highlighting ---
  const currentPath = window.location.pathname.replace(/\\/g, '/');
  const currentFile = currentPath.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.nav-link-item');

  // Detect folder from URL (e.g. /about/who-we-are.html → "about")
  const pathSegments = currentPath.split('/').filter(Boolean);
  const currentFolder = pathSegments.length > 1 ? pathSegments[pathSegments.length - 2] : '';

  // Map folders to nav text labels
  const folderNavMap = {
    'about': 'ABOUT US'
  };

  let activeFound = false;

  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (!href) return;

    const cleanHref = href.replace(/^\.\.\//, '').replace(/^\.\//, '');

    // Skip if already active
    if (activeFound) return;

    // Folder-based match: highlight parent nav when inside its subfolder
    if (currentFolder && folderNavMap[currentFolder]) {
      const linkText = link.textContent.trim();
      if (linkText.startsWith(folderNavMap[currentFolder])) {
        link.classList.add('text-brandRed', 'font-bold');
        link.classList.remove('text-slate-200');
        activeFound = true;
        return;
      }
    }

    // Exact file match
    if (cleanHref === currentFile) {
      link.classList.add('text-brandRed', 'font-bold');
      link.classList.remove('text-slate-200');
      activeFound = true;
    }
  });

  // Mobile tray active link highlighting
  const trayLinksAll = document.querySelectorAll('.mobile-tray-link, .mobile-tray-submenu a');
  let trayActiveFound = false;
  trayLinksAll.forEach(link => {
    if (trayActiveFound) return;
    const href = link.getAttribute('href');
    if (!href) return;
    const cleanHref = href.replace(/^\.\.\//, '').replace(/^\.\//, '');

    // Folder-based match for about pages
    if (currentFolder && currentFolder === 'about' && href.includes('about/')) {
      link.classList.add('active-link');
      trayActiveFound = true;
      // Open the ABOUT US accordion if it exists
      const accBtn = link.closest('.mobile-tray-submenu')?.previousElementSibling;
      if (accBtn && accBtn.classList.contains('mobile-tray-accordion-btn')) {
        accBtn.classList.add('open');
        accBtn.nextElementSibling?.classList.add('open');
      }
      return;
    }

    if (cleanHref === currentFile) {
      link.classList.add('active-link');
      trayActiveFound = true;
      // Open parent accordion if inside submenu
      const accBtn = link.closest('.mobile-tray-submenu')?.previousElementSibling;
      if (accBtn && accBtn.classList.contains('mobile-tray-accordion-btn')) {
        accBtn.classList.add('open');
        accBtn.nextElementSibling?.classList.add('open');
      }
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

  // --- 6. Timeline Scroll Animations ---
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

  // --- 7. Gallery Lightbox ---
  const galleryItems = document.querySelectorAll('.gallery-item[data-src]');
  if (galleryItems.length > 0) {
    // Create lightbox overlay
    const lightbox = document.createElement('div');
    lightbox.id = 'gallery-lightbox';
    lightbox.className = 'fixed inset-0 z-[200] bg-black/90 backdrop-blur-sm hidden items-center justify-center p-4';
    lightbox.setAttribute('role', 'dialog');
    lightbox.setAttribute('aria-modal', 'true');
    lightbox.setAttribute('aria-label', 'Image viewer');
    lightbox.innerHTML = `
      <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all z-10" id="lightbox-close" aria-label="Close lightbox">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
      <button class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all" id="lightbox-prev" aria-label="Previous image">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <button class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all" id="lightbox-next" aria-label="Next image">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
      <div class="max-w-4xl w-full text-center">
        <img id="lightbox-img" src="" alt="" class="max-h-[80vh] mx-auto rounded-lg shadow-2xl object-contain" />
        <p id="lightbox-caption" class="text-white/80 text-sm mt-4 hidden"></p>
      </div>
    `;
    document.body.appendChild(lightbox);

    const lbImg = document.getElementById('lightbox-img');
    const lbCaption = document.getElementById('lightbox-caption');
    const lbClose = document.getElementById('lightbox-close');
    const lbPrev = document.getElementById('lightbox-prev');
    const lbNext = document.getElementById('lightbox-next');
    let currentIndex = 0;
    const items = Array.from(galleryItems);

    function showLightbox(index) {
      currentIndex = index;
      const item = items[index];
      const src = item.getAttribute('data-src');
      const caption = item.getAttribute('data-caption') || '';
      lbImg.src = src;
      lbImg.alt = caption;
      if (caption) {
        lbCaption.textContent = caption;
        lbCaption.classList.remove('hidden');
      } else {
        lbCaption.classList.add('hidden');
      }
      lightbox.classList.remove('hidden');
      lightbox.classList.add('flex');
      document.body.style.overflow = 'hidden';
      lbClose.focus();
    }

    function closeLightbox() {
      lightbox.classList.add('hidden');
      lightbox.classList.remove('flex');
      document.body.style.overflow = '';
    }

    items.forEach((item, i) => {
      item.addEventListener('click', () => showLightbox(i));
      item.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          showLightbox(i);
        }
      });
    });

    lbClose.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
    lbPrev.addEventListener('click', (e) => { e.stopPropagation(); showLightbox((currentIndex - 1 + items.length) % items.length); });
    lbNext.addEventListener('click', (e) => { e.stopPropagation(); showLightbox((currentIndex + 1) % items.length); });

    document.addEventListener('keydown', (e) => {
      if (lightbox.classList.contains('hidden')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') showLightbox((currentIndex - 1 + items.length) % items.length);
      if (e.key === 'ArrowRight') showLightbox((currentIndex + 1) % items.length);
    });
  }

  // --- WRC Program Detail Page Hydration ---
  const programPage = document.getElementById('program-detail-page');
  if (programPage && typeof WRC_PROGRAMS !== 'undefined') {
    const slug = programPage.getAttribute('data-slug');
    const program = WRC_PROGRAMS.find(p => p.slug === slug);
    
    if (program) {
      // Populate Breadcrumbs
      const breadcrumb = document.getElementById('program-breadcrumbs');
      if (breadcrumb) {
        breadcrumb.innerHTML = `
          <div class="breadcrumb-item"><a href="../index.html">Home</a></div>
          <div class="breadcrumb-separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></div>
          <div class="breadcrumb-item"><a href="index.html">Our Work</a></div>
          <div class="breadcrumb-separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></div>
          <div class="breadcrumb-item text-slate-900 font-medium">${program.title}</div>
        `;
      }
      
      // Populate Hero Section
      const heroTitle = document.getElementById('program-hero-title');
      const heroDesc = document.getElementById('program-hero-desc');
      const heroBg = document.getElementById('program-hero-bg');
      if (heroTitle) heroTitle.textContent = program.fullTitle || program.title;
      if (heroDesc) heroDesc.textContent = program.heroDescription;
      if (heroBg) {
        heroBg.style.backgroundImage = `url('${program.heroImage}')`;
      }
      
      // Populate Overview & Why It Matters
      const overviewText = document.getElementById('program-overview-text');
      const whyText = document.getElementById('program-why-text');
      const sideImage = document.getElementById('program-side-image');
      if (overviewText) overviewText.textContent = program.overview;
      if (whyText) whyText.textContent = program.whyItMatters;
      if (sideImage) {
        sideImage.src = program.heroImage;
        sideImage.alt = program.title;
      }
      
      // Populate Focus Areas
      const focusAreasList = document.getElementById('program-focus-areas');
      if (focusAreasList && program.focusAreas) {
        focusAreasList.innerHTML = program.focusAreas.map(area => `
          <span class="px-3 py-1.5 bg-slate-100 text-slate-800 text-xs font-semibold rounded-full border border-slate-200">${area}</span>
        `).join('');
      }
      
      // Populate Interventions (What We Do)
      const interventionsGrid = document.getElementById('program-interventions');
      if (interventionsGrid && program.interventions) {
        interventionsGrid.innerHTML = program.interventions.map(item => `
          <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-brandRed flex items-center justify-center text-xl mb-4">
              <i class="${item.icon}"></i>
            </div>
            <h4 class="font-bold text-slate-900 mb-2 text-sm">${item.title}</h4>
            <p class="text-xs text-slate-600 leading-relaxed">${item.description}</p>
          </div>
        `).join('');
      }
      
      // Populate Who We Serve (Target Groups)
      const targetGroupsList = document.getElementById('program-target-groups');
      if (targetGroupsList && program.targetGroups) {
        targetGroupsList.innerHTML = program.targetGroups.map(group => `
          <span class="px-4 py-2 bg-red-50 text-brandRed text-xs font-semibold rounded-full border border-red-100 flex items-center gap-1.5">
            <i class="fa-solid fa-circle text-[6px]"></i> ${group}
          </span>
        `).join('');
      }
      
      // Populate Projects list
      const projectsGrid = document.getElementById('program-projects');
      if (projectsGrid) {
        projectsGrid.innerHTML = `
          <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col justify-between group hover:shadow-xl transition-all duration-300 max-w-md mx-auto lg:mx-0">
            <div class="relative h-52 overflow-hidden">
              <img src="${program.heroImage}" alt="${program.title} Project" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <span class="absolute top-4 left-4 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-brandRed text-white rounded-full shadow-md">Active</span>
            </div>
            <div class="p-6 flex-grow flex flex-col justify-between">
              <div>
                <div class="flex items-center gap-2 text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-2">
                  <i class="${program.icon} text-brandRed"></i> ${program.title}
                </div>
                <h3 class="text-lg font-bold text-slate-900 font-heading mb-3 group-hover:text-brandRed transition-colors">Emergency ${program.title} Support Project</h3>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">Implementing key intervention services to support local community requirements and enhance long-term resilience.</p>
              </div>
              <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-500 uppercase"><i class="fa-solid fa-location-dot"></i> KP Province</span>
                <a href="../projects/project-name.html" class="text-brandRed hover:text-brandRed-hover text-xs font-semibold hover:underline flex items-center gap-1">
                  View Project &rarr;
                </a>
              </div>
            </div>
          </div>
        `;
      }
      
      // Populate Related Programs
      const relatedGrid = document.getElementById('program-related');
      if (relatedGrid && program.relatedPrograms) {
        const related = program.relatedPrograms.map(rSlug => {
          return WRC_PROGRAMS.find(p => p.slug === rSlug);
        }).filter(Boolean);
        
        relatedGrid.innerHTML = related.map(rel => `
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between group">
            <div>
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center text-base group-hover:bg-brandRed group-hover:text-white transition-colors">
                  <i class="${rel.icon}"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm font-heading">${rel.title}</h4>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed mb-6">${rel.description}</p>
            </div>
            <a href="${rel.slug}.html" class="text-brandRed hover:text-brandRed-hover text-xs font-semibold uppercase tracking-wider flex items-center gap-1.5">
              Explore Sector <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
          </div>
        `).join('');
      }

      // Populate CTA Section title
      const ctaBtn = document.getElementById('program-cta-btn');
      if (ctaBtn) {
        ctaBtn.innerHTML = `<i class="fa-solid fa-handshake"></i> Support ${program.title} Initiatives`;
      }
    }
  }
});
