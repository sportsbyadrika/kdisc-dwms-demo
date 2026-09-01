-- ---------------------------------------------------------------------------
-- DWMS 2.0 — seed data
-- Default super-admin sign-in:  admin@dwms.local / Admin@12345
-- Demo employer sign-in:        hr@technova.in  / Employer@123
-- Demo job seeker sign-in:      seeker@dwms.local / Seeker@123
-- ---------------------------------------------------------------------------

INSERT INTO settings (setting_key, setting_value) VALUES
  ('site_title',      'DWMS 2.0'),
  ('site_tagline',    'Digital Workforce Management System'),
  ('contact_email',   'support@dwms.local'),
  ('contact_phone',   '+91 471 2321100'),
  ('contact_address', 'K-DISC, Thiruvananthapuram, Kerala 695004'),
  ('facebook_url',    '#'),
  ('twitter_url',     '#'),
  ('linkedin_url',    '#'),
  ('youtube_url',     '#'),
  ('about_short',     'DWMS 2.0 connects job seekers, employers and government departments on a single verified workforce platform.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ------------------------------------------------------------------- roles
INSERT INTO roles (name, slug, description, permissions, is_system) VALUES
  ('Super Administrator', 'super_admin', 'Full control over the platform', '["*"]', 1),
  ('Administrator', 'admin', 'Manages day-to-day operations', '["dashboard.view","users.manage","offices.manage","hero.manage","skills.manage","careers.manage","employers.verify","jobs.moderate","seekers.view","messages.view"]', 1),
  ('Office Manager', 'office_manager', 'Manages a single office and its staff', '["dashboard.view","users.manage","seekers.view","jobs.moderate","messages.view"]', 0),
  ('Skills Manager', 'skills_manager', 'Publishes skilling programmes', '["dashboard.view","skills.manage"]', 0),
  ('Career Services Manager', 'career_manager', 'Publishes career services', '["dashboard.view","careers.manage"]', 0),
  ('Verification Officer', 'verification_officer', 'Verifies employers and seeker documents', '["dashboard.view","employers.verify","seekers.view"]', 0)
ON DUPLICATE KEY UPDATE name = VALUES(name), permissions = VALUES(permissions);

-- ----------------------------------------------------------------- offices
INSERT INTO offices (id, parent_id, name, code, type, district, email, phone) VALUES
  (1, NULL, 'Directorate of Employment', 'HQ', 'office', 'Thiruvananthapuram', 'hq@dwms.local', '0471-2321100')
ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO offices (id, parent_id, name, code, type, district) VALUES
  (2, 1, 'Employment Department', 'HQ-EMP', 'department', 'Thiruvananthapuram'),
  (3, 1, 'Skill Development Department', 'HQ-SKL', 'department', 'Thiruvananthapuram'),
  (4, 2, 'Placement Section', 'HQ-EMP-PL', 'section', 'Thiruvananthapuram'),
  (5, 3, 'Training Section', 'HQ-SKL-TR', 'section', 'Thiruvananthapuram')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Default super admin — password: Admin@12345
INSERT INTO users (role_id, office_id, name, designation, email, mobile, password, is_active)
SELECT r.id, 1, 'System Administrator', 'Super Admin', 'admin@dwms.local', '9999999999',
       '$2y$10$msYdEiqxi/k3TSyxRVZ0cuMIPh0eFAuosR59B2bWWagmHIkBqTbBi', 1
FROM roles r WHERE r.slug = 'super_admin'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ------------------------------------------------------------- categories
INSERT INTO job_categories (name, slug, icon) VALUES
  ('Information Technology', 'information-technology', 'cpu'),
  ('Healthcare & Life Sciences', 'healthcare', 'heart'),
  ('Manufacturing & Engineering', 'manufacturing', 'cog'),
  ('Banking & Finance', 'banking-finance', 'bank'),
  ('Education & Training', 'education', 'book'),
  ('Hospitality & Tourism', 'hospitality', 'bed'),
  ('Retail & Sales', 'retail-sales', 'cart'),
  ('Logistics & Transport', 'logistics', 'truck'),
  ('Construction', 'construction', 'building'),
  ('Agriculture & Allied', 'agriculture', 'leaf')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO skill_categories (name, slug) VALUES
  ('Digital & IT Skills', 'digital-it'),
  ('Healthcare Skills', 'healthcare-skills'),
  ('Manufacturing & Technical', 'manufacturing-technical'),
  ('Language & Communication', 'language-communication'),
  ('Entrepreneurship', 'entrepreneurship'),
  ('Green & Sustainability', 'green-skills')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO career_service_categories (name, slug) VALUES
  ('Career Counselling', 'career-counselling'),
  ('Resume & Portfolio', 'resume-portfolio'),
  ('Interview Preparation', 'interview-preparation'),
  ('Overseas Placement', 'overseas-placement'),
  ('Self Employment Support', 'self-employment'),
  ('Aptitude & Psychometric', 'psychometric')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ------------------------------------------------------------ hero slides
INSERT INTO hero_slides (title, subtitle, image, cta_label, cta_url, sort_order, is_active) VALUES
  ('One profile. Every opportunity.', 'Register once and reach verified employers, skilling programmes and career services across the state.', NULL, 'Create your profile', '/register', 1, 1),
  ('Hiring made accountable.', 'Employers publish curated job sheets and reach verified, e-KYC completed candidates.', NULL, 'Post a job', '/employer/register', 2, 1),
  ('Skill up for what is next.', 'Government-backed skilling programmes with certification, stipends and placement support.', NULL, 'Browse skills', '/skills', 3, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- --------------------------------------------------------- demo employers
INSERT INTO employers (id, company_name, email, email_verified, password, ownership_type, industry, employee_range,
                       established_year, website, about, pan, gstin, city, district, state, pincode,
                       contact_person, contact_designation, contact_mobile, contact_email,
                       profile_step, profile_completed, status)
VALUES
 (1,'TechNova Solutions Pvt Ltd','hr@technova.in',1,'$2y$10$WyPXFvoWxQLsr.wkXHZTf.StQI7127KkqHDBVD9upTJbnQle4/HUG','private_limited','Information Technology','51-200',2014,'https://technova.example','Product engineering studio building data platforms for public sector clients.','AABCT1234F','32AABCT1234F1ZP','Kochi','Ernakulam','Kerala','682030','Meera Nair','HR Manager','9847012345','hr@technova.in',5,1,'verified'),
 (2,'Sahya Care Hospitals','careers@sahyacare.in',1,'$2y$10$WyPXFvoWxQLsr.wkXHZTf.StQI7127KkqHDBVD9upTJbnQle4/HUG','private_limited','Healthcare','501-1000',2003,'https://sahyacare.example','Multi-speciality hospital network across four districts.','AACCS9876K','32AACCS9876K1ZQ','Thrissur','Thrissur','Kerala','680001','Dr. Anil Kumar','Administrator','9847098765','careers@sahyacare.in',5,1,'verified'),
 (3,'Malabar Precision Works','jobs@malabarprecision.in',1,'$2y$10$WyPXFvoWxQLsr.wkXHZTf.StQI7127KkqHDBVD9upTJbnQle4/HUG','partnership','Manufacturing','201-500',1998,NULL,'Precision components manufacturer supplying automotive and aerospace OEMs.','AAFFM4567L',NULL,'Kozhikode','Kozhikode','Kerala','673005','Fathima Rasheed','Plant HR Lead','9946011223','jobs@malabarprecision.in',5,1,'verified')
ON DUPLICATE KEY UPDATE company_name = VALUES(company_name);

-- -------------------------------------------------------------- demo jobs
INSERT INTO jobs (employer_id, category_id, code, title, description, responsibilities, min_qualification,
                  skills_required, experience_min, experience_max, age_min, age_max, employment_type, work_mode,
                  vacancies, salary_min, salary_max, salary_period, job_location, district, selection_process,
                  benefits, contact_email, last_date, status, published_at)
VALUES
 (1,1,'JOB0001','Junior PHP Developer','Build and maintain citizen-facing web applications on a LAMP stack. You will work with a small product team shipping every fortnight.','Develop features in PHP and MySQL; write clean, reviewed code; support UAT and production releases.','ug','PHP, MySQL, JavaScript, Git, REST API',0,2,20,35,'full_time','on_site',5,25000,40000,'monthly','Infopark, Kochi','Ernakulam','Written test, technical interview, HR round','ESI, PF, health cover, learning allowance','hr@technova.in',DATE_ADD(CURDATE(), INTERVAL 30 DAY),'published',NOW()),
 (1,1,'JOB0002','Data Analyst','Turn departmental datasets into dashboards and decision notes for programme managers.','Build SQL models; maintain dashboards; publish monthly analytics briefs.','ug','SQL, Excel, Power BI, Python, Statistics',1,4,21,40,'full_time','hybrid',2,35000,55000,'monthly','Technopark, Thiruvananthapuram','Thiruvananthapuram','Case study, panel interview','PF, hybrid working, certification sponsorship','hr@technova.in',DATE_ADD(CURDATE(), INTERVAL 21 DAY),'published',NOW()),
 (2,2,'JOB0003','Staff Nurse','Provide bedside care in the multi-speciality inpatient wards on a three-shift rotation.','Patient monitoring, medication administration, documentation, attendant counselling.','diploma','Patient care, BLS, Documentation, Kerala Nurses Council registration',1,6,21,40,'full_time','on_site',18,22000,32000,'monthly','Sahya Care, Thrissur','Thrissur','Practical assessment, interview','Accommodation, ESI, PF, night shift allowance','careers@sahyacare.in',DATE_ADD(CURDATE(), INTERVAL 14 DAY),'published',NOW()),
 (2,2,'JOB0004','Medical Lab Technician','Run routine and specialised diagnostics in the central laboratory.','Sample collection, analyser operation, quality control, report validation support.','diploma','Phlebotomy, Biochemistry, Haematology, LIS',0,3,20,38,'full_time','on_site',4,18000,26000,'monthly','Sahya Care, Thrissur','Thrissur','Skill test, interview','ESI, PF, canteen','careers@sahyacare.in',DATE_ADD(CURDATE(), INTERVAL 25 DAY),'published',NOW()),
 (3,3,'JOB0005','CNC Machine Operator','Operate and set up CNC turning centres for precision automotive components.','Machine setup, tool change, in-process inspection, 5S upkeep.','iti','CNC turning, Fanuc controls, Micrometer, Drawing reading',2,8,21,45,'full_time','on_site',12,20000,30000,'monthly','Industrial Estate, Kozhikode','Kozhikode','Trade test, interview','Canteen, transport, PF, production incentive','jobs@malabarprecision.in',DATE_ADD(CURDATE(), INTERVAL 20 DAY),'published',NOW()),
 (3,3,'JOB0006','Quality Inspection Trainee','Twelve-month apprenticeship in the quality assurance department.','Dimensional inspection, gauge handling, documentation of non-conformance.','diploma','Metrology, Quality control, MS Office',0,1,18,28,'apprenticeship','on_site',8,12000,15000,'monthly','Industrial Estate, Kozhikode','Kozhikode','Written test, interview','Stipend, hostel, certification','jobs@malabarprecision.in',DATE_ADD(CURDATE(), INTERVAL 40 DAY),'published',NOW()),
 (1,1,'JOB0007','UI/UX Designer','Design accessible interfaces for public service platforms used across the state.','Wireframes, design systems, usability testing, developer handoff.','ug','Figma, Design systems, Accessibility, Prototyping',2,6,22,40,'full_time','remote',1,45000,70000,'monthly','Remote (Kerala)','Ernakulam','Portfolio review, design exercise, interview','Remote allowance, PF, annual bonus','hr@technova.in',DATE_ADD(CURDATE(), INTERVAL 28 DAY),'published',NOW()),
 (2,5,'JOB0008','Nursing Faculty (Part Time)','Deliver classroom and clinical instruction to first-year nursing students.','Lecture delivery, clinical supervision, internal assessment.','pg','Teaching, Nursing practice, Curriculum design',3,NULL,25,50,'part_time','on_site',3,28000,38000,'monthly','Sahya College of Nursing, Thrissur','Thrissur','Demo class, interview','Flexible hours, PF','careers@sahyacare.in',DATE_ADD(CURDATE(), INTERVAL 35 DAY),'published',NOW())
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ------------------------------------------------------ demo skilling data
INSERT INTO skill_programmes (category_id, title, provider, description, outcomes, eligibility, mode, level,
                              duration_value, duration_unit, fee, is_free, seats, district, venue, start_date,
                              contact_email, is_certified, status)
VALUES
 (1,'Full Stack Web Development (PHP & JavaScript)','Kerala Academy for Skills Excellence','Hands-on programme covering HTML, CSS, JavaScript, PHP and MySQL with a capstone project.','Build and deploy a database-driven web application; job-ready portfolio.','Plus Two or above, basic computer literacy','offline','beginner',12,'weeks',0,1,40,'Ernakulam','KASE Centre, Kakkanad',DATE_ADD(CURDATE(), INTERVAL 15 DAY),'skills@dwms.local',1,'published'),
 (1,'Data Analytics with Python','Digital University Kerala','Applied analytics using Python, pandas and visualisation tools with public-dataset case studies.','Clean, analyse and visualise real datasets; build a dashboard.','Any degree with basic mathematics','hybrid','intermediate',10,'weeks',4500,0,60,'Thiruvananthapuram','Technocity Campus',DATE_ADD(CURDATE(), INTERVAL 20 DAY),'skills@dwms.local',1,'published'),
 (2,'Geriatric Care Assistant','Kerala Health Skill Mission','Certified caregiving programme covering elder care, mobility support and emergency response.','Eligible for caregiver roles in hospitals and home-care agencies.','SSLC pass, age 18-40','offline','beginner',6,'months',0,1,30,'Thrissur','District Skill Centre',DATE_ADD(CURDATE(), INTERVAL 10 DAY),'skills@dwms.local',1,'published'),
 (3,'Advanced CNC Programming','Kerala Institute of Technology','Fanuc and Siemens programming, tooling and productivity improvement on live machines.','Independently programme and set up CNC turning and milling centres.','ITI/Diploma in mechanical trades','offline','advanced',8,'weeks',6000,0,20,'Kozhikode','KIT Workshop',DATE_ADD(CURDATE(), INTERVAL 25 DAY),'skills@dwms.local',1,'published'),
 (4,'Workplace English & Interview Communication','Additional Skill Acquisition Programme','Spoken English, business writing and interview communication with weekly practice labs.','Confident interview communication and professional written English.','Plus Two or above','online','beginner',45,'days',0,1,150,'Statewide','Online',DATE_ADD(CURDATE(), INTERVAL 7 DAY),'skills@dwms.local',1,'published'),
 (5,'Start Your Enterprise','Kerala Startup Mission','Idea validation, business model, statutory registration and funding readiness for first-time founders.','A validated business plan and a funding-ready pitch.','Any graduate or entrepreneur','hybrid','intermediate',4,'weeks',2500,0,50,'Ernakulam','Integrated Startup Complex',DATE_ADD(CURDATE(), INTERVAL 18 DAY),'skills@dwms.local',0,'published'),
 (6,'Solar PV Installation Technician','Energy Management Centre','Rooftop solar design, installation, commissioning and safety practice.','Work as a certified rooftop solar installation technician.','ITI Electrical/Electronics','offline','intermediate',8,'weeks',3000,0,25,'Thiruvananthapuram','EMC Campus',DATE_ADD(CURDATE(), INTERVAL 30 DAY),'skills@dwms.local',1,'published'),
 (2,'Emergency Medical Technician (Basic)','Kerala Health Skill Mission','Pre-hospital emergency care, trauma handling and ambulance operations.','Eligible for EMT roles with hospitals and ambulance services.','Plus Two with science','offline','beginner',3,'months',0,1,35,'Kollam','District Hospital Annexe',DATE_ADD(CURDATE(), INTERVAL 22 DAY),'skills@dwms.local',1,'published')
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO career_services (category_id, title, summary, description, provider, service_mode, audience,
                             is_free, fee, district, venue, schedule_note, contact_email, contact_phone, icon, status)
VALUES
 (1,'One-to-One Career Counselling','Talk to a certified counsellor about your next career step.','A 45-minute personal session that maps your qualification, interests and constraints to realistic career pathways, with a written action plan afterwards.','District Employment Exchange','hybrid','Students and job seekers aged 17-35',1,NULL,'All districts','District Employment Exchange','Weekdays, 10 am to 4 pm — by appointment','careers@dwms.local','0471-2321100','compass','published'),
 (2,'Resume Clinic','Get your resume reviewed by hiring practitioners.','Submit your resume and receive line-by-line feedback within three working days, plus an optional 20-minute review call covering formatting, keywords and impact statements.','DWMS Career Cell','online','All registered job seekers',1,NULL,'Statewide','Online','Rolling — submissions reviewed every Tuesday and Friday','careers@dwms.local','0471-2321101','document','published'),
 (3,'Mock Interview Sessions','Practise with a panel before the real thing.','Structured 30-minute mock interviews with a technical or HR panel, followed by a scored feedback sheet covering communication, domain depth and presentation.','DWMS Career Cell','hybrid','Candidates with a scheduled interview',1,NULL,'Ernakulam','Regional Career Centre, Kakkanad','Every Saturday, slots at 10 am / 12 pm / 3 pm','careers@dwms.local','0484-2345678','users','published'),
 (4,'Overseas Placement Guidance','Understand documentation, attestation and safe migration.','Guidance on country-specific requirements, recruiting agent verification, emigration clearance and pre-departure orientation for candidates exploring overseas employment.','Norka Roots','offline','Candidates exploring overseas employment',1,NULL,'Thiruvananthapuram','Norka Centre, Thycaud','Weekdays, 10 am to 5 pm','careers@dwms.local','0471-2332416','globe','published'),
 (5,'Self Employment Support Desk','Loan, licence and subsidy guidance for your own venture.','Help with scheme selection, project report preparation, bank linkage and statutory registration for micro-enterprises.','District Industries Centre','offline','Aspiring entrepreneurs',1,NULL,'All districts','District Industries Centre','Weekdays, 10 am to 4 pm','careers@dwms.local','0471-2300409','briefcase','published'),
 (6,'Aptitude & Psychometric Assessment','Know your strengths before you choose.','A validated aptitude and personality assessment with a detailed report mapping your profile to suitable job families and skilling routes.','DWMS Career Cell','online','Students and early-career job seekers',0,250,'Statewide','Online','On demand — report within 48 hours','careers@dwms.local','0471-2321102','chart','published')
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ------------------------------------------------------- demo job seeker
-- password: Seeker@123
INSERT INTO job_seekers (id, name, email, email_verified, mobile, mobile_verified, password, headline, gender, dob,
                         about, kyc_status, is_active)
VALUES (1,'Arjun Menon','seeker@dwms.local',1,'9847011111',1,'$2y$10$qWaIAtvbv3olyRJQ.hACCetE9X1ntmUxwQyigqotW8znavxUJ2Tva',
        'Computer Science graduate seeking a backend developer role','male','2001-06-14',
        'Fresh graduate with internship experience in PHP and MySQL, comfortable with Git and REST APIs.','not_started',1)
ON DUPLICATE KEY UPDATE name = VALUES(name);
