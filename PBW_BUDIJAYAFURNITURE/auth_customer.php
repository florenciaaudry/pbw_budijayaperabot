<?php
// auth_customer.php
if (session_status() === PHP_SESSION_NONE) session_start();

function customer_is_logged_in(): bool {
  return isset($_SESSION["customer"]) && is_array($_SESSION["customer"]);
}

function require_customer_login(): void {
  if (!customer_is_logged_in()) {
    header("Location: login_customer.php");
    exit;
  }
}

function load_users(string $path = "users.json"): array {
  if (!file_exists($path)) return [];
  $raw = file_get_contents($path);
  $arr = json_decode($raw, true);
  return is_array($arr) ? $arr : [];
}

function save_users(array $users, string $path = "users.json"): void {
  file_put_contents($path, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}