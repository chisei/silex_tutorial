CREATE TABLE member (
    id integer PRIMARY KEY AUTO_INCREMENT,
    email varchar(255) NOT NULL UNIQUE KEY,
    password varchar(64) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME
) ENGINE=InnoDB;
