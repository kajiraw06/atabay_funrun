-- ============================================================
--  Migration 012 — Counter offset setting + reserves in count
-- ============================================================

-- 1. Settings table (key/value store for admin-adjustable values)
CREATE TABLE IF NOT EXISTS public.settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL DEFAULT ''
);

-- Default offset = 0
INSERT INTO public.settings (key, value)
VALUES ('counter_offset', '0')
ON CONFLICT (key) DO NOTHING;

-- RLS: anyone can read, only authenticated (admin) can update
ALTER TABLE public.settings ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "anon read settings"  ON public.settings;
DROP POLICY IF EXISTS "auth update settings" ON public.settings;

CREATE POLICY "anon read settings"
    ON public.settings FOR SELECT TO anon USING (true);

CREATE POLICY "auth update settings"
    ON public.settings FOR UPDATE TO authenticated USING (true);

-- 2. Update get_registration_count to include:
--    • all registrations
--    • reserves that are NOT cancelled
--    • admin-adjustable counter_offset
CREATE OR REPLACE FUNCTION get_registration_count()
RETURNS INTEGER
LANGUAGE sql
SECURITY DEFINER
AS $$
  SELECT (
    (SELECT COUNT(*)::INTEGER FROM public.registrations) +
    (SELECT COUNT(*)::INTEGER FROM public.reserves WHERE status <> 'cancelled') +
    COALESCE(
      (SELECT value::INTEGER FROM public.settings WHERE key = 'counter_offset'),
      0
    )
  );
$$;

GRANT EXECUTE ON FUNCTION get_registration_count() TO anon;

-- 3. RPC for admin to read the current offset (returns integer)
CREATE OR REPLACE FUNCTION get_counter_offset()
RETURNS INTEGER
LANGUAGE sql
SECURITY DEFINER
AS $$
  SELECT COALESCE((SELECT value::INTEGER FROM public.settings WHERE key = 'counter_offset'), 0);
$$;

GRANT EXECUTE ON FUNCTION get_counter_offset() TO authenticated;

-- 4. RPC for admin to set a new offset (authenticated only)
CREATE OR REPLACE FUNCTION set_counter_offset(p_offset INTEGER)
RETURNS VOID
LANGUAGE sql
SECURITY DEFINER
AS $$
  UPDATE public.settings SET value = p_offset::TEXT WHERE key = 'counter_offset';
$$;

GRANT EXECUTE ON FUNCTION set_counter_offset(INTEGER) TO authenticated;
