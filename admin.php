<?php
session_start();

// ── Auth: admin only ──────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// ── DB connection ─────────────────────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hoophub;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ── AJAX: change booking status ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');

    if ($_POST['action'] === 'update_status') {
        $id     = (int) ($_POST['booking_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $allowed = ['pending', 'confirmed', 'cancelled'];
        if (!$id || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['success' => true, 'message' => "Booking #$id set to $status."]);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit();
}

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_status = $_GET['status'] ?? 'all';
$filter_search = trim($_GET['search'] ?? '');
$page          = max(1, (int) ($_GET['page'] ?? 1));
$per_page      = 15;
$offset        = ($page - 1) * $per_page;

// ── Build query ───────────────────────────────────────────────────────────────
$where  = [];
$params = [];

if ($filter_status !== 'all') {
    $where[]  = "b.status = ?";
    $params[] = $filter_status;
}
if ($filter_search !== '') {
    $where[]  = "(u.username LIKE ? OR u.email LIKE ? OR b.court_name LIKE ?)";
    $params[] = "%$filter_search%";
    $params[] = "%$filter_search%";
    $params[] = "%$filter_search%";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Fetch bookings ────────────────────────────────────────────────────────────
/*
    Expected bookings table schema:
    CREATE TABLE bookings (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        user_id      INT NOT NULL,
        court_name   VARCHAR(100) NOT NULL,
        booking_date DATE NOT NULL,
        time_slot    VARCHAR(50) NOT NULL,
        status       ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
*/

$count_stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM bookings b
     JOIN users u ON b.user_id = u.id
     $where_sql"
);
$count_stmt->execute($params);
$total_rows  = (int) $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $per_page));

$stmt = $pdo->prepare(
    "SELECT b.id, b.court_name, b.booking_date, b.time_slot, b.status, b.created_at,
            u.username, u.email
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     $where_sql
     ORDER BY b.created_at DESC
     LIMIT $per_page OFFSET $offset"
);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── KPI summary ───────────────────────────────────────────────────────────────
$kpi = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'pending')   AS pending,
        SUM(status = 'confirmed') AS confirmed,
        SUM(status = 'cancelled') AS cancelled
     FROM bookings"
)->fetch(PDO::FETCH_ASSOC);

// ── Total registered users ────────────────────────────────────────────────────
$total_users = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$admin_name = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – HoopHub</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --orange: #E8631A;
      --dark:   #1A1A2E;
      --muted:  #6B7280;
      --border: #E5E7EB;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', system-ui, sans-serif; }
    body { background: #F6F4EF; min-height: 100vh; }

    /* ── Top bar ── */
    .topbar {
      background: var(--dark); color: #fff;
      padding: 14px 28px; display: flex; align-items: center;
      justify-content: space-between; position: sticky; top: 0; z-index: 100;
    }
    .logo { font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .logo-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--orange); }
    .admin-badge {
      background: rgba(232,99,26,.2); border: 1px solid rgba(232,99,26,.4);
      border-radius: 20px; padding: 5px 14px; font-size: 12px; font-weight: 600;
      color: #FDBA74; display: flex; align-items: center; gap: 10px;
    }
    .logout {
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
      color: #fff; border-radius: 10px; padding: 5px 12px;
      font-size: 12px; cursor: pointer; text-decoration: none;
    }

    /* ── Layout ── */
    .page { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }

    /* ── KPI row ── */
    .kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 28px; }
    .kpi {
      background: #fff; border-radius: 14px; padding: 18px 20px;
      border: 0.5px solid var(--border);
    }
    .kpi-label { font-size: 11px; color: var(--muted); font-weight: 600;
                 text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
    .kpi-val   { font-size: 26px; font-weight: 700; color: #111; line-height: 1; }
    .kpi-sub   { font-size: 11px; color: var(--muted); margin-top: 3px; }

    /* ── Filter bar ── */
    .filter-bar {
      background: #fff; border-radius: 14px; border: 0.5px solid var(--border);
      padding: 16px 20px; display: flex; align-items: center;
      gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .filter-bar input[type=text] {
      border: 1px solid var(--border); border-radius: 10px;
      padding: 8px 14px; font-size: 13px; outline: none;
      flex: 1; min-width: 180px; font-family: inherit;
    }
    .filter-bar input[type=text]:focus { border-color: var(--orange); }
    .filter-tabs { display: flex; gap: 6px; }
    .tab-btn {
      border: 1px solid var(--border); background: #fff;
      border-radius: 20px; padding: 6px 14px; font-size: 12px;
      font-weight: 600; cursor: pointer; color: var(--muted);
      transition: all .12s; font-family: inherit;
    }
    .tab-btn:hover { background: #F6F4EF; }
    .tab-btn.active { background: var(--dark); color: #fff; border-color: var(--dark); }
    .tab-btn.active-pending   { background: #FFF7ED; color: #92400E; border-color: #FDE68A; }
    .tab-btn.active-confirmed { background: #F0FDF4; color: #166534; border-color: #86EFAC; }
    .tab-btn.active-cancelled { background: #FEF2F2; color: #991B1B; border-color: #FECACA; }
    .search-btn {
      background: var(--orange); color: #fff; border: none;
      border-radius: 10px; padding: 8px 18px; font-size: 13px;
      font-weight: 600; cursor: pointer; font-family: inherit;
    }

    /* ── Table ── */
    .table-card {
      background: #fff; border-radius: 16px; border: 0.5px solid var(--border);
      overflow: hidden; margin-bottom: 20px;
    }
    .table-header {
      padding: 18px 22px; border-bottom: 0.5px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .table-title { font-size: 15px; font-weight: 600; color: #111; }
    .table-count { font-size: 12px; color: var(--muted); }
    table { width: 100%; border-collapse: collapse; }
    th {
      text-align: left; font-size: 11px; font-weight: 600; color: var(--muted);
      text-transform: uppercase; letter-spacing: .5px;
      padding: 12px 22px; border-bottom: 0.5px solid var(--border);
      background: #FAFAFA;
    }
    td { padding: 14px 22px; font-size: 13px; color: #374151;
         border-bottom: 0.5px solid #F3F4F6; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #FAFAFA; }

    /* ── Status badges ── */
    .badge {
      display: inline-block; padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 600; letter-spacing: .3px;
    }
    .badge-pending   { background: #FFF7ED; color: #92400E; }
    .badge-confirmed { background: #F0FDF4; color: #166534; }
    .badge-cancelled { background: #FEF2F2; color: #991B1B; }

    /* ── Status select ── */
    .status-select {
      border: 1px solid var(--border); border-radius: 8px;
      padding: 5px 10px; font-size: 12px; font-weight: 600;
      cursor: pointer; outline: none; background: #fff;
      font-family: inherit; transition: border-color .12s;
    }
    .status-select:focus { border-color: var(--orange); }
    .status-select.pending   { color: #92400E; }
    .status-select.confirmed { color: #166534; }
    .status-select.cancelled { color: #991B1B; }

    /* ── Pagination ── */
    .pagination { display: flex; gap: 6px; justify-content: center; margin-top: 8px; }
    .page-btn {
      width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border);
      background: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #374151; text-decoration: none; transition: all .12s;
      font-family: inherit;
    }
    .page-btn:hover { background: #F6F4EF; }
    .page-btn.active { background: var(--dark); color: #fff; border-color: var(--dark); }
    .page-btn.disabled { opacity: 0.4; pointer-events: none; }

    /* ── Toast ── */
    .toast {
      position: fixed; bottom: 28px; left: 50%;
      transform: translateX(-50%) translateY(60px);
      background: var(--dark); color: #fff;
      padding: 12px 24px; border-radius: 14px;
      font-size: 14px; font-weight: 500; z-index: 300;
      transition: transform .25s; pointer-events: none;
    }
    .toast.show { transform: translateX(-50%) translateY(0); }

    .empty-state {
      text-align: center; padding: 52px 20px;
      color: var(--muted); font-size: 14px;
    }
    .empty-state .empty-icon { font-size: 40px; margin-bottom: 12px; }

    @media (max-width: 900px) {
      .kpi-grid { grid-template-columns: repeat(3, 1fr); }
      table { font-size: 12px; }
      td, th { padding: 10px 14px; }
    }
  </style>
</head>
<body>

<nav class="topbar">
  <div class="logo"><div class="logo-dot"></div>HoopHub Admin</div>
  <div style="display:flex;align-items:center;gap:12px;">
    <div class="admin-badge">⚡ <?= $admin_name ?></div>
    <a href="dashboard.php" class="logout">← Player View</a>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</nav>

<div class="page">

  <!-- KPI summary -->
  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-label">Total Users</div>
      <div class="kpi-val"><?= number_format($total_users) ?></div>
      <div class="kpi-sub">Registered</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">All Bookings</div>
      <div class="kpi-val"><?= number_format($kpi['total']) ?></div>
      <div class="kpi-sub">All time</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Pending</div>
      <div class="kpi-val" style="color:#92400E;"><?= number_format($kpi['pending']) ?></div>
      <div class="kpi-sub">Awaiting review</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Confirmed</div>
      <div class="kpi-val" style="color:#166534;"><?= number_format($kpi['confirmed']) ?></div>
      <div class="kpi-sub">Active</div>
    </div>
    <div class="kpi">
      <div class="kpi-label">Cancelled</div>
      <div class="kpi-val" style="color:#991B1B;"><?= number_format($kpi['cancelled']) ?></div>
      <div class="kpi-sub">Cancelled</div>
    </div>
  </div>

  <!-- Filter bar -->
  <form method="GET" action="">
    <div class="filter-bar">
      <div class="filter-tabs">
        <?php
        $tabs = [
          'all'       => 'All',
          'pending'   => 'Pending',
          'confirmed' => 'Confirmed',
          'cancelled' => 'Cancelled',
        ];
        foreach ($tabs as $val => $label):
          $active_class = ($filter_status === $val)
            ? ($val === 'all' ? 'active' : "active-$val")
            : '';
        ?>
          <button type="submit" name="status" value="<?= $val ?>"
            class="tab-btn <?= $active_class ?>">
            <?= $label ?>
          </button>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
      <input type="text" name="search" placeholder="Search player, email, court…"
        value="<?= htmlspecialchars($filter_search) ?>">
      <button type="submit" class="search-btn">Search</button>
    </div>
  </form>

  <!-- Bookings table -->
  <div class="table-card">
    <div class="table-header">
      <div class="table-title">Bookings</div>
      <div class="table-count"><?= number_format($total_rows) ?> result<?= $total_rows !== 1 ? 's' : '' ?></div>
    </div>

    <?php if (empty($bookings)): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        No bookings found for the current filter.
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Player</th>
          <th>Court</th>
          <th>Date</th>
          <th>Time Slot</th>
          <th>Booked On</th>
          <th>Status</th>
          <th>Change Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr id="row-<?= $b['id'] ?>">
          <td style="color:var(--muted);font-size:12px;">#<?= $b['id'] ?></td>
          <td>
            <div style="font-weight:600;color:#111;"><?= htmlspecialchars($b['username']) ?></div>
            <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($b['email']) ?></div>
          </td>
          <td><?= htmlspecialchars($b['court_name']) ?></td>
          <td><?= date('M j, Y', strtotime($b['booking_date'])) ?></td>
          <td><?= htmlspecialchars($b['time_slot']) ?></td>
          <td style="color:var(--muted);font-size:12px;">
            <?= date('M j, Y g:i A', strtotime($b['created_at'])) ?>
          </td>
          <td>
            <span class="badge badge-<?= $b['status'] ?>" id="badge-<?= $b['id'] ?>">
              <?= ucfirst($b['status']) ?>
            </span>
          </td>
          <td>
            <select class="status-select <?= $b['status'] ?>"
              id="select-<?= $b['id'] ?>"
              onchange="updateStatus(<?= $b['id'] ?>, this.value, this)">
              <option value="pending"   <?= $b['status'] === 'pending'   ? 'selected' : '' ?>>Pending</option>
              <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
              <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
  <div class="pagination">
    <?php
    $qs = http_build_query(['status' => $filter_status, 'search' => $filter_search]);
    ?>
    <a href="?<?= $qs ?>&page=<?= max(1, $page - 1) ?>"
       class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>

    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
      <a href="?<?= $qs ?>&page=<?= $p ?>"
         class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>

    <a href="?<?= $qs ?>&page=<?= min($total_pages, $page + 1) ?>"
       class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">›</a>
  </div>
  <?php endif; ?>

</div><!-- /.page -->

<div class="toast" id="toast"></div>

<script>
function updateStatus(bookingId, newStatus, selectEl) {
  selectEl.disabled = true;

  const fd = new FormData();
  fd.append('action',     'update_status');
  fd.append('booking_id', bookingId);
  fd.append('status',     newStatus);

  fetch(window.location.pathname, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      selectEl.disabled = false;

      if (!data.success) {
        showToast('Error: ' + data.message);
        return;
      }

      // Update badge
      const badge = document.getElementById('badge-' + bookingId);
      badge.className  = 'badge badge-' + newStatus;
      badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

      // Update select colour class
      selectEl.className = 'status-select ' + newStatus;

      showToast(data.message);
    })
    .catch(() => {
      selectEl.disabled = false;
      showToast('Network error — please try again.');
    });
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}
</script>

</body>
</html>