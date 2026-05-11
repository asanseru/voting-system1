<?php
// ── Shared sidebar partial ──────────────────────────────────
// Usage: include __DIR__ . '/../includes/sidebar.php';
// Requires $nav_items array and $user to be set.
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-icon">🗳</div>
    <span class="sidebar-logo-text">Vote<span>Secure</span></span>
  </div>

  <?php foreach ($nav_sections as $section): ?>
  <div class="sidebar-section">
    <p class="sidebar-section-label"><?= $section['label'] ?></p>
    <ul class="sidebar-nav">
      <?php foreach ($section['items'] as $item): ?>
      <li>
        <a href="<?= $item['href'] ?>" <?= (basename($_SERVER['PHP_SELF']) === basename($item['href'])) ? 'class="active"' : '' ?>>
          <span class="nav-icon"><?= $item['icon'] ?></span>
          <?= $item['label'] ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endforeach; ?>

  <div class="sidebar-user">
    <div class="sidebar-user-info">
      <div class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 2)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="user-role"><?= $user['role'] ?></div>
      </div>
    </div>
    <a href="<?= APP_URL ?>/logout.php" class="btn btn-outline btn-sm w-full" style="justify-content:center;">
      Sign Out
    </a>
  </div>
</aside>
