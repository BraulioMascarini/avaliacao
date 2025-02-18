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
        body { font-family: Arial, sans-serif; text-align: center; }
        form { width: 50%; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 10px; }
        label { font-weight: bold; }
        input, textarea, select { width: 100%; margin: 10px 0; padding: 8px; }
        button { background: green; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background: darkgreen; }
    </style>
</head>
<body>

    <h2>Avaliação de Atendimento</h2>
    <form action="index.php" method="POST">
        <label>Nome do Atendente:</label>
        <input type="text" name="nome" required>

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
