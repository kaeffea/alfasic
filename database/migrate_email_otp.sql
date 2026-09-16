-- =============================================================================
-- ALFASIC - Migration 2FA via e-mail (remove restos do TOTP; rodar 1x)
-- Pre-req: migrate_2fa.sql ja aplicada (colunas totp_* e user_recovery_codes existem)
-- =============================================================================
ALTER TABLE users
    DROP COLUMN totp_secret,
    DROP COLUMN totp_enabled,
    DROP COLUMN totp_last_step,
    DROP COLUMN totp_enrolled_at;

DROP TABLE IF EXISTS user_recovery_codes;

ALTER TABLE users
    ADD COLUMN phone VARCHAR(50) NULL AFTER password_hash;
