# KotseKoTo - Car Rental System

A modern and user-friendly car rental management system developed using PHP, MySQL, HTML, Tailwind CSS, and JavaScript. Designed for seamless local deployment with XAMPP, ideal for demonstrations and small-scale rental operations.

## Features

### User Features
- **User Registration & Login**: Secure authentication system with role-based access
- **Car Browsing**: Filter and search cars by type, brand, price, and availability
- **Car Details**: Comprehensive car information with specifications and features
- **Booking System**: Easy car reservation with date selection and pricing calculation

### Admin Features
- **Admin Dashboard**: Overview of system statistics and recent activities
- **Car Management**: Full CRUD operations for car inventory
- **Booking Management**: Manage and update booking statuses
- **User Management**: View and manage user accounts

## Tech Stack

- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Local Server**: XAMPP (Apache + MySQL + PHP)

## Screenshots

![Screenshots](/assets/screenshot-1.png)
![Screenshots](/assets/screenshot-2.png)
![Screenshots](/assets/screenshot-3.png)
![Screenshots](/assets/screenshot-4.png)

## Installation & Setup

### Prerequisites
- XAMPP (Download from https://www.apachefriends.org/)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Step 1: Install XAMPP
1. Download and install XAMPP for your operating system
2. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project
1. Clone or download this project
2. Place the project folder in your XAMPP `htdocs` directory:

   ```
   C:\xampp\htdocs\KotseKoTo\ (Windows)
   /opt/lampp/htdocs/KotseKoTo/ (Linux)
   /Applications/XAMPP/htdocs/KotseKoTo/ (macOS)
   ```

### Step 3: Database Setup
1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Create a new database named `kotsekoto` (or your preferred name, but update `includes/config.php` accordingly).
3. Run the following SQL to create the tables and add sample data:

```sql
-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'admin') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create cars table
CREATE TABLE IF NOT EXISTS cars (
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
    status ENUM('available', 'unavailable', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
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
);

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'gcash', 'paymaya', 'bank_transfer') NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES ('Admin User', 'admin@kotsekoto.com', '$2y$10$REPLACE_WITH_HASHED_PASSWORD', 'admin')
    ON DUPLICATE KEY UPDATE email=email;

-- Insert sample cars
INSERT INTO cars (brand, model, year, type, daily_rate, image_url, description, seats, transmission, fuel_type, mileage, features) VALUES
('Toyota', 'Vios', 2023, 'sedan', 1500.00, 'https://content.toyota.com.ph/uploads/articles/251/003_251_1615361615860_000.png', 'A reliable and fuel-efficient sedan, perfect for city driving.', 5, 'automatic', 'gasoline', 12000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags'),
('Honda', 'Civic', 2023, 'sedan', 1800.00, 'https://automobiles.honda.com/-/media/Honda-Automobiles/Vehicles/2026/civic-sedan/non-VLP/10-Family/MY26_Civic_Family_Card_Jelly_Hybrid_2x.jpg?sc_lang=en', 'Sporty design and advanced features for a comfortable ride.', 5, 'automatic', 'gasoline', 9000, 'Bluetooth, Cruise Control, Power Windows, ABS, Airbags'),
('Mitsubishi', 'Lancer', 2022, 'sedan', 1600.00, 'https://stimg.cardekho.com/images/carexteriorimages/930x620/Mitsubishi/Mitsubishi-Lancer/3379/1544677323023/front-left-side-47.jpg', 'Classic sedan with a reputation for durability and performance.', 5, 'automatic', 'gasoline', 15000, 'Bluetooth, Power Windows, ABS, Airbags'),
('Hyundai', 'Accent', 2023, 'sedan', 1400.00, 'https://gcc-1307444343.cos.accelerate.myqcloud.com/cc/modelImage/20241217144656_Accent.png', 'Compact and efficient, ideal for daily commutes.', 5, 'automatic', 'gasoline', 8000, 'Bluetooth, Power Windows, ABS, Airbags'),
('Nissan', 'Almera', 2023, 'sedan', 1300.00, 'https://www-asia.nissan-cdn.net/content/dam/Nissan/th/vehicles/VLP/almera-my23/new/spec/vl-spec.jpg', 'Affordable sedan with spacious interior and modern features.', 5, 'automatic', 'gasoline', 7000, 'Bluetooth, Power Windows, ABS, Airbags'),
('Mazda', '3', 2023, 'sedan', 1700.00, 'https://di-sitebuilder-assets.dealerinspire.com/Mazda/model-pages/2024/Mazda3+Sedan/trim-25-s.png', 'Stylish and fun-to-drive sedan with premium features.', 5, 'automatic', 'gasoline', 6000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, Sunroof'),
('Mitsubishi', 'Montero Sport', 2023, 'suv', 2500.00, 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEjKWZelz-PSKSWe19Ls8x7cvisCcZ-a0G0POtErE52eORCAovmcVQ-lEwWOkbfPEehKSyR-wh2d6N1_4mPAkRrzMy8o6KiQiX7fsQshmBm9cF8QL-dIqQv-UEAXNe3tquwehcAEevgkjQY/s1600/pajero-gl-compress.png', 'Powerful SUV with off-road capabilities and spacious cabin.', 7, 'automatic', 'diesel', 10000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, GPS'),
('Toyota', 'Fortuner', 2023, 'suv', 2600.00, 'https://toyotatuguegarao.com.ph/wp-content/uploads/2024/06/Super-White-II-1.webp', 'Rugged and reliable SUV, perfect for family adventures.', 7, 'automatic', 'diesel', 9000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, GPS'),
('Honda', 'CR-V', 2023, 'suv', 2400.00, 'https://di-uploads-development.dealerinspire.com/bommaritohonda/uploads/2021/03/2021-CR-V-Touring.png', 'Comfortable and efficient SUV with advanced safety features.', 7, 'automatic', 'diesel', 8000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, Lane Assist'),
('Isuzu', 'MU-X', 2023, 'suv', 2550.00, 'https://www.isuzu.co.jp/newsroom/assets/img/20240612_1_im01.png', 'Durable SUV with strong performance and ample space.', 7, 'automatic', 'diesel', 11000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, Hill Start Assist'),
('Nissan', 'Urvan', 2024, 'van', 2000.00, 'https://www-europe.nissan-cdn.net/content/dam/Nissan/nissan_middle_east/vehicles/urvan/configurator/URVAN-2.5-MT-13S-4DR-Microbus-H-R-EX.jpg', 'Multi-purpose van with flexible seating and ample cargo space.', 8, 'automatic', 'diesel', 12000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, USB Charging'),
('Toyota', 'Hiace', 2023, 'van', 2200.00, 'https://www.toyota.gm/media/gamme/modeles/images/30d27f192f2033c2d75991c539c65eaf.png', 'Reliable van with spacious interior, ideal for both family and business use.', 12, 'manual', 'diesel', 15000, 'Bluetooth, Backup Camera, Power Windows, ABS, Airbags, USB Charging, Navigation System');
```

4. Update the admin password hash as needed (the above is a placeholder).

### Step 4: Access the Application
1. Open your web browser
2. Navigate to `http://localhost/KotseKoTo/`
3. The application is ready to use!

## Default Admin Account

The system automatically creates a default admin account:

- **Email**: admin@kotsekoto.com
- **Password**: admin123

**Important**: Change the default admin password after first login for security.

## File Structure

```
KotseKoTo/
├── assets/
│   ├── favicon.ico
│   ├── sedan.webp
│   ├── suv.webp
│   └── van.webp
├── components/
│   ├── footer.php
│   └── nav.php
├── includes/
│   ├── auth.php
│   ├── booking_functions.php
│   ├── car_functions.php
│   ├── config.php
│   ├── flash.php
│   ├── user_functions.php
│   └── validation.php
├── pages/
│   ├── admin_login.php
│   ├── browse.php
│   ├── car-details.php
│   ├── dashboard.php
│   ├── login.php
│   ├── profile.php
│   ├── register.php
├── index.php
└── README.md
```

## License

This project is for educational and demonstration purposes. Feel free to modify and use for your own projects.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request