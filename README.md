# 📚 StudyTrack

A comprehensive study management system for students and teachers at Dhaka International University.

## ✨ Features

- **📅 Calendar View** - 6-month vertical scroll calendar with color-coded events
- **👥 Section Management** - Join sections, view classmates, teacher moderation
- **✅ Task Tracking** - Upcoming/completed events with progress tracking
- **📊 Profile Dashboard** - Personal statistics and performance metrics
- **🎨 Responsive Design** - Works on desktop, tablet, and mobile devices
- **🔒 Secure Authentication** - Role-based access (Student/Teacher)
- **⚡ REST API** - Complete API layer for AJAX operations

## 🚀 Quick Start

### Local Development

1. **Requirements:**
   - PHP 7.4+
   - MySQL 5.7+ or PlanetScale
   - Git

2. **Install:**
   ```bash
   git clone https://github.com/yourusername/studytrack.git
   cd studytrack
   ```

3. **Configure Database:**
   ```bash
   # Option A: Local MySQL (via XAMPP)
   # Start XAMPP MySQL service
   # Create database: studytrack
   
   # Option B: PlanetScale (Cloud)
   # Create account at planetscale.com
   # Create database and get credentials
   ```

4. **Setup Environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

5. **Run Migration:**
   ```bash
   php database/migrate.php
   ```

6. **Start Server:**
   ```bash
   php -S localhost:8000
   ```

7. **Login:**
   - Visit: http://localhost:8000
   - Student: `student@diu.edu.bd` / `student123`
   - Teacher: `teacher@diu.edu.bd` / `teacher123`

## ☁️ Cloud Deployment

### Railway (Recommended - Free)

1. Push to GitHub
2. Sign up at [railway.app](https://railway.app)
3. Deploy from GitHub repo
4. Add environment variables
5. Run migration via setup endpoint

**Detailed Guide:** [CLOUD_DEPLOY_NOW.md](CLOUD_DEPLOY_NOW.md)

### Other Platforms

- **InfinityFree** - 100% free hosting
- **DigitalOcean** - $5/month managed platform
- **Heroku** - Easy deployment

See [PRODUCTION_DEPLOY.md](PRODUCTION_DEPLOY.md) for complete deployment instructions.

## 🏗️ Architecture

```
StudyTrack/
├── auth/              # Authentication (login, register)
├── pages/             # Main application pages
├── api/               # REST API endpoints
├── config/            # Configuration & database functions
├── database/          # Schema & migration scripts
├── assets/            # CSS, JavaScript, images
└── includes/          # Shared components (header, footer)
```

### Technology Stack

- **Backend:** PHP 8.x with PDO
- **Database:** MySQL with prepared statements
- **Frontend:** Vanilla JS with progressive enhancement
- **API:** JSON REST endpoints
- **Security:** bcrypt, HTTPS, XSS protection, CSRF tokens

## 📖 Documentation

- [CLOUD_DEPLOY_NOW.md](CLOUD_DEPLOY_NOW.md) - Cloud deployment guide
- [PRODUCTION_DEPLOY.md](PRODUCTION_DEPLOY.md) - Complete production setup
- [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) - Pre-deployment checklist
- [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - Database structure
- [IMPLEMENTATION_COMPLETE.md](IMPLEMENTATION_COMPLETE.md) - Technical details

## 🗄️ Database Schema

**4 Tables:**
- `users` - Student and teacher accounts
- `sections` - Class sections with unique codes
- `section_members` - Student-section relationships
- `events` - Assignments, exams, and study sessions

**Key Features:**
- Foreign key constraints
- Indexes on frequently queried columns
- ENUM types for status fields
- ON DELETE CASCADE for data integrity

## 🔌 API Endpoints

### Events
- `POST /api/events/create.php` - Create event
- `PUT /api/events/update.php` - Update event
- `DELETE /api/events/delete.php` - Delete event
- `GET /api/events/list.php` - List/filter events
- `POST /api/events/approve.php` - Approve event (teacher)
- `POST /api/events/reject.php` - Reject event (teacher)

### Sections
- `POST /api/sections/join.php` - Join section
- `POST /api/sections/leave.php` - Leave section

### User
- `GET /api/user/stats.php` - Get user statistics

## 🔒 Security Features

- **Authentication:** Bcrypt password hashing
- **Authorization:** Role-based access control
- **SQL Injection:** Prepared statements only
- **XSS Protection:** Input sanitization & CSP headers
- **CSRF:** Token validation on forms
- **Session:** Secure cookies, timeout, regeneration
- **.htaccess:** Config file protection, security headers

## 🧪 Testing

### Production Check
```bash
php production-check.php
```

### Health Check
```bash
curl https://yourdomain.com/health.php
```

### API Testing
See `test-api.html` for interactive API testing interface.

## 📊 Monitoring

### Health Endpoint
- URL: `/health.php`
- Returns JSON status
- Compatible with UptimeRobot, Pingdom

### Error Logs
```bash
# Production logs
tail -f logs/php-errors.log

# Apache logs
tail -f /var/log/apache2/error.log
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Commit changes: `git commit -am 'Add feature'`
4. Push to branch: `git push origin feature-name`
5. Submit pull request

## 📜 License

This project is licensed under the MIT License.

## 👥 Authors

- Initial Development - [Your Name]
- Database Design - [Your Name]
- API Development - [Your Name]

## 🆘 Support

### Common Issues

**Database Connection Failed:**
- Check credentials in `.env`
- Verify database server is running
- Confirm database exists

**500 Internal Server Error:**
- Check error logs
- Verify file permissions
- Confirm PHP version >= 7.4

**API Returns 404:**
- Enable mod_rewrite (Apache)
- Check .htaccess is uploaded
- Verify API files exist

### Getting Help

1. Check documentation in `/docs`
2. Review error logs
3. Run production check script
4. Check health endpoint

## 🎉 Deployment Status

**Version:** 1.0.0  
**Status:** Production Ready ✅  
**Last Updated:** February 14, 2026

### Completed Phases
- ✅ Phase 1: Database Infrastructure
- ✅ Phase 2: Database Functions  
- ✅ Phase 3: Page Integration
- ✅ Phase 4: Form Handlers
- ✅ Phase 5: REST API

### Quick Deploy
```bash
# 1. Push to GitHub
git push origin main

# 2. Deploy to Railway
railway up

# 3. Run setup endpoint
curl https://your-app.up.railway.app/setup-once.php

# 4. Done! 🎉
```

## 🌟 Features Roadmap

- [ ] Email notifications
- [ ] File attachments
- [ ] Calendar export (iCal)
- [ ] Mobile app
- [ ] Dark mode
- [ ] Multi-language support

---

**Made with ❤️ for Dhaka International University students**

**Live Demo:** [Coming Soon]  
**Documentation:** [/docs](/docs)  
**Report Issues:** [GitHub Issues](https://github.com/yourusername/studytrack/issues)
