<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                    <h2 class="text-3xl font-bold text-gray-900">Welcome to KotseKoTo!</h2>
                    <p class="mt-2 text-gray-600">Sign in to your account</p>
                </div>
                <form id="loginForm" class="space-y-6" action="includes/auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter your email">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter your password">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                        </div>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
                    </div>
                    <div id="loginMessage" class="hidden"></div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span id="loginButtonText">Sign In</span>
                    </button>
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account? 
                            <a href="./register.php" class="font-medium text-blue-600 hover:text-blue-500">Sign up</a>
                        </p>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 rounded-lg shadow p-6 mt-6 text-center">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Are you an admin?</h3>
                <p class="text-sm text-gray-600 mb-4">Login to the admin dashboard here.</p>
                <a href="./admin_login.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition">Admin Login</a>
            </div>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const emailInput = document.getElementById('email');
            const loginMessage = document.getElementById('loginMessage');
            if (emailInput.value.trim().toLowerCase() === 'admin@kotsekoto.com') {
                e.preventDefault();
                loginMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                loginMessage.textContent = 'Admin login is not allowed here. Please use the Admin Login button below.';
                loginMessage.classList.remove('hidden');
                return;
            }
            e.preventDefault();
            
            const formData = new FormData(this);
            const loginButton = document.getElementById('loginButtonText');
            
            loginButton.textContent = 'Signing In...';
            loginMessage.classList.add('hidden');
            
            fetch('../includes/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loginMessage.className = 'p-4 rounded-md bg-green-100 text-green-700';
                    loginMessage.textContent = 'Login successful! Redirecting...';
                    loginMessage.classList.remove('hidden');
                    
                    setTimeout(() => {
                        if (data.role === 'admin') {
                            window.location.href = './dashboard.php';
                        } else {
                            window.location.href = '../index.php';
                        }
                    }, 1500);
                } else {
                    loginMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                    loginMessage.textContent = data.message || 'Login failed. Please try again.';
                    loginMessage.classList.remove('hidden');
                }
            })
            .catch(error => {
                loginMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                loginMessage.textContent = 'An error occurred. Please try again.';
                loginMessage.classList.remove('hidden');
            })
            .finally(() => {
                loginButton.textContent = 'Sign In';
            });
        });
    </script>
</body>
</html> 