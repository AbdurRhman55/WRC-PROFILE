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
    'about': 'ABOUT US',
    'our-work': 'OUR WORK',
    'projects': 'PROJECTS',
    'where-we-work': 'WHERE WE WORK',
    'impact': 'IMPACT',
    'resources': 'RESOURCES',
    'news': 'NEWS & MEDIA',
    'get-involved': 'GET INVOLVED',
    'contact': 'CONTACT'
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

    // Folder-based match for all pages
    if (currentFolder && folderNavMap[currentFolder] && href.includes(currentFolder + '/')) {
      link.classList.add('active-link');
      trayActiveFound = true;
      // Open the parent accordion if it exists
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

  // --- WRC Program Detail Page ---
  // Program detail is now fully server-side rendered via PHP
  // The old JS hydration from WRC_PROGRAMS has been removed to prevent overriding DB content

  // --- 8. Hero Slider Auto-Rotation ---
  const heroSlider = document.querySelector('.hero-slider');
  if (heroSlider) {
    const slides = heroSlider.querySelectorAll('.hero-slide');
    const dots = heroSlider.querySelectorAll('.hero-dot');
    const prevBtn = heroSlider.querySelector('.hero-prev');
    const nextBtn = heroSlider.querySelector('.hero-next');
    let currentSlide = 0;
    let autoSlideInterval;

    function goToSlide(index) {
      slides.forEach((s, i) => {
        s.style.transform = `translateX(${(i - index) * 100}%)`;
        s.style.opacity = i === index ? '1' : '0';
        s.style.zIndex = i === index ? '1' : '0';
      });
      dots.forEach((d, i) => {
        d.classList.toggle('bg-white', i === index);
        d.classList.toggle('bg-white/50', i !== index);
      });
      currentSlide = index;
    }

    function nextSlide() {
      goToSlide((currentSlide + 1) % slides.length);
    }

    function prevSlide() {
      goToSlide((currentSlide - 1 + slides.length) % slides.length);
    }

    function startAutoSlide() {
      autoSlideInterval = setInterval(nextSlide, 5000);
    }

    function stopAutoSlide() {
      clearInterval(autoSlideInterval);
    }

    if (slides.length > 1) {
      goToSlide(0);
      startAutoSlide();

      if (nextBtn) nextBtn.addEventListener('click', () => { stopAutoSlide(); nextSlide(); startAutoSlide(); });
      if (prevBtn) prevBtn.addEventListener('click', () => { stopAutoSlide(); prevSlide(); startAutoSlide(); });
      dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { stopAutoSlide(); goToSlide(i); startAutoSlide(); });
      });

      // Pause on hover
      heroSlider.addEventListener('mouseenter', stopAutoSlide);
      heroSlider.addEventListener('mouseleave', startAutoSlide);

      // Touch/swipe support
      let touchStartX = 0;
      heroSlider.addEventListener('touchstart', (e) => { touchStartX = e.touches[0].clientX; stopAutoSlide(); }, { passive: true });
      heroSlider.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { diff > 0 ? nextSlide() : prevSlide(); }
        startAutoSlide();
      }, { passive: true });
    }
  }

  // --- 9. News Page Filters ---
  const newsFilterBtns = document.querySelectorAll('[data-filter]');
  const newsCards = document.querySelectorAll('[data-category]');
  if (newsFilterBtns.length > 0 && newsCards.length > 0) {
    newsFilterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const filter = btn.getAttribute('data-filter');
        newsFilterBtns.forEach(b => {
          b.classList.remove('bg-brandRed', 'text-white');
          b.classList.add('bg-slate-100', 'text-slate-600');
        });
        btn.classList.add('bg-brandRed', 'text-white');
        btn.classList.remove('bg-slate-100', 'text-slate-600');

        newsCards.forEach(card => {
          if (filter === 'all' || card.getAttribute('data-category') === filter) {
            card.style.display = '';
            card.style.opacity = '0';
            requestAnimationFrame(() => { card.style.opacity = '1'; card.style.transition = 'opacity 0.3s'; });
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // --- 10. News Modal ---
  window.openModal = function(title, content, image, date, category) {
    const modal = document.getElementById('news-modal');
    if (!modal) return;
    modal.querySelector('.modal-title').textContent = title || '';
    modal.querySelector('.modal-content').textContent = content || '';
    modal.querySelector('.modal-date').textContent = date || '';
    modal.querySelector('.modal-category').textContent = category || '';
    const img = modal.querySelector('.modal-image');
    if (img && image) { img.src = image; img.style.display = ''; }
    else if (img) { img.style.display = 'none'; }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  };

  window.closeModal = function() {
    const modal = document.getElementById('news-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  };

  // --- 11. Share Functions ---
  window.sharePost = function(platform, url, title) {
    const shareUrls = {
      facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
      twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`,
      linkedin: `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`,
      whatsapp: `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`
    };
    if (shareUrls[platform]) {
      window.open(shareUrls[platform], '_blank', 'width=600,height=400');
    }
  };

  // --- 12. Project Page Filters ---
  const projectFilterBtns = document.querySelectorAll('.project-filter-btn');
  const projectCards = document.querySelectorAll('.project-card');
  if (projectFilterBtns.length > 0 && projectCards.length > 0) {
    projectFilterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const filter = btn.getAttribute('data-filter');
        projectFilterBtns.forEach(b => {
          b.classList.remove('bg-brandRed', 'text-white');
          b.classList.add('bg-slate-100', 'text-slate-600');
        });
        btn.classList.add('bg-brandRed', 'text-white');
        btn.classList.remove('bg-slate-100', 'text-slate-600');

        projectCards.forEach(card => {
          if (filter === 'all' || card.getAttribute('data-sector') === filter) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }

  // --- 13. Newsletter Subscription Form ---
  const newsletterForms = document.querySelectorAll('.newsletter-form');
  newsletterForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const email = this.querySelector('input[type="email"]')?.value;
      if (!email) return;
      const btn = this.querySelector('button');
      const originalText = btn?.textContent;
      if (btn) btn.textContent = 'Subscribing...';

      fetch('includes/newsletter-subscribe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
      }).then(r => r.json()).then(data => {
        if (btn) btn.textContent = data.success ? 'Subscribed!' : 'Try Again';
        if (data.success) this.reset();
        setTimeout(() => { if (btn) btn.textContent = originalText; }, 3000);
      }).catch(() => {
        if (btn) btn.textContent = originalText;
      });
    });
  });
});
