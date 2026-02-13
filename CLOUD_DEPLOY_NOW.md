# ☁️ Deploy StudyTrack to Cloud - Step by Step

Deploy your StudyTrack app to the cloud for **FREE** and access it anytime from anywhere.

---

## 🎯 Best Free Cloud Options

### **Option 1: Railway + PlanetScale** (Recommended - Easiest)
- ✅ Free tier: 500 hours/month
- ✅ Automatic deployments from Git
- ✅ Built-in environment variables
- ✅ HTTPS included
- ✅ Takes 10 minutes

### **Option 2: InfinityFree + PlanetScale** (100% Free Forever)
- ✅ Unlimited free hosting
- ✅ Free SSL certificate
- ✅ No credit card required
- ✅ Takes 15 minutes

### **Option 3: DigitalOcean App Platform** (Easiest, $5/month)
- ✅ Easiest setup
- ✅ Auto-scaling
- ✅ Managed database
- ✅ Takes 5 minutes

---

## 🚀 OPTION 1: Railway Deployment (Recommended)

### Step 1: Setup Git Repository

```bash
# Initialize git if not already done
git init
git add .
git commit -m "Initial commit - Production ready"

# Push to GitHub
# Create a new repository on GitHub first, then:
git remote add origin https://github.com/YOUR_USERNAME/studytrack.git
git branch -M main
git push -u origin main
```

### Step 2: Setup PlanetScale Database

1. **Create Account**
   - Visit: https://planetscale.com
   - Sign up (free, no credit card)

2. **Create Database**
   ```
   Name: studytrack
   Region: AWS us-east-1 (or closest to you)
   ```

3. **Get Connection String**
   - Click "Connect"
   - Select "General" 
   - Copy credentials:
   ```
   Host: xxx.us-east-1.psdb.cloud
   Database: studytrack
   Username: xxx
   Password: xxx
   ```

4. **Create Initial Branch**
   - Click "Branches" → Keep using "main" branch

### Step 3: Deploy to Railway

1. **Create Account**
   - Visit: https://railway.app
   - "Start a New Project" → Sign in with GitHub

2. **Deploy from GitHub**
   - "New Project" → "Deploy from GitHub repo"
   - Select your `studytrack` repository
   - Click "Deploy Now"

3. **Add Environment Variables**
   Click "Variables" tab and add:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://studytrack-production.up.railway.app
   
   DATABASE_HOST=xxx.us-east-1.psdb.cloud
   DATABASE_NAME=studytrack
   DATABASE_USERNAME=xxx
   DATABASE_PASSWORD=xxx
   
   SESSION_COOKIE_SECURE=true
   SESSION_COOKIE_HTTPONLY=true
   ```

4. **Generate Domain**
   - Go to "Settings" tab
   - Click "Generate Domain"
   - Your app will be at: `https://studytrack-production.up.railway.app`

5. **Run Migration**
   - In Railway dashboard, go to "Deployments"
   - Click current deployment → "View Logs"
   - You'll need to run migration via Railway CLI or through a setup endpoint

### Step 4: Run Database Migration

**Option A: Add migration endpoint temporarily**

Create `public/setup.php`:
```php
<?php
// TEMPORARY SETUP FILE - DELETE AFTER USE
require_once __DIR__ . '/../database/migrate.php';
echo "<h1>Setup Complete!</h1>";
echo "<p>NOW DELETE THIS FILE: public/setup.php</p>";
echo "<p><a href='/'>Go to App</a></p>";
```

Then visit: `https://your-app.up.railway.app/setup.php`

**Option B: Use Railway CLI**
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Run migration
railway run php database/migrate.php
```

### Step 5: Test & Go Live!

1. Visit your app: `https://your-app.up.railway.app`
2. Login with: `student@diu.edu.bd` / `student123`
3. Test creating an event
4. **Delete setup.php if you created it**

---

## 🌐 OPTION 2: InfinityFree (100% Free Forever)

### Step 1: Create Hosting Account

1. Visit: https://infinityfree.net
2. Click "Sign Up Now" (no credit card needed)
3. Create account

### Step 2: Create Website

1. Click "Create Account"
2. Enter domain:
   - Use their subdomain: `yourusername.rf.gd`
   - Or connect your own domain
3. Wait 2-3 minutes for activation

### Step 3: Setup PlanetScale Database

(Same as Railway Option above)

### Step 4: Upload Files

1. **Using File Manager:**
   - Go to Control Panel → "Online File Manager"
   - Navigate to `htdocs/` folder
   - Delete default files
   - Upload your StudyTrack files (zip it first, then extract)

2. **Using FTP:**
   - Download FileZilla: https://filezilla-project.org
   - Get FTP credentials from InfinityFree control panel
   - Connect and upload all files to `htdocs/`

### Step 5: Configure Environment

1. Create `.env` file in `htdocs/`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourusername.rf.gd
   
   DATABASE_HOST=xxx.us-east-1.psdb.cloud
   DATABASE_NAME=studytrack
   DATABASE_USERNAME=xxx
   DATABASE_PASSWORD=xxx
   
   SESSION_COOKIE_SECURE=true
   SESSION_COOKIE_HTTPONLY=true
   ```

2. **IMPORTANT:** Update `APP_URL` in config:
   Edit `config/config.php` to use `.env` values (already done!)

### Step 6: Enable SSL

1. In InfinityFree control panel
2. Go to "SSL Certificates"
3. Enable "Free SSL" (CloudFlare)
4. Wait 5-10 minutes

### Step 7: Run Migration

Create `setup-once.php` in root:
```php
<?php
// Run this ONCE then delete this file
require_once __DIR__ . '/database/migrate.php';
```

Visit: `https://yourusername.rf.gd/setup-once.php`

Then DELETE the file!

### Step 8: Update .htaccess

Uncomment HTTPS redirect in `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 💰 OPTION 3: DigitalOcean App Platform ($5/month)

### Step 1: Create DigitalOcean Account

1. Visit: https://digitalocean.com
2. Sign up (get $200 free credit for 60 days with student pack)
3. Add payment method

### Step 2: Create App

1. Click "Create" → "Apps"
2. Connect GitHub repository
3. Select your `studytrack` repo
4. Auto-detected as "PHP app" ✓

### Step 3: Configure

1. **Build Command:** (leave empty)
2. **Run Command:** (auto-detected)
3. **HTTP Port:** 8080
4. **Plan:** Basic ($5/month)

### Step 4: Add PlanetScale Database

(Same setup as above)

### Step 5: Environment Variables

In App Platform:
- Settings → Environment Variables
- Add all from `.env.example`

### Step 6: Deploy

1. Click "Create Resources"
2. Wait 5 minutes for deployment
3. Visit your app URL
4. Run migration via setup endpoint

---

## 📋 Post-Deployment Checklist

### Immediate Tasks
- [ ] Visit your app URL - it loads
- [ ] Create `.env` or set environment variables
- [ ] Run database migration **ONCE**
- [ ] Login with test account
- [ ] Create a test event
- [ ] Verify event saved to database
- [ ] DELETE migration/setup files
- [ ] Enable HTTPS redirect

### Security Tasks
- [ ] Change all default passwords
- [ ] Delete test accounts (or change passwords)
- [ ] Remove `test-api.html`
- [ ] Remove `production-check.php` (or protect it)
- [ ] Verify `.htaccess` is working
- [ ] Check error logs

### Optional Enhancements
- [ ] Setup custom domain
- [ ] Configure email notifications
- [ ] Setup backups (PlanetScale auto-backups)
- [ ] Add monitoring (UptimeRobot - free)
- [ ] Setup CDN (Cloudflare - free)

---

## 🔧 Quick Setup Scripts

### One-Time Setup Endpoint

Create `setup-once.php` in root folder:

```php
<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Visit this URL once, then DELETE THIS FILE
 */

// Prevent running twice
$markerFile = __DIR__ . '/.setup-complete';
if (file_exists($markerFile)) {
    die('Setup already completed. Delete .setup-complete file to run again.');
}

echo "<html><head><title>Setup</title></head><body>";
echo "<h1>StudyTrack Setup</h1>";
echo "<pre>";

try {
    // Run migration
    require_once __DIR__ . '/database/migrate.php';
    
    // Create marker file
    file_put_contents($markerFile, date('Y-m-d H:i:s'));
    
    echo "</pre>";
    echo "<h2>✅ Setup Complete!</h2>";
    echo "<p><strong>IMPORTANT:</strong> Delete these files now:</p>";
    echo "<ul>";
    echo "<li>setup-once.php (this file)</li>";
    echo "<li>database/migrate.php</li>";
    echo "</ul>";
    echo "<p><a href='/'>Go to App →</a></p>";
    
} catch (Exception $e) {
    echo "</pre>";
    echo "<h2>❌ Setup Failed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
```

### Health Check Endpoint

Create `health.php` in root:

```php
<?php
/**
 * Health Check Endpoint
 * Use this for monitoring services
 */
header('Content-Type: application/json');

$status = 'healthy';
$checks = [];

// Check database
try {
    require_once __DIR__ . '/config/Database.php';
    $pdo = getDbConnection();
    $checks['database'] = 'connected';
} catch (Exception $e) {
    $status = 'unhealthy';
    $checks['database'] = 'failed';
}

// Check tables
try {
    $tables = ['users', 'sections', 'section_members', 'events'];
    $stmt = $pdo->query("SHOW TABLES");
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $checks['tables'] = count(array_intersect($tables, $existing)) === 4 ? 'ok' : 'missing';
} catch (Exception $e) {
    $checks['tables'] = 'error';
}

// Check session
$checks['session'] = session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive';

// Response
echo json_encode([
    'status' => $status,
    'timestamp' => date('c'),
    'checks' => $checks
], JSON_PRETTY_PRINT);
```

---

## 🐛 Troubleshooting Cloud Deployment

### Issue: "Database connection failed"

**Railway/DigitalOcean:**
- Check environment variables are set correctly
- Verify PlanetScale database is "Awake" (not sleeping)
- Check connection string has no extra spaces

**InfinityFree:**
- Some free hosts block external MySQL
- Use PlanetScale's SSL connection
- Or use their free MySQL database

### Issue: "500 Error" after deployment

```bash
# Check logs
# Railway: Dashboard → Deployments → View Logs
# DigitalOcean: App → Runtime Logs
# InfinityFree: Control Panel → Error Logs

# Common fixes:
# 1. Check .htaccess syntax
# 2. Verify PHP version (7.4+)
# 3. Check file permissions
# 4. Verify .env file exists
```

### Issue: ".htaccess not working"

**Railway:**
Create `railway.toml`:
```toml
[build]
builder = "NIXPACKS"

[deploy]
startCommand = "php -S 0.0.0.0:$PORT -t ."
```

**InfinityFree:**
- Already supports .htaccess
- Make sure it's in `htdocs/` root

### Issue: "Migration fails"

- Check database credentials
- Verify PlanetScale branch is "active"
- Run migration via web endpoint instead of CLI
- Check error logs for specific SQL errors

---

## 📊 Cost Comparison

| Provider | Cost | Database | SSL | Domain | Easy? |
|----------|------|----------|-----|---------|-------|
| Railway | Free 500hrs | PlanetScale Free | ✓ | Subdomain | ⭐⭐⭐⭐⭐ |
| InfinityFree | Free Forever | PlanetScale Free | ✓ | Subdomain | ⭐⭐⭐ |
| DigitalOcean | $5/mo | PlanetScale Free | ✓ | Subdomain | ⭐⭐⭐⭐⭐ |
| Heroku | $5/mo | $5/mo | ✓ | Subdomain | ⭐⭐⭐⭐ |

---

## 🎉 Recommended Path for You

Since you want it **accessible anytime**, here's my recommendation:

### 🥇 **Best Choice: Railway + PlanetScale**

**Why:**
- ✅ 500 hours/month free (enough for testing/light use)
- ✅ Auto-deployment from Git
- ✅ Easy environment variables
- ✅ Automatic HTTPS
- ✅ Can upgrade later if needed

**Steps:**
1. Push code to GitHub: 5 min
2. Setup PlanetScale: 5 min
3. Deploy to Railway: 3 min
4. Add environment variables: 2 min
5. Run migration: 2 min
6. **DONE!** Access anytime at your Railway URL

---

## 🚦 Quick Start Commands

```bash
# 1. Setup Git (if not done)
git init
git add .
git commit -m "Production ready"

# 2. Push to GitHub
# Create repo on GitHub first
git remote add origin https://github.com/YOUR_USERNAME/studytrack.git
git push -u origin main

# 3. Deploy to Railway
# Visit railway.app → Deploy from GitHub

# 4. That's it! Your app is live!
```

---

## 📞 Need Help?

**Common Questions:**

**Q: Do I need a credit card?**
A: No for Railway (GitHub auth) or InfinityFree. Yes for DigitalOcean (but $200 free).

**Q: Will it stay online forever?**
A: Railway: 500 hours/month. InfinityFree: Forever. DigitalOcean: As long as you pay.

**Q: Can I use my own domain?**
A: Yes! All options support custom domains. Add in settings after deployment.

**Q: Is my data safe?**
A: Yes! PlanetScale has automatic backups. You can export anytime.

---

**Ready to deploy?** Let's start with Railway! 🚀

**Next:** Tell me which option you prefer, and I'll guide you through it step by step!
