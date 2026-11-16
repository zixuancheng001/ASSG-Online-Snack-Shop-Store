<?php
require '_base.php';
auth('Member');

$order_id = (int)get('order_id');

if (!$order_id) redirect('order_history.php');

// Verify order belongs to user and is completed
$stmt = $conn->prepare("
    SELECT o.orders_id, o.total
    FROM orders o
    WHERE o.orders_id = ? AND o.user_id = ? AND o.status = 'Completed'
");
$stmt->execute([$order_id, $_user->user_id]);
$order = $stmt->fetch();

if (!$order) {
    temp('info', 'Order not found or not yet completed.');
    redirect('order_history.php');
}

// Get all products in this order
$stmt = $conn->prepare("
    SELECT oi.product_id, p.name, p.image
    FROM order_item oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$products = $stmt->fetchAll();

// Handle review submit
if (is_post()) {
    $submitted = 0;
    foreach ($products as $p) {
        $pid     = $p->product_id;
        $rating  = (int)post("rating_$pid");
        $comment = trim(post("comment_$pid"));
        if ($rating >= 1 && $rating <= 5) {
            $stmt = $conn->prepare("
                INSERT INTO reviews (user_id, product_id, rating, comment)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = NOW()
            ");
            $stmt->execute([$_user->user_id, $pid, $rating, $comment]);
            $submitted++;
        }
    }
    if ($submitted > 0) {
        temp('info', 'Reviews submitted! Thank you.');
    }
    redirect('order_history.php');
}

// Load existing reviews for this order's products
$productIds = array_column($products, 'product_id');
$existingReviews = [];
if ($productIds) {
    $ph   = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $conn->prepare("SELECT product_id, rating, comment FROM reviews WHERE user_id = ? AND product_id IN ($ph)");
    $stmt->execute([$_user->user_id, ...$productIds]);
    foreach ($stmt->fetchAll() as $r) {
        $existingReviews[$r->product_id] = $r;
    }
}

require '_head.php';
?>

<main class="shop-shell">
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>Review Order #<?= $order_id ?></h1>
                <p class="panel-subtitle">Rate the products you ordered</p>
            </div>
            <a href="order_history.php" class="text-link">← Back to Orders</a>
        </div>
    </section>

    <form method="post" action="review.php?order_id=<?= $order_id ?>">
        <?php foreach ($products as $p): ?>
        <?php $existing = $existingReviews[$p->product_id] ?? null; ?>
        <section class="admin-panel" style="margin-top:14px;">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                <img src="images/<?= encode($p->image ?: 'default_image.jpg') ?>"
                     style="width:60px;height:60px;object-fit:cover;border-radius:10px;border:1px solid #dbe7f3;"
                     onerror="this.onerror=null;this.src='images/default_image.jpg';">
                <h3 style="color:#2f567e;margin:0;"><?= encode($p->name) ?></h3>
            </div>

            <div class="form-row">
                <label>Rating:</label>
                <div class="star-picker" id="star-picker-<?= $p->product_id ?>">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <span class="star <?= ($existing && $existing->rating >= $s) ? 'active' : '' ?>"
                          data-val="<?= $s ?>"
                          data-pid="<?= $p->product_id ?>">★</span>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating_<?= $p->product_id ?>"
                       id="rating-<?= $p->product_id ?>"
                       value="<?= $existing->rating ?? 0 ?>">
            </div>

            <div class="form-row" style="margin-top:10px;">
                <label for="comment_<?= $p->product_id ?>">Comment (optional):</label>
                <textarea name="comment_<?= $p->product_id ?>"
                          id="comment_<?= $p->product_id ?>"
                          rows="2"
                          style="width:100%;padding:10px;border:1px solid #cbdced;border-radius:10px;font-size:14px;resize:vertical;min-height:70px;font-family:inherit;"
                          placeholder="Share your thoughts..."><?= encode($existing->comment ?? '') ?></textarea>
            </div>
        </section>
        <?php endforeach; ?>

        <div style="margin-top:16px;display:flex;gap:10px;">
            <button type="submit" class="btn-blue">Submit Reviews</button>
            <a href="order_history.php" class="text-link" style="align-self:center;">Cancel</a>
        </div>
    </form>
</main>

<script>
document.querySelectorAll('.star-picker').forEach(picker => {
    const stars = picker.querySelectorAll('.star');
    const pid   = stars[0]?.dataset.pid;
    const input = document.getElementById('rating-' + pid);

    stars.forEach(star => {
        star.addEventListener('mouseenter', () => {
            const val = +star.dataset.val;
            stars.forEach((s, i) => s.classList.toggle('active', i < val));
        });
        star.addEventListener('click', () => {
            input.value = star.dataset.val;
            stars.forEach((s, i) => s.classList.toggle('active', i < +star.dataset.val));
        });
    });

    picker.addEventListener('mouseleave', () => {
        const cur = +input.value;
        stars.forEach((s, i) => s.classList.toggle('active', i < cur));
    });
});
</script>

<?php require '_foot.php'; ?>
