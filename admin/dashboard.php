<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$db   = getDB();

// Stats
$stats = [
    'elections' => $db->query("SELECT COUNT(*) FROM elections")->fetchColumn(),
    'voters'    => $db->query("SELECT COUNT(*) FROM users WHERE role='voter'")->fetchColumn(),
    'votes'     => $db->query("SELECT COUNT(*) FROM votes")->fetchColumn(),
    'active'    => $db->query("SELECT COUNT(*) FROM elections WHERE status='active'")->fetchColumn(),
];

$recent_elections = getElections();
$recent_votes = $db->query("
    SELECT v.voted_at, u.full_name AS voter, e.title AS election, c.full_name AS candidate
    FROM votes v
    JOIN users u ON u.id = v.voter_id
    JOIN elections e ON e.id = v.election_id
    JOIN candidates c ON c.id = v.candidate_id
    ORDER BY v.voted_at DESC LIMIT 8
")->fetchAll();

$nav_sections = [
    ['label' => 'Overview', 'items' => [
        ['href' => 'dashboard.php', 'icon' => '📊', 'label' => 'Dashboard'],
        ['href' => 'elections.php', 'icon' => '🗳', 'label' => 'Elections'],
        ['href' => 'candidates.php', 'icon' => '👤', 'label' => 'Candidates'],
        ['href' => 'voters.php',    'icon' => '👥', 'label' => 'Voters'],
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
  <title>Admin Dashboard — VoteSecure</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <div class="breadcrumb">Admin Panel</div>
        <h1>Dashboard</h1>
      </div>
      <div class="flex gap-1">
        <span class="badge badge-admin"><span class="live-dot"></span>System Live</span>
        <span id="live-clock" class="mono text-muted" style="padding:0.4rem 0;"></span>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid animate-in">
      <div class="stat-card">
        <span class="stat-icon">🗳</span>
        <div class="stat-value"><?= $stats['elections'] ?></div>
        <div class="stat-label">Total Elections</div>
      </div>
      <div class="stat-card">
        <span class="stat-icon">✅</span>
        <div class="stat-value"><?= $stats['active'] ?></div>
        <div class="stat-label">Active Now</div>
      </div>
      <div class="stat-card">
        <span class="stat-icon">👥</span>
        <div class="stat-value"><?= $stats['voters'] ?></div>
        <div class="stat-label">Registered Voters</div>
      </div>
      <div class="stat-card">
        <span class="stat-icon">📥</span>
        <div class="stat-value"><?= $stats['votes'] ?></div>
        <div class="stat-label">Votes Cast</div>
      </div>
    </div>

    <div class="grid-2">
      <!-- Elections Table -->
      <div class="card animate-in">
        <div class="card-header">
          <span class="card-title">All Elections</span>
          <a href="elections.php" class="btn btn-primary btn-sm">+ New Election</a>
        </div>
        <?php if (empty($recent_elections)): ?>
          <div class="empty-state"><div class="empty-state-icon">🗳</div><h3>No elections yet</h3></div>
        <?php else: ?>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>Title</th><th>Status</th><th>Votes</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent_elections as $e): ?>
              <tr>
                <td>
                  <div style="font-weight:600;color:var(--white)"><?= htmlspecialchars($e['title']) ?></div>
                  <div class="text-sm text-muted"><?= $e['candidate_count'] ?> candidates</div>
                </td>
                <td>
                  <span class="badge badge-<?= $e['status'] ?>"><?= $e['status'] ?></span>
                </td>
                <td class="mono"><?= $e['vote_count'] ?></td>
                <td>
                  <div class="td-actions">
                    <a href="results.php?id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Results</a>
                    <a href="elections.php?edit=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Recent Votes -->
      <div class="card animate-in">
        <div class="card-header">
          <span class="card-title">Recent Votes</span>
          <span class="badge badge-active"><span class="live-dot"></span>Live</span>
        </div>
        <?php if (empty($recent_votes)): ?>
          <div class="empty-state"><div class="empty-state-icon">📥</div><h3>No votes yet</h3></div>
        <?php else: ?>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Voter</th><th>Election</th><th>Time</th></tr></thead>
            <tbody>
              <?php foreach ($recent_votes as $v): ?>
              <tr>
                <td style="font-weight:500;color:var(--white)"><?= htmlspecialchars($v['voter']) ?></td>
                <td class="text-sm text-muted"><?= htmlspecialchars(substr($v['election'],0,30)) ?>...</td>
                <td class="mono text-sm text-muted"><?= date('H:i M d', strtotime($v['voted_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
