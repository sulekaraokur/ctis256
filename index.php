<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
session_start();
require_once "assets/includes/db.php";

/**
 * Simple search for approved events (guest + user can view)
 * Searches: title, artist_name, location, date
 */
$q = trim($_GET['q'] ?? '');

// Build query
$sql = "SELECT event_id, title, artist_name, `desc`, date, location, image
        FROM events
        WHERE status = 'approved'";

$params = [];

if ($q !== '') {
    // if user typed a date like 2025-12-01, this also matches via LIKE
    $sql .= " AND (
                title LIKE ?
                OR artist_name LIKE ?
                OR location LIKE ?
                OR DATE_FORMAT(date, '%Y-%m-%d') LIKE ?
             )";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}

$sql .= " ORDER BY date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Role helpers
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Concert & Event Tracking</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page">

<header id="header" class="header d-flex align-items-center fixed-top">
  <div class="container-fluid container-xl position-relative d-flex align-items-center">

    <a href="index.php" class="logo d-flex align-items-center me-auto">
      <img src="assets/img/logo.png" alt="">
      <!-- <h1 class="sitename">Concert Tracker</h1> -->
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="#hero" class="active">Home</a></li>
        <li><a href="#events">Events</a></li>
        <li><a href="#contact">Contact</a></li>

        <?php if (!$isLoggedIn): ?>
          <li><a href="assets/auth/login.php">Login</a></li>
          <li><a href="assets/auth/register.php">Register</a></li>
        <?php else: ?>
          <?php if ($role === 'admin'): ?>
            <li><a href="assets/admin/dashboard.php">Admin Panel</a></li>
          <?php elseif ($role === 'organizer'): ?>
            <li><a href="assets/backend/organizer/my_events.php">Organizer Panel</a></li>
          <?php else: ?>
            <!-- Normal user -->
            <li><a href="assets/backend/user/my_registrations.php">My Registrations</a></li>
          <?php endif; ?>
          <li><a href="assets/auth/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>

      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

  </div>
</header>

<main class="main">

  <!-- Hero Section -->
  <section id="hero" class="hero section dark-background">
    <img src="assets/img/hero-bg.jpg" alt="" data-aos="fade-in">

    <div class="container d-flex flex-column align-items-center text-center mt-auto">
      <h2 data-aos="fade-up" data-aos-delay="100">
        CONCERT &<br><span>EVENT</span> TRACKING
      </h2>
      <p data-aos="fade-up" data-aos-delay="200">
        Discover approved events, search by artist/date/location, and register.
      </p>
      <div class="mt-3" data-aos="fade-up" data-aos-delay="300">
        <a href="#events" class="btn btn-light px-4 py-2">Browse Events</a>
      </div>
    </div>
  </section>
  <!-- /Hero Section -->

  <!-- Events Section -->
  <section id="events" class="section">
    <div class="container section-title" data-aos="fade-up">
      <h2>Approved Events</h2>
      <p>Only admin-approved events are listed here.</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">

      <!-- Search -->
      <form class="row g-2 mb-4" method="get" action="#events">
        <div class="col-md-10">
          <input
            type="text"
            name="q"
            class="form-control"
            placeholder="Search by title, artist, date (YYYY-MM-DD) or location..."
            value="<?= e($q) ?>"
          >
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i> Search
          </button>
        </div>
      </form>

      <?php if (count($events) === 0): ?>
        <div class="alert alert-warning">
          No approved events found<?= $q !== '' ? " for: <b>" . e($q) . "</b>" : "" ?>.
        </div>
      <?php else: ?>
        <div class="row gy-4">
          <?php foreach ($events as $ev): ?>
            <?php
              $img = $ev['image'] ? e($ev['image']) : "assets/img/event-gallery/event-gallery-1.jpg";
            ?>
            <div class="col-lg-4 col-md-6">
              <div class="card h-100 shadow-sm">
                <img src="<?= $img ?>" class="card-img-top" alt="Event poster" style="height:220px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-1"><?= e($ev['title']) ?></h5>
                  <div class="text-muted mb-2"><?= e($ev['artist_name']) ?></div>

                  <div class="small mb-1">
                    <i class="bi bi-calendar-event"></i>
                    <?= e($ev['date']) ?>
                  </div>

                  <div class="small mb-3">
                    <i class="bi bi-geo-alt"></i>
                    <?= e($ev['location']) ?>
                  </div>

                  <p class="card-text" style="flex:1;">
                    <?= e(mb_strimwidth((string)$ev['desc'], 0, 140, "...")) ?>
                  </p>

                  <div class="d-flex gap-2">
                    
                    <a class="btn btn-outline-primary btn-sm"
                       href="assets/backend/guest/event_details.php?id=<?= (int)$ev['event_id'] ?>">
                      Details
                    </a>

                    <?php if ($isLoggedIn && $role === 'user'): ?>
                      <a class="btn btn-primary btn-sm"
                         href="assets/backend/user/register_event.php?id=<?= (int)$ev['event_id'] ?>">
                        Register
                      </a>
                    <?php else: ?>
                      <a class="btn btn-secondary btn-sm"
                         href="assets/auth/login.php">
                        Login to Register
                      </a>
                    <?php endif; ?>
                  </div>

                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>
  <!-- /Events Section -->

  <!-- Contact Section -->
  <section id="contact" class="contact section">
    <div class="container section-title" data-aos="fade-up">
      <h2>Contact</h2>
      <p>For questions about events and registrations.</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
      <div class="row gy-4">

        <div class="col-lg-4">
          <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="200">
            <i class="bi bi-geo-alt"></i>
            <h3>Address</h3>
            <p>Bilkent University, Ankara</p>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="300">
            <i class="bi bi-telephone"></i>
            <h3>Phone</h3>
            <p>+90 (___) ___ __ __</p>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="400">
            <i class="bi bi-envelope"></i>
            <h3>Email</h3>
            <p>info@concert-tracker.local</p>
          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- /Contact Section -->

</main>

<footer id="footer" class="footer dark-background">
  <div class="copyright text-center">
    <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">
      <div class="d-flex flex-column align-items-center align-items-lg-start">
        <div>
          © <strong><span>Concert & Event Tracking</span></strong>. All Rights Reserved
        </div>
        <div class="credits">
          Template by <a href="https://bootstrapmade.com/">BootstrapMade</a>
        </div>
      </div>
      <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-linkedin"></i></a>
      </div>
    </div>
  </div>
</footer>

<!-- Scroll Top -->
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
  <i class="bi bi-arrow-up-short"></i>
</a>

<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

<!-- Main JS File -->
<script src="assets/js/main.js"></script>

</body>
</html>
