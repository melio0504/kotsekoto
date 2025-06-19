<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KotseKoTo - Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <?php include 'components/nav.php'; ?>
    <?php require_once 'includes/flash.php'; display_flash(); ?>
    <section class="bg-blue-700 text-white py-20">
        <div class="container mx-auto text-center">
            <h1 class="text-4xl font-bold mb-4">Click, Renta, Sakay!</h1>
            <p class="text-xl mb-8">Find the perfect car for your journey</p>
            <a href="pages/browse.php" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100">Browse Cars</a>
        </div>
    </section>
    <section class="py-16 px-5 container mx-auto">
        <h2 class="text-3xl font-bold text-center mb-12">Car Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/sedan.webp" alt="Sedan" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Sedan</h3>
                    <p class="text-gray-600">Comfortable and fuel-efficient for city driving.</p>
                    <a href="pages/browse.php?type=sedan" class="mt-4 inline-block text-blue-600 hover:underline">View Sedans</a>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/suv.webp" alt="SUV" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">SUV</h3>
                    <p class="text-gray-600">Spacious and powerful for family trips.</p>
                    <a href="pages/browse.php?type=suv" class="mt-4 inline-block text-blue-600 hover:underline">View SUVs</a>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/van.webp" alt="Van" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Van</h3>
                    <p class="text-gray-600">Perfect for group outings and events.</p>
                    <a href="pages/browse.php?type=van" class="mt-4 inline-block text-blue-600 hover:underline">View Vans</a>
                </div>
            </div>
        </div>
    </section>
    <?php include 'components/footer.php'; ?>
</body>
</html>