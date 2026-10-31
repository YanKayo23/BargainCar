-- BargainCar | Esquema inicial para MariaDB/MySQL
-- Execute apenas em banco vazio: o script recria as tabelas listadas.
-- As cidades serao importadas posteriormente via CSV.
-- A exclusao logica dos anuncios utiliza deleted_at.

CREATE DATABASE IF NOT EXISTS bargaincar
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bargaincar;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tokens_recuperacao_senha;
DROP TABLE IF EXISTS favoritos;
DROP TABLE IF EXISTS perguntas;
DROP TABLE IF EXISTS midias;
DROP TABLE IF EXISTS anuncios;
DROP TABLE IF EXISTS lojas;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS cidades;

SET FOREIGN_KEY_CHECKS = 1;

-- Tabela de cidades
CREATE TABLE cidades (
    codigo_ibge INT UNSIGNED NOT NULL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    UNIQUE KEY uq_cidades_nome_uf (nome, uf)
) ENGINE = InnoDB;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    cpf CHAR(11) NOT NULL UNIQUE,
    cidade_id INT UNSIGNED NOT NULL,
    contato_padrao VARCHAR(30) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    senha_hash VARCHAR(255) NOT NULL,

    CONSTRAINT fk_usuarios_cidades
        FOREIGN KEY (cidade_id)
        REFERENCES cidades(codigo_ibge)
) ENGINE = InnoDB;

-- Tabela de lojas
CREATE TABLE lojas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    cidade_id INT UNSIGNED NOT NULL,
    contato VARCHAR(30) NOT NULL,
    nome_empresa VARCHAR(150) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cnpj CHAR(14) UNIQUE,

    CONSTRAINT fk_lojas_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id),

    CONSTRAINT fk_lojas_cidades
        FOREIGN KEY (cidade_id)
        REFERENCES cidades(codigo_ibge)
) ENGINE = InnoDB;

-- Tabela de anúncios
CREATE TABLE anuncios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_anunciante ENUM('pessoa', 'loja')
        NOT NULL DEFAULT 'pessoa',
    usuario_id INT UNSIGNED NOT NULL,
    loja_id INT UNSIGNED NULL,
    cidade_id INT UNSIGNED NOT NULL,
    contato VARCHAR(30) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATETIME NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    ano SMALLINT UNSIGNED NOT NULL,
    preco DECIMAL(12, 2) NOT NULL,
    cor VARCHAR(50) NOT NULL,
    descricao TEXT NOT NULL,
    defeitos TEXT NULL,
    debitos_pendentes TEXT NULL,
    situacao_documentacao VARCHAR(100) NOT NULL,
    status ENUM('ativo', 'pendente', 'expirado')
        NOT NULL DEFAULT 'ativo',
    deleted_at DATETIME NULL,

    INDEX idx_anuncios_busca (status, deleted_at, cidade_id),
    INDEX idx_anuncios_expiracao (status, data_expiracao),
    INDEX idx_anuncios_usuario (usuario_id),

    CONSTRAINT fk_anuncios_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id),

    CONSTRAINT fk_anuncios_lojas
        FOREIGN KEY (loja_id)
        REFERENCES lojas(id),

    CONSTRAINT fk_anuncios_cidades
        FOREIGN KEY (cidade_id)
        REFERENCES cidades(codigo_ibge)
) ENGINE = InnoDB;

-- Tabela de mídias
CREATE TABLE midias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anuncio_id INT UNSIGNED NOT NULL,
    tipo ENUM('foto', 'video') NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    ordem INT UNSIGNED NOT NULL DEFAULT 1,

    INDEX idx_midias_anuncio_ordem (anuncio_id, ordem),

    CONSTRAINT fk_midias_anuncios
        FOREIGN KEY (anuncio_id)
        REFERENCES anuncios(id)
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- Tabela de perguntas
CREATE TABLE perguntas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anuncio_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    pergunta TEXT NOT NULL,
    resposta TEXT NULL,
    data_pergunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resposta DATETIME NULL,

    INDEX idx_perguntas_anuncio (anuncio_id),

    CONSTRAINT fk_perguntas_anuncios
        FOREIGN KEY (anuncio_id)
        REFERENCES anuncios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_perguntas_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- Tabela de favoritos
CREATE TABLE favoritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    anuncio_id INT UNSIGNED NOT NULL,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_favoritos_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_favoritos_anuncios
        FOREIGN KEY (anuncio_id)
        REFERENCES anuncios(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_favorito_usuario_anuncio
        UNIQUE (usuario_id, anuncio_id)
) ENGINE = InnoDB;

-- Tabela de recuperação de senha
CREATE TABLE tokens_recuperacao_senha (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATETIME NOT NULL,
    usado BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_recuperacao_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE = InnoDB;