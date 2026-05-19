<?php
/* ── edit-post-form.php — edit form page, pre-filled from database ── */

session_start();

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
if ($postID <= 0) { header("Location: dashboard.php"); exit; }

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $dbUser, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $db->prepare("SELECT * FROM $table WHERE postID = ?");
    $stmt->execute([$postID]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post || (int)$post['userID'] !== (int)$_SESSION['userID']) {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    die("<p style='color:red;padding:20px;'>Database error — please try again later.</p>");
}

$safe_title    = htmlspecialchars($post['postTitle'],   ENT_QUOTES, 'UTF-8');
$safe_data     = htmlspecialchars($post['postData'],    ENT_QUOTES, 'UTF-8');
$safe_contact  = htmlspecialchars($post['contact'] ?? '', ENT_QUOTES, 'UTF-8');
$safe_category = htmlspecialchars($post['category'],    ENT_QUOTES, 'UTF-8');
$safe_type     = htmlspecialchars(strtolower($post['postType'] ?? 'need'), ENT_QUOTES, 'UTF-8');
$safe_image    = htmlspecialchars($post['imagePath'] ?? '', ENT_QUOTES, 'UTF-8');
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
      --crimson:#c21228; --crimson-dark:#b41414;
      --blue:#1F6FAE; --blue-dark:#155887;
      --sage:#D6E4D6; --sage-dark:#B4CCBA;
      --white:#FFFFFF; --off-white:#F5F5F5;
      --light-gray:#E8E8E8; --mid-gray:#767676;
      --dark:#1A1A1A; --text:#333333;
      --error:#c21228; --success:#2a7a4b;
    }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Source Sans 3',sans-serif; color:var(--text); background:var(--off-white); }

    .top-bar { background:var(--crimson); color:white; font-size:0.78rem; padding:5px 0; }
    .top-bar .inner { max-width:1100px; margin:0 auto; padding:0 24px; display:flex; justify-content:flex-end; gap:18px; }
    .top-bar a { color:white; text-decoration:none; }
    .top-bar a:hover { text-decoration:underline; }

    header { background:var(--white); border-bottom:1px solid var(--light-gray); position:sticky; top:0; z-index:100; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
    .header-inner { max-width:1100px; margin:0 auto; padding:0 24px; display:flex; align-items:center; justify-content:space-between; height:70px; }
    .logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
    .logo-text { display:flex; flex-direction:column; line-height:1.1; }
    .logo-text span:first-child { font-size:0.72rem; color:var(--mid-gray); text-transform:uppercase; letter-spacing:0.05em; }
    .logo-text span:last-child { font-size:1.1rem; font-weight:700; color:var(--crimson); }
    nav { display:flex; align-items:center; gap:6px; }
    nav a { color:var(--text); text-decoration:none; font-size:0.9rem; font-weight:600; padding:6px 12px; border-radius:4px; transition:background 0.2s,color 0.2s; }
    nav a:hover { background:var(--off-white); color:var(--crimson); }
    nav a.active { color:var(--crimson); border-bottom:2px solid var(--crimson); }
    .btn-nav { background:var(--blue); color:white!important; border-radius:4px; padding:7px 16px!important; transition:background 0.2s!important; }
    .btn-nav:hover { background:var(--blue-dark)!important; }

    .page-hero { background:linear-gradient(135deg,var(--sage) 0%,#e8f0e8 60%,#f0f5f0 100%); border-bottom:1px solid var(--sage-dark); padding:40px 24px 36px; text-align:center; }
    .page-hero h1 { font-family:'Merriweather',serif; font-size:1.9rem; color:var(--crimson); margin-bottom:8px; }
    .page-hero p { font-size:1rem; color:#4a4a4a; max-width:500px; margin:0 auto; line-height:1.6; }

    .page-body { max-width:680px; margin:40px auto 80px; padding:0 24px; }

    .form-panel { background:white; border:1px solid var(--light-gray); border-radius:8px; padding:36px 32px; }
    .form-section-title { font-family:'Merriweather',serif; font-size:1rem; color:var(--crimson); border-bottom:2px solid var(--crimson); padding-bottom:7px; margin-bottom:20px; }
    .form-divider { border:none; border-top:1px solid var(--light-gray); margin:24px 0; }

    /* ── POST TYPE TOGGLE ── */
    .type-toggle { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
    .type-option { border:2px solid var(--light-gray); border-radius:6px; padding:16px 14px; cursor:pointer; text-align:center; transition:all 0.2s; background:var(--off-white); }
    .type-option:hover { border-color:var(--mid-gray); }
    .type-option.selected-need { border-color:var(--crimson); background:#fdf3f4; }
    .type-option.selected-have { border-color:var(--blue); background:#eef4fb; }
    .type-option .type-icon { font-size:1.6rem; display:block; margin-bottom:6px; }
    .type-option .type-label { font-size:0.95rem; font-weight:700; color:var(--dark); display:block; }
    .type-option .type-sub { font-size:0.78rem; color:var(--mid-gray); display:block; margin-top:2px; }
    .type-option.selected-need .type-label { color:var(--crimson); }
    .type-option.selected-have .type-label { color:var(--blue); }

    .form-group { margin-bottom:18px; }
    .form-group label { display:block; font-size:0.87rem; font-weight:700; color:var(--dark); margin-bottom:5px; }
    .form-group label .required { color:var(--crimson); margin-left:2px; }
    .form-group .field-hint { font-size:0.78rem; color:var(--mid-gray); margin-top:4px; line-height:1.4; }
    .form-group input, .form-group select, .form-group textarea { width:100%; border:1.5px solid var(--light-gray); border-radius:4px; padding:9px 12px; font-size:0.9rem; font-family:'Source Sans 3',sans-serif; color:var(--text); background:white; transition:border-color 0.2s; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:var(--blue); }
    .form-group input.error, .form-group select.error, .form-group textarea.error { border-color:var(--error); }
    .form-group textarea { resize:vertical; min-height:100px; }
    .field-error { font-size:0.78rem; color:var(--error); margin-top:4px; display:none; }
    .field-error.visible { display:block; }

    /* ── AVAILABILITY GRID ── */
    .availability-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .availability-grid .form-group { margin-bottom:0; }

    /* ── EDIT NOTICE ── */
    .edit-notice { background:#fff8e6; border:1px solid #e8c84a; border-radius:5px; padding:12px 16px; margin-bottom:22px; font-size:0.85rem; color:#7a5a00; }

    /* ── PHOTO SECTION ── */
    .existing-photo { margin-bottom:12px; }
    .existing-photo img { max-height:160px; max-width:100%; border-radius:4px; object-fit:cover; display:block; border:1px solid var(--light-gray); }
    .existing-photo-label { font-size:0.78rem; color:var(--mid-gray); margin-top:4px; }

    .form-submit-row { display:flex; gap:12px; margin-top:28px; align-items:center; }
    .btn-submit { background:var(--blue); color:white; border:none; padding:12px 32px; border-radius:4px; font-size:0.95rem; font-weight:700; cursor:pointer; font-family:'Source Sans 3',sans-serif; transition:background 0.2s; }
    .btn-submit:hover { background:var(--blue-dark); }
    .btn-cancel { background:white; color:var(--mid-gray); border:1.5px solid var(--light-gray); padding:12px 24px; border-radius:4px; font-size:0.95rem; font-weight:600; cursor:pointer; font-family:'Source Sans 3',sans-serif; text-decoration:none; transition:all 0.2s; }
    .btn-cancel:hover { border-color:var(--mid-gray); color:var(--dark); }
    .submit-note { font-size:0.78rem; color:var(--mid-gray); margin-top:12px; line-height:1.5; }

    .success-panel { background:white; border:1px solid var(--light-gray); border-radius:8px; padding:52px 32px; text-align:center; display:none; }
    .success-panel h2 { font-family:'Merriweather',serif; font-size:1.4rem; color:var(--success); margin-bottom:10px; }
    .success-panel p { font-size:0.95rem; color:var(--mid-gray); line-height:1.6; max-width:380px; margin:0 auto 24px; }
    .btn-home { display:inline-block; background:var(--blue); color:white; text-decoration:none; padding:10px 26px; border-radius:4px; font-size:0.92rem; font-weight:600; margin:6px; transition:background 0.2s; }
    .btn-home:hover { background:var(--blue-dark); }
    .btn-home-outline { display:inline-block; background:white; color:var(--crimson); border:2px solid var(--crimson); text-decoration:none; padding:9px 22px; border-radius:4px; font-size:0.92rem; font-weight:600; margin:6px; transition:all 0.2s; }
    .btn-home-outline:hover { background:var(--crimson); color:white; }

    footer { background:var(--crimson); color:white; margin-top:60px; padding:36px 24px 20px; }
    .footer-inner { max-width:1100px; margin:0 auto; }
    .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:32px; margin-bottom:28px; }
    .footer-brand p { font-size:0.85rem; opacity:0.85; line-height:1.65; margin-top:8px; }
    .footer-col h4 { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:12px; opacity:0.7; }
    .footer-col a { display:block; color:white; text-decoration:none; font-size:0.85rem; opacity:0.85; margin-bottom:7px; transition:opacity 0.2s; }
    .footer-col a:hover { opacity:1; text-decoration:underline; }
    .footer-bottom { border-top:1px solid rgba(255,255,255,0.2); padding-top:16px; display:flex; justify-content:space-between; align-items:center; font-size:0.78rem; opacity:0.75; flex-wrap:wrap; gap:8px; }
    .footer-bottom a { color:white; text-decoration:none; }
    .footer-links { display:flex; gap:16px; }

    @media (max-width:600px) {
      .form-panel { padding:24px 18px; }
      .type-toggle { grid-template-columns:1fr; }
      .availability-grid { grid-template-columns:1fr; }
      .form-submit-row { flex-direction:column; align-items:stretch; text-align:center; }
      .footer-grid { grid-template-columns:1fr 1fr; }
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
    <p>Update your post below. Changes appear in the feed immediately.</p>
  </div>

  <div class="page-body">

    <div class="form-panel" id="editForm">

      <div class="edit-notice">
        Editing: <strong>&ldquo;<?= $safe_title ?>&rdquo;</strong>
      </div>

      <!-- Post Type -->
      <p class="form-section-title">What type of post is this?</p>
      <div class="type-toggle">
        <div class="type-option <?= $safe_type === 'need' ? 'selected-need' : '' ?>" id="typeNeed" onclick="selectType('need')">
          <span class="type-icon"></span>
          <span class="type-label">I Need Something</span>
          <span class="type-sub">Post a request for help or donations</span>
        </div>
        <div class="type-option <?= $safe_type === 'offering' ? 'selected-have' : '' ?>" id="typeHave" onclick="selectType('offering')">
          <span class="type-icon"></span>
          <span class="type-label">I'm Offering Something</span>
          <span class="type-sub">Donate or share an item or service</span>
        </div>
      </div>

      <hr class="form-divider"/>

      <!-- Category -->
      <div class="form-group">
        <label for="postCategory">Category <span class="required">*</span></label>
        <select id="postCategory">
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
        <input type="text" id="postTitle" value="<?= $safe_title ?>" maxlength="100"
          placeholder='e.g. "Looking for a winter jacket, size M"'/>
        <div class="field-hint">Keep it short and specific — 10 words or less works best.</div>
        <div class="field-error" id="titleError">Please enter a title for your post.</div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label for="postDescription">Description <span class="required">*</span></label>
        <textarea id="postDescription"
          placeholder="Describe what you need or what you're offering…"><?= $safe_data ?></textarea>
        <div class="field-hint">Minimum 20 characters. Be as descriptive as possible.</div>
        <div class="field-error" id="descriptionError">Please enter a description (at least 20 characters).</div>
      </div>

      <!-- Contact Email -->
      <div class="form-group">
        <label for="postEmail">Contact Email <span style="font-weight:400; color:var(--mid-gray);">(optional)</span></label>
        <input type="email" id="postEmail" value="<?= $safe_contact ?>" placeholder="yourname@myci.csuci.edu"/>
        <div class="field-hint">Only visible to logged-in users. Leave blank to remain anonymous.</div>
        <div class="field-error" id="emailError">Please enter a valid email address.</div>
      </div>

      <!-- Photo -->
      <div class="form-group">
        <label for="postPhoto">Photo <span style="font-weight:400; color:var(--mid-gray);">(optional)</span></label>

        <?php if (!empty($safe_image)): ?>
          <div class="existing-photo" id="existingPhotoWrap">
            <img src="<?= $safe_image ?>" alt="Current photo"/>
            <div class="existing-photo-label">Current photo</div>
            <button type="button"
              style="margin-top:6px; background:none; border:none; color:var(--crimson); font-size:0.8rem; font-weight:600; cursor:pointer; font-family:'Source Sans 3',sans-serif;"
              onclick="removeExistingPhoto()"> Remove photo</button>
          </div>
        <?php endif; ?>

        <div id="photoDropZone"
          style="border:2px dashed var(--light-gray); border-radius:4px; padding:24px; text-align:center; cursor:pointer; transition:all 0.2s; background:var(--off-white); <?= !empty($safe_image) ? 'margin-top:10px;' : '' ?>"
          onclick="document.getElementById('postPhoto').click()"
          ondragover="event.preventDefault(); this.style.borderColor='var(--blue)'; this.style.background='#eef4fb';"
          ondragleave="this.style.borderColor='var(--light-gray)'; this.style.background='var(--off-white)';"
          ondrop="handlePhotoDrop(event)">
          <div id="photoPreviewWrap" style="display:none; margin-bottom:12px;">
            <img id="photoPreview" src="" alt="Preview" style="max-height:180px; max-width:100%; border-radius:4px; object-fit:cover;"/>
          </div>
          <div id="photoPlaceholder">
            <div style="font-size:1.8rem; margin-bottom:8px;"></div>
            <div style="font-weight:600; font-size:0.9rem; color:var(--text);"><?= !empty($safe_image) ? 'Upload a different photo' : 'Click to upload or drag and drop' ?></div>
            <div style="font-size:0.78rem; color:var(--mid-gray); margin-top:4px;">JPG, PNG or GIF · Max 5MB</div>
          </div>
          <div id="photoFileName" style="display:none; font-size:0.82rem; color:var(--blue); font-weight:600; margin-top:8px;"></div>
        </div>
        <input type="file" id="postPhoto" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this)"/>
        <button type="button" id="photoRemoveBtn"
          style="display:none; margin-top:6px; background:none; border:none; color:var(--crimson); font-size:0.8rem; font-weight:600; cursor:pointer; font-family:'Source Sans 3',sans-serif;"
          onclick="removeNewPhoto()"> Remove new photo</button>
        <div class="field-hint">Upload a new photo to replace the existing one, or remove it entirely.</div>
      </div>

      <hr class="form-divider"/>

      <!-- Availability -->
      <p class="form-section-title">Availability <span style="font-family:'Source Sans 3',sans-serif; font-size:0.82rem; font-weight:400; color:var(--mid-gray); border:none; padding:0;">(optional — for pickup or drop-off)</span></p>

      <div class="availability-grid">
        <div class="form-group">
          <label for="availDate">Preferred Date</label>
          <input type="date" id="availDate"/>
          <div class="field-hint">Leave blank if flexible.</div>
        </div>
        <div class="form-group">
          <label for="availTime">Preferred Time</label>
          <input type="time" id="availTime"/>
          <div class="field-hint">Leave blank if flexible.</div>
        </div>
      </div>

      <div class="form-group" style="margin-top:14px;">
        <label for="availLocation">Pickup / Drop-off Location</label>
        <input type="text" id="availLocation"
          placeholder='e.g. "Bell Tower area", "Broome Library front entrance"'/>
        <div class="field-hint">A general campus location is fine — no need to share a specific room or address.</div>
      </div>

      <!-- Submit -->
      <div class="form-submit-row">
        <button class="btn-submit" onclick="handleUpdate()">Save Changes</button>
        <a href="dashboard.php" class="btn-cancel">Cancel</a>
      </div>
      <p class="submit-note">
        By saving, you confirm this post follows the
        <a href="community-guidelines.html" style="color:var(--blue); font-weight:700;">CI Needs community guidelines</a>.
      </p>

    </div><!-- end form-panel -->

    <!-- Success screen -->
    <div class="success-panel" id="successPanel">
      <h2> Post updated!</h2>
      <p>Your changes have been saved and are now live in the feed.</p>
      <a href="index.php" class="btn-home">View Feed</a>
      <a href="dashboard.php" class="btn-home-outline">Back to Dashboard</a>
    </div>

  </div><!-- end page-body -->

  <footer>
    <div class="footer-inner">
      <div class="footer-grid">
        <div class="footer-brand">
          <strong style="font-size:1rem;">CSU Channel Islands / CI Needs</strong>
          <p>A peer-to-peer student resource network built for the Dolphin community.</p>
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
    const POST_ID = <?= $post_id_int ?>;

    // ── Post type toggle — initialized from DB value ──
    let selectedType = '<?= $safe_type ?>';

    function selectType(type) {
      selectedType = type;
      document.getElementById('typeNeed').className =
        'type-option' + (type === 'need' ? ' selected-need' : '');
      document.getElementById('typeHave').className =
        'type-option' + (type === 'offering' ? ' selected-have' : '');
    }

    // ── Photo handlers ──
    let removeExisting = false;

    function removeExistingPhoto() {
      removeExisting = true;
      const wrap = document.getElementById('existingPhotoWrap');
      if (wrap) wrap.style.display = 'none';
    }

    function handlePhotoSelect(input) {
      if (input.files && input.files[0]) loadPhoto(input.files[0]);
    }

    function handlePhotoDrop(event) {
      event.preventDefault();
      const zone = document.getElementById('photoDropZone');
      zone.style.borderColor = 'var(--light-gray)';
      zone.style.background  = 'var(--off-white)';
      const file = event.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) loadPhoto(file);
    }

    function loadPhoto(file) {
      if (file.size > 5 * 1024 * 1024) { alert('Photo must be under 5MB.'); return; }
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('photoPreview').src = e.target.result;
        document.getElementById('photoPreviewWrap').style.display = 'block';
        document.getElementById('photoPlaceholder').style.display = 'none';
        document.getElementById('photoFileName').textContent = ' ' + file.name;
        document.getElementById('photoFileName').style.display = 'block';
        document.getElementById('photoRemoveBtn').style.display = 'block';
        document.getElementById('photoDropZone').style.borderColor = 'var(--blue)';
        document.getElementById('photoDropZone').style.background  = '#eef4fb';
        // If user uploads a new photo, don't also remove existing separately
        removeExisting = false;
      };
      reader.readAsDataURL(file);
    }

    function removeNewPhoto() {
      document.getElementById('postPhoto').value = '';
      document.getElementById('photoPreview').src = '';
      document.getElementById('photoPreviewWrap').style.display = 'none';
      document.getElementById('photoPlaceholder').style.display = 'block';
      document.getElementById('photoFileName').style.display = 'none';
      document.getElementById('photoRemoveBtn').style.display = 'none';
      document.getElementById('photoDropZone').style.borderColor = 'var(--light-gray)';
      document.getElementById('photoDropZone').style.background  = 'var(--off-white)';
    }

    // ── Validation helpers ──
    function showErr(id) { const el = document.getElementById(id); if (el) el.classList.add('visible'); }
    function hideErr(id) { const el = document.getElementById(id); if (el) el.classList.remove('visible'); }
    function markErr(id, hasErr) {
      const el = document.getElementById(id);
      if (el) hasErr ? el.classList.add('error') : el.classList.remove('error');
    }

    // ── Submit handler ──
    function handleUpdate() {
      let valid = true;

      const category = document.getElementById('postCategory').value;
      if (!category) { showErr('categoryError'); markErr('postCategory', true); valid = false; }
      else           { hideErr('categoryError'); markErr('postCategory', false); }

      const title = document.getElementById('postTitle').value.trim();
      if (!title) { showErr('titleError'); markErr('postTitle', true); valid = false; }
      else        { hideErr('titleError'); markErr('postTitle', false); }

      const desc = document.getElementById('postDescription').value.trim();
      if (desc.length < 20) { showErr('descriptionError'); markErr('postDescription', true); valid = false; }
      else                  { hideErr('descriptionError'); markErr('postDescription', false); }

      const email = document.getElementById('postEmail').value.trim();
      if (email && !email.includes('@')) { showErr('emailError'); markErr('postEmail', true); valid = false; }
      else                               { hideErr('emailError'); markErr('postEmail', false); }

      if (!valid) {
        document.querySelector('.error, .field-error.visible')?.scrollIntoView({ behavior:'smooth', block:'center' });
        return;
      }

      const formData = new FormData();
      formData.append('postID',       POST_ID);
      formData.append('postType',     selectedType);
      formData.append('category',     category);
      formData.append('postTitle',    title);
      formData.append('postData',     desc);
      formData.append('contact',      email);
      formData.append('removePhoto',  removeExisting ? '1' : '0');

      const fileInput = document.getElementById('postPhoto');
      if (fileInput && fileInput.files.length > 0) {
        formData.append('image', fileInput.files[0]);
      }

      fetch('edit_post.php', { method:'POST', body:formData })
        .then(async res => {
          const data = await res.json().catch(() => ({}));
          if (!res.ok || !data.success) {
            alert(data.message || 'There was a problem saving your changes.');
            return;
          }
          document.getElementById('editForm').style.display    = 'none';
          document.getElementById('successPanel').style.display = 'block';
          window.scrollTo({ top:0, behavior:'smooth' });
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    // Clear error styling on input
    document.querySelectorAll('input, select, textarea').forEach(el => {
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
</html><?php
/* ── edit_post.php — handles POST requests to update an existing post ── */

session_start();

$host     = "137.184.46.194";
$dbUser   = "cineedsc_sky";
$password = "N3ph@ndus";
$database = "cineedsc_db";
$table    = "CIN_Post";

$uploadDir    = __DIR__ . "/uploads/posts/";
$uploadUri    = "uploads/posts/";
$maxBytes     = 5 * 1024 * 1024;
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/gif"  => "gif",
    "image/webp" => "webp",
];
$allowedCategories = ['food', 'housing', 'financial', 'health', 'academic', 'other'];
$allowedPostTypes  = ['Need', 'Offering'];

function respond(bool $ok, string $message, array $extra = []): void {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(array_merge(["success" => $ok, "message" => $message], $extra));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Method not allowed.");
}

if (empty($_SESSION['userID'])) {
    http_response_code(401);
    respond(false, "You must be logged in to edit a post.");
}

/* ── Collect and sanitize fields ── */
$postID    = (int) ($_POST['postID']    ?? 0);
$category  = strtolower(trim($_POST['category']  ?? ""));
$postType  = ucfirst(strtolower(trim($_POST['postType'] ?? "")));
$postTitle = trim($_POST['postTitle'] ?? "");
$postData  = trim($_POST['postData']  ?? "");
$contact   = trim($_POST['contact']   ?? "");
$removePhoto = ($_POST['removePhoto'] ?? '') === '1';

/* ── Validate ── */
$errors = [];
if ($postID <= 0)                                   $errors[] = "Invalid post ID.";
if (!in_array($category, $allowedCategories, true)) $errors[] = "Invalid category.";
if (!in_array($postType, $allowedPostTypes, true))  $errors[] = "Invalid post type.";
if ($postTitle === "")                              $errors[] = "Title is required.";
elseif (strlen($postTitle) > 255)                  $errors[] = "Title must be 255 characters or fewer.";
if ($postData === "")                               $errors[] = "Description is required.";
elseif (strlen($postData) > 5000)                  $errors[] = "Description must be 5,000 characters or fewer.";

if (!empty($errors)) {
    http_response_code(422);
    respond(false, implode(" ", $errors));
}

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $dbUser, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verify post belongs to logged-in user
    $check = $db->prepare("SELECT userID, imagePath FROM $table WHERE postID = ?");
    $check->execute([$postID]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row) { http_response_code(404); respond(false, "Post not found."); }
    if ((int)$row['userID'] !== (int)$_SESSION['userID']) {
        http_response_code(403);
        respond(false, "You can only edit your own posts.");
    }

    // ── Handle photo ──
    $imagePath = $row['imagePath']; // keep existing by default

    // User clicked Remove Photo
    if ($removePhoto) {
        $imagePath = null;
    }

    // User uploaded a new photo
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => "File exceeds server upload size limit.",
                UPLOAD_ERR_FORM_SIZE  => "File exceeds form size limit.",
                UPLOAD_ERR_PARTIAL    => "File was only partially uploaded.",
                UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder.",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                UPLOAD_ERR_EXTENSION  => "Upload blocked by server extension.",
            ];
            http_response_code(422);
            respond(false, $uploadErrors[$file["error"]] ?? "Unknown upload error.");
        }

        if ($file["size"] > $maxBytes) {
            http_response_code(422);
            respond(false, "Image must be 5 MB or smaller.");
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file["tmp_name"]);

        if (!array_key_exists($mimeType, $allowedTypes)) {
            http_response_code(422);
            respond(false, "Invalid image type. Allowed: JPEG, PNG, GIF, WebP.");
        }

        $ext      = $allowedTypes[$mimeType];
        $filename = bin2hex(random_bytes(16)) . "." . $ext;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $dest = $uploadDir . $filename;
        if (!move_uploaded_file($file["tmp_name"], $dest)) {
            http_response_code(500);
            respond(false, "Server error: could not save the uploaded file.");
        }

        $imagePath = $uploadUri . $filename;
    }

    // ── Run the UPDATE ──
    $stmt = $db->prepare(
        "UPDATE $table
         SET postType  = :postType,
             category  = :category,
             postTitle = :postTitle,
             postData  = :postData,
             contact   = :contact,
             imagePath = :imagePath
         WHERE postID = :postID"
    );
    $stmt->execute([
        ":postType"  => $postType,
        ":category"  => $category,
        ":postTitle" => $postTitle,
        ":postData"  => $postData,
        ":contact"   => $contact !== "" ? $contact : null,
        ":imagePath" => $imagePath,
        ":postID"    => $postID,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    respond(false, "Database error: " . $e->getMessage());
}

respond(true, "Post updated successfully.", ["postID" => $postID]);