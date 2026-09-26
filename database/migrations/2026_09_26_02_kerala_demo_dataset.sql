-- ---------------------------------------------------------------------------
-- Kerala demo dataset: offices for the institutions that make up the workforce
-- ecosystem, and one departmental user in each of the nine user groups.
--
-- The institution names are real and public; every person, e-mail address and
-- mobile number below is invented. Demo accounts carry a hash of a random
-- string nobody holds, so none of them can be signed into until an
-- administrator issues a temporary password with Reset password.
--
-- Job station and SDPK centre names are representative, keyed to the district
-- they serve rather than to a verified published list.
--
-- Delete this file before deploying if you do not want demo content.
-- ---------------------------------------------------------------------------

-- offices.code is treated as unique by the application; make the database
-- agree so this file can be applied more than once safely. Skipped if the
-- table already holds duplicate codes.
SET @idx := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'offices' AND index_name = 'uq_office_code');
SET @dupes := (SELECT COUNT(*) FROM (SELECT code FROM offices
               WHERE code IS NOT NULL AND code <> '' GROUP BY code HAVING COUNT(*) > 1) d);
SET @sql := IF(@idx = 0 AND @dupes = 0,
  'ALTER TABLE offices ADD UNIQUE INDEX uq_office_code (code)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Departments and directorates
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (NULL, 'Kerala Development and Innovation Strategic Council', 'KDISC', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Department of Higher Education', 'DHE', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Directorate of Technical Education', 'DTE', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Department of Labour and Skills', 'DLS', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Directorate of Collegiate Education', 'DCE', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Directorate of Medical Education', 'DME', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Department of General Education', 'DGE', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Directorate of Industries and Commerce', 'DIC', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Local Self Government Department', 'LSGD', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Department of Electronics and Information Technology', 'KITD', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Norka Roots', 'NORKA', 'office', 'Thiruvananthapuram', 1),
  (NULL, 'Department of Social Justice', 'DSJ', 'office', 'Thiruvananthapuram', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

SET @kdisc := (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1);
SET @dhe := (SELECT id FROM offices WHERE code = 'DHE' LIMIT 1);
SET @dte := (SELECT id FROM offices WHERE code = 'DTE' LIMIT 1);
SET @dls := (SELECT id FROM offices WHERE code = 'DLS' LIMIT 1);
SET @dce := (SELECT id FROM offices WHERE code = 'DCE' LIMIT 1);
SET @dme := (SELECT id FROM offices WHERE code = 'DME' LIMIT 1);
SET @emp := (SELECT id FROM offices WHERE code = 'HQ' LIMIT 1);

-- Universities
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dhe, 'Digital University Kerala', 'UNIV-DUK', 'department', 'Thiruvananthapuram', 1),
  (@dhe, 'University of Kerala', 'UNIV-KU', 'department', 'Thiruvananthapuram', 1),
  (@dhe, 'APJ Abdul Kalam Technological University', 'UNIV-KTU', 'department', 'Thiruvananthapuram', 1),
  (@dhe, 'Mahatma Gandhi University', 'UNIV-MGU', 'department', 'Kottayam', 1),
  (@dhe, 'University of Calicut', 'UNIV-CU', 'department', 'Malappuram', 1),
  (@dhe, 'Kannur University', 'UNIV-KNU', 'department', 'Kannur', 1),
  (@dhe, 'Cochin University of Science and Technology', 'UNIV-CUSAT', 'department', 'Ernakulam', 1),
  (@dhe, 'Kerala University of Health Sciences', 'UNIV-KUHS', 'department', 'Thrissur', 1),
  (@dhe, 'Kerala Agricultural University', 'UNIV-KAU', 'department', 'Thrissur', 1),
  (@dhe, 'Kerala University of Fisheries and Ocean Studies', 'UNIV-KUFOS', 'department', 'Ernakulam', 1),
  (@dhe, 'Sree Sankaracharya University of Sanskrit', 'UNIV-SSUS', 'department', 'Ernakulam', 1),
  (@dhe, 'Thunchath Ezhuthachan Malayalam University', 'UNIV-MALU', 'department', 'Malappuram', 1),
  (@dhe, 'Kerala Veterinary and Animal Sciences University', 'UNIV-KVASU', 'department', 'Wayanad', 1),
  (@dhe, 'National University of Advanced Legal Studies', 'UNIV-NUALS', 'department', 'Ernakulam', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Engineering colleges - government
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dte, 'College of Engineering Trivandrum', 'ENGG-CET', 'department', 'Thiruvananthapuram', 1),
  (@dte, 'Government Engineering College, Barton Hill', 'ENGG-GECBH', 'department', 'Thiruvananthapuram', 1),
  (@dte, 'Government Engineering College, Thrissur', 'ENGG-GECT', 'department', 'Thrissur', 1),
  (@dte, 'Government Engineering College, Kozhikode', 'ENGG-GECKKD', 'department', 'Kozhikode', 1),
  (@dte, 'Rajiv Gandhi Institute of Technology, Kottayam', 'ENGG-RIT', 'department', 'Kottayam', 1),
  (@dte, 'College of Engineering, Chengannur', 'ENGG-CEC', 'department', 'Alappuzha', 1),
  (@dte, 'Government Engineering College, Idukki', 'ENGG-GECI', 'department', 'Idukki', 1),
  (@dte, 'Government Engineering College, Wayanad', 'ENGG-GECW', 'department', 'Wayanad', 1),
  (@dte, 'Government Engineering College, Sreekrishnapuram', 'ENGG-GECSKP', 'department', 'Palakkad', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Engineering colleges - aided
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dte, 'TKM College of Engineering, Kollam', 'ENGG-TKM', 'department', 'Kollam', 1),
  (@dte, 'NSS College of Engineering, Palakkad', 'ENGG-NSS', 'department', 'Palakkad', 1),
  (@dte, 'MES College of Engineering, Kuttippuram', 'ENGG-MES', 'department', 'Malappuram', 1),
  (@dte, 'Mar Athanasius College of Engineering, Kothamangalam', 'ENGG-MACE', 'department', 'Ernakulam', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Engineering colleges - self financing
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dte, 'Rajagiri School of Engineering and Technology, Kakkanad', 'ENGG-RSET', 'department', 'Ernakulam', 1),
  (@dte, 'Saintgits College of Engineering, Kottayam', 'ENGG-SGITS', 'department', 'Kottayam', 1),
  (@dte, 'Federal Institute of Science and Technology, Angamaly', 'ENGG-FISAT', 'department', 'Ernakulam', 1),
  (@dte, 'Muthoot Institute of Technology and Science, Varikoli', 'ENGG-MITS', 'department', 'Ernakulam', 1),
  (@dte, 'Vidya Academy of Science and Technology, Thrissur', 'ENGG-VAST', 'department', 'Thrissur', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Polytechnic colleges
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dte, 'Government Polytechnic College, Kalamassery', 'POLY-KLMS', 'department', 'Ernakulam', 1),
  (@dte, 'Central Polytechnic College, Vattiyoorkavu', 'POLY-CPT', 'department', 'Thiruvananthapuram', 1),
  (@dte, 'Maharaja''s Technological Institute, Thrissur', 'POLY-MTI', 'department', 'Thrissur', 1),
  (@dte, 'Government Polytechnic College, Kozhikode', 'POLY-KKD', 'department', 'Kozhikode', 1),
  (@dte, 'Government Polytechnic College, Kannur', 'POLY-KNR', 'department', 'Kannur', 1),
  (@dte, 'Government Women''s Polytechnic College, Thiruvananthapuram', 'POLY-WPTC', 'department', 'Thiruvananthapuram', 1),
  (@dte, 'Carmel Polytechnic College, Punnapra', 'POLY-CARMEL', 'department', 'Alappuzha', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Industrial Training Institutes
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dls, 'Government ITI, Chackai', 'ITI-CHK', 'department', 'Thiruvananthapuram', 1),
  (@dls, 'Government ITI, Kalamassery', 'ITI-KLMS', 'department', 'Ernakulam', 1),
  (@dls, 'Government ITI, West Hill', 'ITI-WH', 'department', 'Kozhikode', 1),
  (@dls, 'Government ITI, Dharmadam', 'ITI-DHM', 'department', 'Kannur', 1),
  (@dls, 'Government ITI, Malampuzha', 'ITI-MLP', 'department', 'Palakkad', 1),
  (@dls, 'Government Women''s ITI, Kaimanam', 'ITI-WKMN', 'department', 'Thiruvananthapuram', 1),
  (@dls, 'Government ITI, Ettumanoor', 'ITI-ETM', 'department', 'Kottayam', 1),
  (@dls, 'Government ITI, Attingal', 'ITI-ATL', 'department', 'Thiruvananthapuram', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Arts and science colleges
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dce, 'University College, Thiruvananthapuram', 'ASC-UCTVM', 'department', 'Thiruvananthapuram', 1),
  (@dce, 'Maharaja''s College, Ernakulam', 'ASC-MAH', 'department', 'Ernakulam', 1),
  (@dce, 'Government Victoria College, Palakkad', 'ASC-VICPKD', 'department', 'Palakkad', 1),
  (@dce, 'Government Brennen College, Thalassery', 'ASC-BRENNEN', 'department', 'Kannur', 1),
  (@dce, 'St. Teresa''s College, Ernakulam', 'ASC-STERESA', 'department', 'Ernakulam', 1),
  (@dce, 'Sacred Heart College, Thevara', 'ASC-SHTHEV', 'department', 'Ernakulam', 1),
  (@dce, 'Farook College, Kozhikode', 'ASC-FAROOK', 'department', 'Kozhikode', 1),
  (@dce, 'St. Joseph''s College, Devagiri', 'ASC-DEVAGIRI', 'department', 'Kozhikode', 1),
  (@dce, 'CMS College, Kottayam', 'ASC-CMS', 'department', 'Kottayam', 1),
  (@dce, 'Mar Ivanios College, Thiruvananthapuram', 'ASC-MIC', 'department', 'Thiruvananthapuram', 1),
  (@dce, 'Zamorin''s Guruvayurappan College, Kozhikode', 'ASC-ZGC', 'department', 'Kozhikode', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Medical and paramedical colleges
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@dme, 'Government Medical College, Thiruvananthapuram', 'MED-TVM', 'department', 'Thiruvananthapuram', 1),
  (@dme, 'Government Medical College, Kottayam', 'MED-KTM', 'department', 'Kottayam', 1),
  (@dme, 'Government Medical College, Kozhikode', 'MED-KKD', 'department', 'Kozhikode', 1),
  (@dme, 'Government Medical College, Thrissur', 'MED-TSR', 'department', 'Thrissur', 1),
  (@dme, 'Government Medical College, Alappuzha', 'MED-ALP', 'department', 'Alappuzha', 1),
  (@dme, 'Government Medical College, Ernakulam', 'MED-EKM', 'department', 'Ernakulam', 1),
  (@dme, 'Government Medical College, Manjeri', 'MED-MJR', 'department', 'Malappuram', 1),
  (@dme, 'Government Dental College, Thiruvananthapuram', 'MED-DENTAL', 'department', 'Thiruvananthapuram', 1),
  (@dme, 'Government College of Nursing, Thiruvananthapuram', 'MED-NURSING', 'department', 'Thiruvananthapuram', 1),
  (@dme, 'Government Ayurveda College, Thiruvananthapuram', 'MED-AYUR', 'department', 'Thiruvananthapuram', 1),
  (@dme, 'Government Homoeopathic Medical College, Thiruvananthapuram', 'MED-HOMEO', 'department', 'Thiruvananthapuram', 1),
  (@dme, 'Sree Chitra Tirunal Institute for Medical Sciences and Technology', 'MED-SCTIMST', 'department', 'Thiruvananthapuram', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Job stations
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@kdisc, 'Job Station, Thiruvananthapuram Corporation', 'JS-TVM', 'department', 'Thiruvananthapuram', 1),
  (@kdisc, 'Job Station, Kollam Corporation', 'JS-KLM', 'department', 'Kollam', 1),
  (@kdisc, 'Job Station, Pathanamthitta Municipality', 'JS-PTA', 'department', 'Pathanamthitta', 1),
  (@kdisc, 'Job Station, Alappuzha Municipality', 'JS-ALP', 'department', 'Alappuzha', 1),
  (@kdisc, 'Job Station, Kottayam Municipality', 'JS-KTM', 'department', 'Kottayam', 1),
  (@kdisc, 'Job Station, Thodupuzha Municipality', 'JS-IDK', 'department', 'Idukki', 1),
  (@kdisc, 'Job Station, Kochi Corporation', 'JS-EKM', 'department', 'Ernakulam', 1),
  (@kdisc, 'Job Station, Thrissur Corporation', 'JS-TSR', 'department', 'Thrissur', 1),
  (@kdisc, 'Job Station, Palakkad Municipality', 'JS-PKD', 'department', 'Palakkad', 1),
  (@kdisc, 'Job Station, Manjeri Municipality', 'JS-MLP', 'department', 'Malappuram', 1),
  (@kdisc, 'Job Station, Kozhikode Corporation', 'JS-KKD', 'department', 'Kozhikode', 1),
  (@kdisc, 'Job Station, Kalpetta Municipality', 'JS-WYD', 'department', 'Wayanad', 1),
  (@kdisc, 'Job Station, Kannur Corporation', 'JS-KNR', 'department', 'Kannur', 1),
  (@kdisc, 'Job Station, Kasaragod Municipality', 'JS-KSD', 'department', 'Kasaragod', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- SDPK centres
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@kdisc, 'SDPK Centre, Thiruvananthapuram', 'SDPK-THI', 'department', 'Thiruvananthapuram', 1),
  (@kdisc, 'SDPK Centre, Kollam', 'SDPK-KOL', 'department', 'Kollam', 1),
  (@kdisc, 'SDPK Centre, Alappuzha', 'SDPK-ALA', 'department', 'Alappuzha', 1),
  (@kdisc, 'SDPK Centre, Kottayam', 'SDPK-KOT', 'department', 'Kottayam', 1),
  (@kdisc, 'SDPK Centre, Ernakulam', 'SDPK-ERN', 'department', 'Ernakulam', 1),
  (@kdisc, 'SDPK Centre, Thrissur', 'SDPK-THR', 'department', 'Thrissur', 1),
  (@kdisc, 'SDPK Centre, Palakkad', 'SDPK-PAL', 'department', 'Palakkad', 1),
  (@kdisc, 'SDPK Centre, Malappuram', 'SDPK-MAL', 'department', 'Malappuram', 1),
  (@kdisc, 'SDPK Centre, Kozhikode', 'SDPK-KOZ', 'department', 'Kozhikode', 1),
  (@kdisc, 'SDPK Centre, Kannur', 'SDPK-KAN', 'department', 'Kannur', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- District employment exchanges
INSERT INTO offices (parent_id, name, code, type, district, is_active) VALUES
  (@emp, 'District Employment Exchange, Thiruvananthapuram', 'DEO-THI', 'department', 'Thiruvananthapuram', 1),
  (@emp, 'District Employment Exchange, Kollam', 'DEO-KOL', 'department', 'Kollam', 1),
  (@emp, 'District Employment Exchange, Pathanamthitta', 'DEO-PAT', 'department', 'Pathanamthitta', 1),
  (@emp, 'District Employment Exchange, Alappuzha', 'DEO-ALA', 'department', 'Alappuzha', 1),
  (@emp, 'District Employment Exchange, Kottayam', 'DEO-KOT', 'department', 'Kottayam', 1),
  (@emp, 'District Employment Exchange, Idukki', 'DEO-IDU', 'department', 'Idukki', 1),
  (@emp, 'District Employment Exchange, Ernakulam', 'DEO-ERN', 'department', 'Ernakulam', 1),
  (@emp, 'District Employment Exchange, Thrissur', 'DEO-THR', 'department', 'Thrissur', 1),
  (@emp, 'District Employment Exchange, Palakkad', 'DEO-PAL', 'department', 'Palakkad', 1),
  (@emp, 'District Employment Exchange, Malappuram', 'DEO-MAL', 'department', 'Malappuram', 1),
  (@emp, 'District Employment Exchange, Kozhikode', 'DEO-KOZ', 'department', 'Kozhikode', 1),
  (@emp, 'District Employment Exchange, Wayanad', 'DEO-WAY', 'department', 'Wayanad', 1),
  (@emp, 'District Employment Exchange, Kannur', 'DEO-KAN', 'department', 'Kannur', 1),
  (@emp, 'District Employment Exchange, Kasaragod', 'DEO-KAS', 'department', 'Kasaragod', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), parent_id = VALUES(parent_id),
  type = VALUES(type), district = VALUES(district), is_active = VALUES(is_active);

-- Roles and groups are looked up once, then reused for every row below.
SET @r_admin := (SELECT id FROM roles WHERE slug = 'admin' LIMIT 1);
SET @r_office := (SELECT id FROM roles WHERE slug = 'office_manager' LIMIT 1);
SET @r_skills := (SELECT id FROM roles WHERE slug = 'skills_manager' LIMIT 1);
SET @r_career := (SELECT id FROM roles WHERE slug = 'career_manager' LIMIT 1);
SET @r_verify := (SELECT id FROM roles WHERE slug = 'verification_officer' LIMIT 1);
SET @g_gov := (SELECT id FROM user_groups WHERE slug = 'government-departments' LIMIT 1);
SET @g_univ := (SELECT id FROM user_groups WHERE slug = 'universities' LIMIT 1);
SET @g_edu := (SELECT id FROM user_groups WHERE slug = 'educational-institutions' LIMIT 1);
SET @g_prog := (SELECT id FROM user_groups WHERE slug = 'programme-executives' LIMIT 1);
SET @g_plac := (SELECT id FROM user_groups WHERE slug = 'placement-coordinators' LIMIT 1);
SET @g_dist := (SELECT id FROM user_groups WHERE slug = 'district-officials' LIMIT 1);
SET @g_job := (SELECT id FROM user_groups WHERE slug = 'job-stations' LIMIT 1);
SET @g_sdpk := (SELECT id FROM user_groups WHERE slug = 'sdpk-centers' LIMIT 1);
SET @g_kdisc := (SELECT id FROM user_groups WHERE slug = 'kdisc-officials' LIMIT 1);

-- Government departments
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_admin, @g_gov, (SELECT id FROM offices WHERE code = 'DHE' LIMIT 1), 'Latha Menon', 'Joint Secretary', 'latha.menon@dwms.local', '6938201573', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_admin, @g_gov, (SELECT id FROM offices WHERE code = 'DTE' LIMIT 1), 'Athira Joseph', 'Director', 'athira.joseph@dwms.local', '7604893997', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_admin, @g_gov, (SELECT id FROM offices WHERE code = 'DLS' LIMIT 1), 'Suja Thomas', 'Additional Director', 'suja.thomas@dwms.local', '7185543506', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_admin, @g_gov, (SELECT id FROM offices WHERE code = 'DCE' LIMIT 1), 'Benny Abraham', 'Director', 'benny.abraham@dwms.local', '8840128672', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_admin, @g_gov, (SELECT id FROM offices WHERE code = 'DME' LIMIT 1), 'Soumya Ummer', 'Director', 'soumya.ummer@dwms.local', '6826222315', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_gov, (SELECT id FROM offices WHERE code = 'DGE' LIMIT 1), 'Aparna Menon', 'Deputy Director', 'aparna.menon@dwms.local', '6734460125', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_gov, (SELECT id FROM offices WHERE code = 'DIC' LIMIT 1), 'Santhosh Varghese', 'Joint Director', 'santhosh.varghese@dwms.local', '6890933876', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_gov, (SELECT id FROM offices WHERE code = 'LSGD' LIMIT 1), 'Latha Gopinath', 'Under Secretary', 'latha.gopinath@dwms.local', '7052676764', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_gov, (SELECT id FROM offices WHERE code = 'KITD' LIMIT 1), 'Pradeep Bhaskaran', 'Scientific Officer', 'pradeep.bhaskaran@dwms.local', '6648226090', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_gov, (SELECT id FROM offices WHERE code = 'NORKA' LIMIT 1), 'Nimmy Bhaskaran', 'Programme Officer', 'nimmy.bhaskaran@dwms.local', '6437223790', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_gov, (SELECT id FROM offices WHERE code = 'DSJ' LIMIT 1), 'Vinod Panicker', 'Assistant Director', 'vinod.panicker@dwms.local', '8484897826', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_verify, @g_gov, (SELECT id FROM offices WHERE code = 'DHE' LIMIT 1), 'Rajesh Das', 'Section Officer', 'rajesh.das@dwms.local', '6917492503', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- Universities
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-DUK' LIMIT 1), 'Rajesh Mohan', 'Director, Career Guidance Cell', 'rajesh.mohan@dwms.local', '6709655585', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KU' LIMIT 1), 'Nisha Abraham', 'Registrar', 'nisha.abraham@dwms.local', '9509794716', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KTU' LIMIT 1), 'Anitha Iyer', 'Registrar', 'anitha.iyer@dwms.local', '9791278696', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-MGU' LIMIT 1), 'Jose Gopinath', 'Director, Career Guidance Cell', 'jose.gopinath@dwms.local', '6203211464', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-CU' LIMIT 1), 'Asha Rajan', 'Registrar', 'asha.rajan@dwms.local', '7620397101', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KNU' LIMIT 1), 'Athira Iyer', 'Registrar', 'athira.iyer@dwms.local', '6569801699', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-CUSAT' LIMIT 1), 'Meera Menon', 'Director, Career Guidance Cell', 'meera.menon@dwms.local', '9689219079', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KUHS' LIMIT 1), 'Ravindran Nair', 'Registrar', 'ravindran.nair@dwms.local', '8582189070', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KAU' LIMIT 1), 'Shalini Nambiar', 'Registrar', 'shalini.nambiar@dwms.local', '7050343165', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KUFOS' LIMIT 1), 'Manoj Namboothiri', 'Director, Career Guidance Cell', 'manoj.namboothiri@dwms.local', '6649391325', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-SSUS' LIMIT 1), 'Neethu Nair', 'Registrar', 'neethu.nair@dwms.local', '6917671461', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-MALU' LIMIT 1), 'Indu Kurup', 'Registrar', 'indu.kurup@dwms.local', '6899908877', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-KVASU' LIMIT 1), 'Athira Menon', 'Director, Career Guidance Cell', 'athira.menon@dwms.local', '8966027647', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_univ, (SELECT id FROM offices WHERE code = 'UNIV-NUALS' LIMIT 1), 'Sajeev Abraham', 'Registrar', 'sajeev.abraham@dwms.local', '7143473284', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- Educational institutions
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-CET' LIMIT 1), 'Asha Mohan', 'Principal', 'asha.mohan@dwms.local', '6031071334', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-GECBH' LIMIT 1), 'Suja Menon', 'Principal', 'suja.menon@dwms.local', '6300797299', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-GECT' LIMIT 1), 'Remya Rasheed', 'Principal', 'remya.rasheed@dwms.local', '7316066823', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-GECKKD' LIMIT 1), 'Athira Namboothiri', 'Principal', 'athira.namboothiri@dwms.local', '9918497953', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-RIT' LIMIT 1), 'Biju Varghese', 'Principal', 'biju.varghese@dwms.local', '9992592864', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-TKM' LIMIT 1), 'Mohan Balakrishnan', 'Principal', 'mohan.balakrishnan@dwms.local', '9566645922', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-NSS' LIMIT 1), 'Ravindran Cherian', 'Principal', 'ravindran.cherian@dwms.local', '8944042583', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-MES' LIMIT 1), 'Prasad Kurup', 'Principal', 'prasad.kurup@dwms.local', '8330470281', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-RSET' LIMIT 1), 'Prasad Rasheed', 'Principal', 'prasad.rasheed@dwms.local', '6915102399', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-SGITS' LIMIT 1), 'Maya Mathew', 'Principal', 'maya.mathew@dwms.local', '7176426201', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ENGG-FISAT' LIMIT 1), 'Manoj Thomas', 'Principal', 'manoj.thomas@dwms.local', '8313546651', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'POLY-KLMS' LIMIT 1), 'Faisal Ummer', 'Principal', 'faisal.ummer@dwms.local', '9818995655', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'POLY-CPT' LIMIT 1), 'Noushad Jacob', 'Principal', 'noushad.jacob@dwms.local', '9560034318', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'POLY-MTI' LIMIT 1), 'Shaji Antony', 'Principal', 'shaji.antony@dwms.local', '9572323787', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'POLY-KKD' LIMIT 1), 'Lakshmi Devassy', 'Principal', 'lakshmi.devassy@dwms.local', '7869627720', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ITI-CHK' LIMIT 1), 'Priya Cherian', 'Principal', 'priya.cherian@dwms.local', '8704307149', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ITI-KLMS' LIMIT 1), 'Anitha Thomas', 'Principal', 'anitha.thomas@dwms.local', '6212336699', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ITI-WH' LIMIT 1), 'Geetha Varghese', 'Principal', 'geetha.varghese@dwms.local', '6658523331', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ITI-DHM' LIMIT 1), 'Hari Varghese', 'Principal', 'hari.varghese@dwms.local', '9506942121', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ASC-UCTVM' LIMIT 1), 'Girish Nambiar', 'Principal', 'girish.nambiar@dwms.local', '7155309085', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ASC-MAH' LIMIT 1), 'Suja Rajan', 'Principal', 'suja.rajan@dwms.local', '8022456157', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ASC-VICPKD' LIMIT 1), 'Pradeep Varghese', 'Principal', 'pradeep.varghese@dwms.local', '9774240311', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'ASC-BRENNEN' LIMIT 1), 'Sajeev Pillai', 'Principal', 'sajeev.pillai@dwms.local', '7723636988', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'MED-TVM' LIMIT 1), 'Smitha Sasidharan', 'Principal', 'smitha.sasidharan@dwms.local', '8239574260', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'MED-KTM' LIMIT 1), 'Prasad Das', 'Principal', 'prasad.das@dwms.local', '8385371642', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_edu, (SELECT id FROM offices WHERE code = 'MED-KKD' LIMIT 1), 'Deepa Devassy', 'Principal', 'deepa.devassy@dwms.local', '6655846982', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- Programme executives
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Smitha Namboothiri', 'Programme Executive, Knowledge Economy', 'smitha.namboothiri@dwms.local', '7645357198', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Santhosh Nair', 'Programme Executive, Skilling', 'santhosh.nair@dwms.local', '8985119647', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Asha Joseph', 'Programme Executive, Apprenticeships', 'asha.joseph@dwms.local', '8936472765', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Deepa Cherian', 'Programme Executive, Industry Liaison', 'deepa.cherian@dwms.local', '6387828811', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'DTE' LIMIT 1), 'Aparna Kurup', 'Programme Executive, Digital Literacy', 'aparna.kurup@dwms.local', '6899303620', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Hari Kurup', 'Programme Executive, Women in Tech', 'hari.kurup@dwms.local', '8014400185', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Deepa Gopinath', 'Programme Executive, Green Skills', 'deepa.gopinath@dwms.local', '6893198705', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'DME' LIMIT 1), 'Unni Krishnan', 'Programme Executive, Health Skilling', 'unni.krishnan@dwms.local', '6466262829', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'LSGD' LIMIT 1), 'Remya Das', 'Programme Executive, Rural Outreach', 'remya.das@dwms.local', '7898306391', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_prog, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Pradeep Rasheed', 'Programme Executive, Monitoring', 'pradeep.rasheed@dwms.local', '9376110107', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- Placement coordinators
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ENGG-CET' LIMIT 1), 'Shalini Cherian', 'Placement Coordinator', 'shalini.cherian@dwms.local', '9119686118', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ENGG-TKM' LIMIT 1), 'Smitha Kumar', 'Placement Coordinator', 'smitha.kumar@dwms.local', '9157472509', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ENGG-RSET' LIMIT 1), 'Noushad Sasidharan', 'Placement Coordinator', 'noushad.sasidharan@dwms.local', '6819926441', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ENGG-GECT' LIMIT 1), 'Shaji Vijayan', 'Placement Coordinator', 'shaji.vijayan@dwms.local', '9111116779', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'POLY-KLMS' LIMIT 1), 'Salim Menon', 'Placement Coordinator', 'salim.menon@dwms.local', '9281461857', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ITI-KLMS' LIMIT 1), 'Latha Rasheed', 'Placement Coordinator', 'latha.rasheed@dwms.local', '6572704672', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ASC-MAH' LIMIT 1), 'Shalini Abraham', 'Placement Coordinator', 'shalini.abraham@dwms.local', '9680014355', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ASC-FAROOK' LIMIT 1), 'Unni Nambiar', 'Placement Coordinator', 'unni.nambiar@dwms.local', '8467142069', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'MED-TVM' LIMIT 1), 'Anoop Rasheed', 'Placement Coordinator', 'anoop.rasheed@dwms.local', '6906243014', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'UNIV-DUK' LIMIT 1), 'Rajesh Bhaskaran', 'Placement Coordinator', 'rajesh.bhaskaran@dwms.local', '7764993657', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'UNIV-CUSAT' LIMIT 1), 'Vivek Bhaskaran', 'Placement Coordinator', 'vivek.bhaskaran@dwms.local', '6960374626', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_plac, (SELECT id FROM offices WHERE code = 'ENGG-MACE' LIMIT 1), 'Anitha Rasheed', 'Placement Coordinator', 'anitha.rasheed@dwms.local', '9443829613', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- District officials
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-THI' LIMIT 1), 'Thomas Sasidharan', 'District Employment Officer', 'thomas.sasidharan@dwms.local', '6118624310', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-KOL' LIMIT 1), 'Meera Das', 'District Employment Officer', 'meera.das@dwms.local', '6863486767', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-PAT' LIMIT 1), 'Unni Menon', 'District Employment Officer', 'unni.menon@dwms.local', '7306518441', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-ALA' LIMIT 1), 'Anoop Nambiar', 'District Employment Officer', 'anoop.nambiar@dwms.local', '8978025336', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-KOT' LIMIT 1), 'Indu Sasidharan', 'District Employment Officer', 'indu.sasidharan@dwms.local', '9368325937', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-IDU' LIMIT 1), 'Divya Namboothiri', 'District Employment Officer', 'divya.namboothiri@dwms.local', '7984864179', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-ERN' LIMIT 1), 'Krishnan Nambiar', 'District Employment Officer', 'krishnan.nambiar@dwms.local', '7533249434', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-THR' LIMIT 1), 'Nisha Rasheed', 'District Employment Officer', 'nisha.rasheed@dwms.local', '9839180448', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-PAL' LIMIT 1), 'Unni Sasidharan', 'District Employment Officer', 'unni.sasidharan@dwms.local', '8856616194', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-MAL' LIMIT 1), 'Geetha Namboothiri', 'District Employment Officer', 'geetha.namboothiri@dwms.local', '7706645975', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-KOZ' LIMIT 1), 'Nimmy Jacob', 'District Employment Officer', 'nimmy.jacob@dwms.local', '9345146296', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-WAY' LIMIT 1), 'Asha Varghese', 'District Employment Officer', 'asha.varghese@dwms.local', '8795912435', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-KAN' LIMIT 1), 'Gopakumar Krishnan', 'District Employment Officer', 'gopakumar.krishnan@dwms.local', '6270371025', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_dist, (SELECT id FROM offices WHERE code = 'DEO-KAS' LIMIT 1), 'Aparna Antony', 'District Employment Officer', 'aparna.antony@dwms.local', '6371833168', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- Job stations
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-TVM' LIMIT 1), 'Meera Jacob', 'Job Station Manager', 'meera.jacob@dwms.local', '9671453738', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-KLM' LIMIT 1), 'Rekha Iyer', 'Job Station Manager', 'rekha.iyer@dwms.local', '7252879352', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-PTA' LIMIT 1), 'Ajith Bhaskaran', 'Job Station Manager', 'ajith.bhaskaran@dwms.local', '8702623873', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-ALP' LIMIT 1), 'Jisha Abraham', 'Job Station Manager', 'jisha.abraham@dwms.local', '9699588452', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-KTM' LIMIT 1), 'Sajeev Nair', 'Job Station Manager', 'sajeev.nair@dwms.local', '8306961162', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-IDK' LIMIT 1), 'Nimmy Antony', 'Job Station Manager', 'nimmy.antony@dwms.local', '8959814069', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-EKM' LIMIT 1), 'Veena Antony', 'Job Station Manager', 'veena.antony@dwms.local', '8304966343', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-TSR' LIMIT 1), 'Jayan Jacob', 'Job Station Manager', 'jayan.jacob@dwms.local', '6255218334', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-PKD' LIMIT 1), 'Nimmy Pillai', 'Job Station Manager', 'nimmy.pillai@dwms.local', '7249258784', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-MLP' LIMIT 1), 'Lakshmi Thomas', 'Job Station Manager', 'lakshmi.thomas@dwms.local', '9821607292', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-KKD' LIMIT 1), 'Hari Abraham', 'Job Station Manager', 'hari.abraham@dwms.local', '8811335701', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-WYD' LIMIT 1), 'Anitha Joseph', 'Job Station Manager', 'anitha.joseph@dwms.local', '9579189725', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-KNR' LIMIT 1), 'Sreeja Rasheed', 'Job Station Manager', 'sreeja.rasheed@dwms.local', '9862171028', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_job, (SELECT id FROM offices WHERE code = 'JS-KSD' LIMIT 1), 'Shaji Chandran', 'Job Station Manager', 'shaji.chandran@dwms.local', '7340540077', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- SDPK centres
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-THI' LIMIT 1), 'Meera Bhaskaran', 'Centre Coordinator', 'meera.bhaskaran@dwms.local', '9915925396', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-KOL' LIMIT 1), 'Smitha Nair', 'Centre Coordinator', 'smitha.nair@dwms.local', '9512834943', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-ALA' LIMIT 1), 'Biju Jacob', 'Centre Coordinator', 'biju.jacob@dwms.local', '9007045058', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-KOT' LIMIT 1), 'Athira Vijayan', 'Centre Coordinator', 'athira.vijayan@dwms.local', '8264208142', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-ERN' LIMIT 1), 'Neethu Thomas', 'Centre Coordinator', 'neethu.thomas@dwms.local', '6899243395', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-THR' LIMIT 1), 'Athira Krishnan', 'Centre Coordinator', 'athira.krishnan@dwms.local', '9167618975', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-PAL' LIMIT 1), 'Anoop Balakrishnan', 'Centre Coordinator', 'anoop.balakrishnan@dwms.local', '6160171924', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-MAL' LIMIT 1), 'Jose Abraham', 'Centre Coordinator', 'jose.abraham@dwms.local', '9719657239', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-KOZ' LIMIT 1), 'Lakshmi Sasidharan', 'Centre Coordinator', 'lakshmi.sasidharan@dwms.local', '6873772325', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_sdpk, (SELECT id FROM offices WHERE code = 'SDPK-KAN' LIMIT 1), 'Remya Thomas', 'Centre Coordinator', 'remya.thomas@dwms.local', '6479949391', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- K-DISC officials
INSERT INTO users (role_id, group_id, office_id, name, designation, email, mobile, password, must_reset, is_active) VALUES
  (@r_admin, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Faisal Iyer', 'Member Secretary', 'faisal.iyer@dwms.local', '7460370972', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_admin, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Krishnan Namboothiri', 'Head, Knowledge Economy Mission', 'krishnan.namboothiri@dwms.local', '7199232657', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_admin, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Abdul Joseph', 'Mission Coordinator', 'abdul.joseph@dwms.local', '7051799388', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Ajith Balakrishnan', 'Data and Analytics Officer', 'ajith.balakrishnan@dwms.local', '6054130457', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_career, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Unni Panicker', 'Career Services Lead', 'unni.panicker@dwms.local', '7517570434', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_verify, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Anitha Jacob', 'Employer Verification Officer', 'anitha.jacob@dwms.local', '7546753422', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_skills, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Abdul Vijayan', 'Content Manager, Skilling', 'abdul.vijayan@dwms.local', '6967564393', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1),
  (@r_office, @g_kdisc, (SELECT id FROM offices WHERE code = 'KDISC' LIMIT 1), 'Soumya Pillai', 'Grievance Officer', 'soumya.pillai@dwms.local', '8423185268', '$2y$12$D9hOyZVSMOI52PwqVXLjxOWglCILyReFf7c9mWHpP9F/U.2Br6BOu', 1, 1)
ON DUPLICATE KEY UPDATE group_id = VALUES(group_id), office_id = VALUES(office_id),
  designation = VALUES(designation), role_id = VALUES(role_id);

-- Marker so the dataset can be recognised later.
INSERT INTO settings (setting_key, setting_value) VALUES ('demo_dataset', 'kerala-2026-09')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
