
CREATE DATABASE IF NOT EXISTS concert_db;
USE concert_db;


CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name_surname VARCHAR(100),
  email VARCHAR(50),
  password VARCHAR(255),
  role VARCHAR(15),
  status VARCHAR(15),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE events (
  event_id INT AUTO_INCREMENT PRIMARY KEY,
  organizer_id INT,
  title VARCHAR(80),
  artist_name VARCHAR(50),
  `desc` VARCHAR(200),
  date DATE,
  location VARCHAR(150),
  image VARCHAR(200),
  status VARCHAR(15),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(user_id)
);

CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  event_id INT,
  reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (event_id) REFERENCES events(event_id)
);


