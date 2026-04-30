-- ============================================================
--  Migration 003 — Add No-Singlet category options
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- 1. Update category CHECK to include no-singlet variants
ALTER TABLE public.registrations
    DROP CONSTRAINT IF EXISTS registrations_category_check;

ALTER TABLE public.registrations
    ADD CONSTRAINT registrations_category_check
    CHECK (category IN ('3K','5K','10K','3K-NS','5K-NS','10K-NS'));

-- 2. Allow 'N/A' shirt size for no-singlet entries
ALTER TABLE public.registrations
    DROP CONSTRAINT IF EXISTS registrations_shirt_size_check;

ALTER TABLE public.registrations
    ADD CONSTRAINT registrations_shirt_size_check
    CHECK (shirt_size IN ('XS','S','M','L','XL','XXL','N/A'));
