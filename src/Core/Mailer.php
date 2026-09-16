<?php

declare(strict_types=1);

namespace Alfasic\Core;

/**
 * Envio de e-mail transacional via HTTP (Resend), sem dependencias.
 * Usa file_get_contents() ou cURL (o que existir). Falha fechado sem chave.
 */
class Mailer
{
    /**
     * @return array{ok:bool, error?:string}
     */
    public static function send(string $to, string $subject, string $text): array
    {
        Env::load();
        $apiKey = trim((string) Env::get('RESEND_API_KEY', ''));
        $from = trim((string) Env::get('OTP_FROM', 'Alfasic <nao-responda@alfagas.kaeffea.me>'));

        if ($apiKey === '') {
            error_log('[Alfasic][Mailer] RESEND_API_KEY ausente; e-mail NAO enviado para ' . $to);
            return ['ok' => false, 'error' => 'Serviço de e-mail não configurado. Avise a TI.'];
        }

        if (!Validator::validateEmail($to, true)) {
            return ['ok' => false, 'error' => 'E-mail de destino inválido.'];
        }

        $payload = json_encode([
            'from' => $from,
            'to' => [$to],
            'subject' => $subject,
            'text' => $text,
        ], JSON_UNESCAPED_UNICODE);

        // 1. cURL quando disponível
        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.resend.com/emails');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
            ]);
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($body === false) {
                error_log('[Alfasic][Mailer] cURL falhou: ' . $err);
                return ['ok' => false, 'error' => 'Falha ao enviar o código. Tente de novo.'];
            }
            if ($status >= 200 && $status < 300) {
                return ['ok' => true];
            }
            error_log('[Alfasic][Mailer] Resend HTTP ' . $status . ': ' . substr((string) $body, 0, 300));
            return ['ok' => false, 'error' => 'Falha ao enviar o código. Tente de novo.'];
        }

        // 2. Fallback streams
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 15,
                'ignore_errors' => true,
                'header' => "Authorization: Bearer {$apiKey}\r\nContent-Type: application/json\r\n",
                'content' => $payload,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents('https://api.resend.com/emails', false, $ctx);
        $status = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }
        if ($body !== false && $status >= 200 && $status < 300) {
            return ['ok' => true];
        }
        error_log('[Alfasic][Mailer] Resend falhou (streams), HTTP ' . $status);
        return ['ok' => false, 'error' => 'Falha ao enviar o código. Tente de novo.'];
    }

    public static function sendLoginCode(string $to, string $name, string $code, int $minutes, ?string $ip): array
    {
        $subject = "[Alfasic] Seu código: {$code}";
        $text = "Olá, {$name}.\n\n"
            . "Seu código de acesso ao ERP Alfasic é:\n\n    {$code}\n\n"
            . "Ele vale por {$minutes} minutos. Se não foi você, ignore e avise a TI.\n"
            . ($ip ? "Tentativa originada de: {$ip}\n" : '');
        return self::send($to, $subject, $text);
    }
}
