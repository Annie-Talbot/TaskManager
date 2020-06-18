create DATABASE TaskManager;

CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    salt VARCHAR(255) NOT NULL
);


create TABLE tasks(
	id INT NOT NULL AUTO_INCREMENT,
    userID INT NOT NULL,
    title VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    status BOOLEAN NOT NULL,
    description VARCHAR(200),
    externalID INT,

    PRIMARY KEY (id),
    FOREIGN KEY (userID) REFERENCES users(id)
)