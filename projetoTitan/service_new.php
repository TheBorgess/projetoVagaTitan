<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

requireAuth();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize($_POST['description'] ?? '');
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    if ($description && $price !== false && $price > 0) {
        $conn   = getConnection();
        $userId = $_SESSION['user_id'];

        $stmt = $conn->prepare(
            "INSERT INTO service (description, price, user_id_user)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sdi', $description, $price, $userId);

        if ($stmt->execute()) {
            $_SESSION['msg']      = 'Serviço cadastrado com sucesso!';
            $_SESSION['msg_type'] = 'success';
            $stmt->close();
            $conn->close();
            redirect('dashboard.php');
        } else {
            $error = 'Erro ao cadastrar o serviço. Tente novamente.';
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = 'Preencha a descrição e o valor corretamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Serviço</title>
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
            <h1>Cadastrar Novo Serviço</h1>

            <?php if ($error): ?>
                <div class="msg msg--error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="service-form" method="POST" action="service_new.php" novalidate>
                <div class="form-card" style="padding:0">
                    <input
                        type="text"
                        id="description"
                        name="description"
                        placeholder="Descrição do serviço"
                        value="<?= htmlspecialchars($_POST['description'] ?? '') ?>"
                        maxlength="45"
                    >
                   
                    <input
                        type="number"
                        id="price"
                        name="price"
                        step="0.01"
                        min="0.01"
                        placeholder="Preço"                 
                        value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                    > 

                    <div class="form-actions">
                        <button type="submit" class="btn btn--primary">Cadastrar</button>
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
