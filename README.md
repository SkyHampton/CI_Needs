# CI Needs
### Cal State Channel Islands — Student Resource Network
**COMP 550 | Spring 2026 | M.S. Computer Science**

 **Live Site:** [cineeds.cikeys.com](http://cineeds.cikeys.com) &nbsp;|&nbsp;  **Repo:** [github.com/comp550-spring26-csuci/CI_Needs](https://github.com/comp550-spring26-csuci/CI_Needs)

---

## About the Project

CI Needs is a full-stack, database-driven peer-to-peer resource network built for the Cal State Channel Islands student community. Students can post needs (food, clothing, textbooks, electronics, housing) or offerings, respond to one another through a comment system, and connect with campus basic needs resources — all through a responsive, accessible web interface.

The platform was designed and built from scratch over three Agile sprints as part of COMP 550 (Advanced Software Engineering), with a team of four M.S. Computer Science students. The project covers the full software development lifecycle: requirements gathering, UI/UX design, frontend implementation, backend development, database design, deployment, and iterative sprint delivery.

---

## The Team

| Name | Program | Focus Area |
|---|---|---|
| Sky Hampton | M.S. Computer Science | Frontend architecture, UI/UX, sprint coordination |
| Dana Harper | M.S. Computer Science | Frontend development, component design |
| AJ Herrera | M.S. Computer Science | Backend development, authentication, database |
| Fahad Uddin | M.S. Computer Science | Backend development, database, deployment |

---

## Technical Highlights

This section summarizes the key engineering decisions and implementations for portfolio and resume reference.

### Frontend

- **Multi-page responsive web application** built in semantic HTML5, CSS3, and vanilla JavaScript — no frameworks, demonstrating foundational web platform knowledge
- **CSS custom property design system** — all colors, typography, and spacing defined as CSS variables (`--crimson`, `--blue`, `--sage`, etc.), enabling consistent theming across 10+ pages
- **CSS Grid and Flexbox layout** — two-column responsive grid collapses to single-column on mobile; sticky header, sidebar panels, and card-based feed
- **Session-based authentication state** — `sessionStorage` and `localStorage` used to persist login state (`ci_user`, `userID`) across pages; nav bar dynamically updates to show username or Sign Out based on session
- **Client-side form validation** — real-time field-level error display with accessible error messages, email format enforcement, and required field checks before submission
- **Photo upload with live preview** — `FileReader` API used to render image previews in the create-post form before upload; drag-and-drop support with drop zone styling
- **Dynamic comment threads** — comments rendered from the database on page load; new comments submitted via `fetch()` POST to `post-comment.php` and reflected on page reload
- **Flag/report system** — modal overlay with reason dropdown and anonymous comment field; confirmation prevents accidental submission; flagged state persists visually per button instance
- **Fulfilled post ribbon** — server-rendered `fulfilled` CSS class adds green left border, strikethrough title, and ✓ Fulfilled badge; Respond and Flag buttons hidden automatically
- **Advanced search with date range toggle** — keyword, category, poster name, post type, single date, and date range fields; custom toggle switch animates between single/range modes
- **Admin dashboard** — separate dark-themed admin interface with six management panels: Flagged Posts, All Posts, On Hold, Expiring Soon, Post Graveyard, and User Management; inline message compose per post; delete confirmation modal
- **Toast notification system** — fixed-position slide-up/down toast with auto-dismiss, used across all interactive actions
- **Community Guidelines page** — full guidelines document with jump-navigation sidebar, consequence tier grid, and inline callout boxes; linked from flag modal, post form, and footer

### Backend

- **PHP with PDO (MySQL)** — all database interaction uses PDO prepared statements, preventing SQL injection; `htmlspecialchars()` applied to all output for XSS prevention
- **Database connection architecture** — connection established once at the top of each PHP file and reused across all queries on that page, avoiding redundant connections
- **Category filtering via GET parameters** — filter buttons navigate to `index.php?category=food`; PHP reads `$_GET['category']`, validates against a server-side whitelist, and applies a parameterized `WHERE` clause to the post query
- **Live community statistics** — four real-time aggregate SQL queries power the stats panel: total posts, fulfilled posts, posts today (`CURDATE()`), and posts this week (`DATE_SUB`)
- **User authentication** — `login.php` validates credentials, returns JSON (`{ success, userID, admin }`); admin flag routes to `admin-dashboard.php`; standard users routed to `index.php`
- **Post creation with file upload** — `create_post.php` handles multipart form data including image uploads stored to `/uploads/posts/` with hashed filenames
- **Comment/reply system** — `post-comment.php` inserts to `CIN_Reply` table with `postID`, `userID`, and `replyData`; reply counts and content rendered via JOIN query on page load
- **Post expiration policy** — posts older than 4 weeks trigger admin review; admin dashboard surfaces expiring posts with Extend or Expire Now actions

### Database

- **MySQL relational schema** with three core tables:
  - `CIN_User` — user accounts with email, hashed password, username, admin flag
  - `CIN_Post` — posts with title, body, category, date, fulfilled flag, imagePath, foreign key to `CIN_User`
  - `CIN_Reply` — comments with reply body, date, foreign keys to both `CIN_Post` and `CIN_User`
- **JOIN queries** across all three tables for feed rendering, comment display, and admin views
- **Aggregate queries** — `COUNT()`, `DATE()`, `CURDATE()`, `DATE_SUB()` used for statistics and expiration logic
- **Ordered result sets** — posts returned `ORDER BY postDate DESC` for chronological feed display

### Security Practices

- PDO prepared statements on all user-supplied query parameters
- Server-side category whitelist prevents URL parameter injection
- `htmlspecialchars()` on all database output before rendering to HTML
- Input validation on both client (JavaScript) and server (PHP) sides
- Hashed filenames on uploaded images prevent path traversal
- Admin routes protected by server-side role check (`admin` flag in `CIN_User`)

### Development Practices

- **Agile/Scrum methodology** — four sprints with defined deliverables, sprint reviews, and a scrum master role
- **Git branching workflow** — feature branches (`frontend/`, `fix/`) with pull requests reviewed and merged to `main`; no direct pushes to main
- **Separation of concerns** — frontend HTML/CSS/JS pages consume PHP backend endpoints; backend handles data and auth; database layer is isolated via PDO
- **Iterative delivery** — stub pages deployed early for navigation testing; backend features incrementally connected as sprints progressed
- **Commented handoff points** — `// TODO: connect to /api/...` comments throughout codebase provided clear integration targets for backend team across sprints

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3 (Grid, Flexbox, Custom Properties), Vanilla JavaScript (ES6+) |
| Backend | PHP 8, PDO |
| Database | MySQL (hosted on CIKeys) |
| Authentication | PHP session + JSON response, sessionStorage client-side |
| File Storage | Server filesystem (`/uploads/posts/`) |
| Fonts | Google Fonts (Merriweather, Source Sans 3) |
| Deployment | CIKeys shared hosting — [cineeds.cikeys.com](http://cineeds.cikeys.com) |
| Version Control | Git / GitHub |

---

## Pages & Files

| File | Type | Description |
|---|---|---|
| `index.php` | PHP | Main feed — live posts from DB, category filtering, live stats |
| `login.html` | HTML/JS | Authentication — POSTs to `login.php`, handles admin routing |
| `login.php` | PHP | Auth endpoint — validates credentials, returns JSON |
| `create-post.html` | HTML/JS | Post creation form — type toggle, categories, photo upload, availability |
| `create_post.php` | PHP | Post submission handler — inserts to DB, handles image upload |
| `post-comment.php` | PHP | Comment submission endpoint |
| `dashboard.html` | HTML/JS | User dashboard — my posts, messages, notification prefs, account settings |
| `profile.html` | HTML/JS | User profile editor — name, pronouns, degree, bio; persists to localStorage |
| `admin-dashboard.html` | HTML/JS | Admin panel — flagged posts, post management, user management, graveyard |
| `community-guidelines.html` | HTML | Full community guidelines with jump nav, consequence tiers |
| `resources.html` | HTML | Campus resource directory with descriptions and links |
| `about.html` | HTML | Project mission and team |

---

## Completed Features

- [x] Responsive multi-page frontend — homepage, dashboard, profile, admin, create post, resources, about, community guidelines
- [x] Live post feed rendered from MySQL database
- [x] Server-side category filtering via URL parameters
- [x] Post creation with photo upload and drag-and-drop preview
- [x] User authentication with admin role detection and routing
- [x] Comment/reply system — submit and display from database
- [x] Fulfilled post tracking with visual ribbon
- [x] Real-time community statistics (total, fulfilled, today, this week)
- [x] Flag/report system with reason modal
- [x] Advanced search with date range toggle
- [x] Session-based login state synced across all pages (Hi, Name / Sign Out)
- [x] Admin dashboard — flagged posts, holds, expiring, graveyard, user management
- [x] Post expiration policy (4-week reminder → auto-removal)
- [x] Community guidelines with tiered consequence system
- [x] Git branching workflow with pull requests
- [x] Deployed to live server

## In Progress / Future Work

- [ ] Direct messaging between users
- [ ] Email notification system (expiration reminders, message alerts)
- [ ] Full advanced search connected to backend
- [ ] Registration / account creation flow
- [ ] Password reset

---

## Campus Resources Referenced

| Resource | URL |
|---|---|
| Food Assistance | https://www.csuci.edu/basicneeds/food-assistance.htm |
| Financial Aid | https://www.csuci.edu/financialaid/ |
| Health Services | https://www.csuci.edu/studenthealth/ |
| Counseling and Psychological Services | https://www.csuci.edu/caps/ |
| Additional On and Off Campus Resources | https://www.csuci.edu/basicneeds/resources.htm |
| Housing Assistance | https://www.csuci.edu/basicneeds/housing-assistance.htm |

---

## Running Locally

No build tools required. Open any `.html` file directly in your browser, or use VS Code with the Live Server extension for PHP-free preview. For full PHP/database functionality, a local PHP server (XAMPP, MAMP, or `php -S localhost:8000`) pointed at the project directory is required.

```bash
# Quick local PHP server
php -S localhost:8000
# Then visit http://localhost:8000/index.php
```

> **Note:** Database credentials in `index.php` connect to the production CIKeys server. For local development, update the `$host`, `$user`, `$password`, and `$database` variables to point to a local MySQL instance.

---

*CI Needs — Built with care for every Dolphin. Spring 2026, CSUCI M.S. Computer Science.*
