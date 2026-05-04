<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RoomEase — Student Rooms Made Easy</title>

<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Epilogue:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

:root{
  --bg:        #07070f;
  --surface:   #0d0d1a;
  --card:      #111120;
  --card-h:    #15152a;
  --border:    #1c1c30;
  --border-l:  #28283e;
  --accent:    #7c6af5;
  --accent-l:  #a99ff8;
  --gold:      #f5c842;
  --rose:      #f56a8f;
  --mint:      #42f5c8;
  --sky:       #42c8f5;
  --text:      #ebebf5;
  --text-2:    #8888aa;
  --text-3:    #505068;
  --ff-serif:  'Instrument Serif', serif;
  --ff-body:   'Epilogue', sans-serif;
  --r:         16px;
  --ease:      cubic-bezier(0.4,0,0.2,1);
}

html{scroll-behavior:smooth;}
body{font-family:var(--ff-body);background:var(--bg);color:var(--text);overflow-x:hidden;}

/* grain */
body::after{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");pointer-events:none;z-index:9999;opacity:.5;}

/* ── ORBS ── */
.orb{position:fixed;border-radius:50%;filter:blur(150px);pointer-events:none;z-index:0;}
.orb-a{width:900px;height:900px;background:rgba(124,106,245,0.1);top:-400px;right:-300px;animation:drift 18s ease-in-out infinite alternate;}
.orb-b{width:600px;height:600px;background:rgba(245,106,143,0.07);bottom:-200px;left:-200px;animation:drift 14s ease-in-out infinite alternate-reverse;}
.orb-c{width:500px;height:500px;background:rgba(66,245,200,0.05);top:40%;left:35%;animation:drift 20s ease-in-out infinite alternate;}
@keyframes drift{from{transform:translate(0,0)}to{transform:translate(40px,30px)}}

/* ── NAV ── */
nav{
  position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:100;
  display:flex;align-items:center;justify-content:space-between;
  padding:10px 16px 10px 20px;
  width:calc(100% - 48px);max-width:1200px;
  border-radius:100px;
  background:rgba(13,13,26,.7);
  backdrop-filter:blur(24px);
  border:1px solid var(--border-l);
  box-shadow:0 8px 32px rgba(0,0,0,.4);
  transition:all .4s var(--ease);
}
nav.scrolled{
  background:rgba(7,7,15,.95);
  box-shadow:0 12px 48px rgba(0,0,0,.6);
  border-color:rgba(124,106,245,.2);
}
.nav-logo{
  display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0;
}
.logo-img{
  height:34px;width:34px;border-radius:50%;object-fit:cover;
  border:2px solid rgba(124,106,245,.4);
}
.nav-logo-text{
  font-family:var(--ff-serif);font-size:1.2rem;
  background:linear-gradient(120deg,var(--accent-l),var(--mint));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  white-space:nowrap;
}
.nav-links{display:flex;align-items:center;gap:2px;}
.nav-links a{
  padding:7px 15px;border-radius:100px;color:var(--text-2);text-decoration:none;
  font-size:.83rem;font-weight:500;border:1px solid transparent;
  transition:all .2s var(--ease);white-space:nowrap;
}
.nav-links a:hover{color:var(--text);background:rgba(255,255,255,.06);border-color:var(--border-l);}
.nav-links a.active{color:var(--accent-l);background:rgba(124,106,245,.1);border-color:rgba(124,106,245,.25);}
.nav-cta-group{display:flex;align-items:center;gap:8px;flex-shrink:0;}
.btn-ghost{
  padding:8px 18px;border-radius:100px;
  color:var(--text-2);text-decoration:none;font-size:.83rem;font-weight:600;
  border:1px solid var(--border-l);transition:all .2s var(--ease);white-space:nowrap;
}
.btn-ghost:hover{color:var(--text);border-color:var(--accent);background:rgba(124,106,245,.08);}
.btn-primary{
  padding:9px 20px;border-radius:100px;
  background:linear-gradient(135deg,var(--accent),#a06cf0);
  color:white;text-decoration:none;font-size:.83rem;font-weight:700;
  box-shadow:0 4px 16px rgba(124,106,245,.4);
  transition:all .2s var(--ease);border:none;white-space:nowrap;
}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(124,106,245,.55);}

/* hamburger */
.hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px;border-radius:10px;border:1px solid var(--border-l);background:rgba(255,255,255,.04);}
.hamburger span{display:block;width:20px;height:2px;background:var(--text-2);border-radius:2px;transition:all .3s var(--ease);}
.hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
.hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0);}
.hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}

/* mobile menu */
.mobile-menu{
  display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:99;
  background:rgba(7,7,15,.97);backdrop-filter:blur(24px);
  flex-direction:column;align-items:center;justify-content:center;gap:16px;
  opacity:0;transform:scale(1.02);
  transition:all .3s var(--ease);
  pointer-events:none;
}
.mobile-menu.open{display:flex;opacity:1;transform:scale(1);pointer-events:all;}
.mobile-menu a{font-family:var(--ff-serif);font-size:2rem;color:var(--text);text-decoration:none;padding:8px 28px;border-radius:14px;transition:all .2s;font-weight:400;}
.mobile-menu a:hover{color:var(--accent-l);background:rgba(124,106,245,.1);}
.mobile-menu-ctas{display:flex;gap:12px;margin-top:16px;}
.mobile-menu-close{position:absolute;top:24px;right:24px;font-size:1.5rem;cursor:pointer;color:var(--text-2);background:rgba(255,255,255,.06);border:1px solid var(--border-l);border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;}

/* ── HERO ── */
.hero{
  position:relative;z-index:1;
  min-height:100vh;
  display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  text-align:center;
  padding:140px 24px 100px;
  overflow:hidden;
}

/* animated grid bg */
.hero-grid{
  position:absolute;inset:0;z-index:0;
  background-image:
    linear-gradient(rgba(124,106,245,.07) 1px, transparent 1px),
    linear-gradient(90deg, rgba(124,106,245,.07) 1px, transparent 1px);
  background-size:50px 50px;
  mask-image:radial-gradient(ellipse 90% 70% at 50% 40%, black 20%, transparent 100%);
  animation:gridPan 25s linear infinite;
}
@keyframes gridPan{from{background-position:0 0}to{background-position:50px 50px}}

/* floating shapes */
.hero-shape{position:absolute;border-radius:50%;pointer-events:none;z-index:0;}
.hs-1{width:320px;height:320px;background:radial-gradient(circle,rgba(124,106,245,.15),transparent 70%);top:10%;right:10%;animation:float1 8s ease-in-out infinite;}
.hs-2{width:220px;height:220px;background:radial-gradient(circle,rgba(66,245,200,.1),transparent 70%);bottom:20%;left:8%;animation:float2 10s ease-in-out infinite;}
.hs-3{width:160px;height:160px;background:radial-gradient(circle,rgba(245,106,143,.12),transparent 70%);top:60%;right:20%;animation:float1 12s ease-in-out infinite reverse;}
@keyframes float1{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-20px,20px) scale(1.05)}}
@keyframes float2{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(20px,-15px) scale(1.08)}}

.hero-badge{
  position:relative;z-index:1;
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 16px 6px 8px;border-radius:100px;
  background:rgba(124,106,245,.1);border:1px solid rgba(124,106,245,.25);
  font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--accent-l);
  margin-bottom:32px;
  animation:fadeUp .6s var(--ease) both;
}
.badge-dot{width:6px;height:6px;border-radius:50%;background:var(--accent);animation:pulse 2s infinite;flex-shrink:0;}
.badge-ping{display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:rgba(124,106,245,.2);}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(0.8)}}

.hero-title{
  position:relative;z-index:1;
  font-family:var(--ff-serif);
  font-size:clamp(3rem,7.5vw,6.5rem);
  font-weight:400;line-height:1.05;letter-spacing:-.03em;
  margin-bottom:28px;
  animation:fadeUp .7s .1s var(--ease) both;
}
.hero-title em{font-style:italic;
  background:linear-gradient(135deg,var(--accent-l),var(--mint));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-title .line-2{color:var(--text-2);font-size:92%;}

/* animated underline on hero title */
.hero-title .u-line{
  position:relative;display:inline-block;
}
.hero-title .u-line::after{
  content:'';position:absolute;bottom:-6px;left:0;right:0;height:3px;
  background:linear-gradient(90deg,var(--accent),var(--mint));
  border-radius:3px;
  transform:scaleX(0);transform-origin:left;
  animation:lineIn 1s .8s var(--ease) forwards;
}
@keyframes lineIn{to{transform:scaleX(1)}}

.hero-sub{
  position:relative;z-index:1;
  max-width:540px;font-size:1.05rem;color:var(--text-2);font-weight:300;line-height:1.75;
  margin-bottom:44px;
  animation:fadeUp .7s .2s var(--ease) both;
}

.hero-actions{
  position:relative;z-index:1;
  display:flex;gap:12px;flex-wrap:wrap;justify-content:center;
  animation:fadeUp .7s .3s var(--ease) both;
}
.hero-btn-main{
  display:inline-flex;align-items:center;gap:9px;
  padding:15px 34px;border-radius:100px;
  background:linear-gradient(135deg,var(--accent),#b06cf0);
  color:white;text-decoration:none;font-size:.95rem;font-weight:700;
  box-shadow:0 8px 32px rgba(124,106,245,.45);
  transition:all .3s var(--ease);position:relative;overflow:hidden;
}
.hero-btn-main::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.15),transparent);opacity:0;transition:opacity .3s;}
.hero-btn-main:hover{transform:translateY(-3px);box-shadow:0 18px 44px rgba(124,106,245,.55);}
.hero-btn-main:hover::before{opacity:1;}
.hero-btn-ghost{
  display:inline-flex;align-items:center;gap:9px;
  padding:15px 32px;border-radius:100px;
  background:rgba(255,255,255,.05);
  color:var(--text);text-decoration:none;font-size:.95rem;font-weight:600;
  border:1px solid var(--border-l);
  transition:all .3s var(--ease);
}
.hero-btn-ghost:hover{background:rgba(124,106,245,.08);border-color:var(--accent);}

/* hero stats */
.hero-stats{
  position:relative;z-index:1;
  display:flex;gap:0;margin-top:72px;
  background:var(--card);border:1px solid var(--border);border-radius:24px;overflow:hidden;
  animation:fadeUp .7s .45s var(--ease) both;
  box-shadow:0 20px 60px rgba(0,0,0,.35);
}
.stat-item{
  padding:22px 40px;text-align:center;
  border-right:1px solid var(--border);
  transition:background .3s;
}
.stat-item:hover{background:rgba(124,106,245,.06);}
.stat-item:last-child{border-right:none;}
.stat-num{
  font-family:var(--ff-serif);font-size:2rem;
  background:linear-gradient(135deg,var(--text),var(--accent-l));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  line-height:1;
}
.stat-label{font-size:.7rem;color:var(--text-3);margin-top:5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;}

/* ── MARQUEE ── */
.marquee-wrap{
  position:relative;z-index:1;
  padding:32px 0;overflow:hidden;
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  background:var(--surface);
}
.marquee-track{
  display:flex;gap:48px;white-space:nowrap;
  animation:marquee 20s linear infinite;
}
.marquee-track:hover{animation-play-state:paused;}
.marquee-item{
  display:flex;align-items:center;gap:10px;
  font-size:.82rem;font-weight:600;color:var(--text-3);
  flex-shrink:0;
}
.marquee-item span{color:var(--accent-l);}
@keyframes marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── FEATURES ── */
.features{
  position:relative;z-index:1;
  padding:100px 56px;
}
.section-eyebrow{
  text-align:center;
  font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
  color:var(--accent-l);margin-bottom:12px;
}
.section-title{
  font-family:var(--ff-serif);
  font-size:clamp(1.8rem,3.5vw,2.8rem);
  text-align:center;line-height:1.2;letter-spacing:-.02em;
  margin-bottom:60px;
}
.section-title em{font-style:italic;color:var(--accent-l);}

.features-grid{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:20px;
  max-width:1000px;margin:0 auto;
}
.feat-card{
  background:var(--card);border:1px solid var(--border);border-radius:20px;
  padding:28px;
  transition:all .3s var(--ease);
  animation:fadeUp .6s var(--ease) both;
}
.feat-card:hover{border-color:var(--border-l);transform:translateY(-4px);box-shadow:0 20px 50px rgba(0,0,0,.4);}
.feat-icon{
  width:48px;height:48px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.3rem;margin-bottom:16px;
}
.feat-card h3{font-family:var(--ff-serif);font-size:1.1rem;margin-bottom:8px;font-weight:400;}
.feat-card p{font-size:.82rem;color:var(--text-2);line-height:1.7;font-weight:300;}

/* ── ROOMS SECTION ── */
.rooms-section{
  position:relative;z-index:1;
  padding:0 56px 100px;
}
.section-head-row{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:36px;}
.view-all{
  display:flex;align-items:center;gap:6px;
  color:var(--accent-l);text-decoration:none;font-size:.85rem;font-weight:600;
  transition:gap .25s var(--ease);
}
.view-all:hover{gap:10px;}

.cards-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:20px;
}

.room-card{
  background:var(--card);border:1px solid var(--border);border-radius:var(--r);
  overflow:hidden;transition:all .3s var(--ease);
  animation:fadeUp .6s var(--ease) both;
}
.room-card:hover{transform:translateY(-6px);border-color:var(--border-l);box-shadow:0 28px 60px rgba(0,0,0,.5);}
.room-card img{width:100%;height:190px;object-fit:cover;display:block;transition:transform .4s var(--ease);}
.room-card:hover img{transform:scale(1.04);}
.card-img-wrap{overflow:hidden;position:relative;}
.card-badge{
  position:absolute;top:10px;left:10px;z-index:2;
  padding:3px 12px;border-radius:100px;
  font-size:.68rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;
  background:rgba(124,106,245,.9);color:#fff;
}
.room-card .card-body{padding:18px;}
.room-card h3{font-family:var(--ff-serif);font-size:1rem;margin-bottom:8px;line-height:1.3;}
.room-card .loc{font-size:.78rem;color:var(--text-2);margin-bottom:8px;display:flex;align-items:center;gap:4px;}
.room-card .price{font-size:.92rem;font-weight:800;color:var(--gold);}

/* ── HOW IT WORKS ── */
.how-section{
  position:relative;z-index:1;
  padding:100px 56px;
  background:var(--surface);
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
}
.steps-grid{
  display:grid;grid-template-columns:repeat(3,1fr);gap:0;
  max-width:900px;margin:0 auto;
  position:relative;
}
.steps-grid::before{
  content:'';position:absolute;top:32px;left:16.6%;right:16.6%;
  height:1px;background:linear-gradient(90deg,transparent,var(--border),var(--border),transparent);
  z-index:0;
}
.step-card{
  text-align:center;padding:0 24px;position:relative;z-index:1;
  animation:fadeUp .6s var(--ease) both;
}
.step-num{
  width:64px;height:64px;border-radius:50%;
  background:var(--card);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--ff-serif);font-size:1.4rem;color:var(--accent-l);
  margin:0 auto 20px;
  transition:all .3s var(--ease);
}
.step-card:hover .step-num{
  background:var(--accent);color:#fff;
  box-shadow:0 8px 24px rgba(124,106,245,.4);
  border-color:var(--accent);
  transform:scale(1.1);
}
.step-card h3{font-family:var(--ff-serif);font-size:1.1rem;margin-bottom:8px;font-weight:400;}
.step-card p{font-size:.82rem;color:var(--text-2);line-height:1.7;font-weight:300;}

/* ── CTA BAND ── */
.cta-band{
  position:relative;z-index:1;
  padding:100px 56px;
  text-align:center;
  overflow:hidden;
}
.cta-band-bg{
  position:absolute;inset:0;z-index:0;
  background:radial-gradient(ellipse 60% 80% at 50% 50%, rgba(124,106,245,.12), transparent 70%);
}
.cta-band .section-title{margin-bottom:16px;}
.cta-band-sub{color:var(--text-2);font-size:.95rem;font-weight:300;margin-bottom:36px;position:relative;z-index:1;}
.cta-band-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1;}

/* ── FOOTER ── */
footer{
  position:relative;z-index:1;
  background:var(--surface);border-top:1px solid var(--border);
  padding:40px 56px;
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:16px;
}
.footer-logo{font-family:var(--ff-serif);font-size:1.2rem;background:linear-gradient(120deg,var(--accent-l),var(--mint));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.footer-copy{font-size:.78rem;color:var(--text-3);}
.footer-links{display:flex;gap:20px;}
.footer-links a{font-size:.78rem;color:var(--text-3);text-decoration:none;transition:color .2s;}
.footer-links a:hover{color:var(--accent-l);}

/* ── SCROLL ANIM ── */
.reveal{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease);}
.reveal.visible{opacity:1;transform:none;}

@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

::-webkit-scrollbar{width:5px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--border-l);border-radius:3px;}::-webkit-scrollbar-thumb:hover{background:var(--accent);}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  nav{width:calc(100% - 32px);top:12px;padding:8px 12px 8px 16px;border-radius:20px;}
  .nav-links{display:none;}
  .nav-cta-group{display:none;}
  .hamburger{display:flex;}
  .hero-stats{flex-wrap:wrap;border-radius:16px;}
  .stat-item{border-right:none;border-bottom:1px solid var(--border);flex:1;min-width:120px;padding:16px 20px;}
  .stat-item:last-child{border-bottom:none;}
  .features-grid,.steps-grid{grid-template-columns:1fr;}
  .steps-grid::before{display:none;}
  .features,.rooms-section,.how-section,.cta-band{padding-left:20px;padding-right:20px;}
  footer{padding:28px 20px;flex-direction:column;align-items:flex-start;}
  .hero-shape{display:none;}
}
</style>
</head>
<body>

<div class="orb orb-a"></div>
<div class="orb orb-b"></div>
<div class="orb orb-c"></div>

<!-- ── MOBILE MENU ── -->
<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu-close" onclick="toggleMenu()">✕</div>
  <a href="index.php" onclick="toggleMenu()">Home</a>
  <a href="findroom.php" onclick="toggleMenu()">Find Room</a>
  <a href="post-room.php" onclick="toggleMenu()">Post Room</a>
  <a href="dashboard.php" onclick="toggleMenu()">Dashboard</a>
  <div class="mobile-menu-ctas">
    <a href="login.php" class="btn-ghost" onclick="toggleMenu()">Login</a>
    <a href="signup.php" class="btn-primary" onclick="toggleMenu()">Sign Up Free</a>
  </div>
</div>

<!-- ── NAV ── -->
<nav id="navbar">
  <a href="index.php" class="nav-logo">
    <img src="images/p2mdestiny.png" alt="logo" class="logo-img">
    <span class="nav-logo-text">P2MDestiny</span>
  </a>
  <div class="nav-links">
    <a href="index.php" class="active">Home</a>
    <a href="findroom.php">Find Room</a>
    <a href="post-room.php">Post Room</a>
    <a href="dashboard.php">Dashboard</a>
  </div>
  <div class="nav-cta-group">
    <a href="login.php" class="btn-ghost">Login</a>
    <a href="signup.php" class="btn-primary">Sign Up Free</a>
  </div>
  <div class="hamburger" id="hamburger" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="hero-shape hs-1"></div>
  <div class="hero-shape hs-2"></div>
  <div class="hero-shape hs-3"></div>

  <div class="hero-badge">
    <span class="badge-ping"><span class="badge-dot"></span></span>
    Trusted by 1,000+ students across UP
  </div>

  <h1 class="hero-title">
    Find Your Perfect<br>
    <em class="u-line">Student Room</em><br>
    <span class="line-2">Without the Hassle</span>
  </h1>

  <p class="hero-sub">
    Verified rooms near top colleges in Lucknow, Prayagraj, Varanasi & more.
    Connect directly with landlords — no middlemen, no hidden fees.
  </p>

  <div class="hero-actions">
    <a href="signup.php" class="hero-btn-main">🔑 Get Started Free</a>
    <a href="findroom.php" class="hero-btn-ghost">🔍 Browse Rooms</a>
  </div>

  <div class="hero-stats">
    <div class="stat-item">
      <div class="stat-num">500+</div>
      <div class="stat-label">Rooms Listed</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">1K+</div>
      <div class="stat-label">Students Helped</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">10+</div>
      <div class="stat-label">Cities Covered</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">4.8★</div>
      <div class="stat-label">Avg Rating</div>
    </div>
  </div>
</section>

<!-- ── MARQUEE ── -->
<div class="marquee-wrap">
  <div class="marquee-track">
    <span class="marquee-item">📍 <span>Lucknow</span></span>
    <span class="marquee-item">📍 <span>Prayagraj</span></span>
    <span class="marquee-item">📍 <span>Varanasi</span></span>
    <span class="marquee-item">📍 <span>Kanpur</span></span>
    <span class="marquee-item">📍 <span>Gorakhpur</span></span>
    <span class="marquee-item">📍 <span>Agra</span></span>
    <span class="marquee-item">📍 <span>Meerut</span></span>
    <span class="marquee-item">📍 <span>Jhansi</span></span>
    <span class="marquee-item">🏠 <span>Single Rooms</span></span>
    <span class="marquee-item">🏠 <span>Double Rooms</span></span>
    <span class="marquee-item">🏠 <span>PG Rooms</span></span>
    <span class="marquee-item">✅ <span>Verified Listings</span></span>
    <span class="marquee-item">⚡ <span>Instant Connect</span></span>
    <!-- duplicate for seamless loop -->
    <span class="marquee-item">📍 <span>Lucknow</span></span>
    <span class="marquee-item">📍 <span>Prayagraj</span></span>
    <span class="marquee-item">📍 <span>Varanasi</span></span>
    <span class="marquee-item">📍 <span>Kanpur</span></span>
    <span class="marquee-item">📍 <span>Gorakhpur</span></span>
    <span class="marquee-item">📍 <span>Agra</span></span>
    <span class="marquee-item">📍 <span>Meerut</span></span>
    <span class="marquee-item">📍 <span>Jhansi</span></span>
    <span class="marquee-item">🏠 <span>Single Rooms</span></span>
    <span class="marquee-item">🏠 <span>Double Rooms</span></span>
    <span class="marquee-item">🏠 <span>PG Rooms</span></span>
    <span class="marquee-item">✅ <span>Verified Listings</span></span>
    <span class="marquee-item">⚡ <span>Instant Connect</span></span>
  </div>
</div>

<!-- ── FEATURES ── -->
<section class="features">
  <div class="section-eyebrow">Why P2MDestiny</div>
  <h2 class="section-title">Everything you need to find<br><em>the right room</em></h2>

  <div class="features-grid">
    <div class="feat-card reveal" style="animation-delay:.05s">
      <div class="feat-icon" style="background:rgba(124,106,245,.15);">🔍</div>
      <h3>Smart Search</h3>
      <p>Filter by city, room type, and budget to find exactly what you're looking for in seconds.</p>
    </div>
    <div class="feat-card reveal" style="animation-delay:.1s">
      <div class="feat-icon" style="background:rgba(66,245,200,.12);">📸</div>
      <h3>Photo & Video Tours</h3>
      <p>See every room before you visit — landlords upload real photos and walkthrough videos.</p>
    </div>
    <div class="feat-card reveal" style="animation-delay:.15s">
      <div class="feat-icon" style="background:rgba(245,200,66,.1);">⭐</div>
      <h3>Verified Ratings</h3>
      <p>Read honest reviews from students who've actually stayed. No fake reviews allowed.</p>
    </div>
    <div class="feat-card reveal" style="animation-delay:.2s">
      <div class="feat-icon" style="background:rgba(245,106,143,.12);">💬</div>
      <h3>Direct WhatsApp</h3>
      <p>One tap to WhatsApp or call the landlord directly. No middlemen, no commissions.</p>
    </div>
    <div class="feat-card reveal" style="animation-delay:.25s">
      <div class="feat-icon" style="background:rgba(66,200,245,.1);">📅</div>
      <h3>Easy Booking</h3>
      <p>Reserve a room online in one click. Manage all your bookings from your dashboard.</p>
    </div>
    <div class="feat-card reveal" style="animation-delay:.3s">
      <div class="feat-icon" style="background:rgba(124,106,245,.15);">🏷️</div>
      <h3>Post for Free</h3>
      <p>Landlords can list unlimited rooms with photos and videos at absolutely zero cost.</p>
    </div>
  </div>
</section>

<!-- ── FEATURED ROOMS ── -->
<section class="rooms-section">
  <div class="section-head-row reveal">
    <div>
      <div class="section-eyebrow" style="text-align:left;">Featured Listings</div>
      <h2 class="section-title" style="text-align:left;margin-bottom:0;">Popular <em>Rooms</em></h2>
    </div>
    <a href="findroom.php" class="view-all">View all rooms →</a>
  </div>

  <div class="cards-grid">
    <div class="room-card reveal" style="animation-delay:.05s">
      <div class="card-img-wrap">
        <span class="card-badge">Single</span>
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?fit=crop&w=600&q=80" alt="Room">
      </div>
      <div class="card-body">
        <h3>2BHK Room Near AKTU</h3>
        <div class="loc">📍 Indira Nagar, Lucknow</div>
        <div class="price">₹5,000 / month</div>
      </div>
    </div>
    <div class="room-card reveal" style="animation-delay:.1s">
      <div class="card-img-wrap">
        <span class="card-badge" style="background:rgba(66,245,200,.9);color:#001a14;">Double</span>
        <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?fit=crop&w=600&q=80" alt="Room">
      </div>
      <div class="card-body">
        <h3>Cozy 1BHK Studio Flat</h3>
        <div class="loc">📍 Gomti Nagar, Lucknow</div>
        <div class="price">₹4,000 / month</div>
      </div>
    </div>
    <div class="room-card reveal" style="animation-delay:.15s">
      <div class="card-img-wrap">
        <span class="card-badge" style="background:rgba(245,106,143,.9);">PG</span>
        <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?fit=crop&w=600&q=80" alt="Room">
      </div>
      <div class="card-body">
        <h3>Shared PG Near BHU</h3>
        <div class="loc">📍 Lanka, Varanasi</div>
        <div class="price">₹3,500 / month</div>
      </div>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="how-section">
  <div class="section-eyebrow">Simple Process</div>
  <h2 class="section-title">How <em>P2MDestiny</em> Works</h2>

  <div class="steps-grid">
    <div class="step-card reveal" style="animation-delay:.05s">
      <div class="step-num">1</div>
      <h3>Create Account</h3>
      <p>Sign up free in 30 seconds — as a student looking for rooms or a landlord with rooms to rent.</p>
    </div>
    <div class="step-card reveal" style="animation-delay:.15s">
      <div class="step-num">2</div>
      <h3>Search or Post</h3>
      <p>Students search by city & budget. Landlords upload photos, videos and set their price.</p>
    </div>
    <div class="step-card reveal" style="animation-delay:.25s">
      <div class="step-num">3</div>
      <h3>Connect & Move In</h3>
      <p>Contact the owner directly via WhatsApp or call. Book online and move in stress-free.</p>
    </div>
  </div>
</section>

<!-- ── CTA BAND ── -->
<section class="cta-band">
  <div class="cta-band-bg"></div>
  <div class="section-eyebrow">Get Started Today</div>
  <h2 class="section-title">Ready to find your<br><em>perfect room?</em></h2>
  <p class="cta-band-sub">Join thousands of students and landlords already on P2MDestiny. It's completely free.</p>
  <div class="cta-band-btns">
    <a href="signup.php" class="hero-btn-main">🔑 Sign Up as Student</a>
    <a href="post-room.php" class="hero-btn-ghost">🏠 List Your Room</a>
  </div>
</section>

<!-- ── FOOTER ── */
<footer>
  <div class="footer-logo">🏠 P2MDestiny</div>
  <div class="footer-links">
    <a href="findroom.php">Find Rooms</a>
    <a href="post-room.php">Post Room</a>
    <a href="login.php">Login</a>
    <a href="signup.php">Sign Up</a>
  </div>
  <div class="footer-copy">© 2026 P2MDestiny. All rights reserved.</div>
</footer>

<script>
// ── NAV SCROLL ──
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 40);
});

// ── HAMBURGER MENU ──
function toggleMenu() {
  const menu = document.getElementById('mobileMenu');
  const ham  = document.getElementById('hamburger');
  menu.classList.toggle('open');
  ham.classList.toggle('open');
  document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
}

// ── SCROLL REVEAL ──
const revealEls = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.12 });
revealEls.forEach(el => observer.observe(el));
</script>

</body>
</html>