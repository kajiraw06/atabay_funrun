-- Migration 016 — Make birthdate optional in registrations
ALTER TABLE public.registrations ALTER COLUMN birthdate DROP NOT NULL;
