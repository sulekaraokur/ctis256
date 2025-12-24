
CREATE DATABASE IF NOT EXISTS concert_db;
USE concert_db;


CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL, /*email ve bunu değiştirdim*/
  email VARCHAR(50) UNIQUE NOT NULL, 
  password VARCHAR(255),
   role ENUM('admin','organizer','user') DEFAULT 'user', /*VARCHAR(15), --DEFAULT "user"*/
   status VARCHAR(15),
  organizer_request ENUM('none','pending','approved','rejected') DEFAULT 'none',
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
   UNIQUE (user_id, event_id), /*değişiklik*/
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (event_id) REFERENCES events(event_id)
);


