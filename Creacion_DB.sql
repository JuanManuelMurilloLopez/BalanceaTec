CREATE DATABASE iot_reto;
USE iot_reto;

CREATE TABLE user_account(
	user_ID INT PRIMARY KEY AUTO_INCREMENT,
    user_name VARCHAR(50),
    password VARCHAR(80),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE device(
	device_ID VARCHAR(20) PRIMARY KEY,
    device_name VARCHAR(50),
    user_ID INT,
    FOREIGN KEY(user_ID) REFERENCES user_account(user_ID),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE temperature(
	temperature_ID INT PRIMARY KEY AUTO_INCREMENT,
    temperature_value FLOAT,
    device_ID VARCHAR(20),
    FOREIGN KEY(device_ID) REFERENCES device(device_ID),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE humidity(
	humidity_ID INT PRIMARY KEY AUTO_INCREMENT,
    humidity_value FLOAT,
    device_ID VARCHAR(20),
    FOREIGN KEY(device_ID) REFERENCES device(device_ID),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE aceleration(
	aceleration_ID INT PRIMARY KEY AUTO_INCREMENT,
    in_x FLOAT,
    in_y FLOAT,
    in_z FLOAT,
    device_ID VARCHAR(20),
    FOREIGN KEY(device_ID) REFERENCES device(device_ID),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE rotation(
	rotation_ID INT PRIMARY KEY AUTO_INCREMENT,
    in_x FLOAT,
    in_y FLOAT,
    in_z FLOAT,
    device_ID VARCHAR(20),
    FOREIGN KEY(device_ID) REFERENCES device(device_ID),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE limit_value(
    limit_ID INT PRIMARY KEY AUTO_INCREMENT,
    max_temperature FLOAT,
    min_temperature FLOAT,
    max_humidity FLOAT,
    min_humidity FLOAT,
    rotation_tolerance FLOAT,
    aceleration_tolerance FLOAT,
    device_ID VARCHAR(20),
    FOREIGN KEY(device_ID) REFERENCES device(device_ID),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


