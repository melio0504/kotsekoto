<?php
require_once '../includes/config.php';
require_once '../includes/car_functions.php';
require_once '../includes/flash.php';

$filters = [];
if (isset($_GET['type'])) $filters['type'] = $_GET['type'];
if (isset($_GET['brand'])) $filters['brand'] = $_GET['brand'];
if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];

$cars = getCars($filters);
$brands = getBrands();
$car_types = getCarTypes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <?php include '../components/nav.php'; ?>
    <?php display_flash(); ?>
    <section class="container mx-auto">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">Filter Cars</h2>
            <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Car Type</label>
                    <select name="type" class="w-full p-2 border rounded">
                        <option value="">All Types</option>
                        <?php foreach ($car_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['type']); ?>" 
                                    <?php echo (isset($_GET['type']) && $_GET['type'] === $type['type']) ? 'selected' : ''; ?>>
                                <?php 
                                    $displayType = $type['type'] === 'suv' ? 'SUV' : ucfirst($type['type']); 
                                    echo htmlspecialchars($displayType); 
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Brand</label>
                    <select name="brand" class="w-full p-2 border rounded">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand['brand']); ?>"
                                    <?php echo (isset($_GET['brand']) && $_GET['brand'] === $brand['brand']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Apply Filters</button>
                    <button type="button" onclick="clearFilters()" class="ml-2 bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Clear</button>
                </div>
            </form>
        </div>
    </section>
    <section class="container mx-auto py-8 px-5">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Available Cars (<?php echo count($cars); ?>)</h2>
            <div>
                <label class="mr-2">Sort by:</label>
                <select id="sortSelect" class="p-2 border rounded" onchange="sortCars(this.value)">
                    <option value="created_at DESC">Newest First</option>
                    <option value="daily_rate ASC">Price (Low to High)</option>
                    <option value="daily_rate DESC">Price (High to Low)</option>
                    <option value="brand ASC">Brand (A-Z)</option>
                    <option value="brand DESC">Brand (Z-A)</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="carsGrid">
            <?php if (empty($cars)): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No cars found matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <img src="<?php echo htmlspecialchars($car['image_url'] ?: 'https://via.placeholder.com/600x400?text=' . urlencode($car['brand'] . '+' . $car['model'])); ?>" 
                             alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" 
                             class="w-full h-48 object-cover">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                                <span class="px-2 py-1 rounded text-sm <?php echo getStatusBadgeClass($car['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($car['status'])); ?>
                                </span>
                            </div>
                            <p class="text-gray-600 mt-1"><?php 
                                $displayType = $car['type'] === 'suv' ? 'SUV' : ucfirst($car['type']); 
                                echo htmlspecialchars($displayType); 
                            ?></p>
                            <div class="mt-4">
                                <span class="text-2xl font-bold">₱<?php echo number_format($car['daily_rate']); ?></span>
                                <span class="text-gray-600">/day</span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                <div><span class="font-semibold">Seats:</span> <?php echo htmlspecialchars($car['seats']); ?></div>
                                <div><span class="font-semibold">Transmission:</span> <?php echo ucfirst(htmlspecialchars($car['transmission'])); ?></div>
                                <div><span class="font-semibold">Fuel:</span> <?php echo ucfirst(htmlspecialchars($car['fuel_type'])); ?></div>
                                <div><span class="font-semibold">Year:</span> <?php echo htmlspecialchars($car['year']); ?></div>
                            </div>
                            <a href="car-details.php?id=<?php echo $car['id']; ?>" 
                               class="mt-6 block w-full bg-blue-600 text-white text-center py-2 rounded hover:bg-blue-700">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php include '../components/footer.php'; ?>
    <script>
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            const sortValue = document.getElementById('sortSelect').value;
            if (sortValue) {
                params.append('sort', sortValue);
            }
            
            window.location.href = 'browse.php?' + params.toString();
        });

        function clearFilters() {
            window.location.href = 'browse.php';
        }

        function sortCars(sortValue) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', sortValue);
            window.location.href = currentUrl.toString();
        }

        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort') || 'created_at DESC';
        document.getElementById('sortSelect').value = currentSort;
    </script>
</body>
</html>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'available':
            return 'bg-green-100 text-green-800';
        case 'unavailable':
            return 'bg-red-100 text-red-800';
        case 'maintenance':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?> 