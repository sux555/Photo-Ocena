<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $db->prepare("SELECT username, avatar, theme_color, effects_enabled FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme_color = $_POST['theme_color'] ?? '#ffffff';
    $effects_enabled = isset($_POST['effects_enabled']) ? 1 : 0;
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (in_array($mime_type, $allowed_types)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_avatar = uniqid('avatar_') . '.' . $ext;
            $destination = __DIR__ . '/assets/avatars/' . $new_avatar;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$new_avatar, $user_id]);
                $user['avatar'] = $new_avatar;
            }
        } else {
            $error = 'Dozwolone są tylko pliki graficzne (JPG, PNG, GIF, WEBP).';
        }
    }
    
    $stmt = $db->prepare("UPDATE users SET theme_color = ?, effects_enabled = ? WHERE id = ?");
    if ($stmt->execute([$theme_color, $effects_enabled, $user_id])) {
        $success = 'Profil został zaktualizowany!';
        $user['theme_color'] = $theme_color;
        $user['effects_enabled'] = $effects_enabled;
        header("Refresh:1");
    } else {
        $error = 'Wystąpił błąd podczas aktualizacji profilu.';
    }
}
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Mój Profil</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="assets/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-color);">
        <h3><?= htmlspecialchars($user['username']) ?></h3>
    </div>
    
    <form method="POST" action="profile.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="avatar">Zmień awatar (Max 5 MB)</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
        </div>
        
        <div class="form-group">
            <label for="theme_color">Kolor wiodący (Motyw)</label>
            <input type="color" id="theme_color" name="theme_color" value="<?= htmlspecialchars($user['theme_color']) ?>" style="height: 50px; padding: 5px;">
        </div>
        
        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" id="effects_enabled" name="effects_enabled" <?= $user['effects_enabled'] ? 'checked' : '' ?> style="width: auto;">
            <label for="effects_enabled">Włącz efekty wizualne (animacje, cienie)</label>
        </div>
        
        <button type="submit">Zapisz zmiany</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
