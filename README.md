# Email Bot

PHP email marketing automation. Upload a contact CSV + HTML template, it queues
and sends personalized emails through SendGrid in rate-limited batches.

## Local setup

```bash
composer install
cp .env.example .env
# fill in your DB and SendGrid credentials in .env
mysql -u root -p email_bot < migrations/001_create_tables.sql
php -S localhost:8000 -t public
```

Visit http://localhost:8000

To test the sender manually:
```bash
php worker/send_queue.php
```

## Deploying to GitHub + Railway

### 1. Push to GitHub
```bash
cd email-bot
git init
git add .
git commit -m "Initial commit: email campaign bot"
gh repo create email-bot --private --source=. --push
# or manually: create repo on github.com, then
# git remote add origin https://github.com/YOUR_USERNAME/email-bot.git
# git branch -M main
# git push -u origin main
```

### 2. Deploy on Railway
1. Go to railway.app, "New Project" -> "Deploy from GitHub repo" -> select `email-bot`
2. Add a MySQL plugin from Railway's plugin marketplace. Railway auto-injects
   DB_HOST/DB_PORT/etc as environment variables, but confirm the variable
   names match what's in `.env.example` (rename in Railway's Variables tab
   if needed, e.g. Railway may use MYSQLHOST instead of DB_HOST).
3. In the service's Variables tab, add:
   - SENDGRID_API_KEY
   - FROM_EMAIL
   - FROM_NAME
   - APP_URL (your Railway-provided domain)
   - BATCH_SIZE (e.g. 25)
4. Run the migration once, either via Railway's console (`railway run`) or
   by connecting to the MySQL plugin with a client and running
   `migrations/001_create_tables.sql`.
5. Railway will build using Nixpacks and start with the command in
   `railway.json` (`php -S 0.0.0.0:$PORT -t public`).

### 3. Set up the sending cron
Railway supports Cron Jobs as a separate service type:
1. In your Railway project, "New" -> "Cron Job"
2. Point it at the same repo, command: `php worker/send_queue.php`
3. Schedule: `* * * * *` (every minute) or `*/5 * * * *` for every 5 minutes
   if you want a gentler send rate

This is what actually sends the emails, the batch size and delay in
worker/send_queue.php controls the pace so you don't get rate-limited or
flagged as spam.

## Getting a SendGrid API key
1. Sign up at sendgrid.com (free tier: 100 emails/day)
2. Verify a sender identity (single sender or full domain authentication,
   domain auth is strongly recommended for deliverability, involves adding
   a few DNS records at your domain registrar)
3. Settings -> API Keys -> Create API Key -> Full Access (or restrict to
   Mail Send) -> copy into SENDGRID_API_KEY

## Compliance notes (important)
This only works well, and stays legal, if people on your list actually
opted in. Sending to purchased or scraped lists gets domains blacklisted
fast and can violate CAN-SPAM, GDPR, or Nigeria's NDPR. The unsubscribe
link is wired in for this reason, don't strip it out.

## Alternative providers
Swap `src/Mailer.php` to use Mailgun or Amazon SES if you prefer, the rest
of the app (queue, template, contacts) doesn't need to change, only the
send() method's HTTP call.
