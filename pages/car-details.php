<?php
require_once '../includes/config.php';
require_once '../includes/car_functions.php';
require_once '../includes/auth.php';
require_once '../includes/booking_functions.php';
require_once '../includes/flash.php';

// Get car ID from query string
$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$car = getCarById($car_id);

// Handle booking form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking' && isLoggedIn()) {
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $pickup_location = $_POST['pickup_location'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $user_id = $_SESSION['user_id'];
    $car_id = intval($_POST['car_id'] ?? 0);

    $car = getCarById($car_id);
    if (!$car) {
        set_flash('error', 'Invalid car selected.');
        header('Location: browse.php');
        exit;
    }

    // Calculate total amount securely on server
    $total_amount = calculateBookingTotal($car_id, $pickup_date, $return_date);

    if (!$pickup_date || !$return_date || !$pickup_location || !$payment_method || $total_amount <= 0) {
        set_flash('error', 'Please fill in all fields and select valid dates.');
        header('Location: car-details.php?id=' . $car_id);
        exit;
    }

    if (!checkCarAvailability($car_id, $pickup_date, $return_date)) {
        set_flash('error', 'Sorry, this car is not available for the selected dates.');
        header('Location: car-details.php?id=' . $car_id);
        exit;
    }

    $data = [
        'user_id' => $user_id,
        'car_id' => $car_id,
        'pickup_date' => $pickup_date,
        'return_date' => $return_date,
        'pickup_location' => $pickup_location,
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'status' => 'completed', 
        'payment_status' => 'paid'
    ];
    $booking_id = createBooking($data);
    if ($booking_id) {
        $_SESSION['last_booking_id'] = $booking_id;
        set_flash('success', 'Booking successful! Your reservation has been made.');
        header('Location: ./browse.php');
        exit;
    } else {
        set_flash('error', 'Failed to create booking. Please try again.');
        header('Location: ./car-details.php?id=' . $car_id);
        exit;
    }
}

if (!$car) {
    echo '<!DOCTYPE html><html><head><title>Car Not Found</title></head><body><h1>Car Not Found</h1><p>The car you are looking for does not exist.</p></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?> - Car Details | KotseKoTo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <?php include '../components/nav.php'; ?>
    <?php require_once '../includes/flash.php'; display_flash(); ?>
    <div class="container mx-auto py-4 px-5">
        <nav class="text-sm">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="../index.php" class="text-blue-600 hover:underline">Home</a>
                    <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                </li>
                <li class="flex items-center">
                    <a href="./browse.php" class="text-blue-600 hover:underline">Browse Cars</a>
                    <i class="fas fa-chevron-right mx-2 text-gray-400"></i>
                </li>
                <li class="text-gray-500">Car Details</li>
            </ol>
        </nav>
    </div>
    <section class="container mx-auto py-8 px-5">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="relative">
                        <img id="mainImage" src="<?php echo htmlspecialchars($car['image_url'] ?: 'https://via.placeholder.com/800x500?text=' . urlencode($car['brand'] . '+' . $car['model'])); ?>" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" class="w-full h-96 object-cover">
                        <div class="absolute top-4 right-4">
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold"><?php echo ucfirst(htmlspecialchars($car['status'])); ?></span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>
                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($car['year']); ?> Model • <?php echo ucfirst(htmlspecialchars($car['type'])); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-blue-600">₱<?php echo number_format($car['daily_rate']); ?></div>
                            <div class="text-gray-600">per day</div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Description</h3>
                        <p class="text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($car['description'])); ?>
                        </p>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Specifications</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Brand & Model:</span>
                                <span class="text-gray-800"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Year:</span>
                                <span class="text-gray-800"><?php echo htmlspecialchars($car['year']); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Type:</span>
                                <span class="text-gray-800"><?php echo ucfirst(htmlspecialchars($car['type'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Seating Capacity:</span>
                                <span class="text-gray-800"><?php echo htmlspecialchars($car['seats']); ?> passengers</span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Transmission:</span>
                                <span class="text-gray-800"><?php echo ucfirst(htmlspecialchars($car['transmission'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Fuel Type:</span>
                                <span class="text-gray-800"><?php echo ucfirst(htmlspecialchars($car['fuel_type'])); ?></span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Air Conditioning:</span>
                                <span class="text-gray-800">Yes</span>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <span class="font-medium text-gray-600">Mileage:</span>
                                <span class="text-gray-800"><?php echo htmlspecialchars($car['mileage']); ?> km</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Features</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <?php foreach (explode(',', $car['features']) as $feature): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <span class="text-gray-700"><?php echo htmlspecialchars(trim($feature)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Rental Terms</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                                    <span>Minimum rental period: 1 day</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                                    <span>Valid driver's license required</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                                    <span>Security deposit: ₱5,000</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                                    <span>Free cancellation up to 24 hours before pickup</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                                    <span>Unlimited mileage included</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <?php if (strtolower($car['status']) === 'unavailable'): ?>
                        <h3 class="text-xl font-bold mb-4 text-red-600">Currently Unavailable</h3>
                        <p class="text-gray-600 mb-4">This car is currently unavailable for rent.</p>
                    <?php else: ?>
                        <?php if (isLoggedIn()): ?>
                            <h3 class="text-xl font-bold mb-4">Reserve This Car</h3>
                            <form id="reservationForm" method="post" class="space-y-4">
                                <input type="hidden" name="action" value="create_booking">
                                <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                                <input type="hidden" id="total_amount" name="total_amount" value="0">
                                <div>
                                    <label for="pickup_date" class="block text-gray-700 font-medium mb-1">Pickup Date</label>
                                    <input type="date" id="pickup_date" name="pickup_date" class="w-full border rounded px-3 py-2" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div>
                                    <label for="return_date" class="block text-gray-700 font-medium mb-1">Return Date</label>
                                    <input type="date" id="return_date" name="return_date" class="w-full border rounded px-3 py-2" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Number of Days</label>
                                    <div id="num_days_display" class="w-full border rounded px-3 py-2 bg-gray-50 text-gray-800">0</div>
                                </div>
                                <div>
                                    <label for="pickup_location" class="block text-gray-700 font-medium mb-1">Pickup Location</label>
                                    <select id="pickup_location" name="pickup_location" class="w-full border rounded px-3 py-2" required>
                                        <option value="">Select Location</option>
                                        <option value="Manila">Manila</option>
                                        <option value="Makati">Makati</option>
                                        <option value="Quezon City">Quezon City</option>
                                        <option value="Taguig">Taguig</option>
                                        <option value="Pasay">Pasay</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="payment_method" class="block text-gray-700 font-medium mb-1">Payment Method</label>
                                    <select id="payment_method" name="payment_method" class="w-full border rounded px-3 py-2" required>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="gcash">GCash</option>
                                        <option value="paymaya">PayMaya</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Total Amount</label>
                                    <div id="total_amount_display" class="w-full border rounded px-3 py-2 bg-gray-50 text-gray-800">₱0</div>
                                </div>
                                <button type="submit" id="reserveNowBtn" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Reserve Now</button>
                            </form>
                            <div id="paymentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                                <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full relative">
                                    <h2 class="text-2xl font-bold mb-4 text-center">Payment</h2>
                                    <p class="mb-2 text-center">Total Amount: <span id="modalTotalAmount" class="font-semibold text-blue-600">₱0</span></p>
                                    <div id="paymentInstructions" class="mb-6 text-sm text-gray-700 bg-gray-50 p-3 rounded"></div>
                                    <button id="payNowBtn" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-200 mb-2">Pay Now</button>
                                    <button id="cancelPaymentBtn" class="w-full bg-gray-300 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-400 transition duration-200">Cancel</button>
                                </div>
                            </div>
                            <div id="creditCardModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                                <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full relative">
                                    <h2 class="text-2xl font-bold mb-4 text-center">Credit Card Details</h2>
                                    <form id="creditCardForm" class="space-y-4">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-1">Card Number</label>
                                            <input type="text" id="cc_number" maxlength="19" class="w-full border rounded px-3 py-2" placeholder="1234 5678 9012 3456" required pattern="[0-9 ]{16,19}">
                                        </div>
                                        <div class="flex space-x-2">
                                            <div class="w-1/2">
                                                <label class="block text-gray-700 font-medium mb-1">Expiry</label>
                                                <input type="text" id="cc_expiry" maxlength="5" class="w-full border rounded px-3 py-2" placeholder="MM/YY" required pattern="(0[1-9]|1[0-2])\/\d{2}">
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-gray-700 font-medium mb-1">CVV</label>
                                                <input type="text" id="cc_cvv" maxlength="4" class="w-full border rounded px-3 py-2" placeholder="123" required pattern="\d{3,4}">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-1">Name on Card</label>
                                            <input type="text" id="cc_name" class="w-full border rounded px-3 py-2" placeholder="Cardholder Name" required>
                                        </div>
                                        <div id="cc_error" class="text-red-600 text-sm hidden"></div>
                                        <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-200">Submit Payment</button>
                                        <button type="button" id="cancelCCBtn" class="w-full bg-gray-300 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-400 transition duration-200 mt-2">Cancel</button>
                                    </form>
                                </div>
                            </div>
                            <div id="paymentSuccessModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                                <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full text-center">
                                    <h2 class="text-2xl font-bold mb-4 text-green-600">Payment Successful!</h2>
                                    <p class="mb-6">Thank you for your payment. Your booking has been confirmed and marked as <span class="font-semibold">Paid</span>.</p>
                                    <button id="closeSuccessModalBtn" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">Close</button>
                                </div>
                            </div>
                        <?php else: ?>
                            <h3 class="text-xl font-bold mb-4">Rent This Car</h3>
                            <p class="text-gray-600 mb-4">Please login or register to book this car.</p>
                            <a href="./login.php" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 mb-2">Login</a>
                            <a href="./register.php" class="block w-full bg-gray-200 text-blue-600 text-center py-3 rounded-lg font-semibold hover:bg-gray-300 transition duration-200">Register</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>
    <script>
    function calculateDays() {
        const pickup = document.getElementById('pickup_date').value;
        const ret = document.getElementById('return_date').value;
        let numDays = 0;
        if (pickup && ret) {
            const pickupDate = new Date(pickup);
            const returnDate = new Date(ret);
            const diff = (returnDate - pickupDate) / (1000 * 60 * 60 * 24);
            numDays = diff >= 0 ? diff + 1 : 0;
        }
        document.getElementById('num_days_display').textContent = numDays;
        updateTotalAmount(numDays);
    }
    function updateTotalAmount(numDays) {
        const dailyRate = <?php echo (float)$car['daily_rate']; ?>;
        const insurancePerDay = 200;
        const total = numDays > 0 ? (dailyRate + insurancePerDay) * numDays : 0;
        document.getElementById('total_amount').value = total;
        let totalDisplay = document.getElementById('total_amount_display');
        if (totalDisplay) totalDisplay.textContent = '₱' + total.toLocaleString();
    }
    document.getElementById('pickup_date').addEventListener('change', function() {
        calculateDays();
        let pickup = document.getElementById('pickup_date').value;
        let returnInput = document.getElementById('return_date');
        if (pickup) {
            returnInput.min = pickup;
            if (returnInput.value < pickup) {
                returnInput.value = pickup;
                calculateDays();
            }
        } else {
            returnInput.min = '<?php echo date('Y-m-d'); ?>';
        }
    });
    document.getElementById('return_date').addEventListener('change', calculateDays);
    calculateDays();

    const reserveBtn = document.getElementById('reserveNowBtn');
    const paymentModal = document.getElementById('paymentModal');
    const payNowBtn = document.getElementById('payNowBtn');
    const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
    const reservationForm = document.getElementById('reservationForm');
    const modalTotalAmount = document.getElementById('modalTotalAmount');
    const paymentInstructions = document.getElementById('paymentInstructions');
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentSuccessModal = document.getElementById('paymentSuccessModal');
    const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');
    const creditCardModal = document.getElementById('creditCardModal');
    const creditCardForm = document.getElementById('creditCardForm');
    const cancelCCBtn = document.getElementById('cancelCCBtn');
    const ccError = document.getElementById('cc_error');

    const paymentInfo = {
        credit_card: `<b>Credit Card:</b><br>Enter your card details on the next page. We accept Visa, MasterCard, and JCB.`,
        gcash: `<b>GCash:</b><br>Send payment to GCash number <span class='font-semibold'>0917-123-4567</span> (KotseKoTo Rentals). Screenshot your payment for reference.`,
        paymaya: `<b>PayMaya:</b><br>Send payment to PayMaya number <span class='font-semibold'>0918-765-4321</span> (KotseKoTo Rentals). Screenshot your payment for reference.`,
        bank_transfer: `<b>Bank Transfer:</b><br>Account Name: KotseKoTo Rentals<br>Account Number: <span class='font-semibold'>1234-5678-90</span><br>Bank: BPI<br>After transfer, keep your transaction receipt.`
    };

    function updatePaymentInstructions() {
        if (paymentInstructions && paymentMethodSelect) {
            const method = paymentMethodSelect.value;
            paymentInstructions.innerHTML = paymentInfo[method] || '';
        }
    }
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updatePaymentInstructions);

        updatePaymentInstructions();
    }

    if (reserveBtn && paymentModal && payNowBtn && cancelPaymentBtn && reservationForm) {
        reserveBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const total = document.getElementById('total_amount').value;
            modalTotalAmount.textContent = '₱' + parseFloat(total).toLocaleString();
            updatePaymentInstructions();
            paymentModal.classList.remove('hidden');
        });
        payNowBtn.addEventListener('click', function() {
            const method = paymentMethodSelect.value;
            if (method === 'credit_card') {
                paymentModal.classList.add('hidden');
                creditCardModal.classList.remove('hidden');
            } else {
                paymentModal.classList.add('hidden');
                paymentSuccessModal.classList.remove('hidden');
            }
        });
        cancelPaymentBtn.addEventListener('click', function() {
            paymentModal.classList.add('hidden');
        });
    }
    if (creditCardForm && creditCardModal) {
        creditCardForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const number = document.getElementById('cc_number').value.replace(/\s+/g, '');
            const expiry = document.getElementById('cc_expiry').value;
            const cvv = document.getElementById('cc_cvv').value;
            const name = document.getElementById('cc_name').value.trim();
            let valid = true;
            ccError.classList.add('hidden');
            if (!/^\d{16,19}$/.test(number)) valid = false;
            if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) valid = false;
            if (!/^\d{3,4}$/.test(cvv)) valid = false;
            if (!name) valid = false;
            if (!valid) {
                ccError.textContent = 'Please enter valid credit card details.';
                ccError.classList.remove('hidden');
                return;
            }
            creditCardModal.classList.add('hidden');
            paymentSuccessModal.classList.remove('hidden');
        });
        if (cancelCCBtn) {
            cancelCCBtn.addEventListener('click', function() {
                creditCardModal.classList.add('hidden');
            });
        }
    }
    if (closeSuccessModalBtn && paymentSuccessModal && reservationForm) {
        closeSuccessModalBtn.addEventListener('click', function() {
            paymentSuccessModal.classList.add('hidden');

            if (lastBookingId) {
                markPaymentCompleted(lastBookingId);
            }
            reservationForm.submit();
        });
    }

    function isReservationFormValid() {
        const pickup = document.getElementById('pickup_date').value;
        const ret = document.getElementById('return_date').value;
        const pickupLoc = document.getElementById('pickup_location').value;
        const paymentMethod = document.getElementById('payment_method').value;
        const numDays = parseInt(document.getElementById('num_days_display').textContent, 10);
        return pickup && ret && pickupLoc && paymentMethod && numDays > 0;
    }

    function updateReserveBtnState() {
        const reserveBtn = document.getElementById('reserveNowBtn');
        if (reserveBtn) {
            reserveBtn.disabled = !isReservationFormValid();
        }
    }
    ['pickup_date', 'return_date', 'pickup_location', 'payment_method'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', updateReserveBtnState);
        }
    });
    document.getElementById('pickup_date').addEventListener('change', updateReserveBtnState);
    document.getElementById('return_date').addEventListener('change', updateReserveBtnState);

    updateReserveBtnState();

    let lastBookingId = <?php echo isset($_SESSION['last_booking_id']) ? intval($_SESSION['last_booking_id']) : 'null'; ?>;

    function markPaymentCompleted(bookingId) {
        if (!bookingId) return;
        fetch('includes/booking_functions.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update_payment_status&booking_id=${bookingId}&status=completed`
        })
        .then(res => res.json())
        .then(data => {
        });
    }
    </script>
</body>
</html> 