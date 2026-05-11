<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$db   = getDB();

$elections      = getElections('active');
$my_votes       = $db->prepare("
    SELECT v.*, e.title AS election_title, c.full_name AS candidate_name
    FROM votes v
    JOIN elections e ON e.id = v.election_id
    JOIN candidates c ON c.id = v.candidate_id
    WHERE v.voter_id = ?
    ORDER BY v.voted_at DESC
");
$my_votes->execute([$_SESSION['user_id']]);
$my_votes = $my_votes->fetchAll();

$upcoming = getElections('upcoming');

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
  <title>Dashboard — VoteSecure</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <div class="breadcrumb">Voter Portal</div>
        <h1>Welcome, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></h1>
      </div>
      <span id="live-clock" class="mono text-muted" style="padding:0.4rem 0;"></span>
    </div>

    <!-- Stats -->
    <div class="stats-grid animate-in">
      <div class="stat-card">
        <span class="stat-icon">🗳</span>
        <div class="stat-value"><?= count($elections) ?></div>
        <div class="stat-label">Active Elections</div>
      </div>
      <div class="stat-card">
        <span class="stat-icon">✅</span>
        <div class="stat-value"><?= count($my_votes) ?></div>
        <div class="stat-label">My Votes Cast</div>
      </div>
      <div class="stat-card">
        <span class="stat-icon">📅</span>
        <div class="stat-value"><?= count($upcoming) ?></div>
        <div class="stat-label">Upcoming Elections</div>
      </div>
    </div>

    <!-- Active Elections -->
    <div class="card animate-in mb-3">
      <div class="card-header">
        <span class="card-title">Active Elections</span>
        <span class="badge badge-active"><span class="live-dot"></span>Open for Voting</span>
      </div>

      <?php if (empty($elections)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">🗳</div>
          <h3>No active elections</h3>
          <p>Check back later for upcoming elections.</p>
        </div>
      <?php else: ?>
      <div style="display:grid;gap:1rem;">
        <?php foreach ($elections as $e): ?>
        <?php $voted = hasVoted($e['id']); ?>
        <div class="card" style="border-color:<?= $voted ? 'var(--accent2)' : 'var(--accent)' ?>;background:rgba(<?= $voted?'16,185,129':'59,130,246' ?>,0.04);">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
            <div style="flex:1;">
              <h3 style="font-family:'Cormorant Garamond',serif;color:var(--white);margin-bottom:0.5rem;">
                <?= htmlspecialchars($e['title']) ?>
              </h3>
              <p class="text-sm text-muted" style="margin-bottom:0.75rem;"><?= htmlspecialchars($e['description']) ?></p>
              <div class="flex gap-2" style="flex-wrap:wrap;">
                <span class="badge badge-active"><?= $e['candidate_count'] ?> Candidates</span>
                <span class="badge badge-upcoming"><?= $e['vote_count'] ?> Votes Cast</span>
                <span class="mono text-sm text-muted">Ends: <?= date('M d, Y H:i', strtotime($e['end_date'])) ?></span>
              </div>
            </div>
            <div>
              <?php if ($voted): ?>
                <span class="btn btn-success" style="cursor:default;">✓ Voted</span>
              <?php else: ?>
                <a href="vote.php?id=<?= $e['id'] ?>" class="btn btn-primary">Cast Vote →</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Recent Vote History -->
    <?php if (!empty($my_votes)): ?>
    <div class="card animate-in">
      <div class="card-header">
        <span class="card-title">My Recent Votes</span>
        <a href="history.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Election</th><th>Voted For</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($my_votes, 0, 5) as $v): ?>
            <tr>
              <td style="color:var(--white);font-weight:500;"><?= htmlspecialchars($v['election_title']) ?></td>
              <td class="text-accent"><?= htmlspecialchars($v['candidate_name']) ?></td>
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
