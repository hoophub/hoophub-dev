<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Player');
$bookings = $_SESSION['bookings'] ?? 12;
$hours    = $_SESSION['hours']    ?? 48;
$rating   = $_SESSION['rating']   ?? 4.8;
$rank     = $_SESSION['rank']     ?? 47;

$monthly_bookings = $_SESSION['monthly_bookings'] ?? [5, 10, 8, 12, 9, 14];
$weekly_hours     = $_SESSION['weekly_hours']     ?? [4, 6, 8, 5, 10, 7, 9, 6];
$court_usage      = $_SESSION['court_usage']      ?? ['Downtown Court' => 40, 'City Arena' => 35, 'Elite Complex' => 25];

$courts = [
    ['name' => 'Downtown Court',  'status' => 'available', 'status_label' => 'Available',     'emoji' => '🏟',  'bg' => 'linear-gradient(135deg,#0f0c29,#E8631A)'],
    ['name' => 'City Arena',      'status' => 'limited',   'status_label' => 'Limited Slots', 'emoji' => '🏀',  'bg' => 'linear-gradient(135deg,#0F172A,#3B82F6)'],
    ['name' => 'Elite Complex',   'status' => 'full',      'status_label' => 'Fully Booked',  'emoji' => '⛹',  'bg' => 'linear-gradient(135deg,#374151,#6B7280)'],
];

// Handle AJAX booking POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    header('Content-Type: application/json');
    $court_name = htmlspecialchars(trim($_POST['court'] ?? ''));
    $date       = htmlspecialchars(trim($_POST['date']  ?? ''));
    $time_slot  = htmlspecialchars(trim($_POST['time']  ?? ''));
    if (!$court_name || !$date || !$time_slot) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }
    // Simulate DB insert + update session stats
    $_SESSION['bookings'] = ($_SESSION['bookings'] ?? 12) + 1;
    $_SESSION['hours']    = ($_SESSION['hours']    ?? 48)  + 2;

    // Update monthly (current month index)
    $mi = (int)date('n') - 1; // 0-indexed
    if (!isset($_SESSION['monthly_bookings'])) $_SESSION['monthly_bookings'] = $monthly_bookings;
    $_SESSION['monthly_bookings'][$mi] = ($_SESSION['monthly_bookings'][$mi] ?? 0) + 1;

    // Update weekly hours (last week)
    if (!isset($_SESSION['weekly_hours'])) $_SESSION['weekly_hours'] = $weekly_hours;
    $last = count($_SESSION['weekly_hours']) - 1;
    $_SESSION['weekly_hours'][$last] = ($_SESSION['weekly_hours'][$last] ?? 0) + 2;

    // Update court usage
    if (!isset($_SESSION['court_usage'])) $_SESSION['court_usage'] = $court_usage;
    if (isset($_SESSION['court_usage'][$court_name])) {
        $_SESSION['court_usage'][$court_name] += 2;
    }

    echo json_encode([
        'success'         => true,
        'message'         => "🏀 Booked! See you on the hardwood.",
        'bookings'        => $_SESSION['bookings'],
        'hours'           => $_SESSION['hours'],
        'monthly_bookings'=> array_values($_SESSION['monthly_bookings']),
        'weekly_hours'    => array_values($_SESSION['weekly_hours']),
        'court_usage'     => array_values($_SESSION['court_usage']),
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – HoopHub</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --orange: #E8631A;
  --orange2: #FF8A47;
  --dark: #0D0D1A;
  --dark2: #1A1A2E;
  --surface: #141420;
  --surface2: #1e1e30;
  --border: rgba(255,255,255,0.07);
  --muted: rgba(255,255,255,0.38);
  --text: #F0EEE8;
  --green: #2ECC71;
  --blue: #3B82F6;
  --yellow: #F59E0B;
  --red: #EF4444;
  --radius: 18px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  background: var(--dark);
  color: var(--text);
  font-family: 'Manrope', sans-serif;
  min-height: 100vh;
  overflow-x: hidden;
  background-image:
    radial-gradient(ellipse 80% 40% at 20% -10%, rgba(232,99,26,0.12) 0%, transparent 60%),
    radial-gradient(ellipse 60% 30% at 80% 110%, rgba(59,130,246,0.08) 0%, transparent 60%);
}

/* ── NAV ── */
nav {
  position: sticky; top: 0; z-index: 200;
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 32px;
  background: rgba(13,13,26,0.85);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
}
.logo {
  font-family: 'Syne', sans-serif;
  font-weight: 800; font-size: 20px; letter-spacing: -0.5px;
  display: flex; align-items: center; gap: 8px;
}
.logo-icon {
  width: 32px; height: 32px; border-radius: 10px;
  background: var(--orange);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px;
}
.nav-right { display: flex; align-items: center; gap: 10px; }
.nav-badge {
  font-size: 13px; font-weight: 600;
  color: var(--muted);
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 40px; padding: 6px 14px;
  display: flex; align-items: center; gap: 6px;
}
.nav-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--green); }
.logout-btn {
  font-family: 'Manrope', sans-serif;
  font-size: 12px; font-weight: 700;
  background: transparent; border: 1px solid rgba(255,255,255,0.15);
  color: var(--muted); border-radius: 40px; padding: 6px 14px;
  cursor: pointer; transition: all .2s; text-decoration: none;
}
.logout-btn:hover { border-color: var(--orange); color: var(--orange); }

/* ── PAGE ── */
.page { max-width: 1140px; margin: 0 auto; padding: 36px 24px 80px; }

/* ── HERO ── */
.hero {
  border-radius: 24px;
  background: linear-gradient(130deg, #1A1A2E 0%, #0f0c29 60%, rgba(232,99,26,0.15) 100%);
  border: 1px solid var(--border);
  padding: 38px 40px;
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 28px; position: relative; overflow: hidden;
}
.hero::before {
  content: ''; position: absolute;
  right: -60px; top: -60px;
  width: 280px; height: 280px; border-radius: 50%;
  background: radial-gradient(circle, rgba(232,99,26,0.18) 0%, transparent 70%);
  pointer-events: none;
}
.hero-eyebrow {
  font-family: 'DM Mono', monospace;
  font-size: 11px; color: var(--orange);
  letter-spacing: 2px; text-transform: uppercase;
  margin-bottom: 10px;
}
.hero h1 {
  font-family: 'Syne', sans-serif;
  font-size: 30px; font-weight: 800; line-height: 1.1;
  margin-bottom: 8px;
}
.hero-sub { font-size: 14px; color: var(--muted); }
.hero-right {
  display: flex; flex-direction: column; align-items: flex-end; gap: 10px;
}
.hero-ball { font-size: 72px; line-height: 1; filter: drop-shadow(0 0 40px rgba(232,99,26,0.5)); }
.hero-date {
  font-family: 'DM Mono', monospace;
  font-size: 11px; color: var(--muted); letter-spacing: 0.5px;
}

/* ── KPI GRID ── */
.kpi-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 16px; margin-bottom: 28px;
}
.kpi {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 22px 22px 20px;
  position: relative; overflow: hidden;
  transition: border-color .2s, transform .2s;
}
.kpi:hover { border-color: rgba(232,99,26,0.3); transform: translateY(-2px); }
.kpi::after {
  content: ''; position: absolute;
  inset: 0; border-radius: inherit;
  background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, transparent 60%);
  pointer-events: none;
}
.kpi-icon { font-size: 18px; margin-bottom: 14px; }
.kpi-label {
  font-family: 'DM Mono', monospace;
  font-size: 10px; letter-spacing: 1.5px;
  text-transform: uppercase; color: var(--muted);
  margin-bottom: 6px;
}
.kpi-val {
  font-family: 'Syne', sans-serif;
  font-size: 34px; font-weight: 800; line-height: 1;
  color: var(--text); margin-bottom: 6px;
}
.kpi-delta {
  font-size: 12px; font-weight: 600;
  display: flex; align-items: center; gap: 4px;
}
.kpi-delta.up { color: var(--green); }
.kpi-delta.neutral { color: var(--muted); }
.kpi-bar {
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px; border-radius: 0 0 var(--radius) var(--radius);
}

/* ── SECTION TITLE ── */
.section-hd {
  display: flex; align-items: baseline; gap: 10px;
  margin-bottom: 16px;
}
.section-hd h2 {
  font-family: 'Syne', sans-serif;
  font-size: 16px; font-weight: 700;
}
.section-hd span {
  font-family: 'DM Mono', monospace;
  font-size: 11px; color: var(--muted);
}

/* ── QUICK ACTIONS ── */
.quick-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 12px; margin-bottom: 32px;
}
.qa {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 14px; padding: 18px 14px;
  text-align: center; cursor: pointer;
  font-size: 13px; font-weight: 700;
  color: var(--text); transition: all .15s;
}
.qa:hover { border-color: rgba(255,255,255,0.2); background: var(--surface2); transform: translateY(-2px); }
.qa.primary {
  background: var(--orange);
  border-color: var(--orange); color: #fff;
  box-shadow: 0 8px 32px rgba(232,99,26,0.35);
}
.qa.primary:hover { background: #d05616; box-shadow: 0 12px 40px rgba(232,99,26,0.45); }
.qa-icon { font-size: 24px; display: block; margin-bottom: 10px; }

/* ── CHARTS ── */
.charts-row {
  display: grid; grid-template-columns: 1.6fr 1fr;
  gap: 20px; margin-bottom: 20px;
}
.chart-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius); padding: 24px;
}
.chart-card.full { grid-column: 1 / -1; }

/* Chart tooltip override */
.chartjs-tooltip { font-family: 'Manrope', sans-serif !important; }

/* Doughnut legend */
.donut-legend { display: flex; flex-direction: column; gap: 8px; margin-top: 14px; }
.dl-item { display: flex; align-items: center; justify-content: space-between; }
.dl-left { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted); }
.dl-dot { width: 8px; height: 8px; border-radius: 2px; }
.dl-pct { font-family: 'DM Mono', monospace; font-size: 12px; color: var(--text); font-weight: 600; }

/* ── COURTS ── */
.courts-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 16px; margin-bottom: 28px;
}
.court-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius); overflow: hidden;
  transition: transform .2s, border-color .2s;
}
.court-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.15); }
.court-thumb {
  height: 130px; display: flex;
  align-items: center; justify-content: center;
  font-size: 50px; position: relative; overflow: hidden;
}
.court-thumb::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(to bottom, transparent 40%, rgba(0,0,0,0.4));
}
.court-body { padding: 16px; }
.court-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 15px; margin-bottom: 4px; }
.court-status { font-size: 12px; margin-bottom: 14px; display: flex; align-items: center; gap: 5px; }
.dot-green { color: var(--green); }
.dot-yellow { color: var(--yellow); }
.dot-red { color: var(--red); }
.book-btn {
  width: 100%; padding: 10px; border-radius: 10px;
  border: none; font-family: 'Manrope', sans-serif;
  font-size: 13px; font-weight: 700; cursor: pointer; transition: all .12s;
}
.book-btn-active { background: var(--orange); color: #fff; }
.book-btn-active:hover { background: #d05616; transform: scale(0.98); }
.book-btn-disabled { background: rgba(255,255,255,0.06); color: var(--muted); cursor: default; }

/* ── MODAL ── */
.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  z-index: 300; align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 24px; padding: 32px;
  width: 380px; max-width: 92vw;
  animation: slideUp .25s ease;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px) scale(0.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header { margin-bottom: 24px; }
.modal-title {
  font-family: 'Syne', sans-serif;
  font-size: 22px; font-weight: 800; margin-bottom: 4px;
}
.modal-sub { font-size: 13px; color: var(--muted); }
.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.field label {
  font-family: 'DM Mono', monospace;
  font-size: 10px; letter-spacing: 1.5px;
  text-transform: uppercase; color: var(--muted);
}
.field select, .field input {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 12px; padding: 12px 14px;
  font-family: 'Manrope', sans-serif;
  font-size: 14px; color: var(--text);
  outline: none; appearance: none; transition: border-color .15s;
}
.field select:focus, .field input:focus { border-color: var(--orange); }
.field select option { background: var(--surface); }
.modal-footer { display: flex; gap: 10px; margin-top: 24px; }
.btn-confirm {
  flex: 1; background: var(--orange); color: #fff;
  border: none; border-radius: 14px; padding: 13px;
  font-family: 'Manrope', sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; transition: all .15s;
  box-shadow: 0 8px 24px rgba(232,99,26,0.3);
}
.btn-confirm:hover { background: #d05616; }
.btn-confirm:disabled { opacity: .5; cursor: default; }
.btn-cancel {
  flex: 1; background: transparent; color: var(--muted);
  border: 1px solid var(--border); border-radius: 14px; padding: 13px;
  font-family: 'Manrope', sans-serif; font-size: 14px; font-weight: 600;
  cursor: pointer; transition: all .15s;
}
.btn-cancel:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }

/* ── TOAST ── */
.toast {
  position: fixed; bottom: 32px; left: 50%;
  transform: translateX(-50%) translateY(80px);
  background: var(--surface2);
  border: 1px solid var(--border);
  color: var(--text);
  padding: 14px 24px; border-radius: 16px;
  font-size: 14px; font-weight: 600;
  z-index: 400; transition: transform .3s cubic-bezier(.34,1.56,.64,1);
  white-space: nowrap; pointer-events: none;
  backdrop-filter: blur(20px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.toast.show { transform: translateX(-50%) translateY(0); }

/* ── LIVE INDICATOR ── */
.live-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-family: 'DM Mono', monospace;
  font-size: 10px; letter-spacing: 1px;
  color: var(--green); background: rgba(46,204,113,0.1);
  border: 1px solid rgba(46,204,113,0.2);
  border-radius: 40px; padding: 3px 10px;
}
.live-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--green); animation: pulse 1.5s infinite;
}
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }

/* ── BOOKING HISTORY ── */
.history-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius); padding: 22px;
  margin-bottom: 28px;
}
.history-empty {
  font-size: 13px; color: var(--muted); text-align: center;
  padding: 24px 0; font-family: 'DM Mono', monospace;
}
.history-table { width: 100%; border-collapse: collapse; }
.history-table th {
  font-family: 'DM Mono', monospace;
  font-size: 10px; letter-spacing: 1.5px;
  text-transform: uppercase; color: var(--muted);
  text-align: left; padding: 8px 12px;
  border-bottom: 1px solid var(--border);
}
.history-table td {
  font-size: 13px; padding: 12px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.04);
}
.history-table tr:last-child td { border-bottom: none; }
.badge-court {
  background: rgba(232,99,26,0.12);
  color: var(--orange); font-weight: 700;
  padding: 3px 10px; border-radius: 6px;
  font-size: 12px;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
  .kpi-grid, .quick-grid { grid-template-columns: repeat(2, 1fr); }
  .charts-row { grid-template-columns: 1fr; }
  .courts-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 540px) {
  .courts-grid { grid-template-columns: 1fr; }
  .hero { padding: 24px; }
  .hero h1 { font-size: 22px; }
  .hero-ball { font-size: 48px; }
}
</style>
</head>
<body>

<nav>
  <div class="logo">
    <div class="logo-icon">🏀</div>
    HoopHub
  </div>
  <div class="nav-right">
    <div class="nav-badge">👤 <?= $username ?></div>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<div class="page">

  <!-- HERO -->
  <div class="hero">
    <div class="hero-left">
      <div class="hero-eyebrow">▸ PLAYER DASHBOARD</div>
      <h1>Welcome back,<br><?= $username ?>!</h1>
      <div class="hero-sub">Your stats update live with every booking.</div>
    </div>
    <div class="hero-right">
      <div class="hero-ball">🏀</div>
      <div class="hero-date" id="hero-date"></div>
    </div>
  </div>

  <!-- KPI -->
  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-icon">📅</div>
      <div class="kpi-label">Total Bookings</div>
      <div class="kpi-val" id="kpi-bookings"><?= $bookings ?></div>
      <div class="kpi-delta up">↑ +2 this week</div>
      <div class="kpi-bar" style="background:var(--orange)"></div>
    </div>
    <div class="kpi">
      <div class="kpi-icon">⏱</div>
      <div class="kpi-label">Hours Played</div>
      <div class="kpi-val" id="kpi-hours"><?= $hours ?></div>
      <div class="kpi-delta up">↑ +6 this week</div>
      <div class="kpi-bar" style="background:var(--blue)"></div>
    </div>
    <div class="kpi">
      <div class="kpi-icon">⭐</div>
      <div class="kpi-label">Player Rating</div>
      <div class="kpi-val"><?= number_format($rating, 1) ?></div>
      <div class="kpi-delta neutral">Top 5%</div>
      <div class="kpi-bar" style="background:var(--yellow)"></div>
    </div>
    <div class="kpi">
      <div class="kpi-icon">🏆</div>
      <div class="kpi-label">Leaderboard</div>
      <div class="kpi-val">#<?= $rank ?></div>
      <div class="kpi-delta up">↑ Up 3 spots</div>
      <div class="kpi-bar" style="background:var(--green)"></div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="section-hd">
    <h2>Quick Actions</h2>
    <span>tap to act instantly</span>
  </div>
  <div class="quick-grid">
    <div class="qa primary" onclick="openModal(null)">
      <span class="qa-icon">🏀</span>Book a Court
    </div>
    <div class="qa" onclick="showToast('📋 Viewing your bookings…')">
      <span class="qa-icon">📋</span>My Bookings
    </div>
    <div class="qa" onclick="showToast('👥 Finding players near you…')">
      <span class="qa-icon">👥</span>Find Players
    </div>
    <div class="qa" onclick="showToast('⚙️ Settings coming soon!')">
      <span class="qa-icon">⚙️</span>Settings
    </div>
  </div>

  <!-- CHARTS ROW -->
  <div class="section-hd">
    <h2>Analytics</h2>
    <div class="live-badge"><div class="live-dot"></div>LIVE</div>
  </div>

  <div class="charts-row" style="margin-bottom:20px">
    <div class="chart-card">
      <div class="section-hd" style="margin-bottom:16px">
        <h2 style="font-size:14px">Monthly Bookings</h2>
        <span>updates on booking</span>
      </div>
      <div style="position:relative;height:230px">
        <canvas id="chartMonthly"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <div class="section-hd" style="margin-bottom:10px">
        <h2 style="font-size:14px">Court Usage</h2>
      </div>
      <div style="position:relative;height:160px">
        <canvas id="chartDonut"></canvas>
      </div>
      <div class="donut-legend" id="donutLegend"></div>
    </div>
  </div>

  <div class="chart-card" style="margin-bottom:28px">
    <div class="section-hd" style="margin-bottom:16px">
      <h2 style="font-size:14px">Hours Played — Weekly Trend</h2>
      <span>last 8 weeks</span>
    </div>
    <div style="position:relative;height:180px">
      <canvas id="chartHours"></canvas>
    </div>
  </div>

  <!-- BOOKING HISTORY -->
  <div class="section-hd">
    <h2>Session History</h2>
    <span>this session</span>
  </div>
  <div class="history-card" id="historyCard">
    <div class="history-empty" id="historyEmpty">— No bookings yet this session —</div>
    <table class="history-table" id="historyTable" style="display:none">
      <thead>
        <tr>
          <th>Court</th>
          <th>Date</th>
          <th>Time Slot</th>
          <th>Booked at</th>
        </tr>
      </thead>
      <tbody id="historyBody"></tbody>
    </table>
  </div>

  <!-- COURTS -->
  <div class="section-hd">
    <h2>Recommended Courts</h2>
    <span>live availability</span>
  </div>
  <div class="courts-grid">
    <?php foreach ($courts as $court): ?>
    <div class="court-card">
      <div class="court-thumb" style="background:<?= $court['bg'] ?>"><?= $court['emoji'] ?></div>
      <div class="court-body">
        <div class="court-name"><?= htmlspecialchars($court['name']) ?></div>
        <div class="court-status">
          <?php if ($court['status'] === 'available'): ?>
            <span class="dot-green">●</span> Available
          <?php elseif ($court['status'] === 'limited'): ?>
            <span class="dot-yellow">●</span> Limited Slots
          <?php else: ?>
            <span class="dot-red">●</span> Fully Booked
          <?php endif; ?>
        </div>
        <?php if ($court['status'] !== 'full'): ?>
          <button class="book-btn book-btn-active"
            onclick="openModal('<?= htmlspecialchars($court['name']) ?>')">
            Quick Book
          </button>
        <?php else: ?>
          <button class="book-btn book-btn-disabled" disabled>Fully Booked</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div><!-- /.page -->

<!-- BOOKING MODAL -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Book a Court</div>
      <div class="modal-sub" id="modal-sub">Select your time and confirm.</div>
    </div>
    <div class="field">
      <label>Court</label>
      <select id="modal-court">
        <?php foreach ($courts as $c): if ($c['status'] !== 'full'): ?>
          <option><?= htmlspecialchars($c['name']) ?></option>
        <?php endif; endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Date</label>
      <input type="date" id="modal-date">
    </div>
    <div class="field">
      <label>Time Slot</label>
      <select id="modal-time">
        <option>8:00 AM – 10:00 AM</option>
        <option>10:00 AM – 12:00 PM</option>
        <option>2:00 PM – 4:00 PM</option>
        <option>5:00 PM – 7:00 PM</option>
        <option>7:00 PM – 9:00 PM</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancel</button>
      <button class="btn-confirm" id="confirmBtn" onclick="confirmBooking()">Confirm Booking</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<!-- PHP → JS data bridge -->
<script>
const PHP = {
  monthly: <?= json_encode(array_values($monthly_bookings)) ?>,
  weekly:  <?= json_encode(array_values($weekly_hours)) ?>,
  donut:   <?= json_encode(array_values($court_usage)) ?>,
  courts:  <?= json_encode(array_column($courts, 'name')) ?>,
  bookings: <?= (int)$bookings ?>,
  hours:    <?= (int)$hours ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
/* ── Date ── */
(function(){
  const d = new Date();
  const opts = { weekday:'short', year:'numeric', month:'short', day:'numeric' };
  document.getElementById('hero-date').textContent = d.toLocaleDateString('en-US', opts).toUpperCase();
  document.getElementById('modal-date').valueAsDate = d;
})();

/* ── Chart defaults ── */
Chart.defaults.color = 'rgba(255,255,255,0.38)';
Chart.defaults.font.family = "'Manrope', sans-serif";

const ORANGE = '#E8631A', BLUE = '#3B82F6', GREEN = '#2ECC71';
const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const currentMonth = new Date().getMonth();
const monthLabels = MONTHS.slice(Math.max(0, currentMonth - 5), currentMonth + 1);

/* ── Bar chart ── */
const ctxBar = document.getElementById('chartMonthly').getContext('2d');
const gradient = ctxBar.createLinearGradient(0, 0, 0, 250);
gradient.addColorStop(0, ORANGE);
gradient.addColorStop(1, 'rgba(232,99,26,0.4)');

const barChart = new Chart(ctxBar, {
  type: 'bar',
  data: {
    labels: monthLabels,
    datasets: [{
      label: 'Bookings',
      data: [...PHP.monthly],
      backgroundColor: PHP.monthly.map((_, i) => i === PHP.monthly.length - 1 ? ORANGE : 'rgba(232,99,26,0.3)'),
      borderRadius: 10,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    animation: { duration: 600, easing: 'easeOutQuart' },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1e1e30',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1,
        titleFont: { family: "'DM Mono', monospace", size: 10 },
        bodyFont: { size: 13, weight: '700' },
        padding: 12,
        callbacks: { label: ctx => ` ${ctx.parsed.y} bookings` }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 } } },
      y: {
        grid: { color: 'rgba(255,255,255,0.05)' },
        ticks: { font: { size: 11 }, stepSize: 2 },
        beginAtZero: true
      }
    }
  }
});

/* ── Donut chart ── */
const courtNames = Object.keys(<?= json_encode($court_usage) ?>);
const donutColors = [ORANGE, '#1A1A2E', '#6B7280', BLUE, GREEN];

function buildDonutLegend(data) {
  const el = document.getElementById('donutLegend');
  const total = data.reduce((a,b) => a+b, 0);
  el.innerHTML = courtNames.map((name, i) => `
    <div class="dl-item">
      <div class="dl-left">
        <div class="dl-dot" style="background:${donutColors[i]}"></div>
        ${name}
      </div>
      <div class="dl-pct">${Math.round(data[i]/total*100)}%</div>
    </div>
  `).join('');
}

const donutChart = new Chart(document.getElementById('chartDonut'), {
  type: 'doughnut',
  data: {
    labels: courtNames,
    datasets: [{
      data: [...PHP.donut],
      backgroundColor: donutColors,
      borderWidth: 0, hoverOffset: 8,
      borderRadius: 4
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    animation: { duration: 600 },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1e1e30',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1,
        padding: 12,
        callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` }
      }
    },
    cutout: '72%'
  }
});
buildDonutLegend(PHP.donut);

/* ── Hours line chart ── */
const ctxLine = document.getElementById('chartHours').getContext('2d');
const lineGrad = ctxLine.createLinearGradient(0, 0, 0, 200);
lineGrad.addColorStop(0, 'rgba(232,99,26,0.25)');
lineGrad.addColorStop(1, 'rgba(232,99,26,0)');

const lineChart = new Chart(ctxLine, {
  type: 'line',
  data: {
    labels: PHP.weekly.map((_, i) => `Wk ${i+1}`),
    datasets: [{
      label: 'Hours',
      data: [...PHP.weekly],
      borderColor: ORANGE,
      backgroundColor: lineGrad,
      fill: true, tension: 0.45,
      pointBackgroundColor: ORANGE,
      pointRadius: 5, pointHoverRadius: 7,
      borderWidth: 2.5
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    animation: { duration: 600, easing: 'easeOutQuart' },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1e1e30',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1, padding: 12,
        callbacks: { label: ctx => ` ${ctx.parsed.y} hrs` }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 } } },
      y: {
        grid: { color: 'rgba(255,255,255,0.05)' },
        ticks: { font: { size: 11 } },
        beginAtZero: true
      }
    }
  }
});

/* ── Booking history ── */
const bookingHistory = [];

function addHistoryRow(court, date, time) {
  bookingHistory.unshift({ court, date, time, at: new Date().toLocaleTimeString() });
  document.getElementById('historyEmpty').style.display = 'none';
  document.getElementById('historyTable').style.display = 'table';
  const tbody = document.getElementById('historyBody');
  tbody.innerHTML = bookingHistory.map(b => `
    <tr>
      <td><span class="badge-court">${b.court}</span></td>
      <td>${b.date}</td>
      <td>${b.time}</td>
      <td style="color:var(--muted);font-size:12px">${b.at}</td>
    </tr>
  `).join('');
}

/* ── Live chart update ── */
function updateCharts(data) {
  // Bar chart — update last bar, highlight it
  barChart.data.datasets[0].data = data.monthly_bookings;
  barChart.data.datasets[0].backgroundColor = data.monthly_bookings.map((_, i) =>
    i === data.monthly_bookings.length - 1 ? ORANGE : 'rgba(232,99,26,0.3)'
  );
  barChart.update();

  // Line chart
  lineChart.data.datasets[0].data = data.weekly_hours;
  lineChart.update();

  // Donut
  donutChart.data.datasets[0].data = data.court_usage;
  donutChart.update();
  buildDonutLegend(data.court_usage);

  // KPIs — animate count
  animateCount('kpi-bookings', data.bookings);
  animateCount('kpi-hours', data.hours);
}

function animateCount(id, target) {
  const el = document.getElementById(id);
  const start = parseInt(el.textContent, 10);
  const diff = target - start;
  let step = 0;
  const steps = 20;
  const timer = setInterval(() => {
    step++;
    el.textContent = Math.round(start + diff * (step / steps));
    if (step >= steps) clearInterval(timer);
  }, 18);
}

/* ── Modal ── */
function openModal(court) {
  document.getElementById('modalOverlay').classList.add('open');
  if (court) {
    document.getElementById('modal-court').value = court;
    document.getElementById('modal-sub').textContent = '📍 ' + court;
  } else {
    document.getElementById('modal-sub').textContent = 'Select your time and confirm.';
  }
}
function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
}
document.getElementById('modalOverlay').addEventListener('click', e => {
  if (e.target === document.getElementById('modalOverlay')) closeModal();
});

/* ── Confirm booking ── */
function confirmBooking() {
  const court = document.getElementById('modal-court').value;
  const date  = document.getElementById('modal-date').value;
  const time  = document.getElementById('modal-time').value;
  if (!date) { showToast('📅 Please pick a date!'); return; }

  const btn = document.getElementById('confirmBtn');
  btn.disabled = true;
  btn.textContent = '⏳ Booking…';

  const fd = new FormData();
  fd.append('action', 'book');
  fd.append('court', court);
  fd.append('date', date);
  fd.append('time', time);

  fetch(window.location.href, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.textContent = 'Confirm Booking';
      if (!data.success) { showToast(data.message); return; }
      updateCharts(data);
      addHistoryRow(court, date, time);
      closeModal();
      showToast(data.message);
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Confirm Booking';
      showToast('⚠️ Network error — try again.');
    });
}

/* ── Toast ── */
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>