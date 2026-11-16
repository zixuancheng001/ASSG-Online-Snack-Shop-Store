<?php
require '_base.php';
auth('Admin', 'Member');
require '_head.php';
 
$id = (int)get('id');
 
if (!$id) {
    redirect('index.php');
}
 
if ($_user->role === 'Admin') {
    $stmt = $conn->prepare("
        SELECT orders.orders_id, orders.total, orders.status, orders.cancel_reason,
               address.address_line,
               users.name AS user_name,
               payment.payment_method, payment.payment_status
        FROM orders
        JOIN address ON orders.address_id = address.address_id
        JOIN users ON orders.user_id = users.user_id
        LEFT JOIN payment ON orders.orders_id = payment.order_id
        WHERE orders.orders_id = ?
    ");
    $stmt->execute([$id]);
} else {
    $stmt = $conn->prepare("
        SELECT orders.orders_id, orders.total, orders.status, orders.cancel_reason,
               address.address_line,
               users.name AS user_name,
               payment.payment_method, payment.payment_status
        FROM orders
        JOIN address ON orders.address_id = address.address_id
        JOIN users ON orders.user_id = users.user_id
        LEFT JOIN payment ON orders.orders_id = payment.order_id
        WHERE orders.orders_id = ? AND orders.user_id = ?
    ");
    $stmt->execute([$id, $_user->user_id]);
}
 
$order = $stmt->fetch();
 
if (!$order) {
    temp('error', 'Order not found.');
    redirect($_user->role === 'Admin' ? 'order_listing.php' : 'order_history.php');
}
 
if (is_post() && $_user->role === 'Admin') {
    $status = post('status');
    $allowed = ['Pending', 'Completed', 'Cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE orders_id = ?");
        $stmt->execute([$status, $id]);
 
        // If cancelled and already paid, mark payment as Refunded
        if ($status === 'Cancelled') {
            $conn->prepare("
                UPDATE payment SET payment_status = 'Refunded'
                WHERE order_id = ? AND payment_status = 'Paid'
            ")->execute([$id]);
        }
    }
 
    temp('info', 'Status updated.');
    redirect('order_detail.php?id=' . $id);
}
 
$stmt = $conn->prepare("
    SELECT order_item.quantity,
           products.name, products.price
    FROM order_item
    JOIN products ON order_item.product_id = products.product_id
    WHERE order_item.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
 
?>
 
<main class="shop-shell">
 
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Order #<?= $order->orders_id ?></h1>
                <p class="panel-subtitle">
                    <?= encode($order->user_name) ?>
                </p>
            </div>
            <a href="<?= $_user->role === 'Admin' ? 'order_listing.php' : 'order_history.php' ?>" class="text-link">← Back</a>
        </div>
    </section>
 
    <section class="admin-panel" style="margin-top:16px;">
        <h3>Delivery Address</h3>
        <p class="orderD1"><?= encode($order->address_line) ?></p>
    </section>
 
    <section class="admin-panel" style="margin-top:16px;">
        <h3>Payment Info</h3>
        <p>Method: <strong><?= $order->payment_method ?? '-' ?></strong></p>
        <p>Payment Status:
            <?php
            $ps = $order->payment_status ?? 'Unpaid';
            $psClass = match($ps) {
                'Paid'     => 'status-completed',
                'Refunded' => 'status-cancelled',
                default    => 'status-pending',
            };
            ?>
            <span class="orderD4 <?= $psClass ?>"><?= $ps ?></span>
        </p>
    </section>
 
    <section class="admin-panel orderD2">
        <div class="orderD3">
            <?php
                $statusClass = [
                    'Pending'    => 'status-pending',
                    'Completed'  => 'status-completed',
                    'Cancelled'  => 'status-cancelled'
                ];
                ?>
 
                <h3>Status:
                    <span class="orderD4 <?= $statusClass[$order->status] ?? 'status-cancelled' ?>">
                        <?= encode($order->status) ?>
                    </span>
                </h3>
 
            <?php if ($_user->role === 'Admin'): ?>
            <form method="post" class="orderD5">
                <select name="status" class="orderD6">
                    <option value="Pending"    <?= $order->status === 'Pending'    ? 'selected' : '' ?>>Pending</option>
                    <option value="Completed"  <?= $order->status === 'Completed'  ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled"  <?= $order->status === 'Cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn-blue">Update</button>
            </form>
            <?php endif; ?>
 
            <?php if ($_user->role === 'Member' && in_array($order->status, ['Pending'])): ?>
            <a href="order_cancel.php?id=<?= $id ?>"
               class="btn-blue"
               style="background:#c94343;text-decoration:none;display:inline-flex;align-items:center;">
                Cancel Order
            </a>
            <?php endif; ?>
        </div>
 
        <?php if ($order->status === 'Cancelled' && $order->cancel_reason): ?>
        <div style="margin-top:12px;padding:10px 14px;background:#fff5f5;border:1px solid #f5c6cb;border-radius:8px;">
            <p style="font-size:0.88rem;color:#721c24;">
                <strong>Cancellation Reason:</strong> <?= encode($order->cancel_reason) ?>
            </p>
        </div>
        <?php endif; ?>
    </section>
 
    <section class="admin-panel" style="margin-top:16px;">
        <h3>Order Items</h3>
        <table class="orderD7">
            <thead>
                <tr class="orderD9">
                    <th class="orderT21">Item</th>
                    <th class="orderT3">Qty</th>
                    <th class="orderD11">Unit Price</th>
                    <th class="orderD11">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i): ?>
                <tr class="orderD13">
                    <td class="orderD12"><?= encode($i->name) ?></td>
                    <td class="orderD10">× <?= $i->quantity ?></td>
                    <td class="orderD11">RM <?= number_format($i->price, 2) ?></td>
                    <td class="orderD11">RM <?= number_format($i->price * $i->quantity, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="orderD14">
                    <td colspan="3" class="orderD15">Total:</td>
                    <td class="orderD15">RM <?= number_format($order->total, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </section>
 
</main>
 
<?php require '_foot.php'; ?>