<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/car_functions.php';

requireAdmin();

$user = getCurrentUser();
$car_stats = getCarStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KotseKoTo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="./dashboard.php" class="text-2xl font-bold">KotseKoTo Admin</a>
            <div class="flex items-center space-x-4 mr-2">
                <span class="text-lg mr-5">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <button id="logoutBtn" class="bg-red-600 px-4 py-2 rounded hover:bg-red-700">Logout</button>
            </div>
        </div>
    </nav>

    <?php require_once '../includes/flash.php'; display_flash(); ?>
    
    <div class="flex">
        <div class="w-64 bg-white shadow-md min-h-screen">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Admin Panel</h2>
                <nav class="space-y-2">
                    <a href="#dashboard" class="block px-4 py-2 text-blue-600 bg-blue-50 rounded-lg" onclick="showSection('dashboard', event)">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="#cars" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg" onclick="showSection('cars', event)">
                        <i class="fas fa-car mr-2"></i>Car Management
                    </a>
                    <a href="#bookings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg" onclick="showSection('bookings', event)">
                        <i class="fas fa-calendar-check mr-2"></i>Booking Management
                    </a>
                    <a href="#users" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg" onclick="showSection('users', event)">
                        <i class="fas fa-users mr-2"></i>User Management
                    </a>
                </nav>
            </div>
        </div>
        <div class="flex-1 p-8">
            <div id="dashboard" class="section">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard Overview</h1>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-car text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Cars</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $car_stats['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Available Cars</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $car_stats['available']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-calendar text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Bookings</p>
                                <p class="text-2xl font-bold text-gray-900" id="activeBookings">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalUsers">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="cars" class="section hidden">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Car Management</h1>
                    <button onclick="showAddCarModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Add New Car
                    </button>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <form id="adminFilterForm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Car Type</label>
                                <select id="filterType" name="type" class="w-full p-2 border rounded-lg">
                                    <option value="">All Types</option>
                                    <option value="sedan">Sedan</option>
                                    <option value="suv">SUV</option>
                                    <option value="van">Van</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                                <select id="filterBrand" name="brand" class="w-full p-2 border rounded-lg">
                                    <option value="">All Brands</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="filterStatus" name="status" class="w-full p-2 border rounded-lg">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                                <select id="filterSort" name="sort" class="w-full p-2 border rounded-lg">
                                    <option value="created_at DESC">Newest First</option>
                                    <option value="daily_rate ASC">Price Low to High</option>
                                    <option value="daily_rate DESC">Price High to Low</option>
                                    <option value="brand ASC">Brand A-Z</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-center mt-4">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200">Apply Filter</button>
                            <button type="button" id="clearAdminFilters" class="ml-2 bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">Clear</button>
                        </div>
                    </form>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daily Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="carsTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Cars will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="bookings" class="section hidden">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Booking Management</h1>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Ref</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Bookings will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="users" class="section hidden">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">User Management</h1>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Users will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="reports" class="section hidden">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Reports</h1>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h3>
                        <div id="revenueChart" class="h-64">
                            <!-- Chart will be loaded here -->
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Cars</h3>
                        <div id="popularCars" class="space-y-3">
                            <!-- Popular cars will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Car Modal -->
    <div id="carModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="carModalContent" class="bg-white rounded-lg shadow-xl max-w-2xl w-full overflow-y-auto max-h-screen my-8 transform transition-all duration-300 scale-95 opacity-0">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add New Car</h3>
                <button onclick="closeCarModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="carForm" class="p-6">
                <input type="hidden" id="carId" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                        <input type="text" id="carBrand" name="brand" required class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <input type="text" id="carModel" name="model" required class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <input type="number" id="carYear" name="year" required class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select id="carType" name="type" required class="w-full p-2 border rounded-lg">
                            <option value="">Select Type</option>
                            <option value="sedan">Sedan</option>
                            <option value="suv">SUV</option>
                            <option value="van">Van</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Daily Rate (₱)</label>
                        <input type="number" id="carDailyRate" name="daily_rate" step="0.01" required class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="carStatus" name="status" required class="w-full p-2 border rounded-lg">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seats</label>
                        <input type="number" id="carSeats" name="seats" required class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transmission</label>
                        <select id="carTransmission" name="transmission" required class="w-full p-2 border rounded-lg">
                            <option value="automatic">Automatic</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fuel Type</label>
                        <select id="carFuelType" name="fuel_type" required class="w-full p-2 border rounded-lg">
                            <option value="gasoline">Gasoline</option>
                            <option value="diesel">Diesel</option>
                            <option value="electric">Electric</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mileage (km)</label>
                        <input type="number" id="carMileage" name="mileage" required class="w-full p-2 border rounded-lg">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                        <input type="url" id="carImageUrl" name="image_url" class="w-full p-2 border rounded-lg">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="carDescription" name="description" rows="3" class="w-full p-2 border rounded-lg"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Features</label>
                        <textarea id="carFeatures" name="features" rows="3" class="w-full p-2 border rounded-lg" placeholder="Bluetooth, Backup Camera, Power Windows, etc."></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeCarModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Car</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="deleteModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Car</h3>
            <p class="mb-6 text-gray-700">Are you sure you want to delete this car?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="logoutModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Logout</h3>
            <p class="mb-6 text-gray-700">Are you sure you want to logout?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeLogoutModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmLogoutBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Logout</button>
            </div>
        </div>
    </div>

    <!-- Confirm Booking Modal -->
    <div id="confirmBookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="confirmBookingModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Booking</h3>
            <p class="mb-6 text-gray-700">Are you sure you want to confirm this booking?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeConfirmBookingModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmBookingBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Cancel Booking Modal -->
    <div id="cancelBookingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="cancelBookingModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Booking</h3>
            <p class="mb-6 text-gray-700">Are you sure you want to cancel this booking?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCancelBookingModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Back</button>
                <button type="button" id="cancelBookingBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Cancel Booking</button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="successModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4" id="successModalTitle">Success</h3>
            <p class="mb-6 text-gray-700" id="successModalMessage">Car saved successfully!</p>
            <div class="flex justify-end">
                <button type="button" onclick="closeSuccessModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">OK</button>
            </div>
        </div>
    </div>

    <!-- Toggle Role Confirmation Modal -->
    <div id="toggleRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="toggleRoleModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Change User Role</h3>
            <p class="mb-6 text-gray-700" id="toggleRoleModalMessage">Are you sure you want to change this user's role?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeToggleRoleModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmToggleRoleBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Change Role</button>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="deleteUserModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete User</h3>
            <p class="mb-6 text-gray-700">Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteUserModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmDeleteUserBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>

    <!-- User Delete Success Modal -->
    <div id="userDeleteSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div id="userDeleteSuccessModalContent" class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-95 opacity-0">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Success</h3>
            <p class="mb-6 text-gray-700">User deleted successfully.</p>
            <div class="flex justify-end">
                <button type="button" onclick="closeUserDeleteSuccessModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">OK</button>
            </div>
        </div>
    </div>

    <script>
        let currentSection = 'dashboard';
        let cars = [];
        let bookings = [];
        let users = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadCars();
            loadBookings();
            loadUsers();
            loadBrands();

            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (carIdToDelete !== null) {
                    fetch('../includes/car_functions.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=delete_car&id=${carIdToDelete}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        closeDeleteModal();
                        if (data.success) {
                            loadCars();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            });
        });

        function showSection(section, event) {
            document.querySelectorAll('.section').forEach(el => el.classList.add('hidden'));
            
            document.getElementById(section).classList.remove('hidden');
            
            document.querySelectorAll('nav a').forEach(el => {
                el.classList.remove('text-blue-600', 'bg-blue-50');
                el.classList.add('text-gray-700', 'hover:bg-gray-100');
            });
            
            event.currentTarget.classList.remove('text-gray-700', 'hover:bg-gray-100');
            event.currentTarget.classList.add('text-blue-600', 'bg-blue-50');
            
            currentSection = section;
        }

        function loadDashboardData() {
            fetch('../includes/booking_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_recent_bookings'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof data.active_count !== "undefined") {
                        document.getElementById('activeBookings').textContent = data.active_count;
                    } else {
                        document.getElementById('activeBookings').textContent = 0;
                        console.error('active_count missing in response:', data);
                    }
                } else {
                    document.getElementById('activeBookings').textContent = 0;
                    console.error('Failed to fetch active bookings:', data.message);
                }
            })
            .catch(err => {
                document.getElementById('activeBookings').textContent = 0;
                console.error('Network or parsing error:', err);
            });

            fetch('../includes/user_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_user_count'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalUsers').textContent = data.count;
                }
            });
        }

        function loadCars() {
            const filters = {
                type: document.getElementById('filterType').value,
                brand: document.getElementById('filterBrand').value,
                status: document.getElementById('filterStatus').value,
                sort: document.getElementById('filterSort').value
            };

            fetch('../includes/car_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_cars&filters=' + JSON.stringify(filters)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cars = data.data;
                    displayCars(cars);
                }
            });
        }

        function displayCars(cars) {
            const tbody = document.getElementById('carsTableBody');
            tbody.innerHTML = '';

            cars.forEach(car => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img src="${car.image_url || 'https://via.placeholder.com/60x40?text=Car'}" alt="${car.brand} ${car.model}" class="w-12 h-8 object-cover rounded mr-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">${car.brand} ${car.model}</div>
                                <div class="text-sm text-gray-500">${car.year}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">${car.type}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${car.daily_rate}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(car.status)}">${car.status}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="editCar(${car.id})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button onclick="showDeleteModal(${car.id})" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function getStatusColor(status) {
            switch(status) {
                case 'available': return 'bg-green-100 text-green-800';
                case 'unavailable': return 'bg-red-100 text-red-800';
                case 'maintenance': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function showAddCarModal() {
            document.getElementById('modalTitle').textContent = 'Add New Car';
            document.getElementById('carForm').reset();
            document.getElementById('carId').value = '';
            const modal = document.getElementById('carModal');
            const content = document.getElementById('carModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function editCar(id) {
            const car = cars.find(c => c.id == id);
            if (car) {
                document.getElementById('modalTitle').textContent = 'Edit Car';
                document.getElementById('carId').value = car.id;
                document.getElementById('carBrand').value = car.brand;
                document.getElementById('carModel').value = car.model;
                document.getElementById('carYear').value = car.year;
                document.getElementById('carType').value = car.type;
                document.getElementById('carDailyRate').value = car.daily_rate;
                document.getElementById('carStatus').value = car.status;
                document.getElementById('carSeats').value = car.seats;
                document.getElementById('carTransmission').value = car.transmission;
                document.getElementById('carFuelType').value = car.fuel_type;
                document.getElementById('carMileage').value = car.mileage;
                document.getElementById('carImageUrl').value = car.image_url || '';
                document.getElementById('carDescription').value = car.description || '';
                document.getElementById('carFeatures').value = car.features || '';
                const modal = document.getElementById('carModal');
                const content = document.getElementById('carModalContent');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }

        function closeCarModal() {
            const modal = document.getElementById('carModal');
            const content = document.getElementById('carModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('carForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const carId = document.getElementById('carId').value;
            const action = carId ? 'update_car' : 'add_car';

            const carData = {
                brand: document.getElementById('carBrand').value,
                model: document.getElementById('carModel').value,
                year: document.getElementById('carYear').value,
                type: document.getElementById('carType').value,
                daily_rate: document.getElementById('carDailyRate').value,
                status: document.getElementById('carStatus').value,
                seats: document.getElementById('carSeats').value,
                transmission: document.getElementById('carTransmission').value,
                fuel_type: document.getElementById('carFuelType').value.toLowerCase(),
                mileage: document.getElementById('carMileage').value,
                image_url: document.getElementById('carImageUrl').value,
                description: document.getElementById('carDescription').value,
                features: document.getElementById('carFeatures').value
            };

            const payload = new URLSearchParams();
            payload.append('action', action);
            if (carId) payload.append('id', carId);
            payload.append('car_data', JSON.stringify(carData));

            fetch('../includes/car_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: payload.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCarModal();
                    showSuccessModal(data.message);
                    loadCars();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        let carIdToDelete = null;

        function showDeleteModal(carId) {
            carIdToDelete = carId;
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteModal() {
            carIdToDelete = null;
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function loadBrands() {
            fetch('../includes/car_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_brands'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('filterBrand');
                    select.innerHTML = '';
                    const allOption = document.createElement('option');
                    allOption.value = '';
                    allOption.textContent = 'All Brands';
                    select.appendChild(allOption);
                    data.data.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand.brand;
                        option.textContent = brand.brand;
                        select.appendChild(option);
                    });
                }
            });
        }

        function loadBookings() {
            fetch('../includes/booking_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_all_bookings'
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        bookings = data.data;
                        displayBookings(bookings);
                    } else {
                        alert('Error loading bookings: ' + data.message);
                        displayBookings([]);
                    }
                } catch (e) {
                    console.error('Raw response:', text);
                    alert('Invalid JSON response from server');
                    displayBookings([]);
                }
            })
            .catch(err => {
                console.error('Fetch failed:', err);
                alert('Network error fetching bookings');
                displayBookings([]);
            });
        }

        function displayBookings(bookings) {
            const tbody = document.getElementById('bookingsTableBody');
            tbody.innerHTML = '';

            if (!bookings || bookings.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="7" class="px-6 py-4 text-center text-gray-500">No bookings found.</td>`;
                tbody.appendChild(row);
                return;
            }

            bookings.forEach(booking => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${booking.booking_reference}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${booking.customer_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${booking.brand} ${booking.model}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${booking.pickup_date} - ${booking.return_date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${booking.total_amount}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getBookingStatusColor(booking.status)}">${booking.status}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="showConfirmBookingModal(${booking.id})" class="text-green-600 hover:text-green-900 mr-2">Confirm</button>
                        <button onclick="showCancelBookingModal(${booking.id})" class="text-red-600 hover:text-red-900">Cancel</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function getBookingStatusColor(status) {
            switch(status) {
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'confirmed': return 'bg-blue-100 text-blue-800';
                case 'active': return 'bg-green-100 text-green-800';
                case 'completed': return 'bg-gray-100 text-gray-800';
                case 'cancelled': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function loadUsers() {
            fetch('../includes/user_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'get_all_users' })
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        users = data.data;
                        displayUsers(users);
                    } else {
                        console.error('User fetch error:', data.message);
                        alert('Error fetching users: ' + data.message);
                    }
                } catch (e) {
                    console.error('Raw response:', text);
                    alert('Invalid JSON response from server');
                }
            })
            .catch(err => {
                console.error('Fetch failed:', err);
                alert('Network error fetching users');
            });
        }

        function displayUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';

            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.email}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'}">${user.role}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(user.created_at).toLocaleDateString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick="showToggleRoleModal(${user.id}, '${user.role}')" class="text-blue-600 hover:text-blue-900">Toggle Role</button>
                        <button onclick="showDeleteUserModal(${user.id})" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        let userIdToToggle = null;
        let newRoleToSet = null;

        function showToggleRoleModal(userId, currentRole) {
            userIdToToggle = userId;
            newRoleToSet = currentRole === 'admin' ? 'client' : 'admin';
            document.getElementById('toggleRoleModalMessage').textContent =
                `Are you sure you want to change this user's role to ${newRoleToSet}?`;
            const modal = document.getElementById('toggleRoleModal');
            const content = document.getElementById('toggleRoleModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        function closeToggleRoleModal() {
            userIdToToggle = null;
            newRoleToSet = null;
            const modal = document.getElementById('toggleRoleModal');
            const content = document.getElementById('toggleRoleModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        document.getElementById('confirmToggleRoleBtn').addEventListener('click', function() {
            if (userIdToToggle && newRoleToSet) {
                fetch('../includes/user_functions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({ action: 'update_user_role', user_id: userIdToToggle, role: newRoleToSet })
                })
                .then(response => response.json())
                .then(data => {
                    closeToggleRoleModal();
                    loadUsers();
                });
            }
        });

        let userIdToDelete = null;

        function showDeleteUserModal(userId) {
            userIdToDelete = userId;
            const modal = document.getElementById('deleteUserModal');
            const content = document.getElementById('deleteUserModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteUserModal() {
            userIdToDelete = null;

            const modal = document.getElementById('deleteUserModal');
            const content = document.getElementById('deleteUserModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('confirmDeleteUserBtn').addEventListener('click', function() {
            if (!userIdToDelete) return;
            fetch('../includes/user_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'delete_user', user_id: userIdToDelete })
            })
            .then(response => response.json())
            .then(data => {
                closeDeleteUserModal();
                if (data.success) {
                    showUserDeleteSuccessModal();
                    loadUsers();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        function showUserDeleteSuccessModal() {
            const modal = document.getElementById('userDeleteSuccessModal');
            const content = document.getElementById('userDeleteSuccessModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeUserDeleteSuccessModal() {
            const modal = document.getElementById('userDeleteSuccessModal');
            const content = document.getElementById('userDeleteSuccessModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('logoutBtn').addEventListener('click', function() {
            showLogoutModal();
        });

        function showLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const content = document.getElementById('logoutModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const content = document.getElementById('logoutModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
            fetch('../includes/auth.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=logout'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = './admin_login.php';
                }
            });
        });

        let bookingIdToActOn = null;

        function showConfirmBookingModal(bookingId) {
            bookingIdToActOn = bookingId;
            const modal = document.getElementById('confirmBookingModal');
            const content = document.getElementById('confirmBookingModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeConfirmBookingModal() {
            bookingIdToActOn = null;

            const modal = document.getElementById('confirmBookingModal');
            const content = document.getElementById('confirmBookingModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function showCancelBookingModal(bookingId) {
            bookingIdToActOn = bookingId;

            const modal = document.getElementById('cancelBookingModal');
            const content = document.getElementById('cancelBookingModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeCancelBookingModal() {
            bookingIdToActOn = null;

            const modal = document.getElementById('cancelBookingModal');
            const content = document.getElementById('cancelBookingModalContent');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('confirmBookingBtn').addEventListener('click', function() {
            if (bookingIdToActOn !== null) {
                updateBookingStatus(bookingIdToActOn, 'confirmed', closeConfirmBookingModal);
            }
        });

        document.getElementById('cancelBookingBtn').addEventListener('click', function() {
            if (bookingIdToActOn !== null) {
                updateBookingStatus(bookingIdToActOn, 'cancelled', closeCancelBookingModal);
            }
        });

        function updateBookingStatus(bookingId, status, callback) {
            fetch('../includes/booking_functions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'update_booking_status', booking_id: bookingId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback();
                if (data.success) {
                    loadBookings();
                    loadDashboardData(); 
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function showSuccessModal(message) {
            document.getElementById('successModalMessage').textContent = message || 'Car saved successfully!';
            const modal = document.getElementById('successModal');
            const content = document.getElementById('successModalContent');
            modal.classList.remove('hidden');

            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            const content = document.getElementById('successModalContent');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); 
        }

        // Add admin filter form submit event
        document.getElementById('adminFilterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            loadCars();
        });
        document.getElementById('clearAdminFilters').addEventListener('click', function() {
            document.getElementById('filterType').value = '';
            document.getElementById('filterBrand').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterSort').value = 'created_at DESC';
            loadCars();
        });
    </script>
</body>
</html>