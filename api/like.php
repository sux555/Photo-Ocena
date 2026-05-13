<?php

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

function sendResponse($success, $data = array(), $message = '') {
    $response = array('success' => $success);
    if ($message) $response['message'] = $message;
    echo json_encode(array_merge($response, $data));
    exit;
}

try {
    ob_start();
    require_once dirname(__FILE__) . '/../includes/db.php';
    ob_end_clean();

    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, array(), 'Musisz być zalogowany.');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $photo_id = isset($data['photo_id']) ? (int)$data['photo_id'] : 0;
    $type = isset($data['type']) ? $data['type'] : '';

    if (!$photo_id || !in_array($type, array('like', 'dislike'))) {
        sendResponse(false, array(), 'Nieprawidłowe dane wejściowe.');
    }

    $user_id = (int)$_SESSION['user_id'];
    $is_like_val = ($type === 'like') ? 1 : 0;

    $check_stmt = $db->prepare("SELECT is_like FROM likes WHERE photo_id = ? AND user_id = ?");
    $check_stmt->execute(array($photo_id, $user_id));
    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if ((int)$existing['is_like'] === $is_like_val) {
            $db->prepare("DELETE FROM likes WHERE photo_id = ? AND user_id = ?")
               ->execute(array($photo_id, $user_id));
        } else {
            $db->prepare("UPDATE likes SET is_like = ? WHERE photo_id = ? AND user_id = ?")
               ->execute(array($is_like_val, $photo_id, $user_id));
        }
    } else {
        $db->prepare("INSERT INTO likes (photo_id, user_id, is_like) VALUES (?, ?, ?)")
           ->execute(array($photo_id, $user_id, $is_like_val));
    }

    $count_stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN is_like = 1 THEN 1 ELSE 0 END) as likes,
            SUM(CASE WHEN is_like = 0 THEN 1 ELSE 0 END) as dislikes
        FROM likes 
        WHERE photo_id = ?
    ");
    $count_stmt->execute(array($photo_id));
    $stats = $count_stmt->fetch(PDO::FETCH_ASSOC);

    sendResponse(true, array(
        'likes' => (int)$stats['likes'],
        'dislikes' => (int)$stats['dislikes']
    ));

} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    sendResponse(false, array('debug' => $e->getMessage()), 'Wystąpił błąd serwera.');
} catch (Error $e) {
    if (ob_get_length()) ob_end_clean();
    sendResponse(false, array('debug' => $e->getMessage()), 'Krytyczny błąd serwera.');
}