<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Snack Shop System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body>

<?php
// Cart count for member topbar badge
$_cartCount = 0;
if (isset($_user) && $_user && $_user->role === 'Member') {
    try {
        $stmCart = $conn->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
        $stmCart->execute([$_user->user_id]);
        $_cartCount = (int)$stmCart->fetchColumn();
    } catch (Exception $e) {}
}
?>

<div class="topbar <?= !isset($_user) || !$_user ? 'guest-topbar' : '' ?>">
    <div class="nav-left">
        <a href="index.php" class="brand-link">Snack Paradise</a>
        <?php if(isset($_user) && $_user && $_user->role === 'Admin'): ?>
            <a href="index.php">Home</a>
            <a href="product_list.php">Products</a>
            <a href="member_list.php">Members</a>
            <a href="order_listing.php">Orders</a>
        <?php elseif(isset($_user) && $_user && $_user->role === 'Member'): ?>
            <a href="index.php">Home</a>
            <a href="product_list.php">Menu</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="order_history.php">Orders</a>
        <?php else: ?>
            <a href="index.php">Home</a>
        <?php endif; ?>
    </div>

    <div class="user-class">
        <?php if(isset($_user) && $_user): ?>
            <div class="user-info <?= $_user->role === 'Admin' ? 'admin-user-box' : '' ?>">
                <?php if($_user->role === 'Member'): ?>
                <a href="shopping_cart.php" class="cart-badge-link" title="My Cart">
                    🛒
                    <?php if ($_cartCount > 0): ?>
                    <span class="cart-badge"><?= $_cartCount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <img src="images/<?= encode($_user->photo ?: 'default_image.jpg') ?>" class="top-avatar" alt="User avatar">
                <div class="user-meta">
                    <strong><?= encode($_user->name) ?></strong>
                    <small><?= encode($_user->role) ?></small>
                </div>
            </div>
            <?php if($_user->role === 'Admin'): ?>
                <a href="profile.php">Profile</a>
                <a href="password.php">Settings</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="profile.php">My Profile</a>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn">Register</a>
        <?php endif; ?>
    </div>
</div>
