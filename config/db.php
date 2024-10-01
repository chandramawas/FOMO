<?php
// Informasi Database
$servername = "localhost";
$username = "root"; //MySQL username
$password = "root"; //MySQL password
$dbname = "fomo"; //Nama Database

// Membuat koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}