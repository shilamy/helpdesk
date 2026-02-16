# KNBS Visitor Registration System

This system manages visitor registration, badge generation, QR code feedback, user management, and reporting for the Kenya National Bureau of Statistics. Below is a page-by-page explanation to help you understand the codebase and its workflow.

---

## 1. config.php
- **Purpose:** Central configuration file. Sets up database connection, session, site URL, app settings, and utility functions (e.g., `isLoggedIn()`).
- **Usage:** Included in almost every page to provide access to the database and session.

---

## 2. index.php
- **Purpose:** Entry point. Redirects users to the login page.

---

## 3. login.php
- **Purpose:** User authentication. Handles login form, checks credentials, and starts session.
- **Workflow:** If already logged in, redirects to dashboard. On POST, verifies user and logs activity.

---

## 4. dashboard.php
- **Purpose:** Main landing page after login. Shows statistics, charts, quick actions, and system status.
- **Features:** Sidebar for navigation, displays visitor stats, trends, and department breakdowns.

---

## 5. visitor-registration.php
- **Purpose:** Register new visitors. Handles form submission, validation, badge number generation, and host details.
- **Workflow:** Only accessible to authorized users. On POST, validates fields and saves visitor to database.

---

## 6. visitor-management.php
- **Purpose:** Manage active and historical visitors. Handles check-out, badge return, feedback, and QR code generation.
- **Workflow:** POST actions for checking out visitors, generating feedback tokens, and updating visitor status.

---

## 7. generate-badge.php
- **Purpose:** Generates and displays visitor badges after registration. Shows badge number, visitor details, and visit duration.

---

## 8. generate-qr.php / generate-universal-qr.php
- **Purpose:** Generates QR codes for visitor feedback. 
	- `generate-qr.php`: For specific visitors, links to their feedback page.
	- `generate-universal-qr.php`: For general feedback, links to universal feedback form.
- **Workflow:** Uses external QR code API, falls back to SVG if API fails.

---

## 9. print-qr.php
- **Purpose:** Printable version of visitor QR code. Displays visitor info and QR for feedback.

---

## 10. feedback.php
- **Purpose:** Feedback form accessed via QR code. Validates token, fetches visitor, marks QR as scanned, and saves feedback.
- **Workflow:** Handles feedback submission, prevents duplicate feedback, and updates visitor record.

---

## 11. reports.php / export-report.php
- **Purpose:** Reporting and analytics.
	- `reports.php`: Displays visitor stats, trends, and summaries.
	- `export-report.php`: Exports visitor data and stats as CSV for a given date range.

---

## 12. user-management.php
- **Purpose:** Manage system users. Add, edit, activate/deactivate users, and assign roles.
- **Workflow:** POST actions for adding users, updating info, and logging activity.

---

## 13. system-settings.php
- **Purpose:** System configuration. Allows admins to update settings, backup database, and clear old logs.

---

## 14. debug-info.php
- **Purpose:** Diagnostic page. Shows environment info, site URL, and test links for QR and feedback.

---

## 15. test-svg.php, tests/test-qr.php, tests/test-final.php
- **Purpose:** Testing utilities.
	- `test-svg.php`: Tests SVG QR code rendering.
	- `tests/test-qr.php`: Debugs QR code generation and visitor creation.
	- `tests/test-final.php`: Comprehensive test for QR code, visitor creation, and system links.

---

## 16. css/style.css, js/script.js, images/site.webmanifest
- **Purpose:** Frontend assets for styling, interactivity, and PWA support.

---

## 17. includes/header.php, includes/footer.php
- **Purpose:** Common layout components included in main pages.

---

## Database
- **Schema:** See `knbs_visitor_system.sql` for table definitions (visitors, users, logs, etc.).

---

## How the System Works

1. **User logs in** via `login.php`.
2. **Dashboard** displays stats and quick actions.
3. **Visitor registration** via `visitor-registration.php` creates a new visitor and badge.
4. **Visitor management** allows check-in/out, badge return, and feedback QR generation.
5. **QR codes** link to feedback forms (`feedback.php`), which update visitor records.
6. **Reports** provide analytics and export options.
7. **User management** and **system settings** are restricted to admins.
8. **Testing pages** help verify QR, badge, and feedback functionality.

---

**For detailed code logic, see comments in each PHP file.**  
**For styling and scripts, refer to the `css` and `js` folders.**

---