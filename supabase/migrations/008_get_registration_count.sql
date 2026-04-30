-- ============================================================
--  Migration 008 — Public registration count RPC
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

CREATE OR REPLACE FUNCTION get_registration_count()
RETURNS INTEGER
LANGUAGE sql
SECURITY DEFINER
AS $$
  SELECT COUNT(*)::INTEGER FROM public.registrations;
$$;

GRANT EXECUTE ON FUNCTION get_registration_count() TO anon;
