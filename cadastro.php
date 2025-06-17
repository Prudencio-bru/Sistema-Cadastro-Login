<?php

    include_once('config.php');

    $mensagem = '';
    $classe_mensagem = '';

    $nome_salvo = '';
    $cpf_salvo = '';
    $email_salvo = '';
    $telefone_salvo = '';
    $data_nascimento_salva = '';
    $genero_salvo = '';
    $cep_salvo = '';
    $endereco_salvo = '';
    $bairro_salvo = '';
    $cidade_salvo = '';
    $estado_salvo = '';

    //Função para verificar o CPF
    function validarCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;}

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;}

        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += (int)$cpf[$i] * (10 - $i);}
        $resto = $soma % 11;
        $dv1 = ($resto < 2) ? 0 : 11 - $resto;

        if ((int)$cpf[9] != $dv1) {
            return false; }

        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += (int)$cpf[$i] * (11 - $i);}
        $resto = $soma % 11;
        $dv2 = ($resto < 2) ? 0 : 11 - $resto;

        if ((int)$cpf[10] != $dv2) {
            return false;}

        return true;
    }

    if(isset($_POST['submit'])){

        $nome_salvo = isset($_POST['nome']) ? $_POST['nome'] : '';
        $cpf_salvo = isset($_POST['cpf']) ? $_POST['cpf'] : '';
        $email_salvo = isset($_POST['email']) ? $_POST['email'] : '';
        $telefone_salvo = isset($_POST['telefone']) ? $_POST['telefone'] : '';
        $data_nascimento_salva = isset($_POST['data_nascimento']) ? $_POST['data_nascimento'] : '';
        $genero_salvo = isset($_POST['genero']) ? $_POST['genero'] : '';
        $cep_salvo = isset($_POST['cep']) ? $_POST['cep'] : '';
        $endereco_salvo = isset($_POST['endereco']) ? $_POST['endereco'] : '';
        $bairro_salvo = isset($_POST['bairro']) ? $_POST['bairro'] : '';
        $cidade_salvo = isset($_POST['cidade']) ? $_POST['cidade'] : '';
        $estado_salvo = isset($_POST['estado']) ? $_POST['estado'] : '';

        $senha_digitada = $_POST['senha'];
        $senha_confirmada = $_POST['confirmar_senha'];

        //Limpa os caracteres do cpf, cep e telefone
        $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf_salvo);
        $cep_limpo = preg_replace('/[^0-9]/', '', $cep_salvo);
        $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone_salvo);

        //Valida o cpf
        if (!validarCPF($cpf_limpo)) {
            $mensagem = "Erro: O CPF digitado é inválido. Por favor, verifique e digite novamente.";
            $classe_mensagem = 'erro';
        }
        //Valida a senha
        elseif($senha_digitada !== $senha_confirmada){
            $mensagem = "Erro: As senhas não coincidem. Por favor, digite novamente.";
            $classe_mensagem = 'erro';
        }
        elseif (strlen($cep_limpo) != 8) {
            $mensagem = "Erro: O CEP digitado é inválido.";
            $classe_mensagem = 'erro';
        }
        elseif (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
            $mensagem = "Erro: O Telefone digitado é inválido. Digite um número com DDD (ex: 11987654321).";
            $classe_mensagem = 'erro';
        }
        else {
            $sql_check_cpf = "SELECT CPF FROM tbUsuarios WHERE CPF = ?";
            $stmt_check = mysqli_prepare($conexao, $sql_check_cpf);
            if ($stmt_check === false) {
                $mensagem = "Erro na preparação da verificação de CPF: " . mysqli_error($conexao);
                $classe_mensagem = 'erro';
            } 
            else {
                mysqli_stmt_bind_param($stmt_check, "s", $cpf_limpo);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);

                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    $mensagem = "Erro: O CPF " . htmlspecialchars($cpf_salvo) . " já está cadastrado.";
                    $classe_mensagem = 'erro';
                }
                else {
                    $senha_hash = password_hash($senha_digitada, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO tbUsuarios(CPF, NOME, EMAIL, TELEFONE, DATA_NASCIMENTO, GENERO, CEP, ENDERECO, BAIRRO, CIDADE, ESTADO, SENHA_HASH) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";

                    $stmt = mysqli_prepare($conexao, $sql);

                    if($stmt === false)
                    {
                        $mensagem = "Erro na preparação da query: " . mysqli_error($conexao);
                        $classe_mensagem = 'erro';
                    }
                    else {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssssssssssss",
                            $cpf_limpo, $nome_salvo, $email_salvo, $telefone_limpo, $data_nascimento_salva, $genero_salvo, $cep_limpo, $endereco_salvo, $bairro_salvo, $cidade_salvo, $estado_salvo, $senha_hash
                        );

                        if(mysqli_stmt_execute($stmt)){
                            $mensagem = "Cadastro realizado com sucesso! Redirecionando para o login...";
                            $classe_mensagem = 'sucesso';

                            $mensagem = "Cadastro realizado com sucesso!";
                            $classe_mensagem = 'sucesso';
                            $nome_salvo = ''; $cpf_salvo = ''; $email_salvo = ''; $telefone_salvo = '';
                            $data_nascimento_salva = ''; $genero_salvo = ''; $cep_salvo = ''; $endereco_salvo = '';
                            $bairro_salvo = ''; $cidade_salvo = ''; $estado_salvo = '';

                            header("Location: login.php");
                            exit();
                        }
                        else {
                            //Entrada duplicada
                            if (mysqli_errno($conexao) == 1062) {
                                $mensagem = "Erro: O CPF " . htmlspecialchars($cpf_salvo) . " já está cadastrado.";
                                $classe_mensagem = 'erro';
                            }
                            else {
                                $mensagem = "Erro ao cadastrar: " . mysqli_error($conexao);
                                $classe_mensagem = 'erro';
                            }
                            mysqli_stmt_close($stmt);
                        }
                    }
                }
                mysqli_stmt_close($stmt_check);
            }
        } 
    }
    if (isset($conexao) && $conexao instanceof mysqli) {
            mysqli_close($conexao);
    }

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="mensagemErro" class="mensagem-box <?php echo $classe_mensagem; ?>">
        <?php echo $mensagem; ?>
    </div>
    
    <div class="form-wrapper">
        <form action="cadastro.php" method="POST" class="container">
            <h1>Cadastro</h1>
            <div class="input-box">
                <input placeholder="Nome Completo" type="text" name="nome" id="nome" required value="<?php echo htmlspecialchars($nome_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="CPF" type="text" name="cpf" id="cpf" maxlength="14" required value="<?php echo htmlspecialchars($cpf_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Email" type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Telefone" type="tel" name="telefone" id="telefone" maxlength="15" required value="<?php echo htmlspecialchars($telefone_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Data de Nascimento" type="date" name="data_nascimento" id="data_nascimento" required value="<?php echo htmlspecialchars($data_nascimento_salva); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Senha" type="password" name="senha" id="senha" required>
            </div>
            <div class="input-box">
                <input placeholder="Confirme a Senha" type="password" name="confirmar_senha" id="confirmar_senha" required>
            </div>
            <div class="genero">
                <p>Gênero:</p>
                <div class="input-genero">
                    <input type="radio" id="feminino" name="genero" value="feminino" <?php echo ($genero_salvo === 'feminino') ? 'checked' : ''; ?> required>
                    <label for="feminino">Feminino</label>
                    <br>
                    <input type="radio" id="masculino" name="genero" value="masculino" <?php echo ($genero_salvo === 'masculino') ? 'checked' : ''; ?> required>
                    <label for="masculino">Masculino</label>
                    <br>
                    <input type="radio" id="outro" name="genero" value="outro" <?php echo ($genero_salvo === 'outro') ? 'checked' : ''; ?> required>
                    <label for="outro">Outro</label>
                    <br>
                    <input type="radio" id="prefere_não_informar" name="genero" value="prefere_não_informar" <?php echo ($genero_salvo === 'prefere_não_informar') ? 'checked' : ''; ?> required>
                    <label for="prefere_não_informar">Prefiro não informar</label>
                </div>
            </div>
            <div class="input-box">
                <input placeholder="CEP" type="text" name="cep" id="cep" maxlength="9" required value="<?php echo htmlspecialchars($cep_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Endereço" type="text" name="endereco" id="endereco" readonly value="<?php echo htmlspecialchars($endereco_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Bairro" type="text" name="bairro" id="bairro" readonly value="<?php echo htmlspecialchars($bairro_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Cidade" type="text" name="cidade" id="cidade" readonly value="<?php echo htmlspecialchars($cidade_salvo); ?>">
            </div>
            <div class="input-box">
                <input placeholder="Estado" type="text" name="estado" id="estado" readonly value="<?php echo htmlspecialchars($estado_salvo); ?>">
            </div>
            <div class="button-box">
                <button name="submit" type="submit">Cadastrar</button>
            </div>
            <div class="trocar-tela">
                <p>Já tem uma conta? <a href="login.php">Logar</a></p>
            </div>
        </form>
    </div>
    <script src="https://unpkg.com/imask"></script>
    <script>
        const cepInput = document.getElementById('cep');
        const cepMask = { mask: '00000-000'};
        IMask(cepInput, cepMask);

        const telefoneInput = document.getElementById('telefone');
        const telefoneMask = {mask:[{ mask: '(00) 00000-0000'}, { mask: '(00) 0000-0000'}]};
        IMask(telefoneInput, telefoneMask);

        const cpfInput = document.getElementById('cpf');
        const cpfMask = { mask: '000.000.000-00' };
        IMask(cpfInput, cpfMask);
        
        //Preenchimento automático de acordo com o CEP
        const enderecoInput = document.getElementById('endereco');
        const bairroInput = document.getElementById('bairro');
        const cidadeInput = document.getElementById('cidade');
        const estadoInput = document.getElementById('estado');

        //Limpa os campos de endereço
        function limparCamposEndereco() {
        enderecoInput.value = '';
        bairroInput.value = '';
        cidadeInput.value = '';
        estadoInput.value = '';
        enderecoInput.removeAttribute('readonly');
        bairroInput.removeAttribute('readonly');
        cidadeInput.removeAttribute('readonly');
        estadoInput.removeAttribute('readonly');
        enderecoInput.focus();
        }

        //Preenche os campos de endereço
        function preencherCamposEndereco(dados) {
            enderecoInput.value = dados.logradouro;
            bairroInput.value = dados.bairro;
            cidadeInput.value = dados.localidade;
            estadoInput.value = dados.uf;
            if (dados.logradouro) enderecoInput.setAttribute('readonly', 'readonly');
            if (dados.bairro) bairroInput.setAttribute('readonly', 'readonly');
            if (dados.localidade) cidadeInput.setAttribute('readonly', 'readonly');
            if (dados.uf) estadoInput.setAttribute('readonly', 'readonly');
        }

        cepInput.addEventListener('blur', function() {
            let cep = this.value.replace(/\D/g, '');
             if (cep.length === 8) { // Verifica se o CEP tem 8 dígitos
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        preencherCamposEndereco(data);
                    } 
                    else {
                        limparCamposEndereco();
                        alert('CEP não encontrado ou inválido. Por favor, preencha o endereço manualmente.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    limparCamposEndereco();
                    alert('Ocorreu um erro ao buscar o CEP. Por favor, preencha o endereço manualmente.');
                });
        } 
        else {
            limparCamposEndereco();
        }
    });

        document.addEventListener('DOMContentLoaded', function() {
            const mensagemBox = document.getElementById('mensagemErro');
            if (mensagemBox.classList.contains('Erro') || mensagemBox.classList.contains('sucesso')) {
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