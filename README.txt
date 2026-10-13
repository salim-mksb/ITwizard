==================================================
MKSB-IT WIZARD - SETUP & DEPLOYMENT INSTRUCTIONS
==================================================

1. LOCAL SETUP WITH LARAGON / PHPMYADMIN:
- Place the "MKSB-IT-WIZARD" folder inside your Laragon web root directory (usually `C:\laragon\www\MKSB-IT-WIZARD`).
- Open Laragon and click "Start All" (ensuring Apache and MySQL are running).
- Open phpMyAdmin (http://localhost/phpmyadmin/).
- Create a new database named `mksb_it_wizard` OR import the provided `database.sql` directly which creates the database and tables automatically.
- Open your browser and go to: http://localhost/MKSB-IT-WIZARD/

2. HOW TO TEST WITH MULTIPLE USERS:
- Open three separate browser windows or use incognito tabs.
- Go to http://localhost/MKSB-IT-WIZARD/ in all tabs.
- In Tab 1, select sender "Umairah".
- In Tab 2, select sender "Ifwat".
- In Tab 3, select sender "Amira".
- Send messages back and forth. The chat will auto-refresh every 3 seconds.
- Click "Save as Instruction" on any message to populate the shared Instructions section.
- Change statuses ("Pending", "In Progress", "Done") in one tab and watch them instantly synchronize across all browser sessions.

3. SECURITY NOTE (DEPLOYING ONLINE WITHOUT A LOGIN SYSTEM):
- Since this application does not use a login password, anyone with the direct URL can access the workspace and send messages as any of the three members.
- If you deploy this website to a public hosting server (such as a VPS or shared hosting), you should protect it by:
  1. Placing an `.htpasswd` (HTTP Basic Authentication) password protection file over the project directory so visitors must enter a password before viewing the URL.
  2. Utilizing a private subdomain URL that is not indexed by search engines.