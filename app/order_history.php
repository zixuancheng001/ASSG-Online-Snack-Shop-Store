<?php
require '_base.php';
auth('Member');
require '_head.php';

$filterStatus = get('status') ?: 'all';
$search       = get('search') ?: '';

$stmt = $conn->prepare("
    SELECT o.orders_id, o.total, o.status,
           a.address_line,
           p.payment_method,
           p.payment_status
    FROM orders o
    JOIN address a ON o.address_id = a.address_id
    LEFT JOIN payment p ON o.orders_id = p.order_id
    WHERE o.user_id = ?
    ORDER BY o.orders_id DESC
");
$stmt->execute([$_user->user_id]);
$allOrders = $stmt->fetchAll();

// Count per status
$counts = ['all' => count($allOrders), 'Pending' => 0, 'Completed' => 0, 'Cancelled' => 0];
foreach ($allOrders as $o) {
    if (isset($counts[$o->status])) $counts[$o->status]++;
}

// Filter
$orders = array_filter($allOrders, function($o) use ($filterStatus, $search) {
    if ($filterStatus !== 'all' && $o->status !== $filterStatus) return false;
    if ($search && stripos($o->orders_id, $search) === false) return false;
    return true;
});
?>

<main class="shop-shell">
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Order History</h1>
                <p class="panel-subtitle"><?= count($allOrders) ?> order(s) total</p>
            </div>
            <a href="product_list.php" class="text-link">← Continue Shopping</a>
        </div>
    </section>

    <div class="oh2-wrap">
        <!-- Status Tabs -->
        <div class="oh2-tabs">
            <?php foreach (['all' => 'All Orders', 'Pending' => 'Unpaid', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'] as $key => $label): ?>
            <a href="order_history.php?status=<?= $key ?>"
               class="oh2-tab <?= $filterStatus === $key ? 'active' : '' ?>">
                <?= $label ?>
                <span class="oh2-tab-count"><?= $counts[$key] ?? 0 ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Table -->
        <?php if ($orders): ?>
        <table class="oh2-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Items</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th style="text-align:right;">Total</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <?php
                    $displayStatus = match($o->status) {
                        'Completed' => 'Paid',
                        'Cancelled' => 'Refunded',
                        default     => 'Unpaid',
                    };
                    $statusColor = match($displayStatus) {
                        'Paid'     => '#059669',
                        'Refunded' => '#dc2626',
                        default    => '#d97706',
                    };
                    $statusBg = match($displayStatus) {
                        'Paid'     => '#d1fae5',
                        'Refunded' => '#fee2e2',
                        default    => '#fef3c7',
                    };
                    $orderStatusColor = match($o->status) {
                        'Completed' => '#059669',
                        'Cancelled' => '#dc2626',
                        default     => '#d97706',
                    };

                    $stmtItems = $conn->prepare("
                        SELECT p.name, p.image, oi.quantity
                        FROM order_item oi
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE oi.order_id = ?
                        LIMIT 2
                    ");
                    $stmtItems->execute([$o->orders_id]);
                    $items = $stmtItems->fetchAll();

                    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM order_item WHERE order_id = ?");
                    $stmtCount->execute([$o->orders_id]);
                    $itemCount = (int)$stmtCount->fetchColumn();
                ?>
                <tr>
                    <td>
                        <a href="order_detail.php?id=<?= $o->orders_id ?>" class="oh2-order-id">#<?= $o->orders_id ?></a>
                    </td>
                    <td>
                        <div class="oh2-items">
                            <?php foreach ($items as $item): ?>
                            <div class="oh2-item-row">
                                <img src="images/<?= encode($item->image ?: 'default_image.jpg') ?>"
                                     class="oh2-thumb"
                                     style="width:32px;height:32px;min-width:32px;max-width:32px;"
                                     onerror="this.onerror=null;this.src='images/default_image.jpg';">
                                <span><?= encode(mb_strimwidth($item->name, 0, 22, '...')) ?> ×<?= $item->quantity ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if ($itemCount > 2): ?>
                            <span style="font-size:12px;color:#8a9bb0;">+<?= $itemCount - 2 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span style="color:#4a6278;font-size:13px;"><?= encode($o->payment_method ?: '—') ?></span>
                    </td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:4px;">
                            <span style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;display:inline-block;">
                                <?= $displayStatus ?>
                            </span>
                            <span style="font-size:11px;color:<?= $orderStatusColor ?>;font-weight:600;"><?= $o->status ?></span>
                        </div>
                    </td>
                    <td style="text-align:right;font-weight:700;color:#1a2e42;">
                        RM <?= number_format($o->total, 2) ?>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                            <?php if ($o->status !== 'Completed' && $o->status !== 'Cancelled' && empty($o->payment_status)): ?>
                            <a href="payment.php?order_id=<?= $o->orders_id ?>" class="btn-blue" style="font-size:12px;padding:5px 12px;">Pay Now</a>
                            <?php endif; ?>
                            <?php if ($o->status === 'Completed'): ?>
                            <a href="review.php?order_id=<?= $o->orders_id ?>" class="btn-blue" style="font-size:12px;padding:5px 12px;background:#5b8fbe;">Review</a>
                            <?php endif; ?>
                            <a href="order_detail.php?id=<?= $o->orders_id ?>" class="text-link" style="font-size:12px;align-self:center;">Details</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state" style="margin-top:16px;">
            <h3>No orders found</h3>
            <p>Try a different filter or go browse the menu.</p>
            <a href="product_list.php" class="btn-blue action-link" style="margin-top:12px;">Browse Menu</a>
        </div>
        <?php endif; ?>
    </div>

</main>

<?php require '_foot.php'; ?>
