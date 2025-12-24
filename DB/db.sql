-- Önce çocuk tabloları siliyoruz (Sıralama önemli)
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

-- Şimdi users tablosunu tertemiz oluşturuyoruz
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'organizer', 'user') DEFAULT 'user',
    is_approved TINYINT(1) DEFAULT 1,
    organizer_request VARCHAR(50) DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Etkinlikler tablosunu tekrar oluşturuyoruz (Silindiği için geri getirmeliyiz)
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    artist_name VARCHAR(100) NOT NULL,
    `desc` TEXT, -- description reserved word olabilir diye backtick
    date DATE NOT NULL,
    location VARCHAR(150) NOT NULL,
    image VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Kayıtlar tablosunu tekrar oluşturuyoruz
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- Test kullanıcısı
INSERT INTO users (username, email, password, role, is_approved) 
VALUES ('TestOrganizer', 'test@test.com', '$2y$10$abcdefghijklmnopqrstuvwx', 'organizer', 1);