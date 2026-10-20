<?php
/**
* WRC Website - Header Include
* Outputs: <!DOCTYPE html> through </header> + mobile tray
*
* Usage: Set $base before including ( e.g., $base = '../' for sub-pages )
* The $pageTitle variable sets the <title> tag
*/

if ( !isset( $base ) ) $base = function_exists( 'siteBaseUrl' ) ? siteBaseUrl() : '/';
if ( !isset( $pageTitle ) ) $pageTitle = 'World Relief Committee (WRC) - Official NGO Portal';
if ( !isset( $metaDescription ) ) $metaDescription = 'World Relief Committee (WRC) is a non-profit humanitarian organization delivering health, education, WASH, food security, and emergency disaster relief globally.';

// Detect current section for navbar active state
$currentFile = basename( $_SERVER[ 'PHP_SELF' ] );
$currentDir = basename( dirname( $_SERVER[ 'PHP_SELF' ] ) );
$navActive = 'home';
if ( $currentDir === 'about' || $currentFile === 'about' ) $navActive = 'about';
elseif ( $currentDir === 'our-work' || $currentDir === 'projects' ) $navActive = 'our-work';
elseif ( $currentDir === 'where-we-work' ) $navActive = 'where-we-work';
elseif ( $currentDir === 'impact' ) $navActive = 'impact';
elseif ( $currentDir === 'resources' ) $navActive = 'resources';
elseif ( $currentDir === 'news' ) $navActive = 'news';
elseif ( $currentDir === 'get-involved' ) $navActive = 'get-involved';
elseif ( $currentDir === 'contact' ) $navActive = 'contact';
elseif ( $currentFile === 'index.php' && $currentDir === '' ) $navActive = 'home';
?>
<!DOCTYPE html>
<html lang = 'en'>
<head>
<meta charset = 'UTF-8'>
<meta name = 'viewport' content = 'width=device-width, initial-scale=1.0'>
<title><?= $pageTitle ?></title>
<meta name = 'description' content = "<?= $metaDescription ?>">

<!-- Tailwind CSS CDN -->
<script src = 'https://cdn.tailwindcss.com'></script>

<!-- Tailwind Configuration -->
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brandRed: {
                    DEFAULT: '#ca0000',
                    hover: '#a30000',
                    light: '#fef2f2',
                }
                ,
                brand: {
                    50: '#f8fafc',
                    900: '#0f172a',
                    950: '#020617'
                }
                ,
                ink: {
                    900: '#0E1F27',
                    800: '#152C36',
                    700: '#1D3A47'
                }
                ,
                sand: {
                    50: '#FAF8F3',
                    100: '#F3EFE4'
                }
                ,
                gold: {
                    400: '#C89B3C',
                    500: '#B0862F',
                    600: '#8F6D26'
                }
                ,
                teal: {
                    500: '#2F6F62',
                    600: '#255A50'
                }
            }
            ,
            fontFamily: {
                display: [ 'Fraunces', 'serif' ],
                sans: [ 'Inter', 'sans-serif' ],
            }
            ,
            keyframes: {
                'pulse-glow': {
                    '0%, 100%': {
                        boxShadow: '0 0 8px rgba(202, 0, 0, 0.25)' }
                        ,
                        '50%': {
                            boxShadow: '0 0 15px rgba(202, 0, 0, 0.45)' }
                            ,
                        }
                        ,
                        'progress': {
                            from: {
                                width: '0%' }
                                ,
                            }
                            ,
                        }
                        ,
                        animation: {
                            'pulse-glow': 'pulse-glow 3s infinite',
                            'progress': 'progress 1.5s ease-out forwards',
                        }
                        ,
                    }
                }
            }
            </script>

            <!-- Font Awesome Icons -->
            <link rel = 'stylesheet' href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>

            <!-- Poppins + Partner section fonts -->
            <link rel = 'preconnect' href = 'https://fonts.googleapis.com'>
            <link rel = 'preconnect' href = 'https://fonts.gstatic.com' crossorigin>
            <link href = 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap' rel = 'stylesheet'>
            <link rel = 'stylesheet' href = "<?= $base ?>assets/css/custom.css">
            <?php if ( isset( $extraHead ) ) echo $extraHead;
            ?>

            </head>
            <body class = 'bg-slate-50 text-slate-800 flex flex-col min-h-screen'>

            <!-- TOP UTILITY BAR -->
            <div class = 'bg-brandRed text-white text-xs py-2 border-b border-brandRed/80 hidden lg:block w-full'>
            <div class = 'w-full px-4 sm:px-6 lg:px-8 flex justify-between items-center'>
            <div class = 'flex items-center gap-6'>
            <span class = 'flex items-center gap-2'><i class = 'fa-solid fa-phone text-white text-[11px]'></i> <?= getSetting( 'phone' ) ?></span>
            <span class = 'flex items-center gap-2'><i class = 'fa-solid fa-envelope text-white text-[11px]'></i> <?= getSetting( 'email' ) ?></span>
            <span class = 'text-white/80'>|</span>
            <span class = 'text-white/90 font-medium'><i class = 'fa-solid fa-bullhorn text-white mr-1 text-[11px]'></i> <?= getSetting( 'emergency_banner' ) ?></span>
            </div>
            <div class = 'flex items-center gap-4 text-xs text-white'>
            <span class = 'flex items-center gap-2'><i class = 'fa-solid fa-location-dot text-white text-[11px]'></i><?= getSetting( 'address' ) ?></span>
            </div>
            </div>
            </div>

            <!-- MAIN HEADER NAVIGATION -->
            <header class = 'sticky top-0 z-50 w-full bg-white/95 lg:bg-white/75 lg:backdrop-blur-xl border-b border-white/50 transition-all duration-300 shadow-sm'>
            <div class = 'w-full px-4 sm:px-6 lg:px-8'>
            <div class = 'flex items-center justify-between h-20 gap-4'>

            <!-- BRAND LOGO -->
            <a href = "<?= $base ?>index.php" class = 'flex items-center gap-3 shrink-0 group'>
            <img src = "<?= $base ?>assets/images/logo.png" alt = 'WRC logo' class = 'w-32 h-18 transition-all group-hover:border-brandRed' />
            </a>

            <!-- DESKTOP NAVBAR LINKS -->
            <nav class = 'hidden lg:flex items-center gap-1 xl:gap-1.5 text-[11px] xl:text-xs font-medium text-slate-600'>

            <!-- 1. HOME -->
            <a href = "<?= $base ?>index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'home' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all">
            HOME
            </a>

            <!-- 2. ABOUT US -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>about/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'about' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            ABOUT US <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full left-0 w-64 bg-white border border-gray-100 rounded-2xl shadow-xl p-3 text-slate-600 text-xs'>
            <div class = 'mb-1 px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider'>About WRC</div>
            <a href = "<?= $base ?>about/who-we-are.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-circle-info w-4 text-center mr-1 text-slate-400'></i> Who We Are</a>
            <a href = "<?= $base ?>about/vision-mission-values.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-bullseye w-4 text-center mr-1 text-slate-400'></i> Vision, Mission & Values</a>
            <a href = "<?= $base ?>about/history.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-timeline w-4 text-center mr-1 text-slate-400'></i> Our History</a>

            <div class = 'mt-2 mb-1 px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider'>People & Governance</div>
            <a href = "<?= $base ?>about/leadership.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-user-tie w-4 text-center mr-1 text-slate-400'></i> Leadership</a>
            <a href = "<?= $base ?>about/organizational-structure.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-sitemap w-4 text-center mr-1 text-slate-400'></i> Organizational Structure</a>
            <a href = "<?= $base ?>about/team.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-users w-4 text-center mr-1 text-slate-400'></i> Our Team</a>

            <div class = 'mt-2 mb-1 px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider'>Transparency</div>
            <a href = "<?= $base ?>about/policies-compliance.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-shield-halved w-4 text-center mr-1 text-slate-400'></i> Policies & Compliance</a>
            <a href = "<?= $base ?>about/annual-reports.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-file-invoice w-4 text-center mr-1 text-slate-400'></i> Annual Reports</a>

            <div class = 'mt-2 pt-2 border-t border-slate-100'>
            <a href = "<?= $base ?>contact/index.php" class = 'block px-3 py-1.5 rounded-xl hover:bg-slate-800 hover:text-white transition-colors font-medium'><i class = 'fa-solid fa-address-book w-4 text-center mr-1 text-slate-400'></i> Contact Us</a>
            </div>
            </div>
            </div>

            <!-- 3. OUR WORK ( MEGA MENU ) -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>our-work/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'our-work' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            OUR WORK <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full left-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>projects/index.php" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-folder-open mr-2 text-slate-400'></i> All Projects</a>
            <a href = "<?= $base ?>projects/index.php#active" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-spinner mr-2 text-slate-400'></i> Active Projects</a>
            <a href = "<?= $base ?>projects/index.php#completed" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-circle-check mr-2 text-slate-400'></i> Completed Projects</a>
            <a href = "<?= $base ?>our-work/index.php" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-info-circle mr-2 text-slate-400'></i> About Our Work</a>
            <a href = "<?= $base ?>projects/index.php#dashboard" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-chart-line mr-2 text-slate-400'></i> Project Dashboard</a>
            </div>
            </div>

            <!-- 4. WHERE WE WORK -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>where-we-work/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'where-we-work' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            WHERE WE WORK <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full left-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>where-we-work/index.php#map" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-map-location-dot mr-2 text-slate-400'></i> Pakistan Map</a>
            <a href = "<?= $base ?>where-we-work/index.php#kp" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-mountain mr-2 text-slate-400'></i> Khyber Pakhtunkhwa</a>
            <a href = "<?= $base ?>where-we-work/index.php#punjab" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-wheat-awn mr-2 text-slate-400'></i> Punjab</a>
            <a href = "<?= $base ?>where-we-work/index.php#sindh" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-water mr-2 text-slate-400'></i> Sindh</a>
            <a href = "<?= $base ?>where-we-work/index.php#balochistan" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-sun mr-2 text-slate-400'></i> Balochistan</a>
            <a href = "<?= $base ?>where-we-work/index.php#districts" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-layer-group mr-2 text-slate-400'></i> District Coverage</a>
            </div>
            </div>

            <!-- 6. IMPACT -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>impact/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'impact' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            IMPACT <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full left-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>impact/index.php#dashboard" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-chart-pie mr-2 text-slate-400'></i> Impact Dashboard</a>
            <a href = "<?= $base ?>impact/index.php#stats" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-users-viewfinder mr-2 text-slate-400'></i> Beneficiary Statistics</a>
            <a href = "<?= $base ?>impact/index.php#mne" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-clipboard-check mr-2 text-slate-400'></i> Monitoring & Evaluation</a>
            <a href = "<?= $base ?>impact/index.php#case-studies" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-book-open-reader mr-2 text-slate-400'></i> Case Studies</a>
            <a href = "<?= $base ?>impact/index.php#sdgs" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-globe mr-2 text-slate-400'></i> SDG Contributions</a>
            </div>
            </div>

            <!-- 7. RESOURCES -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>resources/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'resources' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            RESOURCES <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full right-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>resources/index.php#annual-reports" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-file-pdf mr-2 text-slate-400'></i> Annual Reports</a>
            <a href = "<?= $base ?>resources/index.php#financial-reports" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-coins mr-2 text-slate-400'></i> Financial Reports</a>
            <a href = "<?= $base ?>resources/index.php#registration" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-stamp mr-2 text-slate-400'></i> Registration</a>
            </div>
            </div>

            <!-- 8. NEWS & MEDIA -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>news/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'news' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            NEWS & MEDIA <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full right-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>news/index.php#news" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-newspaper mr-2 text-slate-400'></i> Latest News</a>
            <a href = "<?= $base ?>news/index.php#photos" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-images mr-2 text-slate-400'></i> Photo Gallery</a>
            <a href = "<?= $base ?>news/index.php#videos" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-video mr-2 text-slate-400'></i> Video Gallery</a>
            </div>
            </div>

            <!-- 9. GET INVOLVED -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>get-involved/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'get-involved' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            GET INVOLVED <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full right-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>get-involved/contribute-with-us.php" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-handshake mr-2 text-slate-400'></i> Let’s Work Together</a>
            <a href = "<?= $base ?>get-involved/index.php#volunteer" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-hands-holding-child mr-2 text-slate-400'></i> Volunteer</a>
            <a href = "<?= $base ?>get-involved/index.php#careers" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-user-plus mr-2 text-slate-400'></i> Careers</a>
            <a href = "<?= $base ?>get-involved/index.php#partner" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-handshake-angle mr-2 text-slate-400'></i> Become a Partner</a>
            <a href = "<?= $base ?>get-involved/index.php#membership" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-id-card-clip mr-2 text-slate-400'></i> Membership</a>
            </div>
            </div>

            <!-- 10. CONTACT -->
            <div class = 'nav-item-dropdown'>
            <a href = "<?= $base ?>contact/index.php" class = "nav-link-item px-2.5 py-2 rounded-lg <?= $navActive === 'contact' ? 'text-brandRed font-semibold' : 'hover:text-white hover:bg-slate-800/60' ?> transition-all flex items-center gap-1">
            CONTACT <i class = 'fa-solid fa-chevron-down text-[8px] opacity-60'></i>
            </a>
            <div class = 'dropdown-menu absolute top-full right-0 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl p-2 text-slate-600 text-xs'>
            <a href = "<?= $base ?>contact/index.php" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-envelope-open-text mr-2 text-slate-400'></i> Contact Us</a>
            <a href = "<?= $base ?>contact/index.php#offices" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-building-user mr-2 text-slate-400'></i> Office Locations</a>
            <a href = "<?= $base ?>contact/index.php#feedback" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-comment-dots mr-2 text-slate-400'></i> Feedback</a>
            <a href = "<?= $base ?>contact/index.php#whistleblower" class = 'block px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition-colors'><i class = 'fa-solid fa-triangle-exclamation mr-2 text-slate-400'></i> Report a Concern</a>
            </div>
            </div>

            </nav>

            <!-- CTA CONTRIBUTE BUTTON -->
            <div class = 'hidden lg:flex items-center gap-3 shrink-0'>
            <a href = "<?= $base ?>get-involved/contribute-with-us.php" class = 'animate-pulse-glow bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-1.5 uppercase tracking-wider'>
            <i class = 'fa-solid fa-handshake text-[10px]'></i> Let’s Work Together
            </a>
            </div>

            <!-- MOBILE MENU BUTTON -->
            <button id = 'mobile-menu-btn' class = 'mobile-menu-btn lg:hidden text-slate-900 p-2 rounded-lg relative z-[90]' aria-label = 'Toggle menu' aria-expanded = 'false' aria-controls = 'mobile-tray'>
            <span class = 'hamburger-line block w-6 h-0.5 bg-slate-900 mb-1.5'></span>
            <span class = 'hamburger-line block w-6 h-0.5 bg-slate-900 mb-1.5'></span>
            <span class = 'hamburger-line block w-6 h-0.5 bg-slate-900'></span>
            </button>
            </div>
            </div>

            <?php
            // LATEST NEWS TICKER
            // Headlines are read straight from news_articles, so anything published
            // on the news page appears here automatically. It sits inside <header>
            // (which is sticky) so it stays pinned under the nav while scrolling.
            $newsTicker = [];
            if ( isset( $pdo ) ) {
                try {
                    $newsTickerStmt = $pdo->query(
                        'SELECT slug, title, category FROM news_articles
                          WHERE is_published = 1 AND slug IS NOT NULL AND slug <> ""
                          ORDER BY published_at DESC, id DESC LIMIT 8'
                    );
                    $newsTicker = $newsTickerStmt ? $newsTickerStmt->fetchAll( PDO::FETCH_ASSOC ) : [];
                } catch ( Throwable $tickerError ) {
                    $newsTicker = [];
                }
            }

            $newsTickerLabels = [
                'press_release' => 'Press Release',
                'report'        => 'Report',
                'story'         => 'Story',
                'blog'          => 'Blog',
                'event'         => 'Event',
            ];
            ?>
            <?php if ( count( $newsTicker ) > 1 ): ?>
            <!-- LATEST NEWS TICKER -->
            <div class = 'news-ticker relative z-10 w-full border-t border-slate-200/70' role = 'region' aria-label = 'Latest news headlines'>
            <div class = 'w-full px-4 sm:px-6 lg:px-8 flex items-stretch h-10'>

            <span class = 'news-ticker-label shrink-0 inline-flex items-center gap-2 bg-brandRed text-white text-[10px] font-bold uppercase tracking-[0.18em] px-3 sm:px-4'>
            <i class = 'fa-solid fa-bolt text-[9px]'></i><span class = 'hidden sm:inline'>Latest</span><span class = 'sm:hidden'>News</span>
            </span>

            <div class = 'news-ticker-viewport relative flex-1 overflow-hidden' style = '--ticker-duration: <?= max( 36, count( $newsTicker ) * 9 ) ?>s'>
            <div class = 'news-ticker-track items-center h-full'>
            <?php foreach ( [ 0, 1 ] as $tickerCopy ): ?>
            <?php foreach ( $newsTicker as $tickerItem ): ?>
            <a href = "<?= $base ?>news/<?= rawurlencode( $tickerItem[ 'slug' ] ) ?>"
            class = 'news-ticker-item inline-flex items-center gap-2 shrink-0 h-full px-3 sm:px-4 text-[11px] sm:text-xs text-slate-600 hover:text-brandRed transition-colors group'
            <?= $tickerCopy === 1 ? 'aria-hidden="true" tabindex="-1"' : '' ?>>
            <span class = 'w-1 h-1 rounded-full bg-brandRed/60 group-hover:bg-brandRed shrink-0'></span>
            <span class = 'whitespace-nowrap font-medium'><?= sanitize( $tickerItem[ 'title' ] ) ?></span>
            <span class = 'text-[9px] uppercase tracking-wider text-slate-400 whitespace-nowrap'><?= sanitize( $newsTickerLabels[ $tickerItem[ 'category' ] ] ?? ucfirst( str_replace( '_', ' ', (string) $tickerItem[ 'category' ] ) ) ) ?></span>
            </a>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </div>
            </div>

            <a href = "<?= $base ?>news/index.php" class = 'shrink-0 hidden sm:inline-flex items-center gap-1.5 pl-4 text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-brandRed transition-colors border-l border-slate-200/70 px-3 sm:px-4'>
            All News <i class = 'fa-solid fa-arrow-right text-[9px]'></i>
            </a>
            </div>
            </div>
            <?php endif; ?>
            </header>

            <!-- MOBILE SIDE TRAY -->
            <div id = 'mobile-tray-overlay' class = 'mobile-tray-overlay'></div>
            <div id = 'mobile-tray' class = 'mobile-tray' role = 'dialog' aria-modal = 'true' aria-label = 'Navigation menu'>
            <div class = 'mobile-tray-header'>
            <a href = "<?= $base ?>index.php" class = 'flex items-center gap-2.5'>
            <img src = "<?= $base ?>assets/images/logo.png" alt = 'WRC logo' class = 'w-9 h-9 object-contain rounded-lg bg-slate-100 p-1' />
            <span class = 'text-sm font-bold text-slate-900 tracking-wide'>WRC</span>
            </a>
            <button id = 'mobile-tray-close' class = 'mobile-tray-close' aria-label = 'Close menu'>
            <i class = 'fa-solid fa-xmark text-base'></i>
            </button>
            </div>

            <div class = 'mobile-tray-body'>
            <nav class = 'mobile-tray-nav'>
            <a href = "<?= $base ?>index.php" class = 'mobile-tray-link'>HOME</a>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.05'>
            <span>ABOUT US</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>about/who-we-are.php">Who We Are</a>
            <a href = "<?= $base ?>about/vision-mission-values.php">Vision, Mission & Values</a>
            <a href = "<?= $base ?>about/history.php">Our History</a>
            <a href = "<?= $base ?>about/leadership.php">Leadership</a>
            <a href = "<?= $base ?>about/organizational-structure.php">Organizational Structure</a>
            <a href = "<?= $base ?>about/team.php">Our Team</a>
            <a href = "<?= $base ?>about/policies-compliance.php">Policies & Compliance</a>
            <a href = "<?= $base ?>about/annual-reports.php">Annual Reports</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.10'>
            <span>OUR WORK</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>our-work/index.php">About Our Work</a>
            <a href = "<?= $base ?>projects/index.php">All Projects</a>
            <a href = "<?= $base ?>projects/index.php#active">Active Projects</a>
            <a href = "<?= $base ?>projects/index.php#completed">Completed Projects</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.15'>
            <span>PROJECTS</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>projects/index.php#active">Active Projects</a>
            <a href = "<?= $base ?>projects/index.php#completed">Completed Projects</a>
            <a href = "<?= $base ?>projects/index.php#portfolio">Project Portfolio</a>
            <a href = "<?= $base ?>projects/index.php#stories">Success Stories</a>
            <a href = "<?= $base ?>projects/index.php#dashboard">Project Dashboard</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.25'>
            <span>WHERE WE WORK</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>where-we-work/index.php">Overview</a>
            <a href = "<?= $base ?>where-we-work/index.php#map">Pakistan Map</a>
            <a href = "<?= $base ?>where-we-work/index.php#kp">Khyber Pakhtunkhwa</a>
            <a href = "<?= $base ?>where-we-work/index.php#punjab">Punjab</a>
            <a href = "<?= $base ?>where-we-work/index.php#sindh">Sindh</a>
            <a href = "<?= $base ?>where-we-work/index.php#balochistan">Balochistan</a>
            <a href = "<?= $base ?>where-we-work/index.php#districts">District Coverage</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.30'>
            <span>IMPACT</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>impact/index.php">Impact Dashboard</a>
            <a href = "<?= $base ?>impact/index.php#stats">Beneficiary Statistics</a>
            <a href = "<?= $base ?>impact/index.php#mne">Monitoring & Evaluation</a>
            <a href = "<?= $base ?>impact/index.php#case-studies">Case Studies</a>
            <a href = "<?= $base ?>impact/index.php#sdgs">SDG Contributions</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.35'>
            <span>RESOURCES</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>resources/index.php#annual-reports">Annual Reports</a>
            <a href = "<?= $base ?>resources/index.php#financial-reports">Financial Reports</a>
            <a href = "<?= $base ?>resources/index.php#registration">Registration</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.40'>
            <span>NEWS & MEDIA</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>news/index.php">Latest News</a>
            <a href = "<?= $base ?>news/index.php#photos">Photo Gallery</a>
            <a href = "<?= $base ?>news/index.php#videos">Video Gallery</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.45'>
            <span>GET INVOLVED</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>get-involved/contribute-with-us.php">Let’s Work Together</a>
            <a href = "<?= $base ?>get-involved/index.php#volunteer">Volunteer</a>
            <a href = "<?= $base ?>get-involved/index.php#careers">Careers</a>
            <a href = "<?= $base ?>get-involved/index.php#partner">Become a Partner</a>
            <a href = "<?= $base ?>get-involved/index.php#membership">Membership</a>
            </div></div>

            <button class = 'mobile-tray-accordion-btn' data-delay = '0.50'>
            <span>CONTACT</span>
            <i class = 'acc-icon fa-solid fa-chevron-down'></i>
            </button>
            <div class = 'mobile-tray-submenu'><div>
            <a href = "<?= $base ?>contact/index.php">Contact Us</a>
            <a href = "<?= $base ?>contact/index.php#offices">Office Locations</a>
            <a href = "<?= $base ?>contact/index.php#feedback">Feedback</a>
            <a href = "<?= $base ?>contact/index.php#whistleblower">Report a Concern</a>
            </div></div>
            </nav>
            </div>

            <div class = 'mobile-tray-footer'>
            <a href = "<?= $base ?>get-involved/contribute-with-us.php" class = 'mobile-tray-cta'>
            <i class = 'fa-solid fa-handshake text-xs'></i> Let’s Work Together
            </a>
            </div>
            </div>
