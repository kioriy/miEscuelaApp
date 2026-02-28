-- =========================================================================
-- SCRIPT DE BASE DE DATOS: EDUCONTROL PWA (MULTI-TENANT / OFFLINE-FIRST)
-- Motor recomendado: InnoDB
-- =========================================================================

-- 1. TABLA: schools (Institutos / Tenants)
CREATE TABLE `schools` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(50) UNIQUE NOT NULL, -- Para URLs (ej. app.com/colegio-hidalgo)
    `logo_path` VARCHAR(255) NULL,
    `timezone` VARCHAR(50) DEFAULT 'America/Mexico_City',
    `allowed_kiosks` INT DEFAULT 1, -- Límite de kioscos por plan de pago
    `is_active` BOOLEAN DEFAULT 1,
    `entry_time` TIME NULL,
    `tolerance_minutes` INT NULL DEFAULT 15,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. TABLA: kiosks (Dispositivos / Monitores para Monetización)
CREATE TABLE `kiosks` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` BIGINT UNSIGNED NOT NULL,
    `activation_code` VARCHAR(20) UNIQUE NOT NULL, -- PIN de un solo uso (ej. X7B-902)
    `name` VARCHAR(50) NULL, -- Ej: "Entrada Principal"
    `is_active` BOOLEAN DEFAULT 0, -- Cambia a 1 cuando se usa el PIN
    `device_token` VARCHAR(255) NULL, -- Token permanente guardado en el LocalStorage del Kiosco
    `last_sync_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. TABLA: users (Administrativos, Directores, Maestros y Papás)

    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `users_email_unique` (`email`, `school_id`)
) ENGINE=InnoDB;

-- 4. TABLA: students (Alumnos - Núcleo de la Sincronización)
CREATE TABLE `students` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` BIGINT UNSIGNED NOT NULL,
    `enrollment_code` VARCHAR(50) NOT NULL, -- Matrícula
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    
    -- Clasificación Académica
    `school_level` VARCHAR(50) NOT NULL, -- ej: 'Primaria'
    `grade` VARCHAR(20) NOT NULL,        -- ej: '1'
    `group_letter` VARCHAR(20) NOT NULL, -- ej: 'A'
    `shift` ENUM('matutino', 'vespertino', 'mixto') NOT NULL DEFAULT 'matutino',
    
    -- Correos para Vinculación Pasiva (Login de Tutores) 
	`tutor_email` VARCHAR(150) NULL, -- Queda NULL al inicio hasta que el tutor lo registre
    `secondary_tutor_email` VARCHAR(150) NULL, -- Segundo tutor opcional
    
    -- Gestión de Imágenes (Optimizadas para Offline)
    `photo_path` VARCHAR(255) NULL,
    `photo_hash` VARCHAR(32) NULL, -- MD5 crítico para Delta Sync
    
    `is_active` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    
    -- Índices para rendimiento e Integridad
    UNIQUE KEY `unique_student_school_enrollment` (`school_id`, `enrollment_code`),
    INDEX `idx_school_sync` (`school_id`, `updated_at`),
    INDEX `idx_academic_filter` (`school_id`, `school_level`, `grade`, `group_letter`),
    INDEX `idx_parents_search` (`tutor_email`,`secondary_tutor_email` )
) ENGINE=InnoDB;

-- 5. TABLA: authorized_persons (Personas Autorizadas a recoger al alumno)
CREATE TABLE `authorized_persons` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `relationship` VARCHAR(50) NOT NULL, -- Ej: 'Madre', 'Abuelo'
    `photo_path` VARCHAR(255) NULL,
    `photo_hash` VARCHAR(32) NULL,
    `is_primary` BOOLEAN DEFAULT 0, -- Solo 1 principal permitido (Regla de negocio en Laravel)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_student_sync` (`student_id`, `updated_at`)
) ENGINE=InnoDB;

-- 6. TABLA: teacher_group_assignments (Para Maestros de múltiples grupos y Prefectos)
CREATE TABLE `teacher_group_assignments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `school_id` BIGINT UNSIGNED NOT NULL,
    `school_level` VARCHAR(50) NOT NULL, 
    `grade` VARCHAR(20) NOT NULL,        
    `group_letter` VARCHAR(20) NOT NULL, 
    `shift` ENUM('matutino', 'vespertino', 'mixto') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_teacher_group` (`user_id`, `school_level`, `grade`, `group_letter`, `shift`)
) ENGINE=InnoDB;

-- 7. TABLA: announcements (Avisos y Circulares)
CREATE TABLE `announcements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` BIGINT UNSIGNED NOT NULL,
    `created_by_user_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `is_general` BOOLEAN DEFAULT 0, -- 1 = Toda la escuela, 0 = Grupos específicos
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_school_date` (`school_id`, `created_at` DESC)
) ENGINE=InnoDB;

-- 8. TABLA: announcement_targets (A qué grupos va dirigido el aviso)
CREATE TABLE `announcement_targets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `announcement_id` BIGINT UNSIGNED NOT NULL,
    `school_level` VARCHAR(50) NOT NULL, 
    `grade` VARCHAR(20) NOT NULL,        
    `group_letter` VARCHAR(20) NOT NULL, 
    `shift` ENUM('matutino', 'vespertino', 'mixto') NOT NULL,

    FOREIGN KEY (`announcement_id`) REFERENCES `announcements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. TABLA: attendance_logs (Historial Masivo de Asistencias)
CREATE TABLE `attendance_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    
    `scanned_at` TIMESTAMP NOT NULL, -- Fecha/Hora real del suceso
    `type` ENUM('in', 'out') NOT NULL,
    
    -- ¿Quién registró la asistencia? (Excluyentes entre sí normalmente)
    `kiosk_id` BIGINT UNSIGNED NULL, -- El equipo automático de la puerta
    `recorded_by_user_id` BIGINT UNSIGNED NULL, -- El maestro si pasó lista manual
    
    -- ¿Quién recogió al niño?
    `authorized_person_id` BIGINT UNSIGNED NULL, 
    
    `sync_status` ENUM('pending', 'synced') DEFAULT 'synced',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`kiosk_id`) REFERENCES `kiosks`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`authorized_person_id`) REFERENCES `authorized_persons`(`id`) ON DELETE SET NULL,

    INDEX `idx_report_generation` (`school_id`, `scanned_at`),
    INDEX `idx_student_history` (`student_id`, `scanned_at`)
) ENGINE=InnoDB;

-- =========================================================================
-- FIN DEL SCRIPT
-- =========================================================================
