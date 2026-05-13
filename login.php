<?php
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$stmt = $db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip = ?");
$stmt->execute([$ip]);
$attempt_data = $stmt->fetch(PDO::FETCH_ASSOC);

$locked_out = false;
if ($attempt_data && $attempt_data['attempts'] >= 5) {
    $last_attempt_time = strtotime($attempt_data['last_attempt']);
    if (time() - $last_attempt_time < 300) {
        $locked_out = true;
        $error = 'Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za 5 minut.';
    } else {
        $db->prepare("UPDATE login_attempts SET attempts = 0 WHERE ip = ?")->execute([$ip]);
    }
}

if (!$locked_out && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $db->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nieprawidłowa nazwa użytkownika lub hasło.';
        if ($attempt_data) {
            $db->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE ip = ?")->execute([$ip]);
        } else {
            $db->prepare("INSERT INTO login_attempts (ip, attempts) VALUES (?, 1)")->execute([$ip]);
        }
    }
}
?>
<div style="display: flex; align-items: center; justify-content: center; min-height: 70vh;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <h2>Logowanie</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Nazwa użytkownika</label>
            <input type="text" id="username" name="username" required <?= $locked_out ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label for="password">Hasło</label>
            <input type="password" id="password" name="password" required <?= $locked_out ? 'disabled' : '' ?>>
        </div>
        <button type="submit" <?= $locked_out ? 'disabled' : '' ?>>Zaloguj się</button>
    </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
