-- DROP DATABASE `my_pastebin`;
-- DROP TABLE `my_pastebin`.`users`;
-- DROP TABLE `my_pastebin`.`pastes`;

-- Create main database
CREATE DATABASE `my_pastebin`;

-- Create users table
CREATE TABLE `my_pastebin`.`users` (
    `id` INT NOT NULL AUTO_INCREMENT , 
    `timestamp` TIMESTAMP NOT NULL , 
    `username` VARCHAR(10) NOT NULL , 
    `password` VARCHAR(60) NOT NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- Create pastes table
CREATE TABLE `my_pastebin`.`pastes` (
    `id` INT NOT NULL AUTO_INCREMENT , 
    `creator_id` INT NOT NULL , 
    `title` VARCHAR(30) NOT NULL , 
    `content` MEDIUMTEXT NOT NULL , 
    `visibility` VARCHAR(10) NOT NULL , 
    `expires` BOOLEAN NOT NULL , 
    `expiration_date` DATE NULL , 
    `haspassword` BOOLEAN NOT NULL , 
    `password` VARCHAR(60) NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;