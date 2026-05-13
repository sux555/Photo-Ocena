<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM photos WHERE user_id = ? AND uploaded_at > datetime('now', '-1 minute')");
    $stmt->execute([$user_id]);
    $recent_uploads = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($recent_uploads >= 2) {
        $error = 'Możesz dodać maksymalnie 2 zdjęcia na minutę. Poczekaj chwilę.';
    } else {
        $file = $_FILES['photo'];
        $max_size = 5 * 1024 * 1024;
        
        if ($file['size'] > $max_size) {
            $error = 'Rozmiar pliku nie może przekraczać 5 MB.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Wystąpił błąd podczas przesyłania pliku.';
        } else {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_types)) {
                $error = 'Dozwolone są tylko pliki graficzne (JPG, PNG, GIF, WEBP).';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('photo_') . '.' . $ext;
                $destination = __DIR__ . '/assets/uploads/' . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $stmt = $db->prepare("INSERT INTO photos (user_id, filename) VALUES (?, ?)");
                    $stmt->execute([$user_id, $new_filename]);
                    $success = 'Zdjęcie zostało dodane pomyślnie!';
                } else {
                    $error = 'Nie udało się zapisać pliku na serwerze.';
                }
            }
        }
    }
}
?>
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h2>Dodaj zdjęcie</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="photo">Wybierz zdjęcie (Max 5 MB)</label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>
        <button type="submit">Prześlij</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>
