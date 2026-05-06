-- Migration 015 — Make phone optional in registrations
ALTER TABLE public.registrations ALTER COLUMN phone DROP NOT NULL;
