<?php
require '_base.php';
auth('Admin', 'Member');
require '_head.php';

$search   = get('search');
$category = get('category');
$minPrice = get('min_price');
$maxPrice = get('max_price');
$sort     = get('sort') ?: 'default';
$page     = max(1, (int)get('page'));
$perPage  = 8;

$deleteId = (int)get('delete');
if ($deleteId) {
    $stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
    $stmt->execute([$deleteId]);
    temp('info', 'Product deleted successfully.');
    redirect('product_list.php');
}

// Sort options
$sortOptions = [
    'default'    => 'Default',
    'name_asc'   => 'Name A-Z',
    'name_desc'  => 'Name Z-A',
    'price_asc'  => 'Price: Low to High',
    'price_desc' => 'Price: High to Low',
];
$orderBy = match($sort) {
    'name_asc'   => 'name ASC',
    'name_desc'  => 'name DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    default      => 'product_id ASC',
};

// Build WHERE
$where  = [];
$params = [];
if ($search) {
    $where[]  = '(name LIKE ? OR description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $where[]  = 'category = ?';
    $params[] = $category;
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $where[]  = 'price >= ?';
    $params[] = (float)$minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $where[]  = 'price <= ?';
    $params[] = (float)$maxPrice;
}
$whereSQL = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// Total count for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) FROM products$whereSQL");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalCount / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// Fetch current page
$sql  = "SELECT * FROM products$whereSQL ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products     = $stmt->fetchAll();
$productCount = $totalCount;

$categories = ['Chocolate', 'Chips', 'Ice Cream', 'Cookies', 'Drinks', 'Cake','Others'];

// Load product_images for this page only
$productIds = array_map(fn($p) => $p->product_id, $products);
$photoMap   = [];
if ($productIds) {
    $ph   = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $conn->prepare("SELECT product_id, filename FROM product_images WHERE product_id IN ($ph) ORDER BY sort_order ASC");
    $stmt->execute($productIds);
    foreach ($stmt->fetchAll() as $row) {
        $photoMap[$row->product_id][] = $row->filename;
    }
}

// Wishlist for member
$wishlistIds = [];
if ($_user && $_user->role === 'Member' && $productIds) {
    $ph2  = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ? AND product_id IN ($ph2)");
    $stmt->execute([$_user->user_id, ...$productIds]);
    $wishlistIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'product_id');
}

function getPhotos($p, $photoMap) {
    return !empty($photoMap[$p->product_id]) ? $photoMap[$p->product_id] : [$p->image ?: 'default_image.jpg'];
}

// Build base query string (preserve all filters for sort/page links)
function buildQuery($overrides = []) {
    global $search, $category, $minPrice, $maxPrice, $sort, $page;
    $params = array_merge([
        'search'    => $search,
        'category'  => $category,
        'min_price' => $minPrice,
        'max_price' => $maxPrice,
        'sort'      => $sort,
        'page'      => $page,
    ], $overrides);
    return '?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== 0 && $v !== '0' || $v === '0'));
}
?>

<main class="shop-shell">
    <?php if ($_user && $_user->role === 'Admin'): ?>
    <section class="admin-panel">
        <div class="panel-header">
            <div>
                <h1>Product Catalog</h1>
                <p class="panel-subtitle"><?= $productCount ?> product(s) found</p>
            </div>
            <a href="product_add.php" class="btn-blue action-link">+ Add Product</a>
        </div>

        <form method="get" id="filter-form-admin">
            <!-- Top bar: search + sort + filter trigger -->
            <div class="filter-topbar">
                <input name="search" placeholder="Search products..." value="<?= encode($search) ?>" class="filter-search-input">
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <?php foreach ($sortOptions as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="filter-trigger-btn <?= ($category || $minPrice !== '' || $maxPrice !== '') ? 'has-filter' : '' ?>" onclick="toggleFilterPanel('filter-panel-admin')">
                    ⚙ Filter <?= ($category || $minPrice !== '' || $maxPrice !== '') ? '<span class="filter-dot"></span>' : '' ?>
                </button>
                <?php if ($search || $category || $minPrice !== '' || $maxPrice !== '' || $sort !== 'default'): ?>
                <a href="product_list.php" class="text-link" style="white-space:nowrap;">✕ Reset</a>
                <?php endif; ?>
            </div>

            <!-- Filter dropdown panel -->
            <div class="filter-panel" id="filter-panel-admin" style="display:none;">
                <div class="filter-panel-grid">
                    <div class="filter-panel-group">
                        <label class="filter-panel-label">Category</label>
                        <select name="category" class="filter-select" style="width:100%;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-panel-group">
                        <label class="filter-panel-label">Min Price (RM)</label>
                        <input type="number" name="min_price" placeholder="0" value="<?= encode($minPrice) ?>" min="0" step="1" class="filter-select" style="width:100%;">
                    </div>
                    <div class="filter-panel-group">
                        <label class="filter-panel-label">Max Price (RM)</label>
                        <input type="number" name="max_price" placeholder="Any" value="<?= encode($maxPrice) ?>" min="0" step="1" class="filter-select" style="width:100%;">
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="submit" class="btn-blue">Apply</button>
                    <a href="product_list.php" class="text-link" style="align-self:center;">Clear Filters</a>
                </div>
            </div>
        </form>

        <div class="category-pills">
            <a href="product_list.php?search=<?= urlencode($search) ?>&min_price=<?= urlencode($minPrice) ?>&max_price=<?= urlencode($maxPrice) ?>"
               class="cat-pill <?= !$category ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="product_list.php?search=<?= urlencode($search) ?>&category=<?= urlencode($cat) ?>&min_price=<?= urlencode($minPrice) ?>&max_price=<?= urlencode($maxPrice) ?>"
               class="cat-pill <?= $category === $cat ? 'active' : '' ?>"><?= $cat ?></a>
            <?php endforeach; ?>
        </div>

        <!-- View Toggle (Admin only) -->
        <div style="display:flex;justify-content:flex-end;margin-top:10px;">
            <div class="view-toggle-wrap">
                <button class="view-toggle-btn active" id="btn-photo" onclick="setView('photo')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Photo
                </button>
                <button class="view-toggle-btn" id="btn-table" onclick="setView('table')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    Table
                </button>
            </div>
        </div>
    </section>

    <!-- Photo View -->
    <section class="product-grid simple-grid" id="view-photo">
        <?php $displayNo = ($page - 1) * $perPage + 1; ?>
        <?php foreach($products as $p): ?>
        <?php $photos = getPhotos($p, $photoMap); ?>
        <article class="product-card">
            <div class="product-thumb-wrap" style="padding:0;overflow:hidden;border-radius:14px 14px 0 0;">
                <div class="card-slider" data-id="<?= $p->product_id ?>">
                    <div class="card-slider-track">
                        <?php foreach ($photos as $photo): ?>
                        <div class="card-slider-slide">
                            <img src="images/<?= encode($photo) ?>"
                                 class="product-thumb"
                                 alt="<?= encode($p->name) ?>"
                                 onerror="this.onerror=null;this.src='images/default_image.jpg';">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($photos) > 1): ?>
                    <div class="card-slider-dots">
                        <?php foreach ($photos as $i => $photo): ?>
                        <span class="card-slider-dot <?= $i === 0 ? 'active' : '' ?>"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <span class="product-badge">#<?= $displayNo ?></span>
            </div>
            <div class="product-body">
                <div class="product-meta">
                    <h3><?= encode($p->name) ?></h3>
                    <p><?= encode(mb_strimwidth($p->description ?: 'No description yet.', 0, 80, '...')) ?></p>
                </div>
                <div class="product-card-footer">
                    <strong class="product-price">RM <?= number_format($p->price, 2) ?></strong>
                    <div class="card-actions">
                        <a href="product_detail.php?id=<?= $p->product_id ?>" class="text-link">View</a>
                        <a href="product_edit.php?id=<?= $p->product_id ?>" class="text-link">Edit</a>
                        <a href="product_list.php?delete=<?= $p->product_id ?>" class="text-link danger-link" onclick="return confirm('Delete this product?')">Delete</a>
                    </div>
                </div>
            </div>
        </article>
        <?php $displayNo++; ?>
        <?php endforeach; ?>
        <?php if (!$products): ?>
        <div class="empty-state"><h3>No products found</h3><p>Try a different keyword or add a new snack to the catalog.</p></div>
        <?php endif; ?>
    </section>

    <!-- Table View -->
    <section id="view-table" style="display:none;">
        <?php if ($products): ?>
        <div class="admin-panel" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e8eef5;">
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">#</th>
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Photo</th>
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Name</th>
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Category</th>
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Description</th>
                        <th style="padding:12px 14px;text-align:right;font-weight:600;color:#4a6278;">Price</th>
                        <th style="padding:12px 14px;text-align:center;font-weight:600;color:#4a6278;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $displayNo = ($page - 1) * $perPage + 1; ?>
                    <?php foreach($products as $p): ?>
                    <?php $photos = getPhotos($p, $photoMap); ?>
                    <tr style="border-bottom:1px solid #f0f4f8;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:12px 14px;color:#8a9bb0;font-size:12px;"><?= $displayNo ?></td>
                        <td style="padding:10px 14px;">
                            <img src="images/<?= encode($photos[0]) ?>"
                                 style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e8eef5;"
                                 onerror="this.onerror=null;this.src='images/default_image.jpg';">
                        </td>
                        <td style="padding:12px 14px;font-weight:600;color:#1a2e42;"><?= encode($p->name) ?></td>
                        <td style="padding:12px 14px;">
                            <span style="background:#eef4ff;color:#2563eb;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;"><?= encode($p->category) ?></span>
                        </td>
                        <td style="padding:12px 14px;color:#7a8fa3;max-width:200px;"><?= encode(mb_strimwidth($p->description ?: '-', 0, 60, '...')) ?></td>
                        <td style="padding:12px 14px;text-align:right;font-weight:700;color:#2563eb;">RM <?= number_format($p->price, 2) ?></td>
                        <td style="padding:12px 14px;text-align:center;">
                            <div style="display:flex;gap:8px;justify-content:center;">
                                <a href="product_detail.php?id=<?= $p->product_id ?>" class="text-link" style="font-size:13px;">View</a>
                                <a href="product_edit.php?id=<?= $p->product_id ?>" class="text-link" style="font-size:13px;">Edit</a>
                                <a href="product_list.php?delete=<?= $p->product_id ?>" class="text-link danger-link" style="font-size:13px;" onclick="return confirm('Delete this product?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php $displayNo++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><h3>No products found</h3><p>Try a different keyword or add a new snack to the catalog.</p></div>
        <?php endif; ?>
    </section>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="product_list.php<?= buildQuery(['page' => $page - 1]) ?>" class="page-btn">← Prev</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="product_list.php<?= buildQuery(['page' => $i]) ?>"
           class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="product_list.php<?= buildQuery(['page' => $page + 1]) ?>" class="page-btn">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php elseif ($_user && $_user->role === 'Member'): ?>
        <section class="admin-panel">
        <div class="panel-header">
            <div>
                <h1>Snack Menu</h1>
                <p class="panel-subtitle"><?= $productCount ?> product(s) found</p>
            </div>
        </div>

        <form method="get" id="filter-form-member">
            <div class="filter-topbar">
                <input name="search" placeholder="Search snacks..." value="<?= encode($search) ?>" class="filter-search-input">
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <?php foreach ($sortOptions as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="filter-trigger-btn <?= ($category || $minPrice !== '' || $maxPrice !== '') ? 'has-filter' : '' ?>" onclick="toggleFilterPanel('filter-panel-member')">
                    ⚙ Filter <?= ($category || $minPrice !== '' || $maxPrice !== '') ? '<span class="filter-dot"></span>' : '' ?>
                </button>
                <?php if ($search || $category || $minPrice !== '' || $maxPrice !== '' || $sort !== 'default'): ?>
                <a href="product_list.php" class="text-link" style="white-space:nowrap;">✕ Reset</a>
                <?php endif; ?>
            </div>

            <div class="filter-panel" id="filter-panel-member" style="display:none;">
                <div class="filter-panel-grid">
                    <div class="filter-panel-group">
                        <label class="filter-panel-label">Category</label>
                        <select name="category" class="filter-select" style="width:100%;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-panel-group">
                        <label class="filter-panel-label">Min Price (RM)</label>
                        <input type="number" name="min_price" placeholder="0" value="<?= encode($minPrice) ?>" min="0" step="1" class="filter-select" style="width:100%;">
                    </div>
                    <div class="filter-panel-group">
                        <label class="filter-panel-label">Max Price (RM)</label>
                        <input type="number" name="max_price" placeholder="Any" value="<?= encode($maxPrice) ?>" min="0" step="1" class="filter-select" style="width:100%;">
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="submit" class="btn-blue">Apply</button>
                    <a href="product_list.php" class="text-link" style="align-self:center;">Clear Filters</a>
                </div>
            </div>
        </form>

        <div class="category-pills">
            <a href="product_list.php?search=<?= urlencode($search) ?>&min_price=<?= urlencode($minPrice) ?>&max_price=<?= urlencode($maxPrice) ?>"
               class="cat-pill <?= !$category ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="product_list.php?search=<?= urlencode($search) ?>&category=<?= urlencode($cat) ?>&min_price=<?= urlencode($minPrice) ?>&max_price=<?= urlencode($maxPrice) ?>"
               class="cat-pill <?= $category === $cat ? 'active' : '' ?>"><?= $cat ?></a>
            <?php endforeach; ?>
        </div>

        <!-- View Toggle (Member) -->
        <div style="display:flex;justify-content:flex-end;margin-top:10px;">
            <div class="view-toggle-wrap">
                <button class="view-toggle-btn active" id="mbtn-photo" onclick="setMemberView('photo')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Photo
                </button>
                <button class="view-toggle-btn" id="mbtn-list" onclick="setMemberView('list')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    List
                </button>
            </div>
        </div>
    </section>

    <!-- Member Photo View -->
    <section class="product-grid simple-grid" id="mview-photo">
        <?php $displayNo = ($page - 1) * $perPage + 1 ?>
        <?php foreach($products as $p): ?>
        <?php $photos = getPhotos($p, $photoMap); ?>
        <article class="product-card">
            <div class="product-thumb-wrap" style="padding:0;overflow:hidden;border-radius:14px 14px 0 0;">
                <div class="card-slider" data-id="<?= $p->product_id ?>">
                    <div class="card-slider-track">
                        <?php foreach ($photos as $photo): ?>
                        <div class="card-slider-slide">
                            <img src="images/<?= encode($photo) ?>"
                                 class="product-thumb"
                                 alt="<?= encode($p->name) ?>"
                                 onerror="this.onerror=null;this.src='images/default_image.jpg';">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($photos) > 1): ?>
                    <div class="card-slider-dots">
                        <?php foreach ($photos as $i => $photo): ?>
                        <span class="card-slider-dot <?= $i === 0 ? 'active' : '' ?>"></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <span class="product-badge">#<?= $displayNo ?></span>
            </div>
            <div class="product-body">
                <div class="product-meta">
                    <h3><?= encode($p->name) ?></h3>
                    <p><?= encode(mb_strimwidth($p->description ?: 'No description yet.', 0, 80, '...')) ?></p>
                </div>
                <div class="product-card-footer">
                    <strong class="product-price">RM <?= number_format($p->price, 2) ?></strong>
                    <div class="card-actions">
                        <a href="product_detail.php?id=<?= $p->product_id ?>" class="text-link">View details</a>
                        <a href="add_order.php?id=<?= $p->product_id ?>" class="text-link">Add to Cart</a>
                        <a href="wishlist.php?toggle=<?= $p->product_id ?>&back=<?= urlencode('product_list.php'.buildQuery()) ?>"
                           class="wishlist-btn <?= in_array($p->product_id, $wishlistIds) ? 'wishlisted' : '' ?>">
                            <?= in_array($p->product_id, $wishlistIds) ? '♥' : '♡' ?>
                        </a>
                    </div>
                </div>
            </div>
        </article>
        <?php $displayNo++; ?>
        <?php endforeach; ?>
        <?php if (!$products): ?>
        <div class="empty-state"><h3>No products found</h3><p>Try a different keyword or add a new snack to the catalog.</p></div>
        <?php endif; ?>
    </section>

    <!-- Member List View -->
    <section id="mview-list" style="display:none;">
        <?php if ($products): ?>
        <div class="admin-panel" style="padding:0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e8eef5;">
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Photo</th>
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Name</th>
                        <th style="padding:12px 14px;text-align:left;font-weight:600;color:#4a6278;">Category</th>
                        <th style="padding:12px 14px;text-align:right;font-weight:600;color:#4a6278;">Price</th>
                        <th style="padding:12px 14px;text-align:center;font-weight:600;color:#4a6278;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <?php $photos = getPhotos($p, $photoMap); ?>
                    <tr style="border-bottom:1px solid #f0f4f8;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                        <td style="padding:10px 14px;">
                            <img src="images/<?= encode($photos[0]) ?>"
                                 style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e8eef5;"
                                 onerror="this.onerror=null;this.src='images/default_image.jpg';">
                        </td>
                        <td style="padding:12px 14px;font-weight:600;color:#1a2e42;"><?= encode($p->name) ?></td>
                        <td style="padding:12px 14px;">
                            <span style="background:#eef4ff;color:#2563eb;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;"><?= encode($p->category) ?></span>
                        </td>
                        <td style="padding:12px 14px;text-align:right;font-weight:700;color:#2563eb;">RM <?= number_format($p->price, 2) ?></td>
                        <td style="padding:12px 14px;text-align:center;">
                            <div style="display:flex;gap:8px;justify-content:center;align-items:center;">
                                <a href="product_detail.php?id=<?= $p->product_id ?>" class="text-link" style="font-size:13px;">View</a>
                                <a href="add_order.php?id=<?= $p->product_id ?>" class="text-link" style="font-size:13px;">Add to Cart</a>
                                <a href="wishlist.php?toggle=<?= $p->product_id ?>&back=<?= urlencode('product_list.php'.buildQuery()) ?>"
                                   class="wishlist-btn <?= in_array($p->product_id, $wishlistIds) ? 'wishlisted' : '' ?>" style="font-size:16px;">
                                    <?= in_array($p->product_id, $wishlistIds) ? '♥' : '♡' ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><h3>No products found</h3><p>Try a different keyword or add a new snack to the catalog.</p></div>
        <?php endif; ?>
    </section>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="product_list.php<?= buildQuery(['page' => $page - 1]) ?>" class="page-btn">← Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="product_list.php<?= buildQuery(['page' => $i]) ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="product_list.php<?= buildQuery(['page' => $page + 1]) ?>" class="page-btn">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <section class="admin-panel" style="margin-top:20px;">
            <div class="panel-header">
                <strong style="font-size:1.2rem;"></strong>
                <a href="shopping_cart.php" class="btn-blue action-link">View Cart</a>
            </div>
        </section>
        <?php endif; ?>
</main>

<script>
// Card mini-slider auto-play
document.querySelectorAll('.card-slider').forEach(slider => {
    const track = slider.querySelector('.card-slider-track');
    const dots  = slider.querySelectorAll('.card-slider-dot');
    if (!track || dots.length < 2) return;

    let cur   = 0;
    const total = dots.length;

    function goTo(i) {
        cur = (i + total) % total;
        track.style.transform = `translateX(-${cur * 100}%)`;
        dots.forEach((d, idx) => d.classList.toggle('active', idx === cur));
    }

    let timer = setInterval(() => goTo(cur + 1), 3000);
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', () => { timer = setInterval(() => goTo(cur + 1), 3000); });
});

function toggleFilterPanel(id) {
    const panel = document.getElementById(id);
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// View toggle (Admin)
function setView(view) {
    localStorage.setItem('productView', view);
    document.getElementById('view-photo').style.display = view === 'photo' ? '' : 'none';
    document.getElementById('view-table').style.display = view === 'table' ? '' : 'none';
    document.getElementById('btn-photo').classList.toggle('active', view === 'photo');
    document.getElementById('btn-table').classList.toggle('active', view === 'table');
}

// View toggle (Member)
function setMemberView(view) {
    localStorage.setItem('memberProductView', view);
    document.getElementById('mview-photo').style.display = view === 'photo' ? '' : 'none';
    document.getElementById('mview-list').style.display  = view === 'list'  ? '' : 'none';
    document.getElementById('mbtn-photo').classList.toggle('active', view === 'photo');
    document.getElementById('mbtn-list').classList.toggle('active',  view === 'list');
}

// Restore saved view
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('productView') || 'photo';
    if (document.getElementById('view-photo')) setView(saved);

    const msaved = localStorage.getItem('memberProductView') || 'photo';
    if (document.getElementById('mview-photo')) setMemberView(msaved);
    else if (document.getElementById('mview-photo') === null) {
        // member view not on page, skip
    }
});

// Close panel when clicking outside
document.addEventListener('click', function(e) {
    ['filter-panel-admin', 'filter-panel-member'].forEach(id => {
        const panel = document.getElementById(id);
        if (!panel) return;
        const btn = panel.previousElementSibling?.querySelector('.filter-trigger-btn');
        if (panel.style.display === 'block' && !panel.contains(e.target) && e.target !== btn && !btn?.contains(e.target)) {
            panel.style.display = 'none';
        }
    });
});

// Auto-open panel if filters are active
<?php if ($category || $minPrice !== '' || $maxPrice !== ''): ?>
document.addEventListener('DOMContentLoaded', () => {
    const panel = document.getElementById('filter-panel-admin') || document.getElementById('filter-panel-member');
    if (panel) panel.style.display = 'block';
});
<?php endif; ?>
</script>

<?php require '_foot.php'; ?>
