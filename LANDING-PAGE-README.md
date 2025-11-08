# 12 Week Edge - Landing Page

Professional landing page with waitlist functionality for www.12weekedge.com

## Features

### Landing Page
- ✅ Modern, executive-focused design
- ✅ Fully responsive (mobile-first)
- ✅ Smooth animations (AOS library)
- ✅ Professional color scheme and typography
- ✅ Social proof (testimonials, statistics)
- ✅ Problem-solution framework
- ✅ Pricing preview
- ✅ Strong calls-to-action

### Waitlist System
- ✅ Email capture with validation
- ✅ Duplicate prevention
- ✅ Automatic email confirmation
- ✅ Position tracking
- ✅ Admin dashboard
- ✅ CSV export
- ✅ Status management

## Setup

### 1. Database Migration

Run the waitlist migration:

```bash
mysql -u username -p database_name < migrations/waitlist-migration.sql
```

### 2. Configuration

**If integrated with main app:**

The landing page looks for `/app/config.php` to integrate with the main application. Ensure your app files are in the `/app` directory:

```
/app/config.php
/app/cls/EmailService.php (for Phase 2 email integration)
/app/includes/ebiz-autoload.php
```

**If standalone:** Edit `waitlist-signup.php`, `admin.php`, and `export-waitlist.php` to update:

```php
define('WAITLIST_DB_HOST', 'localhost');
define('WAITLIST_DB_NAME', 'your_database');
define('WAITLIST_DB_USER', 'your_username');
define('WAITLIST_DB_PASS', 'your_password');
```

### 3. Email Configuration

For email notifications, configure SMTP in your main `config.php` or update the email function in `waitlist-signup.php`.

### 4. Admin Password

**IMPORTANT:** Change the admin password in `admin.php`:

```php
$admin_password = 'changeme123'; // CHANGE THIS!
```

Use a strong password for production.

## File Structure

```
/ (root - landing page files)
├── index.php                  # Main landing page
├── waitlist-signup.php        # Waitlist signup handler (AJAX)
├── thank-you.php             # Thank you page after signup
├── admin.php                 # Admin dashboard
├── export-waitlist.php       # CSV export
├── LANDING-PAGE-README.md    # This file
└── migrations/
    └── waitlist-migration.sql

/app (application files)
├── login.php                  # App login
├── config.php                # App config
└── ...                       # Other app files
```

## Pages

### index.php
**URL:** `www.12weekedge.com/` or `www.12weekedge.com/`

Main landing page with:
- Hero section with waitlist form
- Problem-solution framework
- Features showcase
- How it works
- Testimonials
- Pricing preview
- Final CTA

### thank-you.php
**URL:** `www.12weekedge.com/thank-you.php`

Shown after successful waitlist signup. Displays:
- Success confirmation
- Benefits reminder
- Social sharing buttons
- Next steps

### admin.php
**URL:** `www.12weekedge.com/admin.php`

Admin dashboard to:
- View all waitlist entries
- Filter by status (pending, invited, converted, declined)
- Search entries
- Update status
- Add notes
- View statistics
- Export to CSV

**Default Password:** `changeme123` (CHANGE THIS!)

## Usage

### Accessing the Landing Page

1. **Direct access:** `http://your-domain.com/landing/`
2. **Main domain:** Point your domain to `landing/index.php`

### Collecting Signups

The waitlist form automatically:
- Validates email format
- Checks for duplicates
- Sends confirmation email
- Assigns position number
- Stores in database

### Managing Waitlist

1. Login to admin panel: `/landing/admin.php`
2. Use filters to view different statuses
3. Search by name, email, or company
4. Update status as you invite/convert users
5. Export to CSV for email marketing tools

### Status Workflow

```
pending → invited → converted
          ↓
        declined
```

- **Pending:** Just signed up, waiting for launch
- **Invited:** Sent early access invitation
- **Converted:** Created account and using the app
- **Declined:** Invited but didn't convert

## Customization

### Colors

Edit CSS variables in `index.php`:

```css
:root {
    --primary-color: #1a1a2e;
    --secondary-color: #16213e;
    --accent-color: #0f3460;
    --gold-accent: #d4af37;
}
```

### Content

**Statistics:** Update hero stats in `index.php` (lines ~250-280)
**Testimonials:** Update testimonial section (lines ~850-950)
**Pricing:** Update pricing cards (lines ~1000-1100)

### Email Templates

Edit email template in `waitlist-signup.php` function `sendEmailBasic()`.

For professional emails, integrate with EmailService from Phase 2.

## Integration with Main App

### Option 1: Same Database

If using the same database as main app, waitlist table will be created with your table prefix.

### Option 2: Separate Database

Configure standalone database credentials in `waitlist-signup.php`.

### Option 3: Import to Main App

When ready to launch:

1. Export waitlist to CSV
2. Bulk import to main app as users
3. Send invitations via Phase 2 email system
4. Track conversion

## Security

### Implemented

- ✅ Input validation and sanitization
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (htmlspecialchars)
- ✅ Duplicate email prevention
- ✅ Admin password protection
- ✅ CSRF protection (can be enhanced)

### Recommendations

1. **Change admin password** to strong password
2. **Use HTTPS** in production
3. **Rate limiting** on signup form (add if high traffic)
4. **reCAPTCHA** integration (if spam becomes issue)
5. **IP blocking** for repeated failed admin logins

## SEO Optimization

Landing page includes:
- ✅ Meta descriptions
- ✅ Open Graph tags
- ✅ Semantic HTML5
- ✅ Fast loading (CDN assets)
- ✅ Mobile-friendly
- ✅ Structured content

### Additional SEO

Add to `<head>` for enhanced SEO:

```html
<link rel="canonical" href="https://www.12weekedge.com">
<meta name="robots" content="index, follow">
```

## Analytics

Add Google Analytics or tracking:

```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

Add before `</head>` tag in `index.php`.

## Email Marketing Integration

### Export for Email Tools

1. Go to admin panel
2. Click "Export to CSV"
3. Import to:
   - Mailchimp
   - SendGrid
   - ConvertKit
   - HubSpot
   - etc.

### API Integration

For automatic sync, create API endpoint to push new signups to your email marketing platform.

## Performance

### Optimizations

- ✅ CDN-hosted assets (Bootstrap, jQuery, Font Awesome)
- ✅ Lazy-loaded animations
- ✅ Optimized images (use WebP format)
- ✅ Minimal JavaScript
- ✅ CSS minification (in production)

### Additional Optimization

1. Enable gzip compression
2. Use image CDN
3. Add browser caching headers
4. Minify CSS/JS in production

## Testing

### Test Signup Flow

1. Fill out waitlist form
2. Submit form
3. Check confirmation email
4. Verify database entry
5. Check thank you page
6. Verify position assignment

### Test Admin Panel

1. Login to admin.php
2. View all entries
3. Filter by status
4. Search entries
5. Update status
6. Export to CSV

## Deployment

### Production Checklist

- [ ] Update admin password
- [ ] Configure SMTP for emails
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Test all forms
- [ ] Test email delivery
- [ ] Add analytics tracking
- [ ] Update meta tags with actual URL
- [ ] Test on mobile devices
- [ ] Set up backups

### Going Live

1. Point domain to landing page
2. Test on production URL
3. Monitor first signups
4. Check email delivery
5. Review admin panel
6. Start marketing campaigns

## Support

### Common Issues

**Emails not sending:**
- Check SMTP configuration
- Verify PHP `mail()` function works
- Check spam folder
- Review email_log if using Phase 2 EmailService

**Duplicate emails:**
- Unique constraint on email field prevents this
- Error message shown to user

**Admin can't login:**
- Verify password in `admin.php`
- Clear browser cache/cookies
- Check session configuration

**Database connection fails:**
- Verify credentials in config
- Check database exists
- Ensure PDO extension enabled

## Future Enhancements

Potential additions:

- [ ] Social media sharing incentives (referral tracking)
- [ ] Priority position for referrals
- [ ] Email drip campaign integration
- [ ] A/B testing different landing pages
- [ ] Exit-intent popup
- [ ] Live counter of waitlist size
- [ ] Industry segmentation
- [ ] Company size filtering
- [ ] Integration with CRM systems

## License

Proprietary - 12 Week Edge

---

**Version:** 1.0
**Last Updated:** 2025-11-08
**Optimized for:** Desktop & Mobile
**Browser Support:** Chrome, Firefox, Safari, Edge (latest versions)
