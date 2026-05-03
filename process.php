<?php
require_once 'config.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── CSRF token basic protection (referrer check) ────────────────
$ref = $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($ref) && parse_url($ref, PHP_URL_HOST) !== ($_SERVER['HTTP_HOST'] ?? '')) {
    die('Invalid request origin.');
}

// ── COLLECT & SANITIZE INPUTS ───────────────────────────────────
function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

$first_name               = clean($_POST['first_name']               ?? '');
$last_name                = clean($_POST['last_name']                ?? '');
$email                    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone                    = clean($_POST['phone']                    ?? '');
$address                  = clean($_POST['address']                  ?? '');
$birthdate                = clean($_POST['birthdate']                ?? '');
$gender                   = clean($_POST['gender']                   ?? '');
$emergency_contact_name   = clean($_POST['emergency_contact_name']   ?? '');
$emergency_contact_number = clean($_POST['emergency_contact_number'] ?? '');
$category                 = clean($_POST['category']                 ?? '');
$shirt_size               = clean($_POST['shirt_size']               ?? '');
$payment_method           = clean($_POST['payment_method']           ?? '');
$payment_ref              = clean($_POST['payment_ref']              ?? '');
$agree_terms              = isset($_POST['agree_terms']) ? 1 : 0;

// ── VALIDATION ──────────────────────────────────────────────────
$errors = [];

if (strlen($first_name) < 2)  $errors[] = 'First name is required (min 2 characters).';
if (strlen($last_name)  < 2)  $errors[] = 'Last name is required (min 2 characters).';

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'A valid email address is required.';

// Phone: digits, spaces, dashes, + prefix allowed, 10-15 chars
if (!preg_match('/^[0-9+\-\s]{10,15}$/', $phone))
    $errors[] = 'A valid Philippine mobile number is required (e.g. 09271234567).';

if (strlen($address) < 10)
    $errors[] = 'Please provide your complete address.';

// Birthdate
if (empty($birthdate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
    $errors[] = 'A valid birthdate is required.';
} else {
    $bd = new DateTime($birthdate);
    $today = new DateTime();
    if ($bd >= $today) $errors[] = 'Birthdate must be in the past.';
}

$allowed_genders = ['Male', 'Female', 'Other'];
if (!in_array($gender, $allowed_genders))
    $errors[] = 'Please select a valid gender.';

if (strlen($emergency_contact_name) < 2)
    $errors[] = 'Emergency contact name is required.';

if (!preg_match('/^[0-9+\-\s]{10,15}$/', $emergency_contact_number))
    $errors[] = 'A valid emergency contact number is required.';

$valid_categories = array_keys(CATEGORIES);
if (!in_array($category, $valid_categories))
    $errors[] = 'Please select a valid race category.';

$valid_sizes = ['XS','S','M','L','XL','XXL'];
if (!in_array($shirt_size, $valid_sizes))
    $errors[] = 'Please select a valid singlet size.';

$valid_payments = ['gcash','paymaya','cash'];
if (!in_array($payment_method, $valid_payments))
    $errors[] = 'Please select a valid payment method.';

if (!$agree_terms)
    $errors[] = 'You must agree to the terms and conditions.';
// Payment reference number validation (required for GCash/PayMaya)
if (in_array($payment_method, ['gcash', 'paymaya'])) {
    if (empty($payment_ref)) {
        $errors[] = 'Payment reference number is required for GCash/PayMaya.';
    } elseif (!preg_match('/^[A-Za-z0-9\-\s]{6,40}$/', $payment_ref)) {
        $errors[] = 'Payment reference number must be 6–40 characters (letters, numbers, hyphens only).';
    }
} ──────────────────────────────────────
$proof_filename = null;

if (in_array($payment_method, ['gcash', 'paymaya'])) {
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Proof of payment screenshot is required for GCash/PayMaya.';
    } else {
        $file    = $_FILES['payment_proof'];
        $allowed_mime  = ['image/jpeg', 'image/png', 'application/pdf'];
        $allowed_ext   = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));

        // Validate size
        if ($file['size'] > MAX_FILE_SIZE)
            $errors[] = 'Proof of payment file must be 5 MB or smaller.';

        // Validate extension
        if (!in_array($ext, $allowed_ext))
            $errors[] = 'Proof of payment must be a JPG, PNG, or PDF file.';

        // Validate MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowed_mime))
            $errors[] = 'The uploaded file type is not allowed.';

        if (empty($errors)) {
            // Generate safe filename
            $proof_filename = uniqid('proof_', true) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $proof_filename)) {
                $errors[] = 'Failed to save uploaded file. Please try again.';
                $proof_filename = null;
            }
        }
    }
}

// ── RETURN ERRORS IF ANY ────────────────────────────────────────
if (!empty($errors)) {
    // Remove any uploaded file if validation failed elsewhere
    if ($proof_filename && file_exists(UPLOAD_DIR . $proof_filename)) {
        unlink(UPLOAD_DIR . $proof_filename);
    }
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = [
        'first_name'               => $first_name,
        'last_name'                => $last_name,
        'email'                    => $email,
        'phone'                    => $phone,
        'address'                  => $address,
        'birthdate'                => $birthdate,
        'gender'                   => $gender,
        'emergency_contact_name'   => $emergency_contact_name,
        'emergency_contact_number' => $emergency_contact_number,
        'category'                 => $category,
        'shirt_size'               => $shirt_size,
        'payment_method'           => $payment_method,
        'payment_ref'              => $payment_ref,
    ];
    header('Location: index.php');
    exit;
}

// ── GENERATE REFERENCE NUMBER ───────────────────────────────────
// Format: FR2026-XXXXX  (XXXXX = random 5-digit unique segment)
do {
    $ref_num = 'FR' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    $chk = $conn->prepare('SELECT id FROM registrations WHERE reference_number = ?');
    $chk->bind_param('s', $ref_num);
    $chk->execute();
    $chk->store_result();
    $exists = $chk->num_rows > 0;
    $chk->close();
} while ($exists);

// ── INSERT INTO DATABASE ────────────────────────────────────────
$stmt = $conn->prepare(
    'INSERT INTO registrations
        (reference_number, first_name, last_name, email, phone, address,
         birthdate, gender, emergency_contact_name, emergency_contact_number,
         category, shirt_size, payment_method, payment_ref, payment_proof, payment_status)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
);

$pay_status = ($payment_method === 'cash') ? 'verified' : 'pending';

$stmt->bind_param(
    'ssssssssssssssss',
    $ref_num,
    $first_name, $last_name, $email, $phone, $address,
    $birthdate, $gender,
    $emergency_contact_name, $emergency_contact_number,
    $category, $shirt_size,
    $payment_method, $payment_ref, $proof_filename, $pay_status
);

if (!$stmt->execute()) {
    // Rollback uploaded file
    if ($proof_filename && file_exists(UPLOAD_DIR . $proof_filename)) {
        unlink(UPLOAD_DIR . $proof_filename);
    }
    $_SESSION['form_errors'] = ['Registration failed due to a server error. Please try again.'];
    header('Location: index.php');
    exit;
}
$stmt->close();
$conn->close();

// ── PASS SUCCESS DATA TO SUCCESS PAGE ──────────────────────────
$_SESSION['reg_success'] = [
    'ref_num'        => $ref_num,
    'name'           => $first_name . ' ' . $last_name,
    'email'          => $email,
    'category'       => CATEGORIES[$category]['label'],
    'fee'            => CATEGORIES[$category]['fee'],
    'shirt_size'     => $shirt_size,
    'payment_method' => $payment_method,
];

header('Location: success.php');
exit;
