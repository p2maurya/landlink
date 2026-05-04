<?php
session_start();
include("db.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id){ header("Location: findroom.php"); exit; }

$result = mysqli_query($conn, "SELECT r.*, u.username as owner_name, u.id as owner_id
                                FROM rooms r
                                LEFT JOIN users u ON r.user_id = u.id
                                WHERE r.id = $id LIMIT 1");
if(!$result || mysqli_num_rows($result) == 0){
    echo "<script>alert('Room not found!'); window.location='findroom.php';</script>";
    exit;
}
$row = mysqli_fetch_assoc($result);

$user_id_s = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Like status & count
$liked = false;
if($user_id_s){
    $lq = mysqli_query($conn,"SELECT id FROM likes WHERE user_id=$user_id_s AND room_id=$id");
    $liked = mysqli_num_rows($lq) > 0;
}
$lc_q = mysqli_query($conn,"SELECT COUNT(*) as c FROM likes WHERE room_id=$id");
$like_count = $lc_q ? (int)mysqli_fetch_assoc($lc_q)['c'] : 0;

// Rating
$existing_rating = 0;
if($user_id_s){
    $rq = mysqli_query($conn,"SELECT rating FROM ratings WHERE user_id=$user_id_s AND room_id=$id");
    if(mysqli_num_rows($rq)>0) $existing_rating = (int)mysqli_fetch_assoc($rq)['rating'];
}
$avg_q = mysqli_query($conn,"SELECT ROUND(AVG(rating),1) as avg, COUNT(*) as cnt FROM ratings WHERE room_id=$id");
$avg_data = $avg_q ? mysqli_fetch_assoc($avg_q) : ['avg'=>0,'cnt'=>0];
$avg_rating = (float)($avg_data['avg']??0);
$rating_cnt = (int)($avg_data['cnt']??0);

// Booking
$already_booked = false;
if($user_id_s){
    $bq = mysqli_query($conn,"SELECT id FROM bookings WHERE user_id=$user_id_s AND room_id=$id");
    $already_booked = mysqli_num_rows($bq) > 0;
}

// Handle like toggle
if(isset($_POST['like'])){
    if(!$user_id_s){ header("Location: login.php"); exit; }
    if($liked){
        mysqli_query($conn,"DELETE FROM likes WHERE user_id=$user_id_s AND room_id=$id");
    } else {
        mysqli_query($conn,"INSERT IGNORE INTO likes (user_id,room_id) VALUES ($user_id_s,$id)");
    }
    header("Location: room-details.php?id=$id"); exit;
}

// Handle rating
if(isset($_POST['rate'])){
    if(!$user_id_s){ header("Location: login.php"); exit; }
    $rating = max(1,min(5,(int)$_POST['rating']));
    mysqli_query($conn,"INSERT INTO ratings (user_id,room_id,rating) VALUES ($user_id_s,$id,$rating)
        ON DUPLICATE KEY UPDATE rating=$rating");
    header("Location: room-details.php?id=$id&rated=1"); exit;
}

// Handle booking
if(isset($_POST['book'])){
    if(!$user_id_s){ header("Location: login.php"); exit; }
    if(!$already_booked){
        mysqli_query($conn,"INSERT INTO bookings (user_id,room_id) VALUES ($user_id_s,$id)");
    }
    header("Location: room-details.php?id=$id&booked=1"); exit;
}

// FILES: image aur video alag columns hain — dono merge karo
$files = [];
if(!empty($row['image'])){
    $img_files = array_values(array_filter(array_map('trim', explode(",", $row['image']))));
    $files = array_merge($files, $img_files);
}
if(!empty($row['video'])){
    $vid_files = array_values(array_filter(array_map('trim', explode(",", $row['video']))));
    $files = array_merge($files, $vid_files);
}
$fcount = count($files);

// WhatsApp number clean
$wa_number = preg_replace('/[^0-9]/', '', $row['contact_no']);
if(strlen($wa_number)==10) $wa_number = '91'.$wa_number;

// BASE URL absolute — subfolder issue fix
$base_url = 'https://landlink.gt.tc/uploads/';

$media_json = [];
foreach($files as $file){
    $file = trim($file);
    if(!$file) continue;
    // Already full URL? use as-is. Otherwise build absolute URL.
    if(strpos($file,'http') === 0){
        $url = $file;
    } else {
        $url = $base_url . basename($file);
    }
    $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $media_json[] = ['path'=>$url, 'type'=>in_array($ext,['mp4','webm','ogg','mov'])?'video':'image'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($row['title']); ?> — P2MDestiny</title>

<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Epilogue:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

:root{
  --bg:       #07070f;
  --surface:  #0d0d1a;
  --card:     #111120;
  --card-h:   #15152a;
  --border:   #1c1c30;
  --border-l: #28283e;
  --accent:   #7c6af5;
  --accent-l: #a99ff8;
  --gold:     #f5c842;
  --rose:     #f56a8f;
  --mint:     #42f5c8;
  --sky:      #42c8f5;
  --wa:       #25d366;
  --text:     #ebebf5;
  --text-2:   #8888aa;
  --text-3:   #505068;
  --danger:   #f56a42;
  --success:  #42f5a0;
  --ff-serif: 'Instrument Serif', serif;
  --ff-body:  'Epilogue', sans-serif;
  --r:        16px;
  --ease:     cubic-bezier(0.4,0,0.2,1);
}

html{scroll-behavior:smooth;}
body{font-family:var(--ff-body);background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}
body::after{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");pointer-events:none;z-index:999;opacity:.5;}

.orb{position:fixed;border-radius:50%;filter:blur(140px);pointer-events:none;z-index:0;}
.orb-a{width:700px;height:700px;background:rgba(124,106,245,0.09);top:-250px;right:-250px;}
.orb-b{width:500px;height:500px;background:rgba(245,106,143,0.06);bottom:-200px;left:-200px;}

/* ── NAV ── */
nav{position:sticky;top:12px;z-index:200;display:flex;align-items:center;justify-content:space-between;padding:10px 16px 10px 20px;margin:12px 24px 0;border-radius:100px;background:rgba(13,13,26,.85);backdrop-filter:blur(24px);border:1px solid var(--border-l);box-shadow:0 8px 32px rgba(0,0,0,.4);}
.nav-logo{font-family:var(--ff-serif);font-size:1.2rem;background:linear-gradient(120deg,var(--accent-l),var(--mint));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-decoration:none;}
.nav-links{display:flex;align-items:center;gap:2px;}
.nav-links a{padding:7px 15px;border-radius:100px;color:var(--text-2);text-decoration:none;font-size:.83rem;font-weight:500;border:1px solid transparent;transition:all .2s var(--ease);}
.nav-links a:hover{color:var(--text);background:rgba(255,255,255,.06);border-color:var(--border-l);}
.nav-links a.back{color:var(--accent-l);background:rgba(124,106,245,.1);border-color:rgba(124,106,245,.25);}
.hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px;border-radius:10px;border:1px solid var(--border-l);background:rgba(255,255,255,.04);}
.hamburger span{display:block;width:20px;height:2px;background:var(--text-2);border-radius:2px;transition:all .3s var(--ease);}
.hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.hamburger.open span:nth-child(2){opacity:0;}
.hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}
.mobile-menu{display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:999;background:rgba(7,7,15,.97);backdrop-filter:blur(24px);flex-direction:column;align-items:center;justify-content:center;gap:16px;opacity:0;pointer-events:none;transition:all .3s;}
.mobile-menu.open{display:flex;opacity:1;pointer-events:all;}
.mobile-menu a{font-family:var(--ff-serif);font-size:2rem;color:var(--text);text-decoration:none;padding:8px 28px;border-radius:14px;transition:all .2s;}
.mobile-menu a:hover{color:var(--accent-l);}
.mobile-menu-close{position:absolute;top:24px;right:24px;font-size:1.5rem;cursor:pointer;color:var(--text-2);background:rgba(255,255,255,.06);border:1px solid var(--border-l);border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;}

/* ── LAYOUT ── */
.page{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:40px 24px 80px;display:grid;grid-template-columns:1fr 360px;gap:32px;}
@media(max-width:900px){.page{grid-template-columns:1fr;}}

/* ── LEFT COL ── */
/* MAIN SWIPER */
.main-swiper-wrap{border-radius:20px;overflow:hidden;background:var(--surface);border:1px solid var(--border);margin-bottom:16px;position:relative;}
.main-swiper{width:100%;height:420px;}
/* FIX: explicit height on every child level so video gets a real box */
.main-swiper .swiper-wrapper{height:420px;}
.main-swiper .swiper-slide{width:100%;height:420px !important;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#0a0a0a;position:relative;}
.main-swiper .swiper-slide img{width:100%;height:100%;object-fit:cover;display:block;flex-shrink:0;}
.main-swiper .swiper-slide video{width:100%;height:100%;object-fit:contain;background:#000;display:block;cursor:pointer;flex-shrink:0;}

/* FIX: play overlay inside slide, positioned absolutely */
.play-overlay{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  width:54px;height:54px;border-radius:50%;
  background:rgba(0,0,0,0.6);backdrop-filter:blur(8px);
  display:flex;align-items:center;justify-content:center;
  font-size:1.2rem;pointer-events:none;z-index:3;
  border:1px solid rgba(255,255,255,0.2);
  transition:opacity .2s;
}
.play-overlay.hidden{display:none;}

.swiper-pagination-bullet{background:rgba(255,255,255,.4);opacity:1;width:8px;height:8px;}
.swiper-pagination-bullet-active{background:#fff;width:20px;border-radius:4px;}
.swiper-button-next,.swiper-button-prev{color:#fff;background:rgba(0,0,0,.5);width:36px;height:36px;border-radius:50%;backdrop-filter:blur(8px);}
.swiper-button-next::after,.swiper-button-prev::after{font-size:.8rem;font-weight:800;}

/* media count chip */
.media-chip{position:absolute;bottom:14px;right:14px;z-index:5;padding:5px 14px;border-radius:100px;background:rgba(0,0,0,.65);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.12);font-size:.75rem;font-weight:700;color:#fff;}

/* THUMBS */
.thumbs-strip{display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;}
.thumbs-strip::-webkit-scrollbar{height:3px;}
.thumbs-strip::-webkit-scrollbar-thumb{background:var(--border-l);border-radius:2px;}
.thumb{width:72px;height:60px;border-radius:10px;overflow:hidden;border:2px solid var(--border);cursor:pointer;flex-shrink:0;transition:all .2s var(--ease);}
.thumb:hover,.thumb.active{border-color:var(--accent);}
.thumb img,.thumb video{width:100%;height:100%;object-fit:cover;pointer-events:none;}

/* TITLE SECTION */
.room-header{margin:28px 0 20px;}
.room-eyebrow{display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.type-pill{padding:4px 14px;border-radius:100px;font-size:.7rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;}
.pill-single{background:rgba(124,106,245,.2);color:var(--accent-l);border:1px solid rgba(124,106,245,.3);}
.pill-double{background:rgba(66,245,200,.15);color:var(--mint);border:1px solid rgba(66,245,200,.25);}
.pill-pg,.pill-shared{background:rgba(245,106,143,.15);color:var(--rose);border:1px solid rgba(245,106,143,.25);}

.room-title{font-family:var(--ff-serif);font-size:clamp(1.6rem,3vw,2.4rem);font-weight:400;line-height:1.2;letter-spacing:-.02em;margin-bottom:12px;}
.room-title em{font-style:italic;color:var(--accent-l);}

.room-meta-row{display:flex;flex-wrap:wrap;gap:14px;}
.meta-item{display:flex;align-items:center;gap:6px;font-size:.83rem;color:var(--text-2);font-weight:500;}

/* DESCRIPTION */
.section-block{margin:28px 0;}
.section-label{font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text-3);margin-bottom:12px;display:flex;align-items:center;gap:8px;}
.section-label::after{content:'';flex:1;height:1px;background:var(--border);}
.room-desc{font-size:.9rem;color:var(--text-2);line-height:1.8;font-weight:300;}

/* RATING BLOCK */
.rating-block{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:20px;}
.stars-display{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
.stars-big{display:flex;gap:3px;}
.star-big{font-size:1.4rem;transition:transform .15s;}
.star-big.filled{color:var(--gold);}
.star-big.empty{color:var(--text-3);}
.star-big:hover{transform:scale(1.25);cursor:pointer;}
.avg-score{font-family:var(--ff-serif);font-size:2rem;color:var(--gold);line-height:1;}
.avg-label{font-size:.75rem;color:var(--text-3);}

.user-rating-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.rate-label{font-size:.78rem;color:var(--text-2);font-weight:500;}
.stars-interactive{display:flex;gap:4px;}
.star-i{font-size:1.2rem;cursor:pointer;color:var(--text-3);transition:all .15s;}
.star-i.active{color:var(--gold);}
.star-i:hover{transform:scale(1.2);}
.rate-submit{padding:7px 18px;border-radius:100px;background:rgba(245,200,66,.12);border:1px solid rgba(245,200,66,.3);color:var(--gold);font-family:var(--ff-body);font-size:.78rem;font-weight:700;cursor:pointer;transition:all .25s var(--ease);display:none;}
.rate-submit:hover{background:var(--gold);color:#1a1000;}
.rate-submit.show{display:inline-block;}

/* ── RIGHT COL ── */
.sidebar{display:flex;flex-direction:column;gap:16px;}

/* PRICE CARD */
.price-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:24px;position:sticky;top:82px;}
.price-value{font-family:var(--ff-serif);font-size:2.4rem;color:var(--gold);line-height:1;margin-bottom:4px;}
.price-value span{font-size:1rem;color:var(--text-3);font-family:var(--ff-body);}
.price-sub{font-size:.78rem;color:var(--text-3);margin-bottom:20px;}

.cta-stack{display:flex;flex-direction:column;gap:10px;margin-bottom:20px;}
.btn-cta{width:100%;padding:14px;border-radius:12px;font-family:var(--ff-body);font-size:.9rem;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s var(--ease);}
.btn-book{background:linear-gradient(135deg,var(--accent),#a06cf0);color:#fff;box-shadow:0 6px 20px rgba(124,106,245,.35);}
.btn-book:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(124,106,245,.5);}
.btn-book.booked{background:rgba(66,245,160,.1);color:var(--success);border:1px solid rgba(66,245,160,.25);cursor:default;pointer-events:none;box-shadow:none;transform:none;}
.btn-wa{background:rgba(37,211,102,.12);color:var(--wa);border:1px solid rgba(37,211,102,.25);}
.btn-wa:hover{background:var(--wa);color:#fff;border-color:var(--wa);}
.btn-call{background:rgba(66,200,245,.08);color:var(--sky);border:1px solid rgba(66,200,245,.2);}
.btn-call:hover{background:var(--sky);color:#001a1f;border-color:var(--sky);}

.divider-line{height:1px;background:var(--border);margin:4px 0;}

/* owner card */
.owner-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:18px;}
.owner-head{font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text-3);margin-bottom:14px;}
.owner-info{display:flex;align-items:center;gap:12px;}
.owner-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--rose));display:flex;align-items:center;justify-content:center;font-family:var(--ff-serif);font-size:1.1rem;color:#fff;flex-shrink:0;}
.owner-name{font-size:.95rem;font-weight:700;}
.owner-meta{font-size:.75rem;color:var(--text-3);margin-top:2px;}

/* like card */
.like-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:16px;display:flex;align-items:center;justify-content:space-between;}
.like-info{font-size:.82rem;color:var(--text-2);}
.like-info strong{color:var(--text);font-weight:700;}
.like-btn{padding:9px 20px;border-radius:100px;font-family:var(--ff-body);font-size:.82rem;font-weight:700;border:none;cursor:pointer;transition:all .25s var(--ease);}
.like-btn.off{background:rgba(245,106,143,.1);color:var(--rose);border:1px solid rgba(245,106,143,.25);}
.like-btn.off:hover{background:var(--rose);color:#fff;}
.like-btn.on{background:rgba(245,106,143,.2);color:var(--rose);border:1px solid rgba(245,106,143,.4);}

/* ── TOAST ── */
.toast-wrap{position:fixed;top:20px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{padding:12px 18px;border-radius:12px;font-size:.85rem;font-weight:500;backdrop-filter:blur(16px);border:1px solid;display:flex;align-items:center;gap:8px;min-width:240px;transform:translateX(120%);animation:tin .4s var(--ease) forwards;box-shadow:0 8px 24px rgba(0,0,0,.4);}
.toast.out{animation:tout .3s var(--ease) forwards;}
.toast.success{background:rgba(66,245,160,.1);border-color:rgba(66,245,160,.3);color:var(--success);}
.toast.warning{background:rgba(245,200,66,.1);border-color:rgba(245,200,66,.3);color:var(--gold);}

/* ── ANIM ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes tin{to{transform:translateX(0)}}
@keyframes tout{to{transform:translateX(120%);opacity:0}}

::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--border-l);border-radius:3px;}::-webkit-scrollbar-thumb:hover{background:var(--accent);}

@media(max-width:600px){nav{margin:10px 16px 0;border-radius:16px;padding:8px 12px 8px 16px;}.nav-links{display:none;}.hamburger{display:flex;}.page{padding:20px 14px 60px;}
  .main-swiper,
  .main-swiper .swiper-wrapper,
  .main-swiper .swiper-slide,
  .main-swiper .swiper-slide img,
  .main-swiper .swiper-slide video{height:260px !important;}
}
</style>
</head>
<body>

<div class="toast-wrap" id="toastWrap"></div>
<div class="orb orb-a"></div><div class="orb orb-b"></div>

<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu-close" onclick="toggleMobileMenu()">✕</div>
  <a href="findroom.php" onclick="toggleMobileMenu()">← Listings</a>
  <a href="dashboard.php" onclick="toggleMobileMenu()">Dashboard</a>
  <a href="post-room.php" onclick="toggleMobileMenu()">Post Room</a>
</div>

<!-- ── NAV ── -->
<nav>
  <a href="index.php" class="nav-logo">🏠 P2MDestiny</a>
  <div class="nav-links">
    <a href="findroom.php" class="back">← Back to Listings</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="post-room.php">Post Room</a>
  </div>
  <div class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- ── PAGE ── -->
<div class="page" style="animation:fadeUp .5s var(--ease) both">

  <!-- ══ LEFT ══ -->
  <div class="left-col">

    <!-- Main Gallery Swiper -->
    <div class="main-swiper-wrap">
      <?php if($fcount > 1): ?>
        <div class="media-chip">📷 <?php echo $fcount; ?> media</div>
      <?php endif; ?>

      <div class="swiper main-swiper" id="mainSwiper">
        <div class="swiper-wrapper">

          <?php
          /* ── FIX 2: foreach with $i key ── */
          foreach($files as $i => $file):
            $file = trim($file);
            if(!$file) continue;
            $base_url2 = 'https://landlink.gt.tc/';
            // DB mein 'uploads/filename' stored hai — basename() mat lagao
            if(strpos($file,'http')===0){
                $url = $file;
            } elseif(strpos($file,'uploads/')===0){
                $url = $base_url2.$file; // https://landlink.gt.tc/uploads/filename
            } else {
                $url = $base_url2.'uploads/'.basename($file);
            }
            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            $is_video = in_array($ext, ['mp4','webm','ogg','mov']);
            $mime_map = ['mp4'=>'video/mp4','webm'=>'video/webm','ogg'=>'video/ogg','mov'=>'video/mp4'];
            $mime = $mime_map[$ext] ?? 'video/mp4';
          ?>
          <div class="swiper-slide">

            <?php if($is_video): ?>
              <video
                id="slide-video-<?php echo $i; ?>"
                playsinline
                preload="none"
                onclick="toggleVideo(this)"
                style="width:100%;height:100%;object-fit:contain;background:#000;display:block;cursor:pointer;">
                <source src="<?php echo htmlspecialchars($url); ?>" type="<?php echo $mime; ?>">
              </video>
              <div class="play-overlay" id="overlay-<?php echo $i; ?>">▶️</div>

            <?php else: ?>
              <img src="<?php echo htmlspecialchars($url); ?>"
                   alt="Room photo <?php echo $i+1; ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php endif; ?>

          </div>
          <?php endforeach; ?>

        </div>
        <?php if($fcount > 1): ?>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Thumbnail Strip -->
    <?php if($fcount > 1): ?>
    <div class="thumbs-strip" id="thumbsStrip">
      <?php
      /* ── FIX 2: foreach with $i key in thumbs too ── */
      foreach($files as $i => $file):
        $file = trim($file);
        if(!$file) continue;
        $base_url3 = 'https://landlink.gt.tc/';
        if(strpos($file,'http')===0){
            $url = $file;
        } elseif(strpos($file,'uploads/')===0){
            $url = $base_url3.$file;
        } else {
            $url = $base_url3.'uploads/'.basename($file);
        }
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $is_video = in_array($ext, ['mp4','webm','ogg','mov']);
        ?>
      <div class="thumb <?php echo $i===0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>">
        <?php if($is_video): ?>
          <video muted playsinline preload="metadata"
            src="<?php echo htmlspecialchars($url); ?>">
          </video>
        <?php else: ?>
          <img src="<?php echo htmlspecialchars($url); ?>" alt="">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Title -->
    <div class="room-header">
      <div class="room-eyebrow">
        <?php
          $type_key = strtolower($row['room_type']??'single');
          $pill_class = in_array($type_key,['single','double','shared','pg']) ? 'pill-'.$type_key : 'pill-single';
        ?>
        <span class="type-pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($row['room_type']); ?></span>
        <?php if($already_booked): ?>
          <span style="font-size:.72rem;color:var(--success);font-weight:700;">✅ You booked this</span>
        <?php endif; ?>
      </div>
      <h1 class="room-title"><?php echo htmlspecialchars($row['title']); ?></h1>
      <div class="room-meta-row">
        <span class="meta-item">📍 <?php echo htmlspecialchars($row['city']); ?></span>
        <span class="meta-item">🛏️ <?php echo htmlspecialchars($row['room_type']); ?></span>
        <span class="meta-item">❤️ <?php echo $like_count; ?> likes</span>
        <?php if($avg_rating>0): ?>
        <span class="meta-item">⭐ <?php echo $avg_rating; ?>/5 (<?php echo $rating_cnt; ?> ratings)</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Description -->
    <?php if(!empty($row['description'])): ?>
    <div class="section-block">
      <div class="section-label">About this room</div>
      <p class="room-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Rating -->
    <div class="section-block">
      <div class="section-label">Ratings</div>
      <div class="rating-block">
        <div class="stars-display">
          <div>
            <div class="avg-score"><?php echo $avg_rating > 0 ? $avg_rating : '—'; ?></div>
            <div class="avg-label"><?php echo $rating_cnt > 0 ? "$rating_cnt rating".($rating_cnt>1?'s':'') : 'No ratings yet'; ?></div>
          </div>
          <div class="stars-big">
            <?php for($s=1;$s<=5;$s++): ?>
              <span class="star-big <?php echo $s<=$avg_rating?'filled':'empty'; ?>">★</span>
            <?php endfor; ?>
          </div>
        </div>

        <form method="POST" class="user-rating-form">
          <input type="hidden" name="rating" id="ratingVal" value="<?php echo $existing_rating?:3; ?>">
          <span class="rate-label"><?php echo $user_id_s ? 'Your rating:' : 'Login to rate'; ?></span>
          <?php if($user_id_s): ?>
          <div class="stars-interactive" id="starsInter">
            <?php for($s=1;$s<=5;$s++): ?>
              <span class="star-i <?php echo $s<=$existing_rating?'active':''; ?>"
                    onclick="pickRating(<?php echo $s; ?>)" data-v="<?php echo $s; ?>">★</span>
            <?php endfor; ?>
          </div>
          <button type="submit" name="rate" class="rate-submit" id="rateBtn">Submit Rating</button>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <!-- ══ RIGHT SIDEBAR ══ -->
  <div class="sidebar">

    <!-- Price + CTAs -->
    <div class="price-card">
      <div class="price-value">₹<?php echo number_format($row['price']); ?><span>/month</span></div>
      <div class="price-sub">All inclusive · Contact owner for details</div>

      <div class="cta-stack">
        <?php if($user_id_s): ?>
        <form method="POST" style="margin:0">
          <button name="book"
                  class="btn-cta btn-book <?php echo $already_booked?'booked':''; ?>"
                  <?php echo $already_booked?'disabled':''; ?>>
            <?php echo $already_booked ? '✅ Already Booked' : '📅 Book This Room'; ?>
          </button>
        </form>
        <?php else: ?>
        <a href="login.php" class="btn-cta btn-book">🔐 Login to Book</a>
        <?php endif; ?>

        <a href="https://wa.me/<?php echo $wa_number; ?>?text=Hi%2C+I%27m+interested+in+your+room+listing+%22<?php echo urlencode($row['title']); ?>%22"
           target="_blank" class="btn-cta btn-wa">
          💬 WhatsApp Owner
        </a>

        <a href="tel:<?php echo htmlspecialchars($row['contact_no']); ?>" class="btn-cta btn-call">
          📞 Call <?php echo htmlspecialchars($row['contact_no']); ?>
        </a>
      </div>

      <div class="divider-line"></div>

      <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px;">
        <div style="display:flex;justify-content:space-between;font-size:.8rem;">
          <span style="color:var(--text-3)">Room Type</span>
          <span style="font-weight:600"><?php echo htmlspecialchars($row['room_type']); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.8rem;">
          <span style="color:var(--text-3)">City</span>
          <span style="font-weight:600"><?php echo htmlspecialchars($row['city']); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.8rem;">
          <span style="color:var(--text-3)">Contact</span>
          <span style="font-weight:600"><?php echo htmlspecialchars($row['contact_no']); ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.8rem;">
          <span style="color:var(--text-3)">Media</span>
          <span style="font-weight:600"><?php echo $fcount; ?> file<?php echo $fcount!=1?'s':''; ?></span>
        </div>
      </div>
    </div>

    <!-- Owner -->
    <?php if(!empty($row['owner_name'])): ?>
    <div class="owner-card">
      <div class="owner-head">Listed by</div>
      <div class="owner-info">
        <div class="owner-avatar"><?php echo strtoupper(substr($row['owner_name'],0,1)); ?></div>
        <div>
          <div class="owner-name"><?php echo htmlspecialchars($row['owner_name']); ?></div>
          <div class="owner-meta">📞 <?php echo htmlspecialchars($row['contact_no']); ?></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Like -->
    <div class="like-card">
      <div class="like-info">
        <strong><?php echo $like_count; ?></strong> people liked this room
      </div>
      <?php if($user_id_s): ?>
      <form method="POST" style="margin:0">
        <button name="like" class="like-btn <?php echo $liked?'on':'off'; ?>">
          <?php echo $liked ? '❤️ Liked' : '🤍 Like'; ?>
        </button>
      </form>
      <?php else: ?>
      <a href="login.php" class="like-btn off" style="text-decoration:none">🤍 Like</a>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
// ─────────────────────────────────────────────
// FIX 1 & 4: Swiper init + video pause on slide change
// loop:false  →  videos ke saath reliable hai
// slideTo()   →  loop:false ke saath use karo (slideToLoop nahi)
// ─────────────────────────────────────────────
const mainSwiper = new Swiper('#mainSwiper', {
  loop: false,
  speed: 400,
  pagination: { el: '.swiper-pagination', clickable: true },
  navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
  on: {
    slideChange(swiper) {
      updateThumbs(swiper.activeIndex);
      pauseAllVideos();         // slide change hote hi sab videos pause
      loadVideoOnSlide(swiper.activeIndex); // nayi slide ka video load karo
    }
  }
});

// ─────────────────────────────────────────────
// FIX 4: Play/Pause overlay — sibling nahi, ID se dhundo
// toggleVideo() video click pe call hota hai
// ─────────────────────────────────────────────
function toggleVideo(videoEl) {
  const overlayId = videoEl.id.replace('slide-video-', 'overlay-');
  const overlay   = document.getElementById(overlayId);

  // Agar load nahi hua to pehle load karo
  if (!videoEl.getAttribute('data-loaded')) {
    videoEl.load();
    videoEl.setAttribute('data-loaded', '1');
  }

  if (videoEl.paused) {
    videoEl.play().then(() => {
      if (overlay) overlay.classList.add('hidden');
    }).catch(() => {
      // autoplay blocked — show overlay
      if (overlay) overlay.classList.remove('hidden');
    });
  } else {
    videoEl.pause();
    if (overlay) overlay.classList.remove('hidden');
  }
}

function pauseAllVideos() {
  document.querySelectorAll('#mainSwiper video').forEach(v => {
    if (!v.paused) {
      v.pause();
    }
    v.currentTime = 0;
    // overlay wapas dikhao
    const overlayId = v.id.replace('slide-video-', 'overlay-');
    const overlay   = document.getElementById(overlayId);
    if (overlay) overlay.classList.remove('hidden');
  });
}

function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  const ham  = document.getElementById('hamburger');
  menu.classList.toggle('open');
  ham.classList.toggle('open');
  document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
}

// Video loading fix: jab slide active ho tab hi load karo
function loadVideoOnSlide(index) {
  const slides = document.querySelectorAll('#mainSwiper .swiper-slide');
  if (slides[index]) {
    const video = slides[index].querySelector('video');
    if (video) {
      const source = video.querySelector('source');
      if (source && !video.getAttribute('data-loaded')) {
        video.load();
        video.setAttribute('data-loaded', '1');
      }
    }
  }
}

// Pehli slide pe video load karo on page load
document.addEventListener('DOMContentLoaded', () => loadVideoOnSlide(0));

// video ended → overlay wapas dikhao
document.querySelectorAll('#mainSwiper video').forEach(v => {
  v.addEventListener('ended', () => {
    const overlayId = v.id.replace('slide-video-', 'overlay-');
    const overlay   = document.getElementById(overlayId);
    if (overlay) overlay.classList.remove('hidden');
  });
});

// ─────────────────────────────────────────────
// THUMBS: slideTo() use karo (loop:false ke saath)
// ─────────────────────────────────────────────
document.querySelectorAll('.thumb').forEach(thumb => {
  thumb.addEventListener('click', () => {
    const idx = parseInt(thumb.dataset.index, 10);
    pauseAllVideos();
    mainSwiper.slideTo(idx);   // slideToLoop nahi — loop:false hai
    updateThumbs(idx);
  });
});

function updateThumbs(activeIdx) {
  document.querySelectorAll('.thumb').forEach((t, i) => {
    t.classList.toggle('active', i === activeIdx);
  });
}

// ─────────────────────────────────────────────
// STAR RATING
// ─────────────────────────────────────────────
function pickRating(val) {
  document.getElementById('ratingVal').value = val;
  document.querySelectorAll('.star-i').forEach((s, i) => {
    s.classList.toggle('active', i < val);
  });
  const btn = document.getElementById('rateBtn');
  if (btn) btn.classList.add('show');
}

// ─────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────
function showToast(msg, type='success') {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.innerHTML = (type === 'success' ? '✅' : '⚠️') + ' ' + msg;
  w.appendChild(t);
  setTimeout(() => {
    t.classList.add('out');
    setTimeout(() => t.remove(), 400);
  }, 3500);
}

<?php if(isset($_GET['booked'])): ?> showToast('📅 Room booked successfully!', 'success'); <?php endif; ?>
<?php if(isset($_GET['rated'])):  ?> showToast('⭐ Rating submitted, thanks!',  'success'); <?php endif; ?>
</script>
</body>
</html>