-- tbl users
-------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    pin VARCHAR(10) NOT NULL,
    role VARCHAR(100) NOT NULL DEFAULT "",
    _creator VARCHAR(100) NOT NULL,
    _regdate DATE DEFAULT CURRENT_DATE,
    _regtime TIME DEFAULT CURRENT_TIME,
    _modifyuser VARCHAR(100) DEFAULT '',
    _modifydate DATE DEFAULT NULL,
    _modifytime TIME DEFAULT NULL
    -- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

------------------------
CREATE TABLE societies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accesscode INT, 
    name VARCHAR(255) NOT NULL,
    addr VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE wings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controlid INT,
    w_name VARCHAR(50) NOT NULL,
    no_floor INT NOT NULL,
    flat_per_floor INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- CREATE TABLE wings (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     society_id INT,
--     wing_name VARCHAR(50) NOT NULL,
--     no_of_floor INT NOT NULL,
--     flat_per_floor INT NOT NULL,
--     FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE CASCADE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );


CREATE TABLE flats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controlid INT,
    parcontrolid INT,
    flat_no VARCHAR(10) NOT NULL,
    isactive VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- CREATE TABLE flats (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     wing_id INT,
--     flat_no VARCHAR(10) NOT NULL,
--     active VARCHAR(100) NOT NULL,
--     FOREIGN KEY (wing_id) REFERENCES wings(id) ON DELETE CASCADE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );


CREATE TABLE admin_s (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accesscode INT, 
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    pin VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_w (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controlid INT, 
    accesscode INT, 
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    pin VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE member (
    id INT AUTO_INCREMENT PRIMARY KEY,
    controlid INT, 
    parcontrolid INT, 
    parcontrolid INT, 
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    pin VARCHAR(10) NOT NULL,
    isapproved VARCHAR(100) NOT NULL,
    flat_lnkid INT VARCHAR(255) DEFAULT '', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);