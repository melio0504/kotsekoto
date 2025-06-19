<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function display_flash() {
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $msg) {
            $color = $type === 'success' ? 'green' : ($type === 'error' ? 'red' : 'blue');
            echo '<div class="container mx-auto mt-4">';
            echo '<div class="p-4 rounded bg-' . $color . '-100 text-' . $color . '-800 mb-4">' . htmlspecialchars($msg) . '</div>';
            echo '</div>';
        }
        unset($_SESSION['flash']);
    }
}
?> 