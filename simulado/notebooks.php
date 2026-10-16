<?php

// Inicia a sessão para verificar se o usuário está autenticado
session_start();

// Se não houver usuário logado, redireciona para a página de login
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

// Inclui o arquivo de configuração para conexão com o banco de dados
include 'config.php';

// Verifica se o formulário de cadastro foi enviado via POST e o botão 'add' foi clicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Prepara uma consulta segura para inserir um novo notebook, evitando SQL Injection
    // O campo cliente_id liga o notebook ao seu cliente (um cliente tem muitos notebooks)
    $stmt = $conn->prepare(
        "INSERT INTO notebooks (cliente_id, modelo, marca, serie, cor, ano)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    // Liga os parâmetros enviados no formulário aos marcadores da consulta preparada
    $stmt->bind_param(
        "issssi",
        $_POST['cliente_id'],
        $_POST['modelo'],
        $_POST['marca'],
        $_POST['serie'],
        $_POST['cor'],
        $_POST['ano']
    );
    // Executa a inserção no banco
    $stmt->execute();
    // Após adicionar o notebook, redireciona para a mesma página para atualizar a lista
    header("Location: notebooks.php");
    exit();
}

// Recebe o termo de busca enviado via GET, ou vazio se não houver
$busca = $_GET['busca'] ?? '';

// Protege o termo de busca contra SQL Injection antes de colocar na consulta
$termo = $conn->real_escape_string($busca);

// Monta a cláusula WHERE da consulta para filtrar notebooks pelo modelo ou nº de série, se houver termo de busca
$where = $busca ? "WHERE c.modelo LIKE '%$termo%' OR c.serie LIKE '%$termo%'" : '';

// Consulta SQL que une notebooks e cliente (JOIN) para mostrar o nome do cliente de cada notebook
$query = "SELECT c.*, d.nome AS cliente_nome
          FROM notebooks c
          JOIN cliente d ON c.cliente_id = d.id
          $where
          ORDER BY c.modelo";

// Executa a consulta e armazena o resultado
$notebooks = $conn->query($query);

// Busca todos os clientes para preencher o select do formulário de cadastro
$clientes = $conn->query("SELECT id, nome FROM cliente ORDER BY nome");
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">

<h2>Cadastro de Notebook</h2>

<!-- Formulário para pesquisa de notebooks -->
<form method="get">
    <input name="busca" placeholder="Buscar por modelo ou nº de série..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Buscar</button>
    <a href="principal.php">Voltar</a>
</form>

<!-- Tabela exibindo os notebooks buscados/listados -->
<table border="1" cellpadding="5">
    <tr><th>Modelo</th><th>Marca</th><th>Nº de série</th><th>Cor</th><th>Ano</th><th>Cliente</th><th>Ações</th></tr>
    <?php while($row = $notebooks->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['modelo']) ?></td>
        <td><?= htmlspecialchars($row['marca']) ?></td>
        <td><?= htmlspecialchars($row['serie'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['cor'] ?? '') ?></td>
       
        <td><?= htmlspecialchars($row['ano'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
        <td>
            <!-- Link para editar o notebook, passando o ID via GET -->
            <a href="editar_notebook.php?id=<?= $row['id'] ?>">Editar</a>
            <!-- Link para deletar o notebook, com confirmação (os serviços dele também são apagados) -->
            <a href="deletar_notebook.php?id=<?= $row['id'] ?>" onclick="return confirm('Excluir o notebook apaga também os serviços dele. Confirma?')">Excluir</a>
        </td>
      </tr>
    <?php endwhile; ?>
</table>

<!-- Formulário para cadastro de novo notebook -->
<h3>Novo notebook</h3>
<form method="post">
    <!-- Select com os clientes cadastrados: o valor enviado é o id do cliente -->
    <select name="cliente_id" required>
        <option value="">Selecione o cliente</option>
        <?php while ($d = $clientes->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
        <?php endwhile; ?>
    </select>
    
    <input name="modelo" required placeholder="Modelo">
    <input name="marca" required placeholder="Marca">
    <input name="serie" placeholder="Nº de série">
    <input name="cor" placeholder="Cor">
    <input type="number" name="ano" required placeholder="Ano">
    <button name="add">Cadastrar</button>
</form>
