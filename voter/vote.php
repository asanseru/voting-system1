<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user       = currentUser();
$election_id = (int)($_GET['id'] ?? 0);
$election   = $election_id ? getElection($election_id) : null;

if (!$election || $election['status'] !== 'active') {
    header("Location: " . APP_URL . "/voter/dashboard.php");
    exit;
}

if (hasVoted($election_id)) {
    header("Location: " . APP_URL . "/voter/dashboard.php?already_voted=1");
    exit;
}

$candidates = getCandidates($election_id);
$msg = $err = '';

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $candidate_id = (int)($_POST['candidate_id'] ?? 0);
    if (!$candidate_id) {
        $err = 'Please select a candidate.';
    } else {
        $result = castVote($election_id, $candidate_id);
        if ($result['success']) {
            header("Location: " . APP_URL . "/voter/history.php?voted=1");
            exit;
        }
        $err = $result['message'];
    }
}

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
  <title>Vote — <?= htmlspecialchars($election['title']) ?></title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <div class="breadcrumb">Voter Portal / Cast Vote</div>
        <h1><?= htmlspecialchars($election['title']) ?></h1>
      </div>
      <a href="dashboard.php" class="btn btn-outline">← Back</a>
    </div>

    <?php if ($err): ?><div class="alert alert-error" data-auto-dismiss>⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- Election Info -->
    <div class="alert alert-info animate-in" style="margin-bottom:1.5rem;">
      🔒 Your vote is anonymous and encrypted. You can only vote once. Choose carefully.
    </div>

    <div class="card animate-in mb-3" style="background:rgba(59,130,246,0.04);border-color:rgba(59,130,246,0.2);">
      <p style="color:var(--text);line-height:1.8;"><?= htmlspecialchars($election['description']) ?></p>
      <div class="flex gap-2 mt-2" style="flex-wrap:wrap;">
        <span class="badge badge-active"><span class="live-dot"></span>Voting Open</span>
        <span class="mono text-sm text-muted">Closes: <?= date('M d, Y \a\t H:i', strtotime($election['end_date'])) ?></span>
        <span class="mono text-sm text-muted"><?= count($candidates) ?> candidates</span>
      </div>
    </div>

    <!-- Voting Form -->
    <form id="vote-form" method="POST" class="animate-in">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <input type="hidden" name="election_id" value="<?= $election_id ?>"/>

      <h3 style="font-family:'Cormorant Garamond',serif;color:var(--white);margin-bottom:1.25rem;font-size:1.2rem;">
        Select your candidate:
      </h3>

      <?php if (empty($candidates)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">👤</div>
          <h3>No candidates available</h3>
        </div>
      <?php else: ?>
      <div class="candidates-grid">
        <?php foreach ($candidates as $c): ?>
        <div class="candidate-card" onclick="selectCandidate(<?= $c['id'] ?>)">
          <input type="radio" name="candidate_id" value="<?= $c['id'] ?>" id="c<?= $c['id'] ?>" style="display:none;"/>
          <div class="candidate-photo"><?= strtoupper(substr($c['full_name'],0,2)) ?></div>
          <div class="candidate-name"><?= htmlspecialchars($c['full_name']) ?></div>
          <?php if ($c['party']): ?>
          <div class="candidate-party"><?= htmlspecialchars($c['party']) ?></div>
          <?php endif; ?>
          <div class="candidate-bio"><?= htmlspecialchars($c['bio'] ?: 'No biography provided.') ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:2rem;text-align:center;">
        <button type="submit" id="submit-vote-btn" class="btn btn-primary btn-lg" disabled style="justify-content:center;min-width:220px;">
          Select a candidate first
        </button>
        <p class="text-sm text-muted mt-2">You cannot change your vote after submission.</p>
      </div>
      <?php endif; ?>
    </form>
  </main>
</div>

<script>
function selectCandidate(id) {
  document.querySelectorAll('.candidate-card').forEach(c => c.classList.remove('selected'));
  const card = document.querySelector(`.candidate-card input[value="${id}"]`)?.closest('.candidate-card');
  if (card) card.classList.add('selected');
  document.getElementById('c' + id).checked = true;
  const btn = document.getElementById('submit-vote-btn');
  if (btn) { btn.disabled = false; btn.textContent = '🗳 Cast My Vote'; }
}
</script>
<script src="../assets/app.js"></script>
</body>
</html>
