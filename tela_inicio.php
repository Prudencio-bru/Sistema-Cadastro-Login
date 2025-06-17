<?php
    session_start();

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    $user_cpf = $_SESSION['user_cpf']; 
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="welcome-message">
        <h2>Bem-vindo(a), <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p>Você está logado(a) com o e-mail: <?php echo htmlspecialchars($user_email); ?></p>
        <p>Seu CPF é: <?php echo htmlspecialchars($user_cpf); ?></p>
    </div>
    <div class="button-logout">
        <button><a href="login.php">Sair</a></button>
    </div>
</body>
</html>