-- ============================================================
--  Migration 005 — Make email column optional
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- Allow email to be NULL since the field was removed from the form
ALTER TABLE public.registrations
    ALTER COLUMN email DROP NOT NULL;
