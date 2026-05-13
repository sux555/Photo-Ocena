<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (strlen($username) < 3 || strlen($password) < 6) {
        $error = 'Nazwa użytkownika musi mieć co najmniej 3 znaki, a hasło 6 znaków.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Ta nazwa użytkownika jest już zajęta.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashed_password])) {
                $success = 'Konto zostało utworzone! Możesz się teraz zalogować.';
            } else {
                $error = 'Wystąpił błąd podczas tworzenia konta.';
            }
        }
    }
}
?>
<div style="display: flex; align-items: center; justify-content: center; min-height: 70vh;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <h2>Rejestracja</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="username">Nazwa użytkownika</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Hasło</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Zarejestruj się</button>
    </form>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
