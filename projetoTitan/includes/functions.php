<?php

//Funções auxiliares globais 

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAuth(): void
{
    startSession();
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

/**
 * @param string $url
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * @param string $value
 * @return string
 */
function sanitize(string $value): string
{
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * @param float $price
 * @return float
 */
function calcCommission(float $price): float
{
    if ($price > 10000) {
        return $price * 0.20;
    } elseif ($price > 1000) {
        return $price * 0.10;
    } else {
        return $price * 0.05;
    }
}

/**
 * @param float $value
 * @return string
 */
function formatMoney(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * @param string|null $date
 * @return string
 */
function formatDate(?string $date): string
{
    if (empty($date)) return '—';
    return date('d/m/Y', strtotime($date));
}

/**
 * @param string|null $finishedAt
 * @return string
 */
function getStatus(?string $finishedAt): string
{
    return empty($finishedAt) ? 'Pendente' : 'Finalizado';
}

/**
 * @param string $toEmail    
 * @param string $toName     
 * @param array  $service   
 * @return bool
 */
function sendFinishedEmail(string $toEmail, string $toName, array $service): bool
{
    
    $subject = 'Serviço Finalizado - ' . $service['description'];

    $body  = "Olá, {$toName}!\n\n";
    $body .= "Seu serviço foi finalizado com sucesso.\n\n";
    $body .= "Detalhes:\n";
    $body .= "  Descrição : " . $service['description'] . "\n";
    $body .= "  Valor     : " . formatMoney((float)$service['price']) . "\n";
    $body .= "  Comissão  : " . formatMoney((float)$service['commission_user']) . "\n";
    $body .= "  Finalizado: " . date('d/m/Y H:i') . "\n\n";
    $body .= "Atenciosamente,\nJM Informática\n";

    $headers = "From: noreply@jminformatica.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($toEmail, $subject, $body, $headers);
}
