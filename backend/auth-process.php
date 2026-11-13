<?php
/**
 * CINEMA BOOKING - Authentication Processing Backend
 */

require_once __DIR__ . '/../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security token expired. Please try submitting again.');
        redirect(base_url('login.php'));
    }
}

$pdo = getDBConnection();

switch ($action) {
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            set_flash('danger', 'Please enter both email address and password.');
            redirect(base_url('login.php'));
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'banned') {
                set_flash('danger', 'Your account has been suspended. Please contact customer support.');
                redirect(base_url('login.php'));
            }

            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);

            // Set user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            set_flash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');

            $redirectUrl = $_SESSION['redirect_after_login'] ?? base_url('index.php');
            unset($_SESSION['redirect_after_login']);
            redirect($redirectUrl);
        } else {
            set_flash('danger', 'Invalid email address or password.');
            redirect(base_url('login.php'));
        }
        break;

    case 'register':
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($fullName) || empty($email) || empty($password)) {
            set_flash('danger', 'Please fill in all required fields.');
            redirect(base_url('register.php'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('danger', 'Please provide a valid email address.');
            redirect(base_url('register.php'));
        }

        if (strlen($password) < 6) {
            set_flash('danger', 'Password must be at least 6 characters long.');
            redirect(base_url('register.php'));
        }

        if ($password !== $confirmPassword) {
            set_flash('danger', 'Passwords do not match.');
            redirect(base_url('register.php'));
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            set_flash('danger', 'An account with this email address already exists. Please sign in.');
            redirect(base_url('login.php'));
        }

        // Hash password securely with PASSWORD_DEFAULT
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
        $success = $stmt->execute([$fullName, $email, $phone, $hashedPassword]);

        if ($success) {
            $newUserId = $pdo->lastInsertId();
            // Regenerate session ID for newly created authenticated user
            session_regenerate_id(true);

            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'user';

            set_flash('success', 'Registration successful! Welcome to CINEMA BOOKING.');
            $redirectUrl = $_SESSION['redirect_after_login'] ?? base_url('index.php');
            unset($_SESSION['redirect_after_login']);
            redirect($redirectUrl);
        } else {
            set_flash('danger', 'Registration failed. Please try again.');
            redirect(base_url('register.php'));
        }
        break;

    case 'logout':
        logout_user();
        set_flash('info', 'You have been signed out successfully.');
        redirect(base_url('index.php'));
        break;

    default:
        redirect(base_url('index.php'));
}

