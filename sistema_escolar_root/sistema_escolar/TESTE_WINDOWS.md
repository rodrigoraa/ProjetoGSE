# Como testar o sistema no Windows usando VSCode

Este guia ensina a testar o sistema no Windows usando o VSCode e o terminal integrado como Command Prompt (`cmd`).

## 1. Requisitos

Antes de começar, instale:

- Git.
- PHP 8.0 ou superior.
- VSCode.
- Navegador web.

O PHP precisa estar no `PATH` do Windows e com SQLite habilitado.

## 2. Clonar o repositório

Abra o Command Prompt do Windows fora do VSCode:

1. Pressione `Win + R`.
2. Digite `cmd`.
3. Pressione `Enter`.

Escolha onde salvar o projeto:

```cmd
cd C:\
mkdir Projetos
cd Projetos
```

Clone o repositório:

```cmd
git clone URL_DO_REPOSITORIO
```

Troque `URL_DO_REPOSITORIO` pela URL real do Git.

## 3. Abrir o projeto no VSCode

Ainda no Command Prompt, entre na pasta do sistema:

```cmd
cd NOME_DO_REPOSITORIO\sistema_escolar_root\sistema_escolar
```

Depois abra no VSCode:

```cmd
code .
```

Se o comando `code .` não funcionar, abra o VSCode manualmente e use:

```text
File > Open Folder
```

Selecione a pasta:

```text
sistema_escolar_root\sistema_escolar
```

Importante: abra a pasta `sistema_escolar`, não a pasta acima dela.

## 4. Abrir o terminal integrado como Command Prompt

No VSCode:

1. Clique em `Terminal`.
2. Clique em `New Terminal`.
3. Se o terminal abrir como PowerShell, clique na setinha ao lado do `+`.
4. Escolha `Command Prompt`.

O terminal deve mostrar algo parecido com:

```cmd
C:\Projetos\NOME_DO_REPOSITORIO\sistema_escolar_root\sistema_escolar>
```

Todos os comandos a seguir devem ser executados nesse terminal do VSCode.

## 5. Conferir Git, PHP e SQLite

No terminal do VSCode, rode:

```cmd
git --version
php -v
php -m | findstr /i sqlite
```

O último comando deve mostrar algo como:

```text
pdo_sqlite
sqlite3
```

Se não aparecer nada, o PHP está sem SQLite habilitado. Abra o `php.ini` e habilite:

```ini
extension=pdo_sqlite
extension=sqlite3
```

Depois feche e abra o VSCode novamente para o terminal reconhecer a mudança.

## 6. Conferir se o banco existe

No terminal do VSCode:

```cmd
dir database\secretaria.db
```

Se o arquivo aparecer, continue.

Se não aparecer, copie uma base válida ou restaure um backup para:

```text
database\secretaria.db
```

## 7. Criar o arquivo `.env` pelo VSCode

No painel lateral do VSCode:

1. Clique com o botão direito na raiz da pasta `sistema_escolar`.
2. Clique em `New File`.
3. Digite o nome:

```text
.env
```

Cole este conteúdo no arquivo:

```env
APP_ENV=development
APP_URL=http://127.0.0.1:8000
DB_PATH=C:\Projetos\NOME_DO_REPOSITORIO\sistema_escolar_root\sistema_escolar\database\secretaria.db
```

Troque `C:\Projetos\NOME_DO_REPOSITORIO\...` pelo caminho real do seu projeto.

Para descobrir o caminho da pasta atual, rode no terminal:

```cmd
cd
```

O `DB_PATH` deve ser esse caminho mais:

```text
\database\secretaria.db
```

Salve o arquivo `.env` com `Ctrl + S`.

## 8. Atualizar colunas recentes do banco

Se você tiver `sqlite3` instalado no Windows, rode no terminal do VSCode:

```cmd
sqlite3 database\secretaria.db "ALTER TABLE contratos ADD COLUMN faturado INTEGER NOT NULL DEFAULT 0;"
sqlite3 database\secretaria.db "ALTER TABLE contratos ADD COLUMN data_faturamento TEXT DEFAULT NULL;"
sqlite3 database\secretaria.db "ALTER TABLE contrato_folhas ADD COLUMN data_faturamento TEXT DEFAULT NULL;"
sqlite3 database\secretaria.db "ALTER TABLE contrato_folhas ADD COLUMN faturado INTEGER NOT NULL DEFAULT 0;"
sqlite3 database\secretaria.db "ALTER TABLE usuarios ADD COLUMN recebe_avisos_email INTEGER NOT NULL DEFAULT 1;"
```

Se aparecer erro dizendo que a coluna já existe, pode ignorar.

Se aparecer que `sqlite3` não é reconhecido, pode continuar. O sistema tenta criar essas colunas automaticamente quando as telas forem acessadas.

## 9. Subir o servidor local pelo VSCode

No terminal integrado do VSCode, rode:

```cmd
php -S 127.0.0.1:8000 -t public
```

Se deu certo, o terminal ficará preso mostrando o servidor em execução.

Não feche esse terminal enquanto estiver testando.

Abra o navegador e acesse:

```text
http://127.0.0.1:8000/login
```

## 10. Criar admin de teste, se necessário

Use um usuário já existente no banco.

Se não souber nenhum acesso e estiver usando uma base local de teste, crie um administrador temporário.

No VSCode, abra um segundo terminal:

1. Clique em `Terminal`.
2. Clique em `New Terminal`.
3. Escolha `Command Prompt`, se necessário.

Rode:

```cmd
php -r "$pdo=new PDO('sqlite:database/secretaria.db'); $hash=password_hash('admin123', PASSWORD_DEFAULT); $pdo->prepare('INSERT INTO usuarios (nome,email,senha,tipo,recebe_avisos_email) VALUES (?,?,?,?,?)')->execute(['Administrador Teste','admin@teste.com',$hash,'admin',1]); echo 'Usuario criado: admin@teste.com / admin123'.PHP_EOL;"
```

Depois acesse:

```text
E-mail: admin@teste.com
Senha: admin123
```

Use esse usuário apenas no ambiente local.

## 11. Checklist de teste no navegador

Depois de entrar:

1. Abra o painel inicial.
2. Confira se o menu lateral aparece.
3. Abra a tela de alunos.
4. Cadastre e edite um aluno.
5. Abra contratos/pedidos.
6. Abra os detalhes de um pedido e escolha uma nota.
7. Preencha `Lembrete para faturar` e salve sem marcar `Pedido faturado`.
8. Marque `Pedido faturado` e salve para conferir que a nota fica faturada mesmo que a data seja apenas um lembrete.
9. Confira se a listagem mostra a quantidade correta de notas faturadas.
10. Abra usuários como administrador.
11. Marque e desmarque `Receber avisos por e-mail`.
12. Abra certidões, agenda, relatórios e passivo.
13. Salve algum registro e confira se aparece mensagem de sucesso.

## 12. Testar os avisos por e-mail

Atenção: os crons podem enviar e-mails reais se o SMTP estiver configurado.

No terminal do VSCode:

```cmd
php public\cron_dva.php
php public\cron_certidao.php
```

Para testar envio real, configure no `.env`:

```env
MAIL_HOST=smtp.exemplo.com
MAIL_PORT=587
MAIL_USERNAME=usuario@exemplo.com
MAIL_PASSWORD=senha
MAIL_FROM=usuario@exemplo.com
MAIL_FROM_NAME=Sistema Escolar
ALERTA_DVA_DIAS=15
ALERTA_CERTIDAO_DIAS=15
```

Os avisos só vão para usuários com e-mail válido e com `Receber avisos por e-mail` marcado.

## 13. Problemas comuns

### O terminal do VSCode abriu em PowerShell

Clique na setinha ao lado do `+` no painel de terminal e escolha `Command Prompt`.

### O comando `php` não é reconhecido

O PHP não está no `PATH` do Windows.

Instale o PHP corretamente ou adicione a pasta do PHP ao `PATH`. Depois feche e abra o VSCode.

### O navegador mostra erro de banco

Confira se o `.env` existe na raiz da pasta `sistema_escolar`.

Confira se `DB_PATH` aponta para o arquivo correto:

```text
database\secretaria.db
```

### Aparece `could not find driver`

O PHP está sem SQLite habilitado.

Confira:

```cmd
php -m | findstr /i sqlite
```

Se não aparecer `pdo_sqlite`, habilite no `php.ini`.

### A porta 8000 já está em uso

Pare o servidor antigo com `Ctrl + C`, ou use outra porta:

```cmd
php -S 127.0.0.1:8001 -t public
```

Depois acesse:

```text
http://127.0.0.1:8001/login
```

### O CSS parece antigo

No navegador, pressione:

```text
Ctrl + F5
```
