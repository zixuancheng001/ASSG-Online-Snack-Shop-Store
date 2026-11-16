<?php
include_once "_base.php";
auth(); // Only logged-in users can update

if (is_post()) {
    $name  = post('name');
    $email = post('email');
    $file  = get_file('photo');

    // 1. Validation
    if (!$name) $_err['name'] = 'Required';
    if (!is_email($email)) $_err['email'] = 'Invalid email';
    
    // Check if email is taken by someone else
    if ($email != $_user->email && !is_unique($email, 'users', 'email')) {
        $_err['email'] = 'Email already in use';
    }

    if (!$_err) {
        // 2. Handle Photo Upload
        if ($file) {
            $photo = save_photo($file, "images"); // Using our base.php helper
            
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, photo = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $photo, $_user->user_id]);
        } else {
            // Update without changing photo
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $_user->user_id]);
        }

        // 3. CRITICAL: Refresh the Session
        // We need to fetch the new data so the topbar updates immediately
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_user->user_id]);
        $_SESSION['user'] = $stmt->fetch();

        temp('info', 'Profile updated successfully!');
        redirect('profile.php');
    }
}

// If there are errors, redirect back to profile to show them
redirect('profile.php');
