<?php

// Inicia a sessão para acessar dados do usuário logado e verificar autenticação
session_start();

// Se o usuário não estiver autenticado, redireciona para a página de login e encerra o script
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Verifica o tempo de expiração da sessão: se ficou mais de 30 minutos sem usar o sistema,
// encerra a sessão e volta para o login avisando que expirou
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}

// Ainda está no prazo: renova o horário do último acesso
$_SESSION['ultimo_acesso'] = time();

// Inclui o arquivo de configuração que contém a conexão com o banco de dados
include 'config.php';

// Inicializa variável para armazenar mensagens de alerta que poderão ser exibidas para o usuário
$alerta = '';

// Verifica se o formulário foi submetido via método POST para registrar o serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e sanitiza os dados enviados pelo formulário
    $notebook_id = intval($_POST['notebook_id']); // Notebook em que o serviço foi feito
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];       
    $valor = $_POST['valor'] !== '' ? $_POST['valor'] : 0;

    // Verifica se os campos obrigatórios foram preenchidos
    if (!$notebook_id || !$data || !$descricao) {
        $alerta = "Notebook, data e descrição são obrigatórios.";
    } else {
        // Prepara a inserção do serviço no banco de dados
        // O campo notebook_id liga o serviço ao notebook (um notebook tem muitos serviços)
        $stmt = $conn->prepare("INSERT INTO servicos (notebook_id, data, descricao, valor) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $notebook_id, $data, $descricao, $valor);
        $stmt->execute();

        // Redireciona para o histórico para mostrar o serviço registrado
        header('Location: historico.php');
        exit;
    }
}
// Consulta SQL para buscar todos os notebooks (com o nome do cliente) para popular o select no formulário
$sql = "SELECT c.id, c.modelo, c.serie, d.nome AS cliente_nome
        FROM notebooks c
        JOIN cliente d ON c.cliente_id = d.id
        ORDER BY c.modelo";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar Serviço</title>
    <!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h2>Registrar Serviço no Notebook</h2>

<!-- Exibe mensagem de alerta em destaque, caso tenha sido definida -->
<?php if ($alerta): ?>
    <div style="color: red; font-weight: bold; margin-bottom: 1em;">
        <?= htmlspecialchars($alerta) ?>
    </div>
<?php endif; ?>

<!-- Formulário de registro do serviço -->
<form action="" method="post">
    <label for="notebook_id">Notebook:</label>
    <select id="notebook_id" name="notebook_id" required>
        <!-- Preenche o select com os notebooks obtidos do banco (modelo, nº de série e cliente) -->
        <?php while ($row = $res->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['modelo'] . ' - ' . $row['serie'] . ' (' . $row['cliente_nome'] . ')') ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <label for="data">Data:</label>
    <input type="date" id="data" name="data" required>
    <br><br>

    <label for="descricao">Descrição:</label>
    <input type="text" id="descricao" name="descricao" required>
    <br><br>

    <label for="valor">Valor (R$):</label>
    <input type="number" step="0.01" min="0" id="valor" name="valor">
    <br><br>

    <button type="submit">Registrar</button>
</form>

<br>
<a href="principal.php">Voltar</a>

</body>
</html>
