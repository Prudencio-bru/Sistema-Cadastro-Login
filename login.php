<?php
    session_start();

    include_once('config.php');

    $mensagem = '';
    $classe_mensagem = '';

    if (isset($_POST['submit_login'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha_digitada = isset($_POST['senha']) ? $_POST['senha'] : '';

    if (empty($email) || empty($senha_digitada)) {
        $mensagem = "Por favor, preencha todos os campos.";
        $classe_mensagem = 'erro';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido.";
        $classe_mensagem = 'erro';
    } else {
        $query = "SELECT CPF, NOME, EMAIL, SENHA_HASH FROM tbUsuarios WHERE EMAIL = ?";
        $stmt = mysqli_prepare($conexao, $query);

        if ($stmt === false) {
            $mensagem = "Erro na preparação da consulta: " . mysqli_error($conexao);
            $classe_mensagem = 'erro';
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($resultado) > 0) {
                $usuario = mysqli_fetch_assoc($resultado);
                $senha_hash_armazenada = $usuario['SENHA_HASH'];

                if (password_verify($senha_digitada, $senha_hash_armazenada)) {
                    $mensagem = "Login realizado com sucesso! Bem-vindo(a), " . htmlspecialchars($usuario['NOME']) . ".";
                    $classe_mensagem = 'sucesso';

                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_cpf'] = $usuario['CPF'];
                    $_SESSION['user_name'] = $usuario['NOME'];
                    $_SESSION['user_email'] = $usuario['EMAIL'];

                    header("Location: tela_inicio.php");
                    exit();

                } else {
                    $mensagem = "E-mail ou senha incorretos.";
                    $classe_mensagem = 'erro';
                }
            } else {
                $mensagem = "E-mail ou senha incorretos.";
                $classe_mensagem = 'erro';
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conexao);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="mensagemErro" class="mensagem-box <?php echo $classe_mensagem; ?>">
        <?php echo $mensagem; ?>
    </div>
    <div class="form-wrapper">
        <form action="login.php" method="POST" class="container">
            <h1>Login</h1>
            <div class="input-box">
                <input placeholder="Email" type="email" name="email" id="email" required>
            </div>
            <div class="input-box">
                <input placeholder="Senha" type='password' name="senha" id="senha" required>
            </div>
            <div class="section-options">
                <div class="remember_me">
                    <input type="checkbox" name="remember_me" id="remember_me">
                    <label for="remember_me" >Lembrar de mim</label>
                </div>
                <a href="#">Esqueci minha senha</a>
            </div>
            <div class="button-box">
                <button name="submit_login" type="submit">Logar</button>
            </div>
            <div class="trocar-tela">
                <p>Não tem uma conta? <a href="cadastro.php">Cadastrar</a></p>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mensagemBox = document.getElementById('mensagemErro');
            if (mensagemBox.classList.contains('erro') || mensagemBox.classList.contains('sucesso')) {
                setTimeout(() => {
                    mensagemBox.style.opacity = '0';
                    setTimeout(() => {
                        mensagemBox.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>