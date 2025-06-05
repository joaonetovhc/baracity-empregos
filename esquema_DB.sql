create database baracity
use baracity

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('admin', 'empresa', 'candidato') NOT NULL,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vagas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_empresa INT NOT NULL,
  titulo VARCHAR(100) NOT NULL,
  descricao TEXT NOT NULL,
  requisitos TEXT,
  data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_empresa) REFERENCES usuarios(id)
);

CREATE TABLE candidaturas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_candidato INT NOT NULL,
  id_vaga INT NOT NULL,
  curriculo TEXT, -- pode ser caminho do arquivo ou texto
  data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_candidato) REFERENCES usuarios(id),
  FOREIGN KEY (id_vaga) REFERENCES vagas(id)
);


