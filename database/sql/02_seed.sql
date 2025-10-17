-- database/sql/02_seed.sql
USE universidad_social;

-- Estado consistente para todos
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE hours_logs;
TRUNCATE TABLE org_signatures;
TRUNCATE TABLE attendance_entries;
TRUNCATE TABLE attendance_lists;
TRUNCATE TABLE activity_registrations;
TRUNCATE TABLE activity_proposals;
TRUNCATE TABLE activities;
TRUNCATE TABLE organizations;
TRUNCATE TABLE sessions;
TRUNCATE TABLE cache;
TRUNCATE TABLE cache_locks;
TRUNCATE TABLE password_reset_tokens;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS=1;

-- Hash bcrypt conocido de Laravel para 'password'
SET @HASH := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Usuarios base
INSERT INTO users (name,email,password,role,created_at,updated_at) VALUES
('Admin DSSU','admin@dssu.test',@HASH,'administrador',NOW(),NOW()),
('Profe Ana','ana.prof@utp.test',@HASH,'profesor',NOW(),NOW()),
('ONG Manos','contacto@manos.test',@HASH,'organizacion',NOW(),NOW()),
('Est. Luis','luis.est@utp.test',@HASH,'estudiante',NOW(),NOW());

-- Más estudiantes
INSERT INTO users (name,email,password,role,created_at,updated_at) VALUES
('Est. A1','alumno01@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A2','alumno02@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A3','alumno03@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A4','alumno04@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A5','alumno05@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A6','alumno06@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A7','alumno07@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A8','alumno08@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A9','alumno09@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A10','alumno10@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A11','alumno11@utp.test',@HASH,'estudiante',NOW(),NOW()),
('Est. A12','alumno12@utp.test',@HASH,'estudiante',NOW(),NOW());

-- Organización ligada al usuario 'organizacion'
INSERT INTO organizations (user_id,name,phone,contact_email,created_at,updated_at) VALUES
((SELECT id FROM users WHERE email='contacto@manos.test'),'Fundación Manos','6000-0000','contacto@manos.test',NOW(),NOW());

-- Actividad publicada (visible a estudiantes)
INSERT INTO activities (title,description,place,start_date,start_time,created_by,organization_user_id,status,attendance_enabled,social_hours,created_at,updated_at)
VALUES
('Jornada de Reforestación',
 'Apoyo a reforestación en área protegida',
 'Parque Metropolitano','2025-11-15','08:00:00',
 (SELECT id FROM users WHERE email='admin@dssu.test'),
 (SELECT id FROM users WHERE email='contacto@manos.test'),
 'publicada', 0, 4, NOW(), NOW());

-- Postulación de ejemplo (profesor) PENDIENTE
INSERT INTO activity_proposals (
  proposer_user_id, proposer_role, place, event_date, participants_count, work_type, description, permits, manager_data, signature_path, status, activity_id, created_at, updated_at
) VALUES (
  (SELECT id FROM users WHERE email='ana.prof@utp.test'),
  'profesor',
  'Aula 201',
  '2025-11-20',
  35,
  'Tutorías de matemática',
  'Se brindarán tutorías personalizadas de álgebra y cálculo a estudiantes de primer año, con evaluación diagnóstica inicial.',
  'N/A',
  JSON_OBJECT('encargado','Ana Prof','telefono','6000-1111'),
  NULL,
  'pendiente',
  NULL,
  NOW(), NOW()
);

-- Postulación de ejemplo (organización) PENDIENTE
INSERT INTO activity_proposals (
  proposer_user_id, proposer_role, place, event_date, participants_count, work_type, description, permits, manager_data, signature_path, status, activity_id, created_at, updated_at
) VALUES (
  (SELECT id FROM users WHERE email='contacto@manos.test'),
  'organizacion',
  'Comedor social San Pedro',
  '2025-12-02',
  20,
  'Apoyo en comedor social',
  'Preparación y distribución de alimentos a población vulnerable; incluye inducción en buenas prácticas de manipulación de alimentos.',
  'Permiso municipal',
  JSON_OBJECT('encargado','María Pérez','telefono','6000-2222'),
  NULL,
  'pendiente',
  NULL,
  NOW(), NOW()
);

-- Inscripción de ejemplo
INSERT INTO activity_registrations (activity_id,student_id,receipt_path,status,created_at,updated_at)
VALUES (
  (SELECT id FROM activities WHERE title='Jornada de Reforestación'),
  (SELECT id FROM users WHERE email='luis.est@utp.test'),
  'receipts/ejemplo_recibo.pdf','pendiente',NOW(),NOW()
);
