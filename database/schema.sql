-- database/schema.sql
CREATE TABLE IF NOT EXISTS study_programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) UNIQUE,
  password VARCHAR(255),
  role ENUM('admin','peserta') NOT NULL DEFAULT 'peserta',
  category ENUM('mahasiswa','dosen','tendik','umum') DEFAULT 'mahasiswa',
  identity_type ENUM('NIM','NIP','NIK') DEFAULT 'NIM',
  identity_number VARCHAR(64) UNIQUE,
  study_program_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (study_program_id) REFERENCES study_programs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code CHAR(26) NOT NULL UNIQUE,  -- ULID/UUID ringkas
  title VARCHAR(150) NOT NULL,
  description TEXT,
  location VARCHAR(150),
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_tokens (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  token CHAR(36) NOT NULL UNIQUE,  -- UUID v4
  expires_at DATETIME NOT NULL,
  used_by INT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id),
  INDEX (event_id), INDEX (expires_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendances (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT NOT NULL,
  checkin_at DATETIME NOT NULL,
  method ENUM('qr','manual') DEFAULT 'qr',
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  UNIQUE KEY uniq_event_user (event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX (checkin_at)
) ENGINE=InnoDB;
