<?php
// app/Bootstrap.php

// Composer autoload (PSR-4 + files:functions.php)
require_once __DIR__ . '/../vendor/autoload.php';

// Muat konfigurasi & koneksi DB
$config = require __DIR__ . '/../config/config.php';
$pdo    = require __DIR__ . '/../config/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
