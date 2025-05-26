-- Создание таблицы пользователей для тестирования EasyORM
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    age INT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Вставка тестовых данных
INSERT INTO users (name, email, age, city, status) VALUES
('Тимур Ганиуллин', 'timur@example.com', 25, 'Казань', 'active'),
('Иван Иванов', 'ivan@example.com', 30, 'Москва', 'active'),
('Мария Петрова', 'maria@example.com', 22, 'СПб', 'inactive'),
('Алексей Сидоров', 'alexey@example.com', 28, 'Москва', 'active'),
('Елена Козлова', 'elena@example.com', 35, 'Казань', 'active');