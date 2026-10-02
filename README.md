# 🔌 Task API — REST em PHP

API RESTful criada com **PHP 8+ e MySQL** para demonstrar CRUD, autenticação simples por API key, validação de payloads e respostas JSON.

## Endpoints
| Método | Endpoint | Ação |
|---|---|---|
| GET | `/tasks` | Lista tarefas |
| GET | `/tasks/{id}` | Busca uma tarefa |
| POST | `/tasks` | Cria tarefa |
| PUT | `/tasks/{id}` | Atualiza tarefa |
| DELETE | `/tasks/{id}` | Remove tarefa |
| GET | `/tasks?status=doing` | Filtra por status |

## Exemplo
```bash
curl -H "X-API-Key: dev-key-change-me" http://localhost:8001/tasks
```

## Stack
PHP • REST • JSON • MySQL • PDO • Postman

## Executar
1. Copie `.env.example` para `.env` e ajuste as credenciais.
2. Importe `database/schema.sql`.
3. Rode `php -S localhost:8001 -t public`.
4. Importe `docs/postman-collection.json` no Postman.

### Ideias para evoluir
JWT/OAuth2 • paginação • testes automatizados • OpenAPI/Swagger • Docker • rate limiting.

### Health check
`GET /health.php` retorna o estado básico da aplicação sem exigir API key.
