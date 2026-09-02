<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $pass  = sanitize($_POST['password'] ?? '');

    if ($email && $pass) {
        $conn     = getConnection();
        $md5pass  = md5($pass);

        $stmt = $conn->prepare(
            "SELECT id_user, name, email FROM user
             WHERE email = ? AND password = ? AND ativo = 1 LIMIT 1"
        );
        $stmt->bind_param('ss', $email, $md5pass);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if ($user) {
            $_SESSION['user_id']   = $user['id_user'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email']= $user['email'];
            redirect('dashboard.php');
        } else {
            $error = 'Ops, Email ou Senha inválido';
        }
    } else {
        $error = 'Ops, Email ou Senha inválido';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="form-page">
    <div class="form-card">
        <h1>Sistema Controle de Serviços</h1>

        <?php if ($error): ?>
            <div class="msg msg--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="login-form" method="POST" action="index.php" novalidate>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="email@email.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                autocomplete="email"
            >
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="current-password"
            >
            <div class="form-actions">
                <button type="submit" class="btn btn--ok">Entrar</button>
                <a href="register.php" class="link">Cadastrar usuário</a>
            </div>
        </form>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
