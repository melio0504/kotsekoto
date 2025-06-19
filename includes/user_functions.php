<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once 'config.php';
require_once 'auth.php';

function getAllUsers() {
    global $conn;
    
    $sql = "SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUserById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, email, phone, address, role, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function updateUserRole($user_id, $role) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    return $stmt->execute();
}

function getUserCount() {
    global $conn;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    return $result->fetch_assoc()['count'];
}

function getUserStats() {
    global $conn;
    
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as admins FROM users WHERE role = 'admin'");
    $stats['admins'] = $result->fetch_assoc()['admins'];
    
    $result = $conn->query("SELECT COUNT(*) as clients FROM users WHERE role = 'client'");
    $stats['clients'] = $result->fetch_assoc()['clients'];
    
    $result = $conn->query("SELECT COUNT(*) as new_users FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stats['new_users'] = $result->fetch_assoc()['new_users'];
    
    return $stats;
}

function updateUserProfile($user_id, $data) {
    global $conn;
    
    $sql = "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $data['name'], $data['phone'], $data['address'], $user_id);
    
    return $stmt->execute();
}

function changeUserPassword($user_id, $current_password, $new_password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        return false;
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    return $stmt->execute();
}

function deleteUser($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status IN ('pending', 'confirmed', 'active')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return false; 
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);

    ob_clean();

    switch ($_POST['action']) {
        case 'get_all_users':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $users = getAllUsers();
            echo json_encode(['success' => true, 'data' => $users]);
            exit;
            
        case 'get_user':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login first']);
                exit;
            }
            
            $user_id = $_POST['user_id'] ?? $_SESSION['user_id'];
            $user = getUserById($user_id);
            
            if ($user) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            exit;
            
        case 'update_user_role':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $user_id = $_POST['user_id'];
            $role = $_POST['role'];
            
            if (updateUserRole($user_id, $role)) {
                echo json_encode(['success' => true, 'message' => 'User role updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update user role']);
            }
            exit;
            
        case 'get_user_count':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $count = getUserCount();
            echo json_encode(['success' => true, 'count' => $count]);
            exit;
            
        case 'get_user_stats':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $stats = getUserStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            exit;
            
        case 'update_profile':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login first']);
                exit;
            }
            
            $data = [
                'name' => $_POST['name'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address']
            ];
            
            if (updateUserProfile($_SESSION['user_id'], $data)) {
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
            }
            exit;
            
        case 'change_password':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login first']);
                exit;
            }
            
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            
            if (changeUserPassword($_SESSION['user_id'], $current_password, $new_password)) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            }
            exit;
            
        case 'delete_user':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $user_id = $_POST['user_id'];
            
            if (deleteUser($user_id)) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cannot delete user with active bookings']);
            }
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}