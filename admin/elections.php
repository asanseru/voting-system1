<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$db   = getDB();
$msg  = $err = '';

// ── Create / Update election ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title      = clean($_POST['title'] ?? '');
    $desc       = clean($_POST['description'] ?? '');
    $start      = $_POST['start_date'] ?? '';
    $end        = $_POST['end_date'] ?? '';
    $status     = $_POST['status'] ?? 'upcoming';
    $edit_id    = (int)($_POST['edit_id'] ?? 0);

    if (!$title || !$start || !$end) {
        $err = 'Title, start and end dates are required.';
    } elseif ($end <= $start) {
        $err = 'End date must be after start date.';
    } else {
        if ($edit_id) {
            $stmt = $db->prepare("UPDATE elections SET title=?,description=?,start_date=?,end_date=?,status=? WHERE id=?");
            $stmt->execute([$title,$desc,$start,$end,$status,$edit_id]);
            $msg = 'Election updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO elections (title,description,start_date,end_date,status,created_by) VALUES(?,?,?,?,?,?)");
            $stmt->execute([$title,$desc,$start,$end,$status,$_SESSION['user_id']]);
            $msg = 'Election created.';
        }
    }
}

// ── Delete election ────────────────────────────────────────
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM elections WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    $msg = 'Election deleted.';
}

// ── Get edit data ──────────────────────────────────────────
$editing = null;
if (isset($_GET['edit'])) {
    $editing = getElection((int)$_GET['edit']);
}

$elections = getElections();

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
  <title>Elections — VoteSecure Admin</title>
  <link rel="stylesheet" href="../assets/style.css"/>
</head>
<body>
<div class="page-wrapper">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <div class="breadcrumb">Admin / Elections</div>
        <h1><?= $editing ? 'Edit Election' : 'Elections' ?></h1>
      </div>
      <?php if (!$editing): ?>
        <button class="btn btn-primary" data-modal-open="create-modal">+ New Election</button>
      <?php endif; ?>
    </div>

    <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss>✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"   data-auto-dismiss>⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <?php if ($editing): ?>
    <!-- Inline Edit Form -->
    <div class="card mb-3">
      <div class="card-header"><span class="card-title">Edit Election</span><a href="elections.php" class="btn btn-outline btn-sm">Cancel</a></div>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
        <input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"/>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editing['title']) ?>" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" maxlength="500"><?= htmlspecialchars($editing['description']) ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Start Date &amp; Time</label>
            <input type="datetime-local" name="start_date" class="form-control" value="<?= str_replace(' ','T',substr($editing['start_date'],0,16)) ?>" required/>
          </div>
          <div class="form-group">
            <label class="form-label">End Date &amp; Time</label>
            <input type="datetime-local" name="end_date" class="form-control" value="<?= str_replace(' ','T',substr($editing['end_date'],0,16)) ?>" required/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <?php foreach (['upcoming','active','closed'] as $s): ?>
              <option value="<?= $s ?>" <?= $editing['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Elections Table -->
    <div class="card">
      <div class="card-header"><span class="card-title">All Elections (<?= count($elections) ?>)</span></div>
      <?php if (empty($elections)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">🗳</div>
          <h3>No elections created yet</h3>
          <p>Click "+ New Election" to get started.</p>
        </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>Title</th><th>Status</th><th>Period</th><th>Candidates</th><th>Votes</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($elections as $e): ?>
            <tr>
              <td>
                <div style="font-weight:600;color:var(--white)"><?= htmlspecialchars($e['title']) ?></div>
                <div class="text-sm text-muted"><?= htmlspecialchars(substr($e['description'],0,60)) ?>...</div>
              </td>
              <td><span class="badge badge-<?= $e['status'] ?>"><?= $e['status'] ?></span></td>
              <td class="text-sm text-muted">
                <?= date('M d, Y', strtotime($e['start_date'])) ?><br>
                → <?= date('M d, Y', strtotime($e['end_date'])) ?>
              </td>
              <td class="mono text-center"><?= $e['candidate_count'] ?></td>
              <td class="mono text-center"><?= $e['vote_count'] ?></td>
              <td>
                <div class="td-actions">
                  <a href="results.php?id=<?= $e['id'] ?>" class="btn btn-outline btn-sm">📈</a>
                  <a href="elections.php?edit=<?= $e['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                  <a href="elections.php?delete=<?= $e['id'] ?>" class="btn btn-danger btn-sm"
                     data-confirm="Delete this election and all its data?">Delete</a>
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

<!-- Create Modal -->
<div class="modal-overlay" id="create-modal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Create New Election</span>
      <button class="modal-close">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="form-group">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" placeholder="Election title" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" placeholder="Describe this election..." maxlength="500"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Start</label>
          <input type="datetime-local" name="start_date" class="form-control" required/>
        </div>
        <div class="form-group">
          <label class="form-label">End</label>
          <input type="datetime-local" name="end_date" class="form-control" required/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="upcoming">Upcoming</option>
          <option value="active">Active</option>
          <option value="closed">Closed</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">Create Election</button>
    </form>
  </div>
</div>

<script src="../assets/app.js"></script>
</body>
</html>
