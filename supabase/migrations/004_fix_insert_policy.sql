-- ============================================================
--  Migration 004 — Fix anon INSERT policy
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- Drop and recreate the INSERT policy to ensure it's active
DROP POLICY IF EXISTS "Public can register" ON public.registrations;

CREATE POLICY "Public can register"
    ON public.registrations FOR INSERT
    TO anon WITH CHECK (true);

-- Also update payment_method constraint to remove 'cash' since it's no longer offered
ALTER TABLE public.registrations
    DROP CONSTRAINT IF EXISTS registrations_payment_method_check;

ALTER TABLE public.registrations
    ADD CONSTRAINT registrations_payment_method_check
    CHECK (payment_method IN ('gcash','paymaya'));
