<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$admin_id = $_SESSION['user_id'] ?? null;
$admin = new admin($admin_id);

$emp_id = $_SESSION['last_emp_id'] ?? null;
$password = $_SESSION['password'] ?? null;
if (!$emp_id && !$password) {
    echo "No last 11employee found.";
    exit;
}

global $pdo;
$stmt = $pdo->prepare("
            SELECT e.*, p.last_name, p.first_name, l.login_id, l.role
            FROM employee e
            LEFT JOIN passport p ON p.passport_id = e.passport_id
            LEFT JOIN login l ON l.user_id = e.emp_id
            WHERE e.emp_id = ?
        ");
        $stmt->execute([$emp_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Last Created employee</h2>

<?php if ($employee): ?>
<ul>
    <li>employee ID: <?= htmlspecialchars($employee['emp_id']) ?></li>
    <li>Passport ID: <?= htmlspecialchars($employee['passport_id']) ?></li>
    <li>Name: <?= htmlspecialchars($employee['last_name'] . ' ' . $employee['first_name']) ?></li>
    <li>Phone: <?= htmlspecialchars($employee['phone']) ?></li>
    <li>Email: <?= htmlspecialchars($employee['email']) ?></li>
    <li>branch ID: <?= htmlspecialchars($employee['branch_id']) ?></li>
    <li>Login ID: <?= htmlspecialchars($employee['login_id']) ?></li>
    <li>Role: <?= htmlspecialchars($employee['role']) ?></li>
    <li>password: <?= htmlspecialchars($password) ?></li>
</ul>
<?php else: ?>
<p>No last employee found.</p>
<?php endif; ?>