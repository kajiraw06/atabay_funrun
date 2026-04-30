<?php
// ============================================================
//  CONFIGURATION FILE — Edit these values before going live
// ============================================================

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Change to your DB username
define('DB_PASS', '');          // Change to your DB password
define('DB_NAME', 'fun_run_db');

// --- Event Info ---
define('EVENT_NAME',     'Atabay Fiesta Fun Run 2026');
define('EVENT_TAGLINE',  '"Takbo ni Pedro Para sa Kapayapaan"');
define('EVENT_SLOGAN',   'One Run · One Dream · One Peace for Everyone');
define('EVENT_DATE',     'May 2026');  // Exact race day TBA
define('EVENT_REG_END',  'May 24, 2026');
define('EVENT_LOCATION', 'Atabay Barangay Hall, Brgy. Atabay, Hilongos, Leyte');
define('EVENT_ORGANIZER','Barangay Atabay, Hilongos, Leyte');
define('EVENT_CONTACT_NAME', 'Welcho M. Labides');
define('EVENT_CONTACT',  '09208872740');
define('EVENT_ONLINE_COORD', 'Raul Urgel');

// --- Race Categories: 'key' => ['label', fee, inclusions] ---
define('CATEGORIES', [
    '3K'  => ['label' => '3K Fun Run',  'fee' => 500, 'includes' => 'Singlet · Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle'],
    '5K'  => ['label' => '5K Run',      'fee' => 600, 'includes' => 'Singlet · Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle'],
    '10K' => ['label' => '10K Run',     'fee' => 700, 'includes' => 'Singlet · Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle'],
]);

// --- Cash Prizes ---
define('CASH_PRIZES', [
    '3K'  => ['1st' => 1500, '2nd' => 1000, '3rd' => 500],
    '5K'  => ['1st' => 2000, '2nd' => 1500, '3rd' => 1000],
    '10K' => ['1st' => 3000, '2nd' => 2000, '3rd' => 1000],
]);

// --- Payment Details ---
// Online Registration GCash (Raul Urgel)
define('GCASH_NUMBER',    '+63946-713-7308');
define('GCASH_NAME',      'Raul Urgel');

// PayMaya — same coordinator
define('PAYMAYA_NUMBER',  '+63946-713-7308');
define('PAYMAYA_NAME',    'Raul Urgel');

// Onsite payment — Welcho M. Labides
define('ONSITE_GCASH',    '09208-872-740');
define('ONSITE_NAME',     'Welcho M. Labides');

// --- Admin Panel ---
define('ADMIN_PASSWORD',  'Admin@2026');        // !! CHANGE THIS before launch !!

// --- File Uploads ---
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);      // 5 MB

// ============================================================
//  DATABASE CONNECTION  (do not edit below)
// ============================================================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die('<p style="font-family:sans-serif;color:red;">Database connection failed. Please contact the administrator.</p>');
}
$conn->set_charset('utf8mb4');

// Auto-create table if it does not exist
$conn->query("CREATE TABLE IF NOT EXISTS registrations (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    reference_number        VARCHAR(20)  UNIQUE NOT NULL,
    first_name              VARCHAR(100) NOT NULL,
    last_name               VARCHAR(100) NOT NULL,
    email                   VARCHAR(150) NOT NULL,
    phone                   VARCHAR(20)  NOT NULL,
    address                 TEXT         NOT NULL,
    birthdate               DATE         NOT NULL,
    gender                  ENUM('Male','Female','Other') NOT NULL,
    emergency_contact_name  VARCHAR(150) NOT NULL,
    emergency_contact_number VARCHAR(20) NOT NULL,
    category                VARCHAR(10)  NOT NULL,
    shirt_size              ENUM('XS','S','M','L','XL','XXL') NOT NULL,
    payment_method          ENUM('gcash','paymaya','cash') NOT NULL,
    payment_proof           VARCHAR(255) DEFAULT NULL,
    payment_status          ENUM('pending','verified','rejected') DEFAULT 'pending',
    registration_status     ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    notes                   TEXT         DEFAULT NULL,
    created_at              TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure uploads directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

session_start();
