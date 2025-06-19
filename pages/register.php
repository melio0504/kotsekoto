<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <?php include '../components/nav.php'; ?>
    <?php require_once '../includes/flash.php'; display_flash(); ?>
    <section class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Create Account</h2>
                    <p class="mt-2 text-gray-600">Join KotseKoTo today</p>
                </div>
                <form id="registerForm" class="space-y-6" action="../includes/auth.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" id="firstName" name="firstName" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="John">
                        </div>
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Doe">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="john.doe@example.com">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="+63 912 345 6789">
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea id="address" name="address" rows="3" required 
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter your complete address"></textarea>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Create a password">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" name="confirmPassword" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Confirm your password">
                            <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms and Conditions</a> and 
                            <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                        </label>
                    </div>
                    <div id="registerMessage" class="hidden"></div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span id="registerButtonText">Create Account</span>
                    </button>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Already have an account? 
                            <a href="./login.php" class="font-medium text-blue-600 hover:text-blue-500">Sign in</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>

    <script>
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('togglePassword').addEventListener('click', () => {
            togglePasswordVisibility('password', 'togglePassword');
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', () => {
            togglePasswordVisibility('confirmPassword', 'toggleConfirmPassword');
        });

        function validatePassword(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            
            return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers;
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }
            
            const phoneRegex = /^(\+63|0)9\d{9}$/;
            if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
                alert('Please enter a valid Philippine phone number.');
                return false;
            }
            
            if (!validatePassword(password)) {
                alert('Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.');
                return false;
            }
            
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return false;
            }
            
            return true;
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm()) {
                return;
            }
            
            const formData = new FormData(this);
            const registerButton = document.getElementById('registerButtonText');
            const registerMessage = document.getElementById('registerMessage');
            
            registerButton.textContent = 'Creating Account...';
            registerMessage.classList.add('hidden');
            
            fetch('../includes/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    registerMessage.className = 'p-4 rounded-md bg-green-100 text-green-700';
                    registerMessage.textContent = 'Account created successfully! Redirecting to login...';
                    registerMessage.classList.remove('hidden');
                    
                    setTimeout(() => {
                        window.location.href = './login.php';
                    }, 2000);
                } else {
                    registerMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                    registerMessage.textContent = data.message || 'Registration failed. Please try again.';
                    registerMessage.classList.remove('hidden');
                }
            })
            .catch(error => {
                registerMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                registerMessage.textContent = 'An error occurred. Please try again.';
                registerMessage.classList.remove('hidden');
            })
            .finally(() => {
                registerButton.textContent = 'Create Account';
            });
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = validatePassword(password);
            
            if (password.length > 0) {
                if (strength) {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-green-300');
                } else {
                    this.classList.remove('border-green-300');
                    this.classList.add('border-red-300');
                }
            } else {
                this.classList.remove('border-red-300', 'border-green-300');
            }
        });
    </script>
</body>
</html> 