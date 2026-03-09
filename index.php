<?php
session_start();
require 'config/db_connect.php';

// session guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error   = '';

// create task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name'] ?? '');
    if ($task_name === '' || strlen($task_name) > 40) {
        $error = 'Task name must be 1–40 characters.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (task_name, user_id) VALUES (:task_name, :user_id)");
        $stmt->execute([':task_name' => $task_name, ':user_id' => $user_id]);
        header('Location: index.php');
        exit;
    }
}

// fetch tasks for this user
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $user_id]);
$tasks = $stmt->fetchAll();

$total     = count($tasks);
$pending   = 0;
$completed = 0;
foreach ($tasks as $t) {
    if ($t['status'] === 'Pending') $pending++;
    else $completed++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page">

        <div class="top-bar">
            <h1>Task Manager</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <form method="POST" action="index.php" class="add-form">
            <input type="text" name="task_name" placeholder="New task..." autocomplete="off"
                value="<?php echo $error ? htmlspecialchars($_POST['task_name'] ?? '') : ''; ?>">
            <button type="submit">Add</button>
        </form>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tasks)): ?>
                    <?php $i = 1; foreach ($tasks as $row): ?>
                        <tr class="<?php echo $row['status'] === 'Completed' ? 'done' : ''; ?>">
                            <td class="num"><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <span class="badge pending">Pending</span>
                                <?php else: ?>
                                    <span class="badge completed">Completed</span>
                                <?php endif; ?>
                            </td>
                            <td class="date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td class="actions">
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <a href="actions/complete_task.php?id=<?php echo $row['id']; ?>" class="btn-done">Done</a>
                                <?php endif; ?>
                                <a href="actions/delete_task.php?id=<?php echo $row['id']; ?>" class="btn-del"
                                    onclick="return confirm('Delete?')">Del</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty">No tasks yet. Add one above!</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if ($total > 0): ?>
            <tfoot>
                <tr>
                    <td colspan="2">Summary</td>
                    <td colspan="3">
                        <span class="badge pending"><?php echo $pending; ?> Pending</span>
                        <span class="badge completed"><?php echo $completed; ?> Completed</span>
                        <span class="badge total"><?php echo $total; ?> Total</span>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>

    </div>
</body>
</html>