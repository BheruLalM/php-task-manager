<?php
session_start();
require '../config/db_connect.php';

// session guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// delete task — only if it belongs to this user
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => (int) $_GET['id'], ':user_id' => $_SESSION['user_id']]);
}

header('Location: ../index.php');
exit;
?>
