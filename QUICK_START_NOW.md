# 🚀 Quick Start Guide - Run StudyTrack Now!

## Step 1: Set Up Database (Choose One)

### Option A: Local MySQL (Fastest for Testing)

1. **Install XAMPP** (if not installed):
   - Download: https://www.apachefriends.org/
   - Install and start MySQL from control panel

2. **Create Database:**
   ```sql
   -- Open phpMyAdmin (http://localhost/phpmyadmin) or MySQL command line
   CREATE DATABASE studytrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Run Migration:**
   ```powershell
   cd C:\Users\ifti2\Documents\cal
   php database/migrate.php
   ```

### Option B: PlanetScale (Free Cloud Database)

1. **Create Account:**
   - Go to https://planetscale.com
   - Sign up (free tier)

2. **Create Database:**
   - Click "New database"
   - Name: `studytrack`
   - Region: Choose closest to you

3. **Get Credentials:**
   - Click "Connect"
   - Create password
   - Copy connection details

4. **Set Environment Variables:**
   ```powershell
   $env:DATABASE_HOST="aws.connect.psdb.cloud"
   $env:DATABASE_NAME="studytrack"
   $env:DATABASE_USERNAME="your_username_here"
   $env:DATABASE_PASSWORD="your_password_here"
   ```

5. **Run Migration:**
   ```powershell
   cd C:\Users\ifti2\Documents\cal
   php database/migrate.php
   ```

---

## Step 2: Start PHP Server

```powershell
cd C:\Users\ifti2\Documents\cal
php -S localhost:8000 router.php
```

**Expected Output:**
```
PHP 8.x Development Server (http://localhost:8000) started
```

---

## Step 3: Open Browser & Login

1. **Open:** http://localhost:8000

2. **Login with Test Credentials:**
   - **Student Account:**
     - Email: `student@diu.edu.bd`
     - Password: `student123`
     - Account Type: Student
   
   - **Teacher Account:**
     - Email: `teacher@diu.edu.bd`
     - Password: `teacher123`
     - Account Type: Teacher

---

## Step 4: Test Features

### As Student:
1. ✅ View calendar with events
2. ✅ Create new event (will be pending)
3. ✅ Join a section
4. ✅ View tasks/events list
5. ✅ Check profile stats

### As Teacher:
1. ✅ View all sections
2. ✅ Go to Moderate page
3. ✅ Approve/Reject pending events
4. ✅ Create events (auto-approved)
5. ✅ See all section members

---

## Step 5: Test API (Optional)

1. **Open:** http://localhost:8000/test-api.html

2. **Click "Run All Tests"**

3. **Expected Results:**
   - ✅ GET /api/user/stats.php - Pass
   - ✅ GET /api/events/list.php - Pass
   - ✅ POST /api/events/create.php - Pass
   - ✅ POST /api/sections/join.php - Pass

---

## 🔍 Verify Database

### Check Tables Created:
```sql
-- In phpMyAdmin or MySQL command line
USE studytrack;
SHOW TABLES;
```

**Expected Tables:**
- events
- section_members
- sections
- users

### Check Data Migrated:
```sql
-- Check users
SELECT id, name, email, role FROM users;

-- Check sections
SELECT id, name, code FROM sections;

-- Check events
SELECT id, title, date, status FROM events;
```

---

## ⚠️ Troubleshooting

### "Database connection failed"
```powershell
# Test PHP PDO MySQL extension
php -m | Select-String -Pattern "pdo_mysql"

# If not installed, enable in php.ini:
# extension=pdo_mysql
```

### "Migration failed"
- Check database exists: `SHOW DATABASES;`
- Check credentials are correct
- For PlanetScale: Verify IP not blocked

### "Can't login"
- Run migration script first
- Passwords are now hashed (use exact credentials above)
- Clear browser cookies and try again

### "Events not showing"
- Check if user has joined a section
- Students only see approved events
- Check events table: `SELECT * FROM events;`

### "API returns HTML not JSON"
- PHP error occurred
- Check error_log file
- Add to config/database.php: `ini_set('display_errors', 1);`

---

## 📁 Project Structure Quick Reference

```
cal/
├── api/                    # REST API endpoints (Phase 5)
│   ├── events/            # Event CRUD operations
│   ├── sections/          # Section join/leave
│   └── user/              # User stats
├── config/
│   ├── database.php       # PDO connection (Phase 1)
│   ├── db_functions.php   # Query wrapper (Phase 2)
│   └── config.php         # Session management
├── database/
│   ├── schema.sql         # Table definitions (Phase 1)
│   └── migrate.php        # Migration script (Phase 1)
├── pages/
│   ├── calendar.php       # Main calendar view
│   ├── tasks.php          # Event list
│   ├── sections.php       # Section management
│   ├── profile.php        # User profile
│   └── moderate.php       # Teacher moderation
├── auth/
│   ├── login.php          # Database auth (Phase 2)
│   └── register.php       # User registration (Phase 2)
└── assets/js/
    └── api-client.js      # AJAX wrapper (Phase 5)
```

---

## 📊 What to Expect

### Before Migration:
- ❌ Login shows "Invalid credentials"
- ❌ Database connection error
- ❌ Events don't save

### After Migration:
- ✅ Login works with test credentials
- ✅ Events display on calendar
- ✅ Creating events saves to database
- ✅ Students see approved events
- ✅ Teachers can moderate events

---

## 🎯 Success Indicators

Run this checklist to verify everything works:

### Phase 1 (Database):
- [ ] Migration script runs without errors
- [ ] 4 tables exist in database
- [ ] 4 users inserted (check passwords are hashed)
- [ ] 4 sections created
- [ ] 10+ events inserted

### Phase 2 (Backend):
- [ ] Login with credentials works
- [ ] Registration creates new database user
- [ ] Query functions return data

### Phase 3 (Pages):
- [ ] Calendar shows events from database
- [ ] Creating event saves (check events table)
- [ ] Profile shows correct stats
- [ ] Sections page lists data from database

### Phase 4 (Forms):
- [ ] Event form submits to PHP
- [ ] Join section button works
- [ ] Moderation approve/reject works
- [ ] All operations persist after page reload

### Phase 5 (API):
- [ ] No page reload when creating event
- [ ] Loading spinner appears
- [ ] Toast notifications show
- [ ] API test page shows all pass
- [ ] Console shows no errors

---

## 🔄 If You Need to Reset

### Reset Database:
```powershell
# Delete and recreate database
# In MySQL:
DROP DATABASE studytrack;
CREATE DATABASE studytrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Re-run migration
php database/migrate.php
```

### Clear Browser Data:
- Press Ctrl+Shift+Delete
- Clear cookies and cache
- Refresh page (Ctrl+F5)

---

## 📞 Next Steps After Testing

Once everything works:

1. **Read Documentation:**
   - [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) - Phases 1-4 details
   - [PHASE_5_API_GUIDE.md](PHASE_5_API_GUIDE.md) - API reference
   - [IMPLEMENTATION_COMPLETE.md](IMPLEMENTATION_COMPLETE.md) - Full summary

2. **Deploy to Production:**
   - Use PlanetScale for database (free 5GB)
   - Use InfinityFree or Railway for hosting (free)
   - Set environment variables on hosting
   - Run migration on production

3. **Add More Features:**
   - Email notifications
   - File attachments
   - Calendar export (iCal)
   - Real-time updates

---

## 🎉 You're Ready!

**Current Status:** All 5 phases implemented and ready to test!

**What You Have:**
- ✅ Database-driven application
- ✅ REST API with 10 endpoints
- ✅ AJAX operations (no page reloads)
- ✅ Loading states & notifications
- ✅ Password hashing & security
- ✅ Form fallbacks (works without JS)

**Time to Test:** ~10-15 minutes to verify everything works

**Start Now:** Run Step 1 (Database Setup) above!
