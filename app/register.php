<?php
include "_base.php";
include "_head.php";

// Initialize variables for the form
$name = $email = $role = '';

if (is_post()) {
    $name     = post('name');
    $email    = post('email');
    $password = post('password');
    $confirm  = post('confirm');
    $role     = post('role');
    $f        = get_file('photo'); 

    // --- Validation ---
    if (!$name)  $_err['name'] = 'Required';
    
    if (!$email) {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if (!is_unique($email, 'users', 'email')) {
        $_err['email'] = 'Email already registered';
    }

    if (!$password) {
        $_err['password'] = 'Required';
    } else if (strlen($password) < 5) {
        $_err['password'] = 'Minimum 5 characters';
    }

    if ($confirm != $password) {
        $_err['confirm'] = 'Passwords do not match';
    }

    // --- Process Registration ---
    if (!$_err) {
        // Save photo to images folder
        $photo = $f ? save_photo($f, 'images') : 'default_image.jpg';

        // Use $conn (from _base.php)
        $stm = $conn->prepare('INSERT INTO users (email, password, name, photo, role) VALUES (?, ?, ?, ?, ?)');
        
        // Hash password and execute
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stm->execute([$email, $hash, $name, $photo, $role]);

        temp('info', 'Registration successful!');
        redirect('login.php');
    }
}
?>

<main>
    <div class="card">
        <h1>Register</h1>

        <form method="post" class="form" enctype="multipart/form-data">
            <div class="form-row">
                <label for="name">Name:</label>
                <div class="field-group">
                    <?= html_text('name', 'maxlength="100"') ?>
                    <?= err('name') ?>
                </div>
            </div>

            <div class="form-row">
                <label for="email">Email:</label>
                <div class="field-group">
                    <?= html_text('email', 'maxlength="100"') ?>
                    <?= err('email') ?>
                </div>
            </div>

            <div class="form-row">
                <label for="password">Password:</label>
                <div class="field-group">
                    <?= html_password('password', 'maxlength="100"') ?>
                    <?= err('password') ?>
                </div>
            </div>

            <div class="form-row">
                <label for="confirm">Confirm:</label>
                <div class="field-group">
                    <?= html_password('confirm', 'maxlength="100"') ?>
                    <?= err('confirm') ?>
                </div>
            </div>

            <div class="form-row">
                <label>Role:</label>
                <div class="field-group">
                    <select name="role">
                        <option value="Member" <?= $role == 'Member' ? 'selected' : '' ?>>Member</option>
                        <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div class="form-row photo-row">
                <label for="photo">Photo:</label>
                <div class="field-group">
                    <label class="upload" tabindex="0">
                        <?= html_file('photo', 'image/*', 'id="photo" hidden') ?>
                        <img src="images/default_image.jpg" id="preview">
                    </label>
                    <?= err('photo') ?>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-blue">Register</button>
            </div>
        </form>
    </div>
</main>

<?php include "_foot.php"; ?>