<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(isset($_POST['submit'])){

    $user_id = $_SESSION['user_id'];

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price = intval($_POST['price']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);

    $uploads_dir = "uploads/";
    if(!is_dir($uploads_dir)){
        mkdir($uploads_dir, 0777, true);
    }

    $file_name = "";

    if(!empty($_FILES['media']['name'][0])){
        foreach($_FILES['media']['tmp_name'] as $key => $tmp_name){
            $name = $_FILES['media']['name'][$key];
            $tmp  = $_FILES['media']['tmp_name'][$key];
            if(empty($name)) continue;
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg','jpeg','png','webp'])){
                $type_file = 'image';
            } elseif(in_array($ext, ['mp4','webm','ogg'])){
                $type_file = 'video';
            } else {
                continue;
            }
            $new_name = time().'_'.rand(1000,9999).'.'.$ext;
            $path = $uploads_dir . $new_name;
            if(move_uploaded_file($tmp, $path)){
                $file_name .= $path . ",";
            }
        }
    }

    if($file_name == ""){
        echo "<script>showToast('⚠️ Please upload at least one image or video', 'error');</script>";
    } else {
        $query = "INSERT INTO rooms 
        (title, city, room_type, price, description, contact_no, image, user_id)
        VALUES 
        ('$title','$city','$type','$price','$desc','$contact_no','$file_name','$user_id')";

        if(mysqli_query($conn, $query)){
            echo "<script>showToast('✅ Room Posted Successfully!', 'success'); setTimeout(()=>window.location='dashboard.php', 2000);</script>";
        } else {
            echo "<script>showToast('❌ Error: " . mysqli_error($conn) . "', 'error');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Your Room — RoomEase</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">

<style>
/* ── RESET & BASE ── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --bg:        #0a0a0f;
  --surface:   #111118;
  --card:      #16161f;
  --border:    #2a2a38;
  --border-glow: #5b4ef8;
  --accent:    #5b4ef8;
  --accent2:   #f85b8a;
  --accent3:   #5bf8c4;
  --text:      #f0f0f8;
  --muted:     #7a7a9a;
  --danger:    #f85b5b;
  --success:   #5bf8a0;
  --ff-head:   'Syne', sans-serif;
  --ff-body:   'DM Sans', sans-serif;
  --radius:    16px;
  --trans:     cubic-bezier(0.4, 0, 0.2, 1);
}

html { scroll-behavior: smooth; }

body {
  font-family: var(--ff-body);
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ── NOISE OVERLAY ── */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 0;
  opacity: 0.4;
}

/* ── AMBIENT GLOWS ── */
.glow-orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  z-index: 0;
}
.glow-orb.a { width: 600px; height: 600px; background: rgba(91,78,248,0.12); top: -200px; right: -200px; }
.glow-orb.b { width: 400px; height: 400px; background: rgba(248,91,138,0.08); bottom: -100px; left: -100px; }
.glow-orb.c { width: 300px; height: 300px; background: rgba(91,248,196,0.06); top: 50%; left: 50%; transform: translate(-50%,-50%); }

/* ── LAYOUT ── */
.wrapper {
  position: relative;
  z-index: 1;
  max-width: 720px;
  margin: 0 auto;
  padding: 40px 20px 80px;
}

/* ── HEADER ── */
.page-header {
  text-align: center;
  margin-bottom: 48px;
  animation: fadeUp 0.6s var(--trans) both;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 16px;
  background: rgba(91,78,248,0.15);
  border: 1px solid rgba(91,78,248,0.3);
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #a09bfb;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 16px;
}

.page-title {
  font-family: var(--ff-head);
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.02em;
}

.page-title span {
  background: linear-gradient(135deg, #5b4ef8, #f85b8a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-sub {
  margin-top: 10px;
  color: var(--muted);
  font-size: 0.95rem;
  font-weight: 300;
}

/* ── PROGRESS STEPS ── */
.steps {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  margin-bottom: 40px;
  animation: fadeUp 0.6s 0.1s var(--trans) both;
}
.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.step-dot {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--card);
  border: 2px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--ff-head);
  font-weight: 700;
  font-size: 0.85rem;
  color: var(--muted);
  transition: all 0.3s var(--trans);
}
.step.active .step-dot {
  background: var(--accent);
  border-color: var(--accent);
  color: white;
  box-shadow: 0 0 20px rgba(91,78,248,0.5);
}
.step.done .step-dot {
  background: var(--accent3);
  border-color: var(--accent3);
  color: #0a0a0f;
}
.step-label {
  font-size: 0.7rem;
  color: var(--muted);
  letter-spacing: 0.05em;
  text-transform: uppercase;
  font-weight: 500;
}
.step.active .step-label { color: var(--accent); }
.step-line {
  flex: 1;
  height: 2px;
  background: var(--border);
  max-width: 60px;
  margin: 0 8px;
  margin-bottom: 22px;
}

/* ── CARD SECTIONS ── */
.form-section {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 28px;
  margin-bottom: 20px;
  transition: border-color 0.3s var(--trans);
  animation: fadeUp 0.6s var(--trans) both;
}
.form-section:hover { border-color: rgba(91,78,248,0.3); }
.form-section:nth-child(2) { animation-delay: 0.1s; }
.form-section:nth-child(3) { animation-delay: 0.2s; }
.form-section:nth-child(4) { animation-delay: 0.3s; }

.section-title {
  font-family: var(--ff-head);
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.section-title::before {
  content: '';
  display: inline-block;
  width: 16px;
  height: 2px;
  background: var(--accent);
  border-radius: 2px;
}

/* ── GRID ── */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
@media(max-width: 500px) {
  .grid-2, .grid-3 { grid-template-columns: 1fr; }
}

/* ── FIELD ── */
.field { display: flex; flex-direction: column; gap: 6px; }

.field label {
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--muted);
  letter-spacing: 0.04em;
}

.field input,
.field select,
.field textarea {
  background: rgba(255,255,255,0.03);
  border: 1.5px solid var(--border);
  border-radius: 12px;
  padding: 13px 16px;
  color: var(--text);
  font-family: var(--ff-body);
  font-size: 0.93rem;
  outline: none;
  transition: border-color 0.25s var(--trans), box-shadow 0.25s var(--trans), background 0.25s var(--trans);
  width: 100%;
}

.field input::placeholder,
.field textarea::placeholder { color: var(--muted); opacity: 0.6; }

.field input:focus,
.field select:focus,
.field textarea:focus {
  border-color: var(--accent);
  background: rgba(91,78,248,0.06);
  box-shadow: 0 0 0 3px rgba(91,78,248,0.12);
}

.field select option { background: var(--surface); color: var(--text); }

.field textarea { resize: vertical; min-height: 110px; line-height: 1.6; }

/* price input with prefix */
.input-prefix {
  position: relative;
}
.input-prefix .prefix {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--accent);
  font-weight: 600;
  font-size: 1rem;
  pointer-events: none;
}
.input-prefix input { padding-left: 30px; }

/* ── UPLOAD ZONE ── */
.upload-zone {
  border: 2px dashed var(--border);
  border-radius: var(--radius);
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s var(--trans);
  position: relative;
  overflow: hidden;
}
.upload-zone:hover,
.upload-zone.drag-over {
  border-color: var(--accent);
  background: rgba(91,78,248,0.05);
}
.upload-zone.drag-over {
  transform: scale(1.01);
  box-shadow: 0 0 30px rgba(91,78,248,0.15);
}

.upload-icon {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, rgba(91,78,248,0.2), rgba(248,91,138,0.2));
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 14px;
  font-size: 1.5rem;
  transition: transform 0.3s var(--trans);
}
.upload-zone:hover .upload-icon { transform: translateY(-3px) scale(1.05); }

.upload-title {
  font-family: var(--ff-head);
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 6px;
}
.upload-sub {
  font-size: 0.8rem;
  color: var(--muted);
  margin-bottom: 16px;
}

.upload-btn-fake {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 20px;
  background: rgba(91,78,248,0.15);
  border: 1px solid rgba(91,78,248,0.3);
  border-radius: 100px;
  color: #a09bfb;
  font-size: 0.82rem;
  font-weight: 600;
  transition: all 0.25s var(--trans);
}
.upload-zone:hover .upload-btn-fake {
  background: var(--accent);
  border-color: var(--accent);
  color: white;
}

.upload-formats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
  flex-wrap: wrap;
}
.format-tag {
  padding: 3px 10px;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border);
  border-radius: 100px;
  font-size: 0.7rem;
  color: var(--muted);
}

#mediaInput { display: none; }

/* ── PREVIEW GRID ── */
.preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
  margin-top: 16px;
}

.preview-item {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  aspect-ratio: 1;
  background: var(--surface);
  border: 1px solid var(--border);
  animation: popIn 0.35s var(--trans) both;
  group: true;
}

.preview-item img,
.preview-item video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.preview-item .remove-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: rgba(0,0,0,0.7);
  border: 1px solid rgba(255,255,255,0.15);
  color: white;
  font-size: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s var(--trans), background 0.2s var(--trans);
  z-index: 2;
  backdrop-filter: blur(4px);
}
.preview-item:hover .remove-btn {
  opacity: 1;
}
.preview-item .remove-btn:hover {
  background: var(--danger);
}

.preview-item .type-badge {
  position: absolute;
  bottom: 6px;
  left: 6px;
  padding: 2px 8px;
  border-radius: 100px;
  font-size: 0.65rem;
  font-weight: 600;
  backdrop-filter: blur(8px);
}
.type-badge.img { background: rgba(91,78,248,0.8); color: white; }
.type-badge.vid { background: rgba(248,91,138,0.8); color: white; }

.preview-item .overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 50%);
  pointer-events: none;
}

/* file count */
.file-count {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
  padding: 10px 14px;
  background: rgba(91,78,248,0.08);
  border: 1px solid rgba(91,78,248,0.2);
  border-radius: 10px;
  font-size: 0.82rem;
  color: #a09bfb;
  display: none;
}
.file-count.show { display: flex; }
.file-count strong { font-weight: 700; }

/* ── SUBMIT BUTTON ── */
.btn-submit {
  width: 100%;
  padding: 18px;
  background: linear-gradient(135deg, #5b4ef8, #f85b8a);
  border: none;
  border-radius: 14px;
  color: white;
  font-family: var(--ff-head);
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: transform 0.2s var(--trans), box-shadow 0.2s var(--trans), opacity 0.2s var(--trans);
  margin-top: 8px;
  animation: fadeUp 0.6s 0.4s var(--trans) both;
}
.btn-submit::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
  opacity: 0;
  transition: opacity 0.3s var(--trans);
}
.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(91,78,248,0.4), 0 4px 16px rgba(248,91,138,0.3);
}
.btn-submit:hover::before { opacity: 1; }
.btn-submit:active { transform: translateY(0); }
.btn-submit.loading { opacity: 0.7; pointer-events: none; }

.btn-submit .btn-inner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: none;
}
.btn-submit.loading .spinner { display: block; }
.btn-submit.loading .btn-text { opacity: 0.7; }

/* ── TOAST ── */
.toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.toast {
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 0.88rem;
  font-weight: 500;
  backdrop-filter: blur(16px);
  border: 1px solid;
  min-width: 260px;
  display: flex;
  align-items: center;
  gap: 10px;
  transform: translateX(120%);
  animation: toastIn 0.4s var(--trans) forwards;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}
.toast.out { animation: toastOut 0.3s var(--trans) forwards; }
.toast.success {
  background: rgba(91,248,160,0.1);
  border-color: rgba(91,248,160,0.3);
  color: var(--success);
}
.toast.error {
  background: rgba(248,91,91,0.1);
  border-color: rgba(248,91,91,0.3);
  color: var(--danger);
}

/* ── ANIMATIONS ── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes popIn {
  from { opacity: 0; transform: scale(0.85); }
  to   { opacity: 1; transform: scale(1); }
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
@keyframes toastIn {
  to { transform: translateX(0); }
}
@keyframes toastOut {
  to { transform: translateX(120%); opacity: 0; }
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--accent); }
</style>
</head>

<body>

<div class="toast-container" id="toastContainer"></div>

<div class="glow-orb a"></div>
<div class="glow-orb b"></div>
<div class="glow-orb c"></div>

<div class="wrapper">

  <!-- Header -->
  <div class="page-header">
    <div class="badge">🏠 Room Listing</div>
    <h1 class="page-title">Post Your <span>Room</span></h1>
    <p class="page-sub">Fill in the details below to list your property</p>
  </div>

  <!-- Steps -->
  <div class="steps">
    <div class="step active">
      <div class="step-dot">1</div>
      <span class="step-label">Details</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
      <div class="step-dot">2</div>
      <span class="step-label">Location</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
      <div class="step-dot">3</div>
      <span class="step-label">Media</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
      <div class="step-dot">4</div>
      <span class="step-label">Post</span>
    </div>
  </div>

  <form method="POST" enctype="multipart/form-data" id="roomForm">

    <!-- Section 1: Basic Info -->
    <div class="form-section">
      <div class="section-title">Basic Information</div>

      <div class="field" style="margin-bottom:14px;">
        <label>Room Title</label>
        <input type="text" name="title" placeholder="e.g. Spacious 2BHK near Metro Station" required>
      </div>

      <div class="grid-2">
        <div class="field">
          <label>Room Type</label>
          <select name="type" required>
            <option value="">Select Type</option>
            <option>Single</option>
            <option>Double</option>
            <option>PG</option>
          </select>
        </div>
        <div class="field">
          <label>Monthly Rent</label>
          <div class="input-prefix">
            <span class="prefix">₹</span>
            <input type="number" name="price" placeholder="8000" required>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Location & Contact -->
    <div class="form-section">
      <div class="section-title">Location & Contact</div>
      <div class="grid-2">
        <div class="field">
          <label>City</label>
          <input type="text" name="city" placeholder="e.g. Lucknow" required>
        </div>
        <div class="field">
          <label>Contact Number</label>
          <input type="text" name="contact_no" placeholder="9876543210" required>
        </div>
      </div>
    </div>

    <!-- Section 3: Description -->
    <div class="form-section">
      <div class="section-title">Description</div>
      <div class="field">
        <label>Tell tenants about this room</label>
        <textarea name="description" placeholder="Describe amenities, nearby places, rules, furnishing details…" required></textarea>
      </div>
    </div>

    <!-- Section 4: Media Upload -->
    <div class="form-section">
      <div class="section-title">Photos & Videos</div>

      <div class="upload-zone" id="uploadZone">
        <input type="file" name="media[]" id="mediaInput" multiple accept="image/*,video/*">
        <div class="upload-icon">📸</div>
        <div class="upload-title">Drag & Drop or Browse</div>
        <div class="upload-sub">Upload up to 10 files at once</div>
        <div class="upload-btn-fake">
          <span>📁</span> Choose Files
        </div>
        <div class="upload-formats">
          <span class="format-tag">JPG</span>
          <span class="format-tag">PNG</span>
          <span class="format-tag">WEBP</span>
          <span class="format-tag">MP4</span>
          <span class="format-tag">WEBM</span>
        </div>
      </div>

      <div class="file-count" id="fileCount">
        <span>🗂️ <strong id="countText">0 files</strong> selected</span>
        <span onclick="clearAll()" style="cursor:pointer;color:var(--danger);font-size:0.78rem;">Clear all ✕</span>
      </div>

      <div class="preview-grid" id="previewGrid"></div>

    </div>

    <!-- Submit -->
    <button type="submit" name="submit" class="btn-submit" id="submitBtn">
      <div class="btn-inner">
        <div class="spinner"></div>
        <span class="btn-text">🚀 Post Room Now</span>
      </div>
    </button>

  </form>
</div>

<script>
// ── FILES STATE ──
let selectedFiles = [];

// ── UPLOAD ZONE CLICK ──
const uploadZone = document.getElementById('uploadZone');
const mediaInput = document.getElementById('mediaInput');

uploadZone.addEventListener('click', () => mediaInput.click());

// ── DRAG AND DROP ──
uploadZone.addEventListener('dragover', e => {
  e.preventDefault();
  uploadZone.classList.add('drag-over');
});
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', e => {
  e.preventDefault();
  uploadZone.classList.remove('drag-over');
  addFiles(e.dataTransfer.files);
});

// ── FILE INPUT CHANGE ──
mediaInput.addEventListener('change', e => {
  addFiles(e.target.files);
});

// ── ADD FILES ──
function addFiles(files) {
  const validExt = ['jpg','jpeg','png','webp','mp4','webm','ogg'];
  let added = 0;

  Array.from(files).forEach(file => {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!validExt.includes(ext)) return;
    if (selectedFiles.length >= 10) {
      showToast('⚠️ Maximum 10 files allowed', 'error');
      return;
    }
    // avoid duplicates
    if (selectedFiles.find(f => f.name === file.name && f.size === file.size)) return;
    selectedFiles.push(file);
    added++;
  });

  if (added > 0) updatePreview();
}

// ── UPDATE PREVIEW ──
function updatePreview() {
  const grid = document.getElementById('previewGrid');
  const count = document.getElementById('fileCount');
  const countText = document.getElementById('countText');
  grid.innerHTML = '';

  selectedFiles.forEach((file, index) => {
    const url = URL.createObjectURL(file);
    const isVideo = file.type.startsWith('video');
    const div = document.createElement('div');
    div.className = 'preview-item';
    div.style.animationDelay = (index * 0.05) + 's';
    div.innerHTML = `
      ${isVideo
        ? `<video src="${url}" muted loop playsinline></video>`
        : `<img src="${url}" alt="${file.name}">`
      }
      <div class="overlay"></div>
      <span class="type-badge ${isVideo ? 'vid' : 'img'}">${isVideo ? '▶ VID' : '🖼 IMG'}</span>
      <button class="remove-btn" onclick="removeFile(${index})">✕</button>
    `;
    // play video on hover
    if (isVideo) {
      const vid = div.querySelector('video');
      div.addEventListener('mouseenter', () => vid.play());
      div.addEventListener('mouseleave', () => { vid.pause(); vid.currentTime = 0; });
    }
    grid.appendChild(div);
  });

  const n = selectedFiles.length;
  countText.textContent = `${n} file${n !== 1 ? 's' : ''}`;
  if (n > 0) count.classList.add('show'); else count.classList.remove('show');

  // sync with actual input
  syncFilesToInput();
}

// ── REMOVE FILE ──
function removeFile(index) {
  URL.revokeObjectURL(URL.createObjectURL(selectedFiles[index]));
  selectedFiles.splice(index, 1);
  updatePreview();
}

// ── CLEAR ALL ──
function clearAll() {
  selectedFiles = [];
  updatePreview();
}

// ── SYNC FILES TO REAL INPUT (for form submit) ──
function syncFilesToInput() {
  const dt = new DataTransfer();
  selectedFiles.forEach(f => dt.items.add(f));
  mediaInput.files = dt.files;
}

// ── FORM SUBMIT ANIMATION ──
document.getElementById('roomForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.classList.add('loading');
  btn.querySelector('.btn-text').textContent = 'Posting…';
});

// ── STEP PROGRESS ──
const fields = ['title', 'type', 'price', 'city', 'contact_no', 'description'];
function updateSteps() {
  const vals = fields.map(n => document.querySelector(`[name="${n}"]`)?.value?.trim());
  const done1 = vals[0] && vals[1] && vals[2];
  const done2 = vals[3] && vals[4];
  const done3 = vals[5];
  const hasMedia = selectedFiles.length > 0;
  const steps = document.querySelectorAll('.step');

  if (done1) { steps[0].classList.add('done'); steps[0].classList.remove('active'); steps[1].classList.add('active'); }
  if (done2) { steps[1].classList.add('done'); steps[1].classList.remove('active'); steps[2].classList.add('active'); }
  if (hasMedia) { steps[2].classList.add('done'); steps[2].classList.remove('active'); steps[3].classList.add('active'); }
}
document.querySelectorAll('input, select, textarea').forEach(el => {
  el.addEventListener('input', updateSteps);
});

// ── TOAST ──
function showToast(msg, type = 'success') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${type === 'success' ? '✅' : '⚠️'}</span> ${msg}`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('out');
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}
</script>

</body>
</html>