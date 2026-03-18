<?php
$conn = new mysqli('localhost', 'root', '', 'stage_db');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
