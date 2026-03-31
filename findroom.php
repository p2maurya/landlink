<?php
session_start();
include("db.php");

/* ── LIKE ── */
if(isset($_POST['like'])){
    if(isset($_SESSION['user_id'])){
        $uid = (int)$_SESSION['user_id'];
        $rid = (int)$_POST['room_id'];
        $check = mysqli_query($conn, "SELECT id FROM likes WHERE user_id=$uid AND room_id=$rid");
        if(mysqli_num_rows($check) > 0){
            mysqli_query($conn, "DELETE FROM likes WHERE user_id=$uid AND room_id=$rid");
        } else {
            mysqli_query($conn, "INSERT IGNORE INTO likes (user_id, room_id) VALUES ($uid,$rid)");
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
        exit;
    } else {
        header("Location: login.php"); exit;
    }
}

/* ── RATING ── */
if(isset($_POST['rate'])){
    if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
    $uid    = (int)$_SESSION['user_id'];
    $rid    = (int)$_POST['room_id'];
    $rating = max(1, min(5, (int)$_POST['rating']));
    mysqli_query($conn, "INSERT INTO ratings (user_id, room_id, rating)
        VALUES ($uid,$rid,$rating)
        ON DUPLICATE KEY UPDATE rating=$rating");
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit;
}

/* ── BOOK ── */
if(isset($_POST['book'])){
    if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }
    $user_id = (int)$_SESSION['user_id'];
    $room_id = (int)$_POST['room_id'];
    $already = mysqli_query($conn, "SELECT id FROM bookings WHERE user_id=$user_id AND room_id=$room_id");
    if(mysqli_num_rows($already) > 0){
        $booking_msg = "already";
    } else {
        mysqli_query($conn, "INSERT INTO bookings (user_id, room_id) VALUES ($user_id, $room_id)");
        $booking_msg = "success";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "&booked=$booking_msg");
    exit;
}

/* ── FILTER ── */
$city   = isset($_GET['city'])   ? mysqli_real_escape_string($conn, strtolower(trim($_GET['city']))) : '';
$type   = isset($_GET['type'])   ? mysqli_real_escape_string($conn, strtolower(trim($_GET['type']))) : '';
$budget = isset($_GET['budget']) ? (int)$_GET['budget'] : 0;

$query = "SELECT r.*, u.username as owner_name
          FROM rooms r
          LEFT JOIN users u ON r.user_id = u.id
          WHERE 1";
if($city   != '') $query .= " AND LOWER(r.city) LIKE '%$city%'";
if($type   != '') $query .= " AND (LOWER(r.room_type) LIKE '%$type%' OR LOWER(r.title) LIKE '%$type%')";
if($budget  > 0)  $query .= " AND r.price <= $budget";
$query .= " ORDER BY r.id DESC";

$result     = mysqli_query($conn, $query);
$total      = $result ? mysqli_num_rows($result) : 0;
$user_id_s  = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Rooms — RoomEase</title>

<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Epilogue:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<style>
/* ── RESET ── */
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

/* grain */
body::after{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");pointer-events:none;z-index:999;opacity:.5;}

/* orbs */
.orb{position:fixed;border-radius:50%;filter:blur(140px);pointer-events:none;z-index:0;}
.orb-a{width:800px;height:800px;background:rgba(124,106,245,0.08);top:-300px;right:-300px;}
.orb-b{width:500px;height:500px;background:rgba(245,106,143,0.06);bottom:-200px;left:-200px;}
.orb-c{width:400px;height:400px;background:rgba(66,245,200,0.04);top:40%;left:30%;}

/* ── NAV ── */
nav{
  position:sticky;top:0;z-index:200;
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 48px;
  background:rgba(7,7,15,0.88);
  backdrop-filter:blur(28px);
  border-bottom:1px solid var(--border);
}
.nav-logo{
  font-family:var(--ff-serif);
  font-size:1.5rem;
  background:linear-gradient(120deg, var(--accent-l), var(--mint));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  letter-spacing:-0.01em;
}
.nav-links{display:flex;align-items:center;gap:4px;}
.nav-links a{
  padding:7px 16px;border-radius:100px;
  color:var(--text-2);text-decoration:none;
  font-size:0.85rem;font-weight:500;
  border:1px solid transparent;
  transition:all .25s var(--ease);
}
.nav-links a:hover{color:var(--text);background:rgba(255,255,255,0.05);border-color:var(--border-l);}
.nav-links a.active{color:var(--accent-l);background:rgba(124,106,245,0.1);border-color:rgba(124,106,245,0.3);}
.nav-links a.nav-cta{background:var(--accent);color:white;border-color:var(--accent);}
.nav-links a.nav-cta:hover{background:#6b59e6;box-shadow:0 0 20px rgba(124,106,245,0.4);}

/* ── HERO ── */
.hero{
  position:relative;z-index:1;
  padding:64px 48px 40px;
  text-align:center;
  animation:fadeUp .5s var(--ease) both;
}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:6px;
  padding:5px 16px;border-radius:100px;
  background:rgba(124,106,245,0.12);
  border:1px solid rgba(124,106,245,0.25);
  font-size:0.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:var(--accent-l);margin-bottom:18px;
}
.hero-title{
  font-family:var(--ff-serif);
  font-size:clamp(2.2rem,5vw,3.8rem);
  font-weight:400;
  line-height:1.15;
  margin-bottom:14px;
  letter-spacing:-.02em;
}
.hero-title em{font-style:italic;color:var(--accent-l);}
.hero-sub{font-size:.95rem;color:var(--text-2);font-weight:300;margin-bottom:8px;}
.result-count{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 16px;border-radius:100px;
  background:var(--card);border:1px solid var(--border);
  font-size:.78rem;font-weight:600;color:var(--text-2);
  margin-top:16px;
}
.result-count strong{color:var(--accent-l);}

/* ── SEARCH PANEL ── */
.search-panel{
  position:relative;z-index:1;
  max-width:820px;margin:0 auto 48px;
  padding:0 24px;
  animation:fadeUp .5s .1s var(--ease) both;
}
.search-card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:20px;
  padding:24px;
  display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;
}
.sf{display:flex;flex-direction:column;gap:6px;flex:1;min-width:140px;}
.sf label{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-3);}
.sf select,.sf input{
  padding:11px 14px;
  background:rgba(255,255,255,0.04);
  border:1.5px solid var(--border);
  border-radius:12px;
  color:var(--text);
  font-family:var(--ff-body);font-size:.88rem;
  outline:none;transition:border-color .25s,box-shadow .25s;
}
.sf select:focus,.sf input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(124,106,245,.12);}
.sf select option{background:var(--surface);}
.sf input::placeholder{color:var(--text-3);}
.search-btn{
  padding:12px 28px;
  background:linear-gradient(135deg,var(--accent),#a06cf0);
  border:none;border-radius:12px;
  color:white;font-family:var(--ff-body);font-size:.88rem;font-weight:700;
  cursor:pointer;white-space:nowrap;
  transition:all .25s var(--ease);
  box-shadow:0 6px 20px rgba(124,106,245,.35);
  align-self:flex-end;
}
.search-btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(124,106,245,.5);}
.clear-link{
  align-self:flex-end;padding:12px 16px;
  color:var(--text-3);font-size:.82rem;text-decoration:none;
  border:1.5px solid var(--border);border-radius:12px;
  transition:all .25s var(--ease);white-space:nowrap;
}
.clear-link:hover{color:var(--danger);border-color:rgba(245,106,66,.3);}

/* active filters */
.active-filters{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;}
.filter-tag{
  display:inline-flex;align-items:center;gap:5px;
  padding:4px 12px;border-radius:100px;
  background:rgba(124,106,245,.1);border:1px solid rgba(124,106,245,.25);
  font-size:.72rem;font-weight:600;color:var(--accent-l);
}

/* ── GRID ── */
.rooms-grid{
  position:relative;z-index:1;
  display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
  gap:22px;padding:0 48px 80px;
}

/* ── CARD ── */
.room-card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:var(--r);
  overflow:hidden;
  display:flex;flex-direction:column;
  transition:transform .3s var(--ease),border-color .3s var(--ease),box-shadow .3s var(--ease);
  animation:fadeUp .5s var(--ease) both;
  cursor:pointer;
}
.room-card:hover{
  transform:translateY(-6px);
  border-color:var(--border-l);
  box-shadow:0 28px 60px rgba(0,0,0,.55);
}

/* media */
.card-media{position:relative;height:210px;background:var(--surface);overflow:hidden;}
.card-swiper{width:100%;height:210px;}
.card-swiper img,.card-swiper video{width:100%;height:210px;object-fit:cover;display:block;}
.swiper-pagination-bullet{background:rgba(255,255,255,.5);opacity:1;}
.swiper-pagination-bullet-active{background:#fff;transform:scale(1.3);}

/* badges */
.type-pill{
  position:absolute;top:10px;left:10px;z-index:5;
  padding:3px 12px;border-radius:100px;
  font-size:.67rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;
}
.pill-single{background:rgba(124,106,245,.9);color:#fff;}
.pill-double{background:rgba(66,245,200,.9);color:#001a14;}
.pill-shared,.pill-pg{background:rgba(245,106,143,.9);color:#fff;}

.media-count{
  position:absolute;bottom:10px;right:10px;z-index:5;
  padding:3px 10px;border-radius:100px;
  background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
  font-size:.67rem;font-weight:700;color:#fff;
  border:1px solid rgba(255,255,255,.1);
}

/* like btn overlay */
.like-overlay{
  position:absolute;top:10px;right:10px;z-index:6;
}
.like-overlay form{margin:0;}
.like-btn{
  width:34px;height:34px;border-radius:50%;
  background:rgba(0,0,0,.6);backdrop-filter:blur(8px);
  border:1px solid rgba(255,255,255,.15);
  font-size:.9rem;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition:all .25s var(--ease);
}
.like-btn:hover{transform:scale(1.2);background:rgba(245,106,143,.3);}
.like-btn.liked{background:rgba(245,106,143,.25);border-color:rgba(245,106,143,.5);}

/* card body */
.card-body{padding:18px;display:flex;flex-direction:column;flex:1;}

.card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:10px;}
.card-title{
  font-family:var(--ff-serif);
  font-size:1.05rem;font-weight:400;line-height:1.35;
  color:var(--text);text-decoration:none;
  transition:color .2s;
}
.card-title:hover{color:var(--accent-l);}

.price-tag{
  flex-shrink:0;
  font-size:.82rem;font-weight:800;
  color:var(--gold);
  background:rgba(245,200,66,.08);
  border:1px solid rgba(245,200,66,.2);
  padding:4px 10px;border-radius:8px;
}

.card-meta{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;}
.meta{display:flex;align-items:center;gap:4px;font-size:.76rem;color:var(--text-2);font-weight:500;}

/* star rating display */
.stars-row{display:flex;align-items:center;gap:8px;margin-bottom:12px;}
.stars{display:flex;gap:2px;}
.star{font-size:.9rem;cursor:pointer;transition:transform .15s;}
.star:hover{transform:scale(1.3);}
.star.filled{color:var(--gold);}
.star.empty{color:var(--text-3);}
.rating-label{font-size:.72rem;color:var(--text-3);font-weight:500;}

.card-desc{
  font-size:.8rem;color:var(--text-3);line-height:1.6;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
  margin-bottom:16px;flex:1;
}

.owner-row{
  display:flex;align-items:center;gap:7px;
  padding:8px 12px;border-radius:10px;
  background:rgba(66,245,200,.05);border:1px solid rgba(66,245,200,.12);
  margin-bottom:14px;
}
.owner-avatar{
  width:24px;height:24px;border-radius:50%;
  background:linear-gradient(135deg,var(--accent),var(--rose));
  display:flex;align-items:center;justify-content:center;
  font-size:.65rem;font-weight:700;color:#fff;flex-shrink:0;
}
.owner-name{font-size:.75rem;font-weight:600;color:var(--mint);}
.owner-contact{font-size:.72rem;color:var(--text-3);margin-left:auto;}

/* actions */
.card-actions{display:flex;gap:8px;margin-top:auto;}
.btn{
  flex:1;padding:10px 8px;border-radius:10px;
  font-family:var(--ff-body);font-size:.78rem;font-weight:700;
  border:none;cursor:pointer;text-decoration:none;
  display:flex;align-items:center;justify-content:center;gap:5px;
  transition:all .25s var(--ease);
}
.btn-view{background:rgba(124,106,245,.1);color:var(--accent-l);border:1px solid rgba(124,106,245,.25);}
.btn-view:hover{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 4px 16px rgba(124,106,245,.35);}
.btn-book{background:rgba(66,245,200,.08);color:var(--mint);border:1px solid rgba(66,245,200,.2);}
.btn-book:hover{background:var(--mint);color:#001a14;border-color:var(--mint);}
.btn-book.booked{background:rgba(245,200,66,.08);color:var(--gold);border:1px solid rgba(245,200,66,.2);cursor:default;pointer-events:none;}
.btn-call{background:rgba(66,200,245,.08);color:var(--sky);border:1px solid rgba(66,200,245,.2);}
.btn-call:hover{background:var(--sky);color:#001a1f;border-color:var(--sky);}

/* ── EMPTY ── */
.no-rooms{
  grid-column:1/-1;
  display:flex;flex-direction:column;align-items:center;
  padding:100px 20px;text-align:center;
}
.no-rooms .icon{font-size:4rem;margin-bottom:16px;opacity:.3;}
.no-rooms h3{font-family:var(--ff-serif);font-size:1.5rem;color:var(--text-2);margin-bottom:8px;}
.no-rooms p{font-size:.88rem;color:var(--text-3);}

/* ── TOAST ── */
.toast-wrap{position:fixed;top:20px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{
  padding:12px 18px;border-radius:12px;
  font-size:.85rem;font-weight:500;
  backdrop-filter:blur(16px);border:1px solid;
  display:flex;align-items:center;gap:8px;min-width:240px;
  transform:translateX(120%);
  animation:tin .4s var(--ease) forwards;
  box-shadow:0 8px 24px rgba(0,0,0,.4);
}
.toast.out{animation:tout .3s var(--ease) forwards;}
.toast.success{background:rgba(66,245,160,.1);border-color:rgba(66,245,160,.3);color:var(--success);}
.toast.warning{background:rgba(245,200,66,.1);border-color:rgba(245,200,66,.3);color:var(--gold);}
.toast.error{background:rgba(245,106,66,.1);border-color:rgba(245,106,66,.3);color:var(--danger);}

/* ── ANIM ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes tin{to{transform:translateX(0)}}
@keyframes tout{to{transform:translateX(120%);opacity:0}}

/* ── SCROLL ── */
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--border-l);border-radius:3px;}
::-webkit-scrollbar-thumb:hover{background:var(--accent);}

/* ── RESPONSIVE ── */
@media(max-width:700px){
  nav{padding:12px 20px;}
  .nav-links{display:none;}
  .hero{padding:40px 20px 28px;}
  .search-panel{padding:0 16px;}
  .rooms-grid{padding:0 16px 60px;}
  .search-card{gap:10px;}
}
</style>
</head>
<body>

<div class="toast-wrap" id="toastWrap"></div>
<div class="orb orb-a"></div><div class="orb orb-b"></div><div class="orb orb-c"></div>

<!-- ── NAV ── -->
<nav>
  <div class="nav-logo">🏠 RoomEase</div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="findroom.php" class="active">Find Room</a>
    <a href="my-bookings.php">My Bookings</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="post-room.php" class="nav-cta">+ Post Room</a>
  </div>
</nav>

<!-- ── HERO ── -->
<div class="hero">
  <div class="hero-eyebrow">🔍 Room Discovery</div>
  <h1 class="hero-title">Find Your <em>Perfect Room</em></h1>
  <p class="hero-sub">Browse verified listings across top cities in Uttar Pradesh</p>
  <div class="result-count">
    <strong><?php echo $total; ?></strong> room<?php echo $total!=1?'s':''; ?> found
    <?php if($city||$type||$budget): ?>
      &nbsp;·&nbsp; filtered results
    <?php endif; ?>
  </div>
</div>

<!-- ── SEARCH PANEL ── -->
<div class="search-panel">
  <form method="GET" class="search-card">
    <div class="sf">
      <label>City</label>
      <select name="city">
        <option value="">All Cities</option>
        <?php
        $cities = ['Lucknow','Kanpur','Varanasi','Allahabad','Gorakhpur','Agra','Meerut','Jhansi','Prayagraj','Mirzapur'];
        foreach($cities as $c):
          $sel = ($city == strtolower($c)) ? 'selected' : '';
        ?>
        <option value="<?php echo strtolower($c); ?>" <?php echo $sel; ?>><?php echo $c; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="sf">
      <label>Room Type</label>
      <select name="type">
        <option value="">All Types</option>
        <option value="single"  <?php echo $type=='single'  ?'selected':''; ?>>Single</option>
        <option value="double"  <?php echo $type=='double'  ?'selected':''; ?>>Double</option>
        <option value="shared"  <?php echo $type=='shared'  ?'selected':''; ?>>Shared</option>
        <option value="pg"      <?php echo $type=='pg'      ?'selected':''; ?>>PG</option>
      </select>
    </div>
    <div class="sf">
      <label>Max Budget (₹)</label>
      <input type="number" name="budget" placeholder="e.g. 8000" value="<?php echo $budget?:'' ?>">
    </div>
    <button type="submit" class="search-btn">🔍 Search</button>
    <?php if($city||$type||$budget): ?>
      <a href="findroom.php" class="clear-link">✕ Clear</a>
    <?php endif; ?>
  </form>

  <?php if($city||$type||$budget): ?>
  <div class="active-filters">
    <?php if($city):   ?><span class="filter-tag">📍 <?php echo ucfirst($city); ?></span><?php endif; ?>
    <?php if($type):   ?><span class="filter-tag">🏠 <?php echo ucfirst($type); ?></span><?php endif; ?>
    <?php if($budget): ?><span class="filter-tag">💰 Under ₹<?php echo number_format($budget); ?></span><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- ── ROOMS GRID ── -->
<div class="rooms-grid">
<?php
if($total > 0):
  $idx = 0;
  while($row = mysqli_fetch_assoc($result)):
    $room_id = $row['id'];

    // Like status
    $liked = false;
    if($user_id_s){
      $lq = mysqli_query($conn,"SELECT id FROM likes WHERE user_id=$user_id_s AND room_id=$room_id");
      $liked = mysqli_num_rows($lq) > 0;
    }

    // Like count
    $lc_q = mysqli_query($conn,"SELECT COUNT(*) as c FROM likes WHERE room_id=$room_id");
    $like_count = $lc_q ? (int)mysqli_fetch_assoc($lc_q)['c'] : 0;

    // Rating
    $existing_rating = 0;
    if($user_id_s){
      $rq = mysqli_query($conn,"SELECT rating FROM ratings WHERE user_id=$user_id_s AND room_id=$room_id");
      if(mysqli_num_rows($rq)>0) $existing_rating = (int)mysqli_fetch_assoc($rq)['rating'];
    }

    // Avg rating
    $avg_q = mysqli_query($conn,"SELECT ROUND(AVG(rating),1) as avg, COUNT(*) as cnt FROM ratings WHERE room_id=$room_id");
    $avg_data = $avg_q ? mysqli_fetch_assoc($avg_q) : ['avg'=>0,'cnt'=>0];
    $avg_rating = (float)($avg_data['avg']??0);
    $rating_cnt = (int)($avg_data['cnt']??0);

    // Booking status
    $already_booked = false;
    if($user_id_s){
      $bq = mysqli_query($conn,"SELECT id FROM bookings WHERE user_id=$user_id_s AND room_id=$room_id");
      $already_booked = mysqli_num_rows($bq) > 0;
    }

    // Files
    $files = !empty($row['image']) ? array_values(array_filter(array_map('trim', explode(",", $row['image'])))) : [];
    $fcount = count($files);

    $type_key = strtolower($row['room_type'] ?? 'single');
    $pill_class = in_array($type_key,['single','double','shared','pg']) ? 'pill-'.$type_key : 'pill-single';

    $idx++;
?>

<div class="room-card" style="animation-delay:<?php echo $idx*.07; ?>s"
     onclick="window.location='room-details.php?id=<?php echo $room_id; ?>'">

  <!-- ── Media ── -->
  <div class="card-media">
    <!-- Like button overlay -->
    <div class="like-overlay" onclick="event.stopPropagation()">
      <form method="POST">
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
        <input type="hidden" name="<?php echo $_SERVER['QUERY_STRING'] ? 'qs' : 'qs'; ?>" value="">
        <button name="like" class="like-btn <?php echo $liked?'liked':''; ?>"
                title="<?php echo $liked?'Unlike':'Like'; ?>">
          <?php echo $liked ? '❤️' : '🤍'; ?>
        </button>
      </form>
    </div>

    <span class="type-pill <?php echo $pill_class; ?>"><?php echo htmlspecialchars($row['room_type']??'Room'); ?></span>
    <?php if($fcount>1): ?><div class="media-count">📷 <?php echo $fcount; ?></div><?php endif; ?>

    <?php if($fcount>0): ?>
    <div class="swiper card-swiper">
      <div class="swiper-wrapper">
        <?php foreach($files as $file):
          if(!$file) continue;
          $path = (strpos($file,'uploads/')===false) ? "uploads/".basename($file) : $file;
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
      <?php if($fcount>1): ?><div class="swiper-pagination"></div><?php endif; ?>
    </div>
    <?php else: ?>
      <div style="height:210px;display:flex;align-items:center;justify-content:center;color:var(--text-3);font-size:2.5rem;">🏠</div>
    <?php endif; ?>
  </div>

  <!-- ── Body ── -->
  <div class="card-body">
    <div class="card-top">
      <a href="room-details.php?id=<?php echo $room_id; ?>" class="card-title"
         onclick="event.stopPropagation()">
        <?php echo htmlspecialchars($row['title']); ?>
      </a>
      <span class="price-tag">₹<?php echo number_format($row['price']); ?>/mo</span>
    </div>

    <div class="card-meta">
      <span class="meta">📍 <?php echo htmlspecialchars($row['city']); ?></span>
      <?php if($like_count>0): ?>
        <span class="meta">❤️ <?php echo $like_count; ?></span>
      <?php endif; ?>
    </div>

    <!-- Stars rating -->
    <div class="stars-row" onclick="event.stopPropagation()">
      <form method="POST" style="display:flex;align-items:center;gap:8px;">
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
        <input type="hidden" name="rating" id="rval_<?php echo $room_id; ?>" value="<?php echo $existing_rating?:3; ?>">
        <div class="stars" id="stars_<?php echo $room_id; ?>">
          <?php for($s=1;$s<=5;$s++): ?>
          <span class="star <?php echo $s<=$avg_rating?'filled':'empty'; ?>"
                onclick="setRating(<?php echo $room_id; ?>,<?php echo $s; ?>)"
                data-val="<?php echo $s; ?>">★</span>
          <?php endfor; ?>
        </div>
        <button name="rate" id="rate_btn_<?php echo $room_id; ?>"
                style="display:none;padding:4px 12px;border-radius:8px;background:rgba(245,200,66,.15);border:1px solid rgba(245,200,66,.3);color:var(--gold);font-size:.72rem;font-weight:700;cursor:pointer;">
          Rate
        </button>
        <?php if($rating_cnt>0): ?>
        <span class="rating-label"><?php echo $avg_rating; ?> (<?php echo $rating_cnt; ?>)</span>
        <?php else: ?>
        <span class="rating-label">No ratings yet</span>
        <?php endif; ?>
      </form>
    </div>

    <?php if(!empty($row['description'])): ?>
    <p class="card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
    <?php endif; ?>

    <!-- Owner -->
    <?php if(!empty($row['owner_name'])): ?>
    <div class="owner-row">
      <div class="owner-avatar"><?php echo strtoupper(substr($row['owner_name'],0,1)); ?></div>
      <span class="owner-name"><?php echo htmlspecialchars($row['owner_name']); ?></span>
      <span class="owner-contact">📞 <?php echo htmlspecialchars($row['contact_no']); ?></span>
    </div>
    <?php endif; ?>

    <!-- Action buttons -->
    <div class="card-actions" onclick="event.stopPropagation()">
      <a href="room-details.php?id=<?php echo $room_id; ?>" class="btn btn-view">👁 View</a>

      <form method="POST" style="flex:1;margin:0;">
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
        <button name="book"
                class="btn btn-book <?php echo $already_booked?'booked':''; ?>"
                <?php echo $already_booked?'disabled':''; ?>>
          <?php echo $already_booked ? '✅ Booked' : '📅 Book Now'; ?>
        </button>
      </form>

      <a href="tel:<?php echo htmlspecialchars($row['contact_no']); ?>"
         class="btn btn-call">📞 Call</a>
    </div>
  </div>
</div>

<?php endwhile;
else: ?>
<div class="no-rooms">
  <div class="icon">🏠</div>
  <h3>No rooms found</h3>
  <p>Try adjusting your filters or search in a different city</p>
</div>
<?php endif; ?>
</div>

<script>
// ── SWIPER INIT ──
document.querySelectorAll('.card-swiper').forEach(el => {
  new Swiper(el, {
    loop: el.querySelectorAll('.swiper-slide').length > 1,
    autoplay: { delay: 2800, disableOnInteraction: false },
    pagination: el.querySelector('.swiper-pagination')
      ? { el: el.querySelector('.swiper-pagination'), clickable: true } : false,
  });
});

// video hover play
document.querySelectorAll('.card-media').forEach(media => {
  media.addEventListener('mouseenter', () => {
    const v = media.querySelector('.swiper-slide-active video');
    if(v) v.play();
  });
  media.addEventListener('mouseleave', () => {
    media.querySelectorAll('video').forEach(v => { v.pause(); v.currentTime = 0; });
  });
});

// ── STAR RATING ──
function setRating(roomId, val) {
  document.getElementById('rval_' + roomId).value = val;
  const stars = document.querySelectorAll('#stars_' + roomId + ' .star');
  stars.forEach((s, i) => {
    s.classList.toggle('filled', i < val);
    s.classList.toggle('empty',  i >= val);
  });
  const btn = document.getElementById('rate_btn_' + roomId);
  if(btn) { btn.style.display = 'inline-flex'; }
}

// ── TOAST ──
function showToast(msg, type='success') {
  const w = document.getElementById('toastWrap');
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  const icons = { success:'✅', warning:'⚠️', error:'❌' };
  t.innerHTML = (icons[type]||'ℹ️') + ' ' + msg;
  w.appendChild(t);
  setTimeout(() => { t.classList.add('out'); setTimeout(() => t.remove(), 400); }, 3500);
}

// ── PHP-triggered toasts ──
<?php if(isset($_GET['booked'])): ?>
  <?php if($_GET['booked'] === 'success'): ?>
    showToast('📅 Room booked successfully!', 'success');
  <?php elseif($_GET['booked'] === 'already'): ?>
    showToast('⚠️ You already booked this room', 'warning');
  <?php endif; ?>
<?php endif; ?>
</script>

</body>
</html>