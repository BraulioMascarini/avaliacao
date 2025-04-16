<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Adicionar atendente
if (isset($_POST['adicionar'])) {
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        $estrelas = 5;
        $sql = "INSERT INTO feedbacks (nome, estrelas, data_cadastro) VALUES (?, ?, NOW())";
        $stmt = $conexao->prepare($sql);
        if (!$stmt) {
            die("Erro: " . $conexao->error);
        }
        $stmt->bind_param("si", $nome, $estrelas);
        $stmt->execute();
        $stmt->close();
    }
}

// Remover atendente
if (isset($_GET['remover'])) {
    $id = $_GET['remover'];
    $stmt = $conexao->prepare("DELETE FROM feedbacks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Atendentes únicos
$sql = "SELECT MIN(id) as id, nome, MIN(data_cadastro) as data_cadastro FROM feedbacks GROUP BY nome ORDER BY data_cadastro ASC";
$result = $conexao->query($sql);

// Filtro por período
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '30';
$data_inicio = date("Y-m-d", strtotime("-$periodo days"));

// Total de avaliações
$sql_avaliacoes = "SELECT nome, COUNT(*) as total_avaliacoes FROM feedbacks WHERE data_cadastro >= ? GROUP BY nome ORDER BY total_avaliacoes DESC";
$stmt = $conexao->prepare($sql_avaliacoes);
$stmt->bind_param("s", $data_inicio);
$stmt->execute();
$result_avaliacoes = $stmt->get_result();

$nomes = [];
$avaliacoes = [];
$melhor_atendente = null;
$melhor_qtd = 0;

while ($row = $result_avaliacoes->fetch_assoc()) {
    $nomes[] = $row['nome'];
    $avaliacoes[] = $row['total_avaliacoes'];
    if ($row['total_avaliacoes'] > $melhor_qtd) {
        $melhor_qtd = $row['total_avaliacoes'];
        $melhor_atendente = $row['nome'];
    }
}

$nomes_json = json_encode($nomes);
$avaliacoes_json = json_encode($avaliacoes);

// Gráfico de classificação
$sql_categorias = "
    SELECT nome,
        SUM(CASE WHEN estrelas <= 2 THEN 1 ELSE 0 END) AS ruim,
        SUM(CASE WHEN estrelas = 3 THEN 1 ELSE 0 END) AS boa,
        SUM(CASE WHEN estrelas = 4 THEN 1 ELSE 0 END) AS otima,
        SUM(CASE WHEN estrelas = 5 THEN 1 ELSE 0 END) AS excelente
    FROM feedbacks
    WHERE data_cadastro >= ?
    GROUP BY nome
";
$stmt = $conexao->prepare($sql_categorias);
$stmt->bind_param("s", $data_inicio);
$stmt->execute();
$result_categorias = $stmt->get_result();

$nomes_cat = [];
$ruins = [];
$boas = [];
$otimas = [];
$excelentes = [];

while ($row = $result_categorias->fetch_assoc()) {
    $total = $row['ruim'] + $row['boa'] + $row['otima'] + $row['excelente'];
    if ($total == 0) $total = 1;
    $nomes_cat[] = $row['nome'];
    $ruins[] = round(($row['ruim'] / $total) * 100, 2);
    $boas[] = round(($row['boa'] / $total) * 100, 2);
    $otimas[] = round(($row['otima'] / $total) * 100, 2);
    $excelentes[] = round(($row['excelente'] / $total) * 100, 2);
}

$nomes_cat_json = json_encode($nomes_cat);
$ruins_json = json_encode($ruins);
$boas_json = json_encode($boas);
$otimas_json = json_encode($otimas);
$excelentes_json = json_encode($excelentes);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Atendentes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="container mt-4">

    <h2>Gerenciar Atendentes</h2>

    <form method="POST" class="mb-3">
        <input type="text" name="nome" class="form-control w-50 d-inline" placeholder="Nome do atendente" required>
        <button type="submit" name="adicionar" class="btn btn-primary">Adicionar</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Data de Cadastro</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['data_cadastro'] ?></td>
                    <td><a href="?remover=<?= $row['id'] ?>" class="btn btn-danger">Remover</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <form method="GET" class="mb-3">
        <label>Filtrar por período:</label>
        <select name="periodo" onchange="this.form.submit()" class="form-select w-25">
            <option value="30" <?= $periodo == '30' ? 'selected' : '' ?>>Últimos 30 dias</option>
            <option value="31" <?= $periodo == '31' ? 'selected' : '' ?>>Mês Atual</option>
        </select>
    </form>

    <h3>Melhor Atendente: <strong><?= $melhor_atendente ? "$melhor_atendente com $melhor_qtd avaliações" : "Nenhum registro" ?></strong></h3>

    <h4>Total de Avaliações por Atendente</h4>
    <canvas id="graficoAvaliacao" class="mb-5"></canvas>

    <h4>Avaliações por Tipo (Empilhadas por Atendente)</h4>
    <canvas id="graficoClassificacoes"></canvas>

    <button class="btn btn-success mt-4" onclick="gerarPDF()">Gerar PDF dos Gráficos</button>

    <a href="logout.php" class="btn btn-secondary mt-3">Sair</a>

    <script>
        const ctx1 = document.getElementById('graficoAvaliacao').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: <?= $nomes_json ?>,
                datasets: [{
                    label: "Número de Avaliações",
                    data: <?= $avaliacoes_json ?>,
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const ctx2 = document.getElementById('graficoClassificacoes').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= $nomes_cat_json ?>,
                datasets: [
                    { label: 'Ruim', data: <?= $ruins_json ?>, backgroundColor: 'rgba(255,99,132,0.7)' },
                    { label: 'Boa', data: <?= $boas_json ?>, backgroundColor: 'rgba(255,206,86,0.7)' },
                    { label: 'Ótima', data: <?= $otimas_json ?>, backgroundColor: 'rgba(54,162,235,0.7)' },
                    { label: 'Excelente', data: <?= $excelentes_json ?>, backgroundColor: 'rgba(75,192,192,0.7)' }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ": " + ctx.raw + "%"
                        }
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        },
                        title: {
                            display: true,
                            text: 'Percentual de Avaliações (%)'
                        }
                    }
                }
            }
        });

        async function gerarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const logoImg = new Image();
            logoImg.src = "logo.jpg";
            await new Promise(resolve => logoImg.onload = resolve);

            const dataHoje = new Date().toLocaleDateString('pt-BR');
            const melhor = <?= json_encode($melhor_atendente ? "$melhor_atendente com $melhor_qtd avaliações" : "Nenhum registro") ?>;

            doc.addImage(logoImg, "JPG", 150, 10, 40, 20);
            doc.setFontSize(16);
            doc.text("Relatório de Avaliações por Atendente", 10, 20);
            doc.setFontSize(12);
            doc.text("Data de geração: " + dataHoje, 10, 28);
            doc.text("Gerado por: Braulio Dias Mascarini", 10, 35);
            doc.text("Melhor Atendente: " + melhor, 10, 45);

            const canvas1 = await html2canvas(document.getElementById('graficoAvaliacao'));
            doc.addImage(canvas1.toDataURL('image/png'), 'PNG', 10, 50, 180, 90);

            doc.addPage();
            doc.setFontSize(14);
            doc.text("Distribuição Percentual das Avaliações", 10, 15);
            const canvas2 = await html2canvas(document.getElementById('graficoClassificacoes'));
            doc.addImage(canvas2.toDataURL('image/png'), 'PNG', 10, 20, 180, 90);

            doc.setFontSize(10);
            doc.text("Legenda:", 10, 115);
            doc.text("Ruim: 1~2 estrelas | Boa: 3 estrelas | Ótima: 4 estrelas | Excelente: 5 estrelas", 10, 120);

            doc.setFontSize(12);
            doc.text("_________________________", 10, 250);
            doc.text("Assinatura do responsável", 10, 258);

            doc.setFontSize(10);
            doc.text("Relatório gerado automaticamente - Paper Info | (19) 99682-4542", 10, 285);

            doc.save("avaliacoes_atendentes.pdf");
        }
    </script>
</body>
</html>
