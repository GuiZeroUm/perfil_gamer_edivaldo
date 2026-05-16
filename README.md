# Perfil Gamer — Microsserviço de Perfil (GameVerse)

## Link do repositório

https://git.juancjc.com.br/TURMA-SD/Perfil-Gamer.git

---

## Nome do microsserviço

**Perfil Gamer** (gestão de perfis de jogadores).

---

## Integrantes

> **Ação do grupo:** substitua a linha abaixo pelos nomes completos de todos os integrantes (conforme exigência da disciplina).

- *(Nome 1, Nome 2, …)*

---

## Descrição do serviço

O **Perfil Gamer** é um microsserviço REST que armazena e expõe dados de perfil de jogador vinculados ao identificador **`user_id`** do ecossistema GameVerse (tipicamente o mesmo ID emitido pelo serviço de **Autenticação** após cadastro ou login). Inclui apelido (**nickname**), foto (**avatar**), biografia, país, plataformas favoritas e jogos favoritos, em formato JSON.

O serviço **não** implementa login nem emissão de tokens para as rotas de perfil descritas aqui; ele assume que quem chama a API já possui um `user_id` válido no contexto do sistema.

---

## Responsabilidades do microsserviço

| Responsabilidade | Detalhe |
|------------------|---------|
| Criar perfil | Registrar perfil associado a um `user_id` único, com validação de unicidade de `nickname`. |
| Consultar perfis | Listar todos os perfis ou obter um perfil por `user_id`. |
| Atualizar perfil | Alterar dados permitidos; opcionalmente substituir avatar (arquivo enviado em multipart). |
| Remover perfil | Excluir registro do perfil para um dado `user_id`. |
| Armazenamento de avatar | Salvar imagem em disco público (`storage/app/public/avatars`) e persistir **URL absoluta** no banco (depende de `APP_URL` correto). |

---

## O que o serviço faz / dados de entrada e saída / integrações

### O que o serviço faz

Gerencia o **CRUD de perfis de jogador** exposto em JSON pela API prefixada em `/api`.

### Quais dados recebe

| Contexto | Dados |
|----------|--------|
| **POST** `/api/profiles` | `user_id` (obrigatório, único), `nickname` (obrigatório, único), `avatar` (opcional, arquivo imagem jpg/jpeg/png, máx. 2 MB), `bio`, `country`, `platforms` (array), `games` (array). Envio com arquivo: **multipart/form-data**. |
| **PUT/PATCH** `/api/profiles/{user_id}` | `nickname`, `avatar` (arquivo, mesmas regras), `bio`, `country`, `platforms`, `games`. Campos omitidos não são obrigatórios na atualização parcial conforme regras do Laravel + validação do controller. |

### Quais dados retorna

| Operação | Retorno típico |
|----------|----------------|
| **GET** lista | Array JSON de objetos perfil (campos do modelo + timestamps). |
| **GET** um perfil | Um objeto perfil. |
| **POST** criar | Objeto perfil criado, HTTP **201**. |
| **PUT/PATCH** | Objeto perfil atualizado. |
| **DELETE** | `{"message": "Perfil removido"}`. |

Estrutura lógica de um perfil na API (nomes em *snake_case*, como no modelo):

- `id`, `user_id`, `nickname`, `avatar` (URL ou `null`), `bio`, `country`, `platforms` (array ou `null`), `games` (array ou `null`), `created_at`, `updated_at`.

### Quais serviços consome

Nenhuma chamada HTTP a outro microsserviço está implementada neste repositório. A integração com **Autenticação** (e demais serviços) é **por contrato de dados**: o cliente deve enviar um `user_id` coerente com o cadastro de usuário no GameVerse.

### Quais serviços utilizam esta API

Qualquer cliente autorizado no ecossistema que precise exibir ou editar perfil público do jogador, por exemplo:

- front-end do GameVerse;
- microsserviços de **catálogo**, **biblioteca**, **social** ou **notificações**, desde que obtenham ou propaguem o `user_id` e chamem este serviço.

### Como o microsserviço participa do fluxo geral do sistema (GameVerse)

Fluxo ilustrativo:

1. O usuário **autentica-se** no microsserviço de Autenticação e recebe identidade (ex.: `user_id`).
2. O cliente (ou BFF) **cria ou atualiza o perfil** neste serviço usando esse `user_id`.
3. Outras partes do sistema **consultam** `/api/profiles/{user_id}` para exibir nickname, avatar, bio, plataformas e jogos em telas de perfil, ranking ou comunidade.
4. Em remoção de conta ou fluxo administrativo, o cliente pode **deletar** o perfil aqui.

---

## Tecnologias utilizadas

| Camada | Tecnologia |
|--------|------------|
| Linguagem | PHP **^8.3** |
| Framework | **Laravel** **^13** |
| API | REST, JSON |
| ORM / persistência | Eloquent |
| Autenticação API (rota auxiliar) | Laravel **Sanctum** (rota `GET /api/user` com middleware; rotas de perfil não exigem Sanctum no código atual) |
| Banco (padrão do projeto) | **SQLite** (`database/database.sqlite`) |
| Front build (opcional para assets) | Vite, Tailwind (dependências npm do esqueleto Laravel) |

---

## Docker

Este repositório **não inclui** `Dockerfile` nem `docker-compose.yml` utilizáveis na raiz (há pasta `docker/` vazia). A execução documentada abaixo é **local, sem Docker**.

---

## Requisitos para rodar o projeto (sem Docker)

| Requisito | Versão / observação |
|-----------|---------------------|
| PHP | **8.3 ou superior** (extensões comuns Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`; **SQLite**: `pdo_sqlite`) |
| Composer | 2.x |
| Node.js + npm | Apenas se for compilar assets front (`npm run build` / `npm run dev`); **para só consumir a API REST**, não é obrigatório. |
| Banco de dados | **SQLite** (arquivo) no padrão deste `.env.example`, ou configure MySQL/MariaDB no `.env` se preferir. |

---

## Dependências obrigatórias

- PHP e Composer (instala pacotes PHP listados em `composer.json`).
- Arquivo SQLite criado em `database/database.sqlite` quando usar `DB_CONNECTION=sqlite` (veja passo a passo).

---

## Passo a passo de instalação

```bash
git clone https://git.juancjc.com.br/TURMA-SD/Perfil-Gamer.git
cd Perfil-Gamer
composer install
```

Copie o ambiente e gere a chave da aplicação:

```bash
cp .env.example .env
php artisan key:generate
```

Banco SQLite (padrão do exemplo):

```bash
touch database/database.sqlite
php artisan migrate
```

Link simbólico para avatares públicos:

```bash
php artisan storage:link
```

Instalação opcional de assets front:

```bash
npm install
```

---

## Configuração do `.env`

Principais variáveis:

| Variável | Descrição |
|----------|-----------|
| `APP_NAME` | Nome da aplicação. |
| `APP_ENV` | `local`, `production`, etc. |
| `APP_KEY` | Preenchido por `php artisan key:generate`. |
| `APP_DEBUG` | `true` em desenvolvimento; `false` em produção. |
| `APP_URL` | **URL base pública** do serviço (ex.: `http://127.0.0.1:8000`). Afeta URLs absolutas de `avatar`. |
| `DB_CONNECTION` | `sqlite` (padrão no `.env.example`) ou `mysql` / `mariadb`. |
| `DB_DATABASE` | Para SQLite, costuma apontar para `database/database.sqlite` (padrão do Laravel se não definido). |

Há também chaves para fila, sessão e cache em banco (`SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` no `.env.example`); as migrations padrão do Laravel cobrem tabelas auxiliares quando usar `database`.

Arquivo de referência no repositório: **`.env.example`**.

---

## Como executar o projeto

Somente API + servidor embutido do PHP:

```bash
php artisan serve
```

Por padrão: `http://127.0.0.1:8000`. A API de perfis fica em `http://127.0.0.1:8000/api/profiles`.

Ambiente completo com fila, logs e Vite (script Composer do projeto):

```bash
composer run dev
```

---

## Como testar o projeto

Testes automatizados (PHPUnit via Artisan):

```bash
composer test
```

Equivalente:

```bash
php artisan test
```

> Os testes em `tests/Feature` são exemplos genéricos; para a disciplina, recomenda-se adicionar testes de feature cobrindo as rotas de `ProfileController`.

Testes manuais rápidos: use **curl**, **Insomnia** ou **Postman** contra as rotas da seção seguinte. Para **criar/atualizar com avatar**, use **multipart/form-data**.

---

## Rotas da API

Prefixo global das rotas definidas em `routes/api.php`: **`/api`**.

| Método HTTP | Endpoint | Descrição |
|-------------|----------|-----------|
| GET | `/api/profiles` | Lista todos os perfis. |
| POST | `/api/profiles` | Cria um novo perfil (recomendado **multipart/form-data** se houver `avatar`). |
| GET | `/api/profiles/{user_id}` | Busca perfil pelo **`user_id`** (valor numérico na URL; o parâmetro de rota do Laravel pode aparecer como `{profile}` internamente, mas o significado é o `user_id`). |
| PUT | `/api/profiles/{user_id}` | Atualização completa dos campos enviados (validação no controller). |
| PATCH | `/api/profiles/{user_id}` | Atualização parcial (mesma action `update`). |
| DELETE | `/api/profiles/{user_id}` | Remove o perfil. |
| GET | `/api/user` | Retorna usuário autenticado; requer **`auth:sanctum`** (não usado pelas rotas CRUD de perfil acima). |

---

## Exemplos de requisição e resposta em JSON

### POST `/api/profiles` (somente JSON, sem arquivo de avatar)

**Content-Type:** `application/json`

**Requisição:**

```json
{
  "user_id": 1,
  "nickname": "PlayerOne",
  "bio": "Apaixonado por RPGs.",
  "country": "Brasil",
  "platforms": ["PC", "PlayStation"],
  "games": ["Elden Ring", "Hades"]
}
```

**Resposta (201):**

```json
{
  "user_id": 1,
  "nickname": "PlayerOne",
  "avatar": null,
  "bio": "Apaixonado por RPGs.",
  "country": "Brasil",
  "platforms": ["PC", "PlayStation"],
  "games": ["Elden Ring", "Hades"],
  "updated_at": "2026-05-11T12:00:00.000000Z",
  "created_at": "2026-05-11T12:00:00.000000Z",
  "id": 1
}
```

> Com **avatar**, envie `POST` como **multipart/form-data** com campos de texto + arquivo `avatar` (jpg/jpeg/png, máx. 2048 KB). O servidor grava a URL absoluta em `avatar`.

### GET `/api/profiles/1`

**Resposta (200):** um objeto no mesmo formato do exemplo acima (com `avatar` preenchido se existir).

### PUT `/api/profiles/1` (JSON, sem trocar avatar por arquivo)

**Requisição:**

```json
{
  "nickname": "PlayerOne_BR",
  "bio": "Bio atualizada.",
  "country": "Brasil",
  "platforms": ["PC"],
  "games": ["Hades", "Celeste"]
}
```

**Resposta (200):** objeto perfil atualizado.

### DELETE `/api/profiles/1`

**Resposta (200):**

```json
{
  "message": "Perfil removido"
}
```

---

## Possíveis erros e retornos esperados

| Situação | HTTP | Comportamento esperado |
|----------|------|----------------------|
| Perfil inexistente para o `user_id` | **404** | Modelo não encontrado (`firstOrFail`). Corpo padrão Laravel de erro em JSON (mensagem + contexto em modo debug). |
| Validação falhou (campos obrigatórios, tipos, duplicidade de `user_id` ou `nickname`, imagem inválida) | **422** | JSON com objeto `errors` por campo (resposta padrão de validação do Laravel). |
| Método HTTP não suportado na rota | **405** | Resposta de método não permitido. |
| Erro interno não tratado | **500** | Erro do servidor; em produção manter `APP_DEBUG=false` para não expor detalhes. |
| Serviço indisponível | *(infra)* | Timeout ou falha de conexão do cliente; este microsserviço não orquestra fallback. |

Exemplos de mensagens de negócio / validação que o integrador pode mapear:

- **Perfil não encontrado** — 404 ao buscar/atualizar/remover `user_id` inexistente.
- **Dados inválidos** — 422 (nickname já usado, `user_id` já possui perfil, arquivo não é imagem, etc.).
- **Usuário inexistente no Autenticação** — este serviço **não valida** contra Autenticação; evitar perfis órfãos é responsabilidade do fluxo que chama a API.

---

## Comandos resumidos (cópia rápida)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan serve
```

Testes:

```bash
composer test
```

---

## Arquivos relacionados à entrega acadêmica

| Entrega | Status no repositório |
|---------|------------------------|
| README.md | Este arquivo. |
| `.env.example` | Presente na raiz. |
| Dockerfile / docker-compose | **Não** incluídos para uso neste momento. |

Qualquer pessoa deve conseguir clonar o repositório, seguir **Requisitos**, **Instalação**, **`.env`** e **Como executar** para subir o microsserviço localmente.
