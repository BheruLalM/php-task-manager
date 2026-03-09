<?php
require '../config/db_connect.php';

// delete task
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
    $stmt->execute([':id' => (int) $_GET['id']]);
}

header('Location: ../index.php');
exit;
?>
