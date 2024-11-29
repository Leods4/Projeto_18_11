CREATE DATABASE IF NOT EXISTS projeto;
USE projeto;

CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `tipo` ENUM('comum', 'administrador') NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` DATETIME DEFAULT current_timestamp(),
  `foto_perfil` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE veiculos (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `placa` VARCHAR(20) NOT NULL,
    `marca` VARCHAR(20) NOT NULL,
    `modelo` VARCHAR(255) NULL,
    `cor` VARCHAR(20) NOT NULL,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE `vagas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero` INT NOT NULL,
  `status` ENUM('disponivel', 'ocupado') DEFAULT 'disponivel'
);

CREATE TABLE `reservas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `vaga_id` INT NOT NULL,
  `veiculo_id` INT DEFAULT NULL,
  `data_reserva` DATETIME DEFAULT current_timestamp(),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`vaga_id`) REFERENCES `vagas` (`id`),
  FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL
);
