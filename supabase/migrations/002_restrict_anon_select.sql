-- ============================================================
--  Migration 002 — Restrict anon SELECT (block data harvesting)
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- 1. Remove the open "anyone can read all rows" policy
DROP POLICY IF EXISTS "Public can view registrations" ON public.registrations;

-- 2. Block direct anon SELECT on the table entirely
CREATE POLICY "Public cannot directly query registrations"
    ON public.registrations FOR SELECT
    TO anon USING (false);

-- 3. Create a SECURITY DEFINER function — runs as the DB owner,
--    bypasses RLS, but ONLY returns the one row matching the ref#.
--    Anon users can call this but cannot dump all rows.
CREATE OR REPLACE FUNCTION public.get_registration_by_ref(p_ref TEXT)
RETURNS SETOF public.registrations
LANGUAGE sql
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT * FROM public.registrations
    WHERE reference_number = p_ref
    LIMIT 1;
$$;

-- 4. Allow anon role to call this function
GRANT EXECUTE ON FUNCTION public.get_registration_by_ref(TEXT) TO anon;
