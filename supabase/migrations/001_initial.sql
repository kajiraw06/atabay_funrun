-- ============================================================
--  Atabay Fiesta Fun Run 2026 — Supabase Migration
--  Run this in: Supabase Dashboard → SQL Editor → New Query
-- ============================================================

-- 1. Create the registrations table
CREATE TABLE IF NOT EXISTS public.registrations (
    id                       BIGSERIAL PRIMARY KEY,
    reference_number         TEXT UNIQUE NOT NULL,
    first_name               TEXT NOT NULL,
    last_name                TEXT NOT NULL,
    email                    TEXT NOT NULL,
    phone                    TEXT NOT NULL,
    address                  TEXT NOT NULL,
    birthdate                DATE NOT NULL,
    gender                   TEXT NOT NULL CHECK (gender IN ('Male','Female','Other')),
    emergency_contact_name   TEXT NOT NULL,
    emergency_contact_number TEXT NOT NULL,
    category                 TEXT NOT NULL CHECK (category IN ('3K','5K','10K')),
    shirt_size               TEXT NOT NULL CHECK (shirt_size IN ('XS','S','M','L','XL','XXL')),
    payment_method           TEXT NOT NULL CHECK (payment_method IN ('gcash','paymaya','cash')),
    payment_ref              TEXT,
    payment_status           TEXT DEFAULT 'pending' CHECK (payment_status IN ('pending','verified','rejected')),
    status                   TEXT DEFAULT 'pending' CHECK (status IN ('pending','confirmed','cancelled')),
    notes                    TEXT,
    created_at               TIMESTAMPTZ DEFAULT NOW(),
    updated_at               TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Auto-update updated_at on row change
CREATE OR REPLACE FUNCTION public.handle_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS on_registrations_update ON public.registrations;
CREATE TRIGGER on_registrations_update
    BEFORE UPDATE ON public.registrations
    FOR EACH ROW EXECUTE FUNCTION public.handle_updated_at();

-- 3. Enable Row Level Security
ALTER TABLE public.registrations ENABLE ROW LEVEL SECURITY;

-- 4. RLS Policies
-- Public (anon) can INSERT — needed for registration form
CREATE POLICY "Public can register"
    ON public.registrations FOR INSERT
    TO anon WITH CHECK (true);

-- Public (anon) can SELECT — needed for success page lookup by ref#
CREATE POLICY "Public can view registrations"
    ON public.registrations FOR SELECT
    TO anon USING (true);

-- Authenticated users (admin) have full access
CREATE POLICY "Admin full access"
    ON public.registrations FOR ALL
    TO authenticated USING (true) WITH CHECK (true);
