<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard | CI Needs — CSU Channel Islands</title>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --crimson:      #c21228;
      --crimson-dark: #b41414;
      --blue:         #1F6FAE;
      --blue-dark:    #155887;
      --sage:         #D6E4D6;
      --sage-dark:    #B4CCBA;
      --white:        #FFFFFF;
      --off-white:    #F5F5F5;
      --light-gray:   #E8E8E8;
      --mid-gray:     #767676;
      --dark:         #1A1A1A;
      --text:         #333333;
      --green:        #2a7a4b;
      --orange:       #b85c00;
      --orange-bg:    #fff0e0;
      --warning:      #856404;
      --warning-bg:   #FFF3CD;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Source Sans 3', sans-serif; color: var(--text); background: var(--off-white); }

    /* ── TOP BAR ── */
    .top-bar { background: var(--dark); color: white; font-size: 0.78rem; padding: 5px 0; }
    .top-bar .inner { max-width: 1300px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; }
    .top-bar-left { display: flex; align-items: center; gap: 8px; }
    .admin-badge { background: var(--crimson); color: white; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; padding: 2px 8px; border-radius: 3px; }
    .top-bar a { color: rgba(255,255,255,0.75); text-decoration: none; }
    .top-bar a:hover { color: white; }

    /* ── HEADER ── */
    header { background: var(--dark); border-bottom: 2px solid var(--crimson); position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    .header-inner { max-width: 1300px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 60px; }
    .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .logo-text { display: flex; flex-direction: column; line-height: 1.1; }
    .logo-text span:first-child { font-size: 0.65rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.06em; }
    .logo-text span:last-child { font-size: 1rem; font-weight: 700; color: white; }
    .logo-text span:last-child em { color: var(--crimson); font-style: normal; }
    .header-right { display: flex; align-items: center; gap: 14px; }
    .admin-name { font-size: 0.85rem; color: rgba(255,255,255,0.75); font-weight: 600; }
    .btn-logout { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); padding: 6px 14px; border-radius: 4px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: 'Source Sans 3', sans-serif; transition: background 0.2s; text-decoration: none; }
    .btn-logout:hover { background: rgba(255,255,255,0.2); }
    .btn-view-site { background: var(--crimson); color: white; border: none; padding: 6px 14px; border-radius: 4px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: 'Source Sans 3', sans-serif; transition: background 0.2s; text-decoration: none; }
    .btn-view-site:hover { background: var(--crimson-dark); }

    /* ── LAYOUT ── */
    .admin-layout { max-width: 1300px; margin: 0 auto; padding: 24px 24px 80px; display: grid; grid-template-columns: 210px 1fr; gap: 24px; align-items: start; }

    /* ── SIDEBAR ── */
    .admin-sidebar { position: sticky; top: 72px; }
    .admin-nav { background: white; border: 1px solid var(--light-gray); border-radius: 6px; overflow: hidden; margin-bottom: 16px; }
    .admin-nav-item { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border: none; background: white; width: 100%; text-align: left; font-size: 0.88rem; font-weight: 600; color: var(--text); cursor: pointer; font-family: 'Source Sans 3', sans-serif; border-bottom: 1px solid var(--light-gray); transition: background 0.15s, color 0.15s; }
    .admin-nav-item:last-child { border-bottom: none; }
    .admin-nav-item:hover { background: var(--off-white); color: var(--crimson); }
    .admin-nav-item.active { background: #fdf3f4; color: var(--crimson); border-left: 3px solid var(--crimson); }
    .admin-nav-item .nav-icon { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
    .nav-badge { margin-left: auto; background: var(--crimson); color: white; font-size: 0.68rem; font-weight: 700; padding: 1px 7px; border-radius: 999px; }
    .nav-badge-warn { background: var(--warning); }

    /* Stats sidebar */
    .admin-stats { background: white; border: 1px solid var(--light-gray); border-radius: 6px; padding: 16px; }
    .admin-stats h4 { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--mid-gray); margin-bottom: 12px; }
    .admin-stat-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid var(--light-gray); font-size: 0.85rem; }
    .admin-stat-row:last-child { border-bottom: none; }
    .admin-stat-row .label { color: var(--mid-gray); }
    .admin-stat-row .value { font-weight: 700; color: var(--dark); }
    .admin-stat-row .value.red { color: var(--crimson); }
    .admin-stat-row .value.green { color: var(--green); }
    .admin-stat-row .value.orange { color: var(--orange); }

    /* ── MAIN PANELS ── */
    .admin-panel { display: none; }
    .admin-panel.active { display: block; }
    .panel-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 10px; }
    .panel-title { font-family: 'Merriweather', serif; font-size: 1.2rem; color: var(--crimson); border-bottom: 2px solid var(--crimson); padding-bottom: 7px; }
    .panel-desc { font-size: 0.85rem; color: var(--mid-gray); margin-bottom: 18px; line-height: 1.5; }

    /* ── POST TABLE ── */
    .admin-card { background: white; border: 1px solid var(--light-gray); border-radius: 6px; overflow: hidden; margin-bottom: 16px; }
    .admin-post { padding: 16px 18px; border-bottom: 1px solid var(--light-gray); }
    .admin-post:last-child { border-bottom: none; }
    .admin-post-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 8px; flex-wrap: wrap; }
    .admin-post-title { font-weight: 700; font-size: 0.95rem; color: var(--dark); flex: 1; }
    .admin-post-meta { font-size: 0.78rem; color: var(--mid-gray); margin-bottom: 10px; display: flex; gap: 14px; flex-wrap: wrap; }
    .admin-post-body { font-size: 0.85rem; color: var(--text); line-height: 1.55; margin-bottom: 12px; }
    .admin-actions { display: flex; gap: 6px; flex-wrap: wrap; }

    /* Tags */
    .tag { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .tag-need      { background: #fdf3f4; color: var(--crimson); }
    .tag-have      { background: #eef4fb; color: var(--blue); }
    .tag-flagged   { background: #fff0e0; color: var(--orange); }
    .tag-hold      { background: var(--warning-bg); color: var(--warning); }
    .tag-fulfilled { background: #edf7f2; color: var(--green); }
    .tag-expired   { background: var(--light-gray); color: var(--mid-gray); }
    .tag-removed   { background: #fdf3f4; color: var(--crimson); }

    /* Admin action buttons */
    .btn-a { padding: 5px 12px; border-radius: 4px; font-size: 0.78rem; font-weight: 700; cursor: pointer; border: none; font-family: 'Source Sans 3', sans-serif; transition: all 0.18s; }
    .btn-approve  { background: #edf7f2; color: var(--green); border: 1.5px solid #a3d9bc; }
    .btn-approve:hover  { background: var(--green); color: white; }
    .btn-hold     { background: var(--warning-bg); color: var(--warning); border: 1.5px solid #e0c050; }
    .btn-hold:hover     { background: var(--warning); color: white; }
    .btn-fulfill  { background: #edf7f2; color: var(--green); border: 1.5px solid #a3d9bc; }
    .btn-fulfill:hover  { background: var(--green); color: white; }
    .btn-expire   { background: var(--off-white); color: var(--mid-gray); border: 1.5px solid var(--light-gray); }
    .btn-expire:hover   { background: var(--mid-gray); color: white; }
    .btn-delete   { background: #fdf3f4; color: var(--crimson); border: 1.5px solid #e8b0b7; }
    .btn-delete:hover   { background: var(--crimson); color: white; }
    .btn-message  { background: #eef4fb; color: var(--blue); border: 1.5px solid #c0d8f0; }
    .btn-message:hover  { background: var(--blue); color: white; }
    .btn-restore  { background: #edf7f2; color: var(--green); border: 1.5px solid #a3d9bc; }
    .btn-restore:hover  { background: var(--green); color: white; }

    /* Flag reason box */
    .flag-reason { background: #fff0e0; border: 1px solid #e8c070; border-radius: 4px; padding: 10px 12px; margin-bottom: 10px; font-size: 0.82rem; }
    .flag-reason .flag-label { font-weight: 700; color: var(--orange); margin-bottom: 3px; }
    .flag-reason .flag-comment { color: var(--text); line-height: 1.5; }
    .flag-count { background: var(--orange-bg); color: var(--orange); font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; }

    /* Message compose */
    .message-compose { background: #eef4fb; border: 1px solid #c0d8f0; border-radius: 5px; padding: 14px; margin-top: 10px; display: none; }
    .message-compose textarea { width: 100%; border: 1.5px solid #c0d8f0; border-radius: 4px; padding: 8px 10px; font-size: 0.85rem; font-family: 'Source Sans 3', sans-serif; resize: vertical; min-height: 72px; outline: none; }
    .message-compose textarea:focus { border-color: var(--blue); }
    .message-compose-actions { display: flex; gap: 8px; margin-top:8px; }

    /* Graveyard */
    .graveyard-post { padding: 14px 18px; border-bottom: 1px solid var(--light-gray); opacity: 0.75; }
    .graveyard-post:last-child { border-bottom: none; }
    .graveyard-post-title { font-size: 0.9rem; font-weight: 700; color: var(--mid-gray); margin-bottom: 4px; }
    .graveyard-meta { font-size: 0.78rem; color: var(--mid-gray); margin-bottom: 8px; }
    .graveyard-reason { font-size: 0.8rem; color: var(--crimson); font-weight: 600; }

    /* Empty state */
    .empty-state { text-align: center; padding: 48px 20px; color: var(--mid-gray); }
    .empty-state .icon { font-size: 2rem; display: block; margin-bottom: 10px; }
    .empty-state p { font-size: 0.92rem; }

    /* ── MODALS ── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal { background: white; border-radius: 8px; padding: 28px; width: 100%; max-width: 440px; box-shadow: 0 16px 48px rgba(0,0,0,0.2); transform: translateY(12px); transition: transform 0.2s; }
    .modal-overlay.open .modal { transform: translateY(0); }
    .modal h3 { font-family: 'Merriweather', serif; font-size: 1.1rem; color: var(--dark); margin-bottom: 8px; }
    .modal p { font-size: 0.88rem; color: var(--mid-gray); line-height: 1.6; margin-bottom: 16px; }
    .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
    .btn-modal-confirm { background: var(--crimson); color: white; border: none; padding: 10px 22px; border-radius: 4px; font-size: 0.9rem; font-weight: 700; cursor: pointer; font-family: 'Source Sans 3', sans-serif; }
    .btn-modal-cancel  { background: white; color: var(--mid-gray); border: 1.5px solid var(--light-gray); padding: 10px 18px; border-radius: 4px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: 'Source Sans 3', sans-serif; }

    /* Toast */
    #adminToast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(60px); background: var(--dark); color: white; padding: 10px 20px; border-radius: 4px; font-size: 0.88rem; font-weight: 600; transition: transform 0.3s; z-index: 9999; pointer-events: none; white-space: nowrap; }

    @media (max-width: 900px) { .admin-layout { grid-template-columns: 1fr; } .admin-sidebar { position: static; } }
  </style>
</head>
<body>

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="inner">
      <div class="top-bar-left">
        <span class="admin-badge">Admin</span>
        <span style="color:rgba(255,255,255,0.5);">CI Needs Administration Panel</span>
      </div>
      <a href="index.html">← Back to Main Site</a>
    </div>
  </div>

  <!-- HEADER -->
  <header>
    <div class="header-inner">
      <a class="logo" href="admin-dashboard.html">
        <img src="https://www.csuci.edu/img/brand/ci-logo.svg" alt="CSUCI Logo" style="height:36px; width:auto; filter:brightness(0) invert(1);"/>
        <div class="logo-text">
          <span>CI Needs</span>
          <span>Admin <em>Dashboard</em></span>
        </div>
      </a>
      <div class="header-right">
        <span class="admin-name" id="adminNameLabel">Admin</span>
        <a href="index.html" class="btn-view-site">View Site</a>
        <a href="index.html" class="btn-logout" onclick="sessionStorage.removeItem('ci_user')">Sign Out</a>
      </div>
    </div>
  </header>

  <!-- ADMIN LAYOUT -->
  <div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
      <div class="admin-nav">
        <button class="admin-nav-item active" onclick="showPanel('flagged', this)">
          <span class="nav-icon"></span> Flagged Posts
          <span class="nav-badge">3</span>
        </button>
        <button class="admin-nav-item" onclick="showPanel('all', this)">
          <span class="nav-icon"></span> All Posts
        </button>
        <button class="admin-nav-item" onclick="showPanel('holds', this)">
          <span class="nav-icon"></span> On Hold
          <span class="nav-badge nav-badge-warn">1</span>
        </button>
        <button class="admin-nav-item" onclick="showPanel('expiring', this)">
          <span class="nav-icon"></span> Expiring Soon
          <span class="nav-badge nav-badge-warn">2</span>
        </button>
        <button class="admin-nav-item" onclick="showPanel('graveyard', this)">
          <span class="nav-icon"></span> Post Graveyard
        </button>
        <button class="admin-nav-item" onclick="showPanel('users', this)">
          <span class="nav-icon"></span> Users
        </button>
      </div>

      <div class="admin-stats">
        <h4>Site Overview</h4>
        <div class="admin-stat-row"><span class="label">Total Posts</span><span class="value">24</span></div>
        <div class="admin-stat-row"><span class="label">Active Posts</span><span class="value green">19</span></div>
        <div class="admin-stat-row"><span class="label">Flagged</span><span class="value red">3</span></div>
        <div class="admin-stat-row"><span class="label">On Hold</span><span class="value orange">1</span></div>
        <div class="admin-stat-row"><span class="label">Fulfilled</span><span class="value green">138</span></div>
        <div class="admin-stat-row"><span class="label">Total Users</span><span class="value">312</span></div>
      </div>
    </aside>

    <!-- MAIN -->
    <main>

      <!-- ── FLAGGED POSTS ── -->
      <div class="admin-panel active" id="panelFlagged">
        <div class="panel-header">
          <h2 class="panel-title"> Flagged Posts</h2>
        </div>
        <p class="panel-desc">Posts flagged by community members for review. Review each flag, then approve the post to keep it active, place it on hold, or remove it.
          <!-- TODO: connect to GET /api/admin/posts?status=flagged -->
        </p>

        <div class="admin-card">

          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title">
                <span class="tag tag-need">Need</span>&nbsp;
                Looking for anyone to help pay rent this month
              </div>
              <span class="flag-count"> 2 flags</span>
            </div>
            <div class="admin-post-meta">
              <span> Financial</span>
              <span> anonymous_user</span>
              <span> Posted 1 day ago</span>
            </div>
            <div class="flag-reason">
              <div class="flag-label">Flag reason: Possible scam / suspicious</div>
              <div class="flag-comment">"This post is asking for money directly, not a basic need item. Feels like it could be a scam."</div>
            </div>
            <div class="admin-post-body">I'm really struggling this month and my landlord is threatening eviction. If anyone can help with even $50 toward rent I would be so grateful. Venmo @username.</div>
            <div class="admin-actions">
              <button class="btn-a btn-approve"  onclick="adminAction('Post approved and restored to feed.', this)"> Approve</button>
              <button class="btn-a btn-hold"     onclick="adminAction('Post placed on hold.', this)"> Hold</button>
              <button class="btn-a btn-delete"   onclick="confirmDelete(this)"> Remove</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message Poster</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Write a message to the poster about this post…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent to poster.', this)">Send Message</button>
                <button class="btn-a btn-expire" onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>

          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title">
                <span class="tag tag-have">Offering</span>&nbsp;
                Free tutoring sessions — contact me directly
              </div>
              <span class="flag-count"> 1 flag</span>
            </div>
            <div class="admin-post-meta">
              <span> Academic</span>
              <span> tutor_pro99</span>
              <span> Posted 3 days ago</span>
            </div>
            <div class="flag-reason">
              <div class="flag-label">Flag reason: Commercial solicitation</div>
              <div class="flag-comment">"This account has been posting similar 'free tutoring' offers but then charges once you contact them."</div>
            </div>
            <div class="admin-post-body">Offering free tutoring for MATH 150 and COMP 101. Just message me directly on Instagram @tutorpro for details and scheduling.</div>
            <div class="admin-actions">
              <button class="btn-a btn-approve"  onclick="adminAction('Post approved and restored to feed.', this)"> Approve</button>
              <button class="btn-a btn-hold"     onclick="adminAction('Post placed on hold.', this)"> Hold</button>
              <button class="btn-a btn-delete"   onclick="confirmDelete(this)"> Remove</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message Poster</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Write a message to the poster about this post…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent to poster.', this)">Send Message</button>
                <button class="btn-a btn-expire" onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>

          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title">
                <span class="tag tag-need">Need</span>&nbsp;
                Need a place to stay — permanently
              </div>
              <span class="flag-count"> 1 flag</span>
            </div>
            <div class="admin-post-meta">
              <span> Housing</span>
              <span> dolphin_student</span>
              <span> Posted 2 days ago</span>
            </div>
            <div class="flag-reason">
              <div class="flag-label">Flag reason: Concerns about student wellbeing</div>
              <div class="flag-comment">"This student seems to be in a serious housing crisis. May need more support than this platform can offer."</div>
            </div>
            <div class="admin-post-body">I am currently unhoused and sleeping in my car. I have been looking for a room or couch to stay on. I go to CSUCI and work part time. Any help would mean everything.</div>
            <div class="admin-actions">
              <button class="btn-a btn-approve"  onclick="adminAction('Post approved — student may also need to be referred to Basic Needs Center.', this)"> Approve</button>
              <button class="btn-a btn-hold"     onclick="adminAction('Post placed on hold.', this)"> Hold</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message Poster</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Write a message to the poster — consider referring to CSUCI Basic Needs Center at (805) 437-3100…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent to poster.', this)">Send Message</button>
                <button class="btn-a btn-expire" onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ── ALL POSTS ── -->
      <div class="admin-panel" id="panelAll">
        <div class="panel-header">
          <h2 class="panel-title"> All Posts</h2>
          <div style="display:flex; gap:8px;">
            <select style="padding:6px 10px; border:1.5px solid var(--light-gray); border-radius:4px; font-size:0.85rem; font-family:'Source Sans 3',sans-serif;">
              <option>All Categories</option>
              <option>Food</option>
              <option>Housing</option>
              <option>Financial</option>
              <option>Health</option>
              <option>Academic</option>
              <option>Other</option>
            </select>
            <select style="padding:6px 10px; border:1.5px solid var(--light-gray); border-radius:4px; font-size:0.85rem; font-family:'Source Sans 3',sans-serif;">
              <option>All Types</option>
              <option>Needs</option>
              <option>Offerings</option>
            </select>
          </div>
        </div>
        <p class="panel-desc">
          Full post feed — all active, held, and fulfilled posts.
          <!-- TODO: connect to GET /api/admin/posts -->
        </p>
        <div class="admin-card">
          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title"><span class="tag tag-need">Need</span>&nbsp; Need groceries for the week</div>
            </div>
            <div class="admin-post-meta"><span> Food</span><span> Anonymous</span><span> 2 hours ago</span></div>
            <div class="admin-post-body">I ran out of my meal plan balance early and won't receive financial aid until next month. Looking for non-perishable foods or a meal plan swipe.</div>
            <div class="admin-actions">
              <button class="btn-a btn-fulfill"  onclick="adminAction('Post marked as fulfilled.', this)"> Fulfilled</button>
              <button class="btn-a btn-hold"     onclick="adminAction('Post placed on hold.', this)"> Hold</button>
              <button class="btn-a btn-expire"   onclick="adminAction('Post marked as expired.', this)"> Expire</button>
              <button class="btn-a btn-delete"   onclick="confirmDelete(this)"> Remove</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Write a message to the poster…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent.', this)">Send</button>
                <button class="btn-a btn-expire"  onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>
          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title"><span class="tag tag-have">Offering</span>&nbsp; Older laptop, fully functional</div>
            </div>
            <div class="admin-post-meta"><span> Electronics</span><span> Maria C.</span><span> 5 hours ago</span></div>
            <div class="admin-post-body">I upgraded my laptop so I'm giving away my old Dell Inspiron. Works great for notes and papers. First come, first served.</div>
            <div class="admin-actions">
              <button class="btn-a btn-fulfill"  onclick="adminAction('Post marked as fulfilled.', this)"> Fulfilled</button>
              <button class="btn-a btn-hold"     onclick="adminAction('Post placed on hold.', this)"> Hold</button>
              <button class="btn-a btn-expire"   onclick="adminAction('Post marked as expired.', this)"> Expire</button>
              <button class="btn-a btn-delete"   onclick="confirmDelete(this)"> Remove</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Write a message to the poster…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent.', this)">Send</button>
                <button class="btn-a btn-expire"  onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── ON HOLD ── -->
      <div class="admin-panel" id="panelHolds">
        <h2 class="panel-title" style="margin-bottom:16px;"> Posts On Hold</h2>
        <p class="panel-desc">Posts temporarily hidden from the feed pending review or poster response.
          <!-- TODO: connect to GET /api/admin/posts?status=hold -->
        </p>
        <div class="admin-card">
          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title"><span class="tag tag-hold">On Hold</span>&nbsp; Free tutoring — DM me on Instagram</div>
            </div>
            <div class="admin-post-meta"><span> Academic</span><span> tutor_pro99</span><span> Held since: 2 days ago</span></div>
            <div class="admin-post-body">Post placed on hold pending review for possible commercial solicitation.</div>
            <div class="admin-actions">
              <button class="btn-a btn-approve"  onclick="adminAction('Post restored to feed.', this)"> Restore</button>
              <button class="btn-a btn-delete"   onclick="confirmDelete(this)"> Remove Permanently</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message Poster</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Explain the hold to the poster…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent.', this)">Send</button>
                <button class="btn-a btn-expire"  onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── EXPIRING SOON ── -->
      <div class="admin-panel" id="panelExpiring">
        <h2 class="panel-title" style="margin-bottom:16px;"> Expiring Soon</h2>
        <p class="panel-desc">Posts approaching the 4-week limit. Reminder emails have been sent to these posters. If no response is received, posts will be automatically removed.
          <!-- TODO: connect to GET /api/admin/posts?expiring=true -->
        </p>
        <div class="admin-card">
          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title"><span class="tag tag-need">Need</span>&nbsp; Need winter jacket, size M</div>
            </div>
            <div class="admin-post-meta"><span> Clothing</span><span> student_j</span><span> Posted 26 days ago · expires in 2 days</span></div>
            <div class="admin-post-body">Looking for a warm jacket for the winter. Any style is fine, just needs to be warm and around a medium.</div>
            <div class="admin-actions">
              <button class="btn-a btn-approve"  onclick="adminAction('Post extended for another 4 weeks.', this)">↺ Extend</button>
              <button class="btn-a btn-expire"   onclick="adminAction('Post marked as expired and moved to graveyard.', this)"> Expire Now</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message Poster</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Follow up with the poster about their expiring post…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent.', this)">Send</button>
                <button class="btn-a btn-expire"  onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>
          <div class="admin-post">
            <div class="admin-post-top">
              <div class="admin-post-title"><span class="tag tag-have">Offering</span>&nbsp; Unopened hygiene kit items</div>
            </div>
            <div class="admin-post-meta"><span> Health</span><span> giving_back</span><span> Posted 27 days ago · expires in 1 day</span></div>
            <div class="admin-post-body">Have a bunch of unopened travel-size shampoo, conditioner, toothpaste, and soap. Free to anyone who needs it.</div>
            <div class="admin-actions">
              <button class="btn-a btn-approve"  onclick="adminAction('Post extended for another 4 weeks.', this)">↺ Extend</button>
              <button class="btn-a btn-expire"   onclick="adminAction('Post marked as expired and moved to graveyard.', this)"> Expire Now</button>
              <button class="btn-a btn-message"  onclick="toggleMessage(this)"> Message Poster</button>
            </div>
            <div class="message-compose">
              <textarea placeholder="Follow up with the poster…"></textarea>
              <div class="message-compose-actions">
                <button class="btn-a btn-message" onclick="adminAction('Message sent.', this)">Send</button>
                <button class="btn-a btn-expire"  onclick="this.closest('.message-compose').style.display='none'">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── GRAVEYARD ── -->
      <div class="admin-panel" id="panelGraveyard">
        <h2 class="panel-title" style="margin-bottom:16px;"> Post Graveyard</h2>
        <p class="panel-desc">All removed and expired posts. Posts here are no longer visible to users. They can be restored if removed in error.
          <!-- TODO: connect to GET /api/admin/posts?status=removed,expired -->
        </p>
        <div class="admin-card">
          <div class="graveyard-post">
            <div class="admin-post-top" style="margin-bottom:4px;">
              <div class="graveyard-post-title"><span class="tag tag-removed">Removed</span>&nbsp; $500 Venmo request for emergency</div>
              <button class="btn-a btn-restore" onclick="adminAction('Post restored to feed.', this)" style="font-size:0.75rem; padding:4px 10px;">↺ Restore</button>
            </div>
            <div class="graveyard-meta">Financial · anonymous_poster · Removed 3 days ago</div>
            <div class="graveyard-reason">Reason: Violated guidelines — direct money solicitation / possible scam</div>
          </div>
          <div class="graveyard-post">
            <div class="admin-post-top" style="margin-bottom:4px;">
              <div class="graveyard-post-title"><span class="tag tag-expired">Expired</span>&nbsp; Need BIOL 101 lab notebook</div>
              <button class="btn-a btn-restore" onclick="adminAction('Post restored to feed.', this)" style="font-size:0.75rem; padding:4px 10px;">↺ Restore</button>
            </div>
            <div class="graveyard-meta">Academic · student_k · Expired 1 week ago</div>
            <div class="graveyard-reason">Reason: No poster response after 4-week expiration reminder</div>
          </div>
          <div class="graveyard-post">
            <div class="admin-post-top" style="margin-bottom:4px;">
              <div class="graveyard-post-title"><span class="tag tag-fulfilled">Fulfilled</span>&nbsp; Looking for winter jacket, size M</div>
              <button class="btn-a btn-restore" onclick="adminAction('Post restored to feed.', this)" style="font-size:0.75rem; padding:4px 10px;">↺ Restore</button>
            </div>
            <div class="graveyard-meta">Clothing · student_j · Fulfilled 2 weeks ago</div>
            <div class="graveyard-reason">Reason: Marked fulfilled by poster</div>
          </div>
        </div>
      </div>

      <!-- ── USERS ── -->
      <div class="admin-panel" id="panelUsers">
        <h2 class="panel-title" style="margin-bottom:16px;"> User Management</h2>
        <p class="panel-desc">View and manage user accounts.
          <!-- TODO: connect to GET /api/admin/users -->
        </p>
        <div class="admin-card">
        <?php
        #sql server login info
        $host = "137.184.46.194";
        $user = "cineedsc_sky";
        $password = "N3ph@ndus";
        $database = "cineedsc_db";
        #sql table and foreign key info
        $post_table = "CIN_Post";
        $user_table = "CIN_User";
        $reply_table = "CIN_Reply";
        try {
            #connect to database
            $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
            foreach($db->query("SELECT * FROM $user_table") as $user_row){
              $banned = (bool)$user_row['banned'];
              $user_card_html = 
                "<div style=\"padding:16px 18px; border-bottom:1px solid var(--light-gray); display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap;\">
                    <div>
                    <div style=\"font-weight:700; font-size:0.92rem;\">{$user_row['username']} &nbsp; <span style=\"font-size:0.78rem; color:var(--mid-gray); font-weight:400;\"></span></div>
                    <div style=\"font-size:0.78rem; color:var(--mid-gray); margin-top:2px;\">Member since Jan 2026 · 3 posts · 0 flags</div>
                    </div>
                    <div style=\"display:flex; gap:6px;\">
                    <button class=\"btn-a btn-message\" onclick=\"adminAction('Message sent to user.', this)\"> Message</button>
                    <button class=\"btn-a btn-hold\"    onclick=\"adminAction('Account suspended.', this)\"> Suspend</button>
                    <button class=\"btn-a btn-delete\"  onclick=\"banUser({$user_row['userID']})\"> Ban</button>
                    </div>
                </div>";
              $user_card_banned = 
              "<div style=\"padding:16px 18px; border-bottom:1px solid var(--light-gray); display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap;\">
                    <div>
                    <div style=\"font-weight:700; font-size:0.92rem;\">{$user_row['username']} &nbsp; <span style=\"font-size:0.78rem; color:var(--mid-gray); font-weight:400;\"></span></div>
                    <div style=\"font-size:0.78rem; color:var(--mid-gray); margin-top:2px;\">Member since Jan 2026 · 3 posts · 0 flags</div>
                    <span class=\"tag tag-flagged\" style=\"font-size:0.68rem;\">Banned</span>
                    </div>
                    <div style=\"display:flex; gap:6px;\">
                    <button class=\"btn-a btn-message\" onclick=\"adminAction('Message sent to user.', this)\"> Message</button>
                    <button class=\"btn-a btn-hold\"    onclick=\"adminAction('Account suspended.', this)\"> Suspend</button>
                    <button class=\"btn-a btn-delete\"  onclick=\"banUser({$user_row['userID']})\"> Ban</button>
                    </div>
                </div>";

                if ($banned){
                  echo $user_card_banned;
                } else {
                  echo $user_card_html;
                }
            }

          #catch error
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage(). "<br/>";
            die();
        }
        ?>
        </div>
      </div>

    </main>
  </div>

  <!-- DELETE CONFIRM MODAL -->
  <div class="modal-overlay" id="deleteModal" onclick="if(event.target===this) closeModal()">
    <div class="modal">
      <h3>Remove this post?</h3>
      <p>This post will be moved to the Post Graveyard and hidden from all users. You can restore it later if needed.</p>
      <div class="modal-actions">
        <button class="btn-modal-confirm" onclick="confirmDeleteAction()">Yes, Remove Post</button>
        <button class="btn-modal-cancel"  onclick="closeModal()">Cancel</button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div id="adminToast">Done</div>

  <script>
    // ── Auth check — backend will handle real admin check ──
    // TODO: Replace with real admin role check from backend
    // For demo, any logged-in user can view this page
    const user = JSON.parse(sessionStorage.getItem('ci_user') || 'null');
    if (user) {
      document.getElementById('adminNameLabel').textContent = (user.firstName || 'Admin') + ' (Admin)';
    }

    // ── Panel switching ──
    function showPanel(id, btn) {
      document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.admin-nav-item').forEach(b => b.classList.remove('active'));
      const panelId = 'panel' + id.charAt(0).toUpperCase() + id.slice(1);
      document.getElementById(panelId).classList.add('active');
      if (btn) btn.classList.add('active');
    }

    // ── Message toggle ──
    function toggleMessage(btn) {
      const compose = btn.closest('.admin-post').querySelector('.message-compose');
      compose.style.display = compose.style.display === 'block' ? 'none' : 'block';
    }

    function banUser(userID) {
      const formData = new FormData();
      formData.append('userID', userID);

      fetch('ban-user.php', {
        method: 'POST',
        body: formData
      });
      setTimeout(() => {
        location.reload();
      }, 500);
    }

    // ── Delete confirm ──
    let pendingDeleteBtn = null;
    function confirmDelete(btn) {
      pendingDeleteBtn = btn;
      document.getElementById('deleteModal').classList.add('open');
    }
    function confirmDeleteAction() {
      closeModal();
      if (pendingDeleteBtn) {
        const post = pendingDeleteBtn.closest('.admin-post') || pendingDeleteBtn.closest('.graveyard-post');
        if (post) post.style.opacity = '0.35';
        adminAction('Post removed and moved to graveyard.', pendingDeleteBtn);
        pendingDeleteBtn = null;
      }
    }
    function closeModal() {
      document.getElementById('deleteModal').classList.remove('open');
    }

    // ── Toast ──
    let toastTimer;
    function adminAction(msg, btn) {
      const t = document.getElementById('adminToast');
      t.textContent = ' ' + msg;
      t.style.transform = 'translateX(-50%) translateY(0)';
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => t.style.transform = 'translateX(-50%) translateY(60px)', 2800);
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  </script>
</body>
</html>
