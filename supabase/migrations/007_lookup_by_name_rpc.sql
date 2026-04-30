-- ============================================================
--  Migration 007 — Add RPC to lookup registration by name
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

CREATE OR REPLACE FUNCTION public.get_registrations_by_name(p_first TEXT, p_last TEXT)
RETURNS TABLE (
    reference_number TEXT,
    first_name       TEXT,
    last_name        TEXT,
    category         TEXT,
    payment_method   TEXT,
    payment_status   TEXT,
    status           TEXT,
    created_at       TIMESTAMPTZ
)
LANGUAGE sql
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT reference_number, first_name, last_name, category,
           payment_method, payment_status, status, created_at
    FROM public.registrations
    WHERE LOWER(TRIM(first_name)) = LOWER(TRIM(p_first))
      AND LOWER(TRIM(last_name))  = LOWER(TRIM(p_last))
    ORDER BY created_at DESC
    LIMIT 5;
$$;

GRANT EXECUTE ON FUNCTION public.get_registrations_by_name(TEXT, TEXT) TO anon;
