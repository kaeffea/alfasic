-- Canal do 2FA virou escolha de quem loga (sem padrao): coluna removida.
ALTER TABLE users DROP COLUMN twofa_channel;
