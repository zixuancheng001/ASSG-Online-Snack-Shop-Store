<?php
require "_base.php";
auth();
include "_head.php";
?>

<main>
    <div class="card">
        <h1>My Profile</h1>

        <form action="update_profile.php" method="post" enctype="multipart/form-data" class="form">
            <div class="form-row photo-row">
                <label class="upload" for="photo">
                    <img id="preview" src="images/<?= encode($_user->photo ?: 'default_image.jpg') ?>" alt="Avatar">
                </label>
                <input type="file" name="photo" id="photo" accept="image/*">
            </div>

            <div class="form-row">
                <label for="name">Name:</label>
                <div class="field-group">
                    <input type="text" id="name" name="name" value="<?= encode($_user->name) ?>" maxlength="100" required>
                </div>
            </div>

            <div class="form-row">
                <label for="email">Email:</label>
                <div class="field-group">
                    <input type="email" id="email" name="email" value="<?= encode($_user->email) ?>" maxlength="100" required>
                </div>
            </div>

            <div class="form-row">
                <label>Role:</label>
                <div class="field-group">
                    <input type="text" value="<?= encode($_user->role) ?>" readonly>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-blue">Save Profile</button>
                <a href="password.php" class="text-link">Change Password</a>
                <a href="index.php" class="text-link">Back to Home</a>
            </div>
        </form>
    </div>
</main>

<?php include "_foot.php"; ?>
