<?
session_start();
$database_file_size = $_SESSION['size1'];

$file_size_in_mb = floor($database_file_size / (1024 * 1024)); // Convert to MB and round down

echo "File size: " . $file_size_in_mb . " MB";