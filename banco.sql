CREATE DATABASE IF NOT EXISTS controle_labs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE controle_labs;

CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NULL,
    disciplina VARCHAR(100) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    turno ENUM('Manhã','Tarde','Noite') NOT NULL DEFAULT 'Manhã',
    qtd_alunos INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS laboratorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    capacidade INT NOT NULL DEFAULT 0,
    status ENUM('liberado','reservado') NOT NULL DEFAULT 'liberado'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    turma_id INT NOT NULL,
    laboratorio_id INT NOT NULL,
    inicio DATETIME NOT NULL,
    termino DATETIME NOT NULL,
    FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
    FOREIGN KEY (laboratorio_id) REFERENCES laboratorios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
