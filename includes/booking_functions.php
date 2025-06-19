<?php
require_once 'config.php';
require_once 'auth.php';

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        switch ($_POST['action']) {
            case 'create_booking':
                if (!isLoggedIn()) {
                    echo json_encode(['success' => false, 'message' => 'Please login first']);
                    exit;
                }
                $data = [
                    'user_id' => $_SESSION['user_id'],
                    'car_id' => $_POST['car_id'],
                    'pickup_date' => $_POST['pickup_date'],
                    'return_date' => $_POST['return_date'],
                    'pickup_location' => $_POST['pickup_location'],
                    'total_amount' => $_POST['total_amount'],
                    'payment_method' => $_POST['payment_method']
                ];
                $booking_id = createBooking($data);
                if ($booking_id) {
                    echo json_encode(['success' => true, 'message' => 'Booking created successfully', 'booking_id' => $booking_id]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create booking']);
                }
                exit;
            case 'get_user_bookings':
                if (!isLoggedIn()) {
                    echo json_encode(['success' => false, 'message' => 'Please login first']);
                    exit;
                }
                $bookings = getUserBookings($_SESSION['user_id']);
                echo json_encode(['success' => true, 'data' => $bookings]);
                exit;
            case 'get_all_bookings':
                if (!isAdmin()) {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                    exit;
                }
                $bookings = getAllBookings();
                if (isset($bookings['error'])) {
                    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $bookings['error']]);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $bookings]);
                exit;
            case 'get_recent_bookings':
                if (!isAdmin()) {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                    exit;
                }
                $bookings = getRecentBookings(5);
                $stats = getBookingStats();
                echo json_encode(['success' => true, 'data' => $bookings, 'active_count' => $stats['active']]);
                exit;
            case 'update_booking_status':
                if (!isAdmin()) {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                    exit;
                }
                $booking_id = $_POST['booking_id'];
                $status = $_POST['status'];
                if (updateBookingStatus($booking_id, $status)) {
                    echo json_encode(['success' => true, 'message' => 'Booking status updated']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update booking status']);
                }
                exit;
            case 'get_booking':
                $booking_id = $_POST['booking_id'];
                $booking = getBookingById($booking_id);
                if ($booking) {
                    echo json_encode(['success' => true, 'data' => $booking]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Booking not found']);
                }
                exit;
            case 'calculate_total':
                $car_id = $_POST['car_id'];
                $pickup_date = $_POST['pickup_date'];
                $return_date = $_POST['return_date'];
                $total = calculateBookingTotal($car_id, $pickup_date, $return_date);
                echo json_encode(['success' => true, 'total' => $total]);
                exit;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit;
        }
    }
    exit;
}

function createBooking($data) {
    global $conn;
    
    $booking_reference = 'KKT-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $status = isset($data['status']) ? $data['status'] : 'confirmed';
    $payment_status = isset($data['payment_status']) ? $data['payment_status'] : 'paid';
    
    $sql = "INSERT INTO bookings (user_id, car_id, pickup_date, return_date, pickup_location, total_amount, status, payment_status, booking_reference) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdssss", 
        $data['user_id'], 
        $data['car_id'], 
        $data['pickup_date'], 
        $data['return_date'], 
        $data['pickup_location'], 
        $data['total_amount'], 
        $status,
        $payment_status,
        $booking_reference
    );
    
    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;
        
        createPayment($booking_id, $data['total_amount'], $data['payment_method']);
        
        return $booking_id;
    }
    
    return false;
}

function createPayment($booking_id, $amount, $payment_method) {
    global $conn;
    
    $sql = "INSERT INTO payments (booking_id, amount, payment_method) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $booking_id, $amount, $payment_method);
    
    return $stmt->execute();
}

function getUserBookings($user_id) {
    global $conn;
    
    $sql = "SELECT b.*, c.brand, c.model, c.image_url, c.daily_rate 
            FROM bookings b 
            JOIN cars c ON b.car_id = c.id 
            WHERE b.user_id = ? 
            ORDER BY b.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAllBookings() {
    global $conn;
    
    $sql = "SELECT b.*, u.name as customer_name, c.brand, c.model, 
            CONCAT(c.brand, ' ', c.model) as car_name
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN cars c ON b.car_id = c.id 
            ORDER BY b.created_at DESC";
    
    $result = $conn->query($sql);
    if (!$result) {
        return ['error' => $conn->error];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRecentBookings($limit = 5) {
    global $conn;
    
    $sql = "SELECT b.*, u.name as customer_name, c.brand, c.model 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN cars c ON b.car_id = c.id 
            ORDER BY b.created_at DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateBookingStatus($booking_id, $status) {
    global $conn;
    
    $sql = "UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);
    
    return $stmt->execute();
}

function getBookingById($id) {
    global $conn;
    
    $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email, 
            c.brand, c.model, c.image_url, c.daily_rate, c.type,
            CONCAT(c.brand, ' ', c.model) as car_name
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN cars c ON b.car_id = c.id 
            WHERE b.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function calculateBookingTotal($car_id, $pickup_date, $return_date) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT daily_rate FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    
    if (!$car) return 0;
    
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $days = $return->diff($pickup)->days;
    
    $daily_rate = $car['daily_rate'];
    $insurance_per_day = 200; 
    $total = ($daily_rate + $insurance_per_day) * $days;
    
    return $total;
}

function getBookingStats() {
    global $conn;
    
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM bookings");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as active FROM bookings WHERE status IN ('confirmed', 'active')");
    $stats['active'] = $result->fetch_assoc()['active'];
    
    $result = $conn->query("SELECT COUNT(*) as pending FROM bookings WHERE status = 'pending'");
    $stats['pending'] = $result->fetch_assoc()['pending'];
    
    $result = $conn->query("SELECT SUM(total_amount) as revenue FROM bookings WHERE status IN ('confirmed', 'active', 'completed')");
    $stats['revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
    
    return $stats;
} 