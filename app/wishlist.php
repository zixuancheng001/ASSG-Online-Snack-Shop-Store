<?php
require '_base.php';
auth('Member');

// Toggle wishlist (add/remove)
$toggleId = (int)get('toggle');
if ($toggleId) {
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_user->user_id, $toggleId]);
    if ($stmt->fetch()) {
        $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_user->user_id, $toggleId]);
        temp('info', 'Removed from wishlist.');
    } else {
        $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$_user->user_id, $toggleId]);
        temp('info', 'Added to wishlist!');
    }
    // Redirect back to where user came from
    $back = get('back') ?: 'wishlist.php';
    redirect($back);
}

require '_head.php';

// Load wishlist items
$stmt = $conn->prepare("
    SELECT w.wishlist_id, w.product_id,
           p.name, p.price, p.image, p.description
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$_user->user_id]);
$wishlistItems = $stmt->fetchAll();
?>

<main class="shop-shell">
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>My Wishlist</h1>
                <p class="panel-subtitle"><?= count($wishlistItems) ?> item(s)</p>
            </div>
            <a href="product_list.php" class="text-link">← Back to Menu</a>
        </div>
    </section>

    <?php if ($wishlistItems): ?>
    <section class="cart-list">
        <?php foreach ($wishlistItems as $item): ?>
        <article class="cart-item">
            <img src="images/<?= encode($item->image ?: 'default_image.jpg') ?>"
                 class="cart-item-img"
                 alt="<?= encode($item->name) ?>"
                 onerror="this.onerror=null;this.src='images/default_image.jpg';">
            <div class="cart-item-body">
                <div>
                    <h3><?= encode($item->name) ?></h3>
                    <p class="product-price">RM <?= number_format($item->price, 2) ?></p>
                    <p style="font-size:13px;color:#6a7f93;"><?= encode(mb_strimwidth($item->description ?: '', 0, 60, '...')) ?></p>
                </div>
                <div class="cart-item-actions">
                    <a href="product_detail.php?id=<?= $item->product_id ?>" class="btn-blue" >View Detail</a>
                    <a href="add_order.php?id=<?= $item->product_id ?>" class="btn-link">Add to Cart</a>
                    <a href="wishlist.php?toggle=<?= $item->product_id ?>&back=wishlist.php"
                       class="text-link danger-link"
                       onclick="return confirm('Remove from wishlist?')">Remove</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>

    <?php else: ?>
    <div class="empty-state">
        <h3>Your wishlist is empty</h3>
        <p>Browse the menu and click ♥ to save your favourites!</p>
        <a href="product_list.php" class="btn-blue action-link" style="margin-top:12px;">Back to Menu</a>
    </div>
    <?php endif; ?>
</main>

<?php require '_foot.php'; ?>
