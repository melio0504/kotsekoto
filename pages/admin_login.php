<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <?php include '../components/nav.php'; ?>
    <section class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Admin Login</h2>
                    <p class="mt-2 text-gray-600">Sign in to the admin dashboard</p>
                </div>
                <form id="adminLoginForm" class="space-y-6" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Admin Username</label>
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
                            <button type="button" id="toggleAdminPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400"></i>
                            </button>
                        </div>
                    </div>
                    <div id="adminLoginMessage" class="hidden"></div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span id="adminLoginButtonText">Sign In</span>
                    </button>
                    <div class="text-center mt-4">
                        <a href="./login.php" class="text-sm text-blue-600 hover:text-blue-500">Back to User Login</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <?php include '../components/footer.php'; ?>
    <script>
        document.getElementById('toggleAdminPassword').addEventListener('click', function() {
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

        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginButton = document.getElementById('adminLoginButtonText');
            const loginMessage = document.getElementById('adminLoginMessage');
            loginButton.textContent = 'Signing In...';
            loginMessage.classList.add('hidden');
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            fetch('../includes/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.role === 'admin') {
                    loginMessage.className = 'p-4 rounded-md bg-green-100 text-green-700';
                    loginMessage.textContent = 'Login successful! Redirecting to dashboard...';
                    loginMessage.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = './dashboard.php';
                    }, 1200);
                } else if (data.success) {
                    loginMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                    loginMessage.textContent = 'You are not an admin.';
                    loginMessage.classList.remove('hidden');
                } else {
                    loginMessage.className = 'p-4 rounded-md bg-red-100 text-red-700';
                    loginMessage.textContent = data.message || 'Invalid admin credentials.';
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