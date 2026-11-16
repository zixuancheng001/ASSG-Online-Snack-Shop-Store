<?php
require '_base.php';
auth('Admin');

$id = (int)get('id');
$u = get_user($id);

if (!$u || $u->role !== 'Member') {
    temp('info', 'Member not found.');
    redirect('member_list.php');
}

require '_head.php';
?>

<main class="shop-shell">
    <section class="member-detail-card">
        <div class="member-detail-left">
            <img src="images/<?= encode($u->photo ?: 'default_image.jpg') ?>" class="member-detail-avatar" alt="Member photo" onerror="this.onerror=null;this.src='images/default_image.jpg';">
        </div>

        <div class="detail-content">
            <p class="eyebrow">Member Detail</p>
            <h1><?= encode($u->name) ?></h1>
            <p class="detail-description"><strong>ID:</strong> <?= $u->user_id ?></p>
            <p class="detail-description"><strong>Email:</strong> <?= encode($u->email) ?></p>
            <p class="detail-description"><strong>Role:</strong> <?= encode($u->role) ?></p>

            <div class="detail-actions">
                <a href="member_list.php" class="text-link">Back to Members</a>
            </div>
        </div>
    </section>
</main>

<?php require '_foot.php'; ?>
