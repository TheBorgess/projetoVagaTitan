<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

requireAuth();
$conn = getConnection();

$filterName   = sanitize($_GET['name']    ?? '');
$filterStatus = sanitize($_GET['status']  ?? '');
$filterUser   = sanitize($_GET['user']    ?? '');
$filterFrom   = sanitize($_GET['from']    ?? '');
$filterTo     = sanitize($_GET['to']      ?? '');

$where  = ' WHERE 1=1 ';
$params = [];
$types  = '';

if ($filterName) {
    $like    = '%' . $filterName . '%';
    $where  .= ' AND s.description LIKE ? ';
    $params[] = $like;
    $types   .= 's';
}

if ($filterStatus === 'Pendente') {
    $where .= ' AND s.finished_at IS NULL ';
} elseif ($filterStatus === 'Finalizado') {
    $where .= ' AND s.finished_at IS NOT NULL ';
}

if ($filterUser) {
    $likeUser = '%' . $filterUser . '%';
    $where   .= ' AND u.name LIKE ? ';
    $params[] = $likeUser;
    $types   .= 's';
}

if ($filterFrom) {
    $where   .= ' AND DATE(s.created_at) >= ? ';
    $params[] = $filterFrom;
    $types   .= 's';
}

if ($filterTo) {
    $where   .= ' AND DATE(s.created_at) <= ? ';
    $params[] = $filterTo;
    $types   .= 's';
}

$sql = "SELECT s.*, u.name AS user_name, u.email AS user_email
        FROM service s
        INNER JOIN user u ON u.id_user = s.user_id_user
        {$where}
        ORDER BY s.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmtTotal = $conn->prepare(
    "SELECT COALESCE(SUM(price), 0) AS total
     FROM service WHERE user_id_user = ?"
);
$stmtTotal->bind_param('i', $_SESSION['user_id']);
$stmtTotal->execute();
$totalRow = $stmtTotal->get_result()->fetch_assoc();
$stmtTotal->close();

$stmtLast = $conn->prepare(
    "SELECT id_service, description, finished_at
     FROM service WHERE user_id_user = ?
     ORDER BY created_at DESC LIMIT 5"
);
$stmtLast->bind_param('i', $_SESSION['user_id']);
$stmtLast->execute();
$lastServices = $stmtLast->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtLast->close();

$stmtPend = $conn->prepare(
    "SELECT id_service, description
     FROM service WHERE user_id_user = ? AND finished_at IS NULL
     ORDER BY created_at DESC LIMIT 5"
);
$stmtPend->bind_param('i', $_SESSION['user_id']);
$stmtPend->execute();
$pendingServices = $stmtPend->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtPend->close();

$conn->close();

$sessionMsg  = $_SESSION['msg']      ?? '';
$sessionType = $_SESSION['msg_type'] ?? 'success';
unset($_SESSION['msg'], $_SESSION['msg_type']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">

    <aside class="sidebar">
        <div class="sidebar__user">
            Logado como:<br>
            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <small style="display:block;opacity:.6;font-size:12px;margin-top:2px;">
                <?= date('d/m/Y') ?>
            </small>
        </div>

        <nav>
            <a href="service_new.php">Cadastrar Serviço</a>
        </nav>

        <div class="sidebar__logout">
            <a href="logout.php">Sair</a>
        </div>
    </aside>

    <main class="main">
        <h1>Dashboard</h1>

        <?php if ($sessionMsg): ?>
            <div class="msg msg--<?= $sessionType ?>">
                <?= htmlspecialchars($sessionMsg) ?>
            </div>
        <?php endif; ?>

        <div class="summary-grid">

            <div class="summary-card">
                <h2>Últimos Serviços</h2>
                <?php if ($lastServices): ?>
                <ul>
                    <?php foreach ($lastServices as $ls): ?>
                    <li>
                        <strong><?= $ls['id_service'] ?></strong>
                        — <?= htmlspecialchars($ls['description']) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <p style="color:var(--color-muted)">Nenhum serviço registrado.</p>
                <?php endif; ?>
            </div>

            <div class="summary-card">
                <h2>Serviços Pendentes</h2>
                <?php if ($pendingServices): ?>
                <ul>
                    <?php foreach ($pendingServices as $ps): ?>
                    <li>
                        <strong><?= $ps['id_service'] ?></strong>
                        — <?= htmlspecialchars($ps['description']) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <p style="color:var(--color-muted)">Nenhum serviço pendente.</p>
                <?php endif; ?>

                <div style="margin-top:18px;border-top:1px solid #ddd;padding-top:14px;">
                    <div style="font-size:13px;color:var(--color-muted);margin-bottom:4px;">
                        Valor total dos seus serviços
                    </div>
                    <div class="summary-card__total">
                        <?= formatMoney((float)$totalRow['total']) ?>
                    </div>
                </div>
            </div>

        </div>

        <form method="GET" action="dashboard.php" class="filters">
            <input
                type="text"
                name="name"
                placeholder="Nome do serviço"
                value="<?= htmlspecialchars($filterName) ?>"
            >
            <input
                type="date"
                name="from"
                value="<?= htmlspecialchars($filterFrom) ?>"
                title="Data inicial"
            >
            <input
                type="date"
                name="to"
                value="<?= htmlspecialchars($filterTo) ?>"
                title="Data final"
            >
            <select name="status">
                <option value="">Todos os status</option>
                <option value="Pendente"   <?= $filterStatus === 'Pendente'   ? 'selected' : '' ?>>Pendente</option>
                <option value="Finalizado" <?= $filterStatus === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
            </select>
            <input
                type="text"
                name="user"
                placeholder="Nome do usuário"
                value="<?= htmlspecialchars($filterUser) ?>"
            >
            <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>
            <a href="dashboard.php" class="btn btn--sm" style="background:#888;color:#fff;">Limpar</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Valor</th>
                        <th>Comissão</th>
                        <th>Usuário</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($services): ?>
                    <?php foreach ($services as $svc): ?>
                    <?php $status = getStatus($svc['finished_at']); ?>
                    <tr>
                        <td><?= $svc['id_service'] ?></td>
                        <td><?= htmlspecialchars($svc['description']) ?></td>
                        <td>
                            <span class="status-<?= strtolower($status) ?>">
                                <?= $status ?>
                            </span>
                        </td>
                        <td><?= formatMoney((float)$svc['price']) ?></td>
                        <td>
                            <?= $svc['commission_user']
                                ? formatMoney((float)$svc['commission_user'])
                                : '—' ?>
                        </td>
                        <td><?= htmlspecialchars($svc['user_name']) ?></td>
                        <td><?= formatDate($svc['created_at']) ?></td>
                        <td>
                            <div class="actions">
                                <a href="service_edit.php?id=<?= $svc['id_service'] ?>"
                                   class="btn btn--warn">Editar</a>

                                <?php if (!$svc['finished_at']): ?>
                                <a href="actions/finish_service.php?id=<?= $svc['id_service'] ?>"
                                   class="btn btn--ok js-confirm-finish">Finalizar</a>
                                <?php endif; ?>

                                <a href="actions/delete_service.php?id=<?= $svc['id_service'] ?>"
                                   class="btn btn--danger js-confirm-delete">Excluir</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;color:var(--color-muted);padding:24px;">
                            Nenhum serviço encontrado.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
