<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: " . APP_URL . (isAdmin() ? '/admin/dashboard.php' : '/voter/dashboard.php'));
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $data = [
        'full_name'   => clean($_POST['full_name'] ?? ''),
        'email'       => clean($_POST['email'] ?? ''),
        'national_id' => clean($_POST['national_id'] ?? ''),
        'password'    => $_POST['password'] ?? '',
    ];

    if (strlen($data['password']) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($data['password'] !== ($_POST['confirm_password'] ?? '')) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($data);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register — VoteSecure</title>
  <link rel="stylesheet" href="assets/style.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-box animate-in" style="max-width:520px;">
    <div class="auth-logo">
      <div class="auth-logo-icon">🗳</div>
      <h1 class="auth-title">Create Account</h1>
      <p class="auth-subtitle">Register to participate in elections</p>
    </div>

    <?php if ($error):   ?><div class="alert alert-error"   data-auto-dismiss>⚠ <?= htmlspecialchars($error)   ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" data-auto-dismiss>✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" placeholder="Abebe Girma" required/>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required/>
        </div>
        <div class="form-group">
          <label class="form-label">National ID</label>
          <input type="text" name="national_id" class="form-control" placeholder="ETH-123456" required/>
          <p class="form-hint">Used to verify your identity</p>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
        Create Account →
      </button>
    </form>

    <div class="auth-divider"><span>already registered?</span></div>
    <div class="text-center">
      <a href="index.php" class="auth-link">Sign In</a>
    </div>
  </div>
</div>
<script src="assets/app.js"></script>
</body>
</html>
