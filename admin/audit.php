<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$db   = getDB();

$logs = $db->query("
    SELECT a.*, u.full_name AS user_name, u.email
    FROM audit_log a
    LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
    LIMIT 200
")->fetchAll();

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
  <title>Audit Log — VoteSecure Admin</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <div class="breadcrumb">Admin / Security</div>
        <h1>Audit Log</h1>
      </div>
      <span class="badge badge-active"><span class="live-dot"></span><?= count($logs) ?> records</span>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">System Activity Log (Last 200)</span></div>
      <?php if (empty($logs)): ?>
        <div class="empty-state"><div class="empty-state-icon">🔍</div><h3>No activity yet</h3></div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>#</th><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
          <tbody>
            <?php foreach ($logs as $i => $log): ?>
            <tr>
              <td class="mono text-muted"><?= $log['id'] ?></td>
              <td>
                <?php if ($log['user_name']): ?>
                  <div style="font-weight:500;color:var(--white)"><?= htmlspecialchars($log['user_name']) ?></div>
                  <div class="text-sm text-muted"><?= htmlspecialchars($log['email']) ?></div>
                <?php else: ?>
                  <span class="text-muted">System</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $log['action']==='VOTE_CAST'?'badge-active':($log['action']==='LOGIN'?'badge-admin':'badge-upcoming') ?>">
                  <?= htmlspecialchars($log['action']) ?>
                </span>
              </td>
              <td class="text-sm text-muted"><?= htmlspecialchars($log['details']) ?></td>
              <td class="mono text-sm text-muted"><?= htmlspecialchars($log['ip_address']) ?></td>
              <td class="mono text-sm text-muted"><?= date('M d H:i:s', strtotime($log['created_at'])) ?></td>
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
