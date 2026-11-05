<?php
$currentPage = basename( $_SERVER[ 'PHP_SELF' ] );
$adminUser = getAdminUser();
$adminName = $adminUser[ 'full_name' ] ?? ( $_SESSION[ 'admin_name' ] ?? 'Admin' );
$adminRole = $adminUser[ 'role' ] ?? ( $_SESSION[ 'admin_role' ] ?? 'admin' );
// Same logo token as the public site: the CDN caches /assets/* for 7 days.
$logoV = @filemtime( dirname( __DIR__, 2 ) . '/assets/images/logo.png' );
?>
<!DOCTYPE html>
<html lang = 'en'>
<head>
<meta charset = 'UTF-8'>
<meta name = 'viewport' content = 'width=device-width, initial-scale=1.0'>
<title>WRC Admin - <?= $pageTitle ?? 'Dashboard' ?></title>
<script src = 'https://cdn.tailwindcss.com'></script>
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
                ink: {
                    900: '#0E1F27',
                    800: '#152C36',
                    700: '#1D3A47'
                }
                ,
            }
            ,
            fontFamily: {
                display: [ 'Fraunces', 'serif' ],
                sans: [ 'Inter', 'sans-serif' ],
            }
            ,
        }
    }
}
</script>
<link rel = 'stylesheet' href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
<link rel = 'preconnect' href = 'https://fonts.googleapis.com'>
<link rel = 'preconnect' href = 'https://fonts.gstatic.com' crossorigin>
<link href = 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap' rel = 'stylesheet'>
<style>
body {
    font-family: 'Inter', sans-serif;
}
.font-heading {
    font-family: 'Fraunces', serif;
}
</style>
</head>
<body class = 'bg-slate-100 text-slate-800 min-h-screen'>

<!-- Top Bar -->
<header class = 'fixed top-0 left-0 right-0 z-50 bg-white border-b border-slate-200 shadow-sm h-16 flex items-center'>
<div class = 'flex items-center justify-between w-full px-4 lg:px-6'>
<!-- Left: Logo + Mobile Toggle -->
<div class = 'flex items-center gap-3'>
<button id = 'sidebar-toggle' class = 'lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 transition-colors' aria-label = 'Toggle sidebar'>
<i class = 'fa-solid fa-bars text-lg'></i>
</button>
<a href = 'index.php' class = 'flex items-center gap-2.5'>
<img src = '../../assets/images/logo.png?v=<?= $logoV ? (string) $logoV : '1' ?>' alt = 'WRC logo' class = 'w-8 h-8 object-contain rounded-lg' />
<span class = 'text-sm font-bold text-slate-900 hidden sm:inline'>WRC Admin</span>
</a>
</div>

<!-- Right: Admin Info -->
<div class = 'flex items-center gap-4'>
<a href = '../index.php' target = '_blank' class = 'text-slate-500 hover:text-brandRed text-sm transition-colors' title = 'View Site'>
<i class = 'fa-solid fa-external-link'></i>
</a>
<div class = 'flex items-center gap-2.5'>
<div class = 'w-8 h-8 rounded-full brandRed flex items-center justify-center text-white text-xs font-bold'>
<?= strtoupper( substr( $adminName, 0, 1 ) ) ?>
</div>
<div class = 'hidden sm:block'>
<p class = 'text-sm font-semibold text-slate-900 leading-none'><?= htmlspecialchars( $adminName ) ?></p>
<p class = 'text-[11px] text-slate-400 capitalize'><?= htmlspecialchars( $adminRole ) ?></p>
</div>
</div>
<a href = 'logout.php' class = 'text-slate-400 hover:text-red-500 text-sm transition-colors' title = 'Logout'>
<i class = 'fa-solid fa-right-from-bracket'></i>
</a>
</div>
</div>
</header>

<!-- Sidebar Overlay ( mobile ) -->
<div id = 'sidebar-overlay' class = 'fixed inset-0 bg-black/40 z-40 hidden lg:hidden' onclick = 'toggleSidebar()'></div>

<!-- Sidebar -->
<?php include __DIR__ . '/sidebar.php';
?>

<!-- Main Content -->
<main class = 'pt-16 lg:pl-64 min-h-screen transition-all duration-300' id = 'main-content'>
<div class = 'p-4 sm:p-6 lg:p-8'>

<!-- Flash Messages -->
<?php $flash = getFlash();
if ( $flash ): ?>
<div class = "mb-6 p-4 rounded-xl flex items-center gap-3 <?= $flash['type'] === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700' ?>">
<i class = "fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
<span class = 'text-sm font-medium'><?= htmlspecialchars( $flash[ 'message' ] ) ?></span>
<button onclick = 'this.parentElement.remove()' class = 'ml-auto opacity-60 hover:opacity-100'><i class = 'fa-solid fa-xmark'></i></button>
</div>
<?php endif;
?>

<!-- Upload Widget Styles -->
<style>
.upload-zone {
    border:2px dashed #cbd5e1;
    border-radius:12px;
    padding:1.5rem;
    text-align:center;
    cursor:pointer;
    transition:all .2s;
    background:#f8fafc;
    position:relative}
    .upload-zone:hover, .upload-zone.dragover {
        border-color:#ca0000;
        background:#fef2f2}
        .upload-zone input[type=file] {
            display:none}
            .upload-zone .upload-placeholder {
                pointer-events:none}
                .upload-zone .upload-placeholder i {
                    font-size:1.5rem;
                    color:#94a3b8;
                    margin-bottom:.5rem}
                    .upload-zone .upload-placeholder p {
                        font-size:.75rem;
                        color:#64748b}
                        .upload-preview {
                            position:relative;
                            display:inline-block;
                            margin-top:.75rem}
                            .upload-preview img {
                                max-height:120px;
                                border-radius:8px;
                                border:1px solid #e2e8f0;
                                object-fit:cover}
                                .upload-preview .upload-remove {
                                    position:absolute;
                                    top:-6px;
                                    right:-6px;
                                    width:20px;
                                    height:20px;
                                    border-radius:50%;
                                    background:#ef4444;
                                    color:#fff;
                                    border:none;
                                    font-size:10px;
                                    cursor:pointer;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    line-height:1}
                                    .upload-preview .upload-remove:hover {
                                        background:#dc2626}
                                        </style>

                                        <!-- Upload Widget Script -->
                                        <script>
                                        // Bind one upload widget. Exposed so screens that build their
                                        // fields after page load (the About page item editor does) can
                                        // wire up the widgets they just inserted.
                                        window.WRCBindUploadWidget = function( widget ) {
                                            if ( !widget || widget.dataset.wrcUploadBound === '1' ) {
                                                return;
                                            }
                                            widget.dataset.wrcUploadBound = '1';
                                                var type = widget.dataset.type || 'image';
                                                var baseUrl = widget.dataset.baseUrl || '/';
                                                var input = widget.querySelector( 'input[type=file]' );
                                                // The preview below replaces everything inside the zone. If the
                                                // file input lives there it gets destroyed with it, so replacing
                                                // an image that already has a value silently stops working.
                                                // Keep the input on the widget itself so it always survives.
                                                if ( !input ) {
                                                    input = document.createElement( 'input' );
                                                    input.type = 'file';
                                                    input.accept = ( type === 'image' ) ? 'image/*' : '';
                                                }
                                                if ( input.parentNode !== widget ) {
                                                    input.style.display = 'none';
                                                    widget.appendChild( input );
                                                }
                                                var pathInput = widget.closest( '.form-group' )?.querySelector( 'input.upload-path-input' ) || document.getElementById( widget.dataset.target );
                                                var preview = widget.querySelector( '.upload-preview' );
                                                var zone = widget.querySelector( '.upload-zone' );

                                                zone.addEventListener( 'click', function() {
                                                    input.click()}
                                                );
                                                zone.addEventListener( 'dragover', function( e ) {
                                                    e.preventDefault();
                                                    zone.classList.add( 'dragover' )}
                                                );
                                                zone.addEventListener( 'dragleave', function() {
                                                    zone.classList.remove( 'dragover' )}
                                                );
                                                zone.addEventListener( 'drop', function( e ) {
                                                    e.preventDefault();
                                                    zone.classList.remove( 'dragover' );
                                                    if ( e.dataTransfer.files.length ) {
                                                        handleFile( e.dataTransfer.files[ 0 ], type )}
                                                    } );
                                                    input.addEventListener( 'change', function() {
                                                        if ( input.files.length ) {
                                                            handleFile( input.files[ 0 ], type )}
                                                        } );

                                                        function handleFile( file, uploadType ) {
                                                            var fd = new FormData();
                                                            fd.append( 'file', file );
                                                            fd.append( 'type', uploadType );
                                                            zone.innerHTML = '<div class="upload-placeholder"><i class="fa-solid fa-spinner fa-spin"></i><p>Uploading...</p></div>';
                                                            fetch( 'ajax-upload.php', {
                                                                method:'POST', body:fd}
                                                            )
                                                            .then( function( r ) {
                                                                return r.json()}
                                                            )
                                                            .then( function( res ) {
                                                                if ( res.success ) {
                                                                    if ( pathInput ) {
                                                                        pathInput.value = res.path;
                                                                        pathInput.dispatchEvent( new Event( 'change' ) )}
                                                                        showPreview( zone, res.path, uploadType );
                                                                    } else {
                                                                        zone.innerHTML = '<div class="upload-placeholder"><i class="fa-solid fa-circle-exclamation text-red-400"></i><p class="text-red-500">'+res.message+'</p></div>';
                                                                        setTimeout( function() {
                                                                            zone.innerHTML = '<div class="upload-placeholder"><i class="fa-solid fa-cloud-arrow-up"></i><p>Click or drag to upload</p></div>';
                                                                        }
                                                                        , 3000 );
                                                                    }
                                                                }
                                                            )
                                                            .catch( function() {
                                                                zone.innerHTML = '<div class="upload-placeholder"><i class="fa-solid fa-circle-exclamation text-red-400"></i><p class="text-red-500">Upload failed. Try again.</p></div>';
                                                            }
                                                        );
                                                    }

                                                    function showPreview( zone, path, type ) {
                                                        var imageUrl = /^(https?:)?\/\//i.test( path ) ? path : baseUrl + path.replace( /^\/+/, '' );
                                                        var html = '<div class="upload-preview">';
                                                        if ( type === 'image' ) {
                                                            html += '<img src="' + imageUrl.replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ) + '" alt="Preview">';
                                                        } else {
                                                            html += '<div class="bg-white border border-slate-200 rounded-lg p-3 text-xs text-slate-600"><i class="fa-solid fa-file-pdf text-red-400 mr-1"></i>' + path.split( '/' ).pop() + '</div>';
                                                        }
                                                        html += '<button type="button" class="upload-remove" title="Remove">&times;</button></div>';
                                                        zone.innerHTML = html;
                                                        zone.querySelector( '.upload-remove' ).addEventListener( 'click', function( e ) {
                                                            e.stopPropagation();
                                                            if ( pathInput ) {
                                                                pathInput.value = '';
                                                                // Tell any listener (the About page card editor keeps
                                                                // its own copy of the value) that the path is now empty.
                                                                pathInput.dispatchEvent( new Event( 'change' ) );
                                                            }
                                                            zone.innerHTML = '<div class="upload-placeholder"><i class="fa-solid fa-cloud-arrow-up"></i><p>Click or drag to upload</p></div>';
                                                        } );
                                                    }

                                                // Show existing image on load
                                                if ( pathInput && pathInput.value ) {
                                                    var v = pathInput.value.trim();
                                                    if ( v ) {
                                                        showPreview( zone, v, type );
                                                    }
                                                }
                                            };

                                        window.WRCBindUploadWidgets = function( root ) {
                                            ( root || document ).querySelectorAll( '.upload-widget' ).forEach( window.WRCBindUploadWidget );
                                        };

                                        document.addEventListener( 'DOMContentLoaded', function() {
                                            window.WRCBindUploadWidgets( document );
                                        } );
                                </script>

<!-- ===================== ===================== =====================
     Image focus: drag / crop / adjust
     Added to every image field in the dashboard. The chosen point is
     stored as <path>#<x>-<y> on the value the field already had.
     ============================================================ -->
<style>
.wrc-focal-btn { display:inline-flex; align-items:center; gap:.45rem; margin-top:.6rem; padding:.45rem .85rem; border-radius:999px; border:1px solid #e2e8f0; background:#fff; color:#334155; font-size:.75rem; font-weight:600; cursor:pointer; transition:all .18s ease; }
.wrc-focal-btn:hover { border-color:#ca0000; color:#ca0000; background:#fef2f2; transform:translateY(-1px); }
.wrc-focal-btn i { color:#ca0000; font-size:.72rem; }
.wrc-focal-btn-inline { display:flex; width:fit-content; }

.wrc-focal-backdrop { position:fixed; inset:0; z-index:120; background:rgba(2,6,23,.66); backdrop-filter:blur(3px); display:flex; align-items:center; justify-content:center; padding:1rem; }
.wrc-focal-backdrop[hidden] { display:none; }
.wrc-focal-modal { background:#fff; width:min(980px,100%); max-height:92vh; overflow:auto; border-radius:20px; box-shadow:0 40px 80px -20px rgba(0,0,0,.55); }
.wrc-focal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:1.25rem 1.5rem; border-bottom:1px solid #e2e8f0; position:sticky; top:0; background:#fff; z-index:2; border-radius:20px 20px 0 0; }
.wrc-focal-title { display:flex; gap:.7rem; align-items:flex-start; }
.wrc-focal-title i { color:#ca0000; font-size:1.05rem; margin-top:.2rem; }
.wrc-focal-title h3 { margin:0; font-size:1.02rem; font-weight:700; color:#0f172a; }
.wrc-focal-title p { margin:.3rem 0 0; font-size:.78rem; color:#64748b; max-width:56ch; line-height:1.55; }
.wrc-focal-close { border:none; background:#f1f5f9; width:32px; height:32px; border-radius:50%; font-size:1.1rem; line-height:1; color:#475569; cursor:pointer; flex:none; }
.wrc-focal-close:hover { background:#fee2e2; color:#b91c1c; }
.wrc-focal-body { display:grid; grid-template-columns:1.1fr .9fr; gap:1.35rem; padding:1.35rem 1.5rem; }
@media (max-width:820px) { .wrc-focal-body { grid-template-columns:1fr; } }
.wrc-focal-pane { display:flex; flex-direction:column; gap:.6rem; min-width:0; }
.wrc-focal-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:#94a3b8; }
.wrc-focal-stage { background:#0b1620; border:1px solid #1e293b; border-radius:14px; padding:14px; display:flex; align-items:center; justify-content:center; min-height:210px; max-height:370px; overflow:auto; }
.wrc-focal-full { position:relative; display:inline-block; line-height:0; max-width:100%; cursor:crosshair; touch-action:none; }
.wrc-focal-full img { display:block; max-width:100%; max-height:310px; width:auto; height:auto; user-select:none; }
.wrc-focal-marker { position:absolute; width:34px; height:34px; margin:-17px 0 0 -17px; border-radius:50%; background:rgba(202,0,0,.9); border:3px solid #fff; box-shadow:0 3px 12px rgba(0,0,0,.55); cursor:grab; display:flex; align-items:center; justify-content:center; z-index:2; }
.wrc-focal-marker::after { content:''; width:8px; height:8px; border-radius:50%; background:#fff; }
.wrc-focal-marker:active { cursor:grabbing; }
.wrc-focal-marker:focus-visible { outline:3px solid #f59e0b; outline-offset:3px; }
.wrc-focal-crop { position:relative; width:100%; aspect-ratio:16/9; border-radius:14px; overflow:hidden; background:#0e1f27; border:1px solid #e2e8f0; }
.wrc-focal-crop img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:50% 50%; }
.wrc-focal-row { display:flex; flex-wrap:wrap; gap:.4rem; }
.wrc-focal-row button { border:1px solid #e2e8f0; background:#fff; color:#475569; font-size:.72rem; font-weight:600; padding:.35rem .62rem; border-radius:8px; cursor:pointer; transition:all .15s; }
.wrc-focal-row button:hover { border-color:#ca0000; color:#ca0000; }
.wrc-focal-row button.is-on { background:#0e1f27; border-color:#0e1f27; color:#fff; }
.wrc-focal-foot { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; padding:1rem 1.5rem 1.35rem; border-top:1px solid #e2e8f0; background:#f8fafc; border-radius:0 0 20px 20px; }
.wrc-focal-hint { font-size:.78rem; color:#64748b; display:flex; align-items:center; gap:.45rem; }
.wrc-focal-hint i { color:#ca0000; }
.wrc-focal-actions { display:flex; gap:.55rem; margin-left:auto; }
.wrc-focal-actions button { border-radius:10px; font-size:.8rem; font-weight:700; padding:.6rem 1.05rem; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#475569; transition:all .15s; }
.wrc-focal-actions button:hover { background:#f1f5f9; }
.wrc-focal-actions .wrc-focal-apply { background:#ca0000; border-color:#ca0000; color:#fff; box-shadow:0 8px 18px -8px rgba(202,0,0,.7); }
.wrc-focal-actions .wrc-focal-apply:hover { background:#a30000; border-color:#a30000; }
</style>

<script>
( function () {
    'use strict';

    var FOCAL_RE = /^(.*)#(\d{1,3})-(\d{1,3})$/;
    var BASE_URL = <?= json_encode( siteBaseUrl() ) ?>;

    function clamp( n ) { return Math.max( 0, Math.min( 100, n ) ); }

    // "path#40-60" -> { path, x, y }  ( x/y are percentages of the photo )
    function splitFocal( value ) {
        var v = ( value || '' ).trim();
        var m = v.match( FOCAL_RE );
        if ( !m || !m[ 1 ] ) return { path: v, x: 50, y: 50 };
        return {
            path: m[ 1 ],
            x: clamp( parseInt( m[ 2 ], 10 ) ),
            y: clamp( parseInt( m[ 3 ], 10 ) )
        };
    }

    function imageUrl( path, baseUrl ) {
        if ( /^(https?:)?\/\//i.test( path ) || /^data:/i.test( path ) ) return path;
        return ( baseUrl || BASE_URL || '/' ) + path.replace( /^\/+/, '' );
    }

    // A centred point is the natural default, so it is stored plainly
    // instead of leaving "#50-50" noise on every image path.
    function focalValue( path, x, y ) {
        x = Math.round( x );
        y = Math.round( y );
        if ( x === 50 && y === 50 ) return path;
        return path + '#' + x + '-' + y;
    }

    /* ============================================================
       Shared modal
       ============================================================ */

    var modal = null;
    var state = null;

    function buildModal() {
        var el = document.createElement( 'div' );
        el.className = 'wrc-focal-backdrop';
        el.id = 'wrcFocalModal';
        el.hidden = true;
        el.innerHTML =
            '<div class="wrc-focal-modal" role="dialog" aria-modal="true" aria-label="Adjust image focus">' +
                '<div class="wrc-focal-head">' +
                    '<div class="wrc-focal-title">' +
                        '<i class="fa-solid fa-crosshairs"></i>' +
                        '<div>' +
                            '<h3>Adjust image focus</h3>' +
                            '<p>Drag the marker over the part of the photo visitors should see. Everything outside it is cropped away wherever the image is used.</p>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="wrc-focal-close" aria-label="Close">&times;</button>' +
                '</div>' +
                '<div class="wrc-focal-body">' +
                    '<div class="wrc-focal-pane">' +
                        '<span class="wrc-focal-label">Full photo &mdash; drag to place the focus</span>' +
                        '<div class="wrc-focal-stage">' +
                            '<div class="wrc-focal-full"><img alt="Full photo"><span class="wrc-focal-marker" tabindex="0" title="Drag me"></span></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="wrc-focal-pane">' +
                        '<span class="wrc-focal-label">How it will be cropped</span>' +
                        '<div class="wrc-focal-crop"><img alt="Crop preview"></div>' +
                        '<div class="wrc-focal-row" data-role="aspects">' +
                            '<button type="button" data-ar="16/9" class="is-on">Wide</button>' +
                            '<button type="button" data-ar="4/3">4:3</button>' +
                            '<button type="button" data-ar="1/1">Square</button>' +
                            '<button type="button" data-ar="3/4">Portrait</button>' +
                        '</div>' +
                        '<span class="wrc-focal-label">Quick positions</span>' +
                        '<div class="wrc-focal-row" data-role="presets">' +
                            '<button type="button" data-x="50" data-y="50">Centre</button>' +
                            '<button type="button" data-x="50" data-y="0">Top</button>' +
                            '<button type="button" data-x="50" data-y="100">Bottom</button>' +
                            '<button type="button" data-x="0" data-y="50">Left</button>' +
                            '<button type="button" data-x="100" data-y="50">Right</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="wrc-focal-foot">' +
                    '<span class="wrc-focal-hint"></span>' +
                    '<div class="wrc-focal-actions">' +
                        '<button type="button" class="wrc-focal-cancel">Cancel</button>' +
                        '<button type="button" class="wrc-focal-reset">Reset</button>' +
                        '<button type="button" class="wrc-focal-apply">Apply focus</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild( el );

        var full = el.querySelector( '.wrc-focal-full' );
        var dragging = false;

        function dragTo( e ) {
            var box = full.getBoundingClientRect();
            if ( !box.width || !box.height ) return;
            setFocal( ( ( e.clientX - box.left ) / box.width ) * 100, ( ( e.clientY - box.top ) / box.height ) * 100 );
        }

        full.addEventListener( 'pointerdown', function ( e ) {
            e.preventDefault();
            dragging = true;
            try { full.setPointerCapture( e.pointerId ); } catch ( err ) {}
            dragTo( e );
        } );
        full.addEventListener( 'pointermove', function ( e ) { if ( dragging ) dragTo( e ); } );
        full.addEventListener( 'pointerup', function () { dragging = false; } );
        full.addEventListener( 'pointercancel', function () { dragging = false; } );

        // Keyboard nudging for the marker ( arrows, Shift = 10% ).
        el.querySelector( '.wrc-focal-marker' ).addEventListener( 'keydown', function ( e ) {
            if ( !state ) return;
            var step = e.shiftKey ? 10 : 1;
            var x = state.x, y = state.y;
            if ( e.key === 'ArrowLeft' ) x -= step;
            else if ( e.key === 'ArrowRight' ) x += step;
            else if ( e.key === 'ArrowUp' ) y -= step;
            else if ( e.key === 'ArrowDown' ) y += step;
            else return;
            e.preventDefault();
            e.stopPropagation();
            setFocal( x, y );
        } );

        el.querySelector( '.wrc-focal-close' ).addEventListener( 'click', closeFocal );
        el.querySelector( '.wrc-focal-cancel' ).addEventListener( 'click', closeFocal );
        el.querySelector( '.wrc-focal-reset' ).addEventListener( 'click', function () { setFocal( 50, 50 ); } );
        el.querySelector( '.wrc-focal-apply' ).addEventListener( 'click', applyFocal );
        el.addEventListener( 'click', function ( e ) { if ( e.target === el ) closeFocal(); } );

        el.querySelectorAll( '[data-role="aspects"] button' ).forEach( function ( b ) {
            b.addEventListener( 'click', function () {
                el.querySelectorAll( '[data-role="aspects"] button' ).forEach( function ( o ) { o.classList.remove( 'is-on' ); } );
                b.classList.add( 'is-on' );
                el.querySelector( '.wrc-focal-crop' ).style.aspectRatio = b.dataset.ar;
            } );
        } );

        el.querySelectorAll( '[data-role="presets"] button' ).forEach( function ( b ) {
            b.addEventListener( 'click', function () {
                setFocal( parseFloat( b.dataset.x ), parseFloat( b.dataset.y ) );
            } );
        } );

        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && modal && !modal.hidden ) closeFocal();
        } );

        return el;
    }

    function openFocal( opts ) {
        var f = splitFocal( opts && opts.value );
        if ( !f.path ) return;
        if ( !modal ) modal = buildModal();

        state = {
            path: f.path,
            x: f.x,
            y: f.y,
            pathInput: ( opts && opts.pathInput ) || null,
            baseUrl: ( opts && opts.baseUrl ) || BASE_URL,
            onChange: ( opts && opts.onChange ) || null
        };

        var url = imageUrl( f.path, state.baseUrl );
        var fullImg = modal.querySelector( '.wrc-focal-full img' );
        var cropImg = modal.querySelector( '.wrc-focal-crop img' );
        fullImg.onerror = function () {
            modal.querySelector( '.wrc-focal-hint' ).innerHTML =
                '<i class="fa-solid fa-triangle-exclamation"></i> This image could not be loaded from <b>' +
                f.path.replace( /</g, '&lt;' ) + '</b>.';
        };
        fullImg.src = url;
        cropImg.src = url;

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        setFocal( f.x, f.y );
        try { modal.querySelector( '.wrc-focal-marker' ).focus( { preventScroll: true } ); } catch ( err ) {}
    }

    function closeFocal() {
        if ( !modal ) return;
        modal.hidden = true;
        document.body.style.overflow = '';
        state = null;
    }

    function setFocal( x, y ) {
        if ( !state || !modal ) return;
        x = clamp( x );
        y = clamp( y );
        state.x = x;
        state.y = y;

        modal.querySelector( '.wrc-focal-marker' ).style.left = x + '%';
        modal.querySelector( '.wrc-focal-marker' ).style.top = y + '%';
        modal.querySelector( '.wrc-focal-crop img' ).style.objectPosition = x + '% ' + y + '%';

        var rx = Math.round( x ), ry = Math.round( y );
        modal.querySelector( '.wrc-focal-hint' ).innerHTML = ( rx === 50 && ry === 50 )
            ? '<i class="fa-solid fa-circle-info"></i> No focus set &mdash; the photo is used exactly as uploaded.'
            : '<i class="fa-solid fa-crosshairs"></i> Focus at ' + rx + '% across, ' + ry + '% down.';
    }

    function applyFocal() {
        if ( !state ) return;
        var value = focalValue( state.path, state.x, state.y );
        if ( state.pathInput ) {
            state.pathInput.value = value;
            // Tell the screen its own copy of the value changed too ( the
            // About page card editor keeps a parallel array ).
            state.pathInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
            state.pathInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
        }
        if ( typeof state.onChange === 'function' ) state.onChange( value );
        closeFocal();
    }

    /* ============================================================
       Upload widgets ( every "upload from your computer" field )
       ============================================================ */

    function resolvePathInput( widget ) {
        var target = widget.getAttribute( 'data-target' ) || '';
        var group = widget.closest( '.form-group' );
        var input = group ? group.querySelector( 'input.upload-path-input' ) : null;
        if ( !input && target ) input = document.getElementById( target );
        if ( !input ) {
            var fieldGroup = widget.closest( '.field-group' );
            if ( fieldGroup ) input = fieldGroup.querySelector( 'input.upload-path-input' );
        }
        if ( input && ( input.type === 'file' || input.type === 'hidden' ) ) input = null;
        return input;
    }

    function attachWidgetFocal( widget ) {
        if ( widget.dataset.wrcFocalBound === '1' ) return;
        if ( ( widget.dataset.type || 'image' ) !== 'image' ) return;
        var pathInput = resolvePathInput( widget );
        if ( !pathInput ) return;

        widget.dataset.wrcFocalBound = '1';
        pathInput.dataset.wrcFocalBound = '1';
        var baseUrl = widget.dataset.baseUrl || BASE_URL;

        var btn = document.createElement( 'button' );
        btn.type = 'button';
        btn.className = 'wrc-focal-btn';
        btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i><span>Adjust focus</span>';
        widget.appendChild( btn );

        var sync = function () {
            var f = splitFocal( pathInput.value );
            btn.style.display = f.path ? '' : 'none';
            var img = widget.querySelector( '.upload-preview img' );
            if ( img && f.path ) {
                var want = imageUrl( f.path, baseUrl );
                if ( img.getAttribute( 'src' ) !== want ) img.src = want;
                img.style.objectPosition = f.x + '% ' + f.y + '%';
            }
        };

        widget._wrcFocalSync = sync;
        pathInput.addEventListener( 'input', sync );
        pathInput.addEventListener( 'change', sync );
        btn.addEventListener( 'click', function ( e ) {
            e.preventDefault();
            e.stopPropagation();
            openFocal( { value: pathInput.value, pathInput: pathInput, baseUrl: baseUrl, onChange: sync } );
        } );
        sync();
    }

    /* ============================================================
       Plain image fields that have no upload widget next to them
       ( editorial image, resources hero, gallery rows, ... )
       ============================================================ */

    function looksLikeImageInput( el ) {
        if ( !el || el.tagName !== 'INPUT' ) return false;
        var type = ( el.type || 'text' ).toLowerCase();
        if ( type !== 'text' && type !== 'search' ) return false;
        if ( el.closest && el.closest( '.upload-widget' ) ) return false;
        if ( /(^|\s)upload-path-input(\s|$)/.test( el.className || '' ) ) return false;

        var placeholder = el.placeholder || '';
        if ( /\.(pdf|docx?|xlsx?|pptx?|csv|zip|rar|txt)(\b|$)/i.test( placeholder ) ) return false;

        var hint = [ el.id, el.name, el.getAttribute( 'data-field' ), placeholder ].filter( Boolean ).join( ' ' );
        if ( /(^|[^a-z0-9])(image|images|img|photo|logo|thumbnail|banner|picture|avatar|poster)([^a-z0-9]|$)/i.test( hint ) ) return true;
        if ( /(assets|uploads)[\\\/]images/i.test( hint ) ) return true;
        if ( /\.(jpe?g|png|webp|gif|avif|svg)(#.*)?$/i.test( ( el.value || '' ).trim() ) ) return true;

        return false;
    }

    function attachInputFocal( input ) {
        if ( !input || input.dataset.wrcFocalBound === '1' ) return;
        if ( !looksLikeImageInput( input ) ) return;
        input.dataset.wrcFocalBound = '1';

        var btn = document.createElement( 'button' );
        btn.type = 'button';
        btn.className = 'wrc-focal-btn wrc-focal-btn-inline';
        btn.innerHTML = '<i class="fa-solid fa-crosshairs"></i><span>Adjust focus</span>';
        input.insertAdjacentElement( 'afterend', btn );

        var sync = function () {
            btn.style.display = splitFocal( input.value ).path ? '' : 'none';
        };

        input.addEventListener( 'input', sync );
        input.addEventListener( 'change', sync );
        btn.addEventListener( 'click', function ( e ) {
            e.preventDefault();
            e.stopPropagation();
            openFocal( {
                value: input.value,
                pathInput: input,
                baseUrl: input.dataset.baseUrl || BASE_URL,
                onChange: sync
            } );
        } );
        sync();
    }

    /* ============================================================
       Bind now, and keep binding whatever the screens add later
       ( the About page item editor rebuilds its cards on the fly )
       ============================================================ */

    function scan( root ) {
        if ( !root || root.nodeType !== 1 || !root.querySelectorAll ) return;
        if ( root.matches && root.matches( '.upload-widget' ) ) attachWidgetFocal( root );
        if ( root.matches && root.matches( 'input' ) ) attachInputFocal( root );
        root.querySelectorAll( '.upload-widget' ).forEach( attachWidgetFocal );
        root.querySelectorAll( 'input' ).forEach( attachInputFocal );
        var owner = root.closest ? root.closest( '.upload-widget' ) : null;
        if ( owner && typeof owner._wrcFocalSync === 'function' ) owner._wrcFocalSync();
    }

    function start() {
        scan( document.body );
        if ( window.MutationObserver ) {
            new MutationObserver( function ( mutations ) {
                for ( var i = 0; i < mutations.length; i++ ) {
                    var nodes = mutations[ i ].addedNodes;
                    for ( var j = 0; j < nodes.length; j++ ) {
                        var node = nodes[ j ];
                        if ( node.nodeType !== 1 ) continue;
                        if ( node.tagName === 'SCRIPT' || node.tagName === 'STYLE' ) continue;
                        scan( node );
                    }
                }
            } ).observe( document.body, { childList: true, subtree: true } );
        }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', start );
    } else {
        start();
    }
} )();
</script>
