<?php
require 'config/db_connect.php';

$error = '';

// create task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = trim($_POST['task_name'] ?? '');
    if ($task_name === '' || strlen($task_name) > 40) {
    $error = 'Task name cannot be empty and must be less than 40 characters.';
} else {
    $stmt = $pdo->prepare("INSERT INTO tasks (task_name) VALUES (:task_name)");
    $stmt->execute([':task_name' => $task_name]);
    header('Location: index.php');
    exit;
}
}

// read tasks
$stmt = $pdo->prepare("SELECT * FROM tasks ORDER BY created_at DESC");
$stmt->execute();
$tasks = $stmt->fetchAll();

// compute counts
$totalTasks     = count($tasks);
$totalPending   = 0;
$totalCompleted = 0;
foreach ($tasks as $t) {
    if ($t['status'] === 'Pending') $totalPending++;
    else $totalCompleted++;
}

$appName = $_ENV['APP_NAME'] ?? 'Task Manager';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appName); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <h1><?php echo htmlspecialchars($appName); ?></h1>

    <!-- Add Task Form -->
    <div class="form-box">
        <form method="POST" action="index.php">
            <input type="text" name="task_name" id="task_name" placeholder="Enter a new task..."
                value="<?php echo isset($error) ? '': htmlspecialchars($_POST['task_name'] ?? ''); ?>" autocomplete="off">
            <button type="submit" class="btn-add">Add Task</button>
        </form>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>

    <!-- Tasks Table -->
    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tasks)): ?>
                    <?php $counter = 1; foreach ($tasks as $row): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                            <td>
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-completed">Completed</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <a href="actions/complete_task.php?id=<?php echo $row['id']; ?>" class="btn-complete">Complete</a>
                                <?php endif; ?>
                                <a href="actions/delete_task.php?id=<?php echo $row['id']; ?>" class="btn-delete"
                                    onclick="return confirm('Delete this task?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty">No tasks yet. Add one above!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($tasks)): ?>
            <tfoot>
                <tr class="summary-row">
                    <td colspan="2"><strong>Total Pending</strong></td>
                    <td colspan="3"><span class="badge badge-pending"><?php echo $totalPending; ?></span></td>
                </tr>
                <tr class="summary-row">
                    <td colspan="2"><strong>Total Completed</strong></td>
                    <td colspan="3"><span class="badge badge-completed"><?php echo $totalCompleted; ?></span></td>
                </tr>
                <tr class="summary-row summary-total">
                    <td colspan="2"><strong>Total Tasks</strong></td>
                    <td colspan="3"><strong><?php echo $totalTasks; ?></strong></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

</body>

</html>