<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$db   = getDB();
$msg  = $err = '';

// Toggle verification
if (isset($_GET['verify'])) {
    $stmt = $db->prepare("UPDATE users SET is_verified = 1 - is_verified WHERE id = ? AND role = 'voter'");
    $stmt->execute([(int)$_GET['verify']]);
    $msg = 'Voter status updated.';
}

// Delete voter
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'voter'");
    $stmt->execute([(int)$_GET['delete']]);
    $msg = 'Voter removed.';
}

$search  = clean($_GET['search'] ?? '');
$sql     = "SELECT u.*,
            (SELECT COUNT(*) FROM votes WHERE voter_id = u.id) AS vote_count
            FROM users u WHERE u.role = 'voter'";
if ($search) {
    $stmt = $db->prepare($sql . " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.national_id LIKE ?) ORDER BY u.created_at DESC");
    $like = "%$search%";
    $stmt->execute([$like,$like,$like]);
} else {
    $stmt = $db->query($sql . " ORDER BY u.created_at DESC");
}
$voters = $stmt->fetchAll();

$nav_sections = [
    ['label' => 'Overview', 'items' => [
        ['href' => 'dashboard.php',  'icon' => '📊', 'label' => 'Dashboard'],
        ['href' => 'elections.php',  'icon' => '🗳', 'label' => 'Elections'],
        ['href' => 'candidates.php', 'icon' => '👤', 'label' => 'Candidates'],
        ['href' => 'voters.php',     'icon' => '👥', 'label' => 'Voters'],
    ]],
    ['label' => 'Reports', 'items' => [
        ['href' => 'results.php', 'icon' => '📈', 'label' => 'Results'],
        ['href' => 'audit.php',   'icon' => '🔍', 'label' => 'Audit Log'],
    ]],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Voters — VoteSecure Admin</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <div class="breadcrumb">Admin / Voters</div>
        <h1>Registered Voters</h1>
      </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss>✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- Search -->
    <form method="GET" class="card mb-3" style="padding:1rem;">
      <div style="display:flex;gap:0.75rem;">
        <input type="text" name="search" class="form-control" placeholder="Search by name, email or ID..."
               value="<?= htmlspecialchars($search) ?>" style="flex:1;"/>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search): ?><a href="voters.php" class="btn btn-outline">Clear</a><?php endif; ?>
      </div>
    </form>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Voters (<?= count($voters) ?>)</span>
      </div>
      <?php if (empty($voters)): ?>
        <div class="empty-state"><div class="empty-state-icon">👥</div><h3>No voters found</h3></div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Voter</th><th>National ID</th><th>Verified</th><th>Votes Cast</th><th>Joined</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($voters as $v): ?>
            <tr>
              <td>
                <div style="font-weight:600;color:var(--white)"><?= htmlspecialchars($v['full_name']) ?></div>
                <div class="text-sm text-muted"><?= htmlspecialchars($v['email']) ?></div>
              </td>
              <td class="mono text-sm"><?= htmlspecialchars($v['national_id']) ?></td>
              <td>
                <span class="badge <?= $v['is_verified'] ? 'badge-active' : 'badge-closed' ?>">
                  <?= $v['is_verified'] ? '✓ Verified' : 'Pending' ?>
                </span>
              </td>
              <td class="mono text-center"><?= $v['vote_count'] ?></td>
              <td class="text-sm text-muted"><?= date('M d, Y', strtotime($v['created_at'])) ?></td>
              <td>
                <div class="td-actions">
                  <a href="voters.php?verify=<?= $v['id'] ?>" class="btn btn-outline btn-sm">
                    <?= $v['is_verified'] ? 'Suspend' : 'Verify' ?>
                  </a>
                  <a href="voters.php?delete=<?= $v['id'] ?>" class="btn btn-danger btn-sm"
                     data-confirm="Delete this voter? Their votes will also be removed.">Delete</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
