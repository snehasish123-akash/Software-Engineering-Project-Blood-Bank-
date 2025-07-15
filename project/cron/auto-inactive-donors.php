#!/usr/bin/env php
<?php
/**
 * Cron job script to automatically set donors to inactive after 3 months
 * 
 * Add this to your crontab to run daily:
 * 0 2 * * * /usr/bin/php /path/to/your/bbdms/cron/auto-inactive-donors.php
 * 
 * Or run weekly:
 * 0 2 * * 0 /usr/bin/php /path/to/your/bbdms/cron/auto-inactive-donors.php
 */

// Change to the directory containing this script
chdir(dirname(__FILE__));

// Include the check-inactive-donors script
require_once '../admin/check-inactive-donors.php';
?>