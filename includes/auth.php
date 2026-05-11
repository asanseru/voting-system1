<?php
// ============================================================
// Authentication & Helper Functions
// ============================================================

require_once __DIR__ . '/db.php';

session_start();

// ── Session Helpers ──────────────────────────────────────────

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin(string $redirect = '/index.php'): void {
    if (!isLoggedIn()) {
        header("Location: " . APP_URL . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header("Location: " . APP_URL . "/voter/dashboard.php");
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, full_name, email, role, national_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// ── Auth Actions ─────────────────────────────────────────────

function loginUser(string $email, string $password): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    if (!$user['is_verified']) {
        return ['success' => false, 'message' => 'Your account is pending approval.'];
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    logAudit($user['id'], 'LOGIN', 'User logged in');

    return ['success' => true, 'role' => $user['role']];
}

function registerUser(array $data): array {
    $db = getDB();

    // Check duplicates
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR national_id = ?");
    $stmt->execute([$data['email'], $data['national_id']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email or National ID already registered.'];
    }

    $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare("INSERT INTO users (full_name, email, national_id, password, role, is_verified)
                          VALUES (?, ?, ?, ?, 'voter', 1)");
    $stmt->execute([$data['full_name'], $data['email'], $data['national_id'], $hash]);

    return ['success' => true, 'message' => 'Registration successful. You can now log in.'];
}

function logoutUser(): void {
    if (isLoggedIn()) logAudit($_SESSION['user_id'], 'LOGOUT', 'User logged out');
    session_destroy();
    header("Location: " . APP_URL . "/index.php");
    exit;
}

// ── Voting Functions ─────────────────────────────────────────

function castVote(int $electionId, int $candidateId): array {
    requireLogin();
    $db     = getDB();
    $userId = $_SESSION['user_id'];

    // Check election active
    $stmt = $db->prepare("SELECT * FROM elections WHERE id = ? AND status = 'active'");
    $stmt->execute([$electionId]);
    if (!$stmt->fetch()) {
        return ['success' => false, 'message' => 'Election is not currently active.'];
    }

    // Check already voted
    $stmt = $db->prepare("SELECT id FROM votes WHERE election_id = ? AND voter_id = ?");
    $stmt->execute([$electionId, $userId]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'You have already voted in this election.'];
    }

    // Cast vote
    $stmt = $db->prepare("INSERT INTO votes (election_id, voter_id, candidate_id, ip_address)
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$electionId, $userId, $candidateId, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);

    logAudit($userId, 'VOTE_CAST', "Voted in election #$electionId");

    return ['success' => true, 'message' => 'Your vote has been cast successfully!'];
}

function hasVoted(int $electionId): bool {
    if (!isLoggedIn()) return false;
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM votes WHERE election_id = ? AND voter_id = ?");
    $stmt->execute([$electionId, $_SESSION['user_id']]);
    return (bool)$stmt->fetch();
}

// ── Election Queries ──────────────────────────────────────────

function getElections(string $status = ''): array {
    $db  = getDB();
    $sql = "SELECT e.*, u.full_name AS created_by_name,
                   (SELECT COUNT(*) FROM candidates WHERE election_id = e.id) AS candidate_count,
                   (SELECT COUNT(*) FROM votes WHERE election_id = e.id) AS vote_count
            FROM elections e JOIN users u ON e.created_by = u.id";
    if ($status) {
        $stmt = $db->prepare($sql . " WHERE e.status = ? ORDER BY e.start_date DESC");
        $stmt->execute([$status]);
    } else {
        $stmt = $db->query($sql . " ORDER BY e.start_date DESC");
    }
    return $stmt->fetchAll();
}

function getElection(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getCandidates(int $electionId): array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT c.*,
                          (SELECT COUNT(*) FROM votes WHERE candidate_id = c.id) AS vote_count
                          FROM candidates c WHERE c.election_id = ?
                          ORDER BY c.position ASC, c.full_name ASC");
    $stmt->execute([$electionId]);
    return $stmt->fetchAll();
}

function getResults(int $electionId): array {
    $db       = getDB();
    $stmt     = $db->prepare("SELECT COUNT(*) FROM votes WHERE election_id = ?");
    $stmt->execute([$electionId]);
    $total    = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("SELECT c.id, c.full_name, c.party, c.photo,
                          COUNT(v.id) AS vote_count
                          FROM candidates c
                          LEFT JOIN votes v ON v.candidate_id = c.id AND v.election_id = ?
                          WHERE c.election_id = ?
                          GROUP BY c.id
                          ORDER BY vote_count DESC");
    $stmt->execute([$electionId, $electionId]);
    $candidates = $stmt->fetchAll();

    foreach ($candidates as &$c) {
        $c['percentage'] = $total > 0 ? round(($c['vote_count'] / $total) * 100, 1) : 0;
    }

    return ['total_votes' => $total, 'candidates' => $candidates];
}

// ── Audit Log ─────────────────────────────────────────────────

function logAudit(?int $userId, string $action, string $details = ''): void {
    try {
        $db   = getDB();
        $stmt = $db->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?,?,?,?)");
        $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } catch (Exception $e) { /* silent fail */ }
}

// ── CSRF ──────────────────────────────────────────────────────

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}

// ── Sanitize ──────────────────────────────────────────────────

function clean(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
