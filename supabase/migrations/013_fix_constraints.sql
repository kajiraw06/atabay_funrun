-- ============================================================
--  Migration 013 — Fix category & shirt_size check constraints
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- Fix category constraint (add no-singlet variants)
ALTER TABLE public.registrations
    DROP CONSTRAINT IF EXISTS registrations_category_check;

ALTER TABLE public.registrations
    ADD CONSTRAINT registrations_category_check
    CHECK (category IN ('3K','5K','10K','3K-NS','5K-NS','10K-NS'));

-- Fix shirt_size constraint (add N/A for no-singlet entries)
ALTER TABLE public.registrations
    DROP CONSTRAINT IF EXISTS registrations_shirt_size_check;

ALTER TABLE public.registrations
    ADD CONSTRAINT registrations_shirt_size_check
    CHECK (shirt_size IN ('XS','S','M','L','XL','XXL','N/A'));

-- Add with_tshirt column if it doesn't exist yet (migration 011)
ALTER TABLE public.registrations
    ADD COLUMN IF NOT EXISTS with_tshirt BOOLEAN NOT NULL DEFAULT FALSE;
