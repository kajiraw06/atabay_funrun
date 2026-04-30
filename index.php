<?php
require_once 'config.php';

// Build category options for JS fee lookup
$cat_json = json_encode(array_map(fn($v) => $v['fee'], CATEGORIES));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — Atabay Fiesta Fun Run 2026</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ── HERO BANNER ─────────────────────────────── -->
<div class="event-hero">
    <div class="badge-pill">🏁 OFFICIAL ONLINE REGISTRATION</div>
    <h1><?= htmlspecialchars(EVENT_NAME) ?></h1>
    <div style="font-size:1rem;font-style:italic;opacity:.9;margin-bottom:6px;">
        <?= htmlspecialchars(EVENT_TAGLINE) ?>
    </div>
    <div class="meta">
        <span>📅 Registration ends: <?= htmlspecialchars(EVENT_REG_END) ?></span><br>
        <span>📍 <?= htmlspecialchars(EVENT_LOCATION) ?></span>
    </div>
    <div class="mt-3" style="font-size:.82rem;opacity:.8;">
        Organized by <?= htmlspecialchars(EVENT_ORGANIZER) ?> &nbsp;|&nbsp;
        Contact: <?= htmlspecialchars(EVENT_CONTACT_NAME) ?> — <?= htmlspecialchars(EVENT_CONTACT) ?>
    </div>
    <div class="mt-2" style="font-size:.9rem;font-weight:700;color:var(--accent);letter-spacing:1px;">
        <?= htmlspecialchars(EVENT_SLOGAN) ?>
    </div>
</div>

<!-- ── MAIN FORM ────────────────────────────────── -->
<div class="container" style="max-width:780px;padding:32px 16px 64px;">

    <!-- Validation errors (populated by PHP redirect with session errors) -->
    <?php if (!empty($_SESSION['form_errors'])): ?>
    <div class="alert-error">
        <strong>⚠️ Please fix the following:</strong>
        <ul>
            <?php foreach ($_SESSION['form_errors'] as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
    <?php endif; ?>

    <?php $old = $_SESSION['form_old'] ?? []; unset($_SESSION['form_old']); ?>

    <!-- Cash Prizes Info -->
    <div class="form-card mb-4" style="background:linear-gradient(135deg,#0A2A0A,#0D6B2A);color:#fff;border:2px solid var(--accent);">
        <div class="section-title" style="color:var(--accent);border-color:var(--accent);">🏆 Cash Prizes</div>
        <div class="table-responsive">
        <table class="table table-sm mb-0 text-center" style="color:#fff;font-size:.88rem;">
            <thead>
                <tr style="border-color:rgba(255,255,255,.2);">
                    <th style="background:rgba(0,0,0,.3);color:var(--accent);">Place</th>
                    <th style="background:rgba(0,0,0,.3);color:var(--accent);">3KM</th>
                    <th style="background:rgba(0,0,0,.3);color:var(--accent);">5KM</th>
                    <th style="background:rgba(0,0,0,.3);color:var(--accent);">10KM</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $places = ['1st' => '🥇 1st Place', '2nd' => '🥈 2nd Place', '3rd' => '🥉 3rd Place'];
                foreach ($places as $k => $label):
                ?>
                <tr style="border-color:rgba(255,255,255,.1);">
                    <td class="fw-bold"><?= $label ?></td>
                    <?php foreach (CASH_PRIZES as $dist => $prizes): ?>
                    <td>₱<?= number_format($prizes[$k]) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <p style="font-size:.78rem;opacity:.75;margin-top:10px;margin-bottom:0;">
            * Cash prizes are awarded per gender category. All participants receive: Singlet, Finisher Medal, Hydration Station, Racebib, Post Race Snack &amp; Raffle entry.
        </p>
    </div>

    <form action="process.php" method="POST" enctype="multipart/form-data" id="regForm" novalidate>

        <!-- ① PERSONAL INFORMATION -->
        <div class="form-card">
            <div class="section-title">① Personal Information</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control"
                           value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                           placeholder="Juan" required maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control"
                           value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                           placeholder="Dela Cruz" required maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           placeholder="juan@email.com" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                           placeholder="09XX-XXX-XXXX" required maxlength="20">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                    <input type="date" name="birthdate" class="form-control"
                           value="<?= htmlspecialchars($old['birthdate'] ?? '') ?>"
                           max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select…</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                        <option value="<?= $g ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <!-- Age is auto-shown for info only -->
                    <label class="form-label">Age (auto)</label>
                    <input type="text" id="age_display" class="form-control" readonly placeholder="—" style="background:#f8f8f8;">
                </div>
                <div class="col-12">
                    <label class="form-label">Complete Address <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control" rows="2"
                              placeholder="House/Unit, Street, Barangay, Municipality/City, Province"
                              required maxlength="500"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ② EMERGENCY CONTACT -->
        <div class="form-card">
            <div class="section-title">② Emergency Contact</div>
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                    <input type="text" name="emergency_contact_name" class="form-control"
                           value="<?= htmlspecialchars($old['emergency_contact_name'] ?? '') ?>"
                           placeholder="Full name" required maxlength="150">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="tel" name="emergency_contact_number" class="form-control"
                           value="<?= htmlspecialchars($old['emergency_contact_number'] ?? '') ?>"
                           placeholder="09XX-XXX-XXXX" required maxlength="20">
                </div>
            </div>
        </div>

        <!-- ③ RACE DETAILS -->
        <div class="form-card">
            <div class="section-title">③ Race Category & T-Shirt Size</div>

            <label class="form-label mb-2">Choose your category <span class="text-danger">*</span></label>
            <div class="category-grid mb-3">
                <?php foreach (CATEGORIES as $key => $cat): ?>
                <div class="category-card">
                    <input type="radio" name="category" id="cat_<?= $key ?>"
                           value="<?= $key ?>" <?= ($old['category'] ?? '') === $key ? 'checked' : '' ?> required>
                    <label for="cat_<?= $key ?>">
                        <span class="distance"><?= $key ?></span>
                        <span class="cat-name"><?= htmlspecialchars($cat['label']) ?></span>
                        <span class="fee">₱<?= number_format($cat['fee']) ?></span>
                        <span style="font-size:.68rem;color:var(--muted);margin-top:4px;line-height:1.4;">
                            <?= htmlspecialchars($cat['includes']) ?>
                        </span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="fee_notice" class="text-muted mb-3" style="font-size:.88rem;display:none;">
                Registration fee: <strong id="fee_amount" class="text-danger"></strong>
            </div>

            <label class="form-label mb-2">T-Shirt Size <span class="text-danger">*</span></label>
            <div class="size-grid">
                <?php foreach (['XS','S','M','L','XL','XXL'] as $sz): ?>
                <div class="size-option">
                    <input type="radio" name="shirt_size" id="sz_<?= $sz ?>"
                           value="<?= $sz ?>" <?= ($old['shirt_size'] ?? '') === $sz ? 'checked' : '' ?> required>
                    <label for="sz_<?= $sz ?>"><?= $sz ?></label>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-2" style="font-size:.78rem;color:var(--muted);">
                Sizes are for reference. Actual shirt will be given during race kit claiming.
            </div>
        </div>

        <!-- ④ PAYMENT -->
        <div class="form-card">
            <div class="section-title">④ Payment Method</div>

            <div class="payment-tabs mb-3">
                <div class="pay-option">
                    <input type="radio" name="payment_method" id="pm_gcash" value="gcash"
                           <?= ($old['payment_method'] ?? '') === 'gcash' ? 'checked' : '' ?> required>
                    <label for="pm_gcash">
                        <span class="pay-icon">💚</span> GCash
                    </label>
                </div>
                <div class="pay-option">
                    <input type="radio" name="payment_method" id="pm_paymaya" value="paymaya"
                           <?= ($old['payment_method'] ?? '') === 'paymaya' ? 'checked' : '' ?>>
                    <label for="pm_paymaya">
                        <span class="pay-icon">💙</span> PayMaya / Maya
                    </label>
                </div>
                <div class="pay-option">
                    <input type="radio" name="payment_method" id="pm_cash" value="cash"
                           <?= ($old['payment_method'] ?? '') === 'cash' ? 'checked' : '' ?>>
                    <label for="pm_cash">
                        <span class="pay-icon">💵</span> Cash (Walk-in)
                    </label>
                </div>
            </div>

            <!-- GCash instructions -->
            <div id="box_gcash" class="payment-box <?= ($old['payment_method'] ?? '') === 'gcash' ? 'active' : '' ?>">
                <h6>📲 GCash Payment Instructions</h6>
                <ol style="font-size:.9rem;margin:0;padding-left:18px;">
                    <li>Open your <strong>GCash</strong> app and tap <em>Send Money</em>.</li>
                    <li>Enter the number: <span class="acct"><?= htmlspecialchars(GCASH_NUMBER) ?></span></li>
                    <li>Account name: <strong><?= htmlspecialchars(GCASH_NAME) ?></strong></li>
                    <li>Enter the exact registration fee as the amount.</li>
                    <li>Save or screenshot your transaction receipt.</li>
                    <li>Upload the screenshot below.</li>
                </ol>
            </div>

            <!-- PayMaya instructions -->
            <div id="box_paymaya" class="payment-box <?= ($old['payment_method'] ?? '') === 'paymaya' ? 'active' : '' ?>">
                <h6>📲 PayMaya / Maya Payment Instructions</h6>
                <ol style="font-size:.9rem;margin:0;padding-left:18px;">
                    <li>Open your <strong>Maya</strong> app and tap <em>Send Money</em>.</li>
                    <li>Enter the number: <span class="acct"><?= htmlspecialchars(PAYMAYA_NUMBER) ?></span></li>
                    <li>Account name: <strong><?= htmlspecialchars(PAYMAYA_NAME) ?></strong></li>
                    <li>Enter the exact registration fee as the amount.</li>
                    <li>Save or screenshot your transaction receipt.</li>
                    <li>Upload the screenshot below.</li>
                </ol>
            </div>

            <!-- Cash instructions -->
            <div id="box_cash" class="payment-box cash-box <?= ($old['payment_method'] ?? '') === 'cash' ? 'active' : '' ?>">
                <h6>💵 Cash Walk-in Instructions</h6>
                <p style="font-size:.9rem;margin:0;">
                    Pay your registration fee in cash on the day of kit claiming or during the event.
                    Please bring a copy of this confirmation (reference number) when you pay.
                    No proof of payment upload needed.
                </p>
            </div>

            <!-- Proof of payment upload -->
            <div id="proof_section" class="mt-3" style="display:<?= in_array($old['payment_method'] ?? '', ['gcash','paymaya']) ? 'block' : 'none' ?>;">
                <label class="form-label">Upload Proof of Payment <span class="text-danger">*</span></label>
                <div class="upload-area" onclick="document.getElementById('payment_proof').click()">
                    <div class="upload-icon">📎</div>
                    <strong style="font-size:.9rem;">Click to upload</strong>
                    <p>PNG, JPG, or PDF — max 5 MB</p>
                    <img id="file_preview" src="#" alt="Preview">
                    <div id="file_name" style="font-size:.82rem;color:var(--muted);margin-top:6px;"></div>
                </div>
                <input type="file" id="payment_proof" name="payment_proof"
                       accept="image/png,image/jpeg,image/jpg,application/pdf"
                       style="display:none;" onchange="previewFile(this)">
            </div>
        </div>

        <!-- ⑤ TERMS & SUBMIT -->
        <div class="form-card">
            <div class="section-title">⑤ Terms & Waiver</div>
            <div style="background:#F7F9FC;border-radius:8px;padding:14px;font-size:.83rem;color:var(--muted);max-height:140px;overflow-y:auto;margin-bottom:14px;line-height:1.7;">
                I, the undersigned, acknowledge that participating in the <strong><?= htmlspecialchars(EVENT_NAME) ?></strong>
                involves physical activity and associated risks. I voluntarily assume all risks of injury or illness
                that may result from my participation. I agree to abide by all rules and regulations set by the
                organizers. I consent to the use of my personal information for registration and communication purposes
                related to this event. Registration fees are non-refundable except in cases of event cancellation
                by the organizers.
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="agree_terms" id="agreeTerms" required value="1">
                <label class="form-check-label" for="agreeTerms" style="font-size:.9rem;">
                    I have read and agree to the terms, waiver, and privacy policy. <span class="text-danger">*</span>
                </label>
            </div>

            <button type="submit" class="btn-register" id="submitBtn">
                🏃 SUBMIT REGISTRATION
            </button>
            <p class="text-center mt-3" style="font-size:.8rem;color:var(--muted);">
                You will receive a reference number immediately after submitting.
            </p>
        </div>

    </form>
</div>

<!-- Footer -->
<div style="background:var(--dark);color:rgba(255,255,255,.6);text-align:center;padding:18px;font-size:.82rem;line-height:2;">
    <?= htmlspecialchars(EVENT_NAME) ?> &copy; <?= date('Y') ?><br>
    Onsite inquiries: <?= htmlspecialchars(EVENT_CONTACT_NAME) ?> — <?= htmlspecialchars(EVENT_CONTACT) ?> &nbsp;|&nbsp;
    Online coordinator: <?= htmlspecialchars(EVENT_ONLINE_COORD) ?> — <?= htmlspecialchars(GCASH_NUMBER) ?>
</div>

<script>
// ── Fee lookup ──────────────────────────────────────
const fees = <?= $cat_json ?>;
document.querySelectorAll('input[name="category"]').forEach(r => {
    r.addEventListener('change', () => {
        const key = r.value;
        document.getElementById('fee_amount').textContent = '₱' + fees[key].toLocaleString();
        document.getElementById('fee_notice').style.display = 'block';
    });
});

// ── Age calculator ──────────────────────────────────
document.querySelector('input[name="birthdate"]').addEventListener('change', function() {
    if (!this.value) return;
    const diff = Date.now() - new Date(this.value).getTime();
    const age = Math.floor(diff / (365.25 * 24 * 60 * 60 * 1000));
    document.getElementById('age_display').value = age + ' years old';
});

// ── Payment method toggle ───────────────────────────
document.querySelectorAll('input[name="payment_method"]').forEach(r => {
    r.addEventListener('change', () => {
        ['gcash','paymaya','cash'].forEach(m => {
            document.getElementById('box_' + m).classList.remove('active');
        });
        document.getElementById('box_' + r.value).classList.add('active');
        const needProof = (r.value === 'gcash' || r.value === 'paymaya');
        document.getElementById('proof_section').style.display = needProof ? 'block' : 'none';
        if (!needProof) {
            document.getElementById('payment_proof').value = '';
            document.getElementById('file_preview').style.display = 'none';
            document.getElementById('file_name').textContent = '';
        }
    });
});

// ── File preview ────────────────────────────────────
function previewFile(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('file_name').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('file_preview');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('file_preview').style.display = 'none';
    }
}

// ── Client-side validation ──────────────────────────
document.getElementById('regForm').addEventListener('submit', function(e) {
    const pm = document.querySelector('input[name="payment_method"]:checked');
    if (pm && (pm.value === 'gcash' || pm.value === 'paymaya')) {
        const proof = document.getElementById('payment_proof');
        if (!proof.files || proof.files.length === 0) {
            e.preventDefault();
            alert('Please upload your proof of payment screenshot.');
            return;
        }
    }
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').textContent = '⏳ Submitting…';
});
</script>
</body>
</html>
