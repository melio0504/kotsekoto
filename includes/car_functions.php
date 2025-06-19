<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once __DIR__ . '/auth.php';

function getCars($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['type'])) {
        $sql .= " AND type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    if (!empty($filters['brand'])) {
        $sql .= " AND brand = ?";
        $params[] = $filters['brand'];
        $types .= "s";
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND daily_rate >= ?";
        $params[] = $filters['min_price'];
        $types .= "d";
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND daily_rate <= ?";
        $params[] = $filters['max_price'];
        $types .= "d";
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }
    
    $sql .= " ORDER BY " . ($filters['sort'] ?? 'created_at DESC');
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCarById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function addCar($data) {
    global $conn;
    $allowed_fuel_types = ['gasoline', 'diesel', 'electric', 'hybrid'];
    if (!in_array($data['fuel_type'], $allowed_fuel_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid fuel type']);
        exit;
    }
    
    $sql = "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisdssississ", 
        $data['brand'], 
        $data['model'], 
        $data['year'], 
        $data['type'], 
        $data['daily_rate'], 
        $data['image_url'], 
        $data['description'], 
        $data['seats'], 
        $data['transmission'], 
        $data['fuel_type'], 
        $data['mileage'], 
        $data['features'], 
        $data['status']
    );
    
    return $stmt->execute();
}

function updateCar($id, $data) {
    global $conn;
    $allowed_fuel_types = ['gasoline', 'diesel', 'electric', 'hybrid'];
    if (!in_array($data['fuel_type'], $allowed_fuel_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid fuel type']);
        exit;
    }
    
    $sql = "UPDATE cars SET 
            brand = ?, model = ?, year = ?, type = ?, daily_rate = ?, 
            image_url = ?, description = ?, seats = ?, transmission = ?, 
            fuel_type = ?, mileage = ?, features = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisdssisssisi", 
        $data['brand'], 
        $data['model'], 
        $data['year'], 
        $data['type'], 
        $data['daily_rate'], 
        $data['image_url'], 
        $data['description'], 
        $data['seats'], 
        $data['transmission'], 
        $data['fuel_type'], 
        $data['mileage'], 
        $data['features'], 
        $data['status'],
        $id
    );
    
    return $stmt->execute();
}

function deleteCar($id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function checkCarAvailability($car_id, $pickup_date, $return_date) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM bookings 
            WHERE car_id = ? AND status IN ('confirmed', 'active') 
            AND ((pickup_date BETWEEN ? AND ?) OR (return_date BETWEEN ? AND ?) 
            OR (pickup_date <= ? AND return_date >= ?))";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $car_id, $pickup_date, $return_date, $pickup_date, $return_date, $pickup_date, $return_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] == 0;
}

function getCarStats() {
    global $conn;
    
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM cars");
    $stats['total'] = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as available FROM cars WHERE status = 'available'");
    $stats['available'] = $result->fetch_assoc()['available'];
    
    $result = $conn->query("SELECT type, COUNT(*) as count FROM cars GROUP BY type");
    $stats['by_type'] = $result->fetch_all(MYSQLI_ASSOC);
    
    $result = $conn->query("SELECT AVG(daily_rate) as avg_rate FROM cars");
    $stats['avg_rate'] = $result->fetch_assoc()['avg_rate'];
    
    return $stats;
}

function getBrands() {
    global $conn;
    
    $result = $conn->query("SELECT DISTINCT brand FROM cars ORDER BY brand");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCarTypes() {
    global $conn;
    
    $result = $conn->query("SELECT DISTINCT type FROM cars ORDER BY type");
    return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_cars':
            $filters = [];
            if (isset($_POST['filters'])) {
                if (is_array($_POST['filters'])) {
                    $filters = $_POST['filters'];
                } else {
                    $filters = json_decode($_POST['filters'], true) ?? [];
                }
            }
            $cars = getCars($filters);
            echo json_encode(['success' => true, 'data' => $cars]);
            break;
            
        case 'get_car':
            $id = $_POST['id'] ?? 0;
            $car = getCarById($id);
            if ($car) {
                echo json_encode(['success' => true, 'data' => $car]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Car not found']);
            }
            break;
            
        case 'add_car':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            
            $carData = json_decode($_POST['car_data'], true);
            if (addCar($carData)) {
                echo json_encode(['success' => true, 'message' => 'Car added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add car']);
            }
            break;
            
        case 'update_car':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            
            $id = $_POST['id'] ?? 0;
            $data = isset($_POST['car_data']) ? json_decode($_POST['car_data'], true) : [];
            if (updateCar($id, $data)) {
                echo json_encode(['success' => true, 'message' => 'Car updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update car']);
            }
            break;
            
        case 'delete_car':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $id = $_POST['id'] ?? 0;
            if (deleteCar($id)) {
                echo json_encode(['success' => true, 'message' => 'Car deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete car']);
            }
            break;
            
        case 'check_availability':
            $car_id = $_POST['car_id'] ?? 0;
            $pickup_date = $_POST['pickup_date'] ?? '';
            $return_date = $_POST['return_date'] ?? '';
            
            if (checkCarAvailability($car_id, $pickup_date, $return_date)) {
                echo json_encode(['success' => true, 'available' => true]);
            } else {
                echo json_encode(['success' => true, 'available' => false]);
            }
            break;
            
        case 'get_brands':
            $brands = getBrands();
            echo json_encode(['success' => true, 'data' => $brands]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>