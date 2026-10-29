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