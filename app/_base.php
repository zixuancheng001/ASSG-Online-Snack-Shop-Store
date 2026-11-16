<?php

// ============================================================================
// 1. PHP Setups & Sessions
// ============================================================================
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// Global variables used across the system
$_err = []; // Stores validation errors
$_user = $_SESSION['user'] ?? null; // Global user object

// ============================================================================
// 2. Database Connection
// ============================================================================
$conn = new PDO("mysql:host=localhost;dbname=snackshop", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ // Makes database rows act like objects
]);

// Backward-compatible alias for older pages
$db = $conn;

// ============================================================================
// 3. General Request Helpers
// ============================================================================
function is_get()  { return $_SERVER['REQUEST_METHOD'] == 'GET'; }
function is_post() { return $_SERVER['REQUEST_METHOD'] == 'POST'; }

function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = 'index.php') {
    header("Location: $url");
    exit();
}

// Flash messages (Set or Get temporary data)
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// ============================================================================
// 4. File Upload & Security
// ============================================================================
function get_file($key) {
    $f = $_FILES[$key] ?? null;
    return ($f && $f['error'] == 0) ? (object)$f : null;
}

function save_photo($f, $folder) {
    $photo = uniqid() . '.jpg';
    if (move_uploaded_file($f->tmp_name, "$folder/$photo")) {
        return $photo;
    }
    return 'default_image.jpg';
}

function login($user, $url = 'index.php') {
    $_SESSION['user'] = $user;
    redirect($url);
}

function logout($url = 'login.php') {
    unset($_SESSION['user']);
    redirect($url);
}

function get_user($id) {
    global $conn;
    $stm = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
    $stm->execute([$id]);
    return $stm->fetch();
}

function get_product($id) {
    global $conn;
    $stm = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
    $stm->execute([$id]);
    return $stm->fetch();
}

function upload_photo($key, $folder = 'images') {
    $f = get_file($key);
    return $f ? save_photo($f, $folder) : null;
}

// ============================================================================
// 5. HTML & Validation Helpers
// ============================================================================
function encode($value) {
    return htmlentities($value, ENT_QUOTES, 'UTF-8');
}

function html_file($key, $accept = '', $attr = '') {

    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";

}

function err($key) {
    global $_err;
    if (isset($_err[$key])) {
        echo "<span class='err'>{$_err[$key]}</span>";
    }
}

function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? post($key) ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_password($key, $attr = '') {
    echo "<input type='password' id='$key' name='$key' $attr>";
}

function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL);
}

function is_unique($value, $table, $field) {
    global $conn;
    $stm = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

function check_login_attempts($email) {
    global $conn;
    $stm = $conn->prepare("SELECT login_attempts, attempt_time FROM users WHERE email = ?");
    $stm->execute([$email]);
    $user = $stm->fetch();

    if ($user && $user->login_attempts >= 3) {
        $last_attempt = strtotime($user->attempt_time);
        $timeout = 10 * 60; // 10 minutes lockout

        if ((time() - $last_attempt) < $timeout) {
            $remaining = ceil(($timeout - (time() - $last_attempt)) / 60);
            return "Account locked. Try again in $remaining minutes.";
        } else {
            // Time is up, reset attempts
            $conn->prepare("UPDATE users SET login_attempts = 0 WHERE email = ?")->execute([$email]);
        }
    }
    return true;
}

function update_user_status($id, $status) {
    global $conn;
    // status can be 'active' or 'blocked'
    $stm = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stm->execute([$status, $id]);
}

// Update your auth() function slightly to check status:
function auth(...$roles) {
    global $_user;
    if (!$_user) redirect('login.php');
    if ($_user->status == 'blocked') {
        logout('login.php'); // Kick them out if admin blocked them mid-session
    }
    if ($roles && !in_array($_user->role, $roles)) redirect('index.php');
}

function remember_me($email) {
    // Save email in a cookie for 30 days
    setcookie("remember_me", $email, time() + (30 * 24 * 60 * 60), "/");
}

function get_remember_me() {
    return $_COOKIE['remember_me'] ?? '';
}