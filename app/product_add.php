<?php
require '_base.php';
auth('Admin');

$name        = '';
$description = '';
$price       = '';
$category    = '';

$categories = ['Chocolate', 'Chips', 'Ice Cream', 'Cookies', 'Drinks', 'Cake','Others'];

if (is_post()) {
    $name        = post('name');
    $description = post('description');
    $price       = post('price');
    $category    = post('category');


    if (!$name)                                          $_err['name']     = 'Required';
    if ($price === '')                                   $_err['price']    = 'Required';
    elseif (!is_numeric($price) || $price < 0)           $_err['price']    = 'Invalid price';
    if (!$category || !in_array($category, $categories)) $_err['category'] = 'Required';

    if (!$_err) {

        $image = 'default_snackimage.jpg'; 


        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['images']['error'][$i] === 0) {
                    $new_name = uniqid() . '.jpg';
                    if (move_uploaded_file($tmp, "images/$new_name")) {
                        $image = $new_name;
                        break;
                    }
                }
            }
        }

        // Insert product with first photo as cover
        $stmt = $conn->prepare("
            INSERT INTO products (name, description, price, category, image)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $price, $category, $image]);
        $productId = $conn->lastInsertId();

        // Save all photos into product_images (including the first one)
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === 0) {
                $fname = ($i === 0) ? $image : uniqid() . '.jpg';
                if ($i !== 0) move_uploaded_file($tmp, "images/$fname");
                $stmt2 = $conn->prepare("INSERT INTO product_images (product_id, filename, sort_order) VALUES (?, ?, ?)");
                $stmt2->execute([$productId, $fname, $i]);
            }
        }

        temp('info', 'Product added successfully.');
        redirect('product_list.php');
    }
}

require '_head.php';
?>

<main>
    <div class="card">
        <h1>Add Product</h1>

        <form method="POST" enctype="multipart/form-data" class="form">

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

            <div class="form-row">
                <label for="images">Product Photos:</label>
                <div class="field-group">
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <small style="color:#6a7f93;">Select one or more photos. The first photo will be the cover.</small>
                    <?php err('images'); ?>
                     <label class="upload" tabindex="0">
                        <img src="images/default_snackimage.jpg" id="preview">
                    </label>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-blue">Add Product</button>
                <a href="product_list.php" class="text-link">Back to Products</a>
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
