<?php
$pageTitle='Editor artikel';
require __DIR__.'/_header.php';$id=(int)($_GET['id']??0);$post=null;if($id){$st=$pdo->prepare('SELECT * FROM posts WHERE id=?');$st->execute([$id]);$post=$st->fetch();}
$notice='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $title=trim($_POST['title']??'');$slug=slugify(trim($_POST['slug']??$title));$excerpt=trim($_POST['excerpt']??'');$content=trim($_POST['content']??'');$status=($_POST['status']??'draft')==='published'?'published':'draft';
 if($title==='')$error='Judul wajib diisi.';
 else{
  if($id){$st=$pdo->prepare('UPDATE posts SET title=?,slug=?,excerpt=?,content=?,status=?,published_at=IF(?="published",COALESCE(published_at,NOW()),NULL) WHERE id=?');$st->execute([$title,$slug,$excerpt,$content,$status,$status,$id]);}
  else{$st=$pdo->prepare('INSERT INTO posts(title,slug,excerpt,content,status,published_at) VALUES(?,?,?,?,?,IF(?="published",NOW(),NULL))');$st->execute([$title,$slug,$excerpt,$content,$status,$status]);$id=(int)$pdo->lastInsertId();}
  $notice='Artikel tersimpan.';$st=$pdo->prepare('SELECT * FROM posts WHERE id=?');$st->execute([$id]);$post=$st->fetch();
 }
}
?>
<?php if($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?><?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form class="panel admin-form" method="post"><label>Judul artikel<input class="admin-input" name="title" required value="<?= e($post['title']??'') ?>"></label><label>Slug URL<input class="admin-input" name="slug" value="<?= e($post['slug']??'') ?>" placeholder="otomatis-dari-judul-jika-kosong"></label><label>Ringkasan<textarea class="admin-input" name="excerpt"><?= e($post['excerpt']??'') ?></textarea></label><label>Isi artikel<textarea class="admin-input" name="content" style="min-height:260px"><?= e($post['content']??'') ?></textarea></label><label>Status<select class="admin-input" name="status"><option value="draft" <?= ($post['status']??'draft')==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= ($post['status']??'')==='published'?'selected':'' ?>>Published</option></select></label><div><button class="admin-btn">Simpan artikel</button> <a class="admin-btn secondary" href="posts.php">Kembali</a></div></form>
<?php require __DIR__.'/_footer.php'; ?>
