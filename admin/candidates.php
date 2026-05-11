<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$db   = getDB();
$msg  = $err = '';

// ── Add candidate ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_candidate'])) {
    verifyCsrf();
    $election_id = (int)($_POST['election_id'] ?? 0);
    $full_name   = clean($_POST['full_name'] ?? '');
    $party       = clean($_POST['party'] ?? '');
    $bio         = clean($_POST['bio'] ?? '');

    if (!$full_name || !$election_id) {
        $err = 'Name and election are required.';
    } else {
        $stmt = $db->prepare("INSERT INTO candidates (election_id,full_name,party,bio) VALUES(?,?,?,?)");
        $stmt->execute([$election_id,$full_name,$party,$bio]);
        $msg = 'Candidate added.';
    }
}

// ── Delete candidate ───────────────────────────────────────
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM candidates WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    $msg = 'Candidate removed.';
}

// Fetch all candidates with election info
$candidates = $db->query("
    SELECT c.*, e.title AS election_title
    FROM candidates c
    JOIN elections e ON e.id = c.election_id
    ORDER BY e.title, c.full_name
")->fetchAll();

$elections = $db->query("SELECT id, title FROM elections ORDER BY title")->fetchAll();

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
  <title>Candidates — VoteSecure Admin</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <div class="breadcrumb">Admin / Candidates</div>
        <h1>Candidates</h1>
      </div>
      <button class="btn btn-primary" data-modal-open="add-modal">+ Add Candidate</button>
    </div>

    <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss>✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"   data-auto-dismiss>⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><span class="card-title">All Candidates (<?= count($candidates) ?>)</span></div>
      <?php if (empty($candidates)): ?>
        <div class="empty-state"><div class="empty-state-icon">👤</div><h3>No candidates yet</h3></div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Candidate</th><th>Party</th><th>Election</th><th>Bio</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($candidates as $c): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                  <div class="user-avatar" style="width:36px;height:36px;font-size:0.8rem;">
                    <?= strtoupper(substr($c['full_name'],0,2)) ?>
                  </div>
                  <span style="font-weight:600;color:var(--white)"><?= htmlspecialchars($c['full_name']) ?></span>
                </div>
              </td>
              <td><?= htmlspecialchars($c['party'] ?: '—') ?></td>
              <td class="text-sm text-muted"><?= htmlspecialchars($c['election_title']) ?></td>
              <td class="text-sm text-muted"><?= htmlspecialchars(substr($c['bio'],0,60)) ?>...</td>
              <td>
                <a href="candidates.php?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm"
                   data-confirm="Remove this candidate?">Remove</a>
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

<!-- Add Candidate Modal -->
<div class="modal-overlay" id="add-modal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Candidate</span>
      <button class="modal-close">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <input type="hidden" name="add_candidate" value="1"/>
      <div class="form-group">
        <label class="form-label">Election</label>
        <select name="election_id" class="form-control" required>
          <option value="">— Select Election —</option>
          <?php foreach ($elections as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" placeholder="Candidate full name" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Party / Affiliation</label>
        <input type="text" name="party" class="form-control" placeholder="Optional"/>
      </div>
      <div class="form-group">
        <label class="form-label">Bio / Platform</label>
        <textarea name="bio" class="form-control" placeholder="Brief candidate bio..." maxlength="400"></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">Add Candidate</button>
    </form>
  </div>
</div>

<script src="../assets/app.js"></script>
</body>
</html>
