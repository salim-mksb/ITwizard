<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'fetch') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    
    $query = "SELECT * FROM instructions WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR details LIKE ? OR from_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($status) && in_array($status, ['Pending', 'In Progress', 'Done'])) {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    header('Content-Type: application/json');
    echo json_encode($stmt->fetchAll());
    exit;
}

// SAVE INSTRUCTION FROM CHAT MESSAGE
if ($action === 'save_instruction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_id = intval($_POST['message_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $details = trim($_POST['details'] ?? '');
    
    if ($message_id <= 0 || empty($title) || empty($details)) {
        echo json_encode(['success' => false, 'error' => 'Title and details are required.']);
        exit;
    }
    
    $msgStmt = $pdo->prepare("SELECT sender FROM messages WHERE id = ?");
    $msgStmt->execute([$message_id]);
    $msg = $msgStmt->fetch();
    
    if (!$msg) {
        echo json_encode(['success' => false, 'error' => 'Original message not found.']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO instructions (message_id, title, details, from_name, status) VALUES (?, ?, ?, ?, 'Pending')");
    $success = $stmt->execute([$message_id, $title, $details, $msg['sender']]);
    
    echo json_encode(['success' => $success]);
    exit;
}

// MANUAL ADD NEW INSTRUCTION
if ($action === 'manual_add_instruction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $from_name = trim($_POST['from_name'] ?? 'Ifwat');
    
    if (empty($title) || empty($details)) {
        echo json_encode(['success' => false, 'error' => 'Title and details are required.']);
        exit;
    }
    
    // Insert a dummy message record first so foreign key constraint is satisfied, or insert directly if no constraint
    // Since message_id is required in instructions table, we can automatically insert a system message:
    $msgStmt = $pdo->prepare("INSERT INTO messages (sender, message) VALUES (?, ?)");
    $msgStmt->execute([$from_name, $details]);
    $message_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO instructions (message_id, title, details, from_name, status) VALUES (?, ?, ?, ?, 'Pending')");
    $success = $stmt->execute([$message_id, $title, $details, $from_name]);
    
    echo json_encode(['success' => $success]);
    exit;
}

if ($action === 'update_instruction' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $details = trim($_POST['details'] ?? '');
    
    if ($id > 0 && !empty($title) && !empty($details)) {
        $stmt = $pdo->prepare("UPDATE instructions SET title = ?, details = ? WHERE id = ?");
        $success = $stmt->execute([$title, $details, $id]);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Fields cannot be blank.']);
    }
    exit;
}

if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    if ($id > 0 && in_array($status, ['Pending', 'In Progress', 'Done'])) {
        $stmt = $pdo->prepare("UPDATE instructions SET status = ? WHERE id = ?");
        $success = $stmt->execute([$status, $id]);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid status.']);
    }
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM instructions WHERE id = ?");
        $success = $stmt->execute([$id]);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid ID.']);
    }
    exit;
}
?>