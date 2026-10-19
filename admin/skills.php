<?php
$pageTitle='Kelola skills';
require __DIR__.'/_header.php';$notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(($_POST['action']??'')==='delete'){$st=$pdo->prepare('DELETE FROM skills WHERE id=?');$st->execute([(int)$_POST['id']]);$notice='Skill dihapus.';}
 else{$name=trim($_POST['name']??'');$cat=trim($_POST['category']??'Tools');$level=max(0,min(100,(int)($_POST['level']??75)));$order=(int)($_POST['sort_order']??0);if($name!==''){$st=$pdo->prepare('INSERT INTO skills(name,category,level,sort_order) VALUES(?,?,?,?)');$st->execute([$name,$cat,$level,$order]);$notice='Skill ditambahkan.';}}
}
$items=$pdo->query('SELECT * FROM skills ORDER BY sort_order,name')->fetchAll();
?>
<?php if($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
<div class="panel"><h2>Tambah skill</h2><form method="post" class="admin-form"><div class="form-grid"><label>Nama skill<input class="admin-input" name="name" required placeholder="Contoh: PHP"></label><label>Kategori<input class="admin-input" name="category" value="Tools"></label><label>Level (0–100)<input class="admin-input" type="number" min="0" max="100" name="level" value="75"></label><label>Urutan<input class="admin-input" type="number" name="sort_order" value="0"></label></div><button class="admin-btn">Tambah skill</button></form></div>
<div class="panel"><h2>Skills saat ini</h2><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Kategori</th><th>Level</th><th>Aksi</th></tr></thead><tbody><?php foreach($items as $it): ?><tr><td><?= e($it['name']) ?></td><td><?= e($it['category']) ?></td><td><?= (int)$it['level'] ?>%</td><td><form method="post" onsubmit="return confirm('Hapus skill ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>"><button class="admin-btn danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/_footer.php'; ?>
