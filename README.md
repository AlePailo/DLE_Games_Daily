# DLE Games Daily

A daily anime/manga character guessing game in the style of Mangadle.net . 
Each franchise has one secret character per day; guess it by comparing attributes (Correct / Partial (upcoming) / Wrong).

Built as a custom PHP MVC application, without a framework.

## Features
- Play as a guest or with an account
- Progress made as a guest is merged into the account on sign-up or login (only current day game sessions)
- One daily challenge per franchise, with streaks and persistent statistics
- Per-franchise and global leaderboards, favourites, profile with avatars
- Registered users can set favourite frachises (which will then be displayed in the home page) and change their avatar

## Tech stack
PHP 8.2+ (with PHP-DI, FastRoute and PHPMailer libraries), MySQL / MariaDB, Vanilla JS (ES modules).

## Requirements
- PHP 8.2 or later and Composer
- MySQL or MariaDB
- Apache with `mod_rewrite` and `AllowOverride All` (e.g. XAMPP)

## Setup
1. Clone the repository into your web root as `DLE_Games_Daily`
   (the path is currently set in `.htaccess` and `BASE_URL` in `public/index.php`).
2. `composer install`
3. Create a database, then import `database/schema.sql` and `database/seed.sql`.
4. Copy `.env.example` to `.env` and fill it in.
5. Open `http://localhost/DLE_Games_Daily/`.

## Configuration
| Variable | Purpose |
|---|---|
| `APP_ENV` | `development` shows detailed errors; `production` shows only the error page |
| `APP_BASE_PATH` | Folder the app is served from (must match `.htaccess`) |
| `CHALLENGE_AUTO_CREATE` | See below |
| `DB_*` | Database connection |
| `MAIL_*` | SMTP used for verification emails (a Mailtrap sandbox works well) |

## Daily challenges
By default the first request of the day creates the missing challenge for each franchise
(`DailyChallengeService`), so nothing needs to be scheduled to try the project. Creation is
idempotent and safe with simultaneous requests.

To generate them ahead of time instead, set `CHALLENGE_AUTO_CREATE=false` and run:

    php scripts/generateDailyChallenge.php --days=3

Linux/macOS (`crontab -e`), every day at 00:05:

    5 0 * * * cd /path/to/DLE_Games_Daily && php scripts/generateDailyChallenge.php --days=3 >> "$HOME/dle_challenges.log" 2>&1

Windows (Task Scheduler):

    schtasks /Create /SC DAILY /ST 00:05 /TN "DLE Daily Challenges" /TR "php C:\path\to\DLE_Games_Daily\scripts\generateDailyChallenge.php --days=3"

## Maintenance
`php scripts/rebuildUserStats.php` recomputes `user_franchise_stats` from the stored game sessions,
using the same rules as the game. It is a dry run by default; add `--apply` to write. Back up the
database first, and don't run it after deleting game sessions, since it relies on the full history.

## Known limitations
- The install folder (`/DLE_Games_Daily/`) is hard-coded in `.htaccess` and `public/index.php`.
- The streak counts consecutive calendar days: if a franchise is disabled for more than a day,
  users' streaks on it are reset.
- Guest sessions are never cleaned up.
- No automated tests yet (PHPUnit planned).
- Planned: friends and OAuth login.