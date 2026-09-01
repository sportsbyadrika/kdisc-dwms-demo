<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Mailer;
use App\Core\Otp;

/**
 * Employer sign-up (e-mail verification then account details) and sign-in.
 * The company profile itself is a separate wizard — see EmployerProfileController.
 */
class EmployerAuthController
{
    public function register(): void
    {
        if (Auth::check('employer')) {
            redirect('/employer/dashboard');
        }
        $reg  = $_SESSION['ereg'] ?? [];
        $step = 1;
        if (!empty($reg['email'])) {
            $step = empty($reg['verified']) ? 2 : 3;
        }
        if (input('step') === '1') {
            $step = 1;
        }

        view('employer.register', [
            'pageTitle' => 'Register your organisation',
            'step'      => $step,
            'reg'       => $reg,
            'cooldown'  => !empty($reg['email']) ? Otp::cooldownRemaining('employer_register', $reg['email']) : 0,
            'demoCode'  => $_SESSION['ereg_demo_code'] ?? null,
        ]);
    }

    public function sendEmailOtp(): void
    {
        verify_csrf();
        $email  = strtolower((string) input('email'));
        $errors = validate(['email' => 'required|email|max:150'], ['email' => $email]);

        if (!$errors && DB::value('SELECT id FROM employers WHERE email = ?', [$email])) {
            $errors['email'] = 'An employer account already exists with this e-mail address.';
        }
        if ($errors) {
            flash_errors($errors, ['email' => $email]);
            redirect('/employer/register');
        }

        $wait = Otp::cooldownRemaining('employer_register', $email);
        if ($wait > 0) {
            flash('info', 'A one-time password was just sent. Please wait ' . $wait . ' seconds.');
            $_SESSION['ereg'] = ['email' => $email, 'verified' => false];
            redirect('/employer/register');
        }

        $code = Otp::issue('email', 'employer_register', $email);
        Mailer::otp($email, $code, 'verify your organisation e-mail address');
        $_SESSION['ereg'] = ['email' => $email, 'verified' => false];
        if (Otp::demoMode()) {
            $_SESSION['ereg_demo_code'] = $code;
        }
        flash('success', 'We have sent a 6-digit one-time password to ' . $email . '.');
        redirect('/employer/register');
    }

    public function verifyEmailOtp(): void
    {
        verify_csrf();
        $email = $_SESSION['ereg']['email'] ?? null;
        if (!$email) {
            redirect('/employer/register');
        }
        $result = Otp::verify('employer_register', $email, (string) input('code'));
        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/employer/register');
        }
        $_SESSION['ereg']['verified'] = true;
        unset($_SESSION['ereg_demo_code']);
        flash('success', 'E-mail verified. Now create your login.');
        redirect('/employer/register');
    }

    public function complete(): void
    {
        verify_csrf();
        $reg = $_SESSION['ereg'] ?? [];
        if (empty($reg['email']) || empty($reg['verified'])) {
            flash('error', 'Please verify your e-mail address first.');
            redirect('/employer/register');
        }

        $data = [
            'company_name'   => input('company_name'),
            'contact_person' => input('contact_person'),
            'contact_mobile' => preg_replace('/\D/', '', (string) input('contact_mobile')),
            'password'       => input('password'),
            'password_confirmation' => input('password_confirmation'),
            'terms'          => input('terms'),
        ];
        $errors = validate([
            'company_name'   => 'required|min:3|max:180',
            'contact_person' => 'required|min:3|max:120',
            'contact_mobile' => 'required|mobile',
            'password'       => 'required|min:8|max:64',
            'password_confirmation' => 'required|same:password',
            'terms'          => 'accepted',
        ], $data);

        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/employer/register');
        }

        $id = DB::insert('employers', [
            'company_name'   => $data['company_name'],
            'email'          => $reg['email'],
            'email_verified' => 1,
            'password'       => password_hash($data['password'], PASSWORD_BCRYPT),
            'contact_person' => $data['contact_person'],
            'contact_mobile' => $data['contact_mobile'],
            'contact_email'  => $reg['email'],
            'profile_step'   => 2,
            'status'         => 'pending',
        ]);

        unset($_SESSION['ereg'], $_SESSION['ereg_demo_code']);
        $employer = DB::first('SELECT * FROM employers WHERE id = ?', [$id]);
        Auth::login('employer', $employer);
        Auth::refresh('employer', ['name' => $employer['company_name'], 'photo' => null]);
        log_activity('employer', $id, 'register', 'Employer account created');

        flash('success', 'Welcome. Complete your organisation profile so the verification desk can review it.');
        redirect('/employer/profile');
    }

    /* --------------------------------------------------------- sign in */

    public function loginForm(): void
    {
        if (Auth::check('employer')) {
            redirect('/employer/dashboard');
        }
        view('auth.login', [
            'pageTitle'  => 'Employer login',
            'guardIcon'  => 'building',
            'heading'    => 'Employer sign in',
            'sub'        => 'Publish job titles, screen applicants and manage your hiring pipeline.',
            'action'     => '/employer/login',
            'registerPath'  => '/employer/register',
            'registerLabel' => 'Register your organisation',
            'asideTitle' => 'Hire from a verified talent pool',
            'asideSub'   => 'Candidates on DWMS complete e-KYC and build a structured profile before they apply.',
            'points'     => [
                ['shield-check', 'Verified candidates', 'Identity and documents verified once, reused across applications.'],
                ['clipboard', 'Structured applications', 'Qualification, experience and certification in a consistent shape.'],
                ['chart', 'Hiring insight', 'Views, applications and shortlisting rates for every job title.'],
            ],
            'altLinks'   => [
                ['Job seeker login', '/login'],
                ['Officials login', '/official/login'],
            ],
        ]);
    }

    public function login(): void
    {
        verify_csrf();
        $email    = strtolower((string) input('email'));
        $password = (string) input('password');

        $employer = DB::first('SELECT * FROM employers WHERE email = ?', [$email]);
        if (!$employer || !password_verify($password, $employer['password'])) {
            flash('error', 'The e-mail address or password is not correct.');
            flash_errors(['email' => 'Check your e-mail address and password.'], ['email' => $email]);
            redirect('/employer/login');
        }
        if ($employer['status'] === 'suspended') {
            flash('error', 'This account has been suspended. Please contact the verification desk.');
            redirect('/employer/login');
        }

        DB::update('employers', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $employer['id']]);
        Auth::login('employer', $employer);
        Auth::refresh('employer', ['name' => $employer['company_name'], 'photo' => $employer['logo']]);
        log_activity('employer', (int) $employer['id'], 'login', 'Signed in');

        flash('success', 'Welcome back, ' . $employer['company_name'] . '.');
        redirect($employer['profile_completed'] ? '/employer/dashboard' : '/employer/profile');
    }
}
