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

// Recebe o ID do notebook a ser editado via parâmetro GET na URL; se não informado, assume 0
$id = $_GET['id'] ?? 0;

// Prepara e executa uma consulta para buscar os dados do notebook com o ID informado
$sql = "SELECT * FROM notebooks WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$notebook = $res->fetch_assoc();

// Se não encontrou o notebook, exibe mensagem e termina a execução
if (!$notebook) {
    echo "Notebook não encontrado.";
    exit();
}

// Processa o formulário quando enviado via POST para atualizar o notebook
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados atualizados do formulário
    $cliente_id = $_POST['cliente_id'];
    $modelo = $_POST['modelo'];
    $marca = $_POST['marca'];
    $serie = $_POST['serie'];
    $cor = $_POST['cor'];
    $ano = $_POST['ano'];

    // Validação simples: cliente, modelo e marca são obrigatórios
    if (!$cliente_id || !$modelo || !$marca) {
        echo "Cliente, modelo e marca são obrigatórios.";
    } else {
        // Atualiza os dados do notebook no banco de dados
        $sql = "UPDATE notebooks SET cliente_id=?, modelo=?, marca=?, serie=?, cor=?, ano=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssii", $cliente_id, $modelo, $marca, $serie, $cor, $ano, $id);
        $stmt->execute();

        // Redireciona o usuário de volta para a lista de notebooks após atualizar
        
        header("Location: notebooks.php");
        exit();
    }
}

// Busca todos os clientes para preencher o select (permite trocar o cliente do notebook)
$clientes = $conn->query("SELECT id, nome FROM cliente ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">

<h2>Editar Notebook</h2>

<!-- Formulário para edição do notebook com dados já preenchidos -->
<form method="post">
    <select name="cliente_id" required>
        <!-- Deixa selecionado o cliente atual do notebook -->
        <?php while ($d = $clientes->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>" <?= $d['id'] == $notebook['cliente_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    <input name="modelo" placeholder="Modelo" value="<?= htmlspecialchars($notebook['modelo']) ?>" required>
    <input name="marca" placeholder="Marca" value="<?= htmlspecialchars($notebook['marca']) ?>" required>
    <input name="serie" placeholder="Nº de série" value="<?= htmlspecialchars($notebook['serie'] ?? '') ?>">
    <input name="cor" placeholder="Cor" value="<?= htmlspecialchars($notebook['cor'] ?? '') ?>">
    <input type="number" name="ano" required placeholder="Ano" value="<?= htmlspecialchars($notebook['ano'] ?? '') ?>">
    <button>Salvar</button>
</form>

<!-- Link para voltar à página de listagem de notebooks -->
<a href="notebooks.php">Voltar</a>
