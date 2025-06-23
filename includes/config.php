<?php
$env_path = dirname(__DIR__) . '/.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        if (!getenv($name)) putenv("$name=$value");
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'kotsekoto';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$sql = "CREATE DATABASE IF NOT EXISTS $database";
$conn->query($sql);

$conn->select_db($database);

// Create tables if they don't exist
createTables($conn);

function createTables($conn) {
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        password VARCHAR(255) NOT NULL,
        role ENUM('client', 'admin') DEFAULT 'client',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $cars_table = "CREATE TABLE IF NOT EXISTS cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand VARCHAR(100) NOT NULL,
        model VARCHAR(100) NOT NULL,
        year INT NOT NULL,
        type ENUM('sedan', 'suv', 'van') NOT NULL,
        daily_rate DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(500),
        description TEXT,
        seats INT DEFAULT 5,
        transmission ENUM('manual', 'automatic') DEFAULT 'automatic',
        fuel_type ENUM('gasoline', 'diesel', 'electric', 'hybrid') DEFAULT 'gasoline',
        mileage INT DEFAULT 0,
        features TEXT,
        status ENUM('available', 'unavailable') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $bookings_table = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        pickup_date DATE NOT NULL,
        return_date DATE NOT NULL,
        pickup_location VARCHAR(255) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
        payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
        booking_reference VARCHAR(20) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    )";
    
    $payments_table = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method ENUM('credit_card', 'gcash', 'paymaya', 'bank_transfer') NOT NULL,
        transaction_id VARCHAR(100),
        status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )";
    
    $conn->query($users_table);
    $conn->query($cars_table);
    $conn->query($bookings_table);
    $conn->query($payments_table);
    
    $admin_check = "SELECT id FROM users WHERE email = 'admin@kotsekoto.com'";
    $result = $conn->query($admin_check);
    
    if ($result->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $admin_insert = "INSERT INTO users (name, email, password, role) VALUES ('Admin User', 'admin@kotsekoto.com', '$admin_password', 'admin')";
        $conn->query($admin_insert);
    }
    
    $cars_check = "SELECT id FROM cars LIMIT 1";
    $result = $conn->query($cars_check);
    
    if ($result->num_rows == 0) {
        $sample_cars = [
            // Sedans
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Toyota', 'Vios', 2023, 'sedan', 1500.00, 'https://content.toyota.com.ph/uploads/articles/251/003_251_1615361615860_000.png', 'A reliable and fuel-efficient sedan, perfect for city driving.', 5, 'automatic', 'gasoline', 12000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Honda', 'Civic', 2023, 'sedan', 1800.00, 'https://automobiles.honda.com/-/media/Honda-Automobiles/Vehicles/2026/civic-sedan/non-VLP/10-Family/MY26_Civic_Family_Card_Jelly_Hybrid_2x.jpg?sc_lang=en', 'Sporty design and advanced features for a comfortable ride.', 5, 'automatic', 'gasoline', 9000, 'Bluetooth, Cruise Control, Power Windows, ABS, Airbags')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Mitsubishi', 'Lancer', 2022, 'sedan', 1600.00, 'https://stimg.cardekho.com/images/carexteriorimages/930x620/Mitsubishi/Mitsubishi-Lancer/3379/1544677323023/front-left-side-47.jpg', 'Classic sedan with a reputation for durability and performance.', 5, 'automatic', 'gasoline', 15000, 'Bluetooth, Power Windows, ABS, Airbags')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Hyundai', 'Accent', 2023, 'sedan', 1400.00, 'https://gcc-1307444343.cos.accelerate.myqcloud.com/cc/modelImage/20241217144656_Accent.png', 'Compact and efficient, ideal for daily commutes.', 5, 'automatic', 'gasoline', 8000, 'Bluetooth, Power Windows, ABS, Airbags')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Nissan', 'Almera', 2023, 'sedan', 1300.00, 'https://www-asia.nissan-cdn.net/content/dam/Nissan/th/vehicles/VLP/almera-my23/new/spec/vl-spec.jpg', 'Affordable sedan with spacious interior and modern features.', 5, 'automatic', 'gasoline', 7000, 'Bluetooth, Power Windows, ABS, Airbags')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Mazda', '3', 2023, 'sedan', 1700.00, 'https://di-sitebuilder-assets.dealerinspire.com/Mazda/model-pages/2024/Mazda3+Sedan/trim-25-s.png', 'Stylish and fun-to-drive sedan with premium features.', 5, 'automatic', 'gasoline', 6000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, Sunroof')",

            // SUVs
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Mitsubishi', 'Montero Sport', 2023, 'suv', 2500.00, 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjKWZelz-PSKSWe19Ls8x7cvisCcZ-a0G0POtErE52eORCAovmcVQ-lEwWOkbfPEehKSyR-wh2d6N1_4mPAkRrzMy8o6KiQiX7fsQshmBm9cF8QL-dIqQv-UEAXNe3tquwehcAEevgkjQY/s1600/pajero-gl-compress.png', 'Powerful SUV with off-road capabilities and spacious cabin.', 7, 'automatic', 'diesel', 10000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, GPS')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Toyota', 'Fortuner', 2023, 'suv', 2600.00, 'https://toyotatuguegarao.com.ph/wp-content/uploads/2024/06/Super-White-II-1.webp', 'Rugged and reliable SUV, perfect for family adventures.', 7, 'automatic', 'diesel', 9000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, GPS')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Honda', 'CR-V', 2023, 'suv', 2400.00, 'https://di-uploads-development.dealerinspire.com/bommaritohonda/uploads/2021/03/2021-CR-V-Touring.png', 'Comfortable and efficient SUV with advanced safety features.', 7, 'automatic', 'diesel', 8000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, Lane Assist')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Isuzu', 'MU-X', 2023, 'suv', 2550.00, 'https://www.isuzu.co.jp/newsroom/assets/img/20240612_1_im01.png', 'Durable SUV with strong performance and ample space.', 7, 'automatic', 'diesel', 11000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, Hill Start Assist')",

            // Vans
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Nissan', 'Urvan', 2024, 'van', 2000.00, 'https://www-europe.nissan-cdn.net/content/dam/Nissan/nissan_middle_east/vehicles/urvan/configurator/URVAN-2.5-MT-13S-4DR-Microbus-H-R-EX.jpg', 'Multi-purpose van with flexible seating and ample cargo space.', 8, 'automatic', 'diesel', 12000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, USB Charging')",
            "INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES 
            ('Toyota', 'Hiace', 2023, 'van', 2200.00, 'https://www.toyota.gm/media/gamme/modeles/images/30d27f192f2033c2d75991c539c65eaf.png', 'Reliable van with spacious interior, ideal for both family and business use.', 12, 'manual', 'diesel', 15000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, USB Charging, Navigation System')"

        ];
        
        foreach ($sample_cars as $car_sql) {
            $conn->query($car_sql);
        }
    }
}
?>