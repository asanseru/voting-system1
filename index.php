<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: " . APP_URL . (isAdmin() ? '/admin/dashboard.php' : '/voter/dashboard.php'));
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['success']) {
        header("Location: " . APP_URL . ($result['role'] === 'admin' ? '/admin/dashboard.php' : '/voter/dashboard.php'));
        exit;
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — VoteSecure</title>
  <link rel="stylesheet" href="assets/style.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-box animate-in">
    <div class="auth-logo">
      <div class="auth-logo-icon">🗳</div>
      <h1 class="auth-title">VoteSecure</h1>
      <p class="auth-subtitle">Secure Online Voting System</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error" data-auto-dismiss>⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required/>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center; margin-top:0.5rem;">
        Sign In →
      </button>
    </form>

    <div class="auth-divider"><span>don't have an account?</span></div>
    <div class="text-center">
      <a href="register.php" class="auth-link">Create Voter Account</a>
    </div>

    <div class="card mt-3" style="padding:1rem; background:rgba(59,130,246,0.06); border-color:rgba(59,130,246,0.2);">
      <p class="text-sm text-muted" style="margin-bottom:0.4rem;"><strong class="text-accent">Demo Credentials</strong></p>
      <p class="text-sm mono text-muted">Admin: admin@votesystem.com / Admin@1234</p>
    </div>
  </div>
</div>
<script src="assets/app.js"></script>
</body>
</html>
