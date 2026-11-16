<?php
require '_base.php';
auth('Member');
require '_head.php';
 
$order_id = (int)get('order_id');
$selected_method = post('payment_method') ?? get('method') ?? '';
 
$stmt = $conn->prepare("
    SELECT * FROM orders WHERE orders_id = ? AND user_id = ?
");
$stmt->execute([$order_id, $_user->user_id]);
$order = $stmt->fetch();
 
if (!$order) {
    redirect('order_history.php');
}
 
if (is_post()) {
 
    $method = post('payment_method');
 
    if ($method === 'Card') {
 
        $card_num = post('card_number');
        $cvv = post('cvv');
 
        if (!$card_num || !$cvv) {
            $_err['card'] = "Fill all card details";
        }
 
    } elseif ($method === 'Online') {
 
        $bank = post('bank');
        $acc = post('account');
        $pass = post('password');
 
        if (!$bank || !$acc || !$pass) {
            $_err['online'] = "Fill all bank details";
        }
 
        $status = 'Paid';
    }
 
    if (!$_err) {
        $stmt = $conn->prepare("SELECT * FROM payment WHERE order_id = ?");
        $stmt->execute([$order_id]);
 
        if ($stmt->fetch()) {
            temp('error', 'Order already paid!');
            redirect('order_history.php');
        }
 
        $stmt = $conn->prepare("
            INSERT INTO payment (order_id, payment_method, payment_status)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$order_id, $method, 'Paid']);
 
        $stmt = $conn->prepare("
            UPDATE orders SET status = 'Pending' WHERE orders_id = ?
        ");
        $stmt->execute([$order_id]);
 
        temp('info', 'Payment successful!');
        redirect('order_history.php');
    }
}
?>
 
<main class="shop-shell">
 
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Payment</h1>
                <p class="panel-subtitle">Order #<?= $order_id ?></p>
            </div>
            <a href="order_history.php" class="text-link">← Pay Later</a>
        </div>
    </section>
 
    <section class="admin-panel">
        <p style="font-size:1.1rem;margin-bottom:16px;">
            Total: <strong style="color:#2d6ca4;">RM <?= number_format($order->total, 2) ?></strong>
        </p>
 
        <form method="post" class="form">
 
            <div class="form-row">
                <label>Payment Method:</label>
                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="radio" name="payment_method" value="Card"
                               <?= $selected_method === 'Card' ? 'checked' : '' ?>
                               onclick="showForm('card')"> Card
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="radio" name="payment_method" value="Online"
                               <?= $selected_method === 'Online' ? 'checked' : '' ?>
                               onclick="showForm('online')"> Online Banking
                    </label>
                </div>
            </div>
 
            <div id="cardForm" style="display:none;">
                <div class="form-row">
                    <label>Card Number:</label>
                    <div class="field-group">
                        <input type="text" name="card_number" placeholder="Card Number" maxlength="19">
                    </div>
                </div>
                <div class="form-row">
                    <label>CVV:</label>
                    <div class="field-group">
                        <input type="text" name="cvv" placeholder="CVV" maxlength="4" style="width:100px;">
                    </div>
                </div>
                <?php err('card') ?>
            </div>
 
            <div id="onlineForm" style="display:none;">
                <div class="form-row">
                    <label>Bank:</label>
                    <div class="field-group">
                        <select name="bank">
                            <option value="">Select Bank</option>
                            <option>Maybank</option>
                            <option>CIMB</option>
                            <option>Public Bank</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label>Account Number:</label>
                    <div class="field-group">
                        <input type="text" name="account" placeholder="Account Number">
                    </div>
                </div>
                <div class="form-row">
                    <label>Password:</label>
                    <div class="field-group">
                        <input type="password" name="password" placeholder="Password">
                    </div>
                </div>
                <?php err('online') ?>
            </div>
 
            <div class="actions" style="align-items:flex-start;">
                <button type="submit" class="btn-blue">Pay Now</button>
            </div>
 
        </form>
    </section>
 
</main>
 
<script>
function showForm(type) {
    document.getElementById('cardForm').style.display = 'none';
    document.getElementById('onlineForm').style.display = 'none';
 
    if (type === 'card') {
        document.getElementById('cardForm').style.display = 'block';
    }
    if (type === 'online') {
        document.getElementById('onlineForm').style.display = 'block';
    }
}
 
window.onload = function () {
    const selected = document.querySelector("input[name='payment_method']:checked");
 
    if (selected) {
        if (selected.value === "Card") showForm("card");
        if (selected.value === "Online") showForm("online");
    }
}
</script>
 
<?php require '_foot.php'; ?>