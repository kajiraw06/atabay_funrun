-- Migration 017 — Fix payment_method constraint + add bank_transfer/landbank
ALTER TABLE public.registrations
    DROP CONSTRAINT IF EXISTS registrations_payment_method_check;

ALTER TABLE public.registrations
    ADD CONSTRAINT registrations_payment_method_check
    CHECK (payment_method IN ('gcash','paymaya','cash','bank_transfer','landbank'));
