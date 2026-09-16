-- 2FA e e-mail-only: remove coluna phone (nunca usada em producao).
ALTER TABLE users DROP COLUMN phone;
