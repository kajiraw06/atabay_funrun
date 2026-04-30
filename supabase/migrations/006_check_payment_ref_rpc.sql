-- ============================================================
--  Migration 006 — Add RPC to check duplicate payment ref#
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

CREATE OR REPLACE FUNCTION public.check_payment_ref_exists(p_ref TEXT)
RETURNS BOOLEAN
LANGUAGE sql
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1 FROM public.registrations
        WHERE payment_ref = p_ref
        LIMIT 1
    );
$$;

GRANT EXECUTE ON FUNCTION public.check_payment_ref_exists(TEXT) TO anon;
