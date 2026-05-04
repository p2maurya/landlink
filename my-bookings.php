<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "
SELECT rooms.*
FROM bookings
JOIN rooms ON bookings.room_id = rooms.id
WHERE bookings.user_id = $user_id
ORDER BY bookings.id DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings — P2MDestiny</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Epilogue:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --bg:#07070f;--surface:#0d0d1a;--card:#111120;--border:#1c1c30;--border-l:#28283e;
  --accent:#7c6af5;--accent-l:#a99ff8;--mint:#42f5c8;--gold:#f5c842;--success:#22c55e;
  --text:#ebebf5;--text-2:#8888aa;--text-3:#505068;
  --ff-serif:'Instrument Serif',serif;--ff-body:'Epilogue',sans-serif;
  --ease:cubic-bezier(0.4,0,0.2,1);
}
html{scroll-behavior:smooth;}
body{font-family:var(--ff-body);background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}

.orb{position:fixed;border-radius:50%;filter:blur(140px);pointer-events:none;z-index:0;}
.orb-a{width:600px;height:600px;background:rgba(124,106,245,0.08);top:-200px;right:-200px;}
.orb-b{width:400px;height:400px;background:rgba(66,245,200,0.05);bottom:-150px;left:-150px;}

/* NAV */
nav{position:sticky;top:12px;z-index:200;display:flex;align-items:center;justify-content:space-between;padding:10px 16px 10px 20px;margin:12px 24px 0;border-radius:100px;background:rgba(13,13,26,.85);backdrop-filter:blur(24px);border:1px solid var(--border-l);box-shadow:0 8px 32px rgba(0,0,0,.4);}
.nav-logo{font-family:var(--ff-serif);font-size:1.2rem;background:linear-gradient(120deg,var(--accent-l),var(--mint));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-decoration:none;}
.nav-links{display:flex;align-items:center;gap:2px;}
.nav-links a{padding:7px 15px;border-radius:100px;color:var(--text-2);text-decoration:none;font-size:.83rem;font-weight:500;border:1px solid transparent;transition:all .2s var(--ease);}
.nav-links a:hover{color:var(--text);background:rgba(255,255,255,.06);border-color:var(--border-l);}
.nav-links a.active{color:var(--accent-l);background:rgba(124,106,245,.1);border-color:rgba(124,106,245,.25);}
.nav-links a.nav-cta{background:linear-gradient(135deg,var(--accent),#a06cf0);color:white;font-weight:700;box-shadow:0 4px 14px rgba(124,106,245,.35);}
.hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px;border-radius:10px;border:1px solid var(--border-l);background:rgba(255,255,255,.04);}
.hamburger span{display:block;width:20px;height:2px;background:var(--text-2);border-radius:2px;transition:all .3s;}
.hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.hamburger.open span:nth-child(2){opacity:0;}
.hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}
.mobile-menu{display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:999;background:rgba(7,7,15,.97);backdrop-filter:blur(24px);flex-direction:column;align-items:center;justify-content:center;gap:16px;opacity:0;pointer-events:none;transition:all .3s;}
.mobile-menu.open{display:flex;opacity:1;pointer-events:all;}
.mobile-menu a{font-family:var(--ff-serif);font-size:2rem;color:var(--text);text-decoration:none;padding:8px 28px;border-radius:14px;transition:all .2s;}
.mobile-menu a:hover{color:var(--accent-l);}
.mobile-menu-close{position:absolute;top:24px;right:24px;font-size:1.5rem;cursor:pointer;color:var(--text-2);background:rgba(255,255,255,.06);border:1px solid var(--border-l);border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;}

/* PAGE */
.page{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:48px 24px 80px;}

/* HEADER */
.page-header{text-align:center;margin-bottom:48px;}
.page-badge{display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:100px;background:rgba(124,106,245,.1);border:1px solid rgba(124,106,245,.25);font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--accent-l);margin-bottom:16px;}
.page-title{font-family:var(--ff-serif);font-size:clamp(2rem,4vw,3rem);font-weight:400;letter-spacing:-.02em;margin-bottom:8px;}
.page-title em{font-style:italic;color:var(--accent-l);}
.page-sub{color:var(--text-2);font-size:.9rem;}

/* GRID */
.bookings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;}

/* CARD */
.booking-card{
  background:var(--card);border:1px solid var(--border);border-radius:20px;
  overflow:hidden;text-decoration:none;color:var(--text);
  transition:all .3s var(--ease);
  animation:fadeUp .5s var(--ease) both;
}
.booking-card:hover{transform:translateY(-4px);border-color:var(--accent);box-shadow:0 16px 40px rgba(124,106,245,.2);}

.card-img{width:100%;height:200px;object-fit:cover;display:block;background:var(--surface);}
.card-img-placeholder{width:100%;height:200px;background:linear-gradient(135deg,var(--surface),var(--card));display:flex;align-items:center;justify-content:center;font-size:2.5rem;}

.card-body{padding:18px;}
.card-type{display:inline-block;padding:3px 10px;border-radius:100px;font-size:.65rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;background:rgba(124,106,245,.15);color:var(--accent-l);border:1px solid rgba(124,106,245,.25);margin-bottom:10px;}
.card-title{font-size:1rem;font-weight:700;margin-bottom:6px;line-height:1.3;}
.card-city{font-size:.8rem;color:var(--text-2);margin-bottom:12px;display:flex;align-items:center;gap:4px;}
.card-footer{display:flex;align-items:center;justify-content:space-between;}
.card-price{font-size:1.1rem;font-weight:800;color:var(--success);}
.card-price span{font-size:.7rem;color:var(--text-3);font-weight:400;}
.card-badge{padding:5px 12px;border-radius:100px;font-size:.7rem;font-weight:700;background:rgba(66,245,160,.1);color:var(--success);border:1px solid rgba(66,245,160,.25);}

/* EMPTY STATE */
.empty-state{text-align:center;padding:80px 20px;}
.empty-icon{font-size:4rem;margin-bottom:16px;}
.empty-title{font-family:var(--ff-serif);font-size:1.8rem;margin-bottom:8px;}
.empty-sub{color:var(--text-2);margin-bottom:28px;}
.btn-find{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;border-radius:100px;background:linear-gradient(135deg,var(--accent),#a06cf0);color:white;text-decoration:none;font-weight:700;font-size:.9rem;box-shadow:0 8px 24px rgba(124,106,245,.4);transition:all .3s var(--ease);}
.btn-find:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(124,106,245,.55);}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--border-l);border-radius:3px;}

@media(max-width:768px){
  nav{margin:10px 16px 0;border-radius:16px;padding:8px 12px 8px 16px;}
  .nav-links{display:none;}
  .hamburger{display:flex;}
  .page{padding:32px 16px 60px;}
  .bookings-grid{grid-template-columns:repeat(auto-fill,minmax(240px,1fr));}
}
</style>
</head>
<body>

<div class="orb orb-a"></div>
<div class="orb orb-b"></div>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu-close" onclick="toggleMenu()">✕</div>
  <a href="index.php" onclick="toggleMenu()">Home</a>
  <a href="findroom.php" onclick="toggleMenu()">Find Room</a>
  <a href="my-bookings.php" onclick="toggleMenu()">My Bookings</a>
  <a href="dashboard.php" onclick="toggleMenu()">Dashboard</a>
</div>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">🏠 P2MDestiny</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="findroom.php">Find Room</a>
    <a href="my-bookings.php" class="active">My Bookings</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="post-room.php" class="nav-cta">+ Post Room</a>
  </div>
  <div class="hamburger" id="hamburger" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- PAGE -->
<div class="page">

  <div class="page-header">
    <div class="page-badge">📌 Your Bookings</div>
    <h1 class="page-title">My <em>Booked</em> Rooms</h1>
    <p class="page-sub">All rooms you have booked — manage and track them here</p>
  </div>

  <?php if(mysqli_num_rows($result) > 0): ?>

  <div class="bookings-grid">
    <?php while($row = mysqli_fetch_assoc($result)): ?>

    <?php
      // ── IMAGE URL FIX ──
      // DB mein 'uploads/filename.jpg' ya sirf 'filename.jpg' stored hota hai
      // Comma se pehli image lo agar multiple hain
      $raw_img = trim(explode(',', $row['image'] ?? '')[0]);

      if(empty($raw_img)){
        $img_url = null;
      } elseif(strpos($raw_img, 'http') === 0){
        $img_url = $raw_img; // already full URL
      } elseif(strpos($raw_img, 'uploads/') === 0){
        $img_url = 'https://landlink.gt.tc/' . $raw_img; // uploads/ already prefix mein hai
      } else {
        $img_url = 'https://landlink.gt.tc/uploads/' . basename($raw_img); // sirf filename
      }

      $ext = strtolower(pathinfo($img_url ?? '', PATHINFO_EXTENSION));
      $is_video = in_array($ext, ['mp4','webm','ogg','mov']);
    ?>

    <a href="room-details.php?id=<?php echo $row['id']; ?>" class="booking-card">

      <?php if($img_url && !$is_video): ?>
        <img class="card-img"
             src="<?php echo htmlspecialchars($img_url); ?>"
             alt="<?php echo htmlspecialchars($row['title']); ?>"
             loading="lazy"
             onerror="this.parentElement.innerHTML='<div class=\'card-img-placeholder\'>🏠</div>'+this.parentElement.innerHTML.replace(this.outerHTML,'')">
      <?php else: ?>
        <div class="card-img-placeholder">🏠</div>
      <?php endif; ?>

      <div class="card-body">
        <div class="card-type"><?php echo htmlspecialchars($row['room_type'] ?? 'Room'); ?></div>
        <div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>
        <div class="card-city">📍 <?php echo htmlspecialchars($row['city']); ?></div>
        <div class="card-footer">
          <div class="card-price">
            ₹<?php echo number_format($row['price']); ?>
            <span>/month</span>
          </div>
          <span class="card-badge">✅ Booked</span>
        </div>
      </div>
    </a>

    <?php endwhile; ?>
  </div>

  <?php else: ?>

  <div class="empty-state">
    <div class="empty-icon">🏠</div>
    <div class="empty-title">No bookings yet</div>
    <p class="empty-sub">You haven't booked any rooms. Start exploring!</p>
    <a href="findroom.php" class="btn-find">🔍 Find a Room</a>
  </div>

  <?php endif; ?>

</div>

<script>
function toggleMenu() {
  const menu = document.getElementById('mobileMenu');
  const ham  = document.getElementById('hamburger');
  menu.classList.toggle('open');
  ham.classList.toggle('open');
  document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
}
</script>
</body>
</html>
