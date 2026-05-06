-- ============================================================
--  Migration 014 — Make reserves.phone nullable
--  (needed so bulk-imported reserves without phone numbers work)
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

ALTER TABLE public.reserves
    ALTER COLUMN phone DROP NOT NULL;
