<?php
require_once 'config.php';

// ── ADMIN LOGIN ──────────────────────────────────────────────────
if (isset($_POST['admin_login'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Incorrect password.';
    }
}
if (isset($_GET['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    header('Location: admin.php');
    exit;
}
if (!($_SESSION['admin_logged_in'] ?? false)):
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= htmlspecialchars(EVENT_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body style="background:var(--dark);display:flex;align-items:center;justify-content:center;min-height:100vh;">
<div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:380px;box-shadow:0 8px 32px rgba(0,0,0,.4);">
    <div class="text-center mb-4">
        <div style="font-size:2.5rem;">🔐</div>
        <h5 class="fw-bold mt-2">Admin Panel</h5>
        <div class="text-muted" style="font-size:.85rem;"><?= htmlspecialchars(EVENT_NAME) ?></div>
    </div>
    <?php if (!empty($login_error)): ?>
    <div class="alert alert-danger py-2" style="font-size:.88rem;"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="admin_password" class="form-control" autofocus required>
        </div>
        <button name="admin_login" value="1" type="submit" class="btn-register">Login</button>
    </form>
</div>
</body>
</html>
<?php
    exit;
endif;

// ── ADMIN ACTIONS ────────────────────────────────────────────────
$message = '';

// Update payment/registration status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $upd_id      = (int) $_POST['update_id'];
    $pay_status  = in_array($_POST['payment_status'] ?? '', ['pending','verified','rejected'])
                   ? $_POST['payment_status'] : 'pending';
    $reg_status  = in_array($_POST['registration_status'] ?? '', ['pending','confirmed','cancelled'])
                   ? $_POST['registration_status'] : 'pending';
    $notes       = substr(htmlspecialchars(trim($_POST['notes'] ?? ''), ENT_QUOTES, 'UTF-8'), 0, 500);

    $upd = $conn->prepare(
        'UPDATE registrations SET payment_status=?, registration_status=?, notes=? WHERE id=?'
    );
    $upd->bind_param('sssi', $pay_status, $reg_status, $notes, $upd_id);
    $upd->execute();
    $upd->close();
    $message = 'Registration #' . $upd_id . ' updated successfully.';
}

// ── FILTERS ──────────────────────────────────────────────────────
$filter_status  = $_GET['status']   ?? '';
$filter_cat     = $_GET['cat']      ?? '';
$filter_pay     = $_GET['pay']      ?? '';
$search         = trim($_GET['q']   ?? '');

$where = ['1=1'];
$params = [];
$types  = '';

if ($filter_status !== '') {
    $where[] = 'payment_status = ?';
    $params[] = $filter_status;
    $types   .= 's';
}
if ($filter_cat !== '') {
    $where[] = 'category = ?';
    $params[] = $filter_cat;
    $types   .= 's';
}
if ($filter_pay !== '') {
    $where[] = 'payment_method = ?';
    $params[] = $filter_pay;
    $types   .= 's';
}
if ($search !== '') {
    $where[]  = '(reference_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
    $s = '%' . $search . '%';
    $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
    $types   .= 'ssss';
}

$where_sql = implode(' AND ', $where);

// Stats
$stats = [];
foreach (['pending','verified','rejected'] as $st) {
    $r = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(
        CASE category WHEN '3K' THEN 500 WHEN '5K' THEN 600 WHEN '10K' THEN 700 ELSE 0 END
    ),0) amt FROM registrations WHERE payment_status='$st'");
    $stats[$st] = $r->fetch_assoc();
}
$total_row = $conn->query("SELECT COUNT(*) c FROM registrations")->fetch_assoc();
$cash_row  = $conn->query("SELECT COUNT(*) c FROM registrations WHERE payment_method='cash'")->fetch_assoc();

// Category breakdown
$cat_rows = $conn->query("SELECT category, COUNT(*) c FROM registrations GROUP BY category ORDER BY category");
$cat_counts = [];
while ($row = $cat_rows->fetch_assoc()) $cat_counts[$row['category']] = $row['c'];

// Main query
$sql = "SELECT * FROM registrations WHERE $where_sql ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — <?= htmlspecialchars(EVENT_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="d-flex" style="min-height:100vh;">

    <!-- ── SIDEBAR ─────────────────────────────── -->
    <div class="admin-sidebar" style="width:220px;flex-shrink:0;">
        <div class="brand">🏃 Admin Panel</div>
        <div style="padding:14px 20px;font-size:.75rem;color:rgba(255,255,255,.45);border-bottom:1px solid rgba(255,255,255,.1);">
            <?= htmlspecialchars(EVENT_NAME) ?>
        </div>
        <a href="admin.php" class="<?= empty($_GET) ? 'active' : '' ?>">📋 All Registrations</a>
        <a href="admin.php?status=pending">⏳ Pending Verification</a>
        <a href="admin.php?status=verified">✅ Verified</a>
        <a href="admin.php?status=rejected">❌ Rejected</a>
        <a href="admin.php?pay=cash">💵 Cash Registrants</a>
        <div style="padding:10px 20px;font-size:.72rem;color:rgba(255,255,255,.3);margin-top:8px;">CATEGORIES</div>
        <?php foreach (CATEGORIES as $k => $c): ?>
        <a href="admin.php?cat=<?= $k ?>"><?= $k ?> — <?= $cat_counts[$k] ?? 0 ?> pax</a>
        <?php endforeach; ?>
        <div style="position:absolute;bottom:0;width:220px;padding:14px 20px;border-top:1px solid rgba(255,255,255,.1);">
            <a href="admin.php?logout=1" style="color:rgba(255,100,100,.7);font-size:.82rem;">🚪 Logout</a>
        </div>
    </div>

    <!-- ── MAIN CONTENT ────────────────────────── -->
    <div style="flex:1;padding:28px;background:var(--light-bg);overflow-x:auto;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars(EVENT_NAME) ?></h4>
                <div class="text-muted" style="font-size:.82rem;">
                    Registration ends <?= htmlspecialchars(EVENT_REG_END) ?> &nbsp;|&nbsp;
                    <?= htmlspecialchars(EVENT_LOCATION) ?>
                </div>
            </div>
            <a href="index.php" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                🌐 View Registration Page
            </a>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success py-2 mb-3" style="font-size:.88rem;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-num"><?= $total_row['c'] ?></div>
                    <div class="stat-label">TOTAL REGISTRANTS</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card yellow">
                    <div class="stat-num"><?= $stats['pending']['c'] ?></div>
                    <div class="stat-label">PENDING VERIFICATION</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card green">
                    <div class="stat-num"><?= $stats['verified']['c'] ?></div>
                    <div class="stat-label">VERIFIED / PAID</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-num">₱<?= number_format($stats['verified']['amt'] ?? 0) ?></div>
                    <div class="stat-label">TOTAL COLLECTED</div>
                </div>
            </div>
        </div>

        <!-- Search + filters -->
        <form method="GET" class="d-flex gap-2 mb-3 flex-wrap align-items-center">
            <input type="text" name="q" class="form-control" style="max-width:220px;border-radius:8px;"
                   placeholder="🔍 Search name / email / ref…" value="<?= htmlspecialchars($search) ?>">
            <select name="status" class="form-select" style="max-width:170px;border-radius:8px;">
                <option value="">All Status</option>
                <?php foreach (['pending','verified','rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="cat" class="form-select" style="max-width:130px;border-radius:8px;">
                <option value="">All Categories</option>
                <?php foreach (CATEGORIES as $k => $c): ?>
                <option value="<?= $k ?>" <?= $filter_cat === $k ? 'selected' : '' ?>><?= $k ?></option>
                <?php endforeach; ?>
            </select>
            <select name="pay" class="form-select" style="max-width:160px;border-radius:8px;">
                <option value="">All Payment</option>
                <option value="gcash"   <?= $filter_pay === 'gcash'   ? 'selected' : '' ?>>GCash</option>
                <option value="paymaya" <?= $filter_pay === 'paymaya' ? 'selected' : '' ?>>PayMaya</option>
                <option value="cash"    <?= $filter_pay === 'cash'    ? 'selected' : '' ?>>Cash</option>
            </select>
            <button type="submit" class="btn btn-primary" style="border-radius:8px;">Filter</button>
            <a href="admin.php" class="btn btn-outline-secondary" style="border-radius:8px;">Clear</a>
            <a href="admin.php?export=csv<?= !empty($_GET) ? '&' . http_build_query($_GET) : '' ?>"
               class="btn btn-success ms-auto" style="border-radius:8px;font-size:.85rem;">
                ⬇ Export CSV
            </a>
        </form>

        <!-- Registrations Table -->
        <div class="table-responsive" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Shirt</th>
                    <th>Payment</th>
                    <th>Pay Status</th>
                    <th>Reg Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No registrations found.</td></tr>
            <?php endif; ?>
            <?php while ($reg = $result->fetch_assoc()): ?>
            <tr>
                <td><strong style="font-size:.8rem;"><?= htmlspecialchars($reg['reference_number']) ?></strong></td>
                <td>
                    <div class="fw-bold" style="font-size:.85rem;">
                        <?= htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']) ?>
                    </div>
                    <div style="font-size:.75rem;color:var(--muted);"><?= htmlspecialchars($reg['email']) ?></div>
                    <div style="font-size:.75rem;color:var(--muted);"><?= htmlspecialchars($reg['phone']) ?></div>
                </td>
                <td><span class="fw-bold" style="color:var(--primary);"><?= htmlspecialchars($reg['category']) ?></span></td>
                <td><?= htmlspecialchars($reg['shirt_size']) ?></td>
                <td>
                    <span style="font-size:.8rem;font-weight:600;">
                        <?= ucfirst($reg['payment_method']) ?>
                    </span>
                    <?php if ($reg['payment_proof']): ?>
                    <br><a href="uploads/<?= htmlspecialchars($reg['payment_proof']) ?>"
                           target="_blank" style="font-size:.72rem;color:var(--primary);">View Proof</a>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge badge-<?= $reg['payment_status'] ?>">
                        <?= ucfirst($reg['payment_status']) ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge badge-<?= $reg['registration_status'] ?>">
                        <?= ucfirst($reg['registration_status']) ?>
                    </span>
                </td>
                <td style="font-size:.75rem;white-space:nowrap;">
                    <?= date('M d, Y', strtotime($reg['created_at'])) ?><br>
                    <span style="color:var(--muted);"><?= date('h:i A', strtotime($reg['created_at'])) ?></span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" style="font-size:.75rem;border-radius:6px;"
                        onclick="openModal(<?= $reg['id'] ?>,
                            '<?= addslashes($reg['payment_status']) ?>',
                            '<?= addslashes($reg['registration_status']) ?>',
                            '<?= addslashes(htmlspecialchars_decode($reg['notes'] ?? '')) ?>',
                            '<?= addslashes($reg['first_name'] . ' ' . $reg['last_name']) ?>')">
                        ✏️ Update
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <div class="text-muted mt-2" style="font-size:.78rem;">
            Showing <?= $result->num_rows ?> record(s).
        </div>

    </div><!-- /main -->
</div><!-- /flex -->

<!-- ── UPDATE MODAL ───────────────────────────── -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <form method="POST">
            <div class="modal-header" style="background:var(--dark);color:#fff;border-radius:14px 14px 0 0;">
                <h5 class="modal-title fw-bold">Update Registration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="update_id" id="modal_id">
                <p id="modal_name" class="fw-bold mb-3"></p>

                <div class="mb-3">
                    <label class="form-label fw-bold">Payment Status</label>
                    <select name="payment_status" id="modal_pay_status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="verified">Verified ✅</option>
                        <option value="rejected">Rejected ❌</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Registration Status</label>
                    <select name="registration_status" id="modal_reg_status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed ✅</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Notes (optional)</label>
                    <textarea name="notes" id="modal_notes" class="form-control" rows="3"
                              placeholder="e.g. Payment verified via GCash ref #..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modal = new bootstrap.Modal(document.getElementById('updateModal'));
function openModal(id, payStatus, regStatus, notes, name) {
    document.getElementById('modal_id').value = id;
    document.getElementById('modal_name').textContent = name;
    document.getElementById('modal_pay_status').value = payStatus;
    document.getElementById('modal_reg_status').value = regStatus;
    document.getElementById('modal_notes').value = notes;
    modal.show();
}
</script>
</body>
</html>
<?php
// ── CSV EXPORT ────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $conn2 = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn2->set_charset('utf8mb4');
    $export_result = $conn2->query("SELECT * FROM registrations WHERE $where_sql ORDER BY created_at DESC");

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="registrations_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Ref #','First Name','Last Name','Email','Phone','Address',
                   'Birthdate','Gender','Emergency Contact','Emergency Phone',
                   'Category','Fee','Shirt Size','Payment Method',
                   'Payment Status','Registration Status','Notes','Registered On']);
    while ($r = $export_result->fetch_assoc()) {
        fputcsv($out, [
            $r['reference_number'], $r['first_name'], $r['last_name'],
            $r['email'], $r['phone'], $r['address'], $r['birthdate'],
            $r['gender'], $r['emergency_contact_name'], $r['emergency_contact_number'],
            $r['category'],
            '₱' . number_format(CATEGORIES[$r['category']]['fee'] ?? 0),
            $r['shirt_size'], $r['payment_method'], $r['payment_status'],
            $r['registration_status'], $r['notes'],
            $r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}
