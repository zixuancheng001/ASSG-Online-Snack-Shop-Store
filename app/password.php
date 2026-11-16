<?php
include_once "_base.php"; 
include "_head.php";

if (is_post()) {
    $new = post('new');
    $confirm = post('confirm');

    // Basic Validation
    if (!$new) {
        $_err['new'] = 'Required';
    } else if (strlen($new) < 5) {
        $_err['new'] = 'Minimum 5 characters';
    }

    if ($new != $confirm) {
        $_err['confirm'] = 'Passwords do not match';
    }

    if (!$_err) {
        $hash = password_hash($new, PASSWORD_DEFAULT);

        // Use the ID or Email from the global $_user object
        $stm = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stm->execute([$hash, $_user->email]);

        temp('info', 'Password updated successfully!');
        redirect('profile.php');
    }
}
?>

<main>
    <div class="card">
        <h1>Change Password</h1>

        <form method="post" class="form">
            <div class="form-row">
                <label for="new">New Password:</label>
                <div class="field-group">
                    <input name="new" id="new" type="password" placeholder="Enter new password">
                    <?= err('new') ?>
                </div>
            </div>

            <div class="form-row">
                <label for="confirm">Confirm New:</label>
                <div class="field-group">
                    <input name="confirm" id="confirm" type="password" placeholder="Confirm new password">
                    <?= err('confirm') ?>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-blue">Update Password</button>
            </div>
        </form>
    </div>
</main>

<?php include "_foot.php"; ?>