-- ---------------------------------------------------------------------------
-- User groups: the segment of the workforce ecosystem a departmental user
-- belongs to. Independent of the role (which carries permissions) and of the
-- office (which carries the hierarchy), because a placement coordinator sits
-- inside a college and a programme executive sits inside K-DISC.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS user_groups (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,
  slug        VARCHAR(60) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  icon        VARCHAR(40) NOT NULL DEFAULT 'users',
  sort_order  SMALLINT NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO user_groups (name, slug, description, icon, sort_order) VALUES
  ('Government Departments',   'government-departments',   'Secretariat departments and directorates that own employment and skilling programmes.', 'building',   1),
  ('Universities',             'universities',             'State, deemed and technological universities across Kerala.',                            'graduation', 2),
  ('Educational Institutions', 'educational-institutions', 'Engineering and polytechnic colleges, ITIs, arts and science colleges, and medical and paramedical institutions.', 'book', 3),
  ('Programme Executives',     'programme-executives',     'Executives running skilling and employment programmes on the ground.',                   'clipboard',  4),
  ('Placement Coordinators',   'placement-coordinators',   'Coordinators who link candidates at an institution to employers.',                        'users',      5),
  ('District Officials',       'district-officials',       'District level employment and skilling officers.',                                        'map-pin',    6),
  ('Job Stations',             'job-stations',             'Job stations operating across blocks, municipalities and corporations.',                  'briefcase',  7),
  ('SDPK Centers',             'sdpk-centers',             'Centres operating under the SDPK network across the districts.',                          'sparkles',   8),
  ('K-DISC Officials',         'kdisc-officials',          'Kerala Development and Innovation Strategic Council staff administering the platform.',   'shield',     9)
ON DUPLICATE KEY UPDATE
  name = VALUES(name), description = VALUES(description), icon = VALUES(icon), sort_order = VALUES(sort_order);

-- users.group_id — added defensively so the migration is safe to re-run.
SET @col := (SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'group_id');
SET @sql := IF(@col = 0,
  'ALTER TABLE users ADD COLUMN group_id INT UNSIGNED NULL AFTER role_id, ADD INDEX idx_users_group (group_id)',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk := (SELECT COUNT(*) FROM information_schema.table_constraints
            WHERE table_schema = DATABASE() AND table_name = 'users' AND constraint_name = 'fk_user_group');
SET @sql := IF(@fk = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_user_group FOREIGN KEY (group_id) REFERENCES user_groups(id) ON DELETE SET NULL',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The people already on the platform administer it, so they are K-DISC staff.
UPDATE users u
  JOIN user_groups g ON g.slug = 'kdisc-officials'
   SET u.group_id = g.id
 WHERE u.group_id IS NULL;
