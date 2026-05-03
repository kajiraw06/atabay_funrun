-- ============================================================
--  Migration 009 — payment_ref column already exists (no-op)
--  The payment_ref TEXT column was added in migration 001.
--  No action needed — this migration is intentionally empty.
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- payment_ref column exists from migration 001_initial.sql
-- It accepts any text value so both GCash (13-digit numeric)
-- and Maya (alphanumeric, e.g. TXN-ABC12345) refs are stored fine.
SELECT 1; -- no-op
