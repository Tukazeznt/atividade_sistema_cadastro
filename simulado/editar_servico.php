<?php

// Inicia a sessão para acessar os dados do usuário logado
session_start();

// Verifica se o usuário está autenticado; caso contrário, redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
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

// Inclui o arquivo de configuração para estabelecer a conexão com a base de dados
include 'config.php';

// Recebe o ID do serviço a ser editado via parâmetro GET na URL; se não informado, assume 0
$id = $_GET['id'] ?? 0;

// Prepara e executa uma consulta para buscar o serviço e o notebook em que ele foi feito
$sql = "SELECT p.*, c.modelo, c.serie FROM servicos p JOIN notebooks c ON p.notebook_id = c.id WHERE p.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$servico = $res->fetch_assoc();

// Se não encontrou o serviço, exibe mensagem e termina a execução
if (!$servico) {
    echo "Serviço não encontrado.";
    exit();
}

// Processa o formulário quando enviado via POST para atualizar o serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados atualizados do formulário
    $data = $_POST['data'];
    $descricao = $_POST ['descricao'];
    $valor = $_POST['valor'] !== '' ? $_POST['valor'] : 0;

    // Validação simples: data e descrição são obrigatórias
    if (!$data || !$descricao) {
        echo "Data e descrição são obrigatórias.";
    } else {
        // Atualiza a data (e a descrição e o valor) do serviço no banco de dados
        $sql = "UPDATE servicos SET data=?, descricao=?, valor=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $data, $descricao, $valor, $id);
        $stmt->execute();

        // Redireciona o usuário de volta para o histórico após atualizar
        header("Location: historico.php");
        exit();
    }
}
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">

<h2>Editar Serviço</h2>

<!-- Mostra em qual notebook o serviço foi feito -->
<p>Notebook: <?= htmlspecialchars($servico['modelo'] . ' - ' . $servico['serie']) ?></p>

<!-- Formulário para edição do serviço com dados já preenchidos -->
<form method="post">
    <label for="data">Data:</label>
    <input type="date" id="data" name="data" value="<?= htmlspecialchars($servico['data']) ?>" required>
    <br><br>

    <label for="descricao">Descrição:</label>
    <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($servico['descricao']) ?>" required>
    <br><br>

    <label for="valor">Valor (R$):</label>
    <input type="number" step="0.01" min="0" id="valor" name="valor" value="<?= htmlspecialchars($servico['valor']) ?>">
    <br><br>

    <button>Salvar</button>
</form>

<!-- Link para voltar ao histórico -->
<a href="historico.php">Voltar</a>
