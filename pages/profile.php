<?php
require_once '../includes/auth.php';
require_once '../includes/flash.php';
require_once '../includes/user_functions.php';
require_once '../includes/validation.php';

requireLogin();
$user = getCurrentUser();

$user_data = getUserById($user['id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - KotseKoTo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
</head>
<body class="bg-gray-100">
    <?php include '../components/nav.php'; ?>
    <?php display_flash(); ?>
    <div class="container mx-auto py-10 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6">My Profile</h2>
            <form id="profileForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700">Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label class="block text-gray-700">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" class="w-full p-2 border rounded bg-gray-100" disabled>
                </div>
                <div>
                    <label class="block text-gray-700">Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block text-gray-700">Address</label>
                    <textarea name="address" class="w-full p-2 border rounded"><?php echo htmlspecialchars($user_data['address']); ?></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update Profile</button>
            </form>
            <hr class="my-8">
            <h3 class="text-xl font-bold mb-4">Change Password</h3>
            <form id="passwordForm" class="space-y-4">
                <div>
                    <label class="block text-gray-700">Current Password</label>
                    <input type="password" name="current_password" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label class="block text-gray-700">New Password</label>
                    <input type="password" name="new_password" class="w-full p-2 border rounded" required>
                </div>
                <div>
                    <label class="block text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="w-full p-2 border rounded" required>
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Change Password</button>
            </form>
        </div>
    </div>
    <style>
        .modal-transition {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .modal-hidden {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
        }
        .modal-visible {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
        }
    </style>
    <div id="confirmationModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
      <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full text-center modal-transition modal-hidden">
        <h4 class="text-lg font-bold mb-4" id="confirmationModalTitle">Confirm Action</h4>
        <p class="mb-6" id="confirmationModalMessage">Are you sure you want to proceed?</p>
        <div class="flex justify-center space-x-2 mt-4">
          <button id="cancelConfirmBtn" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button id="proceedConfirmBtn" class="px-4 py-2 bg-blue-600 text-white rounded">Yes, Proceed</button>
        </div>
      </div>
    </div>
    <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
      <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full text-center modal-transition modal-hidden">
        <h4 class="text-lg font-bold mb-4" id="successModalTitle">Success</h4>
        <p class="mb-6" id="successModalMessage">Your action was successful!</p>
        <button id="closeSuccessBtn" class="px-4 py-2 bg-green-600 text-white rounded">OK</button>
      </div>
    </div>
    <script>
        function showModal(modalId, title, message) {
        const modal = document.getElementById(modalId);
        const modalBox = modal.querySelector('.modal-transition');
        if (title) modal.querySelector('h4').textContent = title;
        if (message) modal.querySelector('p').textContent = message;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('modal-hidden');
            modalBox.classList.add('modal-visible');
        }, 10);
        }
        function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalBox = modal.querySelector('.modal-transition');

        modalBox.classList.remove('modal-visible');
        modalBox.classList.add('modal-hidden');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200); 
        }

        let profileFormData = null;

        document.getElementById('profileForm').onsubmit = function(e) {
            e.preventDefault();
            profileFormData = new FormData(this);
            showModal('confirmationModal', 'Update Profile', 'Are you sure you want to update your profile information?');
            document.getElementById('proceedConfirmBtn').onclick = function() {
                hideModal('confirmationModal');
                profileFormData.append('action', 'update_profile');
                fetch('../includes/user_functions.php', {
                    method: 'POST',
                    body: profileFormData
                })
                .then(res => res.json())
                .then(data => {
                    showModal('successModal', 'Profile Updated', data.message || 'Profile updated successfully!');
                    document.getElementById('closeSuccessBtn').onclick = function() {
                        hideModal('successModal');
                        if (data.success) location.reload();
                    };
                });
            };
            document.getElementById('cancelConfirmBtn').onclick = function() {
                hideModal('confirmationModal');
            };
        };

        let passwordFormData = null;

        document.getElementById('passwordForm').onsubmit = function(e) {
            e.preventDefault();
            passwordFormData = new FormData(this);
            if (passwordFormData.get('new_password') !== passwordFormData.get('confirm_password')) {
                showModal('successModal', 'Error', 'New passwords do not match.');
                document.getElementById('closeSuccessBtn').onclick = function() {
                    hideModal('successModal');
                };
                return;
            }
            showModal('confirmationModal', 'Change Password', 'Are you sure you want to change your password?');
            document.getElementById('proceedConfirmBtn').onclick = function() {
                hideModal('confirmationModal');
                passwordFormData.append('action', 'change_password');
                fetch('../includes/user_functions.php', {
                    method: 'POST',
                    body: passwordFormData
                })
                .then(res => res.json())
                .then(data => {
                    showModal('successModal', data.success ? 'Password Changed' : 'Error', data.message || (data.success ? 'Password changed successfully!' : 'Failed to change password.'));
                    document.getElementById('closeSuccessBtn').onclick = function() {
                        hideModal('successModal');
                        if (data.success) location.reload();
                    };
                });
            };
            document.getElementById('cancelConfirmBtn').onclick = function() {
                hideModal('confirmationModal');
            };
        };
    </script>
    <?php include '../components/footer.php'; ?>
</body>
</html> 