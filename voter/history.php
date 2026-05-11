<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$db   = getDB();

$stmt = $db->prepare("
    SELECT v.*, e.title AS election_title, e.status AS election_status,
           c.full_name AS candidate_name, c.party
    FROM votes v
    JOIN elections e ON e.id = v.election_id
    JOIN candidates c ON c.id = v.candidate_id
    WHERE v.voter_id = ?
    ORDER BY v.voted_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$votes = $stmt->fetchAll();

$nav_sections = [
    ['label' => 'Voter Panel', 'items' => [
        ['href' => 'dashboard.php', 'icon' => '🏠', 'label' => 'Home'],
        ['href' => 'history.php',   'icon' => '📋', 'label' => 'My Votes'],
    ]],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Vote History — VoteSecure</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <div class="breadcrumb">Voter Portal / History</div>
        <h1>My Vote History</h1>
      </div>
    </div>

    <?php if (isset($_GET['voted'])): ?>
    <div class="alert alert-success animate-in" data-auto-dismiss>
      🎉 Your vote has been cast successfully! Thank you for participating.
    </div>
    <?php endif; ?>

    <?php if (empty($votes)): ?>
      <div class="empty-state animate-in">
        <div class="empty-state-icon">📋</div>
        <h3>No votes yet</h3>
        <p>You haven't cast any votes. <a href="dashboard.php" class="auth-link">View active elections →</a></p>
      </div>
    <?php else: ?>

    <div class="stats-grid animate-in">
      <div class="stat-card">
        <span class="stat-icon">✅</span>
        <div class="stat-value"><?= count($votes) ?></div>
        <div class="stat-label">Elections Voted In</div>
      </div>
    </div>

    <div class="card animate-in">
      <div class="card-header"><span class="card-title">All My Votes</span></div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Election</th><th>My Candidate</th><th>Party</th><th>Election Status</th><th>Voted At</th></tr></thead>
          <tbody>
            <?php foreach ($votes as $v): ?>
            <tr>
              <td style="font-weight:600;color:var(--white)"><?= htmlspecialchars($v['election_title']) ?></td>
              <td class="text-accent"><?= htmlspecialchars($v['candidate_name']) ?></td>
              <td class="text-muted text-sm"><?= htmlspecialchars($v['party'] ?: '—') ?></td>
              <td><span class="badge badge-<?= $v['election_status'] ?>"><?= $v['election_status'] ?></span></td>
              <td class="mono text-sm text-muted"><?= date('M d, Y H:i', strtotime($v['voted_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </main>
</div>
<script src="../assets/app.js"></script>
</body>
</html>
