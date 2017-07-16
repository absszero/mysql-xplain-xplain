<?php
// Composer install verification : https://github.com/rap2hpoutre/mysql-xplain-xplain/issues/4
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    exit('Install dependencies : "composer install"');
}

// Composer autoloader
require __DIR__ . '/../vendor/autoload.php';
// Some consts
require __DIR__ . '/constants.php';

// Session
session_start();

// UTF-8
header('Content-Type: text/html; charset=utf-8');

// Template engine
$engine = new \League\Plates\Engine(__DIR__ . '/templates');

// Template
$template = new \League\Plates\Template($engine);

// Template title
$template->title = "MySQL Explain Explain";

// Flash message
if (isset($_SESSION['flash_message'])) {
    $template->flash_message  = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
