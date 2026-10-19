<?php
$pageTitle='Kelola project';
require __DIR__.'/_header.php';
$notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if($action==='delete'){ $stmt=$pdo->prepare('DELETE FROM projects WHERE id=?');$stmt->execute([(int)($_POST['id']??0)]);$notice='Project dihapus.'; }
    if($action==='save'){
        $id=(int)($_POST['id']??0);$title=trim($_POST['title']??'');$category=trim($_POST['category']??'');$description=trim($_POST['description']??'');$demo=trim($_POST['demo_url']??'');$repo=trim($_POST['repo_url']??'');$featured=isset($_POST['featured'])?1:0;
        if($title!==''){
            if($id){$stmt=$pdo->prepare('UPDATE projects SET title=?,category=?,description=?,demo_url=?,repo_url=?,featured=? WHERE id=?');$stmt->execute([$title,$category,$description,$demo,$repo,$featured,$id]);}
            else{$stmt=$pdo->prepare('INSERT INTO projects(title,category,description,demo_url,repo_url,featured) VALUES(?,?,?,?,?,?)');$stmt->execute([$title,$category,$description,$demo,$repo,$featured]);}
            $notice='Project berhasil disimpan.';
        }
    }
}
$edit=null;if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM projects WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$items=$pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();
?>
<?php if($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
<div class="panel"><h2><?= $edit?'Edit project':'Tambah project' ?></h2><form class="admin-form" method="post"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><div class="form-grid">
<label>Nama project<input class="admin-input" name="title" required value="<?= e($edit['title']??'') ?>"></label><label>Kategori / teknologi<input class="admin-input" name="category" value="<?= e($edit['category']??'Web Development') ?>"></label>
<label class="full">Deskripsi<textarea class="admin-input" name="description"><?= e($edit['description']??'') ?></textarea></label><label>URL demo<input class="admin-input" type="url" name="demo_url" value="<?= e($edit['demo_url']??'') ?>" placeholder="https://..."></label><label>URL source code<input class="admin-input" type="url" name="repo_url" value="<?= e($edit['repo_url']??'') ?>" placeholder="https://github.com/..."></label>
<label class="full"><span><input type="checkbox" name="featured" <?= !empty($edit['featured'])?'checked':'' ?>> Tandai sebagai project pilihan</span></label></div><button class="admin-btn">Simpan project</button><?php if($edit): ?> <a class="admin-btn secondary" href="projects.php">Batal edit</a><?php endif; ?></form></div>
<div class="panel"><h2>Daftar project</h2><div class="table-wrap"><table><thead><tr><th>Project</th><th>Kategori</th><th>Featured</th><th>Aksi</th></tr></thead><tbody><?php foreach($items as $it): ?><tr><td><strong><?= e($it['title']) ?></strong><br><span class="muted"><?= e($it['description']) ?></span></td><td><?= e($it['category']) ?></td><td><?= $it['featured']?'Ya':'—' ?></td><td><a class="admin-btn secondary" href="?edit=<?= (int)$it['id'] ?>">Edit</a><form method="post" style="display:inline" onsubmit="return confirm('Hapus project ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>"><button class="admin-btn danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/_footer.php'; ?>
