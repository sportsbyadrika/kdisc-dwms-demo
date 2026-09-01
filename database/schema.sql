-- ---------------------------------------------------------------------------
-- DWMS 2.0 — schema
-- MySQL 5.7+ / MariaDB 10.3+
-- ---------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------ system
CREATE TABLE IF NOT EXISTS settings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hero_slides (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(150) NOT NULL,
  subtitle    VARCHAR(255) NULL,
  image       VARCHAR(255) NULL,
  cta_label   VARCHAR(60) NULL,
  cta_url     VARCHAR(255) NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_by  INT UNSIGNED NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------- offices & users
-- One self-referencing tree covers office -> department -> section.
CREATE TABLE IF NOT EXISTS offices (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id  INT UNSIGNED NULL,
  name       VARCHAR(150) NOT NULL,
  code       VARCHAR(40) NULL,
  type       ENUM('office','department','section') NOT NULL DEFAULT 'office',
  district   VARCHAR(80) NULL,
  address    VARCHAR(255) NULL,
  phone      VARCHAR(30) NULL,
  email      VARCHAR(150) NULL,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (parent_id), INDEX (type),
  CONSTRAINT fk_office_parent FOREIGN KEY (parent_id) REFERENCES offices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,
  slug        VARCHAR(60) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  permissions TEXT NULL,            -- JSON array of permission keys
  is_system   TINYINT(1) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id       INT UNSIGNED NOT NULL,
  office_id     INT UNSIGNED NULL,
  name          VARCHAR(120) NOT NULL,
  designation   VARCHAR(120) NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  mobile        VARCHAR(15) NULL,
  password      VARCHAR(255) NOT NULL,
  must_reset    TINYINT(1) NOT NULL DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_by    INT UNSIGNED NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (role_id), INDEX (office_id),
  CONSTRAINT fk_user_role   FOREIGN KEY (role_id)   REFERENCES roles(id),
  CONSTRAINT fk_user_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------- job seekers
CREATE TABLE IF NOT EXISTS job_seekers (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(120) NOT NULL,
  email            VARCHAR(150) NOT NULL UNIQUE,
  email_verified   TINYINT(1) NOT NULL DEFAULT 0,
  mobile           VARCHAR(15) NULL,
  mobile_verified  TINYINT(1) NOT NULL DEFAULT 0,
  password         VARCHAR(255) NOT NULL,
  photo            VARCHAR(255) NULL,
  gender           ENUM('male','female','other') NULL,
  dob              DATE NULL,
  headline         VARCHAR(160) NULL,
  about            TEXT NULL,
  -- e-KYC
  kyc_status       ENUM('not_started','pending','verified','failed') NOT NULL DEFAULT 'not_started',
  kyc_method       ENUM('aadhaar','pan','driving_license','passport') NULL,
  kyc_ref          VARCHAR(64) NULL,        -- masked reference, never the full number
  kyc_consent      TINYINT(1) NOT NULL DEFAULT 0,
  kyc_consent_at   DATETIME NULL,
  kyc_verified_at  DATETIME NULL,
  kyc_department_id INT UNSIGNED NULL,      -- department the consent was given to
  profile_score    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  is_active        TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at    DATETIME NULL,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (kyc_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- e-mail / mobile / Aadhaar OTPs
CREATE TABLE IF NOT EXISTS verification_codes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  channel     ENUM('email','mobile','aadhaar') NOT NULL,
  purpose     VARCHAR(40) NOT NULL DEFAULT 'register',
  identifier  VARCHAR(190) NOT NULL,        -- e-mail address / mobile / masked aadhaar
  code_hash   VARCHAR(255) NOT NULL,
  payload     TEXT NULL,
  attempts    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  consumed_at DATETIME NULL,
  expires_at  DATETIME NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (identifier, purpose), INDEX (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_addresses (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id    INT UNSIGNED NOT NULL,
  address_type ENUM('communication','permanent') NOT NULL,
  line1        VARCHAR(180) NOT NULL,
  line2        VARCHAR(180) NULL,
  city         VARCHAR(100) NULL,
  district     VARCHAR(100) NULL,
  state        VARCHAR(100) NULL,
  country      VARCHAR(100) NOT NULL DEFAULT 'India',
  pincode      VARCHAR(10) NULL,
  landmark     VARCHAR(150) NULL,
  updated_at   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seeker_address (seeker_id, address_type),
  CONSTRAINT fk_addr_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_documents (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id   INT UNSIGNED NOT NULL,
  doc_type    ENUM('driving_license','pan_card','passport','voter_id','ration_card','photo','other') NOT NULL,
  doc_number  VARCHAR(60) NULL,
  issued_by   VARCHAR(120) NULL,
  valid_upto  DATE NULL,
  file_path   VARCHAR(255) NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  verified_by INT UNSIGNED NULL,
  remarks     VARCHAR(255) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id, doc_type),
  CONSTRAINT fk_doc_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_resumes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id   INT UNSIGNED NOT NULL,
  title       VARCHAR(120) NULL,
  file_path   VARCHAR(255) NOT NULL,
  file_name   VARCHAR(180) NULL,
  file_size   INT UNSIGNED NULL,
  is_primary  TINYINT(1) NOT NULL DEFAULT 0,
  parse_status ENUM('pending','parsed','failed','skipped') NOT NULL DEFAULT 'pending',
  parsed_data  MEDIUMTEXT NULL,       -- reserved for the resume-parsing phase
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id),
  CONSTRAINT fk_resume_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_qualifications (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id     INT UNSIGNED NOT NULL,
  level         ENUM('below_10','sslc','plus_two','iti','diploma','ug','pg','phd','other') NOT NULL,
  course        VARCHAR(150) NOT NULL,
  specialisation VARCHAR(150) NULL,
  institution   VARCHAR(180) NULL,
  board         VARCHAR(150) NULL,
  year_of_pass  SMALLINT UNSIGNED NULL,
  mark_type     ENUM('percentage','cgpa','grade') NULL,
  mark_value    VARCHAR(20) NULL,
  certificate   VARCHAR(255) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id),
  CONSTRAINT fk_qual_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_experiences (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id    INT UNSIGNED NOT NULL,
  designation  VARCHAR(150) NOT NULL,
  organisation VARCHAR(180) NOT NULL,
  employment_type ENUM('full_time','part_time','contract','internship','freelance','apprenticeship') NULL,
  location     VARCHAR(150) NULL,
  from_date    DATE NULL,
  to_date      DATE NULL,
  is_current   TINYINT(1) NOT NULL DEFAULT 0,
  last_salary  DECIMAL(12,2) NULL,
  responsibilities TEXT NULL,
  document     VARCHAR(255) NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id),
  CONSTRAINT fk_exp_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_certifications (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id     INT UNSIGNED NOT NULL,
  title         VARCHAR(180) NOT NULL,
  issued_by     VARCHAR(180) NULL,
  credential_id VARCHAR(120) NULL,
  credential_url VARCHAR(255) NULL,
  issued_on     DATE NULL,
  valid_upto    DATE NULL,
  file_path     VARCHAR(255) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id),
  CONSTRAINT fk_cert_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_achievements (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id    INT UNSIGNED NOT NULL,
  title        VARCHAR(180) NOT NULL,
  category     ENUM('award','publication','sports','arts','social','patent','other') NOT NULL DEFAULT 'other',
  awarded_by   VARCHAR(180) NULL,
  awarded_on   DATE NULL,
  description  TEXT NULL,
  file_path    VARCHAR(255) NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id),
  CONSTRAINT fk_ach_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seeker_skills (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id   INT UNSIGNED NOT NULL,
  skill_name  VARCHAR(120) NOT NULL,
  proficiency ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'intermediate',
  years       DECIMAL(4,1) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (seeker_id),
  CONSTRAINT fk_sskill_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------- employers
CREATE TABLE IF NOT EXISTS employers (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_name      VARCHAR(180) NOT NULL,
  email             VARCHAR(150) NOT NULL UNIQUE,
  email_verified    TINYINT(1) NOT NULL DEFAULT 0,
  password          VARCHAR(255) NOT NULL,
  logo              VARCHAR(255) NULL,
  -- company details (wizard step 2)
  ownership_type    ENUM('proprietorship','partnership','llp','private_limited','public_limited','government','psu','ngo','cooperative','other') NULL,
  industry          VARCHAR(120) NULL,
  employee_range    ENUM('1-10','11-50','51-200','201-500','501-1000','1000+') NULL,
  established_year  SMALLINT UNSIGNED NULL,
  website           VARCHAR(180) NULL,
  about             TEXT NULL,
  -- statutory (wizard step 3)
  pan               VARCHAR(15) NULL,
  gstin             VARCHAR(20) NULL,
  cin               VARCHAR(30) NULL,
  registration_no   VARCHAR(60) NULL,
  labour_licence_no VARCHAR(60) NULL,
  -- address + contact (wizard step 4)
  address_line1     VARCHAR(180) NULL,
  address_line2     VARCHAR(180) NULL,
  city              VARCHAR(100) NULL,
  district          VARCHAR(100) NULL,
  state             VARCHAR(100) NULL,
  pincode           VARCHAR(10) NULL,
  contact_person    VARCHAR(120) NULL,
  contact_designation VARCHAR(120) NULL,
  contact_mobile    VARCHAR(15) NULL,
  contact_email     VARCHAR(150) NULL,
  -- workflow
  profile_step      TINYINT UNSIGNED NOT NULL DEFAULT 1,
  profile_completed TINYINT(1) NOT NULL DEFAULT 0,
  status            ENUM('pending','verified','rejected','suspended') NOT NULL DEFAULT 'pending',
  verified_by       INT UNSIGNED NULL,
  verified_at       DATETIME NULL,
  remarks           VARCHAR(255) NULL,
  last_login_at     DATETIME NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employer_documents (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id INT UNSIGNED NOT NULL,
  doc_type    ENUM('pan','gst','incorporation','licence','other') NOT NULL,
  file_path   VARCHAR(255) NOT NULL,
  label       VARCHAR(150) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (employer_id),
  CONSTRAINT fk_edoc_employer FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------------- jobs
CREATE TABLE IF NOT EXISTS job_categories (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(120) NOT NULL,
  slug      VARCHAR(120) NOT NULL UNIQUE,
  icon      VARCHAR(60) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A job title is the "curation sheet" an employer uploads.
CREATE TABLE IF NOT EXISTS jobs (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id       INT UNSIGNED NOT NULL,
  category_id       INT UNSIGNED NULL,
  code              VARCHAR(20) NOT NULL UNIQUE,
  title             VARCHAR(180) NOT NULL,
  slug              VARCHAR(200) NULL,
  description       TEXT NULL,
  responsibilities  TEXT NULL,
  -- curation sheet: eligibility
  min_qualification ENUM('below_10','sslc','plus_two','iti','diploma','ug','pg','phd','any') NOT NULL DEFAULT 'any',
  qualification_note VARCHAR(255) NULL,
  skills_required   VARCHAR(500) NULL,
  experience_min    DECIMAL(4,1) NOT NULL DEFAULT 0,
  experience_max    DECIMAL(4,1) NULL,
  age_min           TINYINT UNSIGNED NULL,
  age_max           TINYINT UNSIGNED NULL,
  gender_preference ENUM('any','male','female') NOT NULL DEFAULT 'any',
  -- curation sheet: engagement
  employment_type   ENUM('full_time','part_time','contract','internship','apprenticeship','freelance') NOT NULL DEFAULT 'full_time',
  work_mode         ENUM('on_site','hybrid','remote') NOT NULL DEFAULT 'on_site',
  vacancies         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  salary_min        DECIMAL(12,2) NULL,
  salary_max        DECIMAL(12,2) NULL,
  salary_period     ENUM('monthly','annual','daily','hourly') NOT NULL DEFAULT 'monthly',
  job_location      VARCHAR(180) NULL,
  district          VARCHAR(100) NULL,
  state             VARCHAR(100) NULL DEFAULT 'Kerala',
  -- curation sheet: process
  selection_process VARCHAR(255) NULL,
  benefits          VARCHAR(500) NULL,
  contact_email     VARCHAR(150) NULL,
  contact_mobile    VARCHAR(15) NULL,
  last_date         DATE NULL,
  status            ENUM('draft','published','closed','archived') NOT NULL DEFAULT 'draft',
  views             INT UNSIGNED NOT NULL DEFAULT 0,
  published_at      DATETIME NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (status, last_date), INDEX (employer_id), INDEX (category_id), INDEX (district),
  FULLTEXT KEY ft_job (title, description, skills_required),
  CONSTRAINT fk_job_employer FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  CONSTRAINT fk_job_category FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id        INT UNSIGNED NOT NULL,
  seeker_id     INT UNSIGNED NOT NULL,
  resume_id     INT UNSIGNED NULL,
  cover_note    TEXT NULL,
  status        ENUM('applied','shortlisted','interview','selected','rejected','withdrawn') NOT NULL DEFAULT 'applied',
  employer_remarks VARCHAR(255) NULL,
  match_score   TINYINT UNSIGNED NULL,
  applied_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_application (job_id, seeker_id),
  INDEX (seeker_id), INDEX (status),
  CONSTRAINT fk_app_job    FOREIGN KEY (job_id)    REFERENCES jobs(id) ON DELETE CASCADE,
  CONSTRAINT fk_app_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- "Apply after login" parking area, also the seeker's saved jobs
CREATE TABLE IF NOT EXISTS wishlists (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seeker_id  INT UNSIGNED NOT NULL,
  job_id     INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wishlist (seeker_id, job_id),
  CONSTRAINT fk_wish_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE,
  CONSTRAINT fk_wish_job    FOREIGN KEY (job_id)    REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------ skills
CREATE TABLE IF NOT EXISTS skill_categories (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(120) NOT NULL,
  slug      VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS skill_programmes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id   INT UNSIGNED NULL,
  title         VARCHAR(180) NOT NULL,
  provider      VARCHAR(180) NULL,
  description   TEXT NULL,
  outcomes      TEXT NULL,
  eligibility   VARCHAR(255) NULL,
  mode          ENUM('online','offline','hybrid') NOT NULL DEFAULT 'offline',
  level         ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  duration_value SMALLINT UNSIGNED NULL,
  duration_unit ENUM('hours','days','weeks','months') NULL DEFAULT 'weeks',
  fee           DECIMAL(10,2) NULL,
  is_free       TINYINT(1) NOT NULL DEFAULT 0,
  seats         SMALLINT UNSIGNED NULL,
  district      VARCHAR(100) NULL,
  venue         VARCHAR(180) NULL,
  start_date    DATE NULL,
  apply_url     VARCHAR(255) NULL,
  contact_email VARCHAR(150) NULL,
  contact_phone VARCHAR(30) NULL,
  image         VARCHAR(255) NULL,
  is_certified  TINYINT(1) NOT NULL DEFAULT 0,
  status        ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  views         INT UNSIGNED NOT NULL DEFAULT 0,
  created_by    INT UNSIGNED NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (status), INDEX (category_id), INDEX (district),
  CONSTRAINT fk_skill_cat FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS skill_enrolments (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  programme_id INT UNSIGNED NOT NULL,
  seeker_id    INT UNSIGNED NOT NULL,
  status       ENUM('interested','enrolled','completed','dropped') NOT NULL DEFAULT 'interested',
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enrol (programme_id, seeker_id),
  CONSTRAINT fk_enrol_prog   FOREIGN KEY (programme_id) REFERENCES skill_programmes(id) ON DELETE CASCADE,
  CONSTRAINT fk_enrol_seeker FOREIGN KEY (seeker_id)    REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------- career services
CREATE TABLE IF NOT EXISTS career_service_categories (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(120) NOT NULL,
  slug      VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS career_services (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id   INT UNSIGNED NULL,
  title         VARCHAR(180) NOT NULL,
  summary       VARCHAR(255) NULL,
  description   TEXT NULL,
  provider      VARCHAR(180) NULL,
  service_mode  ENUM('online','offline','hybrid') NOT NULL DEFAULT 'online',
  audience      VARCHAR(180) NULL,
  is_free       TINYINT(1) NOT NULL DEFAULT 1,
  fee           DECIMAL(10,2) NULL,
  district      VARCHAR(100) NULL,
  venue         VARCHAR(180) NULL,
  schedule_note VARCHAR(180) NULL,
  booking_url   VARCHAR(255) NULL,
  contact_email VARCHAR(150) NULL,
  contact_phone VARCHAR(30) NULL,
  icon          VARCHAR(60) NULL,
  image         VARCHAR(255) NULL,
  status        ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  views         INT UNSIGNED NOT NULL DEFAULT 0,
  created_by    INT UNSIGNED NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (status), INDEX (category_id),
  CONSTRAINT fk_cs_cat FOREIGN KEY (category_id) REFERENCES career_service_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS career_service_requests (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id INT UNSIGNED NOT NULL,
  seeker_id  INT UNSIGNED NOT NULL,
  note       VARCHAR(500) NULL,
  status     ENUM('requested','scheduled','completed','cancelled') NOT NULL DEFAULT 'requested',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_csr (service_id, seeker_id),
  CONSTRAINT fk_csr_service FOREIGN KEY (service_id) REFERENCES career_services(id) ON DELETE CASCADE,
  CONSTRAINT fk_csr_seeker  FOREIGN KEY (seeker_id)  REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------------ misc
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  phone      VARCHAR(20) NULL,
  subject    VARCHAR(180) NULL,
  message    TEXT NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_log (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_type  ENUM('seeker','employer','official','system') NOT NULL,
  actor_id    INT UNSIGNED NULL,
  action      VARCHAR(80) NOT NULL,
  subject     VARCHAR(80) NULL,
  subject_id  INT UNSIGNED NULL,
  description VARCHAR(255) NULL,
  ip_address  VARCHAR(45) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (actor_type, actor_id), INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
