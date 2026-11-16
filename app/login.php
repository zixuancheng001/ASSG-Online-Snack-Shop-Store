<?php 
include_once "_base.php"; 

if (is_post()) {
    $email = post('email');
    $password = post('password');
    $remember = post('remember'); 

    // A. Check for lockout first
    $lock_status = check_login_attempts($email);
    if ($lock_status !== true) {
        $_err['email'] = $lock_status;
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        // B. Check if account is blocked
        if ($user && $user->status == 'blocked') {
            $_err['email'] = "This account has been suspended by Admin.";
        }
        // C. Success Case
        elseif ($user && password_verify($password, $user->password)) {
            $conn->prepare("UPDATE users SET login_attempts = 0 WHERE email = ?")->execute([$email]);
            if ($remember) {
                remember_me($email);
            } else {
                setcookie("remember_me", "", time() - 3600, "/");
            }
            login($user);
        } 
        // D. Failure Case (Where the warning used to happen)
        else {
            if ($user) {
                // Email is correct, but password is wrong
                $conn->prepare("UPDATE users SET login_attempts = login_attempts + 1, attempt_time = NOW() WHERE email = ?")
                     ->execute([$email]);
                
                $attempts = $user->login_attempts + 1;
                $_err['email'] = "Invalid login. Attempt $attempts of 3.";
            } else {
                // Email doesn't even exist in the database
                $_err['email'] = "Invalid login.Email doesn't even exist.Please Try to register first";
            }
        }
    }
}

include "_head.php"; 
?>

<main>
    <div class="card">
        <h1>Login</h1>

        <form method="post" class="form">
            <div class="form-row">
                <label for="email">Email:</label>
                <div class="field-group">
                    <?= html_text('email', 'maxlength="100" autofocus') ?>
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

            <div class="form-row" style="flex-direction: row; align-items: center; gap: 10px;">
                <input type="checkbox" name="remember" id="remember" style="width: auto; height: auto;">
                <label for="remember" style="margin: 0;">Remember Me</label>
            </div>

            <div class="actions">
                <button type="submit" class="btn-blue">Login</button>
            </div>
        </form>
    </div>
</main>

<?php include "_foot.php"; ?>