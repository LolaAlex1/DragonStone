<?php
require_once __DIR__ . '/includes/config.php';   // <-- defines BASE_URL
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php'; // url(), ensure_session(), etc.

logout_user();
set_flash('You have been logged out successfully.', 'info');

header('Location: ' . url('auth_login.php'));
exit;