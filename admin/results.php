<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user      = currentUser();
$db        = getDB();
$elections = getElections();

$selected  = isset($_GET['id']) ? (int)$_GET['id'] : ($elections[0]['id'] ?? 0);
$results   = $selected ? getResults($selected) : null;
$election  = $selected ? getElection($selected) : null;

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
  <title>Results — VoteSecure Admin</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <div class="breadcrumb">Admin / Results</div>
        <h1>Election Results</h1>
      </div>
    </div>

    <!-- Election selector -->
    <form method="GET" class="card mb-3" style="padding:1rem;">
      <div style="display:flex;gap:0.75rem;align-items:center;">
        <label class="form-label" style="margin:0;white-space:nowrap;">View Results For:</label>
        <select name="id" class="form-control" onchange="this.form.submit()" style="flex:1;">
          <?php foreach ($elections as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $e['id']==$selected?'selected':'' ?>>
              <?= htmlspecialchars($e['title']) ?> (<?= $e['status'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>

    <?php if ($results && $election): ?>
    <div class="grid-2">
      <!-- Results Card -->
      <div class="card">
        <div class="card-header">
          <span class="card-title"><?= htmlspecialchars($election['title']) ?></span>
          <span class="badge badge-<?= $election['status'] ?>"><?= $election['status'] ?></span>
        </div>

        <div class="flex gap-2 mb-3" style="flex-wrap:wrap;">
          <div class="stat-card" style="flex:1;min-width:120px;">
            <span class="stat-icon">📥</span>
            <div class="stat-value"><?= $results['total_votes'] ?></div>
            <div class="stat-label">Total Votes</div>
          </div>
          <div class="stat-card" style="flex:1;min-width:120px;">
            <span class="stat-icon">👤</span>
            <div class="stat-value"><?= count($results['candidates']) ?></div>
            <div class="stat-label">Candidates</div>
          </div>
        </div>

        <?php if (empty($results['candidates'])): ?>
          <div class="empty-state"><p>No candidates in this election.</p></div>
        <?php else: ?>
          <?php $winner = $results['candidates'][0]; ?>
          <?php foreach ($results['candidates'] as $i => $c): ?>
          <div class="result-row">
            <div class="result-info">
              <div class="result-name">
                <?php if ($i === 0 && $results['total_votes'] > 0): ?>
                  <span class="winner-crown">👑 </span>
                <?php endif; ?>
                <?= htmlspecialchars($c['full_name']) ?>
                <?php if ($c['party']): ?>
                  <span class="text-muted text-sm"> — <?= htmlspecialchars($c['party']) ?></span>
                <?php endif; ?>
              </div>
              <div style="display:flex;gap:0.75rem;align-items:center;">
                <span class="result-votes"><?= $c['vote_count'] ?> votes</span>
                <span class="result-percent"><?= $c['percentage'] ?>%</span>
              </div>
            </div>
            <div class="progress-bar">
              <div class="progress-fill <?= $i===0?'winner':'' ?>" data-width="<?= $c['percentage'] ?>"></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Chart Card -->
      <div class="card">
        <div class="card-header"><span class="card-title">Vote Distribution</span></div>
        <?php if ($results['total_votes'] > 0): ?>
        <canvas id="resultsChart" height="280"></canvas>
        <?php else: ?>
        <div class="empty-state"><div class="empty-state-icon">📊</div><p>No votes cast yet.</p></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </main>
</div>

<?php if ($results && $results['total_votes'] > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('resultsChart');
if (ctx) {
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_column($results['candidates'], 'full_name')) ?>,
      datasets: [{
        data: <?= json_encode(array_column($results['candidates'], 'vote_count')) ?>,
        backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'],
        borderColor: '#111827',
        borderWidth: 3,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { labels: { color: '#94a3b8', font: { family: 'Outfit', size: 13 } } }
      }
    }
  });
}
</script>
<?php endif; ?>

<script src="../assets/app.js"></script>
</body>
</html>
