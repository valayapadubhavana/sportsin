<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'register':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, user_type, sport, location) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['fullName'],
                    $data['email'],
                    $data['password'],
                    $data['userType'],
                    $data['sport'],
                    $data['location']
                ]);
                
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            }
            break;
            
        case 'login':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
                $stmt->execute([$data['email'], $data['password']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo json_encode(['success' => true, 'user' => $user]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
                }
            }
            break;
            
        case 'create_post':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("INSERT INTO posts (author_id, content, attachments, likes, comments) VALUES (?, ?, ?, '[]', '[]')");
                $stmt->execute([
                    $data['authorId'],
                    $data['content'],
                    json_encode($data['attachments'] ?? [])
                ]);
                
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            }
            break;
            
        case 'get_posts':
            $stmt = $pdo->prepare("SELECT p.*, u.full_name, u.user_type, u.profile_photo FROM posts p JOIN users u ON p.author_id = u.id WHERE p.status = 'approved' ORDER BY p.created_at DESC");
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($posts);
            break;
            
        case 'get_pending_posts':
            $stmt = $pdo->prepare("SELECT p.*, u.full_name, u.user_type FROM posts p JOIN users u ON p.author_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at DESC");
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($posts);
            break;
            
        case 'approve_post':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("UPDATE posts SET status = 'approved' WHERE id = ?");
                $stmt->execute([$data['postId']]);
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'delete_post':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$data['postId']]);
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'get_users':
            $stmt = $pdo->prepare("SELECT id, full_name, email, user_type, sport, location, created_at, suspended FROM users ORDER BY created_at DESC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($users);
            break;
            
        case 'delete_user':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$data['userId']]);
                
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'suspend_user':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $stmt = $pdo->prepare("UPDATE users SET suspended = 1 WHERE id = ?");
                $stmt->execute([$data['userId']]);
                
                echo json_encode(['success' => true]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>