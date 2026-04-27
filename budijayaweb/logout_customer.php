<?php
if (session_status() === PHP_SESSION_NONE) session_start();
unset($_SESSION['customer']);
session_write_close();
header('Location: index.php');
exit;
