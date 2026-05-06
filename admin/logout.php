<?php
require_once __DIR__ . '/auth_admin.php';
session_destroy();
header('Location: login.php');
exit;
