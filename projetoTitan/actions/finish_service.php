<?php

 //   ≤ R$1.000,00  --> 5%  de comissão
 //   > R$1.000,00  --> 10% de comissão
 //   > R$10.000,00 --> 20% de comissão
 
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('../dashboard.php');
}

$conn = getConnection();

$stmt = $conn->prepare(
    "SELECT s.*, u.name AS user_name, u.email AS user_email
     FROM service s
     INNER JOIN user u ON u.id_user = s.user_id_user
     WHERE s.id_service = ? AND s.finished_at IS NULL
     LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$service) {
    $_SESSION['msg']      = 'Serviço não encontrado ou já finalizado.';
    $_SESSION['msg_type'] = 'error';
    $conn->close();
    redirect('../dashboard.php');
}

$commission  = calcCommission((float)$service['price']);
$finishedAt  = date('Y-m-d H:i:s');

$stmtUpd = $conn->prepare(
    "UPDATE service
     SET finished_at = ?, commission_user = ?
     WHERE id_service = ?"
);
$stmtUpd->bind_param('sdi', $finishedAt, $commission, $id);

if ($stmtUpd->execute()) {
    $service['commission_user'] = $commission;
    sendFinishedEmail($service['user_email'], $service['user_name'], $service);

    $_SESSION['msg']      = 'Serviço finalizado! Comissão: ' . formatMoney($commission);
    $_SESSION['msg_type'] = 'success';
} else {
    $_SESSION['msg']      = 'Erro ao finalizar o serviço.';
    $_SESSION['msg_type'] = 'error';
}

$stmtUpd->close();
$conn->close();
redirect('../dashboard.php');
