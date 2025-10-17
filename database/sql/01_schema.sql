-- database/sql/01_schema.sql
-- MySQL/MariaDB 10.x — juego de caracteres moderno
DROP DATABASE IF EXISTS universidad_social;
CREATE DATABASE universidad_social CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE universidad_social;

-- =========================
-- USUARIOS (rol obligatorio)
-- =========================
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('estudiante','profesor','organizacion','administrador') NOT NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- =========================
-- SESIONES (Laravel)
-- =========================
CREATE TABLE sessions (
  id VARCHAR(255) PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity),
  CONSTRAINT fk_sessions_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- RESET DE CONTRASEÑAS
-- =========================
CREATE TABLE password_reset_tokens (
  email VARCHAR(255) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- =========================
-- CACHE (para RateLimiter si usan CACHE_STORE=database)
-- =========================
CREATE TABLE cache (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int unsigned NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int unsigned NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- ORGANIZACIONES (metadatos)
-- =========================
CREATE TABLE organizations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,             -- dueño/encargado con rol 'organizacion'
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NULL,
  contact_email VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY uq_org_user (user_id),
  CONSTRAINT fk_org_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- ACTIVIDADES (incluye PARCHE)
-- =========================
CREATE TABLE activities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  place VARCHAR(255) NOT NULL,
  start_date DATE NOT NULL,
  start_time TIME NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,         -- admin que la publica
  organization_user_id BIGINT UNSIGNED NULL,   -- (opcional) organización dueña
  status ENUM('borrador','publicada','cerrada','cancelada','finalizada') NOT NULL DEFAULT 'borrador',
  attendance_enabled TINYINT(1) NOT NULL DEFAULT 0, -- habilitar “Tomar lista”
  social_hours INT UNSIGNED NOT NULL DEFAULT 1,      -- horas a otorgar
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  KEY idx_act_status (status),
  CONSTRAINT fk_act_created_by FOREIGN KEY (created_by) REFERENCES users(id),
  CONSTRAINT fk_act_org_user FOREIGN KEY (organization_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =========================
-- POSTULACIONES (NUEVO: description + activity_id)
-- =========================
CREATE TABLE activity_proposals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  proposer_user_id BIGINT UNSIGNED NOT NULL,    -- profesor u organización
  proposer_role ENUM('profesor','organizacion') NOT NULL,
  place VARCHAR(255) NOT NULL,
  event_date DATE NOT NULL,
  participants_count INT UNSIGNED NOT NULL,
  work_type VARCHAR(120) NOT NULL,              -- “tipo de trabajo social”
  description TEXT NOT NULL,                    -- <<< NUEVO: descripción detallada de la propuesta
  permits TEXT NULL,
  manager_data JSON NULL,                       -- nombre, tel, etc.
  signature_path VARCHAR(255) NULL,             -- ruta documento del encargado (PDF/JPG/PNG)
  status ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  activity_id BIGINT UNSIGNED NULL,             -- vínculo a actividad creada (si aprobada)
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  KEY idx_ap_status (status),
  CONSTRAINT fk_ap_user     FOREIGN KEY (proposer_user_id) REFERENCES users(id),
  CONSTRAINT fk_prop_act    FOREIGN KEY (activity_id)      REFERENCES activities(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- INSCRIPCIONES DE ESTUDIANTES
-- =========================
CREATE TABLE activity_registrations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,          -- rol 'estudiante'
  receipt_path VARCHAR(255) NOT NULL,           -- archivo en storage/app/public/receipts
  status ENUM('pendiente','aceptado','rechazado') NOT NULL DEFAULT 'pendiente',
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY uq_reg_unique (activity_id, student_id),
  CONSTRAINT fk_reg_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  CONSTRAINT fk_reg_student  FOREIGN KEY (student_id)  REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- LISTAS DE ASISTENCIA
-- =========================
CREATE TABLE attendance_lists (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id BIGINT UNSIGNED NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,          -- admin DSSU
  status ENUM('borrador','compartida','enviada','cerrada') NOT NULL DEFAULT 'borrador',
  shared_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_attl_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  CONSTRAINT fk_attl_creator  FOREIGN KEY (created_by)  REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE attendance_entries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attendance_list_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  attended TINYINT(1) NOT NULL DEFAULT 0,
  marked_at TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY uq_att_entry (attendance_list_id, student_id),
  CONSTRAINT fk_ae_list    FOREIGN KEY (attendance_list_id) REFERENCES attendance_lists(id) ON DELETE CASCADE,
  CONSTRAINT fk_ae_student FOREIGN KEY (student_id)        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Firma del encargado (AHORA admite texto)
CREATE TABLE org_signatures (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attendance_list_id BIGINT UNSIGNED NOT NULL,
  organization_user_id BIGINT UNSIGNED NOT NULL, -- rol 'organizacion' o 'profesor'
  signature_path VARCHAR(255) NULL,              -- opcional: si se subiera archivo
  signature_text VARCHAR(255) NULL,              -- NUEVO: firma como texto
  signed_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_sig_list FOREIGN KEY (attendance_list_id)  REFERENCES attendance_lists(id) ON DELETE CASCADE,
  CONSTRAINT fk_sig_org  FOREIGN KEY (organization_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- HORAS (PARCHE: activity_id + unique)
-- =========================
CREATE TABLE hours_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  hours_type ENUM('servicio','voluntariado') NOT NULL,
  activity_id BIGINT UNSIGNED NULL,             -- evitar duplicar horas por actividad
  hours INT UNSIGNED NOT NULL,
  added_by BIGINT UNSIGNED NOT NULL,           -- admin
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_hours_unique (student_id, activity_id, hours_type),
  CONSTRAINT fk_hl_stu   FOREIGN KEY (student_id)  REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_hl_admin FOREIGN KEY (added_by)    REFERENCES users(id),
  CONSTRAINT fk_hl_act   FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- (Opcional) JOBS si alguien usa cola en BD
-- =========================
CREATE TABLE IF NOT EXISTS jobs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  queue varchar(255) NOT NULL,
  payload longtext NOT NULL,
  attempts tinyint unsigned NOT NULL,
  reserved_at int unsigned DEFAULT NULL,
  available_at int unsigned NOT NULL,
  created_at int unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS failed_jobs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  uuid varchar(255) NOT NULL,
  connection text NOT NULL,
  queue text NOT NULL,
  payload longtext NOT NULL,
  exception longtext NOT NULL,
  failed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
