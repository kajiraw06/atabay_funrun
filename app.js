// ============================================================
//  Atabay Fiesta Fun Run 2026 — Supabase Client & Utilities
//  Requires supabase-config.js to be loaded first
// ============================================================

// Supabase client — initialized from supabase-config.js constants
const _supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

const CONFIG = {
    eventName:     'Atabay Fiesta Fun Run 2026',
    eventTagline:  '"Takbo ni Pedro Para sa Kapayapaan"',
    eventSlogan:   'One Run · One Dream · One Peace for Everyone',
    eventRegEnd:   'May 24, 2026',
    eventLocation: 'Atabay Barangay Hall, Brgy. Atabay, Hilongos, Leyte',
    eventOrganizer:'Barangay Atabay, Hilongos, Leyte',

    // Onsite contact
    contactName:   'Welcho M. Labides',
    contactPhone:  '09208872740',

    // Online registration coordinator
    onlineCoord:   'Raul Urgel',
    gcashNumber:   '+63946-713-7308',
    gcashName:     'Raul Urgel',
    paymayaNumber: '+63946-713-7308',
    paymayaName:   'Raul Urgel',

    // Admin password (used only as a fallback label — real auth is Supabase Auth)
    adminPassword: 'Admin@2026',

    categories: {
        '3K':    { label: '3K Fun Run',          fee: 500,
                   includes: 'Singlet · Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle',
                   noSinglet: false },
        '5K':    { label: '5K Run',              fee: 600,
                   includes: 'Singlet · Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle',
                   noSinglet: false },
        '10K':   { label: '10K Run',             fee: 700,
                   includes: 'Singlet · Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle',
                   noSinglet: false },
        '3K-NS': { label: '3K Fun Run (No Singlet)', fee: 300,
                   includes: 'Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle',
                   noSinglet: true },
        '5K-NS': { label: '5K Run (No Singlet)',     fee: 400,
                   includes: 'Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle',
                   noSinglet: true },
        '10K-NS':{ label: '10K Run (No Singlet)',    fee: 500,
                   includes: 'Finisher Medal · Hydration Station · Racebib · Post Race Snack · Raffle',
                   noSinglet: true },
    },

    cashPrizes: {
        '3K':  { '1st': 1500, '2nd': 1000, '3rd': 500  },
        '5K':  { '1st': 2000, '2nd': 1500, '3rd': 1000 },
        '10K': { '1st': 3000, '2nd': 2000, '3rd': 1000 },
    },
};

// ── Utilities ────────────────────────────────────────────────────

/** Generate unique reference number: FR2026-XXXXX */
function generateRef() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    // Generate one ref — DB UNIQUE constraint handles rare collisions
    let suffix = '';
    for (let i = 0; i < 5; i++) suffix += chars[Math.floor(Math.random() * chars.length)];
    return 'FR2026-' + suffix;
}

/** Format amount as peso string */
function peso(n) {
    return '₱' + Number(n).toLocaleString('en-PH');
}

/** Format date string nicely */
function fmtDate(isoStr) {
    if (!isoStr) return '—';
    return new Date(isoStr).toLocaleString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

// ── Supabase CRUD (all async) ────────────────────────────────────

/** Insert a new registration. Returns { data, error } */
async function saveRegistration(regData) {
    const { error } = await _supabase
        .from('registrations')
        .insert([regData]);
    return { data: regData, error };
}

/** Check if a payment ref# is already used. Returns { exists, error } */
async function checkPaymentRefExists(paymentRef) {
    const { data, error } = await _supabase
        .rpc('check_payment_ref_exists', { p_ref: paymentRef });
    return { exists: !!data, error };
}

/** Fetch a single registration by reference number via secure RPC. Returns { data, error } */
async function getRegistrationByRef(ref) {
    const { data, error } = await _supabase
        .rpc('get_registration_by_ref', { p_ref: ref });
    // RPC returns array (SETOF) — unwrap to single object to keep same interface
    return { data: (data && data.length > 0) ? data[0] : null, error };
}

/**
 * Fetch all registrations with optional filters.
 * filters: { paymentStatus, category, paymentMethod, search }
 * Returns { data, error }
 */
async function getRegistrations(filters = {}) {
    let query = _supabase.from('registrations').select('*');

    if (filters.paymentStatus) query = query.eq('payment_status', filters.paymentStatus);
    if (filters.category)      query = query.eq('category', filters.category);
    if (filters.paymentMethod) query = query.eq('payment_method', filters.paymentMethod);
    if (filters.search) {
        const s = filters.search;
        query = query.or(
            `reference_number.ilike.%${s}%,first_name.ilike.%${s}%,last_name.ilike.%${s}%,email.ilike.%${s}%,phone.ilike.%${s}%`
        );
    }

    const { data, error } = await query.order('created_at', { ascending: false });
    return { data: data || [], error };
}

/** Update payment/registration status for a registration. Returns { data, error } */
async function updateReg(ref, updates) {
    const { data, error } = await _supabase
        .from('registrations')
        .update(updates)
        .eq('reference_number', ref)
        .select()
        .single();
    return { data, error };
}

/** Delete a registration by reference number. Returns { error } */
async function deleteReg(ref) {
    const { error } = await _supabase
        .from('registrations')
        .delete()
        .eq('reference_number', ref);
    return { error };
}

// ── Admin Auth ───────────────────────────────────────────────────

async function adminSignIn(email, password) {
    return await _supabase.auth.signInWithPassword({ email, password });
}

async function adminSignOut() {
    return await _supabase.auth.signOut();
}

// ── CSV Export (pass already-fetched rows array) ─────────────────

function exportCSV(regs) {
    if (!regs || !regs.length) { alert('No registrations to export.'); return; }

    const headers = [
        'Ref #','First Name','Last Name','Email','Phone','Address',
        'Birthdate','Gender','Emergency Contact','Emergency Phone',
        'Category','Fee','Shirt Size','Payment Method','GCash/Maya Ref',
        'Payment Status','Registration Status','Notes','Registered On'
    ];
    const rows = regs.map(r => [
        r.reference_number, r.first_name, r.last_name,
        r.email, r.phone, r.address, r.birthdate, r.gender,
        r.emergency_contact_name, r.emergency_contact_number,
        r.category, '₱' + (CONFIG.categories[r.category]?.fee || 0),
        r.shirt_size, r.payment_method, r.payment_ref || '',
        r.payment_status, r.status, r.notes || '',
        fmtDate(r.created_at)
    ]);

    const csv = [headers, ...rows]
        .map(row => row.map(v => '"' + String(v || '').replace(/"/g, '""') + '"').join(','))
        .join('\r\n');

    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'registrations_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

