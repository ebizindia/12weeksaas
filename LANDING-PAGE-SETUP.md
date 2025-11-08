# 12 Week Edge - Landing Page Setup Guide

**URL:** www.12weekedge.com
**Purpose:** Collect waitlist signups before public launch

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Database Setup

Run the waitlist migration:

```bash
cd /path/to/12weeksaas
mysql -u username -p database_name < migrations/waitlist-migration.sql
```

This creates the `waitlist` table.

### Step 2: Change Admin Password

**CRITICAL:** Edit `landing/admin.php` line 10:

```php
$admin_password = 'YOUR_STRONG_PASSWORD_HERE'; // Change from 'changeme123'
```

### Step 3: Configure Domain

Point your domain to the landing page:

**Option A: Subdomain**
```
www.12weekedge.com → /path/to/12weeksaas/landing/
```

**Option B: Main Domain**
```
12weekedge.com → /path/to/12weeksaas/landing/
```

**Option C: Apache Virtual Host**
```apache
<VirtualHost *:80>
    ServerName www.12weekedge.com
    DocumentRoot /path/to/12weeksaas/landing
    <Directory /path/to/12weeksaas/landing>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Option D: Nginx**
```nginx
server {
    listen 80;
    server_name www.12weekedge.com;
    root /path/to/12weeksaas/landing;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### Step 4: Test

Visit your domain:
```
http://www.12weekedge.com
```

Fill out the waitlist form and verify:
- ✅ Form submits successfully
- ✅ Redirects to thank-you page
- ✅ Email confirmation sent
- ✅ Entry appears in database

---

## 📧 Email Configuration

### Using Phase 2 EmailService (Recommended)

If you've set up Phase 2 SMTP, emails will automatically use EmailService.

**Verify config.php has:**
```php
define('CONST_SMTP_HOST', 'smtp.gmail.com');
define('CONST_SMTP_USERNAME', 'your-email@gmail.com');
define('CONST_SMTP_PASSWORD', 'your-app-password');
define('CONST_SMTP_FROM_EMAIL', 'noreply@12weekedge.com');
```

### Using PHP mail() (Fallback)

If Phase 2 is not configured, emails use PHP's built-in `mail()` function.

**Test email delivery:**
```bash
php -r "mail('your-email@example.com', 'Test', 'Test message');"
```

---

## 🔐 Admin Dashboard

### Access

URL: `http://www.12weekedge.com/admin.php`

**Default Password:** `changeme123` (CHANGE THIS!)

### Features

- View all waitlist entries
- Filter by status (pending/invited/converted/declined)
- Search by name, email, or company
- Update status and add notes
- Export to CSV

### Change Password

Edit `landing/admin.php`:

```php
$admin_password = 'YOUR_STRONG_PASSWORD';
```

---

## 📊 Managing Waitlist

### Status Workflow

```
1. User signs up → Status: "pending"
2. Send invitation → Update to "invited"
3. User creates account → Update to "converted"
   OR
   User ignores → Update to "declined"
```

### Inviting Users

When ready to launch:

1. **Export waitlist**
   - Go to admin panel
   - Click "Export to CSV"

2. **Send invitations**
   - Use Phase 2 email system
   - Or import to email marketing tool
   - Include personal invite link

3. **Track conversions**
   - Mark as "invited" when email sent
   - Mark as "converted" when account created
   - Mark as "declined" if no response after 2 weeks

### Bulk Conversion

When launching, bulk create accounts:

```php
// Use admin-add-user.php from Phase 1
// Or bulk import via SQL script
```

---

## 🎨 Customization

### Update Statistics

Edit `landing/index.php` around line 250:

```html
<span class="stat-number">4x</span>
<span class="stat-label">Faster Execution</span>
```

Change numbers based on real data.

### Update Testimonials

Edit `landing/index.php` around line 850:

```html
<div class="testimonial-text">
    "Your testimonial here..."
</div>
<h5>Name</h5>
<p>Title, Company</p>
```

### Update Pricing

Edit `landing/index.php` around line 1000:

```html
<div class="pricing-amount">$29</div>
<div class="pricing-period">per month</div>
```

### Change Colors

Edit CSS variables in `landing/index.php`:

```css
:root {
    --primary-color: #1a1a2e;
    --gold-accent: #d4af37;  /* Change this */
}
```

### Update Content

All content is in `landing/index.php`. Search for:
- Hero title
- Feature descriptions
- Problem-solution text
- Call-to-action buttons

---

## 📈 Analytics

### Add Google Analytics

Insert before `</head>` in `landing/index.php`:

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

### Track Conversions

Add event tracking to waitlist form:

```javascript
// After successful signup
gtag('event', 'waitlist_signup', {
    'event_category': 'engagement',
    'event_label': 'waitlist'
});
```

---

## 🔒 Security Checklist

- [ ] Changed admin password from default
- [ ] Enabled HTTPS (SSL certificate)
- [ ] Set secure file permissions (644 for PHP files)
- [ ] Disabled directory listing
- [ ] Updated email FROM address
- [ ] Tested SQL injection prevention
- [ ] Verified email validation works
- [ ] Added rate limiting (if high traffic)

### Enable HTTPS

**Let's Encrypt (Free SSL):**

```bash
sudo certbot --nginx -d www.12weekedge.com
```

or

```bash
sudo certbot --apache -d www.12weekedge.com
```

Update config after SSL:
```php
define('CONST_APP_URL', 'https://www.12weekedge.com');
```

---

## 🧪 Testing Checklist

### Functionality

- [ ] Landing page loads correctly
- [ ] All sections display properly
- [ ] Waitlist form validates input
- [ ] Form submits successfully
- [ ] Confirmation email received
- [ ] Thank-you page shows correct info
- [ ] Admin login works
- [ ] Admin panel displays entries
- [ ] CSV export works
- [ ] Status updates save correctly

### Responsive Design

- [ ] Mobile phone (portrait)
- [ ] Mobile phone (landscape)
- [ ] Tablet (portrait)
- [ ] Tablet (landscape)
- [ ] Desktop (1920x1080)
- [ ] Desktop (4K)

### Browsers

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Performance

- [ ] Page loads in < 3 seconds
- [ ] Images optimized
- [ ] No console errors
- [ ] Smooth animations

---

## 📱 Social Media

### Open Graph Tags

Already included in `landing/index.php`:

```html
<meta property="og:title" content="12 Week Edge - Achieve More in 12 Weeks">
<meta property="og:description" content="The proven system used by Fortune 500 executives...">
<meta property="og:type" content="website">
<meta property="og:url" content="https://www.12weekedge.com">
```

### Add OG Image

Create `og-image.png` (1200x630px) and add:

```html
<meta property="og:image" content="https://www.12weekedge.com/og-image.png">
```

### Twitter Cards

Add to `<head>`:

```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="12 Week Edge">
<meta name="twitter:description" content="Achieve more in 12 weeks...">
<meta name="twitter:image" content="https://www.12weekedge.com/og-image.png">
```

---

## 🚀 Pre-Launch Checklist

### 1 Week Before

- [ ] Test all functionality
- [ ] Review and update content
- [ ] Add real testimonials (if available)
- [ ] Set up analytics
- [ ] Enable HTTPS
- [ ] Test email delivery to various providers
- [ ] Prepare social media graphics
- [ ] Draft launch announcement

### Launch Day

- [ ] Final content review
- [ ] Clear any test data
- [ ] Announce on social media
- [ ] Send to email list
- [ ] Monitor signups in admin panel
- [ ] Check email delivery
- [ ] Respond to questions

### Post-Launch

- [ ] Monitor daily signups
- [ ] Export weekly reports
- [ ] Engage with signups (thank you emails)
- [ ] Share milestones (500 signups, 1000 signups, etc.)
- [ ] Update statistics on landing page
- [ ] Add new testimonials
- [ ] A/B test different headlines

---

## 📧 Email Marketing Integration

### Export to Tools

Admin panel exports CSV compatible with:
- ✅ Mailchimp
- ✅ ConvertKit
- ✅ SendGrid
- ✅ HubSpot
- ✅ ActiveCampaign

### CSV Format

Columns exported:
- ID
- Name
- Email
- Company
- Title
- Status
- IP Address
- Joined Date
- Invited Date
- Converted Date
- Notes

### Import Instructions

**Mailchimp:**
1. Go to Audience → Import contacts
2. Upload CSV
3. Map fields
4. Add tag "12WeekEdge-Waitlist"

**SendGrid:**
1. Marketing → Contacts → Upload CSV
2. Select list "Waitlist"
3. Map custom fields

---

## 🐛 Troubleshooting

### Emails Not Sending

**Check:**
1. SMTP configuration in config.php
2. PHP mail() function: `php -i | grep sendmail`
3. Email logs (if using Phase 2)
4. Spam folder
5. DNS records (SPF, DKIM)

### Form Not Submitting

**Check:**
1. JavaScript console for errors
2. Database connection
3. File permissions
4. PHP error log

### Admin Can't Login

**Check:**
1. Correct password in admin.php
2. Session configuration
3. Cookie settings
4. Browser cache

### Database Connection Failed

**Check:**
1. Database credentials
2. Database exists
3. User has permissions
4. MySQL service running

---

## 📞 Support

### Documentation

- Landing page: `landing/README.md`
- This guide: `LANDING-PAGE-SETUP.md`
- Phase 2 guide: `PHASE2-DEPLOYMENT-GUIDE.md`

### File Structure

```
landing/
├── index.php              # Main landing page
├── waitlist-signup.php    # Signup handler
├── thank-you.php         # Confirmation page
├── admin.php             # Admin dashboard
├── export-waitlist.php   # CSV export
└── README.md             # Detailed docs
```

---

## 🎯 Success Metrics

Track these KPIs:

- **Conversion Rate:** Visitors → Signups (target: 15-25%)
- **Email Delivery Rate:** Sent → Delivered (target: >95%)
- **Source Attribution:** Where signups come from
- **Time to Convert:** Days from signup to account creation
- **Executive Signups:** Track title/company for targeting

---

**Landing page is ready to collect signups!** 🚀

Deploy following this guide and start building your waitlist for www.12weekedge.com

---

*Last Updated: 2025-11-08*
*Version: 1.0*
*Status: Production Ready*
