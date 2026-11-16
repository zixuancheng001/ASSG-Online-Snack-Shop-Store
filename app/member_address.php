<?php
require '_base.php';
auth('Member');
 
$deleteId = (int)get('delete');
if ($deleteId) {
    $stmt = $conn->prepare('DELETE FROM address WHERE address_id = ? AND user_id = ?');
    $stmt->execute([$deleteId, $_user->user_id]);
    temp('info', 'Address removed.');
    redirect('member_address.php');
}
 
if (is_post()) {
    $name = trim(post('name'));
    $phone = trim(post('phone_no'));
    $address_line = trim(post('address_line'));
    $city = trim(post('city'));
    $postcode = trim(post('postcode'));
    $address_id = (int)post('address_id');
 
    if (!$name || !$phone || !$address_line || !$city || !$postcode) {
        $_err['all'] = 'Please fill all fields.';
    }
 
    if (empty($_err)) {
        if ($address_id) {

            $stmt = $conn->prepare("
                UPDATE address 
                SET name = ?, phone_no = ?, address_line = ?, city = ?, postcode = ?
                WHERE address_id = ? AND user_id = ?
            ");
            $stmt->execute([
                $name,
                $phone,
                $address_line,
                $city,
                $postcode,
                $address_id,
                $_user->user_id
            ]);

            temp('info', 'Address updated.');

        } else {
            $stmt = $conn->prepare("
                INSERT INTO address (user_id, name, phone_no, address_line, city, postcode)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_user->user_id,
                $name,
                $phone,
                $address_line,
                $city,
                $postcode
            ]);
            temp('info', 'Address added.');
        }
        redirect('member_address.php');
    }
}
 
$editId   = (int)get('edit');
$editAddr = null;
if ($editId) {
    $stmt = $conn->prepare('SELECT * FROM address WHERE address_id = ? AND user_id = ?');
    $stmt->execute([$editId, $_user->user_id]);
    $editAddr = $stmt->fetch();
}
 
$stmt = $conn->prepare('SELECT * FROM address WHERE user_id = ? ORDER BY address_id ASC');
$stmt->execute([$_user->user_id]);
$addresses = $stmt->fetchAll();

require '_head.php';
?>
 
<main class="shop-shell">
 
    <section class="member-panel">
        <div class="panel-header">
            <div>
                <h1>My Addresses</h1>
                <p class="panel-subtitle"><?= count($addresses) ?> address(es) saved</p>
            </div>
            <a href="shopping_cart.php" class="text-link">← Back to Cart</a>
        </div>
    </section>
 
    <section class="admin-panel" style="margin-top:16px;">
        <h3><?= $editAddr ? 'Edit Address' : 'Add New Address' ?></h3>
        <form method="post" action="member_address.php" style="margin-top:12px;">
            <?php if ($editAddr): ?>
                <input type="hidden" name="address_id" value="<?= $editAddr->address_id ?>">
            <?php else: ?>
                <input type="hidden" name="address_id" value="0">
            <?php endif; ?>
 
            <div style="display:flex;flex-direction:column;gap:8px;">

                <input type="text" name="name" placeholder="Full Name"
                    value="<?= encode($editAddr->name ?? post('name') ?? '') ?>">

                <input type="text" name="phone_no" placeholder="Phone Number"
                    value="<?= encode($editAddr->phone_no ?? post('phone_no') ?? '') ?>">

                <textarea name="address_line" rows="3"
                    placeholder="Full Address"><?= encode($editAddr->address_line ?? post('address_line') ?? '') ?></textarea>

                <input type="text" name="city" placeholder="City"
                    value="<?= encode($editAddr->city ?? post('city') ?? '') ?>">

                <input type="text" name="postcode" placeholder="Postcode"
                    value="<?= encode($editAddr->postcode ?? post('postcode') ?? '') ?>">

                <?= err('all') ?>

            </div>
 
            <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn-blue"><?= $editAddr ? 'Update Address' : 'Add Address' ?></button>
                <?php if ($editAddr): ?>
                    <a href="member_address.php" class="text-link" style="align-self:center;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <?php if ($addresses): ?>
    <section class="simple-grid" style="margin-top:20px;">
        <?php foreach ($addresses as $a): ?>
        <article class="product-card">
            <div class="product-body">
                <div class="product-meta">
                    <p style="white-space:pre-line;"><?= encode($a->address_line) ?></p>
                </div>
                <div class="card-actions" style="margin-top:10px;">
                    <a href="member_address.php?edit=<?= $a->address_id ?>" class="text-link">Edit</a>
                    <a href="member_address.php?delete=<?= $a->address_id ?>"
                       class="text-link danger-link"
                       onclick="return confirm('Remove this address?')">Remove</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php else: ?>
    <div class="empty-state" style="margin-top:20px;">
        <h3>No addresses</h3>
        <p>Add your address.</p>
    </div>
    <?php endif; ?>
 
</main>
 
<?php require '_foot.php'; ?>