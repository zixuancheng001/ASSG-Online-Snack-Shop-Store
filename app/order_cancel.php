<?php
require '_base.php';
auth('Member');
require '_head.php';
 
$id = (int)get('id');
 
if (!$id) {
    redirect('order_history.php');
}
 
$stmt = $conn->prepare("
    SELECT orders.orders_id, orders.status,
           address.address_line,
           users.name AS user_name
    FROM orders
    JOIN address ON orders.address_id = address.address_id
    JOIN users ON orders.user_id = users.user_id
    WHERE orders.orders_id = ? AND orders.user_id = ?
");
$stmt->execute([$id, $_user->user_id]);
$order = $stmt->fetch();
 
if (!$order) {
    temp('error', 'Order not found.');
    redirect('order_history.php');
}
 
if (!in_array($order->status, ['Pending'])) {
    temp('error', 'Only pending orders can be cancelled.');
    redirect('order_detail.php?id=' . $id);
}
 
$error = '';
 
if (is_post()) {
    $reason_choice = post('reason_choice');
    $reason_custom = trim(post('reason_custom'));
 
    if ($reason_choice === 'other') {
        $cancel_reason = $reason_custom;
        if (!$cancel_reason) {
            $error = 'Please enter your cancellation reason.';
        }
    } elseif (in_array($reason_choice, ['changed_mind', 'change_address'])) {
        $labels = [
            'changed_mind'   => 'Changed mind',
            'change_address' => 'Change address',
        ];
        $cancel_reason = $labels[$reason_choice];
    } else {
        $error = 'Please select a cancellation reason.';
    }
 
    if (!$error) {
        $stmt = $conn->prepare("
            UPDATE orders SET status = 'Cancelled', cancel_reason = ?
            WHERE orders_id = ? AND user_id = ?
        ");
        $stmt->execute([$cancel_reason, $id, $_user->user_id]);
 
        // If already paid, mark as Refunded
        $conn->prepare("
            UPDATE payment SET payment_status = 'Refunded'
            WHERE order_id = ? AND payment_status = 'Paid'
        ")->execute([$id]);
 
        temp('info', 'Order #' . $id . ' has been cancelled.');
        redirect('order_detail.php?id=' . $id);
    }
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
                <h1>Cancel Order #<?= $order->orders_id ?></h1>
                <p class="panel-subtitle">Please select a reason for cancellation</p>
            </div>
            <a href="order_detail.php?id=<?= $id ?>" class="text-link">← Back to Order</a>
        </div>
    </section>
 
    <section class="admin-panel" style="margin-top:16px;">
        <h3>Order Summary</h3>
        <p class="orderD1 orderC1"><?= encode($order->address_line) ?></p>
        <table class="orderD7">
            <thead>
                <tr class="orderD9">
                    <th class="orderT2">Item</th>
                    <th class="orderT3">Qty</th>
                    <th class="orderT4">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i): ?>
                <tr class="orderD13">
                    <td class="orderD12"><?= encode($i->name) ?></td>
                    <td class="orderD10">× <?= $i->quantity ?></td>
                    <td class="orderD11">RM <?= number_format($i->price * $i->quantity, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
 
    <section class="admin-panel orderC2">
        <h3 style="margin-bottom:14px;">Cancellation Reason</h3>
 
        <?php if ($error): ?>
        <p class="err orderC3">⚠ <?= encode($error) ?></p>
        <?php endif; ?>
 
        <form method="post" class="form" id="cancel-form">
 
            <div class="cancel-reason-list">
 
                <label class="cancel-option" onclick="selectReason(this, 'changed_mind')">
                    <input type="radio" name="reason_choice" value="changed_mind"
                           <?= post('reason_choice') === 'changed_mind' ? 'checked' : '' ?>>
                    <div class="cancel-option-body">
                        <span class="cancel-option-icon"></span>
                        <div>
                            <strong>Changed mind</strong>
                            <p>I no longer want this order</p>
                        </div>
                    </div>
                </label>
 
                <label class="cancel-option" onclick="selectReason(this, 'change_address')">
                    <input type="radio" name="reason_choice" value="change_address"
                           <?= post('reason_choice') === 'change_address' ? 'checked' : '' ?>>
                    <div class="cancel-option-body">
                        <span class="cancel-option-icon"></span>
                        <div>
                            <strong>Change address</strong>
                            <p>I need to change my delivery address</p>
                        </div>
                    </div>
                </label>
 
                <label class="cancel-option" id="option-other" onclick="selectReason(this, 'other')">
                    <input type="radio" name="reason_choice" value="other"
                           <?= post('reason_choice') === 'other' ? 'checked' : '' ?>>
                    <div class="cancel-option-body">
                        <span class="cancel-option-icon"></span>
                        <div>
                            <strong>other</strong>
                            <p>Other reason (please specify)</p>
                        </div>
                    </div>
                </label>
 
            </div>
 
            <div id="custom-reason-wrap" style="display:<?= post('reason_choice') === 'other' ? 'block' : 'none' ?>;margin-top:10px;">
                <div class="form-row">
                    <label for="reason_custom">Please describe your reason:</label>
                    <textarea name="reason_custom" id="reason_custom"
                              placeholder="Enter your reason here..."
                              class="orderC4"><?= encode(post('reason_custom')) ?></textarea>
                </div>
            </div>
 
            <div class="orderC5">
                <a href="order_detail.php?id=<?= $id ?>" class="btn-blue" style="background:#6a7f93;text-decoration:none;display:inline-flex;align-items:center;">
                    Back
                </a>
                <button type="submit"
                        class="btn-blue"
                        style="background:#c94343;"
                        onclick="return confirm('Are you sure you want to cancel Order #<?= $id ?>? This cannot be undone.')">
                    Confirm Cancel
                </button>
            </div>
 
        </form>
    </section>
 
</main>
 
<script>
function selectReason(el, value) {
 
    document.querySelectorAll('.cancel-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
 
    const wrap = document.getElementById('custom-reason-wrap');
    wrap.style.display = value === 'other' ? 'block' : 'none';
 
    if (value === 'other') {
        document.getElementById('reason_custom').focus();
    }
}
 
document.addEventListener('DOMContentLoaded', function () {
    const checked = document.querySelector('input[name="reason_choice"]:checked');
    if (checked) {
        checked.closest('.cancel-option').classList.add('selected');
    }
});
</script>
 
<?php require '_foot.php'; ?>