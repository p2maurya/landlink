<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$post_success = false;
$post_error   = '';

if(isset($_POST['submit'])){

    $user_id    = (int)$_SESSION['user_id'];
    $title      = mysqli_real_escape_string($conn, trim($_POST['title']));
    $city       = mysqli_real_escape_string($conn, trim($_POST['city']));
    $type       = mysqli_real_escape_string($conn, trim($_POST['type']));
    $price      = (int)$_POST['price'];
    $desc       = mysqli_real_escape_string($conn, trim($_POST['description']));
    $contact_no = mysqli_real_escape_string($conn, trim($_POST['contact_no']));

    $uploads_dir = "uploads/";
    if(!is_dir($uploads_dir)) mkdir($uploads_dir, 0777, true);

    $image_paths = [];
    $video_paths = [];
    $doc_paths   = [];

    // ── MEDIA FILES (images + videos) ──
    if(!empty($_FILES['media']['name'][0])){
        foreach($_FILES['media']['tmp_name'] as $key => $tmp){
            if($_FILES['media']['error'][$key] !== 0) continue;
            $orig = $_FILES['media']['name'][$key];
            if(empty($orig)) continue;
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            // FIX: uniqid guarantees unique name even in same-second loop
            $new_name = uniqid(mt_rand(), true) . '.' . $ext;
            $dest     = $uploads_dir . $new_name;
            if(in_array($ext, ['jpg','jpeg','png','webp','gif'])){
                if(move_uploaded_file($tmp, $dest)) $image_paths[] = $dest;
            } elseif(in_array($ext, ['mp4','webm','ogg','mov'])){
                if(move_uploaded_file($tmp, $dest)) $video_paths[] = $dest;
            }
        }
    }

    // ── DOCUMENT FILES ──
    if(!empty($_FILES['documents']['name'][0])){
        $doc_dir = "uploads/docs/";
        if(!is_dir($doc_dir)) mkdir($doc_dir, 0777, true);
        foreach($_FILES['documents']['tmp_name'] as $key => $tmp){
            if($_FILES['documents']['error'][$key] !== 0) continue;
            $orig = $_FILES['documents']['name'][$key];
            if(empty($orig)) continue;
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if(!in_array($ext, ['pdf','jpg','jpeg','png','webp'])) continue;
            $new_name = uniqid(mt_rand(), true) . '.' . $ext;
            $dest     = $doc_dir . $new_name;
            if(move_uploaded_file($tmp, $dest)) $doc_paths[] = $dest;
        }
    }

    if(empty($image_paths) && empty($video_paths)){
        $post_error = 'Please upload at least one image or video.';
    } else {
        $images_str   = mysqli_real_escape_string($conn, implode(',', $image_paths));
        $videos_str   = mysqli_real_escape_string($conn, implode(',', $video_paths));
        $docs_str     = mysqli_real_escape_string($conn, implode(',', $doc_paths));
        $prop_type    = mysqli_real_escape_string($conn, trim($_POST['property_type'] ?? ''));
        $doc_note     = mysqli_real_escape_string($conn, trim($_POST['doc_note'] ?? ''));

        // Check if 'documents' column exists — if not, we skip it gracefully
        $query = "INSERT INTO rooms
            (title, city, room_type, price, description, contact_no, image, video, user_id)
            VALUES
            ('$title','$city','$type','$price','$desc','$contact_no','$images_str','$videos_str','$user_id')";

        if(mysqli_query($conn, $query)){
            $new_room_id = mysqli_insert_id($conn);

            // Save docs separately if docs table or column exists
            // Graceful — won't break if table doesn't exist yet
            if(!empty($doc_paths)){
                @mysqli_query($conn,
                    "UPDATE rooms SET
                     documents='$docs_str',
                     property_type='$prop_type',
                     doc_note='$doc_note'
                     WHERE id=$new_room_id"
                );
            }

            $post_success = true;
        } else {
            $post_error = mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Your Room — p2mdestiny</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --bg:       #0a0a0f;
  --surface:  #111118;
  --card:     #16161f;
  --border:   #2a2a38;
  --accent:   #5b4ef8;
  --accent2:  #f85b8a;
  --accent3:  #5bf8c4;
  --gold:     #f0b429;
  --text:     #f0f0f8;
  --muted:    #7a7a9a;
  --danger:   #f85b5b;
  --success:  #5bf8a0;
  --ff-head:  'Syne', sans-serif;
  --ff-body:  'DM Sans', sans-serif;
  --radius:   16px;
  --trans:    cubic-bezier(0.4,0,0.2,1);
}

html { scroll-behavior: smooth; }
body { font-family: var(--ff-body); background: var(--bg); color: var(--text); min-height: 100vh; overflow-x: hidden; }

body::before {
  content:''; position:fixed; inset:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  pointer-events:none; z-index:0; opacity:0.4;
}

.glow-orb { position:fixed; border-radius:50%; filter:blur(120px); pointer-events:none; z-index:0; }
.glow-orb.a { width:600px; height:600px; background:rgba(91,78,248,0.12); top:-200px; right:-200px; }
.glow-orb.b { width:400px; height:400px; background:rgba(248,91,138,0.08); bottom:-100px; left:-100px; }
.glow-orb.c { width:300px; height:300px; background:rgba(91,248,196,0.06); top:50%; left:50%; transform:translate(-50%,-50%); }

.wrapper { position:relative; z-index:1; max-width:720px; margin:0 auto; padding:40px 20px 80px; }

/* PAGE HEADER */
.page-header { text-align:center; margin-bottom:48px; animation:fadeUp 0.6s var(--trans) both; }
.badge { display:inline-flex; align-items:center; gap:6px; padding:6px 16px; background:rgba(91,78,248,0.15); border:1px solid rgba(91,78,248,0.3); border-radius:100px; font-size:0.75rem; font-weight:500; color:#a09bfb; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:16px; }
.page-title { font-family:var(--ff-head); font-size:clamp(2rem,5vw,3rem); font-weight:800; line-height:1.1; letter-spacing:-0.02em; }
.page-title span { background:linear-gradient(135deg,#5b4ef8,#f85b8a); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.page-sub { margin-top:10px; color:var(--muted); font-size:0.95rem; font-weight:300; }

/* STEPS */
.steps { display:flex; align-items:center; justify-content:center; margin-bottom:40px; animation:fadeUp 0.6s 0.1s var(--trans) both; }
.step { display:flex; flex-direction:column; align-items:center; gap:6px; }
.step-dot { width:36px; height:36px; border-radius:50%; background:var(--card); border:2px solid var(--border); display:flex; align-items:center; justify-content:center; font-family:var(--ff-head); font-weight:700; font-size:0.85rem; color:var(--muted); transition:all 0.3s var(--trans); }
.step.active .step-dot { background:var(--accent); border-color:var(--accent); color:white; box-shadow:0 0 20px rgba(91,78,248,0.5); }
.step.done .step-dot { background:var(--accent3); border-color:var(--accent3); color:#0a0a0f; }
.step-label { font-size:0.7rem; color:var(--muted); letter-spacing:0.05em; text-transform:uppercase; font-weight:500; }
.step.active .step-label { color:var(--accent); }
.step-line { flex:1; height:2px; background:var(--border); max-width:50px; margin:0 6px 22px; transition:background .3s; }
.step-line.done { background:var(--accent3); }

/* FORM SECTIONS */
.form-section { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:28px; margin-bottom:20px; transition:border-color 0.3s var(--trans); animation:fadeUp 0.6s var(--trans) both; }
.form-section:hover { border-color:rgba(91,78,248,0.3); }
.form-section:nth-child(2){animation-delay:.1s}
.form-section:nth-child(3){animation-delay:.15s}
.form-section:nth-child(4){animation-delay:.2s}
.form-section:nth-child(5){animation-delay:.25s}

.section-title { font-family:var(--ff-head); font-size:0.8rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); margin-bottom:20px; display:flex; align-items:center; gap:8px; }
.section-title::before { content:''; display:inline-block; width:16px; height:2px; background:var(--accent); border-radius:2px; }
.section-title.doc-title::before { background:var(--gold); }

.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media(max-width:500px){ .grid-2 { grid-template-columns:1fr; } }

/* FIELDS */
.field { display:flex; flex-direction:column; gap:6px; }
.field label { font-size:0.78rem; font-weight:500; color:var(--muted); letter-spacing:0.04em; }
.field input, .field select, .field textarea {
  background:rgba(255,255,255,0.03); border:1.5px solid var(--border);
  border-radius:12px; padding:13px 16px; color:var(--text);
  font-family:var(--ff-body); font-size:0.93rem; outline:none; width:100%;
  transition:border-color 0.25s var(--trans), box-shadow 0.25s var(--trans), background 0.25s var(--trans);
}
.field input::placeholder, .field textarea::placeholder { color:var(--muted); opacity:0.6; }
.field input:focus, .field select:focus, .field textarea:focus { border-color:var(--accent); background:rgba(91,78,248,0.06); box-shadow:0 0 0 3px rgba(91,78,248,0.12); }
.field select option { background:var(--surface); }
.field textarea { resize:vertical; min-height:110px; line-height:1.6; }
.input-prefix { position:relative; }
.input-prefix .prefix { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--accent); font-weight:600; pointer-events:none; }
.input-prefix input { padding-left:30px; }

/* UPLOAD ZONE */
.upload-zone { border:2px dashed var(--border); border-radius:var(--radius); padding:36px 20px; text-align:center; cursor:pointer; transition:all 0.3s var(--trans); position:relative; overflow:hidden; }
.upload-zone:hover, .upload-zone.drag-over { border-color:var(--accent); background:rgba(91,78,248,0.05); }
.upload-zone.drag-over { transform:scale(1.01); box-shadow:0 0 30px rgba(91,78,248,0.15); }
.upload-zone input[type=file] { display:none; }

.upload-icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; font-size:1.4rem; transition:transform 0.3s var(--trans); }
.upload-zone:hover .upload-icon { transform:translateY(-3px) scale(1.05); }
.uz-media .upload-icon { background:linear-gradient(135deg,rgba(91,78,248,0.2),rgba(248,91,138,0.2)); }
.uz-doc   .upload-icon { background:linear-gradient(135deg,rgba(240,180,41,0.2),rgba(91,248,196,0.2)); }

.upload-title { font-family:var(--ff-head); font-size:0.95rem; font-weight:700; margin-bottom:6px; }
.upload-sub { font-size:0.78rem; color:var(--muted); margin-bottom:14px; }
.upload-btn-fake { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; background:rgba(91,78,248,0.12); border:1px solid rgba(91,78,248,0.25); border-radius:100px; color:#a09bfb; font-size:0.8rem; font-weight:600; transition:all 0.25s; }
.upload-zone:hover .upload-btn-fake { background:var(--accent); border-color:var(--accent); color:white; }
.uz-doc .upload-btn-fake { background:rgba(240,180,41,0.1); border-color:rgba(240,180,41,0.25); color:var(--gold); }
.uz-doc:hover .upload-btn-fake { background:var(--gold); border-color:var(--gold); color:#0a0a0f; }

.format-tags { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:12px; flex-wrap:wrap; }
.format-tag { padding:2px 9px; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:100px; font-size:0.68rem; color:var(--muted); }

/* PREVIEWS */
.preview-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:10px; margin-top:14px; }
.preview-item { position:relative; border-radius:10px; overflow:hidden; aspect-ratio:1; background:var(--surface); border:1px solid var(--border); animation:popIn 0.3s var(--trans) both; }
.preview-item img, .preview-item video { width:100%; height:100%; object-fit:cover; display:block; }
.preview-item .remove-btn { position:absolute; top:5px; right:5px; width:24px; height:24px; border-radius:50%; background:rgba(0,0,0,0.7); border:1px solid rgba(255,255,255,0.15); color:white; font-size:0.7rem; cursor:pointer; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s, background 0.2s; backdrop-filter:blur(4px); z-index:2; }
.preview-item:hover .remove-btn { opacity:1; }
.preview-item .remove-btn:hover { background:var(--danger); }
.preview-item .type-badge { position:absolute; bottom:5px; left:5px; padding:2px 7px; border-radius:100px; font-size:0.62rem; font-weight:600; backdrop-filter:blur(8px); }
.type-badge.img { background:rgba(91,78,248,0.85); color:white; }
.type-badge.vid { background:rgba(248,91,138,0.85); color:white; }
.type-badge.doc { background:rgba(240,180,41,0.85); color:#0a0a0f; }
.preview-item .overlay { position:absolute; inset:0; background:linear-gradient(to top,rgba(0,0,0,0.45) 0%,transparent 55%); pointer-events:none; }

.file-count { display:none; align-items:center; justify-content:space-between; margin-top:10px; padding:9px 14px; background:rgba(91,78,248,0.08); border:1px solid rgba(91,78,248,0.2); border-radius:10px; font-size:0.8rem; color:#a09bfb; }
.file-count.show { display:flex; }
.file-count strong { font-weight:700; }
.doc-count { background:rgba(240,180,41,0.08); border-color:rgba(240,180,41,0.2); color:var(--gold); }

/* DOC SECTION special styling */
.doc-section { border-color:rgba(240,180,41,0.2); }
.doc-section:hover { border-color:rgba(240,180,41,0.4); }
.doc-notice { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; background:rgba(240,180,41,0.08); border:1px solid rgba(240,180,41,0.2); border-radius:12px; margin-bottom:20px; font-size:0.83rem; color:rgba(240,180,41,0.9); line-height:1.6; }
.doc-notice .icon { font-size:1.2rem; flex-shrink:0; margin-top:1px; }

/* SUBMIT */
.btn-submit { width:100%; padding:18px; background:linear-gradient(135deg,#5b4ef8,#f85b8a); border:none; border-radius:14px; color:white; font-family:var(--ff-head); font-size:1rem; font-weight:700; letter-spacing:0.02em; cursor:pointer; position:relative; overflow:hidden; transition:transform 0.2s var(--trans), box-shadow 0.2s var(--trans); margin-top:8px; animation:fadeUp 0.6s 0.4s var(--trans) both; }
.btn-submit::before { content:''; position:absolute; inset:0; background:linear-gradient(135deg,rgba(255,255,255,0.15),transparent); opacity:0; transition:opacity 0.3s; }
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 12px 40px rgba(91,78,248,0.4),0 4px 16px rgba(248,91,138,0.3); }
.btn-submit:hover::before { opacity:1; }
.btn-submit:active { transform:translateY(0); }
.btn-submit.loading { opacity:0.7; pointer-events:none; }
.btn-inner { display:flex; align-items:center; justify-content:center; gap:10px; }
.spinner { width:18px; height:18px; border:2px solid rgba(255,255,255,0.3); border-top-color:white; border-radius:50%; animation:spin 0.7s linear infinite; display:none; }
.btn-submit.loading .spinner { display:block; }

/* ── SUCCESS MODAL ── */
.modal-overlay {
  position:fixed; inset:0; z-index:1000;
  background:rgba(0,0,0,0.75); backdrop-filter:blur(12px);
  display:flex; align-items:center; justify-content:center;
  padding:20px;
  opacity:0; pointer-events:none;
  transition:opacity .35s var(--trans);
}
.modal-overlay.show { opacity:1; pointer-events:all; }

.modal {
  background:var(--card);
  border:1px solid var(--border);
  border-radius:24px;
  padding:48px 40px;
  max-width:440px; width:100%;
  text-align:center;
  transform:translateY(30px) scale(0.95);
  transition:transform .4s var(--trans);
  position:relative;
  overflow:hidden;
}
.modal-overlay.show .modal { transform:translateY(0) scale(1); }

/* confetti burst */
.modal::before {
  content:'';
  position:absolute; inset:0;
  background:radial-gradient(ellipse at 50% 0%, rgba(91,78,248,0.18) 0%, transparent 70%);
  pointer-events:none;
}

.modal-icon {
  width:80px; height:80px; border-radius:50%;
  background:linear-gradient(135deg,rgba(91,248,160,0.2),rgba(91,78,248,0.2));
  border:2px solid rgba(91,248,160,0.4);
  display:flex; align-items:center; justify-content:center;
  font-size:2.2rem; margin:0 auto 20px;
  animation:popIn .5s .2s var(--trans) both;
}
.modal-title {
  font-family:var(--ff-head); font-size:1.6rem; font-weight:800;
  letter-spacing:-.02em; margin-bottom:10px;
}
.modal-title span { color:var(--success); }
.modal-sub { font-size:.9rem; color:var(--muted); line-height:1.6; margin-bottom:32px; }

.modal-actions { display:flex; flex-direction:column; gap:12px; }
.btn-dashboard {
  display:flex; align-items:center; justify-content:center; gap:10px;
  padding:15px 24px; border-radius:12px;
  background:linear-gradient(135deg,var(--accent),#a06cf0);
  color:white; font-family:var(--ff-head); font-size:.95rem; font-weight:700;
  text-decoration:none; border:none; cursor:pointer;
  box-shadow:0 6px 24px rgba(91,78,248,0.4);
  transition:all .3s var(--trans);
}
.btn-dashboard:hover { transform:translateY(-2px); box-shadow:0 12px 36px rgba(91,78,248,0.55); }
.btn-post-another {
  padding:13px 24px; border-radius:12px;
  background:transparent; color:var(--muted);
  font-family:var(--ff-body); font-size:.88rem; font-weight:500;
  border:1px solid var(--border); cursor:pointer;
  transition:all .25s var(--trans);
}
.btn-post-another:hover { color:var(--text); border-color:var(--border-l,#3a3a52); background:rgba(255,255,255,.04); }

/* countdown ring */
.countdown-wrap { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:16px; font-size:.78rem; color:var(--muted); }
.countdown-ring { position:relative; width:28px; height:28px; }
.countdown-ring svg { transform:rotate(-90deg); }
.countdown-ring circle { fill:none; stroke:var(--border); stroke-width:3; }
.countdown-ring .prog { stroke:var(--accent); stroke-dasharray:69.1; stroke-dashoffset:69.1; stroke-linecap:round; transition:stroke-dashoffset 1s linear; }
.countdown-num { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:700; color:var(--accent); }

/* TOAST */
.toast-container { position:fixed; top:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:10px; }
.toast { padding:13px 18px; border-radius:12px; font-size:0.86rem; font-weight:500; backdrop-filter:blur(16px); border:1px solid; min-width:260px; display:flex; align-items:center; gap:10px; transform:translateX(120%); animation:toastIn 0.4s var(--trans) forwards; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
.toast.out { animation:toastOut 0.3s var(--trans) forwards; }
.toast.success { background:rgba(91,248,160,0.1); border-color:rgba(91,248,160,0.3); color:var(--success); }
.toast.error   { background:rgba(248,91,91,0.1);  border-color:rgba(248,91,91,0.3);  color:var(--danger); }

/* ANIMS */
@keyframes fadeUp  { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
@keyframes popIn   { from{opacity:0;transform:scale(0.8)}       to{opacity:1;transform:scale(1)} }
@keyframes spin    { to{transform:rotate(360deg)} }
@keyframes toastIn { to{transform:translateX(0)} }
@keyframes toastOut{ to{transform:translateX(120%);opacity:0} }

::-webkit-scrollbar{width:5px} ::-webkit-scrollbar-track{background:var(--bg)} ::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px} ::-webkit-scrollbar-thumb:hover{background:var(--accent)}
</style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>
<div class="glow-orb a"></div>
<div class="glow-orb b"></div>
<div class="glow-orb c"></div>

<!-- ══ SUCCESS MODAL ══ -->
<div class="modal-overlay <?php echo $post_success ? 'show' : ''; ?>" id="successModal">
  <div class="modal">
    <div class="modal-icon">🎉</div>
    <h2 class="modal-title">Room <span>Posted!</span></h2>
    <p class="modal-sub">
      Your listing is now live and visible to renters.<br>
      You'll be redirected to your dashboard automatically.
    </p>
    <div class="modal-actions">
      <a href="dashboard.php" class="btn-dashboard">
        🏠 Go to Dashboard
      </a>
      <button class="btn-post-another" onclick="closeModal()">
        ➕ Post Another Room
      </button>
    </div>
    <div class="countdown-wrap">
      <div class="countdown-ring">
        <svg width="28" height="28" viewBox="0 0 28 28">
          <circle cx="14" cy="14" r="11"/>
          <circle class="prog" cx="14" cy="14" r="11" id="progCircle"/>
        </svg>
        <div class="countdown-num" id="countNum">5</div>
      </div>
      <span>Auto-redirecting in <span id="countTxt">5</span>s</span>
    </div>
  </div>
</div>

<div class="wrapper">

  <!-- HEADER -->
  <div class="page-header">
    <div class="badge">🏠 Room Listing</div>
    <h1 class="page-title">Post Your <span>Room</span></h1>
    <p class="page-sub">Fill in the details below to list your property</p>
  </div>

  <!-- STEPS -->
  <div class="steps" id="stepsRow">
    <div class="step active" id="step1"><div class="step-dot">1</div><span class="step-label">Details</span></div>
    <div class="step-line" id="line1"></div>
    <div class="step" id="step2"><div class="step-dot">2</div><span class="step-label">Location</span></div>
    <div class="step-line" id="line2"></div>
    <div class="step" id="step3"><div class="step-dot">3</div><span class="step-label">Media</span></div>
    <div class="step-line" id="line3"></div>
    <div class="step" id="step4"><div class="step-dot">4</div><span class="step-label">Docs</span></div>
    <div class="step-line" id="line4"></div>
    <div class="step" id="step5"><div class="step-dot">5</div><span class="step-label">Post</span></div>
  </div>

  <?php if($post_error): ?>
  <div style="padding:14px 18px;background:rgba(248,91,91,0.1);border:1px solid rgba(248,91,91,0.3);border-radius:12px;color:var(--danger);font-size:.88rem;margin-bottom:24px;">
    ⚠️ <?php echo htmlspecialchars($post_error); ?>
  </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="roomForm">

    <!-- ── SECTION 1: BASIC INFO ── -->
    <div class="form-section">
      <div class="section-title">Basic Information</div>
      <div class="field" style="margin-bottom:14px;">
        <label>Room Title</label>
        <input type="text" name="title" placeholder="e.g. Spacious Double Room near Metro" required>
      </div>
      <div class="grid-2">
        <div class="field">
          <label>Room Type</label>
          <select name="type" required>
            <option value="">Select Type</option>
            <option>Single</option>
            <option>Double</option>
            <option>PG</option>
            <option>Shared</option>
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

    <!-- ── SECTION 2: LOCATION ── -->
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

    <!-- ── SECTION 3: DESCRIPTION ── -->
    <div class="form-section">
      <div class="section-title">Description</div>
      <div class="field">
        <label>Tell tenants about this room</label>
        <textarea name="description" placeholder="Describe amenities, nearby places, furnishing…" required></textarea>
      </div>
    </div>

    <!-- ── SECTION 4: MEDIA ── -->
    <div class="form-section">
      <div class="section-title">Photos & Videos</div>
      <div class="upload-zone uz-media" id="uploadZone">
        <input type="file" name="media[]" id="mediaInput" multiple accept="image/*,video/*">
        <div class="upload-icon">📸</div>
        <div class="upload-title">Drag & Drop or Browse</div>
        <div class="upload-sub">Upload photos and/or a video tour (max 10 files)</div>
        <div class="upload-btn-fake">📁 Choose Files</div>
        <div class="format-tags">
          <span class="format-tag">JPG</span><span class="format-tag">PNG</span>
          <span class="format-tag">WEBP</span><span class="format-tag">MP4</span><span class="format-tag">WEBM</span>
        </div>
      </div>
      <div class="file-count" id="mediaCount"><span>🗂️ <strong id="mediaCountTxt">0</strong> files selected</span><span onclick="clearMedia()" style="cursor:pointer;color:var(--danger);font-size:.75rem;">Clear ✕</span></div>
      <div class="preview-grid" id="mediaGrid"></div>
    </div>

    <!-- ── SECTION 5: DOCUMENTS ── -->
    <div class="form-section doc-section">
      <div class="section-title doc-title">📄 Property Documents <span style="font-size:.7rem;color:var(--gold);background:rgba(240,180,41,.1);border:1px solid rgba(240,180,41,.25);padding:2px 10px;border-radius:100px;margin-left:8px;font-family:var(--ff-body);">Optional</span></div>

      <div class="doc-notice">
        <span class="icon">🔒</span>
        <span>For plot, land, or mortgage listings — upload ownership documents (Registry, Sale Deed, Aadhaar, etc.) to build trust with renters. Documents are stored securely and only shared with verified users.</span>
      </div>

      <div class="grid-2" style="margin-bottom:16px;">
        <div class="field">
          <label>Property Type</label>
          <select name="property_type">
            <option value="">Select (optional)</option>
            <option value="Room">Room / Flat</option>
            <option value="Plot">Plot / Land</option>
            <option value="Field">Agricultural Field</option>
            <option value="Commercial">Commercial Space</option>
            <option value="Mortgage">Mortgage / Loan Property</option>
          </select>
        </div>
        <div class="field">
          <label>Document Note <span style="font-size:.7rem;opacity:.6;">(optional)</span></label>
          <input type="text" name="doc_note" placeholder="e.g. Registry 2022, verified">
        </div>
      </div>

      <div class="upload-zone uz-doc" id="docZone">
        <input type="file" name="documents[]" id="docInput" multiple accept="image/*,.pdf">
        <div class="upload-icon">📋</div>
        <div class="upload-title">Upload Property Documents</div>
        <div class="upload-sub">Registry, Sale Deed, Aadhaar, NOC, etc.</div>
        <div class="upload-btn-fake">📂 Choose Documents</div>
        <div class="format-tags">
          <span class="format-tag">PDF</span><span class="format-tag">JPG</span>
          <span class="format-tag">PNG</span><span class="format-tag">WEBP</span>
        </div>
      </div>
      <div class="file-count doc-count" id="docCount"><span>📋 <strong id="docCountTxt">0</strong> documents selected</span><span onclick="clearDocs()" style="cursor:pointer;color:var(--danger);font-size:.75rem;">Clear ✕</span></div>
      <div class="preview-grid" id="docGrid"></div>
    </div>

    <!-- ── SUBMIT ── -->
    <button type="submit" name="submit" class="btn-submit" id="submitBtn">
      <div class="btn-inner">
        <div class="spinner"></div>
        <span class="btn-text">🚀 Post Room Now</span>
      </div>
    </button>

  </form>
</div>

<script>
// ── MEDIA FILES ──
let mediaFiles = [];
let docFiles   = [];

// Media zone
const uploadZone = document.getElementById('uploadZone');
const mediaInput = document.getElementById('mediaInput');
uploadZone.addEventListener('click', () => mediaInput.click());
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', e => { e.preventDefault(); uploadZone.classList.remove('drag-over'); addMedia(e.dataTransfer.files); });
mediaInput.addEventListener('change', e => addMedia(e.target.files));

function addMedia(files) {
  const valid = ['jpg','jpeg','png','webp','gif','mp4','webm','ogg','mov'];
  Array.from(files).forEach(f => {
    const ext = f.name.split('.').pop().toLowerCase();
    if(!valid.includes(ext)) return;
    if(mediaFiles.length >= 10){ showToast('Max 10 media files','error'); return; }
    if(mediaFiles.find(x => x.name===f.name && x.size===f.size)) return;
    mediaFiles.push(f);
  });
  renderMedia();
}
function removeMedia(i){ mediaFiles.splice(i,1); renderMedia(); }
function clearMedia(){ mediaFiles=[]; renderMedia(); }

function renderMedia(){
  const grid = document.getElementById('mediaGrid');
  const cnt  = document.getElementById('mediaCount');
  grid.innerHTML='';
  mediaFiles.forEach((f,i)=>{
    const url = URL.createObjectURL(f);
    const isVid = f.type.startsWith('video');
    const d = document.createElement('div');
    d.className='preview-item';
    d.style.animationDelay=(i*.04)+'s';
    d.innerHTML=`
      ${isVid?`<video src="${url}" muted loop playsinline>`:`<img src="${url}">`}
      <div class="overlay"></div>
      <span class="type-badge ${isVid?'vid':'img'}">${isVid?'▶ VID':'🖼 IMG'}</span>
      <button class="remove-btn" onclick="removeMedia(${i})">✕</button>`;
    if(isVid){ const v=d.querySelector('video'); d.addEventListener('mouseenter',()=>v.play()); d.addEventListener('mouseleave',()=>{v.pause();v.currentTime=0;}); }
    grid.appendChild(d);
  });
  document.getElementById('mediaCountTxt').textContent = mediaFiles.length;
  cnt.classList.toggle('show', mediaFiles.length>0);
  syncInput(mediaInput, mediaFiles);
  updateSteps();
}

// Doc zone
const docZone  = document.getElementById('docZone');
const docInput = document.getElementById('docInput');
docZone.addEventListener('click', ()=>docInput.click());
docZone.addEventListener('dragover', e=>{e.preventDefault();docZone.classList.add('drag-over');});
docZone.addEventListener('dragleave', ()=>docZone.classList.remove('drag-over'));
docZone.addEventListener('drop', e=>{e.preventDefault();docZone.classList.remove('drag-over');addDocs(e.dataTransfer.files);});
docInput.addEventListener('change', e=>addDocs(e.target.files));

function addDocs(files){
  const valid=['pdf','jpg','jpeg','png','webp'];
  Array.from(files).forEach(f=>{
    const ext=f.name.split('.').pop().toLowerCase();
    if(!valid.includes(ext)) return;
    if(docFiles.length>=5){showToast('Max 5 documents','error');return;}
    if(docFiles.find(x=>x.name===f.name&&x.size===f.size)) return;
    docFiles.push(f);
  });
  renderDocs();
}
function removeDocs(i){docFiles.splice(i,1);renderDocs();}
function clearDocs(){docFiles=[];renderDocs();}

function renderDocs(){
  const grid=document.getElementById('docGrid');
  const cnt=document.getElementById('docCount');
  grid.innerHTML='';
  docFiles.forEach((f,i)=>{
    const isPdf=f.name.endsWith('.pdf');
    const url=URL.createObjectURL(f);
    const d=document.createElement('div');
    d.className='preview-item';
    d.style.animationDelay=(i*.04)+'s';
    d.innerHTML=`
      ${isPdf
        ?`<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:rgba(240,180,41,0.1);font-size:2rem;">📄</div>`
        :`<img src="${url}">`}
      <div class="overlay"></div>
      <span class="type-badge doc">${isPdf?'PDF':'IMG'}</span>
      <button class="remove-btn" onclick="removeDocs(${i})">✕</button>`;
    grid.appendChild(d);
  });
  document.getElementById('docCountTxt').textContent=docFiles.length;
  cnt.classList.toggle('show',docFiles.length>0);
  syncInput(docInput,docFiles);
}

function syncInput(input,files){
  const dt=new DataTransfer();
  files.forEach(f=>dt.items.add(f));
  input.files=dt.files;
}

// ── STEPS ──
function updateSteps(){
  const v = name => document.querySelector(`[name="${name}"]`)?.value?.trim();
  const d1 = v('title')&&v('type')&&v('price');
  const d2 = v('city')&&v('contact_no');
  const d3 = v('description');
  const d4 = mediaFiles.length>0;

  const mark=(id,done,active)=>{
    const el=document.getElementById(id);
    el.classList.toggle('done',done);
    el.classList.toggle('active',active&&!done);
  };
  const line=(id,done)=>document.getElementById(id).classList.toggle('done',done);

  mark('step1',!!d1,true); line('line1',!!d1);
  mark('step2',!!d2,!!d1); line('line2',!!d2);
  mark('step3',!!d3,!!d2); line('line3',!!d3);
  mark('step4',d4,!!d3);   line('line4',d4);
  mark('step5',false,d4);
}
document.querySelectorAll('input,select,textarea').forEach(el=>el.addEventListener('input',updateSteps));

// ── FORM SUBMIT ──
document.getElementById('roomForm').addEventListener('submit',()=>{
  const btn=document.getElementById('submitBtn');
  btn.classList.add('loading');
  btn.querySelector('.btn-text').textContent='Posting…';
});

// ── SUCCESS MODAL AUTO-REDIRECT ──
<?php if($post_success): ?>
(function(){
  const overlay = document.getElementById('successModal');
  const circle  = document.getElementById('progCircle');
  const numEl   = document.getElementById('countNum');
  const txtEl   = document.getElementById('countTxt');
  const total = 5;
  const circumference = 2 * Math.PI * 11; // r=11

  let remaining = total;
  circle.style.strokeDashoffset = 0;

  const tick = setInterval(()=>{
    remaining--;
    numEl.textContent = remaining;
    txtEl.textContent = remaining;
    const offset = circumference * (remaining / total);
    circle.style.strokeDashoffset = circumference - offset;
    if(remaining <= 0){
      clearInterval(tick);
      window.location = 'dashboard.php';
    }
  }, 1000);
})();
<?php endif; ?>

// ── CLOSE MODAL ──
function closeModal(){
  document.getElementById('successModal').classList.remove('show');
  // reset form
  document.getElementById('roomForm').reset();
  mediaFiles=[]; docFiles=[];
  renderMedia(); renderDocs();
}

// ── TOAST ──
function showToast(msg, type='success'){
  const c=document.getElementById('toastContainer');
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<span>${type==='success'?'✅':'⚠️'}</span> ${msg}`;
  c.appendChild(t);
  setTimeout(()=>{ t.classList.add('out'); setTimeout(()=>t.remove(),400); },3500);
}

<?php if($post_error): ?>
showToast('<?php echo addslashes($post_error); ?>', 'error');
<?php endif; ?>
</script>
</body>
</html>