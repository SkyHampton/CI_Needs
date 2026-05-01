<?php
// ── Database connection + all queries run once at the top ──
$host      = "137.184.46.194";
$user      = "cineedsc_sky";
$password  = "N3ph@ndus";
$database  = "cineedsc_db";
$post_table  = "CIN_Post";
$user_table  = "CIN_User";
$reply_table = "CIN_Reply";

// Read active category filter from URL (e.g. ?category=food)
$activeCategory = isset($_GET['category']) && $_GET['category'] !== 'all'
    ? $_GET['category']
    : 'all';

// Valid categories whitelist — prevents SQL injection from URL tampering
$validCategories = ['food', 'housing', 'financial', 'health', 'academic', 'other'];
if ($activeCategory !== 'all' && !in_array($activeCategory, $validCategories)) {
    $activeCategory = 'all';
}

try {
    $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ── Stats queries ──
    $statTotalPosts = $db->query("SELECT COUNT(*) FROM $post_table")->fetchColumn();
    $statFulfilled  = $db->query("SELECT COUNT(*) FROM $post_table WHERE fulfilled = 1")->fetchColumn();
    $statToday      = $db->query("SELECT COUNT(*) FROM $post_table WHERE DATE(postDate) = CURDATE()")->fetchColumn();
    $statThisWeek   = $db->query("SELECT COUNT(*) FROM $post_table WHERE postDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

    // ── Posts query with optional category filter ──
    if ($activeCategory !== 'all') {
        $stmt = $db->prepare(
            "SELECT * FROM $post_table
             INNER JOIN $user_table ON {$post_table}.userID = {$user_table}.userID
             WHERE {$post_table}.category = ?
             ORDER BY {$post_table}.postDate DESC
             LIMIT 20"
        );
        $stmt->execute([$activeCategory]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $posts = $db->query(
            "SELECT * FROM $post_table
             INNER JOIN $user_table ON {$post_table}.userID = {$user_table}.userID
             ORDER BY {$post_table}.postDate DESC
             LIMIT 20"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Show friendly error and stop — don't expose raw DB errors in production
    die("<p style='color:red;padding:20px;'>Database error — please try again later.</p>");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CI Needs | CSU Channel Islands</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Source+Sans+3:wght@300;400;600;700&display=swap"
    rel="stylesheet" />
  <style>
    :root {
      --crimson: #c21228;
      --crimson-dark: #b41414;
      --blue: #1F6FAE;
      --blue-dark: #155887;
      --sage: #D6E4D6;
      --sage-dark: #B4CCBA;
      --white: #FFFFFF;
      --off-white: #F5F5F5;
      --light-gray: #E8E8E8;
      --mid-gray: #767676;
      --dark: #1A1A1A;
      --text: #333333;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Source Sans 3', sans-serif;
      color: var(--text);
      background: var(--white);
    }

    /* ── TOP BAR ── */
    .top-bar { background: var(--crimson); color: white; font-size: 0.78rem; padding: 5px 0; }
    .top-bar .inner { max-width: 1100px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: flex-end; gap: 18px; }
    .top-bar a { color: white; text-decoration: none; }
    .top-bar a:hover { text-decoration: underline; }

    /* ── HEADER ── */
    header { background: var(--white); border-bottom: 1px solid var(--light-gray); position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    .header-inner { max-width: 1100px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 70px; }
    .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .logo-text { display: flex; flex-direction: column; line-height: 1.1; }
    .logo-text span:first-child { font-size: 0.72rem; color: var(--mid-gray); text-transform: uppercase; letter-spacing: 0.05em; }
    .logo-text span:last-child { font-size: 1.1rem; font-weight: 700; color: var(--crimson); }
    nav { display: flex; align-items: center; gap: 6px; }
    nav a { color: var(--text); text-decoration: none; font-size: 0.9rem; font-weight: 600; padding: 6px 12px; border-radius: 4px; transition: background 0.2s, color 0.2s; }
    nav a:hover { background: var(--off-white); color: var(--crimson); }
    nav a.active { color: var(--crimson); border-bottom: 2px solid var(--crimson); }
    .btn-nav { background: var(--blue); color: white !important; border-radius: 4px; padding: 7px 16px !important; transition: background 0.2s !important; }
    .btn-nav:hover { background: var(--blue-dark) !important; }

    /* ── HERO ── */
    .hero { background: linear-gradient(135deg, var(--sage) 0%, #e8f0e8 60%, #f0f5f0 100%); padding: 56px 24px 52px; text-align: center; border-bottom: 1px solid var(--sage-dark); }
    .hero h1 { font-family: 'Merriweather', serif; font-size: 2.2rem; color: var(--crimson); margin-bottom: 12px; }
    .hero p { font-size: 1.05rem; color: #4a4a4a; max-width: 600px; margin: 0 auto 28px; line-height: 1.65; }
    .hero-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .btn-primary { background: var(--blue); color: white; border: none; padding: 11px 28px; border-radius: 4px; font-size: 0.95rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background 0.2s; font-family: 'Source Sans 3', sans-serif; }
    .btn-primary:hover { background: var(--blue-dark); }

    /* ── ANNOUNCEMENTS ── */
    .announcements-bar { background: var(--off-white); border-bottom: 1px solid var(--light-gray); padding: 10px 24px; }
    .announcements-bar .inner { max-width: 1100px; margin: 0 auto; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .ann-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--crimson); white-space: nowrap; }
    .ann-item { font-size: 1rem; color: var(--blue); text-decoration: none; font-weight: 700; }
    .ann-item:hover { text-decoration: underline; }
    .ann-divider { color: var(--mid-gray); }

    /* ── MAIN LAYOUT ── */
    .main-wrapper { max-width: 1100px; margin: 36px auto; padding: 0 24px; display: grid; grid-template-columns: 1fr 300px; gap: 32px; align-items: start; }
    .section-title { font-family: 'Merriweather', serif; font-size: 1.25rem; color: var(--crimson); border-bottom: 2px solid var(--crimson); padding-bottom: 8px; margin-bottom: 20px; }

    /* ── FILTER BAR ── */
    .filter-bar { display: flex; gap: 8px; margin-bottom: 22px; flex-wrap: wrap; }
    .filter-btn { background: var(--white); border: 1.5px solid var(--light-gray); color: var(--text); padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Source Sans 3', sans-serif; text-decoration: none; display: inline-block; }
    .filter-btn:hover, .filter-btn.active { background: var(--crimson); border-color: var(--crimson); color: white; }

    /* ── NEED CARDS ── */
    .needs-grid { display: flex; flex-direction: column; gap: 16px; }
    .need-card { background: white; border: 1px solid var(--light-gray); border-radius: 6px; padding: 20px 22px; transition: box-shadow 0.2s, border-color 0.2s; border-left: 4px solid transparent; }
    .need-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.09); border-left-color: var(--blue); }
    .need-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
    .need-card h3 { font-size: 1rem; font-weight: 700; color: var(--dark); }
    .tag { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.73rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
    .tag-food     { background: #FFF3CD; color: #856404; }
    .tag-housing  { background: #D1ECF1; color: #0C5460; }
    .tag-financial{ background: #D4EDDA; color: #155724; }
    .tag-health   { background: #F8D7DA; color: #721C24; }
    .tag-academic { background: #E2D9F3; color: #4A235A; }
    .tag-other    { background: var(--light-gray); color: #555; }
    .need-card p { font-size: 0.9rem; color: #555; line-height: 1.6; margin-bottom: 14px; }
    .need-card-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--mid-gray); flex-wrap: wrap; gap: 8px; }
    .respond-btn { background: var(--blue); color: white; border: none; padding: 6px 16px; border-radius: 4px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: 'Source Sans 3', sans-serif; transition: background 0.2s; }
    .respond-btn:hover { background: var(--blue-dark); }
    .urgent-badge { background: var(--crimson); color: white; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 3px; text-transform: uppercase; letter-spacing: 0.04em; }

    /* ── FULFILLED RIBBON ── */
    .need-card.fulfilled { border-left-color: #2a7a4b; opacity: 0.82; }
    .fulfilled-ribbon { display: inline-flex; align-items: center; gap: 5px; background: #edf7f2; color: #2a7a4b; border: 1.5px solid #a3d9bc; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 10px; border-radius: 999px; flex-shrink: 0; }
    .need-card.fulfilled .need-card-top h3 { color: var(--mid-gray); text-decoration: line-through; text-decoration-color: #a3d9bc; }
    .need-card.fulfilled .respond-btn { display: none; }
    .need-card.fulfilled .flag-btn { display: none; }

    /* ── POST IMAGE ── */
    .need-card-image { margin-bottom: 12px; }
    .need-card-image img { width: 100%; max-height: 200px; object-fit: cover; border-radius: 4px; display: block; }

    /* ── FLAG BUTTON ── */
    .flag-btn { background: none; border: none; color: var(--mid-gray); font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 4px 8px; border-radius: 4px; font-family: 'Source Sans 3', sans-serif; transition: color 0.15s, background 0.15s; }
    .flag-btn:hover { color: #b85c00; background: #fff0e0; }
    .flag-btn.flagged { color: #b85c00; background: #fff0e0; }

    /* ── COMMENTS ── */
    .comments-section { border-top: 1px solid var(--light-gray); margin-top: 14px; padding-top: 12px; }
    .comments-toggle { background: none; border: none; color: var(--blue); font-size: 0.82rem; font-weight: 700; cursor: pointer; padding: 0; font-family: 'Source Sans 3', sans-serif; margin-bottom: 10px; display: block; }
    .comments-toggle:hover { text-decoration: underline; }
    .comments-list { display: none; flex-direction: column; gap: 10px; margin-bottom: 10px; }
    .comments-list.open { display: flex; }
    .comment-item { display: flex; gap: 10px; align-items: flex-start; }
    .comment-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--sage-dark); color: white; font-size: 0.68rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
    .comment-bubble { background: var(--off-white); border: 1px solid var(--light-gray); border-radius: 0 6px 6px 6px; padding: 8px 12px; flex: 1; }
    .comment-author { font-size: 0.78rem; font-weight: 700; color: var(--dark); margin-bottom: 2px; }
    .comment-text { font-size: 0.85rem; color: var(--text); line-height: 1.5; }
    .comment-time { font-size: 0.72rem; color: var(--mid-gray); margin-top: 3px; }
    .comment-input-row { display: flex; gap: 8px; align-items: center; margin-top: 6px; }
    .comment-input-row input { flex: 1; padding: 7px 12px; border: 1.5px solid var(--light-gray); border-radius: 999px; font-size: 0.85rem; font-family: 'Source Sans 3', sans-serif; outline: none; transition: border-color 0.2s; }
    .comment-input-row input:focus { border-color: var(--blue); }
    .comment-submit { background: var(--blue); color: white; border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.2s; }
    .comment-submit:hover { background: var(--blue-dark); }
    .guidelines-note { font-size: 0.75rem; color: var(--mid-gray); margin-top: 6px; }
    .guidelines-note a { color: var(--blue); font-weight: 600; text-decoration: none; }
    .guidelines-note a:hover { text-decoration: underline; }

    /* ── EMPTY STATE ── */
    .empty-state { text-align: center; padding: 48px 20px; color: var(--mid-gray); }
    .empty-state .icon { font-size: 2rem; display: block; margin-bottom: 10px; }
    .empty-state p { font-size: 0.95rem; line-height: 1.6; }

    /* ── FLAG MODAL ── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal { background: white; border-radius: 8px; padding: 28px; width: 100%; max-width: 420px; box-shadow: 0 16px 48px rgba(0,0,0,0.15); transform: translateY(12px); transition: transform 0.2s; }
    .modal-overlay.open .modal { transform: translateY(0); }
    .modal h3 { font-family: 'Merriweather', serif; font-size: 1.1rem; color: var(--dark); margin-bottom: 6px; }
    .modal p { font-size: 0.85rem; color: var(--mid-gray); line-height: 1.6; margin-bottom: 16px; }
    .modal label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--dark); margin-bottom: 5px; }
    .modal select, .modal textarea { width: 100%; border: 1.5px solid var(--light-gray); border-radius: 4px; padding: 8px 12px; font-size: 0.88rem; font-family: 'Source Sans 3', sans-serif; color: var(--text); background: white; outline: none; margin-bottom: 14px; transition: border-color 0.2s; }
    .modal select:focus, .modal textarea:focus { border-color: var(--blue); }
    .modal textarea { resize: vertical; min-height: 72px; }
    .modal-actions { display: flex; gap: 10px; margin-top: 4px; }
    .btn-flag-confirm { background: var(--crimson); color: white; border: none; padding: 10px 22px; border-radius: 4px; font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Source Sans 3', sans-serif; transition: background 0.2s; }
    .btn-flag-confirm:hover { background: var(--crimson-dark); }
    .btn-flag-cancel { background: white; color: var(--mid-gray); border: 1.5px solid var(--light-gray); padding: 10px 18px; border-radius: 4px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: 'Source Sans 3', sans-serif; }
    .btn-flag-cancel:hover { border-color: var(--mid-gray); color: var(--dark); }

    /* ── TOAST ── */
    #toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(60px); background: var(--dark); color: white; padding: 10px 20px; border-radius: 4px; font-size: 0.88rem; font-weight: 600; transition: transform 0.3s; z-index: 9999; pointer-events: none; white-space: nowrap; }

    /* ── SIDEBAR ── */
    .submit-panel { background: white; border: 1px solid var(--light-gray); border-radius: 6px; padding: 22px; margin-bottom: 24px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--dark); margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; border: 1.5px solid var(--light-gray); border-radius: 4px; padding: 8px 12px; font-size: 0.88rem; font-family: 'Source Sans 3', sans-serif; color: var(--text); transition: border-color 0.2s; background: white; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--blue); }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-submit { background: var(--blue); color: white; border: none; width: 100%; padding: 10px; border-radius: 4px; font-size: 0.92rem; font-weight: 700; cursor: pointer; font-family: 'Source Sans 3', sans-serif; transition: background 0.2s; }
    .form-submit:hover { background: var(--blue-dark); }
    .sidebar-panel { background: white; border: 1px solid var(--light-gray); border-radius: 6px; padding: 20px; margin-bottom: 20px; }
    .sidebar-panel h3 { font-family: 'Merriweather', serif; font-size: 1rem; color: var(--crimson); border-bottom: 2px solid var(--crimson); padding-bottom: 7px; margin-bottom: 14px; }
    .resource-link { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--light-gray); text-decoration: none; color: var(--text); font-size: 0.88rem; transition: color 0.2s; }
    .resource-link:last-child { border-bottom: none; }
    .resource-link:hover { color: var(--blue); }
    .resource-icon { width: 28px; height: 28px; background: var(--sage); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0; }
    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .stat-box { background: var(--off-white); border-radius: 5px; padding: 12px 10px; text-align: center; }
    .stat-box .num { font-size: 1.6rem; font-weight: 700; color: var(--crimson); display: block; }
    .stat-box .lbl { font-size: 0.75rem; color: var(--mid-gray); }

    /* ── FOOTER ── */
    footer { background: var(--crimson); color: white; margin-top: 60px; padding: 36px 24px 20px; }
    .footer-inner { max-width: 1100px; margin: 0 auto; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 32px; margin-bottom: 28px; }
    .footer-brand p { font-size: 0.85rem; opacity: 0.85; line-height: 1.65; margin-top: 8px; }
    .footer-col h4 { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 12px; opacity: 0.7; }
    .footer-col a { display: block; color: white; text-decoration: none; font-size: 0.85rem; opacity: 0.85; margin-bottom: 7px; transition: opacity 0.2s; }
    .footer-col a:hover { opacity: 1; text-decoration: underline; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.2); padding-top: 16px; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; opacity: 0.75; flex-wrap: wrap; gap: 8px; }
    .footer-bottom a { color: white; text-decoration: none; }
    .footer-bottom a:hover { text-decoration: underline; }
    .footer-links { display: flex; gap: 16px; }

    @media (max-width: 768px) {
      .main-wrapper { grid-template-columns: 1fr; }
      .footer-grid { grid-template-columns: 1fr 1fr; }
      .hero h1 { font-size: 1.6rem; }
    }
  </style>
</head>

<body>

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="inner">
      <a href="https://www.csuci.edu/students/" target="_blank">Current Students</a>
      <a href="https://www.csuci.edu/faculty/" target="_blank">Faculty</a>
      <a href="https://www.csuci.edu/staff/" target="_blank">Staff</a>
      <a href="https://www.csuci.edu/alumni/" target="_blank">Alumni</a>
      <a href="login.html" id="navLoginLink">CI Needs Login</a>
    </div>
  </div>

  <!-- HEADER -->
  <header>
    <div class="header-inner">
      <a class="logo" href="index.php">
        <img src="https://www.csuci.edu/img/brand/ci-logo.svg" alt="CSUCI Logo" style="height:46px; width:auto;" />
        <div class="logo-text">
          <span>California State University</span>
          <span>Channel Islands / CI Needs</span>
        </div>
      </a>
      <nav>
        <a href="index.php" class="active">Home</a>
        <a href="dashboard.html">Dashboard</a>
        <a href="resources.html">Resources</a>
        <a href="about.html">About</a>
        <a href="create-post.html" class="btn-nav">Create a Post</a>
        <span id="navUserLabel" style="display:none; font-size:0.88rem; font-weight:600; color:var(--mid-gray); padding:6px 8px;"></span>
        <a href="#" id="navLogoutLink" style="display:none; font-size:0.88rem; font-weight:600; color:var(--crimson); padding:6px 10px;" onclick="ciLogout()">Sign Out</a>
      </nav>
    </div>
  </header>

  <!-- ANNOUNCEMENTS -->
  <div class="announcements-bar">
    <div class="inner">
      <span class="ann-label">Announcements</span>
      <a class="ann-item" href="community-guidelines.html">CI Needs Community Guidelines</a>
      <span class="ann-divider">|</span>
      <a class="ann-item" href="https://www.csuci.edu/basicneeds/resources.htm">On and Off-Campus Resources</a>
      <span class="ann-divider">|</span>
      <a class="ann-item" href="https://www.csuci.edu/basicneeds/housing-assistance.htm">Housing Assistance</a>
    </div>
  </div>

  <!-- HERO -->
  <div class="hero">
    <h1>CI Needs — Student Resource Network</h1>
    <p>A community space for CSUCI students to share needs and offer support. Whether you need help or want to give it, you belong here.</p>
    <div class="hero-actions">
      <a class="btn-primary" href="create-post.html">Create a Post</a>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-wrapper">

    <!-- LEFT: Feed -->
    <div>

      <!-- Filter bar — clicking reloads the page with ?category=xxx -->
      <div class="filter-bar">
        <a class="filter-btn <?= $activeCategory === 'all'       ? 'active' : '' ?>" href="index.php">All</a>
        <a class="filter-btn <?= $activeCategory === 'food'      ? 'active' : '' ?>" href="index.php?category=food">Food</a>
        <a class="filter-btn <?= $activeCategory === 'housing'   ? 'active' : '' ?>" href="index.php?category=housing">Housing</a>
        <a class="filter-btn <?= $activeCategory === 'financial' ? 'active' : '' ?>" href="index.php?category=financial">Financial</a>
        <a class="filter-btn <?= $activeCategory === 'health'    ? 'active' : '' ?>" href="index.php?category=health">Health</a>
        <a class="filter-btn <?= $activeCategory === 'academic'  ? 'active' : '' ?>" href="index.php?category=academic">Academic</a>
        <a class="filter-btn <?= $activeCategory === 'other'     ? 'active' : '' ?>" href="index.php?category=other">Other</a>
      </div>

      <h2 class="section-title">
        Current Needs
        <?php if ($activeCategory !== 'all'): ?>
          <span style="font-family:'Source Sans 3',sans-serif; font-size:0.85rem; font-weight:400; color:var(--mid-gray); margin-left:10px;">
            — <?= ucfirst($activeCategory) ?>
            <a href="index.php" style="color:var(--blue); font-size:0.8rem; margin-left:6px;">Clear</a>
          </span>
        <?php endif; ?>
      </h2>

      <div class="needs-grid">
        <?php if (empty($posts)): ?>
          <div class="empty-state">
            <span class="icon">🔍</span>
            <p>No posts found<?= $activeCategory !== 'all' ? ' in the <strong>' . ucfirst($activeCategory) . '</strong> category' : '' ?>.<br>
               <a href="create-post.html" style="color:var(--blue); font-weight:600;">Be the first to post!</a></p>
          </div>
        <?php else: ?>
          <?php foreach ($posts as $post_row): ?>
            <?php
              $category    = htmlspecialchars($post_row['category'], ENT_QUOTES, 'UTF-8');
              $uc_category = ucfirst($category);
              $isFulfilled = (bool)$post_row['fulfilled'];

              // Optional image HTML
              $image_html = '';
              if (!empty($post_row['imagePath'])) {
                $safe_image = htmlspecialchars($post_row['imagePath'], ENT_QUOTES, 'UTF-8');
                $safe_alt   = htmlspecialchars($post_row['postTitle'], ENT_QUOTES, 'UTF-8');
                $image_html = "<div class=\"need-card-image\"><img src=\"{$safe_image}\" alt=\"{$safe_alt}\" /></div>";
              }

              // Reply count
              $reply_stmt = $db->prepare("SELECT COUNT(replyID) AS count FROM $reply_table WHERE postID = ?");
              $reply_stmt->execute([$post_row['postID']]);
              $reply_count = $reply_stmt->fetchColumn();

              $card_class = $isFulfilled ? 'need-card fulfilled' : 'need-card';
              $safe_title = htmlspecialchars($post_row['postTitle'], ENT_QUOTES, 'UTF-8');
              $safe_data  = htmlspecialchars($post_row['postData'],  ENT_QUOTES, 'UTF-8');
              $safe_user  = htmlspecialchars($post_row['username'],  ENT_QUOTES, 'UTF-8');
              $safe_email = htmlspecialchars($post_row['email'],     ENT_QUOTES, 'UTF-8');
              $safe_date  = htmlspecialchars($post_row['postDate'],  ENT_QUOTES, 'UTF-8');
              $post_id    = (int)$post_row['postID'];
            ?>
            <div class="<?= $card_class ?>">
              <div class="need-card-top">
                <div>
                  <h3><?= $safe_title ?></h3>
                </div>
                <div style="display:flex; gap:6px; align-items:center; flex-shrink:0;">
                  <span class="tag tag-<?= $category ?>"><?= $uc_category ?></span>
                  <?php if ($isFulfilled): ?>
                    <span class="fulfilled-ribbon">✓ Fulfilled</span>
                  <?php endif; ?>
                </div>
              </div>

              <?= $image_html ?>

              <p><?= $safe_data ?></p>

              <div class="need-card-meta">
                <span>Posted on <?= $safe_date ?> · <?= $safe_user ?> (<?= $safe_email ?>)</span>
                <button class="respond-btn">Respond</button>
                <button class="flag-btn" onclick="openFlagModal(this)" title="Flag this post">🚩 Flag</button>
              </div>

              <!-- Comments Section -->
              <div class="comments-section">
                <button class="comments-toggle" onclick="toggleComments(this)">
                  💬 <?= $reply_count ?> comment<?= $reply_count != 1 ? 's' : '' ?> — show
                </button>
                <div class="comments-list">
                  <?php
                    $replies = $db->prepare(
                      "SELECT * FROM $reply_table
                       INNER JOIN $user_table ON {$reply_table}.userID = {$user_table}.userID
                       WHERE postID = ?
                       ORDER BY replyDate ASC"
                    );
                    $replies->execute([$post_id]);
                    foreach ($replies->fetchAll(PDO::FETCH_ASSOC) as $reply_row):
                      $r_user  = htmlspecialchars($reply_row['username'],  ENT_QUOTES, 'UTF-8');
                      $r_email = htmlspecialchars($reply_row['email'],     ENT_QUOTES, 'UTF-8');
                      $r_data  = htmlspecialchars($reply_row['replyData'], ENT_QUOTES, 'UTF-8');
                      $r_date  = htmlspecialchars($reply_row['replyDate'], ENT_QUOTES, 'UTF-8');
                  ?>
                  <div class="comment-item">
                    <div class="comment-avatar"></div>
                    <div class="comment-bubble">
                      <div class="comment-author"><?= $r_user ?> (<?= $r_email ?>)</div>
                      <div class="comment-text"><?= $r_data ?></div>
                      <div class="comment-time">Posted on <?= $r_date ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>

                <div class="comment-input-row">
                  <input type="text" placeholder="Add a comment…"
                    onkeydown="if(event.key==='Enter') submitComment(this, <?= $post_id ?>)" />
                  <button class="comment-submit"
                    onclick="submitComment(this.previousElementSibling, <?= $post_id ?>)">➤</button>
                </div>
                <div class="guidelines-note">
                  Be respectful and helpful. <a href="community-guidelines.html">Community Guidelines</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT: Sidebar -->
    <div>

      <!-- Advanced Search -->
      <div class="submit-panel">
        <h2 class="section-title">Advanced Search</h2>
        <div class="form-group">
          <label for="searchKeyword">Keyword</label>
          <input type="text" id="searchKeyword" placeholder='e.g. "jacket", "textbook", "ramen"' />
        </div>
        <div class="form-group">
          <label for="searchCategory">Category</label>
          <select id="searchCategory">
            <option value="">All Categories</option>
            <option value="food">Food</option>
            <option value="housing">Housing</option>
            <option value="financial">Financial</option>
            <option value="health">Health</option>
            <option value="academic">Academic</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label for="searchCreator">Posted By</label>
          <input type="text" id="searchCreator" placeholder="Creator's name or username" />
        </div>
        <div class="form-group">
          <label for="searchType">Post Type</label>
          <select id="searchType">
            <option value="">Needs &amp; Offerings</option>
            <option value="need">Needs Only</option>
            <option value="have">Offerings Only</option>
          </select>
        </div>
        <!-- Date Search -->
        <div class="form-group">
          <label>Date Posted</label>
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
            <span style="font-size:0.82rem; color:var(--mid-gray);">Single Date</span>
            <div id="dateToggle" onclick="toggleDateMode()"
              style="width:40px; height:22px; background:var(--light-gray); border-radius:999px; cursor:pointer; position:relative; transition:background 0.2s; flex-shrink:0;">
              <div id="dateToggleThumb"
                style="width:16px; height:16px; background:white; border-radius:50%; position:absolute; top:3px; left:3px; transition:left 0.2s; box-shadow:0 1px 3px rgba(0,0,0,0.2);">
              </div>
            </div>
            <span style="font-size:0.82rem; color:var(--mid-gray);">Date Range</span>
          </div>
          <div id="singleDateField">
            <input type="date" id="searchDate"
              style="width:100%; border:1.5px solid var(--light-gray); border-radius:4px; padding:9px 12px; font-size:0.9rem; font-family:'Source Sans 3',sans-serif; color:var(--text); background:white;" />
            <div style="font-size:0.78rem; color:var(--mid-gray); margin-top:4px;">Show posts from this day only.</div>
          </div>
          <div id="dateRangeFields" style="display:none;">
            <div style="display:flex; align-items:center; gap:8px;">
              <div style="flex:1;">
                <input type="date" id="searchDateFrom"
                  style="width:100%; border:1.5px solid var(--light-gray); border-radius:4px; padding:9px 12px; font-size:0.9rem; font-family:'Source Sans 3',sans-serif; color:var(--text); background:white;" />
                <div style="font-size:0.75rem; color:var(--mid-gray); margin-top:3px;">From</div>
              </div>
              <span style="color:var(--mid-gray); font-size:0.9rem; flex-shrink:0; padding-bottom:16px;">—</span>
              <div style="flex:1;">
                <input type="date" id="searchDateTo"
                  style="width:100%; border:1.5px solid var(--light-gray); border-radius:4px; padding:9px 12px; font-size:0.9rem; font-family:'Source Sans 3',sans-serif; color:var(--text); background:white;" />
                <div style="font-size:0.75rem; color:var(--mid-gray); margin-top:3px;">To</div>
              </div>
            </div>
            <div style="font-size:0.78rem; color:var(--mid-gray); margin-top:6px;">Show posts within this date range.</div>
          </div>
        </div>
        <button class="form-submit" onclick="handleAdvancedSearch()">Search</button>
        <button onclick="clearSearch()"
          style="width:100%; margin-top:8px; padding:8px; background:white; border:1.5px solid var(--light-gray); border-radius:4px; font-size:0.88rem; font-weight:600; color:var(--mid-gray); cursor:pointer; font-family:'Source Sans 3',sans-serif; transition:all 0.2s;"
          onmouseover="this.style.borderColor='var(--mid-gray)'; this.style.color='var(--dark)'"
          onmouseout="this.style.borderColor='var(--light-gray)'; this.style.color='var(--mid-gray)'">
          Clear Search
        </button>
      </div>

      <!-- Community Stats — now live from the database -->
      <div class="sidebar-panel">
        <h3>Community Stats</h3>
        <div class="stats-grid">
          <div class="stat-box">
            <span class="num"><?= number_format($statTotalPosts) ?></span>
            <span class="lbl">Total Posts</span>
          </div>
          <div class="stat-box">
            <span class="num"><?= number_format($statFulfilled) ?></span>
            <span class="lbl">Needs Fulfilled</span>
          </div>
          <div class="stat-box">
            <span class="num"><?= number_format($statToday) ?></span>
            <span class="lbl">Posts Today</span>
          </div>
          <div class="stat-box">
            <span class="num"><?= number_format($statThisWeek) ?></span>
            <span class="lbl">Posts This Week</span>
          </div>
        </div>
      </div>

      <!-- Campus Resources -->
      <div class="sidebar-panel">
        <h3>Campus Resources</h3>
        <a class="resource-link" href="https://www.csuci.edu/basicneeds/food-assistance.htm" target="_blank">
          <div class="resource-icon"></div> Food Assistance
        </a>
        <a class="resource-link" href="https://www.csuci.edu/housing/" target="_blank">
          <div class="resource-icon"></div> Housing &amp; Residential Life
        </a>
        <a class="resource-link" href="https://www.csuci.edu/financialaid/" target="_blank">
          <div class="resource-icon"></div> Financial Aid Office
        </a>
        <a class="resource-link" href="https://www.csuci.edu/studenthealth/" target="_blank">
          <div class="resource-icon"></div> Student Health Services
        </a>
        <a class="resource-link" href="https://www.csuci.edu/learningresourcecenter/" target="_blank">
          <div class="resource-icon"></div> Learning Resource Center
        </a>
        <a class="resource-link" href="https://www.csuci.edu/caps/" target="_blank">
          <div class="resource-icon"></div> Counseling &amp; Psychological Services
        </a>
      </div>
    </div>
  </div>

  <!-- FLAG MODAL -->
  <div class="modal-overlay" id="flagModal" onclick="if(event.target===this) closeFlagModal()">
    <div class="modal">
      <h3>🚩 Flag This Post</h3>
      <p>Help keep CI Needs safe. Flagged posts are reviewed by our team. Please only flag posts that genuinely violate our guidelines.</p>
      <label for="flagReason">Reason for flagging <span style="color:var(--crimson)">*</span></label>
      <select id="flagReason">
        <option value="">Select a reason…</option>
        <option value="scam">Possible scam or fraudulent offer</option>
        <option value="inappropriate">Inappropriate or offensive content</option>
        <option value="spam">Spam or duplicate post</option>
        <option value="commercial">Commercial solicitation / advertising</option>
        <option value="illegal">Illegal item or activity</option>
        <option value="harassment">Harassment or hate speech</option>
        <option value="wellbeing">Concerns about a student's wellbeing</option>
        <option value="other">Other</option>
      </select>
      <label for="flagComment">Additional comments <span style="font-weight:400; color:var(--mid-gray);">(optional)</span></label>
      <textarea id="flagComment" placeholder="Any additional context that might help our team review this post…"></textarea>
      <div style="font-size:0.78rem; color:var(--mid-gray); margin-bottom:14px;">
        Your report is anonymous — the poster will not see who flagged their post.
      </div>
      <div class="modal-actions">
        <button class="btn-flag-confirm" onclick="submitFlag()">Submit Report</button>
        <button class="btn-flag-cancel" onclick="closeFlagModal()">Cancel</button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div id="toast">Done</div>

  <!-- FOOTER -->
  <footer>
    <div class="footer-inner">
      <div class="footer-grid">
        <div class="footer-brand">
          <strong style="font-size:1rem;">CSU Channel Islands / CI Needs</strong>
          <p>A peer-to-peer student resource network built for the Dolphin community. Connecting students with needs to those who can help.</p>
        </div>
        <div class="footer-col">
          <h4>Information For</h4>
          <a href="https://www.csuci.edu/students/" target="_blank">Current Students</a>
          <a href="https://www.csuci.edu/faculty/" target="_blank">Faculty</a>
          <a href="https://www.csuci.edu/staff/" target="_blank">Staff</a>
          <a href="https://www.csuci.edu/alumni/" target="_blank">Alumni</a>
        </div>
        <div class="footer-col">
          <h4>Resources</h4>
          <a href="https://www.csuci.edu/basicneeds/food-assistance.htm" target="_blank">Food Assistance</a>
          <a href="https://www.csuci.edu/financialaid/" target="_blank">Financial Aid</a>
          <a href="https://www.csuci.edu/studenthealth/" target="_blank">Health Services</a>
          <a href="https://www.csuci.edu/caps/" target="_blank">Counseling and Psychological Services</a>
        </div>
        <div class="footer-col">
          <h4>Contact</h4>
          <a href="https://www.csuci.edu/contact.htm?ftcontact" target="_blank">Contact Us</a>
          <a href="https://www.csuci.edu/emergencyinfo/" target="_blank">Emergency Info</a>
          <a href="https://maps.csuci.edu/" target="_blank">Campus Map</a>
          <a href="community-guidelines.html">Community Guidelines</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>&copy; 2026 California State University Channel Islands &middot; 1 University Dr., Camarillo, CA 93012 &middot; 805-437-8400</span>
        <div class="footer-links">
          <a href="https://www.csuci.edu/titleix/annual-report/clery/annual-security-report.htm" target="_blank">Annual Security Report</a>
          <a href="https://www.csuci.edu/titleix/" target="_blank">Title IX</a>
          <a href="https://www.csuci.edu/legal/" target="_blank">Legal</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // ── Advanced Search (sidebar) ──
    function handleAdvancedSearch() {
      const keyword  = document.getElementById('searchKeyword').value.trim().toLowerCase();
      const category = document.getElementById('searchCategory').value;
      const creator  = document.getElementById('searchCreator').value.trim().toLowerCase();
      const type     = document.getElementById('searchType').value;
      const dateMode = document.getElementById('dateToggle').dataset.mode || 'single';
      const singleDate = document.getElementById('searchDate').value;
      const dateFrom   = document.getElementById('searchDateFrom').value;
      const dateTo     = document.getElementById('searchDateTo').value;
      // TODO: wire to backend search endpoint
      console.log('Advanced search:', { keyword, category, creator, type, dateMode, singleDate, dateFrom, dateTo });
      alert(`Search ready to connect to backend!\nKeyword: "${keyword}"\nCategory: "${category}"\nPosted by: "${creator}"\nType: "${type}"`);
    }

    function toggleDateMode() {
      const toggle = document.getElementById('dateToggle');
      const thumb  = document.getElementById('dateToggleThumb');
      const single = document.getElementById('singleDateField');
      const range  = document.getElementById('dateRangeFields');
      const isRange = toggle.dataset.mode === 'range';
      if (isRange) {
        toggle.dataset.mode = 'single';
        toggle.style.background = 'var(--light-gray)';
        thumb.style.left = '3px';
        single.style.display = 'block';
        range.style.display  = 'none';
      } else {
        toggle.dataset.mode = 'range';
        toggle.style.background = 'var(--blue)';
        thumb.style.left = '21px';
        single.style.display = 'none';
        range.style.display  = 'block';
      }
    }

    function clearSearch() {
      document.getElementById('searchKeyword').value  = '';
      document.getElementById('searchCategory').value = '';
      document.getElementById('searchCreator').value  = '';
      document.getElementById('searchType').value     = '';
      document.getElementById('searchDate').value     = '';
      document.getElementById('searchDateFrom').value = '';
      document.getElementById('searchDateTo').value   = '';
      const toggle = document.getElementById('dateToggle');
      const thumb  = document.getElementById('dateToggleThumb');
      toggle.dataset.mode = 'single';
      toggle.style.background = 'var(--light-gray)';
      thumb.style.left = '3px';
      document.getElementById('singleDateField').style.display = 'block';
      document.getElementById('dateRangeFields').style.display = 'none';
    }

    // ── Flag modal ──
    let currentFlagBtn = null;
    function openFlagModal(btn) {
      currentFlagBtn = btn;
      document.getElementById('flagReason').value  = '';
      document.getElementById('flagComment').value = '';
      document.getElementById('flagModal').classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeFlagModal() {
      document.getElementById('flagModal').classList.remove('open');
      document.body.style.overflow = '';
    }
    function submitFlag() {
      const reason = document.getElementById('flagReason').value;
      if (!reason) { alert('Please select a reason for flagging.'); return; }
      // TODO: POST to flag endpoint
      closeFlagModal();
      if (currentFlagBtn) {
        currentFlagBtn.textContent = '🚩 Flagged';
        currentFlagBtn.classList.add('flagged');
        currentFlagBtn.disabled = true;
      }
      showToast('🚩 Post reported. Thank you — our team will review it.');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFlagModal(); });

    // ── Comments ──
    function toggleComments(btn) {
      const list = btn.nextElementSibling;
      const open = list.classList.toggle('open');
      const count = list.querySelectorAll('.comment-item').length;
      btn.textContent = '💬 ' + count + ' comment' + (count !== 1 ? 's' : '') + ' — ' + (open ? 'hide' : 'show');
    }

    function submitComment(input, postID) {
      const text = input.value.trim();
      if (!text) return;
      const formData = new FormData();
      const userID = sessionStorage.getItem("userID");
      formData.append('replyData', text);
      formData.append('postID',    postID);
      formData.append('userID',    userID);
      fetch('post-comment.php', { method: 'POST', body: formData });
      setTimeout(() => { location.reload(); }, 500);
    }

    // ── Toast ──
    let toastTimer;
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.transform = 'translateX(-50%) translateY(0)';
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => t.style.transform = 'translateX(-50%) translateY(60px)', 3000);
    }

    // ── CI Needs session sync ──
    function ciGetUser() {
      try { return JSON.parse(sessionStorage.getItem('ci_user')); } catch (e) { return null; }
    }
    function ciLogout() {
      sessionStorage.removeItem('ci_user');
      sessionStorage.removeItem('userID');
      localStorage.removeItem('ci_profile');
      window.location.href = 'index.php';
    }
    function ciSyncNav() {
      const user = ciGetUser();
      const loginLink  = document.getElementById('navLoginLink');
      const logoutLink = document.getElementById('navLogoutLink');
      const userLabel  = document.getElementById('navUserLabel');
      if (user) {
        if (loginLink)  loginLink.style.display  = 'none';
        if (logoutLink) logoutLink.style.display  = 'inline';
        if (userLabel) {
          userLabel.textContent = 'Hi, ' + (user.firstName || user.email.split('@')[0]);
          userLabel.style.display = 'inline';
        }
      } else {
        if (loginLink)  loginLink.style.display  = 'inline';
        if (logoutLink) logoutLink.style.display  = 'none';
        if (userLabel)  userLabel.style.display   = 'none';
      }
    }
    ciSyncNav();
  </script>

</body>
</html>