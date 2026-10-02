CREATE DATABASE IF NOT EXISTS task_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_api;

CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status ENUM('todo','doing','done') NOT NULL DEFAULT 'todo',
    due_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tasks_status (status),
    INDEX idx_tasks_priority (priority)
);

INSERT INTO tasks (title, description, priority, status, due_date) VALUES
('Documentar integração', 'Registrar endpoints e payloads usados na integração.', 'high', 'doing', DATE_ADD(CURDATE(), INTERVAL 3 DAY)),
('Criar tela inicial', 'Construir dashboard com indicadores.', 'medium', 'todo', DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
('Revisar validações', 'Revisar entradas e mensagens de erro.', 'low', 'done', CURDATE());
