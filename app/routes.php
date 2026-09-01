<?php
/**
 * Application routes. Every URL is extension-less and resolved by index.php.
 *
 * @var \App\Core\Router $router
 */

/* ------------------------------------------------------------- installer */
$router->get('/setup',          'SetupController@index');
$router->post('/setup/install', 'SetupController@install');

/* ---------------------------------------------------------------- public */
$router->get('/',               'HomeController@index');
$router->get('/about',          'PageController@about');
$router->get('/employers',      'PageController@employers');
$router->get('/faq',            'PageController@faq');
$router->get('/privacy',        'PageController@privacy');
$router->get('/terms',          'PageController@terms');
$router->get('/accessibility',  'PageController@accessibility');
$router->get('/sitemap',        'PageController@sitemap');
$router->get('/contact',        'PageController@contact');
$router->post('/contact',       'PageController@contactSubmit');

/* ---------------------------------------------- public: jobs search */
$router->get('/jobs',                 'JobController@index');
$router->get('/jobs/{id}',            'JobController@show');
$router->post('/jobs/{id}/apply',     'JobController@apply');
$router->post('/jobs/save/{id}',      'JobController@save');

/* --------------------------------------------- public: skilling */
$router->get('/skills',               'SkillController@index');
$router->get('/skills/{id}',          'SkillController@show');
$router->post('/skills/{id}/enrol',   'SkillController@enrol');

/* --------------------------------------- public: career services */
$router->get('/career-services',              'CareerServiceController@index');
$router->get('/career-services/{id}',         'CareerServiceController@show');
$router->post('/career-services/{id}/request', 'CareerServiceController@request');

/* ------------------------------------------------- job seeker: auth */
$router->get('/register',           'SeekerAuthController@register');
$router->post('/register/email',    'SeekerAuthController@sendEmailOtp');
$router->post('/register/verify',   'SeekerAuthController@verifyEmailOtp');
$router->post('/register/complete', 'SeekerAuthController@completeRegistration');
$router->get('/login',              'SeekerAuthController@loginForm');
$router->post('/login',             'SeekerAuthController@login');
$router->post('/logout',            'SeekerAuthController@logout');
$router->get('/forgot-password',    'SeekerAuthController@forgotForm');
$router->post('/forgot-password',   'SeekerAuthController@forgotSend');
$router->post('/forgot-password/reset', 'SeekerAuthController@forgotReset');

/* -------------------------------------------- job seeker: dashboard */
$router->get('/dashboard',           'SeekerController@dashboard');
$router->get('/dashboard/password',  'SeekerController@passwordForm');
$router->post('/dashboard/password', 'SeekerController@passwordUpdate');

/* ------------------------------------------------ job seeker: e-KYC */
$router->get('/dashboard/kyc',           'KycController@show');
$router->post('/dashboard/kyc/send-otp', 'KycController@sendOtp');
$router->post('/dashboard/kyc/verify',   'KycController@verify');
$router->post('/dashboard/kyc/cancel',   'KycController@cancel');

/* ---------------------------- job seeker: applications & saved jobs */
$router->get('/dashboard/applications', 'SeekerController@applications');
$router->post('/dashboard/applications/{id}/withdraw', 'SeekerController@withdraw');
$router->get('/dashboard/saved',        'SeekerController@saved');
$router->post('/dashboard/saved/{id}/remove', 'SeekerController@unsave');

/* --------------------------------------------- job seeker: profile */
$router->get('/dashboard/profile',   'SeekerProfileController@profile');
$router->post('/dashboard/profile',  'SeekerProfileController@profileUpdate');
$router->get('/dashboard/address',   'SeekerProfileController@address');
$router->post('/dashboard/address',  'SeekerProfileController@addressUpdate');
$router->get('/dashboard/resume',    'SeekerProfileController@resume');
$router->post('/dashboard/resume',   'SeekerProfileController@resumeUpload');
$router->post('/dashboard/resume/{id}/primary', 'SeekerProfileController@resumePrimary');
$router->post('/dashboard/resume/{id}/delete',  'SeekerProfileController@resumeDelete');

// Qualifications, experience, certifications, achievements, skills and
// documents share one engine driven by App\Core\Sections.
$router->post('/dashboard/{section}/save',         'SeekerProfileController@recordSave');
$router->post('/dashboard/{section}/{id}/delete',  'SeekerProfileController@recordDelete');
$router->get('/dashboard/{section}',               'SeekerProfileController@records');

/* ---------------------------------------------------- employer: auth */
$router->get('/employer/register',           'EmployerAuthController@register');
$router->post('/employer/register/email',    'EmployerAuthController@sendEmailOtp');
$router->post('/employer/register/verify',   'EmployerAuthController@verifyEmailOtp');
$router->post('/employer/register/complete', 'EmployerAuthController@complete');
$router->get('/employer/login',              'EmployerAuthController@loginForm');
$router->post('/employer/login',             'EmployerAuthController@login');

/* ----------------------------------------------- employer: dashboard */
$router->get('/employer/dashboard',    'EmployerController@dashboard');
$router->get('/employer/password',     'EmployerController@passwordForm');
$router->post('/employer/password',    'EmployerController@passwordUpdate');

/* ------------------------------------------------- employer: profile */
$router->get('/employer/profile',      'EmployerProfileController@show');
$router->post('/employer/profile',     'EmployerProfileController@save');
$router->get('/employer/documents',    'EmployerProfileController@documents');
$router->post('/employer/documents',   'EmployerProfileController@documentStore');
$router->post('/employer/documents/{id}/delete', 'EmployerProfileController@documentDelete');

/* -------------------------------------- employer: job curation sheets */
$router->get('/employer/jobs',                  'EmployerJobController@index');
$router->get('/employer/jobs/create',           'EmployerJobController@create');
$router->post('/employer/jobs/create',          'EmployerJobController@store');
$router->get('/employer/jobs/{id}/edit',        'EmployerJobController@edit');
$router->post('/employer/jobs/{id}/edit',       'EmployerJobController@update');
$router->post('/employer/jobs/{id}/publish',    'EmployerJobController@publish');
$router->post('/employer/jobs/{id}/close',      'EmployerJobController@close');
$router->post('/employer/jobs/{id}/reopen',     'EmployerJobController@reopen');
$router->post('/employer/jobs/{id}/delete',     'EmployerJobController@destroy');
$router->get('/employer/jobs/{id}/applicants',  'EmployerJobController@applicants');

/* -------------------------------------------- employer: applications */
$router->get('/employer/applications', 'EmployerJobController@allApplications');
$router->post('/employer/applications/{id}/status', 'EmployerJobController@updateApplication');

/* ---------------------------------------------------- officials: auth */
$router->get('/official/login',   'OfficialAuthController@loginForm');
$router->post('/official/login',  'OfficialAuthController@login');

/* ----------------------------------------------- officials: dashboard */
$router->get('/official/dashboard',  'OfficialController@dashboard');
$router->get('/official/password',   'OfficialController@passwordForm');
$router->post('/official/password',  'OfficialController@passwordUpdate');

/* ---------------------------------- officials: verification & registry */
$router->get('/official/employers',            'OfficialController@employers');
$router->get('/official/employers/{id}',       'OfficialController@employerShow');
$router->post('/official/employers/{id}/decide', 'OfficialController@employerDecide');
$router->get('/official/jobs',                 'OfficialController@jobs');
$router->post('/official/jobs/{id}/moderate',  'OfficialController@jobModerate');
$router->get('/official/seekers',              'OfficialController@seekers');
$router->get('/official/seekers/{id}',         'OfficialController@seekerShow');
$router->post('/official/documents/{id}/verify', 'OfficialController@seekerVerifyDocument');
$router->get('/official/messages',             'OfficialController@messages');
$router->post('/official/messages/{id}/read',  'OfficialController@messageRead');
$router->get('/official/settings',             'OfficialController@settings');
$router->post('/official/settings',            'OfficialController@settingsSave');

/* ------------------------------- officials: offices, users and roles */
$router->get('/official/offices',              'OfficialAdminController@offices');
$router->post('/official/offices',             'OfficialAdminController@officeSave');
$router->post('/official/offices/{id}/delete', 'OfficialAdminController@officeDelete');
$router->get('/official/users',                'OfficialAdminController@users');
$router->post('/official/users',               'OfficialAdminController@userSave');
$router->post('/official/users/{id}/reset-password', 'OfficialAdminController@userResetPassword');
$router->post('/official/users/{id}/deactivate',     'OfficialAdminController@userDelete');
$router->get('/official/roles',                'OfficialAdminController@roles');
$router->post('/official/roles',               'OfficialAdminController@roleSave');
$router->post('/official/roles/{id}/delete',   'OfficialAdminController@roleDelete');

/* ------------------------------------------------- officials: content */
// hero | skills | careers, all driven by App\Core\Content.
$router->get('/official/{section}',                'OfficialContentController@index');
$router->post('/official/{section}',               'OfficialContentController@save');
$router->post('/official/{section}/{id}/status',   'OfficialContentController@status');
$router->post('/official/{section}/{id}/delete',   'OfficialContentController@delete');
