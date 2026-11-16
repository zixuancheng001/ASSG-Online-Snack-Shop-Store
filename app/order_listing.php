<?php
require '_base.php';
auth('Admin');
require '_head.php';
 
$stmt = $conn->prepare("
    SELECT orders.orders_id, orders.total, orders.status,
           users.name AS user_name,
           payment.payment_method
    FROM orders
    JOIN users ON orders.user_id = users.user_id
    LEFT JOIN payment ON orders.orders_id = payment.order_id
    ORDER BY orders.orders_id DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();
 
$total_revenue = array_sum(array_map(
    fn($o) => $o->status === 'Completed' ? $o->total : 0,
    $orders
));
?>
 
<main class="shop-shell">
 
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Order Listing</h1>
                <p class="panel-subtitle"><?= count($orders) ?> order(s) | Total Revenue: RM <?= number_format($total_revenue, 2) ?></p>
            </div>
            <a href="index.php" class="text-link">← Back to Home</a>
        </div>
    </section>
 
    <?php if ($orders): ?>
    <section class="admin-panel" style="margin-top:16px;">
        <table class="listing-form">
            <thead>
                <tr class="listing-table">
                    <th class="listing-table1">#ID</th>
                    <th class="listing-table1">Customer</th>
                    <th class="listing-table2">Total</th>
                    <th class="listing-table3">Payment Method</th>
                    <th class="listing-table3">Status</th>
                    <th class="listing-table3">Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr style="border-bottom:1px solid #f5f5f5;">
                    <td class="listing-table1"><?= $o->orders_id ?></td>
                    <td class="listing-table1"><?= encode($o->user_name) ?></td>
                    <td class="listing-table2">RM <?= number_format($o->total, 2) ?></td>
                    <td class="listing-table3"><?= $o->payment_method ?? '-' ?></td>
                    <td class="listing-table3">
                        <?php
                        $payDisplay = match($o->status) {
                            'Completed'  => ['Paid',       'completed'],
                            'Cancelled'  => ['Refunded',   'refunded'],
                            default      => ['Pending',    'pending'],
                        };
                        ?>
                        <span class="status <?= $payDisplay[1] ?>"><?= $payDisplay[0] ?></span>
                    </td>
                    <td class="listing-table3">
                        <a href="order_detail.php?id=<?= $o->orders_id ?>" class="text-link">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
 
    <?php else: ?>
    <div class="empty-state">
        <h3>No orders yet</h3>
    </div>
    <?php endif; ?>
 
</main>
 
<?php require '_foot.php'; ?>