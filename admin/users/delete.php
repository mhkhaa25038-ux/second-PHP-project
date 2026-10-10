<?php
require_once '../../config/database.php';
$conn = db_connect();
$id = $_GET['id'];

$sql = "DELETE FROM users WHERE user_id=$id";

mysqli_query($conn, $sql);

header("Location: index.php");
?>