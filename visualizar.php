<?php
include("conexao.php");

// Verificar se a conexão foi estabelecida corretamente
if (!$conexao) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}

// Query SQL
$sql = "SELECT * FROM feedbacks ORDER BY data_envio DESC";
$resultado = $conexao->query($sql);

// Verificar se a consulta foi bem-sucedida
if (!$resultado) {
    die("Erro ao executar a consulta: " . $conexao->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbacks Recebidos</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 80%; margin: auto; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #333; color: white; }
    </style>
</head>
<body>

    <h2>Feedbacks Recebidos</h2>

    <?php if ($resultado->num_rows > 0): ?>
        <table>
            <tr>
                <th>Nome</th>
                <th>Avaliação</th>
                <th>Comentário</th>
                <th>Data</th>
            </tr>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["nome"]) ?></td>
                    <td><?= str_repeat("⭐", (int)$row["estrelas"]) ?></td>
                    <td><?= htmlspecialchars($row["comentario"]) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($row["data_envio"])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Nenhum feedback recebido ainda.</p>
    <?php endif; ?>

</body>
</html>
