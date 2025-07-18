<?php
// index.php
require_once 'config/database.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('pages/dashboard_admin.php');
    } else {
        redirect('pages/dashboard_pelanggan.php');
    }
} else {
    redirect('pages/login.php');
}
?>