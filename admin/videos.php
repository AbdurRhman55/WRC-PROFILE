<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Video Gallery';

if ( isPost() && isset( $_POST[ 'delete_id' ] ) ) {
    if ( !verifyCSRF( $_POST[ 'csrf_token' ] ?? '' ) ) {
        redirect( 'videos.php' );
    }
    global $pdo;
    $videoId = ( int ) $_POST[ 'delete_id' ];
    $video = getRow( 'video_gallery', [ 'id' => $videoId ] );
    if ( $video && !empty( $video[ 'thumbnail_path' ] ) && file_exists( __DIR__ . '/../' . $video[ 'thumbnail_path' ] ) ) {
        unlink( __DIR__ . '/../' . $video[ 'thumbnail_path' ] );
    }
    $pdo->prepare( 'DELETE FROM video_gallery WHERE id = ?' )->execute( [ $videoId ] );
    setFlash( 'success', 'Video deleted.' );
    redirect( 'videos.php' );
}

$videos = getRows( 'video_gallery', [], 'sort_order ASC' );

include __DIR__ . '/includes/header.php';
?>

<div class = 'flex items-center justify-between mb-8'>
<div>
<h1 class = 'text-2xl font-bold text-slate-900'>Video Gallery</h1>
<p class = 'text-sm text-slate-500 mt-1'>Manage video content for the news page</p>
</div>
<a href = 'video-edit.php' class = 'inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors'>
<i class = 'fa-solid fa-plus text-xs'></i> Add Video
</a>
</div>

<?php if ( $flash = getFlash() ): ?>
<div class = "mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash[ 'message' ] ?></div>
<?php endif;
?>

<div class = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>
<?php if ( empty( $videos ) ): ?>
<div class = 'col-span-full text-center py-16'>
<i class = 'fa-solid fa-video text-4xl text-slate-200 mb-4'></i>
<p class = 'text-slate-400 text-sm'>No videos yet.</p>
<a href = 'video-edit.php' class = 'mt-3 inline-block text-sm text-brandRed font-semibold hover:underline'>Add your first video &rarr;
</a>
</div>
<?php else: ?>
<?php foreach ( $videos as $vid ): ?>
<div class = 'bg-white rounded-2xl border border-slate-200 overflow-hidden group'>
<div class = 'aspect-video bg-slate-100 overflow-hidden relative'>
<?php if ( $vid[ 'thumbnail_path' ] ): ?>
<img src = "<?= htmlspecialchars(getImageUrl($vid['thumbnail_path']), ENT_QUOTES, 'UTF-8') ?>" alt = '' class = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'>
<?php else: ?>
<div class = 'w-full h-full flex items-center justify-center'><i class = 'fa-solid fa-play-circle text-4xl text-slate-300'></i></div>
<?php endif;
?>
<div class = 'absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity'>
<div class = 'w-12 h-12 rounded-full bg-white/90 flex items-center justify-center'><i class = 'fa-solid fa-play text-brandRed ml-0.5'></i></div>
</div>
<?php if ( $vid[ 'duration' ] ): ?>
<span class = 'absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-semibold px-2 py-0.5 rounded'><?= htmlspecialchars( $vid[ 'duration' ] ) ?></span>
<?php endif;
?>
</div>
<div class = 'p-4'>
<p class = 'text-sm font-semibold text-slate-900'><?= htmlspecialchars( $vid[ 'title' ] ) ?></p>
<p class = 'text-xs text-slate-400 mt-1 line-clamp-2'><?= htmlspecialchars( $vid[ 'description' ] ?? '' ) ?></p>
<div class = 'flex items-center justify-end gap-2 mt-3'>
<a href = "video-edit.php?id=<?= $vid['id'] ?>" class = 'px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors'>Edit</a>
<form method = 'POST' onsubmit = "return confirm('Delete this video?')">
<input type = 'hidden' name = 'csrf_token' value = '<?= generateCSRF() ?>'>
<input type = 'hidden' name = 'delete_id' value = "<?= $vid['id'] ?>">
<button class = 'px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors'>Delete</button>
</form>
</div>
</div>
</div>
<?php endforeach;
?>
<?php endif;
?>
</div>

<?php include __DIR__ . '/includes/footer.php';
?>
