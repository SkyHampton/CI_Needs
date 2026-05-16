<?php
/* ── edit-post.php — loads existing post data and shows the edit form ── */

session_start();

/* ── Require login ── */
if (empty($_SESSION['userID'])) {
    header("Location: login.html");
    exit;
}

$host     = "137.184.46.194";
$dbUser   = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";
$table    = "CIN_Post";

$postID = (int)($_GET['id'] ?? 0);

if ($postID <= 0) {
    header("Location: dashboard.php");
    exit;
}

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $dbUser,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $db->prepare("SELECT * FROM $table WHERE postID = ?");
    $stmt->execute([$postID]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    // Post not found or doesn't belong to this user — redirect away
    if (!$post || (int)$post['userID'] !== (int)$_SESSION['userID']) {
        header("Location: dashboard.php");
        exit;
    }

} catch (PDOException $e) {
    die("<p style='color:red;padding:20px;'>Database error — please try again later.</p>");
}

// Safe values for form pre-fill
$safe_title    = htmlspecialchars($post['postTitle'], ENT_QUOTES, 'UTF-8');
$safe_data     = htmlspecialchars($post['postData'],  ENT_QUOTES, 'UTF-8');
$safe_contact  = htmlspecialchars($post['contact'] ?? '', ENT_QUOTES, 'UTF-8');
$safe_category = htmlspecialchars($post['category'],  ENT_QUOTES, 'UTF-8');
$post_id_int   = (int)$post['postID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Post | CI Needs — CSU Channel Islands</title>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --crimson: #c21228; --crimson-dark: #b41414;
      --blue: #1F6FAE; --blue-dark: #155887;
      --sage: #D6E4D6; --sage-dark: #B4CCBA;
      --white: #FFFFFF; --off-white: #F5F5F5;
      --light-gray: #E8E8E8; --mid-gray: #767676;
      --dark: #1A1A1A; --text: #333333;
      --success: #2a7a4b;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Source Sans 3', sans-serif; color: var(--text); background: var(--off-white); }

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

    /* ── PAGE HERO ── */
    .page-hero { background: linear-gradient(135deg, var(--sage) 0%, #e8f0e8 60%, #f0f5f0 100%); border-bottom: 1px solid var(--sage-dark); padding: 40px 24px 36px; text-align: center; }
    .page-hero h1 { font-family: 'Merriweather', serif; font-size: 1.9rem; color: var(--crimson); margin-bottom: 8px; }
    .page-hero p { font-size: 1rem; color: #4a4a4a; max-width: 500px; margin: 0 auto; line-height: 1.6; }

    /* ── FORM ── */
    .page-body { max-width: 680px; margin: 40px auto 80px; padding: 0 24px; }

    .form-panel { background: white; border: 1px solid var(--light-gray); border-radius: 8px; padding: 36px 32px; }
    .form-section-title { font-family: 'Merriweather', serif; font-size: 1rem; color: var(--crimson); border-bottom: 2px solid var(--crimson); padding-bottom: 7px; margin-bottom: 20px; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 0.87rem; font-weight: 700; color: var(--dark); margin-bottom: 5px; }
    .form-group label .required { color: var(--crimson); }
    .form-group .field-hint { font-size: 0.78rem; color: var(--mid-gray); margin-top: 4px; line-height: 1.4; }

    .form-input { width: 100%; padding: 11px 14px; border: 1.5px solid var(--light-gray); border-radius: 4px; background: var(--off-white); font-size: 0.9rem; font-family: 'Source Sans 3', sans-serif; color: var(--text); outline: none; transition: border-color 0.2s, background 0.2s; }
    .form-input:focus { border-color: var(--blue); background: white; }
    textarea.form-input { resize: vertical; min-height: 120px; }
    select.form-input { cursor: pointer; }

    .field-error { font-size: 0.78rem; color: var(--crimson); margin-top: 4px; display: none; }
    .field-error.visible { display: block; }
    .form-input.error { border-color: var(--crimson); }

    /* ── EDIT NOTICE ── */
    .edit-notice { background: #fff8e6; border: 1px solid #e8c84a; border-radius: 5px; padding: 12px 16px; margin-bottom: 22px; font-size: 0.85rem; color: #7a5a00; display: flex; gap: 10px; align-items: flex-start; }

    /* ── SUBMIT ROW ── */
    .form-submit-row { display: flex; gap: 10px; margin-top: 28px; align-items: center; }
    .btn-submit { background: var(--blue); color: white; border: none; padding: 12px 32px; border-radius: 4px; font-size: 0.95rem; font-weight: 700; cursor: pointer; font-family: 'Source Sans 3', sans-serif; transition: background 0.2s; }
    .btn-submit:hover { background: var(--blue-dark); }
    .btn-cancel { background: white; color: var(--mid-gray); border: 1.5px solid var(--light-gray); padding: 12px 24px; border-radius: 4px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; font-family: 'Source Sans 3', sans-serif; transition: all 0.2s; display: inline-block; }
    .btn-cancel:hover { border-color: var(--mid-gray); color: var(--dark); }

    /* ── SUCCESS ── */
    .success-panel { background: white; border: 1px solid var(--light-gray); border-radius: 8px; padding: 52px 32px; text-align: center; display: none; }
    .success-panel h2 { font-family: 'Merriweather', serif; font-size: 1.4rem; color: var(--success); margin-bottom: 10px; }
    .success-panel p { font-size: 0.95rem; color: var(--mid-gray); margin-bottom: 24px; line-height: 1.6; }
    .btn-home { display: inline-block; background: var(--blue); color: white; text-decoration: none; padding: 10px 26px; border-radius: 4px; font-size: 0.92rem; font-weight: 600; margin: 6px; transition: background 0.2s; }
    .btn-home:hover { background: var(--blue-dark); }
    .btn-home-outline { display: inline-block; background: white; color: var(--crimson); border: 2px solid var(--crimson); text-decoration: none; padding: 9px 22px; border-radius: 4px; font-size: 0.92rem; font-weight: 600; margin: 6px; transition: all 0.2s; }
    .btn-home-outline:hover { background: var(--crimson); color: white; }

    /* ── FOOTER ── */
    footer { background: var(--crimson); color: white; margin-top: 60px; padding: 36px 24px 20px; }
    .footer-inner { max-width: 1100px; margin: 0 auto; }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.2); padding-top: 16px; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; opacity: 0.75; flex-wrap: wrap; gap: 8px; }
    .footer-bottom a { color: white; text-decoration: none; }
    .footer-bottom a:hover { text-decoration: underline; }
    .footer-links { display: flex; gap: 16px; }

    @media (max-width: 600px) {
      .form-panel { padding: 24px 18px; }
      .form-submit-row { flex-direction: column; align-items: stretch; }
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <div class="inner">
      <a href="https://www.csuci.edu/students/" target="_blank">Current Students</a>
      <a href="https://www.csuci.edu/faculty/" target="_blank">Faculty</a>
      <a href="https://www.csuci.edu/staff/" target="_blank">Staff</a>
      <a href="https://www.csuci.edu/alumni/" target="_blank">Alumni</a>
      <a href="login.html" id="navLoginLink">CI Needs Login</a>
    </div>
  </div>

  <header>
    <div class="header-inner">
      <a class="logo" href="index.php">
        <img src="https://www.csuci.edu/img/brand/ci-logo.svg" alt="CSUCI Logo" style="height:46px; width:auto;"/>
        <div class="logo-text">
          <span>California State University</span>
          <span>Channel Islands / CI Needs</span>
        </div>
      </a>
      <nav>
        <a href="index.php">Home</a>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="resources.html">Resources</a>
        <a href="about.html">About</a>
        <a href="create-post.html" class="btn-nav">Create a Post</a>
        <span id="navUserLabel" style="display:none; font-size:0.88rem; font-weight:600; color:var(--mid-gray); padding:6px 8px;"></span>
        <a href="#" id="navLogoutLink" style="display:none; font-size:0.88rem; font-weight:600; color:var(--crimson); padding:6px 10px;" onclick="ciLogout()">Sign Out</a>
      </nav>
    </div>
  </header>

  <div class="page-hero">
    <h1>Edit Post</h1>
    <p>Update your post details below. Your changes will appear in the feed immediately.</p>
  </div>

  <div class="page-body">

    <!-- Edit Form -->
    <div class="form-panel" id="editForm">

      <div class="edit-notice">
        ✏️ You are editing: <strong>&ldquo;<?= $safe_title ?>&rdquo;</strong>
      </div>

      <p class="form-section-title">Post Details</p>

      <!-- Category -->
      <div class="form-group">
        <label for="postCategory">Category <span class="required">*</span></label>
        <select id="postCategory" class="form-input">
          <option value="">Select a category…</option>
          <option value="food"      <?= $safe_category === 'food'      ? 'selected' : '' ?>>Food</option>
          <option value="housing"   <?= $safe_category === 'housing'   ? 'selected' : '' ?>>Housing</option>
          <option value="financial" <?= $safe_category === 'financial' ? 'selected' : '' ?>>Financial</option>
          <option value="health"    <?= $safe_category === 'health'    ? 'selected' : '' ?>>Health</option>
          <option value="academic"  <?= $safe_category === 'academic'  ? 'selected' : '' ?>>Academic</option>
          <option value="other"     <?= $safe_category === 'other'     ? 'selected' : '' ?>>Other</option>
        </select>
        <div class="field-error" id="categoryError">Please select a category.</div>
      </div>

      <!-- Title -->
      <div class="form-group">
        <label for="postTitle">Title <span class="required">*</span></label>
        <input type="text" id="postTitle" class="form-input"
          value="<?= $safe_title ?>" maxlength="100"
          placeholder='e.g. "Looking for a winter jacket, size M"'/>
        <div class="field-hint">Keep it short and specific — 10 words or less works best.</div>
        <div class="field-error" id="titleError">Please enter a title.</div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label for="postDescription">Description <span class="required">*</span></label>
        <textarea id="postDescription" class="form-input"
          placeholder="Describe your post in detail…"><?= $safe_data ?></textarea>
        <div class="field-hint">Minimum 20 characters. Be as descriptive as possible.</div>
        <div class="field-error" id="descriptionError">Please enter a description (at least 20 characters).</div>
      </div>

      <!-- Contact Email -->
      <div class="form-group">
        <label for="postEmail">Contact Email <span style="font-weight:400; color:var(--mid-gray);">(optional)</span></label>
        <input type="email" id="postEmail" class="form-input"
          value="<?= $safe_contact ?>"
          placeholder="yourname@myci.csuci.edu"/>
        <div class="field-hint">Only visible to logged-in users.</div>
        <div class="field-error" id="emailError">Please enter a valid email address.</div>
      </div>

      <!-- Submit -->
      <div class="form-submit-row">
        <button class="btn-submit" onclick="handleUpdate()">Save Changes</button>
        <a href="dashboard.php" class="btn-cancel">Cancel</a>
      </div>

    </div>

    <!-- Success screen -->
    <div class="success-panel" id="successPanel">
      <h2>✅ Post updated!</h2>
      <p>Your changes have been saved and are now live in the feed.</p>
      <a href="index.php" class="btn-home">View Feed</a>
      <a href="dashboard.php" class="btn-home-outline">Back to Dashboard</a>
    </div>

  </div>

  <footer>
    <div class="footer-inner">
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
    const POST_ID = <?= $post_id_int ?>;

    function showErr(id) { document.getElementById(id).classList.add('visible'); }
    function hideErr(id) { document.getElementById(id).classList.remove('visible'); }
    function markErr(id, hasErr) {
      const el = document.getElementById(id);
      if (el) hasErr ? el.classList.add('error') : el.classList.remove('error');
    }

    function handleUpdate() {
      let valid = true;

      const category = document.getElementById('postCategory').value;
      if (!category) { showErr('categoryError'); markErr('postCategory', true); valid = false; }
      else           { hideErr('categoryError'); markErr('postCategory', false); }

      const title = document.getElementById('postTitle').value.trim();
      if (!title)    { showErr('titleError'); markErr('postTitle', true); valid = false; }
      else           { hideErr('titleError'); markErr('postTitle', false); }

      const desc = document.getElementById('postDescription').value.trim();
      if (desc.length < 20) { showErr('descriptionError'); markErr('postDescription', true); valid = false; }
      else                  { hideErr('descriptionError'); markErr('postDescription', false); }

      const email = document.getElementById('postEmail').value.trim();
      if (email && !email.includes('@')) { showErr('emailError'); markErr('postEmail', true); valid = false; }
      else                               { hideErr('emailError'); markErr('postEmail', false); }

      if (!valid) {
        document.querySelector('.error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      const formData = new FormData();
      formData.append('postID',    POST_ID);
      formData.append('category',  category);
      formData.append('postTitle', title);
      formData.append('postData',  desc);
      formData.append('contact',   email);

      fetch('edit_post.php', { method: 'POST', body: formData })
        .then(async res => {
          const data = await res.json().catch(() => ({}));
          if (!res.ok || !data.success) {
            alert(data.message || 'There was a problem saving your changes.');
            return;
          }
          document.getElementById('editForm').style.display    = 'none';
          document.getElementById('successPanel').style.display = 'block';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    // Clear error styling on input
    document.querySelectorAll('.form-input').forEach(el => {
      el.addEventListener('input', () => el.classList.remove('error'));
    });

    // ── CI Needs session sync ──
    function ciGetUser() { try { return JSON.parse(sessionStorage.getItem('ci_user')); } catch(e) { return null; } }
    function ciLogout() { sessionStorage.removeItem('ci_user'); sessionStorage.removeItem('userID'); localStorage.removeItem('ci_profile'); window.location.href = 'login.html'; }
    function ciSyncNav() {
      const user = ciGetUser();
      const loginLink  = document.getElementById('navLoginLink');
      const logoutLink = document.getElementById('navLogoutLink');
      const userLabel  = document.getElementById('navUserLabel');
      if (user) {
        if (loginLink)  loginLink.style.display  = 'none';
        if (logoutLink) logoutLink.style.display  = 'inline';
        if (userLabel) { userLabel.textContent = 'Hi, ' + (user.firstName || user.email.split('@')[0]); userLabel.style.display = 'inline'; }
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