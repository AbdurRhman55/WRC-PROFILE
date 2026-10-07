<?php
$currentPage = basename( $_SERVER[ 'PHP_SELF' ] );
$adminUser = getAdminUser();
$adminName = $adminUser[ 'full_name' ] ?? ( $_SESSION[ 'admin_name' ] ?? 'Admin' );
$adminRole = $adminUser[ 'role' ] ?? ( $_SESSION[ 'admin_role' ] ?? 'admin' );
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
<img src = '../../assets/images/logo.png' alt = 'WRC logo' class = 'w-8 h-8 object-contain rounded-lg' />
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
                                        document.addEventListener( 'DOMContentLoaded', function() {
                                            document.querySelectorAll( '.upload-widget' ).forEach( function( widget ) {
                                                var type = widget.dataset.type || 'image';
                                                var baseUrl = widget.dataset.baseUrl || '/';
                                                var input = widget.querySelector( 'input[type=file]' );
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
                                                        var imageUrl = /^( https?: )?\/\//i.test( path )?path:baseUrl+path.replace( /^\/+/, '' );
                                                        var html = '<div class="upload-preview">';
                                                        if ( type === 'image' ) {
                                                            html += '<img src="'+imageUrl.replace( /&/g, '&amp;' ).replace( /"/g,'&quot;')+'" alt = 'Preview'>';}
        else{html+='<div class = 'bg-white border border-slate-200 rounded-lg p-3 text-xs text-slate-600'><i class = 'fa-solid fa-file-pdf text-red-400 mr-1'></i>'+path.split('/').pop()+'</div>';}
        html+='<button type = 'button' class = 'upload-remove' title = 'Remove'>&times;
                                                            </button></div>';
        zone.innerHTML=html;
        zone.querySelector('.upload-remove').addEventListener('click',function(e){
          e.stopPropagation();
          if(pathInput){pathInput.value=''}
          zone.innerHTML='<div class = 'upload-placeholder'><i class = 'fa-solid fa-cloud-arrow-up'></i><p>Click or drag to upload</p></div>';
                                                        }
                                                    );
                                                }

                                                // Show existing image on load
                                                if ( pathInput && pathInput.value ) {
                                                    var v = pathInput.value.trim();
                                                    if ( v ) {
                                                        showPreview( zone, v, type );
                                                    }
                                                }
                                            }
                                        );
                                    }
                                );
                                </script>
