<?php
require_once 'config.php';

if (empty($_SESSION['reg_success'])) {
    header('Location: index.php');
    exit;
}
$d = $_SESSION['reg_success'];
unset($_SESSION['reg_success']);

$pay_label = ['gcash' => 'GCash', 'paymaya' => 'PayMaya / Maya', 'cash' => 'Cash (Walk-in)'];
$is_online = in_array($d['payment_method'], ['gcash','paymaya']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Confirmed — <?= htmlspecialchars(EVENT_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="event-hero" style="padding:32px 24px 24px;">
    <h1 style="font-size:1.5rem;"><?= htmlspecialchars(EVENT_NAME) ?></h1>
    <div class="meta">
        📅 <?= htmlspecialchars(EVENT_DATE) ?> &nbsp;|&nbsp;
        📍 <?= htmlspecialchars(EVENT_LOCATION) ?>
    </div>
</div>

<div class="container" style="max-width:680px;padding:40px 16px 64px;">

    <!-- Success card -->
    <div class="form-card text-center mb-4">
        <span class="success-icon">✅</span>
        <h2 class="mt-3 mb-1" style="font-weight:800;">Registration Submitted!</h2>
        <p class="text-muted mb-3">Thank you, <strong><?= htmlspecialchars($d['name']) ?></strong>!<br>
            Your registration has been received and is now <em>pending verification</em>.
        </p>
        <div>Your Reference Number:</div>
        <div class="ref-number"><?= htmlspecialchars($d['ref_num']) ?></div>
        <p class="text-muted mt-2" style="font-size:.82rem;">
            📌 Save or screenshot this reference number for follow-up.
        </p>
    </div>

    <!-- Registration details summary -->
    <div class="form-card mb-4">
        <div class="section-title">Registration Summary</div>
        <table class="table table-sm mb-0" style="font-size:.9rem;">
            <tbody>
                <tr><td class="fw-bold" style="width:45%;">Name</td>
                    <td><?= htmlspecialchars($d['name']) ?></td></tr>
                <tr><td class="fw-bold">Email</td>
                    <td><?= htmlspecialchars($d['email']) ?></td></tr>
                <tr><td class="fw-bold">Race Category</td>
                    <td><?= htmlspecialchars($d['category']) ?></td></tr>
                <tr><td class="fw-bold">Registration Fee</td>
                    <td><strong style="color:var(--primary)">₱<?= number_format($d['fee']) ?></strong></td></tr>
                <tr><td class="fw-bold">Singlet Size</td>
                    <td><?= htmlspecialchars($d['shirt_size']) ?></td></tr>
                <tr><td class="fw-bold">Payment Method</td>
                    <td><?= htmlspecialchars($pay_label[$d['payment_method']]) ?></td></tr>
                <tr><td class="fw-bold">Payment Status</td>
                    <td>
                        <?php if ($d['payment_method'] === 'cash'): ?>
                            <span class="status-badge badge-confirmed">Cash – Pay on site</span>
                        <?php else: ?>
                            <span class="status-badge badge-pending">Pending Verification</span>
                        <?php endif; ?>
                    </td></tr>
            </tbody>
        </table>
    </div>

    <!-- What's next -->
    <div class="form-card mb-4">
        <div class="section-title">What Happens Next?</div>
        <?php if ($is_online): ?>
        <ol style="font-size:.9rem;line-height:2;">
            <li>Our team will verify your payment proof within <strong>1–2 business days</strong>.</li>
            <li>Once verified, your registration status will be updated to <strong>Confirmed</strong>.</li>
            <li>You may check your status by contacting us with your reference number.</li>
            <li>Instructions for <strong>race kit claiming</strong> will be announced via our official pages.</li>
        </ol>
        <?php else: ?>
        <ol style="font-size:.9rem;line-height:2;">
            <li>Bring your <strong>reference number</strong> (screenshot or printout) on the day of kit claiming.</li>
            <li>Pay your registration fee of <strong>₱<?= number_format($d['fee']) ?></strong> at the registration desk.</li>
            <li>Receive your race kit — bib number, shirt, and event details.</li>
        </ol>
        <?php endif; ?>
    </div>

    <!-- Payment reminder for online payments -->
    <?php if ($is_online): ?>
    <div class="form-card mb-4" style="border-left:4px solid var(--success);">
        <div class="section-title" style="color:var(--success);border-color:var(--success);">
            ⚠️ Payment Reminder
        </div>
        <?php if ($d['payment_method'] === 'gcash'): ?>
        <p style="font-size:.9rem;margin:0;">
            If you haven't sent your GCash payment yet, please send
            <strong>₱<?= number_format($d['fee']) ?></strong> to:<br>
            📱 <span style="font-size:1rem;font-weight:700;color:var(--primary)"><?= htmlspecialchars(GCASH_NUMBER) ?></span>
            — <strong><?= htmlspecialchars(GCASH_NAME) ?></strong><br>
            📱 <span style="font-size:1rem;font-weight:700;color:var(--primary)"><?= htmlspecialchars(GCASH_NUMBER_2) ?></span>
            — <strong><?= htmlspecialchars(GCASH_NAME_2) ?></strong>
        </p>
        <?php else: ?>
        <p style="font-size:.9rem;margin:0;">
            If you haven't sent your PayMaya/Maya payment yet, please send
            <strong>₱<?= number_format($d['fee']) ?></strong> to:<br>
            📱 <span style="font-size:1rem;font-weight:700;color:var(--primary)"><?= htmlspecialchars(PAYMAYA_NUMBER) ?></span>
            — <strong><?= htmlspecialchars(PAYMAYA_NAME) ?></strong>
        </p>
        <?php endif; ?>
        <p style="font-size:.8rem;color:var(--muted);margin-top:8px;">
            Use your reference number <strong><?= htmlspecialchars($d['ref_num']) ?></strong> as a note/message when sending.
        </p>
    </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="index.php" class="btn btn-outline-secondary me-2" style="border-radius:10px;">
            ← Register Another
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary" style="border-radius:10px;">
            🖨 Print This Page
        </button>
    </div>

</div>

<div style="background:var(--dark);color:rgba(255,255,255,.6);text-align:center;padding:18px;font-size:.82rem;line-height:2;">
    <?= htmlspecialchars(EVENT_NAME) ?> &copy; <?= date('Y') ?><br>
    Onsite: <?= htmlspecialchars(EVENT_CONTACT_NAME) ?> — <?= htmlspecialchars(EVENT_CONTACT) ?> &nbsp;|&nbsp;
    Online Coordinator: <?= htmlspecialchars(EVENT_ONLINE_COORD) ?> — <?= htmlspecialchars(GCASH_NUMBER) ?> / <?= htmlspecialchars(GCASH_NUMBER_2) ?>
</div>

</body>
</html>
