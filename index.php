<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LandLink - Student Rooms Made Easy</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
 /* Reset & Base */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background: #f5f6fa;
  color: #0f172a;
}

a {
  text-decoration: none;
  color: inherit;
}

/* Navbar */
nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 30px;
  background: rgba(29, 147, 210, 0.15);
  backdrop-filter: blur(15px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  position: sticky;
  top: 0;
  z-index: 10; /* Fixed: allow clicks on elements below */
  pointer-events: auto; /* Ensure nav clickable */
}

nav .logo {
  display: flex;
  align-items: center;
  flex-shrink: 0; /* logo compress nahi hoga */
}

nav .logo img {
  height: 80px;
  width: 80px;
  border-radius: 50%;
  object-fit: cover;
  display: block;
  transition: transform 0.3s;
}

nav .logo img:hover {
  transform: scale(1.1) rotate(-5deg);
}

nav ul {
  list-style: none;
  display: flex;
  margin: 0;
  padding: 0;
}

nav ul li {
  margin-left: 25px;
}

nav ul li a {
  text-decoration: none;
  color: #0f172a;
  font-weight: bold;
  transition: 0.3s;
}

nav ul li a:hover {
  color: #2563eb;
}

.hero {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  height: 90vh;
  color: #e0f15b;
  z-index: 1;
  padding: 0 20px;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top:0; left:0;
  width:100%; height:100%;
  background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45));
  z-index: 0;
  pointer-events: none;
}

.hero img {
  position: absolute;
  top:0; left:0;
  width:100%; height:100%;
  object-fit: cover;
  z-index: -1;
}

.hero h1 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 15px;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
}

.hero p {
  font-size: 1.2rem;
  margin-bottom: 10px;
  text-shadow: 1px 1px 5px rgba(0,0,0,0.4);
}

.hero-cta {
  display: inline-block;
  padding: 12px 25px;
  margin-top: 20px;
  border-radius: 8px;
  background: #2563eb;
  color: #fff;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s;
  box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
}

.hero-cta:hover {
  background: #1e4fbf;
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.6);
}
/* Featured Rooms */
.rooms {
  padding: 60px 50px;
}

.rooms h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 40px;
  font-weight: 700;
}

.room-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
}

.room-card {
  background: #fff;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: 0.3s;
}

.room-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.room-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.room-card .content {
  padding: 15px;
}

.room-card .content h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: #2563eb;
}

.room-card .content p {
  font-size: 0.95rem;
  margin-bottom: 8px;
}

.room-card .content .price {
  font-weight: 700;
  color: #22c55e;
}

/* How It Works */
.how {
  padding: 60px 50px;
  background: #2563eb;
  color: #fff;
  text-align: center;
}

.how h2 {
  font-size: 2.2rem;
  margin-bottom: 40px;
  font-weight: 700;
}

.steps {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
}

.step {
  background: rgba(255, 255, 255, 0.1);
  padding: 30px 20px;
  border-radius: 12px;
  width: 220px;
  transition: 0.3s;
}

.step:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-5px);
}

.step h3 {
  font-size: 1.1rem;
  margin-bottom: 10px;
  font-weight: 600;
}

.step p {
  font-size: 0.9rem;
}

/* Footer */
footer {
  background: #0f172a;
  color: #fff;
  padding: 30px 50px;
  text-align: center;
  margin-top: 50px;
}

footer p {
  font-size: 0.9rem;
}
</style>
</head>
<body>

  <!-- Navbar -->
 <nav>
    <div class="logo">
        <a href="index.php">
            <img src="images/landlink logo.png" alt="LandLink Logo" />
        </a>
    </div>
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="findroom.php"></a></li>
      <li><a href="post-room.php"></a></li>
      <li><a href="login.php">Login</a></li>
      <li><a href="signup.php">Signup</a></li>
    </ul>
</nav>



  <section class="hero">
  <img src="images/landlink logo.png" alt="City View">

  <h1>Welcome to LandLink</h1>
  <p>Your trusted platform for verified student rooms near top colleges.</p>
  <p>Quick, secure & hassle-free rentals for students & landlords alike.</p>

  <!-- Call-to-action button -->
  <a href="signup.php" class="hero-cta">Signup to Find Rooms 🔑</a>
</section>

  <!-- Featured Rooms -->
  <section class="rooms">
    <h2>Featured Rooms</h2>
    <div class="room-cards">
      <div class="room-card">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?fit=crop&w=600&q=80" alt="Room 1">
        <div class="content">
          <h3>2BHK Room Near AKTU</h3>
          <p>Location: Indira Nagar, Lucknow</p>
          <p class="price">₹5000 / month</p>
        </div>
      </div>
      <div class="room-card">
        <img src="images/study-room-2bhk-house-design.jpg" alt="Room 2">
        <div class="content">
          <h3>1BHK Studio</h3>
          <p>Location: Gomti Nagar, Lucknow</p>
          <p class="price">₹4000 / month</p>
        </div>
      </div>
      <div class="room-card">
        <img src="images/gall-2-3BHK_GUESTBEDROOM.webp" alt="Room 3">
        <div class="content">
          <h3>Shared 3BHK Room</h3>
          <p>Location: Aliganj, Lucknow</p>
          <p class="price">₹3500 / month</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="how">
    <h2>How LandLink Works</h2>
    <div class="steps">
      <div class="step">
        <h3>1. Upload Room</h3>
        <p>Landlords add verified rooms with video & photos</p>
      </div>
      <div class="step">
        <h3>2. Search & View</h3>
        <p>Students find rooms with AI-powered matching</p>
      </div>
      <div class="step">
        <h3>3. Contact Owner</h3>
        <p>Directly connect & finalize rent securely</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p>© 2026 LandLink. All Rights Reserved.</p>
  </footer>

</body>
</html>