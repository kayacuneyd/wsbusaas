# Website Builder SaaS

## Project Structure
- `frontend/`: SvelteKit Application (Vercel)
- `backend/`: PHP API Application (Hostinger)

## Setup Instructions

### 1. Database Setup
1. Create a MySQL database on Hostinger.
2. Import `backend/database.sql` into the database using phpMyAdmin.
3. Note down the database credentials (host, name, user, password).

### 2. Backend Deployment (Hostinger)
1. Upload the contents of `backend/` to `public_html/` (or a subdomain folder like `public_html/api/`).
2. Update `config/Database.php` with your database credentials (or use environment variables if supported).
3. Update `config/config.php` with your `WEBHOOK_SECRET` and frontend URL.
4. Ensure `.htaccess` is uploaded and working.

### 3. Frontend Deployment (Vercel)
1. Push the `frontend/` directory to GitHub.
2. Import the project in Vercel.
3. **IMPORTANT**: Set the Environment Variable `VITE_API_URL` to your backend URL:
   - Go to Vercel Dashboard → Your Project → Settings → Environment Variables
   - Add: `VITE_API_URL` = `https://bezmidar.de/api` (or your backend domain)
   - Make sure to set it for **Production**, **Preview**, and **Development** environments
4. Redeploy after setting the environment variable.

**Note**: If `VITE_API_URL` is not set, the app will automatically use `https://bezmidar.de/api` in production, but it's recommended to set it explicitly.

### 4. Google Apps Script Integration
1. Go to [script.google.com](https://script.google.com) and create a new project.
2. Copy the code from `google_apps_script.js` into the editor.
3. Update `WEBHOOK_URL` and `WEBHOOK_SECRET` at the top of the script.
4. Run `setupTrigger()` once to start the 5-minute interval check.
5. Grant necessary permissions when prompted.

### 5. Ruul.io Integration
- Ensure your `orders.php` generates the correct payment link for your Ruul.io account.
- The system relies on email parsing, so ensure the email subject/body matching logic in Apps Script matches the actual emails you receive from Ruul.io.

## Development
- Frontend: `cd frontend && npm run dev`
- Backend: Use a local PHP server or Docker.

### Local Backend Setup (Mac/Linux)
1. Ensure you have PHP, Composer, and MySQL installed.
2. Create a MySQL database named `website_builder` (or update `backend/.env` if you want a different name).
3. Run `./start-backend.sh` in the root directory.
   - This script will install dependencies, setup the database tables, and start the PHP server at `localhost:8000`.
4. The frontend is already configured to talk to `localhost:8000` by default in development.

