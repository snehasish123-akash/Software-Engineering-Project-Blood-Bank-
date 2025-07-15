<?php
require_once 'config/database.php';

// Destroy all session data
session_unset();
session_destroy();

// Start a new session for the message
session_start();
setMessage('You have been successfully logged out.', 'success');

// Redirect to home page
redirectTo('index.php');
?>