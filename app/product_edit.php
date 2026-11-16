<?php
require '_base.php';
auth('Admin');

$id = (int)get('id');
$p  = get_product($id);

if (!$p) {
    temp('info', 'Product not found.');
    redirect('product_list.php');
}

$categories = ['Chocolate', 'Chips', 'Ice Cream', 'Cookies', 'Drinks', 'Cake', 'Others'];

// Handle delete single extra photo
$delImg = (int)get('del_img');
if ($delImg) {
    $stmt = $conn->prepare("SELECT filename FROM product_images WHERE image_id = ? AND product_id = ?");
    $stmt->execute([$delImg, $id]);
    $row = $stmt->fetch();
    if ($row) {
        $file = "images/{$row->filename}";
        if (file_exists($file) && $row->filename !== 'default_image.jpg') @unlink($file);
        $conn->prepare("DELETE FROM product_images WHERE image_id = ?")->execute([$delImg]);
    }
    temp('info', 'Photo removed.');
    redirect("product_edit.php?id=$id");
}

if (is_post()) {
    $name        = post('name');
    $description = post('description');
    $price       = post('price');
    $category    = post('category');
    $image       = $p->image ?: 'default_image.jpg';

    if (!$name)                                          $_err['name']     = 'Required';
    if ($price === '')                                   $_err['price']    = 'Required';
    elseif (!is_numeric($price) || $price < 0)          $_err['price']    = 'Invalid price';
    if (!$category || !in_array($category, $categories)) $_err['category'] = 'Required';

    // Replace main image if new photos uploaded — first photo becomes new cover
    if (!empty($_FILES['images']['name'][0])) {
        $firstDone = false;
        // Get current max sort_order
        $maxStmt = $conn->prepare("SELECT COALESCE(MAX(sort_order),0) FROM product_images WHERE product_id = ?");
        $maxStmt->execute([$id]);
        $offset = (int)$maxStmt->fetchColumn();

        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === 0) {
                $fname = uniqid() . '.jpg';
                move_uploaded_file($tmp, "images/$fname");

                // First new photo replaces the cover
                if (!$firstDone) {
                    $image = $fname;
                    $firstDone = true;
                }

                $stmt2 = $conn->prepare("INSERT INTO product_images (product_id, filename, sort_order) VALUES (?, ?, ?)");
                $stmt2->execute([$id, $fname, $offset + $i + 1]);
            }
        }
    }

    if (!$_err) {
        $stmt = $conn->prepare("
            UPDATE products
            SET name = ?, description = ?, price = ?, category = ?, image = ?
            WHERE product_id = ?
        ");
        $stmt->execute([$name, $description, $price, $category, $image, $id]);
        temp('info', 'Product updated successfully.');
        redirect('product_detail.php?id=' . $id);
    }
}

// Load existing extra photos
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$stmt->execute([$id]);
$extraPhotos = $stmt->fetchAll();

require '_head.php';
?>

<main>
    <div class="card" style="max-width:680px;">
        <h1>Edit Product</h1>

        <form method="POST" enctype="multipart/form-data" class="form">
            <?php $name        = $name        ?? $p->name; ?>
            <?php $description = $description ?? $p->description; ?>
            <?php $price       = $price       ?? $p->price; ?>
            <?php $category    = $category    ?? $p->category; ?>

            <div class="form-row">
                <label for="name">Name:</label>
                <div class="field-group">
                    <?php html_text('name', 'maxlength="100"'); ?>
                    <?php err('name'); ?>
                </div>
            </div>

            <div class="form-row">
                <label for="description">Description:</label>
                <div class="field-group">
                    <textarea id="description" name="description"><?= encode($description) ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <label for="category">Category:</label>
                <div class="field-group">
                    <select id="category" name="category">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($category === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php err('category'); ?>
                </div>
            </div>

            <div class="form-row">
                <label for="price">Price:</label>
                <div class="field-group">
                    <input id="price" name="price" value="<?= encode($price) ?>" type="number" min="0" step="0.01">
                    <?php err('price'); ?>
                </div>
            </div>

            <!-- Current photos -->
            <?php if ($extraPhotos): ?>
            <div class="form-row">
                <label>Current Photos:</label>
                <div class="field-group">
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        <?php foreach ($extraPhotos as $ep): ?>
                        <div style="position:relative;display:inline-block;">
                            <img src="images/<?= encode($ep->filename) ?>"
                                 style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #cddff0;"
                                 alt="Gallery photo"
                                 onerror="this.onerror=null;this.src='images/default_image.jpg';">
                            <a href="product_edit.php?id=<?= $id ?>&del_img=<?= $ep->image_id ?>"
                               onclick="return confirm('Remove this photo?')"
                               style="position:absolute;top:-6px;right:-6px;background:#c94343;color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;text-decoration:none;">×</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small style="color:#6a7f93;margin-top:6px;display:block;">Click × to remove a photo.</small>
                </div>
            </div>
            <?php else: ?>
            <div class="form-row">
                <label>Current Photos:</label>
                <div class="field-group">
                    <img src="images/<?= encode($p->image ?: 'default_image.jpg') ?>"
                         style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #cddff0;"
                         alt="Cover photo">
                </div>
            </div>
            <?php endif; ?>

            <div class="form-row">
                <label for="images">Add Photos:</label>
                <div class="field-group">
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <small style="color:#6a7f93;">Select one or more photos to add. The first new photo will become the new cover.</small>
                    <div id="preview-wrap" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-blue">Update Product</button>
                <a href="product_detail.php?id=<?= $p->product_id ?>" class="text-link">Back to Detail</a>
            </div>

        </form>
    </div>
</main>

<script>
document.getElementById('images').addEventListener('change', function () {
    const wrap = document.getElementById('preview-wrap');
    wrap.innerHTML = '';
    Array.from(this.files).forEach(file => {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #cddff0;';
        wrap.appendChild(img);
    });
});
</script>

<?php require '_foot.php'; ?>
