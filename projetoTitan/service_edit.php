<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

requireAuth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('dashboard.php');
}

$conn = getConnection();

$stmtGet = $conn->prepare(
    "SELECT * FROM service WHERE id_service = ? LIMIT 1"
);
$stmtGet->bind_param('i', $id);
$stmtGet->execute();
$service = $stmtGet->get_result()->fetch_assoc();
$stmtGet->close();

if (!$service) {
    $conn->close();
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize($_POST['description'] ?? '');
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    if ($description && $price !== false && $price > 0) {
        $stmtUpd = $conn->prepare(
            "UPDATE service SET description = ?, price = ? WHERE id_service = ?"
        );
        $stmtUpd->bind_param('sdi', $description, $price, $id);

        if ($stmtUpd->execute()) {
            $_SESSION['msg']      = 'Serviço atualizado com sucesso!';
            $_SESSION['msg_type'] = 'success';
            $stmtUpd->close();
            $conn->close();
            redirect('dashboard.php');
        } else {
            $error = 'Erro ao atualizar o serviço. Tente novamente.';
        }
        $stmtUpd->close();
    } else {
        $error = 'Preencha a descrição e o valor corretamente.';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Serviço</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">

    <aside class="sidebar">
        <div class="sidebar__user">
            Logado como:<br>
            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </div>
        <nav>
            <a href="dashboard.php">← Dashboard</a>
        </nav>
        <div class="sidebar__logout">
            <a href="logout.php">Sair</a>
        </div>
    </aside>

    <main class="main">
        <div class="page-inner">
            <h1>Editar Serviço #<?= $id ?></h1>

            <?php if ($error): ?>
                <div class="msg msg--error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="service-form" method="POST" action="service_edit.php?id=<?= $id ?>" novalidate>
                <div class="form-card" style="padding:0">
                    <input
                        type="text"
                        id="description"
                        name="description"
                        placeholder="Descrição do serviço"
                        value="<?= htmlspecialchars($_POST['description'] ?? $service['description']) ?>"
                        maxlength="45"
                    >
                    <input
                        type="number"
                        id="price"
                        name="price"
                        placeholder="Preço"
                        step="0.01"
                        min="0.01"
                        value="<?= htmlspecialchars($_POST['price'] ?? $service['price']) ?>"
                    >
                    <div class="form-actions">
                        <button type="submit" class="btn btn--primary">Salvar</button>
                        <a href="dashboard.php" class="link">Cancelar</a>
                    </div>
                </div>
            </form>
            
        </div>
    </main>

</div>
<script src="assets/js/main.js"></script>
</body>
</html>
