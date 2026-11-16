<?php
require '_base.php';
auth('Admin');
require '_head.php';

// --- 1. Handle Block/Unblock Logic ---
if ($id = get('block')) {
    $stmt = $conn->prepare("UPDATE users SET status = 'blocked' WHERE user_id = ? AND role = 'Member'");
    $stmt->execute([$id]);
    temp('info', 'Member has been blocked.');
    redirect('member_list.php'); // Redirect to clear the GET parameter
}

if ($id = get('unblock')) {
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id = ? AND role = 'Member'");
    $stmt->execute([$id]);
    temp('info', 'Member has been unblocked.');
    redirect('member_list.php');
}



$search = get('search');

// --- 2. Added 'status' to the SELECT query ---
$stmt = $conn->prepare("
    SELECT user_id, name, email, role, photo, status
    FROM users
    WHERE role = 'Member'
      AND (name LIKE ? OR email LIKE ?)
    ORDER BY user_id ASC
");
$stmt->execute(["%$search%", "%$search%"]);
$users = $stmt->fetchAll();
$memberCount = count($users);
?>

<main class="shop-shell">
    <section class="admin-panel">
        <div class="panel-header">
            <div>
                <h1>Member List</h1>
                <p class="panel-subtitle"><?= $memberCount ?> member(s) found</p>
            </div>
        </div>

        <form method="get" class="shop-search simple-search">
            <input type="text" name="search" placeholder="Search name or email..." value="<?= encode($search) ?>">
            <button type="submit" class="btn-blue">Search</button>
        </form>
    </section>

    <section class="member-grid">
        <?php $displayNo = 1; ?>
        <?php foreach($users as $u): ?>
        <article class="member-card <?= $u->status == 'blocked' ? 'blocked-style' : '' ?>">
            <div class="member-card-top">
                <img src="images/<?= encode($u->photo ?: 'default_image.jpg') ?>" 
                     class="member-avatar" 
                     style="<?= $u->status == 'blocked' ? 'filter: grayscale(100%); opacity: 0.5;' : '' ?>"
                     alt="Member photo" 
                     onerror="this.onerror=null;this.src='images/default_image.jpg';">
                <div>
                    <span class="member-id">Member #<?= $displayNo ?></span>
                    <h3>
                        <?= encode($u->name) ?> 
                        <?php if($u->status == 'blocked'): ?>
                            <small style="color: red;">(BLOCKED)</small>
                        <?php endif; ?>
                    </h3>
                    <p class="member-email"><?= encode($u->email) ?></p>
                </div>
            </div>

            <div class="member-card-footer">
                <span class="member-role"><?= encode($u->role) ?></span>
                <div class="card-actions">
                    <a href="member_detail.php?id=<?= $u->user_id ?>" class="text-link">View</a>
                    
                    <?php if ($u->status == 'active'): ?>
                        <a href="?block=<?= $u->user_id ?>" 
                          class="text-link action-memberblock" 
                          onclick="return confirm('Confirm blocking this member?')">Block</a>
                     <?php else: ?>
                        <a href="?unblock=<?= $u->user_id ?>" 
                        class="text-link action-memberunblock"
                        onclick="return confirm('Confirm unblocking this member?')">Unblock</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php $displayNo++; ?>
        <?php endforeach; ?>

        <?php if (!$users): ?>
        <div class="empty-state">
            <h3>No members found</h3>
            <p>Try another keyword.</p>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php require '_foot.php'; ?>