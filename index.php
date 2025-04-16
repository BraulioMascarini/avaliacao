<?php
include("conexao.php");

// Se houver submissão do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar e validar os dados enviados pelo formulário
    $nome = isset($_POST["nome"]) ? trim($_POST["nome"]) : null;
    $estrelas = isset($_POST["estrelas"]) ? (int)$_POST["estrelas"] : null;
    $comentario = isset($_POST["comentario"]) ? trim($_POST["comentario"]) : null;

    // Verificar se os campos obrigatórios foram preenchidos
    if (empty($nome) || empty($estrelas)) {
        die("<script>alert('Erro: Preencha todos os campos obrigatórios!'); window.location='index.php';</script>");
    }

    // Preparar a query SQL para inserir os dados no banco
    $sql = "INSERT INTO feedbacks (nome, estrelas, comentario) VALUES (?, ?, ?)";
    $stmt = $conexao->prepare($sql);

    // Verificar se a preparação da query foi bem-sucedida
    if (!$stmt) {
        die("<script>alert('Erro na preparação da query: " . $conexao->error . "');</script>");
    }

    // Associar os parâmetros
    $stmt->bind_param("sis", $nome, $estrelas, $comentario);

    // Executar a query e verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        echo "<script>alert('Avaliação enviada com sucesso!'); window.location='index.php';</script>";
    } else {
        die("<script>alert('Erro ao inserir no banco: " . $stmt->error . "');</script>");
    }

    // Fechar a conexão
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação de Atendimento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #ADFF2F;
            color: black;
        }
        h2 {
            font-weight: bold;
        }
        form {
            width: 50%;
            margin: auto;
            background: black;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
        }
        label {
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
        }
        button {
            background: #32CD32;
            color: black;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #228B22;
        }
    </style>
</head>
<body>
    <h2>Avaliação de Atendimento</h2>
    <form action="index.php" method="POST">
        <label>Nome do Atendente:</label>
        <select name="nome" required>
            <option value="">Selecione o atendente</option>
            <?php
            // Buscar atendentes do banco de dados, evitando duplicatas
            $sql = "SELECT DISTINCT nome FROM feedbacks ORDER BY nome ASC";
            $result = $conexao->query($sql);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['nome'], ENT_QUOTES) . "'>" . htmlspecialchars($row['nome'], ENT_QUOTES) . "</option>";
                }
            } else {
                echo "<option value=''>Erro ao carregar atendentes</option>";
            }
            ?>
        </select>

        <label>Avaliação:</label>
        <select name="estrelas" required>
            <option value="1">⭐ (Ruim)</option>
            <option value="2">⭐⭐ (Regular)</option>
            <option value="3">⭐⭐⭐ (Bom)</option>
            <option value="4">⭐⭐⭐⭐ (Ótimo)</option>
            <option value="5">⭐⭐⭐⭐⭐ (Excelente)</option>
        </select>

        <label>Comentário/Sugestão:</label>
        <textarea name="comentario" rows="4"></textarea>

        <button type="submit">Enviar Avaliação</button>
    </form>
</body>
</html>
