<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

requireAuth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('../dashboard.php');
}

$conn = getConnection();

$stmt = $conn->prepare("DELETE FROM service WHERE id_service = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['msg']      = 'Serviço excluído com sucesso.';
    $_SESSION['msg_type'] = 'success';
} else {
    $_SESSION['msg']      = 'Serviço não encontrado ou erro ao excluir.';
    $_SESSION['msg_type'] = 'error';
}

$stmt->close();
$conn->close();
redirect('../dashboard.php');
