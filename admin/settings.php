<?php
$pageTitle='Tampilan & konten';
require __DIR__.'/_header.php';
$keys=['name','role','hero_title','hero_description','about_title','about_text','email','location','github','instagram','linkedin','accent_color','availability','footer_text'];
$labels=['Nama / brand','Profesi / headline kecil','Judul utama beranda','Deskripsi beranda','Judul bagian tentang','Tentang saya','Email kontak','Lokasi','URL GitHub','URL Instagram','URL LinkedIn','Warna aksen (hex)','Status ketersediaan','Teks footer'];
$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    foreach($keys as $key){
        $value=trim($_POST[$key]??'');
        if($key==='accent_color' && !preg_match('/^#[0-9a-fA-F]{6}$/',$value)) $value='#5b6cff';
        save_setting($pdo,$key,$value);
    }
    if(!empty($_FILES['profile_image']['name']) && is_uploaded_file($_FILES['profile_image']['tmp_name'])){
        $f=$_FILES['profile_image']; $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if(isset($allowed[$mime]) && $f['size']<=3*1024*1024){
            if(!is_dir(__DIR__.'/../uploads')) mkdir(__DIR__.'/../uploads',0755,true);
            $filename='profile_'.bin2hex(random_bytes(6)).'.'.$allowed[$mime];
            if(move_uploaded_file($f['tmp_name'],__DIR__.'/../uploads/'.$filename)) save_setting($pdo,'profile_image',$filename);
        }
    }
    $success='Perubahan berhasil disimpan.';
}
?>
<?php if($success): ?><div class="notice"><?= e($success) ?></div><?php endif; ?>
<form class="panel admin-form" method="post" enctype="multipart/form-data">
<h2>Konten utama</h2><div class="form-grid">
<?php foreach($keys as $i=>$key): $long=in_array($key,['hero_title','hero_description','about_text','footer_text']); ?>
<label class="<?= $long?'full':'' ?>"><?= e($labels[$i]) ?>
<?php if(in_array($key,['hero_description','about_text','footer_text'])): ?><textarea class="admin-input" name="<?= e($key) ?>"><?= e(setting($pdo,$key)) ?></textarea>
<?php else: ?><input class="admin-input" name="<?= e($key) ?>" value="<?= e(setting($pdo,$key)) ?>" <?= $key==='accent_color'?'pattern="^#[0-9a-fA-F]{6}$"':'' ?> required><?php endif; ?></label>
<?php endforeach; ?>
<label class="full">Foto profil (JPG, PNG, WEBP · maks. 3 MB)<input class="admin-input" type="file" name="profile_image" accept="image/jpeg,image/png,image/webp"></label>
</div><button class="admin-btn" type="submit">Simpan perubahan →</button></form>
<?php require __DIR__.'/_footer.php'; ?>
