<?php
require '_base.php';
auth('Admin', 'Member');

$id = (int)get('id');
$p  = get_product($id);

if (!$p) {
    temp('info', 'Product not found.');
    redirect('product_list.php');
}

// Load gallery photos
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$stmt->execute([$id]);
$extraPhotos = $stmt->fetchAll();
$allPhotos = $extraPhotos ? array_map(fn($ep) => $ep->filename, $extraPhotos) : [$p->image ?: 'default_image.jpg'];

// Wishlist check for member
$inWishlist = false;
if ($_user && $_user->role === 'Member') {
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_user->user_id, $id]);
    $inWishlist = (bool)$stmt->fetch();
}

// Load all reviews + avg rating (visible to all)
$stmt = $conn->prepare("
    SELECT r.*, u.name AS reviewer_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$id]);
$reviews   = $stmt->fetchAll();
$avgRating = $reviews ? round(array_sum(array_map(fn($r) => $r->rating, $reviews)) / count($reviews), 1) : 0;

require '_head.php';
?>

<main class="shop-shell">
    <?php if ($_user && $_user->role === 'Admin'): ?>
    <section class="product-detail-card simple-detail">
        <div class="detail-image-panel" style="padding:0;overflow:hidden;border-radius:14px;">
            <div class="pslider">
                <div class="pslider-track" id="pslider-track">
                    <?php foreach ($allPhotos as $i => $photo): ?>
                    <div class="pslider-slide">
                        <img src="images/<?= encode($photo) ?>" class="pslider-img"
                             alt="Product photo <?= $i+1 ?>"
                             onerror="this.onerror=null;this.src='images/default_image.jpg';">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($allPhotos) > 1): ?>
                <button class="pslider-btn pslider-prev" onclick="pSlide(-1)">&#8249;</button>
                <button class="pslider-btn pslider-next" onclick="pSlide(1)">&#8250;</button>
                <div class="pslider-dots" id="pslider-dots">
                    <?php foreach ($allPhotos as $i => $photo): ?>
                    <span class="pslider-dot <?= $i === 0 ? 'active' : '' ?>" onclick="pGoTo(<?= $i ?>)"></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="detail-content">
            <p class="eyebrow">Snack Showcase</p>
            <h1><?= encode($p->name) ?></h1>
            <p class="detail-price">RM <?= number_format($p->price, 2) ?></p>
            <?php if ($avgRating > 0): ?>
            <p class="review-avg">
                <?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆', 5 - round($avgRating)) ?>
                <span><?= $avgRating ?> / 5 (<?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?>)</span>
            </p>
            <?php endif; ?>
            <p class="detail-description"><?= nl2br(encode($p->description ?: 'No description yet.')) ?></p>
            <div class="detail-actions">
                <a href="product_edit.php?id=<?= $p->product_id ?>" class="btn-blue action-link">Edit Product</a>
                <a href="product_list.php" class="text-link">Back to Products</a>
            </div>
        </div>
    </section>

    <?php elseif ($_user && $_user->role === 'Member'): ?>
    <section class="product-detail-card simple-detail">
        <div class="detail-image-panel" style="padding:0;overflow:hidden;border-radius:14px;">
            <div class="pslider">
                <div class="pslider-track" id="pslider-track">
                    <?php foreach ($allPhotos as $i => $photo): ?>
                    <div class="pslider-slide">
                        <img src="images/<?= encode($photo) ?>" class="pslider-img"
                             alt="Product photo <?= $i+1 ?>"
                             onerror="this.onerror=null;this.src='images/default_image.jpg';">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($allPhotos) > 1): ?>
                <button class="pslider-btn pslider-prev" onclick="pSlide(-1)">&#8249;</button>
                <button class="pslider-btn pslider-next" onclick="pSlide(1)">&#8250;</button>
                <div class="pslider-dots" id="pslider-dots">
                    <?php foreach ($allPhotos as $i => $photo): ?>
                    <span class="pslider-dot <?= $i === 0 ? 'active' : '' ?>" onclick="pGoTo(<?= $i ?>)"></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="detail-content">
            <p class="eyebrow">Snack Showcase</p>
            <h1><?= encode($p->name) ?></h1>
            <p class="detail-price">RM <?= number_format($p->price, 2) ?></p>
            <?php if ($avgRating > 0): ?>
            <p class="review-avg">
                <?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆', 5 - round($avgRating)) ?>
                <span><?= $avgRating ?> / 5 (<?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?>)</span>
            </p>
            <?php endif; ?>
            <p class="detail-description"><?= nl2br(encode($p->description ?: 'No description yet.')) ?></p>
            <div class="detail-actions">
                <form method="post" action="add_order.php" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <input type="hidden" name="product_id" value="<?= $p->product_id ?>">
                    <label for="quantity" style="font-weight:600;">Qty:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                           style="width:65px;padding:6px;font-size:1rem;">
                    <button type="submit" class="btn-blue action-link">Add to Cart</button>
                </form>
                <a href="wishlist.php?toggle=<?= $p->product_id ?>&back=<?= urlencode('product_detail.php?id='.$p->product_id) ?>"
                   class="wishlist-btn <?= $inWishlist ? 'wishlisted' : '' ?>">
                    <?= $inWishlist ? '♥' : '♡' ?> <?= $inWishlist ? 'Wishlisted' : 'Wishlist' ?>
                </a>
                <a href="product_list.php" class="text-link">Back to Menu</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Customer Reviews (read-only) -->
    <?php if ($reviews): ?>
    <section class="admin-panel" style="margin-top:16px;max-width:760px;margin-left:auto;margin-right:auto;">
        <h3 style="margin-bottom:14px;color:#2f567e;">
            Customer Reviews
            <?php if ($avgRating > 0): ?>
            <span style="font-size:14px;font-weight:400;color:#6a7f93;margin-left:8px;">avg <?= $avgRating ?>/5</span>
            <?php endif; ?>
        </h3>
        <?php foreach ($reviews as $rv): ?>
        <div class="review-item">
            <div class="review-header">
                <strong><?= encode($rv->reviewer_name) ?></strong>
                <span class="review-stars"><?= str_repeat('★', $rv->rating) ?><?= str_repeat('☆', 5 - $rv->rating) ?></span>
                <span class="review-date"><?= date('d M Y', strtotime($rv->created_at)) ?></span>
            </div>
            <?php if ($rv->comment): ?>
            <p class="review-comment"><?= encode($rv->comment) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</main>

<script>
let pCurrent = 0;
const pTotal = <?= count($allPhotos) ?>;

function pGoTo(index) {
    pCurrent = (index + pTotal) % pTotal;
    document.getElementById('pslider-track').style.transform = `translateX(-${pCurrent * 100}%)`;
    document.querySelectorAll('.pslider-dot').forEach((d, i) => d.classList.toggle('active', i === pCurrent));
}
function pSlide(dir) { pGoTo(pCurrent + dir); }

<?php if (count($allPhotos) > 1): ?>
let pTimer = setInterval(() => pSlide(1), 3000);
const pSlider = document.querySelector('.pslider');
pSlider.addEventListener('mouseenter', () => clearInterval(pTimer));
pSlider.addEventListener('mouseleave', () => { pTimer = setInterval(() => pSlide(1), 3000); });
<?php endif; ?>
</script>

<?php require '_foot.php'; ?>
