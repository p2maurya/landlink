<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

include("db.php");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

$user_id = (int)$_SESSION['user_id'];

// My posted rooms (Seller)
$my_rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE user_id=$user_id ORDER BY id DESC");
if(!$my_rooms) die("Query Failed: " . mysqli_error($conn));
$my_count = mysqli_num_rows($my_rooms);

// All available rooms (Renter view — exclude own)
$all_rooms = mysqli_query($conn, "SELECT r.*, u.username as owner_name FROM rooms r JOIN users u ON r.user_id = u.id WHERE r.user_id != $user_id ORDER BY r.id DESC");
if(!$all_rooms) die("Query Failed: " . mysqli_error($conn));
$all_count = mysqli_num_rows($all_rooms);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — RoomEase</title>

<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Cabinet+Grotesk:wght@300;400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
/* ─── RESET ─── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

:root {
  --bg:        #080810;
  --surface:   #0e0e1a;
  --card:      #12121e;
  --card-h:    #161628;
  --border:    #1e1e32;
  --border-l:  #2c2c48;
  --accent:    #6c5ce7;
  --accent-g:  #a29bfe;
  --gold:      #ffd32a;
  --gold-d:    #e6b800;
  --emerald:   #00cec9;
  --rose:      #fd79a8;
  --text:      #eeeef8;
  --text-2:    #9898b8;
  --text-3:    #58587a;
  --danger:    #e17055;
  --success:   #00b894;
  --ff-head:   'Clash Display', sans-serif;
  --ff-body:   'Cabinet Grotesk', sans-serif;
  --r:         18px;
  --r-sm:      12px;
  --ease:      cubic-bezier(0.4,0,0.2,1);
  --shadow:    0 20px 60px rgba(0,0,0,0.6);
}

html { scroll-behavior: smooth; }

body {
  font-family: var(--ff-body);
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
}

/* ─── GRAIN ─── */
body::after {
  content:'';
  position:fixed; inset:0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
  pointer-events:none; z-index:999; opacity:.5;
}

/* ─── AMBIENT ─── */
.orb { position:fixed; border-radius:50%; filter:blur(130px); pointer-events:none; z-index:0; }
.orb-1 { width:700px; height:700px; background:rgba(108,92,231,0.09); top:-300px; right:-200px; }
.orb-2 { width:500px; height:500px; background:rgba(253,121,168,0.06); bottom:-150px; left:-150px; }
.orb-3 { width:350px; height:350px; background:rgba(0,206,201,0.05); top:50%; left:40%; }

/* ─── NAVBAR ─── */
.navbar {
  position: sticky; top:0; z-index:100;
  display:flex; align-items:center; justify-content:space-between;
  padding:16px 40px;
  background: rgba(8,8,16,0.85);
  backdrop-filter: blur(24px);
  border-bottom: 1px solid var(--border);
}

.nav-logo {
  font-family: var(--ff-head);
  font-size: 1.3rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--accent-g), var(--rose));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  letter-spacing: -0.02em;
}

.nav-links { display:flex; align-items:center; gap:6px; }
.nav-links a {
  padding: 8px 16px;
  border-radius: 100px;
  color: var(--text-2);
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  transition: all 0.25s var(--ease);
  border: 1px solid transparent;
}
.nav-links a:hover {
  color: var(--text);
  background: rgba(255,255,255,0.05);
  border-color: var(--border-l);
}
.nav-links a.cta {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
}
.nav-links a.cta:hover { background: #7c6ef0; box-shadow: 0 0 20px rgba(108,92,231,0.4); }
.nav-links a.danger { color: var(--danger); }
.nav-links a.danger:hover { background: rgba(225,112,85,0.1); border-color: rgba(225,112,85,0.3); color: var(--danger); }

.nav-user {
  display:flex; align-items:center; gap:10px;
  padding: 6px 14px 6px 6px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 100px;
}
.nav-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--rose));
  display:flex; align-items:center; justify-content:center;
  font-family: var(--ff-head); font-size: 0.8rem; font-weight: 700; color: white;
}
.nav-name { font-size: 0.85rem; font-weight: 600; }

/* ─── HERO STRIP ─── */
.hero-strip {
  position: relative; z-index:1;
  padding: 48px 40px 0;
  animation: fadeUp 0.5s var(--ease) both;
}
.hero-greeting {
  font-size: 0.78rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--accent-g); margin-bottom: 8px;
}
.hero-title {
  font-family: var(--ff-head);
  font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 700; letter-spacing: -0.03em; line-height: 1.1;
  margin-bottom: 24px;
}
.hero-title em { font-style: normal; color: var(--text-2); }

/* ─── STATS ROW ─── */
.stats-row {
  display: flex; gap: 14px; flex-wrap: wrap;
  margin-bottom: 36px;
}
.stat-chip {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 20px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 100px;
  font-size: 0.85rem; font-weight: 500; color: var(--text-2);
  transition: all 0.25s var(--ease);
}
.stat-chip:hover { border-color: var(--border-l); background: var(--card-h); }
.stat-chip .dot {
  width: 8px; height: 8px; border-radius: 50%;
}
.stat-chip strong { color: var(--text); font-weight: 700; font-size: 1rem; font-family: var(--ff-head); }

/* ─── TABS ─── */
.tabs-wrap {
  position: relative; z-index:1;
  padding: 0 40px;
  margin-bottom: 32px;
  animation: fadeUp 0.5s 0.1s var(--ease) both;
}
.tabs {
  display: inline-flex; gap: 4px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 5px;
}
.tab-btn {
  padding: 10px 24px;
  border-radius: 10px;
  border: none; background: transparent;
  color: var(--text-2); font-family: var(--ff-body);
  font-size: 0.88rem; font-weight: 600; cursor: pointer;
  transition: all 0.25s var(--ease);
  display: flex; align-items: center; gap: 8px;
}
.tab-btn .count {
  padding: 2px 8px; border-radius: 100px;
  background: rgba(255,255,255,0.07);
  font-size: 0.72rem; font-weight: 700;
}
.tab-btn.active {
  background: var(--accent);
  color: white;
  box-shadow: 0 4px 16px rgba(108,92,231,0.4);
}
.tab-btn.active .count { background: rgba(255,255,255,0.2); }
.tab-btn:not(.active):hover { background: rgba(255,255,255,0.05); color: var(--text); }

/* ─── CONTENT PANELS ─── */
.panel { display: none; }
.panel.active { display: block; }

/* ─── SECTION HEADER ─── */
.section-head {
  position: relative; z-index:1;
  display:flex; align-items:center; justify-content:space-between;
  padding: 0 40px;
  margin-bottom: 24px;
}
.section-head h2 {
  font-family: var(--ff-head);
  font-size: 1.1rem; font-weight: 700;
  display:flex; align-items:center; gap:10px;
}
.section-head h2 .icon {
  width:36px; height:36px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  font-size: 1rem;
}
.icon-seller { background: rgba(108,92,231,0.2); }
.icon-renter { background: rgba(0,206,201,0.2); }

.add-btn {
  display:flex; align-items:center; gap:8px;
  padding: 9px 20px;
  background: var(--accent); color: white;
  border:none; border-radius: 100px;
  font-family: var(--ff-body); font-size: 0.83rem; font-weight: 600;
  cursor:pointer; text-decoration:none;
  transition: all 0.25s var(--ease);
  box-shadow: 0 4px 16px rgba(108,92,231,0.3);
}
.add-btn:hover { background: #7c6ef0; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(108,92,231,0.45); }

/* ─── GRID ─── */
.rooms-grid {
  position: relative; z-index:1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 22px;
  padding: 0 40px 60px;
}

/* ─── CARD ─── */
.room-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
  transition: transform 0.3s var(--ease), border-color 0.3s var(--ease), box-shadow 0.3s var(--ease);
  animation: fadeUp 0.5s var(--ease) both;
  display: flex; flex-direction: column;
}
.room-card:hover {
  transform: translateY(-6px);
  border-color: var(--border-l);
  box-shadow: 0 24px 60px rgba(0,0,0,0.5);
}

/* MEDIA SLIDER */
.card-media {
  position: relative;
  height: 200px;
  overflow: hidden;
  background: var(--surface);
}
.card-media .swiper { width:100%; height:200px; }
.card-media img,
.card-media video {
  width:100%; height:200px;
  object-fit:cover; display:block;
}
.card-media video { cursor:pointer; }

/* swiper pagination dots */
.swiper-pagination-bullet { background: rgba(255,255,255,0.5); opacity:1; }
.swiper-pagination-bullet-active { background: white; transform: scale(1.2); }

/* media count badge */
.media-count {
  position:absolute; top:10px; right:10px; z-index:5;
  padding: 3px 10px; border-radius:100px;
  background: rgba(0,0,0,0.6); backdrop-filter:blur(8px);
  font-size: 0.7rem; font-weight:600; color:white;
  border: 1px solid rgba(255,255,255,0.1);
}

/* card type pill */
.type-pill {
  position:absolute; top:10px; left:10px; z-index:5;
  padding: 3px 12px; border-radius:100px;
  font-size: 0.68rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase;
}
.pill-single  { background:rgba(108,92,231,0.85); color:white; }
.pill-double  { background:rgba(0,206,201,0.85);  color:#001f1e; }
.pill-pg      { background:rgba(253,121,168,0.85); color:#3d0020; }

/* ─── CARD BODY ─── */
.card-body { padding: 18px 18px 16px; flex:1; display:flex; flex-direction:column; }

.card-title-row { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:10px; }

.card-title {
  font-family: var(--ff-head);
  font-size: 1rem; font-weight: 700;
  line-height: 1.3; color: var(--text);
  text-decoration: none;
  transition: color 0.2s;
}
.card-title:hover { color: var(--accent-g); }

.price-badge {
  font-family: var(--ff-head);
  font-size: 0.95rem; font-weight: 700;
  color: var(--gold);
  white-space: nowrap;
  flex-shrink: 0;
}

.card-meta {
  display:flex; gap:14px; flex-wrap:wrap;
  margin-bottom: 12px;
}
.meta-item {
  display:flex; align-items:center; gap:5px;
  font-size: 0.78rem; color: var(--text-2); font-weight: 500;
}

.card-desc {
  font-size: 0.82rem; color: var(--text-3);
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow:hidden;
  margin-bottom: 16px;
  flex:1;
}

/* owner chip (renter view) */
.owner-chip {
  display:inline-flex; align-items:center; gap:6px;
  padding: 5px 12px;
  background: rgba(0,206,201,0.08);
  border: 1px solid rgba(0,206,201,0.2);
  border-radius: 100px;
  font-size: 0.75rem; color: var(--emerald); font-weight: 600;
  margin-bottom: 14px;
}

/* ─── ACTIONS ─── */
.card-actions { display:flex; gap:8px; margin-top:auto; }
.btn {
  flex:1; padding: 10px; border-radius: var(--r-sm);
  font-family: var(--ff-body); font-size: 0.82rem; font-weight: 600;
  border:none; cursor:pointer; text-decoration:none;
  display:flex; align-items:center; justify-content:center; gap:6px;
  transition: all 0.25s var(--ease);
}
.btn-view   { background:rgba(108,92,231,0.12); color:var(--accent-g); border:1px solid rgba(108,92,231,0.25); }
.btn-view:hover { background:var(--accent); color:white; border-color:var(--accent); box-shadow:0 4px 16px rgba(108,92,231,0.35); }
.btn-edit   { background:rgba(0,206,201,0.08);  color:var(--emerald); border:1px solid rgba(0,206,201,0.2); }
.btn-edit:hover { background:var(--emerald); color:#001f1e; border-color:var(--emerald); }
.btn-del    { background:rgba(225,112,85,0.08); color:var(--danger); border:1px solid rgba(225,112,85,0.2); }
.btn-del:hover  { background:var(--danger); color:white; border-color:var(--danger); }
.btn-contact { background:rgba(0,184,148,0.1); color:var(--success); border:1px solid rgba(0,184,148,0.25); }
.btn-contact:hover { background:var(--success); color:white; border-color:var(--success); }

/* ─── EMPTY STATE ─── */
.empty-state {
  grid-column: 1 / -1;
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding: 80px 20px; text-align:center;
}
.empty-icon { font-size: 4rem; margin-bottom:16px; opacity:0.4; }
.empty-title { font-family:var(--ff-head); font-size:1.3rem; font-weight:700; color:var(--text-2); margin-bottom:8px; }
.empty-sub { font-size:0.88rem; color:var(--text-3); margin-bottom:28px; }

/* ─── SEARCH / FILTER BAR ─── */
.filter-bar {
  position:relative; z-index:1;
  display:flex; align-items:center; gap:10px;
  padding: 0 40px; margin-bottom:24px;
  flex-wrap:wrap;
}
.search-wrap {
  flex:1; min-width:200px; position:relative;
}
.search-icon {
  position:absolute; left:14px; top:50%; transform:translateY(-50%);
  color:var(--text-3); pointer-events:none; font-size:0.9rem;
}
.search-input {
  width:100%; padding: 11px 16px 11px 40px;
  background:var(--card); border:1px solid var(--border);
  border-radius: 12px; color:var(--text); font-family:var(--ff-body);
  font-size: 0.88rem; outline:none;
  transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease);
}
.search-input::placeholder { color:var(--text-3); }
.search-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(108,92,231,0.12); }

.filter-select {
  padding: 11px 16px;
  background:var(--card); border:1px solid var(--border);
  border-radius:12px; color:var(--text); font-family:var(--ff-body);
  font-size:0.88rem; outline:none; cursor:pointer;
  transition: border-color 0.25s var(--ease);
}
.filter-select:focus { border-color:var(--accent); }
.filter-select option { background:var(--surface); }

/* ─── DIVIDER ─── */
.divider {
  height:1px; background:var(--border);
  margin: 0 40px 32px;
}

/* ─── TOAST ─── */
.toast-wrap { position:fixed; top:20px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; }
.toast {
  padding:12px 18px; border-radius:12px;
  font-size:0.85rem; font-weight:500;
  backdrop-filter:blur(16px); border:1px solid;
  display:flex; align-items:center; gap:8px;
  transform:translateX(120%);
  animation: tin 0.4s var(--ease) forwards;
  box-shadow:0 8px 24px rgba(0,0,0,0.4);
}
.toast.out { animation: tout 0.3s var(--ease) forwards; }
.toast.success { background:rgba(0,184,148,0.12); border-color:rgba(0,184,148,0.3); color:var(--success); }
.toast.error   { background:rgba(225,112,85,0.12);  border-color:rgba(225,112,85,0.3);  color:var(--danger); }

/* ─── ANIM ─── */
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
@keyframes tin  { to{transform:translateX(0)} }
@keyframes tout { to{transform:translateX(120%);opacity:0} }

/* ─── SCROLL ─── */
::-webkit-scrollbar { width:5px; }
::-webkit-scrollbar-track { background:var(--bg); }
::-webkit-scrollbar-thumb { background:var(--border-l); border-radius:3px; }
::-webkit-scrollbar-thumb:hover { background:var(--accent); }

/* ─── RESPONSIVE ─── */
@media(max-width:700px){
  .navbar { padding:14px 20px; }
  .hero-strip, .tabs-wrap, .section-head, .filter-bar, .rooms-grid { padding-left:20px; padding-right:20px; }
  .nav-links { display:none; }
  .rooms-grid { grid-template-columns:1fr; }
}
</style>
</head>
<body>

<div class="toast-wrap" id="toastWrap"></div>
<div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>

<!-- ─── NAVBAR ─── -->
<nav class="navbar">
  <div class="nav-logo">🏠 RoomEase</div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="findroom.php">Find Room</a>
    <a href="post-room.php" class="cta">+ Post Room</a>
    <a href="logout.php" class="danger">Logout</a>
  </div>
  <div class="nav-user">
    <div class="nav-avatar"><?php echo strtoupper(substr($_SESSION['username'],0,1)); ?></div>
    <span class="nav-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
  </div>
</nav>

<!-- ─── HERO ─── -->
<div class="hero-strip">
  <div class="hero-greeting">👋 Welcome back</div>
  <h1 class="hero-title"><?php echo htmlspecialchars($_SESSION['username']); ?>'s <em>Dashboard</em></h1>

  <div class="stats-row">
    <div class="stat-chip">
      <div class="dot" style="background:#6c5ce7;box-shadow:0 0 8px #6c5ce7;"></div>
      <strong><?php echo $my_count; ?></strong> My Listings
    </div>
    <div class="stat-chip">
      <div class="dot" style="background:#00cec9;box-shadow:0 0 8px #00cec9;"></div>
      <strong><?php echo $all_count; ?></strong> Available Rooms
    </div>
    <div class="stat-chip">
      <div class="dot" style="background:#ffd32a;box-shadow:0 0 8px #ffd32a;"></div>
      Active Account
    </div>
  </div>
</div>

<!-- ─── TABS ─── -->
<div class="tabs-wrap">
  <div class="tabs">
    <button class="tab-btn active" id="tab-seller" onclick="switchTab('seller')">
      🏷️ My Listings <span class="count"><?php echo $my_count; ?></span>
    </button>
    <button class="tab-btn" id="tab-renter" onclick="switchTab('renter')">
      🔍 Find a Room <span class="count"><?php echo $all_count; ?></span>
    </button>
  </div>
</div>

<!-- ══════════ SELLER PANEL ══════════ -->
<div class="panel active" id="panel-seller">

  <div class="section-head">
    <h2><span class="icon icon-seller">🏷️</span> My Posted Rooms</h2>
    <a href="post-room.php" class="add-btn">+ Add New Room</a>
  </div>

  <div class="rooms-grid" id="seller-grid">
  <?php
  if($my_count > 0):
    mysqli_data_seek($my_rooms, 0);
    $i=0;
    while($room = mysqli_fetch_assoc($my_rooms)):
      $files = !empty($room['image']) ? array_filter(array_map('trim', explode(",", $room['image']))) : [];
      $files = array_values($files);
      $fcount = count($files);
      $type_class = 'pill-'.strtolower($room['room_type']);
      $i++;
  ?>
  <div class="room-card" style="animation-delay:<?php echo ($i*0.07); ?>s">

    <!-- Media Slider -->
    <div class="card-media">
      <?php if($fcount > 1): ?>
        <div class="media-count">📷 <?php echo $fcount; ?></div>
      <?php endif; ?>
      <span class="type-pill <?php echo $type_class; ?>"><?php echo $room['room_type']; ?></span>

      <?php if($fcount > 0): ?>
      <div class="swiper card-swiper">
        <div class="swiper-wrapper">
          <?php foreach($files as $file):
            if(!$file) continue;
            $path = (strpos($file,'uploads/')===false) ? "uploads/".$file : $file;
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            ?>
            <div class="swiper-slide">
              <?php if(in_array($ext,['mp4','webm','ogg'])): ?>
                <video muted playsinline loop><source src="<?php echo htmlspecialchars($path); ?>"></video>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($path); ?>" alt="Room" loading="lazy">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if($fcount > 1): ?><div class="swiper-pagination"></div><?php endif; ?>
      </div>
      <?php else: ?>
        <div style="height:200px;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:2rem;">🏠</div>
      <?php endif; ?>
    </div>

    <!-- Body -->
    <div class="card-body">
      <div class="card-title-row">
        <a href="room-details.php?id=<?php echo $room['id']; ?>" class="card-title"><?php echo htmlspecialchars($room['title']); ?></a>
        <span class="price-badge">₹<?php echo number_format($room['price']); ?>/mo</span>
      </div>
      <div class="card-meta">
        <span class="meta-item">📍 <?php echo htmlspecialchars($room['city']); ?></span>
        <span class="meta-item">📞 <?php echo htmlspecialchars($room['contact_no']); ?></span>
      </div>
      <p class="card-desc"><?php echo htmlspecialchars($room['description']); ?></p>

      <div class="card-actions">
        <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-view">👁 View</a>
        <a href="edit-room.php?id=<?php echo $room['id']; ?>" class="btn btn-edit">✏️ Edit</a>
        <a href="delete-room.php?id=<?php echo $room['id']; ?>"
           class="btn btn-del"
           onclick="return confirmDelete(event, this)">🗑 Delete</a>
      </div>
    </div>
  </div>
  <?php endwhile; else: ?>
  <div class="empty-state">
    <div class="empty-icon">🏠</div>
    <div class="empty-title">No rooms listed yet</div>
    <div class="empty-sub">Start earning by posting your first room</div>
    <a href="post-room.php" class="add-btn">+ Post Your First Room</a>
  </div>
  <?php endif; ?>
  </div>
</div>

<!-- ══════════ RENTER PANEL ══════════ -->
<div class="panel" id="panel-renter">

  <div class="section-head">
    <h2><span class="icon icon-renter">🔍</span> Available Rooms</h2>
  </div>

  <!-- Filter Bar -->
  <div class="filter-bar">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" class="search-input" id="renterSearch" placeholder="Search by title, city…" oninput="filterRenter()">
    </div>
    <select class="filter-select" id="renterType" onchange="filterRenter()">
      <option value="">All Types</option>
      <option>Single</option>
      <option>Double</option>
      <option>PG</option>
    </select>
    <select class="filter-select" id="renterSort" onchange="filterRenter()">
      <option value="newest">Newest First</option>
      <option value="price_asc">Price ↑</option>
      <option value="price_desc">Price ↓</option>
    </select>
  </div>

  <div class="rooms-grid" id="renter-grid">
  <?php
  if($all_count > 0):
    mysqli_data_seek($all_rooms, 0);
    $j=0;
    while($room = mysqli_fetch_assoc($all_rooms)):
      $files = !empty($room['image']) ? array_filter(array_map('trim', explode(",", $room['image']))) : [];
      $files = array_values($files);
      $fcount = count($files);
      $type_class = 'pill-'.strtolower($room['room_type']);
      $j++;
  ?>
  <div class="room-card renter-card"
       data-title="<?php echo strtolower(htmlspecialchars($room['title'].' '.$room['city'])); ?>"
       data-type="<?php echo $room['room_type']; ?>"
       data-price="<?php echo $room['price']; ?>"
       data-id="<?php echo $room['id']; ?>"
       style="animation-delay:<?php echo ($j*0.07); ?>s">

    <div class="card-media">
      <?php if($fcount > 1): ?>
        <div class="media-count">📷 <?php echo $fcount; ?></div>
      <?php endif; ?>
      <span class="type-pill <?php echo $type_class; ?>"><?php echo $room['room_type']; ?></span>

      <?php if($fcount > 0): ?>
      <div class="swiper card-swiper">
        <div class="swiper-wrapper">
          <?php foreach($files as $file):
            if(!$file) continue;
            $path = (strpos($file,'uploads/')===false) ? "uploads/".$file : $file;
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            ?>
            <div class="swiper-slide">
              <?php if(in_array($ext,['mp4','webm','ogg'])): ?>
                <video muted playsinline loop><source src="<?php echo htmlspecialchars($path); ?>"></video>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($path); ?>" alt="Room" loading="lazy">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if($fcount > 1): ?><div class="swiper-pagination"></div><?php endif; ?>
      </div>
      <?php else: ?>
        <div style="height:200px;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:2rem;">🏠</div>
      <?php endif; ?>
    </div>

    <div class="card-body">
      <div class="card-title-row">
        <a href="room-details.php?id=<?php echo $room['id']; ?>" class="card-title"><?php echo htmlspecialchars($room['title']); ?></a>
        <span class="price-badge">₹<?php echo number_format($room['price']); ?>/mo</span>
      </div>
      <div class="card-meta">
        <span class="meta-item">📍 <?php echo htmlspecialchars($room['city']); ?></span>
      </div>
      <div class="owner-chip">👤 <?php echo htmlspecialchars($room['owner_name']); ?></div>
      <p class="card-desc"><?php echo htmlspecialchars($room['description']); ?></p>

      <div class="card-actions">
        <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-view">👁 View Details</a>
        <a href="tel:<?php echo htmlspecialchars($room['contact_no']); ?>" class="btn btn-contact">📞 Contact</a>
      </div>
    </div>
  </div>
  <?php endwhile; else: ?>
  <div class="empty-state">
    <div class="empty-icon">🔍</div>
    <div class="empty-title">No rooms available right now</div>
    <div class="empty-sub">Check back later for new listings</div>
  </div>
  <?php endif; ?>
  </div>

  <div id="no-results" style="display:none;" class="empty-state" style="grid-column:1/-1">
    <div class="empty-icon">😕</div>
    <div class="empty-title">No rooms match your search</div>
    <div class="empty-sub">Try different keywords or filters</div>
  </div>
</div>

<!-- ─── SCRIPTS ─── -->
<script>
// ── TABS ──
function switchTab(tab) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('panel-'+tab).classList.add('active');
  document.getElementById('tab-'+tab).classList.add('active');
  initSwipers();
}

// ── SWIPER INIT ──
function initSwipers() {
  document.querySelectorAll('.card-swiper:not(.swiper-initialized)').forEach(el => {
    new Swiper(el, {
      loop: el.querySelectorAll('.swiper-slide').length > 1,
      autoplay: { delay: 3000, disableOnInteraction: false },
      pagination: el.querySelector('.swiper-pagination') ? { el: el.querySelector('.swiper-pagination'), clickable: true } : false,
      on: {
        slideChange() {
          // play video in current slide
          el.querySelectorAll('video').forEach(v => v.pause());
          const active = el.querySelector('.swiper-slide-active video');
          if(active) active.play();
        }
      }
    });
  });
}
document.addEventListener('DOMContentLoaded', initSwipers);

// video hover
document.querySelectorAll('.card-media video').forEach(v => {
  v.closest('.card-media').addEventListener('mouseenter', () => { if(!v.closest('.swiper-slide-active')) return; v.play(); });
  v.closest('.card-media').addEventListener('mouseleave', () => { v.pause(); v.currentTime=0; });
});

// ── RENTER FILTER ──
function filterRenter() {
  const q    = document.getElementById('renterSearch').value.toLowerCase();
  const type = document.getElementById('renterType').value;
  const sort = document.getElementById('renterSort').value;
  const grid = document.getElementById('renter-grid');
  const cards = [...grid.querySelectorAll('.renter-card')];

  let visible = cards.filter(c => {
    const matchQ    = !q || c.dataset.title.includes(q);
    const matchType = !type || c.dataset.type === type;
    return matchQ && matchType;
  });

  // Sort
  visible.sort((a,b) => {
    if(sort === 'price_asc')  return +a.dataset.price - +b.dataset.price;
    if(sort === 'price_desc') return +b.dataset.price - +a.dataset.price;
    return +b.dataset.id - +a.dataset.id; // newest
  });

  cards.forEach(c => c.style.display='none');
  visible.forEach((c,i) => { c.style.display='flex'; c.style.animationDelay=(i*0.04)+'s'; });

  document.getElementById('no-results').style.display = visible.length ? 'none' : 'block';
}

// ── DELETE CONFIRM ──
function confirmDelete(e, el) {
  e.preventDefault();
  if(confirm('🗑️ Are you sure you want to delete this room? This cannot be undone.')) {
    window.location = el.href;
  }
  return false;
}

// ── TOAST ──
function showToast(msg, type='success') {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = (type==='success'?'✅':'⚠️') + ' ' + msg;
  w.appendChild(t);
  setTimeout(()=>{ t.classList.add('out'); setTimeout(()=>t.remove(),400); }, 3500);
}

<?php if(isset($_GET['posted'])): ?>
showToast('🚀 Room posted successfully!', 'success');
<?php endif; ?>
</script>

</body>
</html>