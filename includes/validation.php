<?php

function sanitize_string($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_password($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

function validate_phone($phone) {
    return preg_match('/^[0-9\s\-\(\)\+]{7,20}$/', $phone);
}

function sanitize_int($val) {
    return filter_var($val, FILTER_SANITIZE_NUMBER_INT);
}

function sanitize_email($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function validate_required($value) {
    return isset($value) && trim($value) !== '';
}