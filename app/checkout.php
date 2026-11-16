<?php
require '_base.php';
auth('Member');
require '_head.php';
 
$selected = array_map('intval', $_POST['selected'] ?? []);
 
if (!$selected) {
    temp('error', 'Please select at least one item to checkout.');
    redirect('shopping_cart.php');
}
 
$placeholders = implode(',', array_fill(0, count($selected), '?'));
 
$stmt = $conn->prepare("
    SELECT cart.cart_id, cart.quantity,
           products.product_id, products.name, products.price
    FROM cart
    JOIN products ON cart.product_id = products.product_id
    WHERE cart.cart_id IN ($placeholders)
      AND cart.user_id = ?
");
$stmt->execute([...$selected, $_user->user_id]);
$items = $stmt->fetchAll();
 
if (!$items) {
    temp('error', 'No valid items found.');
    redirect('shopping_cart.php');
}
 
$total = array_sum(array_map(fn($i) => $i->price * $i->quantity, $items));
 
$stmt = $conn->prepare("SELECT * FROM address WHERE user_id = ?");
$stmt->execute([$_user->user_id]);
$addresses = $stmt->fetchAll();
 
if (is_post() && post('address_id')) {
 
    $address_id = (int)post('address_id');
 
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, address_id, total)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$_user->user_id, $address_id, $total]);
    $order_id = $conn->lastInsertId();
 
    foreach ($items as $i) {
        $stmt = $conn->prepare("
            INSERT INTO order_item (order_id, product_id, quantity)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$order_id, $i->product_id, $i->quantity]);
    }

    $stmt = $conn->prepare("
        DELETE FROM cart
        WHERE cart_id IN ($placeholders)
          AND user_id = ?
    ");
    $stmt->execute([...$selected, $_user->user_id]);
 
    temp('info', 'Order placed successfully!');
    redirect("payment.php?order_id=$order_id");
}
?>
 
<main class="shop-shell">
 
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Checkout</h1>
                <p class="panel-subtitle"><?= count($items) ?> item(s) selected</p>
            </div>
            <a href="shopping_cart.php" class="text-link">← Back to Cart</a>
        </div>
    </section>
 
    <section class="simple-grid">
        <?php foreach ($items as $i): ?>
        <article class="product-card">
            <div class="product-body">
                <div class="product-meta">
                    <h3><?= encode($i->name) ?></h3>
                    <p class="product-price">RM <?= number_format($i->price, 2) ?> × <?= $i->quantity ?></p>
                </div>
                <strong style="font-size:1.1rem;">
                    RM <?= number_format($i->price * $i->quantity, 2) ?>
                </strong>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
 
    <form method="post" action="checkout.php">
 
        <?php foreach ($selected as $cid): ?>
            <input type="hidden" name="selected[]" value="<?= $cid ?>">
        <?php endforeach; ?>
 
        <?php if ($addresses): ?>
        <section class="admin-panel" style="margin-top:20px;">
            <div class="checkout1">
                <h3 class="checkout2">Select Delivery Address</h3>
                <a href="member_address.php" class="text-link checkout3">+ Manage Addresses</a>
            </div>

            <div class="address-card-list">
                <?php foreach ($addresses as $idx => $a): ?>
                <label class="address-card-option <?= $idx === 0 ? 'selected' : '' ?>">
                    <input type="radio" name="address_id" value="<?= $a->address_id ?>"
                           <?= $idx === 0 ? 'checked' : '' ?> required
                           onchange="selectAddress(this)">
                    <div class="address-card-body">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <strong><?= encode($a->name ?? '') ?></strong>
                            <span style="font-size:0.82rem;color:#6a7f93;"><?= encode($a->phone_no ?? '') ?></span>
                        </div>
                        <p style="margin:4px 0 0;font-size:0.88rem;color:#4a6278;">
                            <?= encode($a->address_line) ?>
                            <?php if (!empty($a->postcode) || !empty($a->city)): ?>
                            , <?= encode($a->postcode) ?> <?= encode($a->city) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </section>
 
        <?php else: ?>
        <section class="admin-panel checkout5">
            <p>You have no saved addresses. <a href="member_address.php" class="text-link">Add address</a>.</p>
        </section>
        <?php endif; ?>
 
        <section class="admin-panel checkout6">
            <div class="panel-header">
                <strong class="checkout7">Total: RM <?= number_format($total, 2) ?></strong>
                <?php if ($addresses): ?>
                <button type="submit" class="btn-blue"
                        onclick="return confirm('Confirm order for RM <?= number_format($total, 2) ?>?')">
                    Place Order
                </button>
                <?php endif; ?>
            </div>
        </section>

    </form>
 
</main>
 
<script>
function selectAddress(radio) {
    document.querySelectorAll('.address-card-option').forEach(o => o.classList.remove('selected'));
    radio.closest('.address-card-option').classList.add('selected');
}
</script>
 
<?php require '_foot.php'; ?>