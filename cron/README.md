# Cron Scripts

This directory contains automated scripts that can be executed via cron jobs.

## Daily Waitlist Count Email

**Script:** `send-waitlist-count-email.php`

### Description
Sends a daily email with the total count of waitlist members to configured recipients.

### Requirements
1. **Configuration File**: Ensure `config.php` exists (copy from `config-sample.php`)
2. **Database**: The `waitlist` table must exist
3. **Email Settings**: SMTP settings must be configured in `config.php`
4. **Recipient**: `CONST_WAITLIST_REPORT_RECP` must be defined in `config.php`

### Configuration

Add the following to your `config.php` if not already present:

```php
define('CONST_WAITLIST_REPORT_RECP', [
    'to' => ['arun@ebizindia.com'],
    'cc' => [],  // Optional
    'bcc' => []  // Optional
]);
```

### Manual Execution

Test the script manually before setting up cron:

```bash
# Run from project root
php /home/user/12weeksaas/cron/send-waitlist-count-email.php

# Or run directly (script is executable)
/home/user/12weeksaas/cron/send-waitlist-count-email.php
```

### Cron Setup

#### Daily at 9:00 AM
```bash
0 9 * * * /usr/bin/php /home/user/12weeksaas/cron/send-waitlist-count-email.php >> /var/log/waitlist-email.log 2>&1
```

#### Daily at 8:00 AM with logging
```bash
0 8 * * * cd /home/user/12weeksaas && /usr/bin/php cron/send-waitlist-count-email.php >> /var/log/12week-cron.log 2>&1
```

#### Multiple times per day (8 AM and 6 PM)
```bash
0 8,18 * * * /usr/bin/php /home/user/12weeksaas/cron/send-waitlist-count-email.php >> /var/log/waitlist-email.log 2>&1
```

### Output

The script outputs status messages for logging:
- ✓ Success messages for each step
- ✗ Error messages if something fails
- Timestamps for start and completion

### Troubleshooting

#### Script fails with "Configuration file not found"
- Copy `config-sample.php` to `config.php`
- Update database and SMTP settings

#### Script fails with "recipient email not configured"
- Add `CONST_WAITLIST_REPORT_RECP` constant to `config.php`

#### No email received
- Check SMTP settings in `config.php`
- Verify `CONST_MAIL_SENDERS_EMAIL` is configured
- Check spam folder
- Review log output for errors

#### Email goes to wrong address (testing)
- Check if `CONST_EMAIL_OVERRIDE` is set in `config.php`
- Remove or empty it for production use

### Email Format

Recipients will receive an HTML formatted email containing:
- Total count of waitlist members
- Report generation date and time
- Clean, professional layout

### Security Notes

- This script requires no authentication (designed for cron execution)
- Ensure proper file permissions (644 or 755)
- Keep config.php secure with database credentials
- Use email override feature for testing
