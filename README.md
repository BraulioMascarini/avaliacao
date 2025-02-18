# Avaliação de Atendimento - Projeto PHP

Este projeto PHP é um sistema simples para coleta e visualização de feedbacks de atendimento. Ele permite que os usuários avaliem o atendimento de uma equipe, atribuindo notas, comentários e selecionando o nome do atendente.

## Funcionalidades
- Formulário para submissão de feedbacks com o nome do atendente, avaliação e comentários.
- Armazenamento dos dados em um banco de dados MySQL.
- Visualização dos feedbacks em uma página separada.

## Estrutura do Projeto

O projeto é composto pelos seguintes arquivos principais:

### 1. `conexao.php`
Este arquivo contém a configuração de conexão com o banco de dados MySQL. Certifique-se de atualizar as credenciais de acesso ao banco de dados (host, usuário, senha e nome do banco de dados) conforme suas configurações locais.

### 2. `index.php`
Este arquivo é o formulário principal para coleta de feedbacks. Ele inclui:
- Um formulário com os seguintes campos:
  - Nome do Atendente (com opções: Karolaine, Ruan, Braulio e Raphaela).
  - Avaliação (de 1 a 5 estrelas).
  - Comentário/Sugestão.
- Validação de dados antes de inserir no banco de dados.
- Exibição de mensagens de sucesso ou erro.

### 3. `visualizar.php`
Este arquivo exibe todos os feedbacks registrados no banco de dados. Ele permite visualizar de forma organizada as avaliações feitas pelos clientes.

## Configuração do Banco de Dados

1. Crie um banco de dados MySQL e uma tabela para armazenar os feedbacks. Use o seguinte comando SQL para criar a tabela:
```sql
CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    estrelas INT NOT NULL,
    comentario TEXT
);
```

2. Configure as credenciais do banco de dados no arquivo `conexao.php`:
```php
$host = 'localhost';
$usuario = 'seu_usuario';
$senha = 'sua_senha';
$banco = 'seu_banco_de_dados';
```

## Como Executar o Projeto

1. Copie todos os arquivos para o servidor local (como XAMPP ou WAMP).
2. Inicie o servidor Apache e MySQL.
3. Acesse o formulário de avaliação pelo navegador:
```
http://localhost/seu_projeto/index.php
```
4. Para visualizar os feedbacks:
```
http://localhost/seu_projeto/visualizar.php
```

## Personalização
- Adicione ou remova atendentes no campo "Nome do Atendente" no arquivo `index.php`.
- Personalize o estilo do formulário e da página de visualização editando o CSS embutido nos arquivos HTML.

## Contribuição
Contribuições são bem-vindas! Sinta-se à vontade para melhorar o código, adicionar novas funcionalidades ou corrigir bugs.

## Licença
Este projeto está disponível sob a licença MIT. Sinta-se livre para usá-lo e modificá-lo conforme necessário.

