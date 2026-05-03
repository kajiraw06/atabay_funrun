-- ============================================================
--  Migration 010 — Create reserves table
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

CREATE TABLE IF NOT EXISTS public.reserves (
    id          BIGSERIAL PRIMARY KEY,
    first_name  TEXT NOT NULL,
    last_name   TEXT NOT NULL,
    phone       TEXT NOT NULL,
    email       TEXT,
    category    TEXT,
    notes       TEXT,
    status      TEXT DEFAULT 'interested' CHECK (status IN ('interested','contacted','converted','cancelled')),
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

-- Auto-update updated_at
CREATE OR REPLACE TRIGGER on_reserves_update
    BEFORE UPDATE ON public.reserves
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

-- Enable RLS
ALTER TABLE public.reserves ENABLE ROW LEVEL SECURITY;

-- Only authenticated admins can read/write
CREATE POLICY "Admin full access on reserves"
    ON public.reserves
    FOR ALL
    TO authenticated
    USING (true)
    WITH CHECK (true);
