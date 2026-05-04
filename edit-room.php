<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// Fetch room — only owner can edit
$result = mysqli_query($conn, "SELECT * FROM rooms WHERE id=$id AND user_id=$user_id");
if(!$result || mysqli_num_rows($result) == 0){
    echo "<script>alert('Room not found or access denied!'); window.location='dashboard.php';</script>";
    exit;
}
$row = mysqli_fetch_assoc($result);

$success = '';
$error   = '';

if(isset($_POST['update'])){
    $title   = mysqli_real_escape_string($conn, trim($_POST['title']));
    $city    = mysqli_real_escape_string($conn, trim($_POST['city']));
    $price   = (int)$_POST['price'];
    $type    = mysqli_real_escape_string($conn, trim($_POST['room_type']));
    $desc    = mysqli_real_escape_string($conn, trim($_POST['description']));
    $contact = mysqli_real_escape_string($conn, trim($_POST['contact_no']));

    // ── IMAGE HANDLING ──
    $existing_images = array_values(array_filter(array_map('trim', explode(",", $row['image'] ?? ''))));

    // Remove images marked for deletion
    $remove_imgs = isset($_POST['remove_images']) ? (array)$_POST['remove_images'] : [];
    $existing_images = array_values(array_filter($existing_images, fn($f) => !in_array($f, $remove_imgs)));

    // Upload new images
    if(!empty($_FILES['new_images']['name'][0])){
        $allowed = ['jpg','jpeg','png','webp','gif'];
        foreach($_FILES['new_images']['tmp_name'] as $k => $tmp){
            if($_FILES['new_images']['error'][$k] !== 0) continue;
            $orig = $_FILES['new_images']['name'][$k];
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if(!in_array($ext, $allowed)) continue;
            $fname = time().'_'.mt_rand(1000,9999).'.'.$ext;
            $dest  = 'uploads/'.$fname;
            if(move_uploaded_file($tmp, $dest)){
                $existing_images[] = $dest;
            }
        }
    }
    $new_image = implode(',', $existing_images);

    // ── VIDEO HANDLING ──
    $existing_video = trim($row['video'] ?? '');

    // Remove video if checked
    if(isset($_POST['remove_video'])){
        $existing_video = '';
    }

    // Upload new video
    if(!empty($_FILES['new_video']['name']) && $_FILES['new_video']['error'] === 0){
        $vext  = strtolower(pathinfo($_FILES['new_video']['name'], PATHINFO_EXTENSION));
        $vname = time().'_'.mt_rand(1000,9999).'.'.$vext;
        $vdest = 'uploads/'.$vname;
        if(move_uploaded_file($_FILES['new_video']['tmp_name'], $vdest)){
            $existing_video = $vdest;
        }
    }

    mysqli_query($conn, "
        UPDATE rooms SET
        title='$title',
        city='$city',
        price='$price',
        room_type='$type',
        description='$desc',
        contact_no='$contact',
        image='$new_image',
        video='$existing_video'
        WHERE id=$id AND user_id=$user_id
    ");

    if(mysqli_affected_rows($conn) >= 0){
        // Refresh row
        $result2 = mysqli_query($conn,"SELECT * FROM rooms WHERE id=$id");
        $row = mysqli_fetch_assoc($result2);
        $success = 'Room updated successfully!';
    } else {
        $error = 'Update failed. Please try again.';
    }
}

// Parse current media
$cur_images = array_values(array_filter(array_map('trim', explode(",", $row['image'] ?? ''))));
$cur_video  = trim($row['video'] ?? '');
$base_url   = 'https://landlink.gt.tc/';

function abs_url($f, $base){
    $f = trim($f);
    if(!$f) return '';
    if(strpos($f,'http')===0) return $f;
    if(strpos($f,'uploads/')===0) return $base.$f;
    return $base.'uploads/'.basename($f);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Room — p2mdestiny</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">

<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

:root{
  --bg:      #08080e;
  --surf:    #0e0e18;
  --card:    #111120;
  --border:  #1d1d30;
  --border-l:#2a2a42;
  --accent:  #6d5afc;
  --accent-l:#9b8fff;
  --gold:    #f0b429;
  --rose:    #f06292;
  --mint:    #26c6a0;
  --danger:  #ef5350;
  --success: #26c6a0;
  --text:    #f0f0fa;
  --text-2:  #8888a8;
  --text-3:  #484860;
  --ff-head: 'Syne', sans-serif;
  --ff-body: 'DM Sans', sans-serif;
  --ease:    cubic-bezier(0.4,0,0.2,1);
}

html{scroll-behavior:smooth;}
body{
  font-family:var(--ff-body);
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  overflow-x:hidden;
}

/* grain */
body::before{
  content:'';position:fixed;inset:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  pointer-events:none;z-index:999;opacity:.45;
}

/* orbs */
.orb{position:fixed;border-radius:50%;filter:blur(140px);pointer-events:none;z-index:0;}
.orb-a{width:600px;height:600px;background:rgba(109,90,252,0.1);top:-200px;right:-200px;}
.orb-b{width:400px;height:400px;background:rgba(240,98,146,0.06);bottom:-150px;left:-150px;}

/* ── NAV ── */
nav{
  position:sticky;top:0;z-index:200;
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 48px;
  background:rgba(8,8,14,.88);
  backdrop-filter:blur(24px);
  border-bottom:1px solid var(--border);
}
.nav-logo{
  font-family:var(--ff-head);font-size:1.2rem;font-weight:800;
  background:linear-gradient(120deg,var(--accent-l),var(--mint));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.nav-back{
  display:flex;align-items:center;gap:8px;
  padding:8px 18px;border-radius:100px;
  color:var(--text-2);text-decoration:none;font-size:.85rem;font-weight:500;
  border:1px solid var(--border-l);
  transition:all .25s var(--ease);
}
.nav-back:hover{color:var(--text);background:rgba(255,255,255,.05);}

/* ── LAYOUT ── */
.page{
  position:relative;z-index:1;
  max-width:900px;margin:0 auto;
  padding:48px 24px 80px;
  animation:fadeUp .5s var(--ease) both;
}

/* ── PAGE HEADER ── */
.page-header{margin-bottom:40px;}
.page-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  padding:5px 14px;border-radius:100px;
  background:rgba(109,90,252,.12);border:1px solid rgba(109,90,252,.25);
  font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:var(--accent-l);margin-bottom:14px;
}
.page-title{
  font-family:var(--ff-head);
  font-size:clamp(1.8rem,4vw,2.8rem);
  font-weight:800;letter-spacing:-.03em;line-height:1.1;
}
.page-title span{color:var(--accent-l);}
.page-sub{font-size:.9rem;color:var(--text-2);margin-top:8px;font-weight:300;}

/* ── ALERTS ── */
.alert{
  display:flex;align-items:center;gap:10px;
  padding:14px 18px;border-radius:12px;
  font-size:.88rem;font-weight:500;margin-bottom:28px;
}
.alert-success{background:rgba(38,198,160,.1);border:1px solid rgba(38,198,160,.3);color:var(--success);}
.alert-error  {background:rgba(239,83,80,.1); border:1px solid rgba(239,83,80,.3); color:var(--danger);}

/* ── FORM CARD ── */
.form-card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:20px;
  overflow:hidden;
}

.form-section{padding:28px 32px;border-bottom:1px solid var(--border);}
.form-section:last-child{border-bottom:none;}

.section-title{
  font-family:var(--ff-head);
  font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:var(--text-3);margin-bottom:20px;
  display:flex;align-items:center;gap:10px;
}
.section-title::after{content:'';flex:1;height:1px;background:var(--border);}

/* ── GRID ── */
.fields-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.field-full{grid-column:1/-1;}
@media(max-width:600px){.fields-grid{grid-template-columns:1fr;}}

/* ── FIELD ── */
.field{display:flex;flex-direction:column;gap:8px;}
.field-label{
  font-size:.78rem;font-weight:600;color:var(--text-2);
  display:flex;align-items:center;gap:6px;
}
.field-label .req{color:var(--rose);font-size:.9em;}

.field-input,
.field-select,
.field-textarea{
  padding:12px 16px;
  background:var(--surf);
  border:1px solid var(--border-l);
  border-radius:10px;
  color:var(--text);
  font-family:var(--ff-body);
  font-size:.9rem;
  outline:none;
  transition:border-color .25s var(--ease),box-shadow .25s var(--ease);
  width:100%;
}
.field-input:focus,
.field-select:focus,
.field-textarea:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px rgba(109,90,252,.15);
}
.field-input::placeholder,
.field-textarea::placeholder{color:var(--text-3);}
.field-select option{background:var(--surf);}
.field-textarea{resize:vertical;min-height:110px;line-height:1.6;}

/* price prefix */
.price-wrap{position:relative;}
.price-prefix{
  position:absolute;left:14px;top:50%;transform:translateY(-50%);
  color:var(--gold);font-weight:700;font-size:1rem;
}
.price-wrap .field-input{padding-left:30px;}

/* ── MEDIA SECTION ── */

/* Current images grid */
.images-grid{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;}
.img-thumb{
  position:relative;width:100px;height:84px;
  border-radius:10px;overflow:hidden;
  border:2px solid var(--border-l);
}
.img-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
.img-thumb .remove-overlay{
  position:absolute;inset:0;
  background:rgba(239,83,80,.85);
  display:flex;align-items:center;justify-content:center;
  opacity:0;transition:opacity .2s;
  font-size:1.4rem;cursor:pointer;
}
.img-thumb.marked .remove-overlay{opacity:1;}
.img-thumb .remove-overlay:hover{opacity:1;}
.img-thumb input[type=checkbox]{display:none;}

/* video thumb */
.video-thumb{
  position:relative;
  display:inline-flex;align-items:center;gap:10px;
  padding:10px 16px;
  background:var(--surf);border:1px solid var(--border-l);border-radius:10px;
  font-size:.82rem;color:var(--text-2);
  margin-bottom:16px;
}
.video-thumb .vname{max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.remove-video-btn{
  display:flex;align-items:center;gap:6px;
  padding:5px 12px;border-radius:100px;
  background:rgba(239,83,80,.1);border:1px solid rgba(239,83,80,.25);
  color:var(--danger);font-size:.75rem;font-weight:600;cursor:pointer;
  transition:all .2s;font-family:var(--ff-body);
}
.remove-video-btn:hover{background:var(--danger);color:#fff;}
.remove-video-btn.active{background:var(--danger);color:#fff;}

/* Upload zones */
.upload-zone{
  border:2px dashed var(--border-l);
  border-radius:12px;
  padding:24px;
  text-align:center;
  cursor:pointer;
  transition:all .25s var(--ease);
  position:relative;
}
.upload-zone:hover,.upload-zone.drag{
  border-color:var(--accent);
  background:rgba(109,90,252,.05);
}
.upload-zone input[type=file]{
  position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;
}
.uz-icon{font-size:1.8rem;margin-bottom:8px;}
.uz-title{font-size:.88rem;font-weight:600;color:var(--text-2);margin-bottom:4px;}
.uz-sub{font-size:.75rem;color:var(--text-3);}

/* new previews */
.new-previews{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px;}
.preview-thumb{
  width:80px;height:68px;border-radius:8px;overflow:hidden;
  border:2px solid rgba(109,90,252,.4);position:relative;
}
.preview-thumb img,.preview-thumb video{width:100%;height:100%;object-fit:cover;}
.preview-thumb .rm{
  position:absolute;top:2px;right:2px;
  width:18px;height:18px;border-radius:50%;
  background:var(--danger);color:#fff;
  font-size:.7rem;display:flex;align-items:center;justify-content:center;
  cursor:pointer;line-height:1;
}

/* ── FOOTER / SUBMIT ── */
.form-footer{
  padding:24px 32px;
  display:flex;align-items:center;justify-content:space-between;gap:16px;
  flex-wrap:wrap;
}
.btn-cancel{
  padding:12px 24px;border-radius:100px;
  color:var(--text-2);text-decoration:none;
  font-size:.88rem;font-weight:600;
  border:1px solid var(--border-l);
  transition:all .25s var(--ease);font-family:var(--ff-body);
  background:transparent;cursor:pointer;
}
.btn-cancel:hover{color:var(--text);background:rgba(255,255,255,.05);}
.btn-update{
  display:flex;align-items:center;gap:10px;
  padding:14px 36px;border-radius:100px;
  background:linear-gradient(135deg,var(--accent),#a06cf0);
  color:#fff;font-family:var(--ff-body);font-size:.95rem;font-weight:700;
  border:none;cursor:pointer;
  box-shadow:0 6px 24px rgba(109,90,252,.4);
  transition:all .3s var(--ease);
}
.btn-update:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(109,90,252,.55);}
.btn-update:active{transform:translateY(0);}

/* spinner */
.spinner{
  width:18px;height:18px;border-radius:50%;
  border:2px solid rgba(255,255,255,.3);
  border-top-color:#fff;
  animation:spin .6s linear infinite;
  display:none;
}
.btn-update.loading .spinner{display:block;}
.btn-update.loading .btn-label{opacity:.7;}

/* ── ANIM ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@keyframes spin{to{transform:rotate(360deg)}}

::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--border-l);border-radius:3px;}
::-webkit-scrollbar-thumb:hover{background:var(--accent);}

@media(max-width:600px){
  nav{padding:12px 16px;}
  .form-section{padding:20px 18px;}
  .form-footer{padding:18px;}
  .btn-update{width:100%;justify-content:center;}
}
</style>
</head>
<body>

<div class="orb orb-a"></div>
<div class="orb orb-b"></div>

<!-- NAV -->
<nav>
  <div class="nav-logo">🏠 p2mdestiny</div>
  <a href="dashboard.php" class="nav-back">← Dashboard</a>
</nav>

<!-- PAGE -->
<div class="page">

  <div class="page-header">
    <div class="page-eyebrow">✏️ Editing Room #<?php echo $id; ?></div>
    <h1 class="page-title">Update Your <span>Listing</span></h1>
    <p class="page-sub">Make changes to your room details, photos, or video below.</p>
  </div>

  <?php if($success): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?> <a href="room-details.php?id=<?php echo $id; ?>" style="color:inherit;margin-left:auto;font-weight:700;">View →</a></div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="editForm">

    <div class="form-card">

      <!-- ── BASIC INFO ── -->
      <div class="form-section">
        <div class="section-title">Basic Information</div>
        <div class="fields-grid">

          <div class="field field-full">
            <label class="field-label">Room Title <span class="req">*</span></label>
            <input type="text" name="title" class="field-input"
                   value="<?php echo htmlspecialchars($row['title']); ?>"
                   placeholder="e.g. Cozy Single Room near Metro" required>
          </div>

          <div class="field">
            <label class="field-label">City <span class="req">*</span></label>
            <input type="text" name="city" class="field-input"
                   value="<?php echo htmlspecialchars($row['city']); ?>"
                   placeholder="e.g. Lucknow" required>
          </div>

          <div class="field">
            <label class="field-label">Room Type <span class="req">*</span></label>
            <select name="room_type" class="field-select">
              <?php foreach(['Single','Double','PG','Shared'] as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo $row['room_type']==$t?'selected':''; ?>><?php echo $t; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label class="field-label">Monthly Rent <span class="req">*</span></label>
            <div class="price-wrap">
              <span class="price-prefix">₹</span>
              <input type="number" name="price" class="field-input"
                     value="<?php echo htmlspecialchars($row['price']); ?>"
                     placeholder="5000" required>
            </div>
          </div>

          <div class="field">
            <label class="field-label">Contact Number <span class="req">*</span></label>
            <input type="text" name="contact_no" class="field-input"
                   value="<?php echo htmlspecialchars($row['contact_no']); ?>"
                   placeholder="10-digit mobile number" required>
          </div>

          <div class="field field-full">
            <label class="field-label">Description</label>
            <textarea name="description" class="field-textarea"
                      placeholder="Describe the room, amenities, nearby places…"><?php echo htmlspecialchars($row['description']); ?></textarea>
          </div>

        </div>
      </div>

      <!-- ── IMAGES ── -->
      <div class="form-section">
        <div class="section-title">Photos</div>

        <?php if(!empty($cur_images)): ?>
        <div style="font-size:.78rem;color:var(--text-3);margin-bottom:10px;">Click a photo to mark for removal</div>
        <div class="images-grid" id="curImgGrid">
          <?php foreach($cur_images as $img): if(!$img) continue; ?>
          <label class="img-thumb" title="Click to remove">
            <input type="checkbox" name="remove_images[]" value="<?php echo htmlspecialchars($img); ?>"
                   onchange="toggleMark(this)">
            <img src="<?php echo abs_url($img, $base_url); ?>" alt="">
            <div class="remove-overlay">🗑</div>
          </label>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- New image upload zone -->
        <div class="upload-zone" id="imgZone">
          <input type="file" name="new_images[]" id="newImgInput"
                 accept="image/*" multiple onchange="previewImages(this)">
          <div class="uz-icon">📷</div>
          <div class="uz-title">Add New Photos</div>
          <div class="uz-sub">JPG, PNG, WEBP — drag & drop or click</div>
        </div>
        <div class="new-previews" id="imgPreviews"></div>
      </div>

      <!-- ── VIDEO ── -->
      <div class="form-section">
        <div class="section-title">Video</div>

        <?php if(!empty($cur_video)): ?>
        <div class="video-thumb" id="curVideoWrap">
          <span>🎬</span>
          <span class="vname"><?php echo htmlspecialchars(basename($cur_video)); ?></span>
          <button type="button" class="remove-video-btn" id="removeVidBtn" onclick="toggleRemoveVideo()">
            🗑 Remove
          </button>
          <input type="checkbox" name="remove_video" id="removeVideoCheck" style="display:none">
        </div>
        <?php endif; ?>

        <div class="upload-zone" id="vidZone" <?php echo empty($cur_video)?'':'style="margin-top:0"'; ?>>
          <input type="file" name="new_video" id="newVidInput"
                 accept="video/*" onchange="previewVideo(this)">
          <div class="uz-icon">🎬</div>
          <div class="uz-title"><?php echo empty($cur_video) ? 'Upload Video' : 'Replace Video'; ?></div>
          <div class="uz-sub">MP4, WEBM — max recommended 50MB</div>
        </div>
        <div id="vidPreview" style="margin-top:12px;"></div>
      </div>

      <!-- ── FOOTER ── -->
      <div class="form-footer">
        <a href="dashboard.php" class="btn-cancel">Cancel</a>
        <button type="submit" name="update" class="btn-update" id="updateBtn">
          <div class="spinner"></div>
          <span class="btn-label">💾 Save Changes</span>
        </button>
      </div>

    </div>
  </form>
</div>

<script>
// Mark/unmark image for removal
function toggleMark(cb) {
  cb.closest('.img-thumb').classList.toggle('marked', cb.checked);
}

// Preview new images
function previewImages(input) {
  const wrap = document.getElementById('imgPreviews');
  [...input.files].forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'preview-thumb';
      div.innerHTML = `<img src="${e.target.result}"><span class="rm" title="Remove preview">✕</span>`;
      div.querySelector('.rm').onclick = () => div.remove();
      wrap.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}

// Preview new video
function previewVideo(input) {
  const wrap = document.getElementById('vidPreview');
  wrap.innerHTML = '';
  if(!input.files[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  wrap.innerHTML = `
    <div class="video-thumb">
      <span>🎬</span>
      <span class="vname">${input.files[0].name}</span>
      <span style="font-size:.75rem;color:var(--mint);font-weight:600;">New</span>
    </div>`;
}

// Toggle video removal
function toggleRemoveVideo() {
  const btn   = document.getElementById('removeVidBtn');
  const check = document.getElementById('removeVideoCheck');
  const isActive = btn.classList.toggle('active');
  check.checked = isActive;
  btn.textContent = isActive ? '↩ Undo' : '🗑 Remove';
}

// Drag over effect
['imgZone','vidZone'].forEach(id => {
  const el = document.getElementById(id);
  if(!el) return;
  el.addEventListener('dragover', e => { e.preventDefault(); el.classList.add('drag'); });
  el.addEventListener('dragleave', () => el.classList.remove('drag'));
  el.addEventListener('drop', () => el.classList.remove('drag'));
});

// Loading state on submit
document.getElementById('editForm').addEventListener('submit', () => {
  const btn = document.getElementById('updateBtn');
  btn.classList.add('loading');
  btn.querySelector('.btn-label').textContent = 'Saving…';
});
</script>
</body>
</html>