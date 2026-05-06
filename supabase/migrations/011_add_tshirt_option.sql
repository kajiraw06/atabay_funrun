-- Migration 011: Add with_tshirt column to registrations
-- Runners can opt-in to a T-shirt add-on for an additional ₱50.

ALTER TABLE registrations
    ADD COLUMN IF NOT EXISTS with_tshirt BOOLEAN NOT NULL DEFAULT FALSE;
