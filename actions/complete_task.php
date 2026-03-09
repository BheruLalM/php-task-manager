<?php
require '../config/db_connect.php';

// update task
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("UPDATE tasks SET status='Completed' WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['id']]);
}

header('Location: ../index.php');
exit;
?>
