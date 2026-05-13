<?php
require_once 'includes/header.php';

$query = "
    SELECT * FROM (
        SELECT p.*, u.username, u.avatar, 
        (SELECT COUNT(*) FROM likes WHERE photo_id = p.id AND is_like = 1) as likes_count
        FROM photos p
        JOIN users u ON p.user_id = u.id
    ) as subquery
    WHERE likes_count > 0
    ORDER BY likes_count DESC
    LIMIT 10
";
$stmt = $db->query($query);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Ranking - Top 10 Zdjęć</h2>
<div class="feed">
    <?php foreach ($photos as $index => $photo): ?>
        <div class="card">
            <div class="photo-info">
                <div>
                    <span style="font-size: 24px; font-weight: bold; color: var(--accent-color); margin-right: 10px;">#<?= $index + 1 ?></span>
                    <img src="assets/avatars/<?= htmlspecialchars($photo['avatar']) ?>" class="avatar-small" alt="Avatar">
                    <strong><?= htmlspecialchars($photo['username']) ?></strong>
                </div>
            </div>
            <img src="assets/uploads/<?= htmlspecialchars($photo['filename']) ?>" class="photo-img" alt="Zdjęcie">
            <div class="actions">
                <span style="color: var(--accent-color); font-weight: bold;">👍 <?= (int)$photo['likes_count'] ?> polubień</span>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($photos)): ?>
        <p>Brak ocenionych zdjęć w rankingu.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
