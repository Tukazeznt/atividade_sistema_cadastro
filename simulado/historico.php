<?php

// Inicia a sessão para acessar dados do usuário autenticado
session_start();

// Verifica se o usuário está logado; se não, redireciona para a página de login
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

// Monta a consulta SQL para buscar o histórico de serviços
// Traz TODOS os dados do notebook (modelo, marca, nº de série, cor e ano) e do cliente (nome, CPF, telefone e e-mail)
// A consulta une as tabelas servicos, notebooks e cliente seguindo os relacionamentos:
// servicos.notebook_id -> notebooks.id e notebooks.cliente_id -> cliente.id
// Os registros são ordenados pela data do serviço (do mais antigo para o mais novo)
// Prova 01 (por dia): ORDER BY p.data
// Prova 02 (por mês): ORDER BY MONTH(p.data), p.data
// Prova 03 (por ano): ORDER BY YEAR(p.data), p.data
$sql = "SELECT p.id, p.data, p.descricao, p.valor,
               c.modelo, c.marca, c.serie, c.cor, c.ano,
               d.nome AS cliente_nome, CAST(AES_DECRYPT(d.cpf, '$chave') AS CHAR) AS cliente_cpf,
               d.telefone, d.email
        FROM servicos p
        JOIN notebooks c ON p.notebook_id = c.id
        JOIN cliente d ON c.cliente_id = d.id
        ORDER BY p.data";

// Executa a consulta no banco de dados
$result = $conn->query($sql);
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">

<!-- Título da página -->
<h2>Histórico de Serviços</h2>

<!-- Tabela para exibir o histórico com todos os dados do serviço, do notebook e do cliente -->
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Data</th>
        <th>Serviço</th>
        <th>Valor (R$)</th>
        <th>Modelo</th>
        <th>Marca</th>
        <th>Nº de série</th>
        <th>Cor</th>
        <th>Ano</th>
        <th>Cliente</th>
        <th>CPF</th>
        <th>Telefone</th>
        <th>E-mail</th>
        <th>Ações</th>
    </tr>

    <!-- Iteração sobre cada registro retornado do banco para popular as linhas da tabela -->
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <!-- Exibe o ID do serviço -->
        <td><?= intval($row['id']) ?></td>
        <!-- Exibe a data do serviço no formato dia/mês/ano -->
        <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
        <!-- Exibe a descrição do serviço realizado -->
        <td><?= htmlspecialchars($row['descricao']) ?></td>
        <!-- Exibe o valor do serviço com duas casas decimais -->
        <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
        <!-- Dados do notebook -->
        <td><?= htmlspecialchars($row['modelo']) ?></td>
        <td><?= htmlspecialchars($row['marca']) ?></td>
        <td><?= htmlspecialchars($row['serie'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['cor'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['ano'] ?? '') ?></td>
        <!-- Dados do cliente (o CPF aparece descriptografado) -->
        <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
        <td><?= htmlspecialchars($row['cliente_cpf'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['telefone'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
        <td>
            <!-- Link para editar o serviço (atualizar a data), passando o ID via GET -->
            <a href="editar_servico.php?id=<?= $row['id'] ?>">Editar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Link para voltar à página principal do sistema -->
<a href="principal.php">Voltar</a>
