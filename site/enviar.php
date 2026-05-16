<?php
/**
 * Prontu! — Envio do formulário de contato via SMTP autenticado.
 * Destino: prontuassessoria@gmail.com
 * Remetente SMTP: contato@prontuassessoria.com.br (Hostinger)
 */

declare(strict_types=1);

// ---------- Configurações ----------
// Credenciais e destino ficam em config.php (não versionado).
$configPath = __DIR__ . '/config.php';
if (!is_readable($configPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Configuração ausente no servidor.']);
    exit;
}
$CFG = require $configPath;

// Rate limit: máximo de envios por IP por hora
const RATE_LIMIT_PER_HOUR = 5;
const RATE_LIMIT_FILE = __DIR__ . '/.rate_limit.json';

// ---------- CORS / método ----------
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

// ---------- Honeypot anti-spam ----------
// Bots preenchem todos os campos, inclusive os escondidos. Humanos não.
if (!empty($_POST['website'] ?? '')) {
    http_response_code(200);
    echo json_encode(['ok' => true]); // fingimos sucesso para não dar pista ao bot
    exit;
}

// ---------- Validação ----------
$nome    = trim((string)($_POST['name']    ?? ''));
$email   = trim((string)($_POST['email']   ?? ''));
$clinica = trim((string)($_POST['clinic']  ?? ''));
$fone    = trim((string)($_POST['phone']   ?? ''));
$msg     = trim((string)($_POST['message'] ?? ''));

$erros = [];
if ($nome === '' || mb_strlen($nome) < 2)          $erros[] = 'Nome inválido.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $erros[] = 'E-mail inválido.';
if ($clinica === '')                                $erros[] = 'Informe a clínica ou negócio.';
if ($fone === '' || mb_strlen($fone) < 8)           $erros[] = 'Telefone inválido.';
if ($msg === '' || mb_strlen($msg) < 5)             $erros[] = 'Mensagem muito curta.';

// Limite de tamanho para evitar abuso
if (mb_strlen($nome)    > 120)  $erros[] = 'Nome muito longo.';
if (mb_strlen($email)   > 160)  $erros[] = 'E-mail muito longo.';
if (mb_strlen($clinica) > 200)  $erros[] = 'Nome da clínica muito longo.';
if (mb_strlen($fone)    > 40)   $erros[] = 'Telefone muito longo.';
if (mb_strlen($msg)     > 5000) $erros[] = 'Mensagem muito longa.';

if ($erros) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => implode(' ', $erros)]);
    exit;
}

// ---------- Rate limit por IP ----------
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? 'unknown';
$ip = explode(',', $ip)[0];

$now = time();
$rate = ['data' => []];
if (is_readable(RATE_LIMIT_FILE)) {
    $raw = file_get_contents(RATE_LIMIT_FILE);
    $tmp = json_decode($raw ?: '{}', true);
    if (is_array($tmp) && isset($tmp['data']) && is_array($tmp['data'])) $rate = $tmp;
}
// Limpa registros com mais de 1h
foreach ($rate['data'] as $k => $list) {
    $rate['data'][$k] = array_values(array_filter($list, fn($t) => ($now - $t) < 3600));
    if (empty($rate['data'][$k])) unset($rate['data'][$k]);
}
$ipKey = hash('sha256', $ip);
$count = count($rate['data'][$ipKey] ?? []);
if ($count >= RATE_LIMIT_PER_HOUR) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Muitas tentativas em pouco tempo. Tente novamente daqui a pouco ou nos chame no WhatsApp.']);
    exit;
}
$rate['data'][$ipKey][] = $now;
@file_put_contents(RATE_LIMIT_FILE, json_encode($rate), LOCK_EX);

// ---------- Envio via PHPMailer ----------
require __DIR__ . '/vendor/phpmailer/Exception.php';
require __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $CFG['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $CFG['smtp_user'];
    $mail->Password   = $CFG['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL/TLS na porta 465
    $mail->Port       = (int)$CFG['smtp_port'];
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 20;

    $mail->setFrom($CFG['mail_from'], $CFG['mail_from_name']);
    $mail->addAddress($CFG['mail_to'], $CFG['mail_to_name']);
    $mail->addReplyTo($email, $nome);

    $mail->Subject = 'Novo contato pelo site — ' . $clinica;

    // Corpo HTML
    $h = fn($s) => htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $msgHtml = nl2br($h($msg));
    $body = "
    <div style='font-family:Inter,Arial,sans-serif;color:#0D223F;'>
      <h2 style='font-family:Georgia,serif;color:#0D223F;'>Novo contato pelo site</h2>
      <table cellpadding='6' style='border-collapse:collapse;font-size:14px;'>
        <tr><td style='color:#737373;'>Nome</td><td><strong>{$h($nome)}</strong></td></tr>
        <tr><td style='color:#737373;'>E-mail</td><td><a href='mailto:{$h($email)}'>{$h($email)}</a></td></tr>
        <tr><td style='color:#737373;'>Clínica</td><td>{$h($clinica)}</td></tr>
        <tr><td style='color:#737373;'>Telefone</td><td><a href='tel:{$h($fone)}'>{$h($fone)}</a></td></tr>
      </table>
      <h3 style='font-family:Georgia,serif;color:#0D223F;margin-top:24px;'>Mensagem</h3>
      <p style='line-height:1.6;'>{$msgHtml}</p>
      <hr style='border:none;border-top:1px solid #E6E6E6;margin:24px 0;'>
      <p style='font-size:12px;color:#A6A6A6;'>Enviado de prontuassessoria.com.br · IP {$h($ip)} · " . date('d/m/Y H:i') . "</p>
    </div>";

    $bodyText = "Novo contato pelo site Prontu!\n\n"
              . "Nome: {$nome}\nE-mail: {$email}\nClínica: {$clinica}\nTelefone: {$fone}\n\n"
              . "Mensagem:\n{$msg}\n\n"
              . "— Enviado de prontuassessoria.com.br · " . date('d/m/Y H:i');

    $mail->isHTML(true);
    $mail->Body    = $body;
    $mail->AltBody = $bodyText;

    $mail->send();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log('[prontu-form] ' . $mail->ErrorInfo);
    echo json_encode(['ok' => false, 'error' => 'Não conseguimos enviar agora. Tente novamente ou nos chame no WhatsApp.']);
}
