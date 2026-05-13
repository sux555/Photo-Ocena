<?php
require_once 'includes/header.php';

$query = "
    SELECT p.*, u.username, u.avatar,
    (SELECT SUM(is_like = 1) FROM likes WHERE photo_id = p.id) as likes_count,
    (SELECT SUM(is_like = 0) FROM likes WHERE photo_id = p.id) as dislikes_count
    FROM photos p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.uploaded_at DESC
";
$stmt = $db->query($query);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Najnowsze zdjęcia</h2>
<div class="feed">
    <?php foreach ($photos as $photo): ?>
        <div class="card">
            <div class="photo-info">
                <div>
                    <img src="assets/avatars/<?= htmlspecialchars($photo['avatar']) ?>" class="avatar-small" alt="Avatar">
                    <strong><?= htmlspecialchars($photo['username']) ?></strong>
                </div>
                <small><?= date('d.m.Y H:i', strtotime($photo['uploaded_at'])) ?></small>
            </div>
            <img src="assets/uploads/<?= htmlspecialchars($photo['filename']) ?>" class="photo-img" alt="Zdjęcie">
            <div class="actions">
                <button class="btn-like" data-photo-id="<?= $photo['id'] ?>">👍 <span class="likes-count"><?= (int)$photo['likes_count'] ?></span></button>
                <button class="btn-dislike" data-photo-id="<?= $photo['id'] ?>">👎 <span class="dislikes-count"><?= (int)$photo['dislikes_count'] ?></span></button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?>
        <p>Brak zdjęć do wyświetlenia. Bądź pierwszy i dodaj coś!</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
