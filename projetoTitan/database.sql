
-- Projeto vaga Titan

CREATE DATABASE IF NOT EXISTS projeto_titan
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE projeto_titan;

CREATE TABLE IF NOT EXISTS `user` (
    `id_user`    BIGINT(20)    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150)  NOT NULL,
    `email`      VARCHAR(100)  NOT NULL UNIQUE,
    `password`   VARCHAR(45)   NOT NULL,         
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `ativo`      TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `service` (
    `id_service`       BIGINT(20)     NOT NULL AUTO_INCREMENT,
    `description`      VARCHAR(45)    NOT NULL,
    `price`            DECIMAL(11,2)  NOT NULL,
    `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `finished_at`      DATETIME                DEFAULT NULL,
    `commission_user`  DECIMAL(11,2)           DEFAULT NULL,
    `user_id_user`     BIGINT(20)     NOT NULL,
    PRIMARY KEY (`id_service`),
    CONSTRAINT `fk_service_user`
        FOREIGN KEY (`user_id_user`) REFERENCES `user` (`id_user`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Popular com alguns dados
INSERT INTO `user` (`name`, `email`, `password`) VALUES
    ('José Rossi', 'jose@teste1.com', MD5('123456')),
    ('Maria Cristina', 'maria@teste2.com.br', MD5('12345678'));

INSERT INTO `service` (`description`, `price`, `user_id_user`, `finished_at`, `commission_user`) VALUES
    ('Troca de Tela do Notebook', 425.00, 1, NULL, NULL),
    ('Conserto do carregador',     100.00, 1, NOW(), 5.00),
    ('Instalação do Office',  200.00, 2, NULL, NULL),
    ('Reparo do Sistema', 350.00, 2, NULL, NULL);
