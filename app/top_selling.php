<?php
require '_base.php';
auth('Admin');
require '_head.php';

$stmt = $conn->query("
    SELECT pr.product_id, pr.name, pr.image, pr.category, pr.price,
           COALESCE(SUM(oi.quantity), 0) AS total_sold,
           COALESCE(SUM(oi.quantity * pr.price), 0) AS revenue
    FROM products pr
    LEFT JOIN order_item oi ON pr.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.orders_id AND o.status != 'Cancelled'
    GROUP BY pr.product_id, pr.name, pr.image, pr.category, pr.price
    ORDER BY total_sold DESC
");
$products = $stmt->fetchAll();
$totalRevenue = array_sum(array_column($products, 'revenue'));
$totalSold    = array_sum(array_column($products, 'total_sold'));
?>

<main class="shop-shell">
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Top Selling Products</h1>
                <p class="panel-subtitle"><?= count($products) ?> products · <?= $totalSold ?> units sold · RM <?= number_format($totalRevenue, 2) ?> revenue</p>
            </div>
            <a href="index.php" class="text-link">← Back to Dashboard</a>
        </div>
    </section>

    <section class="admin-panel" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:2px solid #e8eef5;">
                    <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Rank</th>
                    <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Photo</th>
                    <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Product</th>
                    <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Category</th>
                    <th style="padding:12px 14px;text-align:right;font-weight:600;color:#4a6278;">Unit Price</th>
                    <th style="padding:12px 14px;text-align:center;font-weight:600;color:#4a6278;">Units Sold</th>
                    <th style="padding:12px 14px;text-align:right;font-weight:600;color:#4a6278;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $i => $p): ?>
                <tr style="border-bottom:1px solid #f0f4f8;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 14px;">
                        <?php if ($i < 3): ?>
                        <span style="font-size:18px;"><?= ['🥇','🥈','🥉'][$i] ?></span>
                        <?php else: ?>
                        <span style="color:#8a9bb0;font-weight:600;">#<?= $i+1 ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 14px;">
                        <img src="images/<?= encode($p->image ?: 'default_image.jpg') ?>"
                             style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #e8eef5;"
                             onerror="this.onerror=null;this.src='images/default_image.jpg';">
                    </td>
                    <td style="padding:12px 14px;font-weight:600;color:#1a2e42;">
                        <a href="product_detail.php?id=<?= $p->product_id ?>" class="text-link"><?= encode($p->name) ?></a>
                    </td>
                    <td style="padding:12px 14px;">
                        <span style="background:#eef4ff;color:#2563eb;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;"><?= encode($p->category) ?></span>
                    </td>
                    <td style="padding:12px 14px;text-align:right;color:#4a6278;">RM <?= number_format($p->price, 2) ?></td>
                    <td style="padding:12px 14px;text-align:center;">
                        <?php if ($p->total_sold > 0): ?>
                        <span style="background:#eef4ff;color:#2563eb;padding:3px 12px;border-radius:999px;font-weight:700;font-size:13px;"><?= $p->total_sold ?></span>
                        <?php else: ?>
                        <span style="color:#c0ccd8;font-size:13px;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 14px;text-align:right;font-weight:700;color:<?= $p->revenue > 0 ? '#059669' : '#c0ccd8' ?>;">
                        <?= $p->revenue > 0 ? 'RM '.number_format($p->revenue, 2) : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;border-top:2px solid #e8eef5;">
                    <td colspan="5" style="padding:12px 14px;font-weight:700;color:#4a6278;">Total</td>
                    <td style="padding:12px 14px;text-align:center;font-weight:800;color:#2563eb;"><?= $totalSold ?></td>
                    <td style="padding:12px 14px;text-align:right;font-weight:800;color:#059669;">RM <?= number_format($totalRevenue, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </section>
</main>

<?php require '_foot.php'; ?>
