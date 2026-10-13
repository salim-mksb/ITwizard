<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. FETCH MESSAGES
if ($action === 'fetch') {
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at ASC");
    $messages = $stmt->fetchAll();
    
    $instStmt = $pdo->query("SELECT message_id FROM instructions");
    $savedMessageIds = $instStmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($messages as &$msg) {
        $msg['is_saved'] = in_array($msg['id'], $savedMessageIds);
    }
    
    header('Content-Type: application/json');
    echo json_encode($messages);
    exit;
}

// 2. SEND MESSAGE
if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender = trim($_POST['sender'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $allowedSenders = ['Ifwat', 'Amira', 'Umairah'];
    
    if (in_array($sender, $allowedSenders) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender, message) VALUES (?, ?)");
        $stmt->execute([$sender, $message]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Please select a valid sender and type a message.']);
    }
    exit;
}

// 3. EDIT MESSAGE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if ($id > 0 && !empty($message)) {
        $stmt = $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?");
        $success = $stmt->execute([$message, $id]);
        
        // Synchronize with instructions if saved previously
        $pdo->prepare("UPDATE instructions SET details = ? WHERE message_id = ?")->execute([$message, $id]);
        
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Message content cannot be empty.']);
    }
    exit;
}

// 4. DELETE MESSAGE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $success = $stmt->execute([$id]);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID.']);
    }
    exit;
}
?>