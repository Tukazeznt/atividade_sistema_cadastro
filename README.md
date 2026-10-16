# Sistema de Gerenciamento de Clientes, Notebooks e Serviços — SAEP

Este projeto foi desenvolvido como parte de uma atividade prática do SAEP, com o objetivo de criar um sistema web para 
gerenciar clientes, notebooks e serviços de manutenção. A aplicação permite cadastrar, consultar, editar e excluir informações, 
além de registrar os serviços realizados em cada notebook e consultar o histórico de atendimentos.

O sistema foi desenvolvido utilizando PHP para a lógica da aplicação, HTML para a estrutura das páginas, CSS para a
estilização da interface e MySQL para o armazenamento dos dados. A conexão com o banco de dados é realizada por meio da 
biblioteca MySQLi.

A aplicação possui uma tela de login para autenticação dos usuários. Após entrar no sistema, o usuário pode acessar as 
funcionalidades de gerenciamento de clientes, notebooks e serviços. No cadastro de clientes, é possível registrar informações
como nome, CPF, telefone e e-mail, além de pesquisar, editar e excluir os registros. No cadastro de notebooks, podem ser
informados o modelo, a marca, o número de série, a cor, o ano e o cliente responsável pelo equipamento. O sistema também 
permite pesquisar, editar e excluir notebooks cadastrados.

A funcionalidade de serviços possibilita registrar a data, a descrição e o valor de cada manutenção realizada em um notebook.
O sistema conta ainda com uma página de histórico que reúne informações sobre os serviços, os equipamentos atendidos e seus
respectivos clientes, facilitando a consulta dos registros e o acompanhamento das manutenções realizadas.

O banco de dados utilizado é o `saep_db`, composto pelas tabelas `usuarios`, `cliente`, `notebooks` e `servicos`. 
Essas tabelas organizam as informações e estabelecem os relacionamentos necessários entre usuários, clientes, equipamentos
e serviços. As chaves primárias e estrangeiras são utilizadas para identificar os registros e relacionar os dados, enquanto
a opção `ON DELETE CASCADE` permite excluir registros dependentes quando um cliente ou notebook é removido.

Em relação à segurança, o sistema utiliza `password_hash()` para armazenar as senhas em formato hash e `password_verify()` 
para verificar as credenciais durante o login. O CPF dos clientes é armazenado de forma criptografada por meio das funções 
`AES_ENCRYPT()` e recuperado com `AES_DECRYPT()`. Além disso, são utilizadas consultas preparadas com `prepare()` e
`bind_param()` para ajudar a prevenir ataques de SQL Injection. O controle de acesso é realizado por meio de sessões, 
que impedem a visualização das páginas internas por usuários não autenticados. Também existe um limite de 30 minutos de
inatividade, após o qual a sessão é encerrada e o usuário precisa realizar o login novamente.

Para executar o projeto, é necessário utilizar um ambiente de desenvolvimento com suporte a PHP e MySQL, como o XAMPP. 
Os arquivos devem ser colocados na pasta pública do servidor local, geralmente chamada `htdocs`. Em seguida, é preciso 
iniciar o Apache e o MySQL, acessar o phpMyAdmin, criar o banco de dados `saep_db` e executar o script SQL disponibilizado 
na atividade para criar as tabelas e inserir os dados iniciais. Também é necessário verificar as configurações de conexão 
no arquivo `config.php`, incluindo o servidor, o usuário, a senha e o nome do banco de dados. A chave de criptografia do 
CPF deve ser a mesma utilizada no script SQL e no arquivo de configuração.

Após configurar o ambiente, o sistema pode ser acessado pelo navegador por meio do endereço local correspondente à pasta
do projeto. Por exemplo, se os arquivos estiverem na pasta `sistema`, o endereço será `http://localhost/sistema/`. 
Ao acessar a página inicial, o usuário será redirecionado para a tela de login.

As principais tecnologias utilizadas no desenvolvimento foram PHP, HTML, CSS, MySQL, MySQLi, XAMPP e phpMyAdmin. 
A atividade possibilita aplicar conhecimentos de desenvolvimento web, integração com banco de dados, operações CRUD,
autenticação de usuários, controle de sessões, relacionamentos entre tabelas e proteção de informações. Dessa forma, 
o sistema centraliza os dados de clientes, notebooks e serviços em uma única aplicação, tornando o gerenciamento dos 
equipamentos e o acompanhamento dos atendimentos técnicos mais organizados e eficientes.
