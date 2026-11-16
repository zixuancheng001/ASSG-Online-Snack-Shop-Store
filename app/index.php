<?php
include_once "_base.php";
include "_head.php";

$featuredProduct = null;
$guestProducts   = [];
$memberCount     = 0;
$productCount    = 0;
$orderCount      = 0;
$totalSales      = 0.0;
$todaySales      = 0.0;
$recentOrders    = [];
$topProducts     = [];

try {
    $stm = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'Member'");
    $memberCount = (int)$stm->fetchColumn();

    $stm = $conn->query("SELECT COUNT(*) FROM products");
    $productCount = (int)$stm->fetchColumn();

    $stm = $conn->query("SELECT COUNT(*) FROM orders WHERE status != 'Cancelled'");
    $orderCount = (int)$stm->fetchColumn();
} catch (Exception $e) {}

try {
    // Total sales from completed orders
    $stm = $conn->query("
        SELECT COALESCE(SUM(o.total), 0)
        FROM orders o
        WHERE o.status = 'Completed'
    ");
    $totalSales = (float)$stm->fetchColumn();
} catch (Exception $e) {}

try {
    // Today's sales
    $stm = $conn->query("
        SELECT COALESCE(SUM(o.total), 0)
        FROM orders o
        JOIN payment p ON o.orders_id = p.order_id
        WHERE p.payment_status = 'Paid'
          AND DATE(p.created_at) = CURDATE()
    ");
    $todaySales = (float)$stm->fetchColumn();
} catch (Exception $e) {}

try {
    // Recent 5 orders
    $stm = $conn->query("
        SELECT o.orders_id, o.total, o.status,
               u.name AS member_name,
               p.payment_status
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN payment p ON o.orders_id = p.order_id
        ORDER BY o.orders_id DESC
        LIMIT 5
    ");
    $recentOrders = $stm->fetchAll();
} catch (Exception $e) {}

try {
    // Top 5 selling products (exclude cancelled orders)
    $stm = $conn->query("
        SELECT pr.product_id, pr.name, pr.image,
               SUM(oi.quantity) AS total_sold,
               SUM(oi.quantity * pr.price) AS revenue
        FROM order_item oi
        JOIN products pr ON oi.product_id = pr.product_id
        JOIN orders o ON oi.order_id = o.orders_id
        WHERE o.status != 'Cancelled'
        GROUP BY oi.product_id, pr.name, pr.image
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $topProducts = $stm->fetchAll();
} catch (Exception $e) {}

try {
    // Featured product for member/guest
    $stm = $conn->query("SELECT * FROM products ORDER BY RAND() LIMIT 1");
    $featuredProduct = $stm->fetch();

    $stm = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 3");
    $guestProducts = $stm->fetchAll();
} catch (Exception $e) {}
?>

<main class="shop-shell">
    <?php if ($_user && $_user->role === 'Admin'): ?>
    <section class="admin-home">

        <!-- Header Banner -->
        <div class="admin-dash-header">
            <div>
                <p class="eyebrow" style="color:rgba(255,255,255,0.65);">Admin Dashboard</p>
                <h1 style="color:#fff;font-size:clamp(20px,2.5vw,28px);margin:4px 0 6px;">
                    Hello, <?= encode(explode(' ', $_user->name)[0]) ?>!
                </h1>
                <p style="color:rgba(255,255,255,0.7);font-size:14px;">Here's what's happening in your store today.</p>
            </div>
            <a href="product_add.php" class="btn-blue" style="background:#fff;color:#2563eb;white-space:nowrap;">+ Add Product</a>
        </div>

        <!-- Summary Cards -->
        <section class="admin-summary-grid">
            <article class="admin-stat-card" style="--accent:#2563eb;">
                <div class="asc-icon">🍫</div>
                <div class="asc-body">
                    <span class="asc-label">Products</span>
                    <strong class="asc-value"><?= $productCount ?></strong>
                    <p class="asc-sub">In catalog</p>
                </div>
            </article>

            <article class="admin-stat-card" style="--accent:#0891b2;">
                <div class="asc-icon">👥</div>
                <div class="asc-body">
                    <span class="asc-label">Members</span>
                    <strong class="asc-value"><?= $memberCount ?></strong>
                    <p class="asc-sub">Registered</p>
                </div>
            </article>

            <article class="admin-stat-card" style="--accent:#7c3aed;">
                <div class="asc-icon">📋</div>
                <div class="asc-body">
                    <span class="asc-label">Total Orders</span>
                    <strong class="asc-value"><?= $orderCount ?></strong>
                    <p class="asc-sub">Completed orders</p>
                </div>
            </article>

            <article class="admin-stat-card" style="--accent:#059669;">
                <div class="asc-icon">💰</div>
                <div class="asc-body">
                    <span class="asc-label">Total Sales</span>
                    <strong class="asc-value" style="font-size:20px;">RM <?= number_format($totalSales, 2) ?></strong>
                    <p class="asc-sub">Revenue</p>
                </div>
            </article>
        </section>

        <!-- Top Selling Products -->
        <?php if ($topProducts): ?>
        <section class="admin-shortcut-card" style="padding:18px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="color:#2f567e;">Top Selling Products</h3>
                <a href="top_selling.php" class="text-link" style="font-size:13px;">View All →</a>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid #e8f0f8;text-align:left;">
                        <th style="padding:8px 10px;">Product</th>
                        <th style="padding:8px 10px;text-align:center;">Units Sold</th>
                        <th style="padding:8px 10px;text-align:right;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $i => $tp): ?>
                    <tr style="border-bottom:1px solid #f0f5fb;">
                        <td style="padding:10px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-weight:800;color:#5b8fbe;width:20px;">#<?= $i+1 ?></span>
                                <img src="images/<?= encode($tp->image ?: 'default_image.jpg') ?>"
                                     style="width:36px;height:36px;object-fit:cover;border-radius:8px;border:1px solid #dbe7f3;"
                                     onerror="this.onerror=null;this.src='images/default_image.jpg';">
                                <span style="font-weight:600;color:#2f567e;"><?= encode($tp->name) ?></span>
                            </div>
                        </td>
                        <td style="padding:10px;text-align:center;">
                            <span style="background:#eef6ff;color:#2d6ca4;padding:3px 10px;border-radius:999px;font-weight:700;font-size:13px;">
                                <?= $tp->total_sold ?> units
                            </span>
                        </td>
                        <td style="padding:10px;text-align:right;font-weight:700;color:#2d6ca4;">
                            RM <?= number_format($tp->revenue, 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <!-- Recent Orders -->
        <?php if ($recentOrders): ?>
        <section class="admin-shortcut-card" style="padding:18px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="color:#2f567e;">Recent Orders</h3>
                <a href="order_listing.php" class="text-link" style="font-size:13px;">View All →</a>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid #e8f0f8;text-align:left;">
                        <th style="padding:8px 10px;">Order #</th>
                        <th style="padding:8px 10px;">Member</th>
                        <th style="padding:8px 10px;text-align:right;">Total</th>
                        <th style="padding:8px 10px;text-align:center;">Payment</th>
                        <th style="padding:8px 10px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $ro): ?>
                    <?php
                        $displayPs = match($ro->status) {
                            'Completed' => 'Paid',
                            'Cancelled' => 'Refunded',
                            default     => 'Unpaid',
                        };
                        $badge = match($displayPs) {
                            'Paid'     => 'background:#d4edda;color:#155724;',
                            'Refunded' => 'background:#f8d7da;color:#721c24;',
                            default    => 'background:#fff3cd;color:#856404;',
                        };
                    ?>
                    <tr style="border-bottom:1px solid #f0f5fb;">
                        <td style="padding:10px;font-weight:700;color:#2f567e;">#<?= $ro->orders_id ?></td>
                        <td style="padding:10px;"><?= encode($ro->member_name) ?></td>
                        <td style="padding:10px;text-align:right;font-weight:600;">RM <?= number_format($ro->total, 2) ?></td>
                        <td style="padding:10px;text-align:center;">
                            <span style="<?= $badge ?> padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;">
                                <?= ucfirst($displayPs) ?>
                            </span>
                        </td>
                        <td style="padding:10px;text-align:center;">
                            <a href="order_detail.php?id=<?= $ro->orders_id ?>" class="text-link" style="font-size:13px;">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <!-- Shortcut Cards -->
        <section class="member-quick-panel">
            <a href="product_list.php" class="member-quick-tile">
                <div class="mq-icon">🍫</div>
                <div class="mq-body">
                    <strong><?= $productCount ?> products</strong>
                    <span>Product Management</span>
                </div>
                <span class="mq-arrow">→</span>
            </a>

            <a href="member_list.php" class="member-quick-tile">
                <div class="mq-icon">👥</div>
                <div class="mq-body">
                    <strong><?= $memberCount ?> members</strong>
                    <span>Member Management</span>
                </div>
                <span class="mq-arrow">→</span>
            </a>

            <a href="order_listing.php" class="member-quick-tile">
                <div class="mq-icon">📋</div>
                <div class="mq-body">
                    <strong><?= $orderCount ?> orders</strong>
                    <span>Order Management</span>
                </div>
                <span class="mq-arrow">→</span>
            </a>

            <a href="product_add.php" class="member-quick-tile">
                <div class="mq-icon">➕</div>
                <div class="mq-body">
                    <strong>Add Product</strong>
                    <span>Create new listing</span>
                </div>
                <span class="mq-arrow">→</span>
            </a>
        </section>
    </section>
    <?php elseif ($_user && $_user->role === 'Member'): ?>
    <section class="member-hero-banner">
        <div class="member-hero-left">
            <p class="eyebrow" style="color:rgba(255,255,255,0.7);">Welcome back</p>
            <h1 style="color:#fff;font-size:clamp(24px,3vw,36px);margin:6px 0 10px;">
                Hi, <?= encode(explode(' ', $_user->name)[0]) ?>!
            </h1>
            <p style="color:rgba(255,255,255,0.8);font-size:15px;margin-bottom:20px;">
                Ready for your next snack fix?
            </p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="product_list.php" class="btn-blue action-link" style="background:#fff;color:#2d6ca4;">Browse Menu</a>
                <a href="order_history.php" class="action-link" style="color:rgba(255,255,255,0.9);font-weight:600;padding:10px 0;">My Orders →</a>
            </div>
        </div>

        <div class="member-hero-right">
            <?php if ($featuredProduct): ?>
            <div class="member-featured-card">
                <img src="images/<?= encode($featuredProduct->image ?: 'default_image.jpg') ?>"
                     style="width:80px;height:80px;object-fit:cover;border-radius:12px;flex-shrink:0;"
                     onerror="this.onerror=null;this.src='images/default_image.jpg';"
                     alt="<?= encode($featuredProduct->name) ?>">
                <div>
                    <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.6);">Featured Today</span>
                    <h3 style="color:#fff;margin:4px 0 4px;font-size:16px;"><?= encode($featuredProduct->name) ?></h3>
                    <p style="color:rgba(255,255,255,0.75);font-size:13px;margin:0 0 8px;"><?= encode(mb_strimwidth($featuredProduct->description ?? '', 0, 50, '...')) ?></p>
                    <a href="product_detail.php?id=<?= $featuredProduct->product_id ?>"
                       style="font-size:13px;font-weight:700;color:#fff;background:rgba(255,255,255,0.2);padding:5px 12px;border-radius:999px;text-decoration:none;">
                        RM <?= number_format($featuredProduct->price, 2) ?> — Order Now
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
                <a href="shopping_cart.php" class="member-stat-pill">
                    <span style="font-size:20px;">🛒</span>
                    <span>My Cart</span>
                </a>
                <a href="wishlist.php" class="member-stat-pill">
                    <span style="font-size:20px;">♥</span>
                    <span>Wishlist</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Top Selling Products for Member -->
    <?php if ($topProducts): ?>
    <section class="guest-section" style="margin-top:16px;">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Most Popular</p>
                <h2>🔥 Top Selling Snacks</h2>
            </div>
        </div>
        <div class="product-grid simple-grid">
            <?php foreach (array_slice($topProducts, 0, 4) as $i => $tp): ?>
            <article class="product-card">
                <div class="product-thumb-wrap" style="padding:0;overflow:hidden;border-radius:14px 14px 0 0;position:relative;">
                    <img src="images/<?= encode($tp->image ?: 'default_image.jpg') ?>"
                         class="product-thumb"
                         alt="<?= encode($tp->name) ?>"
                         onerror="this.onerror=null;this.src='images/default_image.jpg';">
                    <span class="product-badge" style="background:#2d7dd2;color:#fff;">#<?= $i+1 ?></span>
                </div>
                <div class="product-body">
                    <div class="product-meta">
                        <h3><?= encode($tp->name) ?></h3>
                        <p style="color:#2d6ca4;font-weight:600;font-size:13px;"><?= $tp->total_sold ?> sold</p>
                    </div>
                    <div class="product-card-footer">
                        <strong class="product-price">RM <?= number_format($tp->revenue / $tp->total_sold, 2) ?></strong>
                        <a href="product_detail.php?id=<?= $tp->product_id ?>" class="text-link">Order Now</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <?php else: ?>
    <section class="guest-home">
        <section class="landing-hero guest-landing-hero">
            <div class="landing-copy">
                <p class="eyebrow">Snack Paradise</p>
                <span class="hero-badge">Fresh picks every day</span>
                <h1>Your snack stop for fast cravings, sweet treats, and easy ordering.</h1>
                <div class="hero-actions">
                    <a href="register.php" class="btn-blue action-link">Create Account</a>
                    <a href="login.php" class="ghost-link action-link">Login</a>
                </div>
            </div>

                <div class="showcase-card alt info-card">
                    <span class="showcase-tag">Why Join</span>
                    <h3>Designed for members who want convenience</h3>
                    <p>Keep up with your snack choices, and enjoy a smoother experience the moment you sign in.</p>
                    <div class="mini-pill-row">
                        <span class="mini-pill">Fast access</span>
                        <span class="mini-pill">Clean profile</span>
                        <span class="mini-pill">Easy tracking</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="guest-section">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Latest Arrivals</p>
                    <h2>Fresh snacks now in the catalog</h2>
                </div>
                 </div>

            <div class="product-grid simple-grid">
                <?php foreach ($guestProducts as $p): ?>
                <article class="product-card guest-product-card">
                    <div class="product-thumb-wrap">
                        <img src="images/<?= encode($p->image ?: 'default_image.jpg') ?>" class="product-thumb" alt="Product photo #<?= $p->id ?>" onerror="this.onerror=null;this.src='images/default_image.jpg';">
                        <span class="product-badge">New</span>
                    </div>

                    <div class="product-body">
                        <div class="product-meta">
                            <h3><?= encode($p->name) ?></h3>
                            <p><?= encode(mb_strimwidth($p->description ?: 'No description yet.', 0, 90, '...')) ?></p>
                        </div>

                        <div class="product-card-footer">
                            <strong class="product-price">RM <?= number_format($p->price, 2) ?></strong>
                            <a href="register.php" class="text-link">Join to explore</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>

                <?php if (!$guestProducts): ?>
                <div class="empty-state">
                    <h3>No snacks yet</h3>
                    <p>Add a few products and this area will automatically become your public storefront preview.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="guest-promo-band">
            <div>
                <p class="eyebrow">Start Here</p>
                <h2>Join Snack Paradise and enjoy snack now!</h2>
            </div>
            <div class="promo-actions">
                <a href="register.php" class="btn-blue action-link">Register Now</a>
                <a href="login.php" class="text-link">Already have an account?</a>
            </div>
        </section>
    </section>
    <?php endif; ?>

    <?php if ($_user && $_user->role === 'Member'): ?>
    <?php
        // Quick stats for member
        $stmCart = $conn->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
        $stmCart->execute([$_user->user_id]);
        $cartQty = (int)$stmCart->fetchColumn();

        $stmOrders = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmOrders->execute([$_user->user_id]);
        $totalOrders = (int)$stmOrders->fetchColumn();

        $stmWish = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmWish->execute([$_user->user_id]);
        $wishCount = (int)$stmWish->fetchColumn();

        $stmPending = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status != 'Completed' AND status != 'Cancelled'");
        $stmPending->execute([$_user->user_id]);
        $pendingOrders = (int)$stmPending->fetchColumn();
    ?>
    <section class="member-quick-panel">
        <a href="shopping_cart.php" class="member-quick-tile">
            <div class="mq-icon">🛒</div>
            <div class="mq-body">
                <strong><?= $cartQty ?> item<?= $cartQty !== 1 ? 's' : '' ?></strong>
                <span>Shopping Cart</span>
            </div>
            <span class="mq-arrow">→</span>
        </a>

        <a href="order_history.php" class="member-quick-tile">
            <div class="mq-icon">📦</div>
            <div class="mq-body">
                <strong><?= $totalOrders ?> order<?= $totalOrders !== 1 ? 's' : '' ?></strong>
                <span>Order History<?= $pendingOrders > 0 ? " · <em style='color:#e05c7a;font-style:normal;'>{$pendingOrders} unpaid</em>" : '' ?></span>
            </div>
            <span class="mq-arrow">→</span>
        </a>

        <a href="wishlist.php" class="member-quick-tile">
            <div class="mq-icon">♥</div>
            <div class="mq-body">
                <strong><?= $wishCount ?> saved</strong>
                <span>My Wishlist</span>
            </div>
            <span class="mq-arrow">→</span>
        </a>

        <a href="profile.php" class="member-quick-tile">
            <div class="mq-icon">👤</div>
            <div class="mq-body">
                <strong><?= encode(explode(' ', $_user->name)[0]) ?></strong>
                <span>My Profile</span>
            </div>
            <span class="mq-arrow">→</span>
        </a>
    </section>
    <?php endif; ?>
</main>

<?php include "_foot.php"; ?>
