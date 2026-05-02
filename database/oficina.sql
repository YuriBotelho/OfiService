-- =============================================================
-- OfiService — Banco de Dados
-- Importe este arquivo no phpMyAdmin ou via MySQL CLI:
--   mysql -u root -p < database/oficina.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS oficina_os
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE oficina_os;

-- -------------------------------------------------------------
-- Usuários do sistema
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil     ENUM('admin','mecanico','atendente') DEFAULT 'mecanico',
  telefone   VARCHAR(20),
  status     ENUM('ativo','inativo') DEFAULT 'ativo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Clientes
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(100) NOT NULL,
  cpf_cnpj   VARCHAR(20),
  telefone   VARCHAR(20),
  email      VARCHAR(100),
  endereco   VARCHAR(200),
  data_nasc  DATE,
  obs        TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Veículos (vinculados a clientes)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS veiculos (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  modelo     VARCHAR(100) NOT NULL,
  placa      VARCHAR(10),
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Ordens de Serviço
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ordens_servico (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  numero          VARCHAR(20) NOT NULL UNIQUE,
  status          ENUM('aberta','andamento','concluida','cancelada') DEFAULT 'aberta',
  cliente_id      INT,
  veiculo_modelo  VARCHAR(100),
  veiculo_placa   VARCHAR(10),
  responsavel_id  INT,
  atendente_id    INT,
  data_abertura   DATE NOT NULL,
  data_entrega    DATE,
  km_atual        INT,
  obs             TEXT,
  desconto        DECIMAL(10,2) DEFAULT 0.00,
  total           DECIMAL(10,2) DEFAULT 0.00,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id)     REFERENCES clientes(id)  ON DELETE SET NULL,
  FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)  ON DELETE SET NULL,
  FOREIGN KEY (atendente_id)   REFERENCES usuarios(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Itens de cada OS (serviços e peças)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS servicos_os (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  os_id          INT NOT NULL,
  descricao      VARCHAR(200) NOT NULL,
  tipo           ENUM('servico','peca') DEFAULT 'servico',
  quantidade     DECIMAL(10,2) DEFAULT 1.00,
  valor_unitario DECIMAL(10,2) DEFAULT 0.00,
  FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Configurações da empresa (chave → valor)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracoes (
  chave VARCHAR(50) PRIMARY KEY,
  valor TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Valores padrão
INSERT IGNORE INTO configuracoes (chave, valor) VALUES
  ('nome',        'Auto Center São Paulo'),
  ('cnpj',        '12.345.678/0001-99'),
  ('endereco',    'Rua das Oficinas, 200 – Centro'),
  ('cidade',      'São Paulo'),
  ('estado',      'SP'),
  ('telefone',    '(11) 9 8888-7777'),
  ('email',       'contato@oficinasaopaulo.com.br'),
  ('prefixo_os',  'OS-2024-'),
  ('proximo_os',  '1'),
  ('mensagem_os', 'Agradecemos pela preferência! Garantia de 90 dias para os serviços realizados.');

-- -------------------------------------------------------------
-- Dados de exemplo (clientes + veículos)
-- Execute database/criar_admin.php para criar o usuário admin
-- -------------------------------------------------------------
INSERT IGNORE INTO clientes (id, nome, cpf_cnpj, telefone, email, endereco) VALUES
  (1, 'Carlos Menezes',    '123.456.789-00', '(11) 9 8765-4321', 'carlos@email.com',   'Rua A, 100 – Centro'),
  (2, 'Fernanda Lima',     '987.654.321-00', '(11) 9 1111-2222', 'fernanda@email.com', 'Av. B, 200 – Jardins'),
  (3, 'Rodrigo Santos',    '111.222.333-44', '(11) 9 3333-4444', 'rodrigo@email.com',  'Rua C, 300 – Vila Nova'),
  (4, 'Ana Carvalho',      '555.666.777-88', '(11) 9 5555-6666', 'ana@email.com',      'Al. D, 400 – Morumbi'),
  (5, 'Marcos Souza',      '999.888.777-66', '(11) 9 7777-8888', 'marcos@email.com',   'Rua E, 500 – Lapa');

INSERT IGNORE INTO veiculos (cliente_id, modelo, placa) VALUES
  (1, 'Honda Civic 2019',      'ABC-1D23'),
  (2, 'VW Gol 2015',           'XYZ-9K88'),
  (3, 'Fiat Strada 2022',      'DEF-4R77'),
  (4, 'Renault Kwid 2020',     'GHI-5M11'),
  (5, 'Toyota Corolla 2021',   'JKL-2N44');

-- Nota: as OS de exemplo são inseridas por criar_admin.php
-- após criação dos usuários (necessário para as FKs)
