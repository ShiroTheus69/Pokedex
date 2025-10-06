CREATE DATABASE pokedex;

USE pokedex;

CREATE TABLE favoritos (   
    id INT AUTO_INCREMENT PRIMARY KEY,   
    usuario_id INT NOT NULL,   
    pokemon_nome VARCHAR(100) NOT NULL,   
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) );

CREATE TABLE usuarios (     
    id INT AUTO_INCREMENT PRIMARY KEY,     
    usuario VARCHAR(50) NOT NULL UNIQUE,     
    senha VARCHAR(255) NOT NULL );