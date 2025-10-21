# CFPlus - Competitive Programming Training Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-orange?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38bdf8?style=for-the-badge&logo=tailwindcss" alt="Tailwind">
  <img src="https://img.shields.io/badge/Chart.js-4.4-ff6384?style=for-the-badge&logo=chart.js" alt="ChartJS">
</p>


## About

**CFPlus** is a comprehensive competitive programming training platform built with Laravel 11. It seamlessly integrates with the Codeforces API to help competitive programmers track their progress, create custom practice contests, and analyze their performance through detailed statistics and visualizations.

Perfect for:
- Students learning competitive programming
- Developers preparing for coding interviews
- Competitive programmers training for contests
- Anyone wanting to track their Codeforces progress

---

## Features

### Codeforces Integration
- **Link Your CF Account**: Connect your Codeforces profile to sync all your data
- **Automatic Sync**: Background jobs fetch your submissions, contests, and ratings
- **Real-time Updates**: Keep your progress synchronized with Codeforces

### Comprehensive Dashboard
- **Hero Section**: Display your avatar, current rating, and rank with beautiful badges
- **Quick Stats**: View contests completed, problems solved, accuracy %, and activity streak
- **Rating Chart**: Interactive Chart.js visualization showing rating progression (actual vs performance)
- **Recent Activity Feed**: Track your latest achievements and contest completions
- **Active Contests Widget**: Quick access to ongoing and draft contests

### Custom Practice Contests
- **Create Contests**: Design personalized practice contests with problems of your choice
- **Flexible Duration**: Set contest length from 30 minutes to 6 hours
- **Problem Selection**: Choose from 5000+ Codeforces problems filtered by difficulty and tags
- **Live Participation**: Real-time timer and problem status tracking during contests
- **Auto-Sync**: Automatically detect solved problems from Codeforces submissions

### Advanced Rating System
- **Gradual Rating Changes**: Codeforces-style rating system with K-factor algorithm
- **Dual Ratings**: Track both performance rating and actual (gradual) rating
- **Smart K-Factors**: Variable rating volatility based on current skill level:
  - Beginners (<1200): 60% volatility
  - Intermediate (1200-1600): 40-50%
  - Advanced (1600-2100): 30-35%
  - Experts (2100+): 25%
- **Rating Cap**: ±200 maximum change per contest for stability
- **Performance Calculation**: Considers problem difficulty, rarity, and solving speed

### Detailed Statistics
- **Rating Graph**: Historical rating progression with actual vs performance lines
- **Problem Analytics**: Pie charts showing solved vs attempted problems
- **Language Breakdown**: Donut chart of programming languages used (C++, Python, Java, etc.)
- **Verdict Analysis**: Submission verdicts distribution (AC, WA, TLE, MLE, etc.)
- **Tag Distribution**: Progress bars showing proficiency in different topics (DP, Graphs, Math, etc.)
- **Accuracy Metrics**: Success rate and problem-solving patterns

### Smart Problem Browser
- **Advanced Filters**: Filter by rating (800-3500), tags, and solve status
- **Random Problem**: Get random unsolved problems matching your criteria
- **Problem Stats**: View difficulty, tags, and solve count for each problem
- **Direct Links**: One-click access to problems on Codeforces
- **Status Indicators**: Visual markers for solved/attempted problems

### Beautiful UI/UX
- **Modern Design**: Clean, responsive interface built with Tailwind CSS
- **Gradient Elements**: Eye-catching gradient buttons and cards
- **Smooth Animations**: Hover effects and transitions
- **Mobile Responsive**: Works perfectly on all device sizes
- **Dark Mode Ready**: Color scheme optimized for extended use

### User Management
- **Authentication**: Secure login/register with Laravel Breeze
- **Profile Management**: Update name, email, timezone preferences
- **Password Reset**: Forgot password functionality
- **Email Verification**: Optional email verification
- **Session Management**: Secure session handling

---

## Tech Stack

### Backend
- **Laravel 11.34.0** - PHP Web Framework
- **PHP 8.2.12** - Server-side Language
- **MySQL 8.0+** - Relational Database
- **Laravel Breeze** - Authentication Scaffolding
- **Queue System** - Background Job Processing

### Frontend
- **Blade Templates** - Server-side Templating
- **Tailwind CSS 3.x** - Utility-first CSS Framework
- **Alpine.js** - Lightweight JavaScript Framework
- **Chart.js 4.4.0** - Data Visualization Library
- **Vite** - Frontend Build Tool

### External APIs
- **Codeforces API** - Problem data and user statistics

### Tools & Services
- **Composer** - PHP Dependency Manager
- **NPM** - Node Package Manager
- **Git** - Version Control

---

## Prerequisites

Before you begin, ensure you have the following installed on your system:

### Required Software
- **PHP >= 8.2** with extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
  
- **Composer** - Latest version ([Download](https://getcomposer.org/))
- **Node.js >= 18.x** and **NPM** ([Download](https://nodejs.org/))
- **MySQL 8.0+** or **MariaDB 10.3+**
- **Git** ([Download](https://git-scm.com/))

### Optional
- **Apache/Nginx** - Web server (or use Laravel's built-in server)
- **Redis** - For caching and queue management (optional but recommended)

---

## Installation

Follow these steps to set up CFPlus on your local machine:

### 1. Clone the Repository

```bash
git clone https://github.com/rahman-arifur/cfplus.git
cd cfplus
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Create Environment File

```bash
# On Linux/Mac
cp .env.example .env

# On Windows
copy .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Configure Database

Edit the `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cfplus
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 7. Create Database

Create a new MySQL database:

```sql
CREATE DATABASE cfplus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or use command line:

```bash
mysql -u your_username -p -e "CREATE DATABASE cfplus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 8. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- users
- cf_accounts
- rating_snapshots
- problems
- contests
- user_contests
- user_problem (pivot)
- user_contest_problems (pivot)

### 9. (Optional) Seed the Database

If you want sample data for testing:

```bash
php artisan db:seed
```

---

## ⚙️ Configuration

### Queue Configuration (Important!)

CFPlus uses queues for background API syncing. Configure the queue driver in `.env`:

**Option 1: Database Driver (Recommended for Development)**
```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:table
php artisan migrate
```

**Option 2: Redis Driver (Recommended for Production)**
```env
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Cache Configuration

For better performance:

```env
CACHE_DRIVER=file
# Or use Redis
CACHE_DRIVER=redis
```

### Session Configuration

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Timezone (Optional)

Set your default timezone:

```env
APP_TIMEZONE=Asia/Dhaka
```

### Sync Codeforces Problems (One-time Setup)

To populate your database with Codeforces problems:

```bash
php artisan tinker
```

Then run:
```php
\App\Jobs\SyncProblemsFromCf::dispatch();
exit
```

Or create an artisan command:
```bash
php artisan make:command SyncProblems
```

---

## Running the Application

### Development Server

#### Method 1: Laravel Development Server (Recommended)

```bash
# Terminal 1: Start Laravel Server
php artisan serve

# Terminal 2: Compile Assets (Watch Mode)
npm run dev

# Terminal 3: Process Queue Jobs
php artisan queue:work
```

#### Method 2: XAMPP/LAMPP

1. Move project to `htdocs` folder:
   ```bash
   # Linux
   sudo mv cfplus /opt/lampp/htdocs/
   
   # Windows
   move cfplus C:\xampp\htdocs\
   ```

2. Start Apache and MySQL from XAMPP Control Panel

3. Compile assets:
   ```bash
   npm run build
   ```

4. Run queue worker:
   ```bash
   php artisan queue:work
   ```

## Usage Guide

### 1. Register an Account

1. Visit the homepage
2. Click **"Register"**
3. Fill in your name, email, and password
4. Submit the form

### 2. Link Your Codeforces Account

1. Go to **Profile** page
2. Click **"Link Codeforces Account"**
3. Enter your Codeforces handle (e.g., "tourist")
4. Click **"Link"**
5. Wait for background sync to complete (check back in 1-2 minutes)

### 3. View Your Dashboard

- Navigate to **Dashboard** from the top menu
- See your current rating, rank, and statistics
- View your rating progression chart
- Check recent activities

### 4. Create a Custom Contest

1. Go to **Custom Contests** → **Create New**
2. Enter contest title (e.g., "DP Practice")
3. Set duration (e.g., 120 minutes)
4. Select problems by difficulty and tags
5. Click **"Create Contest"**
6. Contest is saved as **Draft**

### 5. Participate in Contest

1. Open your contest from the list
2. Click **"Start Contest"**
3. Timer begins counting down
4. Open problems in new tabs on Codeforces
5. Solve and submit on Codeforces
6. Return to CFPlus and mark problems as solved
7. Click **"Complete Contest"** when done

### 6. View Results

After completing a contest:
- See your **Performance Rating** (based on problems solved)
- See your **Actual Rating** change (gradual, K-factor based)
- View rating change (+X or -X)
- Updated rank badge if you changed divisions

### 7. Analyze Statistics

1. Go to **Statistics** page
2. View rating graph over time
3. Check problem-solving breakdown
4. Analyze your programming languages usage
5. See submission verdicts distribution
6. Review problem tags strengths

### 8. Browse Problems

1. Go to **Problems** page
2. Use filters:
   - Rating range (800-3500)
   - Tags (DP, Graphs, Math, etc.)
   - Status (All, Solved, Unsolved)
3. Click **"Random Problem"** for practice recommendations
4. Click problem name to open on Codeforces

---

## 📁 Project Structure

```
cfplus/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php    # Dashboard logic
│   │   │   ├── UserContestsController.php # Custom contests
│   │   │   ├── StatsController.php        # Statistics
│   │   │   ├── ProblemsController.php     # Problem browser
│   │   │   └── ProfileController.php      # User profile
│   │   └── Middleware/                    # Auth, CSRF, etc.
│   ├── Models/
│   │   ├── User.php                       # User model + relationships
│   │   ├── CfAccount.php                  # CF account model
│   │   ├── UserContest.php                # Custom contest model
│   │   ├── Problem.php                    # Problem model
│   │   └── RatingSnapshot.php             # CF rating history
│   ├── Jobs/
│   │   ├── SyncCfAccount.php              # Background CF sync
│   │   ├── SyncProblemsFromCf.php         # Import problems
│   │   └── SyncContestsFromCf.php         # Import contests
│   └── Services/
│       └── CodeforcesApiService.php       # CF API wrapper
├── database/
│   ├── migrations/                        # Database schema
│   └── seeders/                           # Sample data
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php            # Main dashboard
│   │   ├── stats/index.blade.php          # Statistics page
│   │   ├── user-contests/                 # Contest views
│   │   ├── problems/index.blade.php       # Problem browser
│   │   └── layouts/app.blade.php          # Main layout
│   ├── css/
│   │   └── app.css                        # Tailwind CSS
│   └── js/
│       └── app.js                         # Alpine.js
├── routes/
│   ├── web.php                            # Web routes
│   └── console.php                        # CLI commands
├── public/
│   ├── index.php                          # Entry point
│   └── build/                             # Compiled assets
├── .env.example                           # Environment template
├── composer.json                          # PHP dependencies
├── package.json                           # Node dependencies
├── vite.config.js                         # Vite configuration
├── tailwind.config.js                     # Tailwind configuration
└── README.md                              # This file
```

---

## Troubleshooting

### Common Issues

**1. "500 Internal Server Error"**
```bash
# Check permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # Linux

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**2. "Class not found" errors**
```bash
composer dump-autoload
php artisan clear-compiled
```

**3. Assets not loading**
```bash
npm run build
php artisan view:clear
```

**4. Queue jobs not processing**
```bash
# Make sure queue worker is running
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

**5. Codeforces sync not working**
- Ensure queue worker is running
- Check `storage/logs/laravel.log` for errors
- Verify handle is correct on Codeforces
- Wait 1-2 minutes for background job to complete

**6. Database connection issues**
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
```