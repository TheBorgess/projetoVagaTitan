<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($_POST['name']  ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $pass  = sanitize($_POST['password'] ?? '');

    if ($name && $email && $pass) {
        $conn    = getConnection();
        $md5pass = md5($pass);

        $stmtCheck = $conn->prepare("SELECT id_user FROM user WHERE email = ? LIMIT 1");
        $stmtCheck->bind_param('s', $email);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $error = 'Este e-mail já está cadastrado.';
        } else {
            $stmtIns = $conn->prepare(
                "INSERT INTO user (name, email, password) VALUES (?, ?, ?)"
            );
            $stmtIns->bind_param('sss', $name, $email, $md5pass);

            if ($stmtIns->execute()) {
                $success = 'Usuário cadastrado com sucesso! Faça o login.';
            } else {
                $error = 'Erro ao cadastrar. Tente novamente.';
            }
            $stmtIns->close();
        }

        $stmtCheck->close();
        $conn->close();
    } else {
        $error = 'Preencha todos os campos obrigatórios.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário — Sistema OS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="form-page">
    <div class="form-card">
        <h1>Cadastrar Novo Usuário</h1>

        <?php if ($error): ?>
            <div class="msg msg--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg msg--success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
            
        <form id="register-form" method="POST" action="register.php" novalidate>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="Nome completo"
                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
            >
            <input
                type="email"
                id="email"
                name="email"
                placeholder="email@email.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            >
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••••••••"
            >
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Cadastrar</button>
                <a href="index.php" class="link">Voltar ao login</a>
            </div>
        </form>

        <?php else: ?>
            <a href="index.php" class="btn btn--primary">Ir para o Login</a>
        <?php endif; ?>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
