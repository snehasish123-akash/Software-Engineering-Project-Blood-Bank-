# Donor Status Management System

This system provides interactive donor status controls for administrators and automatic inactive status management.

## Features

### 1. Interactive Status Controls
- **Admin Panel**: Dropdown menus to change donor status (Active/Pending/Inactive)
- **Real-time Updates**: AJAX-powered status changes without page reload
- **Visual Feedback**: Row highlighting based on status
- **Status Statistics**: Dashboard showing counts for each status

### 2. Automatic Inactive Status
- **3-Month Rule**: Donors automatically become inactive after 3 months of no activity
- **Manual Check**: Admin button to manually run the inactive check
- **Cron Job Support**: Automated daily/weekly checks via cron
- **Activity Tracking**: System tracks when donors were last updated

## Files Added/Modified

### New Files:
1. `admin/update-donor-status.php` - AJAX endpoint for status updates
2. `admin/check-inactive-donors.php` - Script to check and update inactive donors
3. `admin/donors.php` - Enhanced donor management interface
4. `admin/donor-activity-tracker.php` - Activity tracking utilities
5. `cron/auto-inactive-donors.php` - Cron job script
6. `README-donor-status.md` - This documentation

## Setup Instructions

### 1. Database Setup
Your existing database structure supports this system. No additional tables needed.

### 2. File Permissions
Make sure the cron script is executable:
```bash
chmod +x cron/auto-inactive-donors.php
```

### 3. Cron Job Setup (Optional but Recommended)
Add to your crontab to run daily at 2 AM:
```bash
0 2 * * * /usr/bin/php /path/to/your/bbdms/cron/auto-inactive-donors.php
```

Or run weekly on Sundays:
```bash
0 2 * * 0 /usr/bin/php /path/to/your/bbdms/cron/auto-inactive-donors.php
```

### 4. Manual Testing
You can manually test the inactive donor check by:
1. Going to Admin Panel > Donors
2. Clicking "Check Inactive Donors" button

## How It Works

### Status Management
1. **Active**: Donor is available for blood donation requests
2. **Pending**: New donor waiting for admin approval
3. **Inactive**: Donor not available (manual or automatic)

### Activity Tracking
- `updated_at` field tracks last donor activity
- System considers donors inactive after 3 months
- Activity is updated when:
  - Donor logs in
  - Donor updates profile
  - Admin manually updates donor
  - Donor responds to messages

### Admin Interface
- **Status Dropdown**: Click to change any donor's status
- **Visual Indicators**: Color-coded rows and badges
- **Bulk Operations**: Check all inactive donors at once
- **Statistics**: Real-time counts of donors by status

## Customization

### Change Inactive Period
To change from 3 months to a different period, modify these files:
- `admin/check-inactive-donors.php` (line with `strtotime('-3 months')`)
- `admin/donor-activity-tracker.php` (multiple locations)

### Add Email Notifications
Implement email notifications in:
- `admin/donor-activity-tracker.php` in the `notifyDonorsBeforeInactive()` function

### Custom Activity Triggers
Add calls to `updateDonorActivity($donor_id)` in other parts of your system where donor activity occurs.

## Security Notes

- All status changes are logged
- Only admins can change donor status
- CSRF protection should be added for production use
- Input validation is implemented for all status changes

## Troubleshooting

### Status Not Updating
1. Check browser console for JavaScript errors
2. Verify admin permissions
3. Check PHP error logs

### Cron Job Not Working
1. Verify file permissions
2. Check cron logs: `grep CRON /var/log/syslog`
3. Test script manually: `php cron/auto-inactive-donors.php`

### Database Issues
1. Verify database connection in `config/database.php`
2. Check that `users` table has `updated_at` column
3. Ensure proper MySQL/MariaDB permissions

## Future Enhancements

- Email notifications for status changes
- Donor activity dashboard
- Bulk status operations
- Export functionality for donor lists
- Integration with donation scheduling system