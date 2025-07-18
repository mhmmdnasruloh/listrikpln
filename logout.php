<?php
// logout.php
require_once 'config/database.php';

// Destroy session
session_destroy();

// Redirect to login
redirect('pages/login.php');
?>