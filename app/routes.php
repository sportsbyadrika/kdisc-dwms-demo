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
