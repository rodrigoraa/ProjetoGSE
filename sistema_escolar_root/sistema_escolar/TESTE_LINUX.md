# Como testar o sistema no Linux usando VSCode

Este guia ensina a testar o sistema no Linux usando o VSCode e o terminal integrado.

## 1. Requisitos

Antes de começar, instale:

- Git.
- PHP 8.0 ou superior.
- Extensão SQLite/PDO SQLite do PHP.
- SQLite CLI.
- VSCode.
- Navegador web.

Em Ubuntu/Debian, uma instalação comum seria:

```bash
sudo apt update
sudo apt install php php-cli php-sqlite3 sqlite3 git
```

Se o VSCode ainda não estiver instalado, instale pela forma recomendada para a sua distribuição Linux.

## 2. Clonar o repositório

Abra o terminal do Linux.

Escolha onde salvar o projeto:

```bash
mkdir -p ~/Projetos
cd ~/Projetos
```

Clone o repositório:

```bash
git clone URL_DO_REPOSITORIO
```

Troque `URL_DO_REPOSITORIO` pela URL real do Git.

## 3. Abrir o projeto no VSCode

Ainda no terminal, entre na pasta do sistema:

```bash
cd NOME_DO_REPOSITORIO/sistema_escolar_root/sistema_escolar
```

Depois abra no VSCode:

```bash
code .
```

Se o comando `code .` não funcionar, abra o VSCode manualmente e use:

```text
File > Open Folder
```

Selecione a pasta:

```text
sistema_escolar_root/sistema_escolar
```

Importante: abra a pasta `sistema_escolar`, não a pasta acima dela.

## 4. Abrir o terminal integrado no VSCode

No VSCode:

1. Clique em `Terminal`.
2. Clique em `New Terminal`.

O terminal deve abrir já dentro da pasta do projeto, mostrando algo parecido com:

```bash
~/Projetos/NOME_DO_REPOSITORIO/sistema_escolar_root/sistema_escolar$
```

Todos os comandos a seguir devem ser executados nesse terminal integrado do VSCode.

## 5. Conferir Git, PHP e SQLite

No terminal do VSCode, rode:

```bash
git --version
php -v
php -m | grep -i sqlite
sqlite3 --version
```

O comando do PHP deve mostrar algo como:

```text
pdo_sqlite
sqlite3
```

Se não aparecer `pdo_sqlite` ou `sqlite3`, instale ou habilite a extensão SQLite do PHP:

```bash
sudo apt install php-sqlite3
```

Depois feche e abra o terminal do VSCode novamente.

## 6. Conferir se o banco existe

No terminal do VSCode:

```bash
ls database/secretaria.db
```

Se o arquivo aparecer, continue.

Se não aparecer, copie uma base válida ou restaure um backup para:

```text
database/secretaria.db
```

## 7. Criar o arquivo `.env` pelo VSCode

No painel lateral do VSCode:

1. Clique com o botão direito na raiz da pasta `sistema_escolar`.
2. Clique em `New File`.
3. Digite o nome:

```text
.env
```

Para descobrir o caminho completo do banco, rode no terminal:

```bash
realpath database/secretaria.db
```

Cole este conteúdo no arquivo `.env`, ajustando o caminho:

```env
APP_ENV=development
APP_URL=http://127.0.0.1:8000
DB_PATH=/home/seu_usuario/Projetos/NOME_DO_REPOSITORIO/sistema_escolar_root/sistema_escolar/database/secretaria.db
```

O `DB_PATH` deve ser exatamente o caminho retornado pelo comando:

```bash
realpath database/secretaria.db
```

Salve o arquivo `.env` com `Ctrl + S`.

## 8. Ajustar permissões do banco

Garanta que o usuário atual consiga ler e escrever no banco:

```bash
chmod u+rw database/secretaria.db
chmod u+rwx database
```

## 9. Atualizar colunas recentes do banco

Rode uma vez no terminal do VSCode:

```bash
sqlite3 database/secretaria.db "ALTER TABLE contratos ADD COLUMN faturado INTEGER NOT NULL DEFAULT 0;"
sqlite3 database/secretaria.db "ALTER TABLE contratos ADD COLUMN data_faturamento TEXT DEFAULT NULL;"
sqlite3 database/secretaria.db "ALTER TABLE usuarios ADD COLUMN recebe_avisos_email INTEGER NOT NULL DEFAULT 1;"
```

Se aparecer erro dizendo que a coluna já existe, pode ignorar.

## 10. Subir o servidor local pelo VSCode

No terminal integrado do VSCode, rode:

```bash
php -S 127.0.0.1:8000 -t public
```

Se deu certo, o terminal ficará preso mostrando o servidor em execução.

Não feche esse terminal enquanto estiver testando.

Abra o navegador e acesse:

```text
http://127.0.0.1:8000/login
```

## 11. Criar admin de teste, se necessário

Use um usuário já existente no banco.

Se não souber nenhum acesso e estiver usando uma base local de teste, crie um administrador temporário.

No VSCode, abra um segundo terminal:

1. Clique em `Terminal`.
2. Clique em `New Terminal`.

Rode:

```bash
php -r '$pdo=new PDO("sqlite:database/secretaria.db"); $hash=password_hash("admin123", PASSWORD_DEFAULT); $pdo->prepare("INSERT INTO usuarios (nome,email,senha,tipo,recebe_avisos_email) VALUES (?,?,?,?,?)")->execute(["Administrador Teste","admin@teste.com",$hash,"admin",1]); echo "Usuário criado: admin@teste.com / admin123\n";'
```

Depois acesse:

```text
E-mail: admin@teste.com
Senha: admin123
```

Use esse usuário apenas no ambiente local.

## 12. Checklist de teste no navegador

Depois de entrar:

1. Abra o painel inicial.
2. Confira se o menu lateral aparece.
3. Abra a tela de alunos.
4. Cadastre e edite um aluno.
5. Abra contratos/pedidos.
6. Marque `Pedido faturado`.
7. Tente salvar sem `Data do faturamento` e confira se o sistema bloqueia.
8. Informe a data e salve.
9. Confira se o contrato faturado fica destacado na listagem.
10. Abra usuários como administrador.
11. Marque e desmarque `Receber avisos por e-mail`.
12. Abra certidões, agenda, relatórios e passivo.
13. Salve algum registro e confira se aparece mensagem de sucesso.

## 13. Testar os avisos por e-mail

Atenção: os crons podem enviar e-mails reais se o SMTP estiver configurado.

No terminal do VSCode:

```bash
php public/cron_dva.php
php public/cron_certidao.php
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

## 14. Problemas comuns

### O comando `php` não é encontrado

O PHP não está instalado ou não está no `PATH`.

Em Ubuntu/Debian:

```bash
sudo apt install php php-cli
```

Depois feche e abra o terminal do VSCode novamente.

### Aparece `could not find driver`

O PHP está sem SQLite habilitado.

Confira:

```bash
php -m | grep -i sqlite
```

Em Ubuntu/Debian, instale:

```bash
sudo apt install php-sqlite3
```

### O navegador mostra erro de banco

Confira se o `.env` existe na raiz da pasta `sistema_escolar`.

Confira se o `DB_PATH` aponta para o arquivo correto:

```bash
realpath database/secretaria.db
```

### Permissão negada no banco

Rode:

```bash
chmod u+rw database/secretaria.db
chmod u+rwx database
```

### A porta 8000 já está em uso

Pare o servidor antigo com `Ctrl + C`, ou use outra porta:

```bash
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
