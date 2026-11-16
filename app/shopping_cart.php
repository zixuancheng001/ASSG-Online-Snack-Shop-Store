<?php
require '_base.php';
auth('Member');
require '_head.php';
 
$deleteId = (int)get('delete');
if ($deleteId) {
    $stmt = $conn->prepare('DELETE FROM cart WHERE cart_id = ? AND user_id = ?');
    $stmt->execute([$deleteId, $_user->user_id]);
    temp('info', 'Item removed.');
    redirect('shopping_cart.php');
}
 
if (is_post() && post('action') === 'update' && post('cart_id')) {
    $cartId = (int)post('cart_id');
    $qty    = (int)post('quantity');
 
    if ($qty <= 0) {
        $stmt = $conn->prepare('DELETE FROM cart WHERE cart_id = ? AND user_id = ?');
        $stmt->execute([$cartId, $_user->user_id]);
    } else {
        $stmt = $conn->prepare('UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?');
        $stmt->execute([$qty, $cartId, $_user->user_id]);
    }
    redirect('shopping_cart.php');
}
 
$stmt = $conn->prepare("
    SELECT cart.cart_id, cart.quantity,
           products.product_id, products.name, products.price, products.image
    FROM cart
    JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = ?
    ORDER BY cart.cart_id ASC
");
$stmt->execute([$_user->user_id]);
$cartItems = $stmt->fetchAll();
 
$total = array_sum(array_map(fn($item) => $item->price * $item->quantity, $cartItems));
?>
 
<main class="shop-shell">
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>My Cart</h1>
                <p class="panel-subtitle"><?= count($cartItems) ?> item(s)</p>
            </div>
            <a href="product_list.php" class="text-link">← Back to Menu</a>
        </div>
    </section>
 
    <?php if ($cartItems): ?>

    <section class="cart-list">
        <?php foreach ($cartItems as $item): ?>
        <article class="cart-item">
            <img src="images/<?= encode($item->image ?: 'default_image.jpg') ?>"
                 class="cart-item-img"
                 alt="<?= encode($item->name) ?>"
                 onerror="this.onerror=null;this.src='images/default_image.jpg';">

            <div class="cart-item-body">
                <div>
                    <h3><?= encode($item->name) ?></h3>
                    <p class="product-price">RM <?= number_format($item->price, 2) ?></p>
                </div>

                <div class="cart-item-actions">
                    <!-- Standalone update form per item -->
                    <form method="post" action="shopping_cart.php" style="display:contents;">
                        <input type="hidden" name="action"   value="update">
                        <input type="hidden" name="cart_id"  value="<?= $item->cart_id ?>">
                        <input type="number" name="quantity" value="<?= $item->quantity ?>" min="1" style="width:60px;">
                        <button type="submit" class="btn-blue">Update</button>
                    </form>

                    <strong>RM <?= number_format($item->price * $item->quantity, 2) ?></strong>

                    <label style="display:flex;align-items:center;gap:4px;white-space:nowrap;">
                        <input type="checkbox" name="selected[]" value="<?= $item->cart_id ?>"
                               class="cart-checkbox" form="checkout-form" checked> Checkout
                    </label>

                    <a href="shopping_cart.php?delete=<?= $item->cart_id ?>"
                       class="text-link danger-link"
                       onclick="return confirm('Remove this item?')">Remove</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>

    <form method="post" action="checkout.php" id="checkout-form"></form>

    <section class="admin-panel" style="margin-top:20px;">
        <div class="panel-header">
            <strong style="font-size:1.2rem;">
                Cart Total: RM <?= number_format($total, 2) ?>
            </strong>
            <button type="submit" form="checkout-form" class="btn-blue action-link" onclick="return validateCheckout()">Checkout</button>
        </div>
    </section>
 
    <?php else: ?>
    <div class="empty-state">
        <h3>Your cart is empty</h3>
        <p>Go add some snacks!</p>
    </div>
    <?php endif; ?>
</main>
 
<script>
function validateCheckout() {
    const checked = document.querySelectorAll('.cart-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one item to checkout.');
        return false;
    }
    return true;
}
</script>
 
<?php require '_foot.php'; ?>