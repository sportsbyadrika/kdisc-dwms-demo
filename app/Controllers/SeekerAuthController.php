<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Core\Mailer;
use App\Core\Otp;

/**
 * Job seeker registration wizard, sign-in and password recovery.
 *
 * The wizard keeps its state in $_SESSION['reg'] so a half-finished
 * registration never creates a database row:
 *   step 1  e-mail address        -> a one-time password is issued
 *   step 2  verify the OTP        -> the address is marked verified
 *   step 3  basic profile         -> the account is created and signed in
 */
class SeekerAuthController
{
    /* ------------------------------------------------------------ wizard */

    public function register(): void
    {
        if (Auth::check('seeker')) {
            redirect('/dashboard');
        }
        $reg = $_SESSION['reg'] ?? [];
        $step = 1;
        if (!empty($reg['email'])) {
            $step = empty($reg['verified']) ? 2 : 3;
        }
        // Allow stepping back to change the address.
        if (input('step') === '1') {
            $step = 1;
        }

        view('auth.register', [
            'pageTitle' => 'Create your job seeker account',
            'step'      => $step,
            'reg'       => $reg,
            'cooldown'  => !empty($reg['email']) ? Otp::cooldownRemaining('seeker_register', $reg['email']) : 0,
            'demoCode'  => $_SESSION['reg_demo_code'] ?? null,
        ]);
    }

    /** Step 1 — capture the e-mail address and issue an OTP. */
    public function sendEmailOtp(): void
    {
        verify_csrf();
        $email  = strtolower((string) input('email'));
        $errors = validate(['email' => 'required|email|max:150'], ['email' => $email]);

        if (!$errors && DB::value('SELECT id FROM job_seekers WHERE email = ?', [$email])) {
            $errors['email'] = 'An account already exists with this e-mail address. Please sign in instead.';
        }
        if ($errors) {
            flash_errors($errors, ['email' => $email]);
            redirect('/register');
        }

        $wait = Otp::cooldownRemaining('seeker_register', $email);
        if ($wait > 0) {
            flash('info', 'A one-time password was just sent. Please wait ' . $wait . ' seconds before requesting another.');
            $_SESSION['reg'] = ['email' => $email, 'verified' => false];
            redirect('/register');
        }

        $code = Otp::issue('email', 'seeker_register', $email);
        Mailer::otp($email, $code, 'verify your e-mail address');

        $_SESSION['reg'] = ['email' => $email, 'verified' => false];
        if (Otp::demoMode()) {
            $_SESSION['reg_demo_code'] = $code;
        }
        flash('success', 'We have sent a 6-digit one-time password to ' . $email . '.');
        redirect('/register');
    }

    /** Step 2 — check the OTP. */
    public function verifyEmailOtp(): void
    {
        verify_csrf();
        $email = $_SESSION['reg']['email'] ?? null;
        if (!$email) {
            redirect('/register');
        }

        $result = Otp::verify('seeker_register', $email, (string) input('code'));
        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/register');
        }

        $_SESSION['reg']['verified'] = true;
        unset($_SESSION['reg_demo_code']);
        flash('success', 'E-mail address verified. Now set up your profile.');
        redirect('/register');
    }

    /** Step 3 — create the account and sign in. */
    public function completeRegistration(): void
    {
        verify_csrf();
        $reg = $_SESSION['reg'] ?? [];
        if (empty($reg['email']) || empty($reg['verified'])) {
            flash('error', 'Please verify your e-mail address first.');
            redirect('/register');
        }

        $data = [
            'name'    => input('name'),
            'mobile'  => preg_replace('/\D/', '', (string) input('mobile')),
            'password' => input('password'),
            'password_confirmation' => input('password_confirmation'),
            'terms'   => input('terms'),
        ];
        $errors = validate([
            'name'     => 'required|min:3|max:120',
            'mobile'   => 'required|mobile',
            'password' => 'required|min:8|max:64',
            'password_confirmation' => 'required|same:password',
            'terms'    => 'accepted',
        ], $data);

        if (!$errors && DB::value('SELECT id FROM job_seekers WHERE mobile = ?', [$data['mobile']])) {
            $errors['mobile'] = 'This mobile number is already registered.';
        }

        $photo = null;
        if (!$errors) {
            $uploadError = null;
            $photo = store_upload('photo', 'photos', ['jpg', 'jpeg', 'png', 'webp'], $uploadError);
            if ($uploadError) {
                $errors['photo'] = $uploadError;
            }
        }

        if ($errors) {
            flash_errors($errors, ['name' => $data['name'], 'mobile' => $data['mobile']]);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/register');
        }

        $id = DB::insert('job_seekers', [
            'name'           => $data['name'],
            'email'          => $reg['email'],
            'email_verified' => 1,
            'mobile'         => $data['mobile'],
            'password'       => password_hash($data['password'], PASSWORD_BCRYPT),
            'photo'          => $photo,
            'profile_score'  => 25,
        ]);

        unset($_SESSION['reg'], $_SESSION['reg_demo_code']);

        $seeker = DB::first('SELECT * FROM job_seekers WHERE id = ?', [$id]);
        Auth::login('seeker', $seeker);
        Auth::refresh('seeker', ['photo' => $photo]);
        log_activity('seeker', $id, 'register', 'Job seeker account created');

        flash('success', 'Welcome to DWMS, ' . $data['name'] . '. Complete your e-KYC and profile to start applying.');
        redirect('/dashboard');
    }

    /* --------------------------------------------------------- sign in */

    public function loginForm(): void
    {
        if (Auth::check('seeker')) {
            redirect('/dashboard');
        }
        // Arriving from "Apply" on a vacancy: name it so the visitor knows the
        // job has not been lost.
        $applyJob = null;
        if ($jobId = (int) input('job')) {
            $applyJob = DB::first("SELECT id, title FROM jobs WHERE id = ? AND status = 'published'", [$jobId]);
            if ($applyJob) {
                $_SESSION['pending_apply'] = (int) $applyJob['id'];
            }
        }

        view('auth.login', [
            'pageTitle' => 'Job seeker login',
            'guard'     => 'seeker',
            'applyJob'  => $applyJob,
            'heading'   => $applyJob ? 'Sign in to apply' : 'Sign in to your job seeker account',
            'sub'       => $applyJob
                ? 'Sign in to apply for ' . $applyJob['title'] . '. We have kept it for you.'
                : 'Apply for vacancies, track applications and manage your profile.',
            'action'    => '/login',
            'forgotPath'   => '/forgot-password',
            'registerPath' => '/register',
            'registerLabel' => 'New here? Create a job seeker account',
            'altLinks'  => [
                ['Employer login', '/employer/login'],
                ['Officials login', '/official/login'],
            ],
        ]);
    }

    public function login(): void
    {
        verify_csrf();
        $email    = strtolower((string) input('email'));
        $password = (string) input('password');

        $seeker = DB::first('SELECT * FROM job_seekers WHERE email = ?', [$email]);
        if (!$seeker || !password_verify($password, $seeker['password'])) {
            flash('error', 'The e-mail address or password is not correct.');
            flash_errors(['email' => 'Check your e-mail address and password.'], ['email' => $email]);
            redirect('/login');
        }
        if (!(int) $seeker['is_active']) {
            flash('error', 'This account has been deactivated. Please contact support.');
            redirect('/login');
        }

        DB::update('job_seekers', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $seeker['id']]);
        Auth::login('seeker', $seeker);
        Auth::refresh('seeker', ['photo' => $seeker['photo']]);
        log_activity('seeker', (int) $seeker['id'], 'login', 'Signed in');

        // A job parked by the "apply before signing in" flow — either posted by
        // the login modal or stashed in the session by JobController::apply().
        $jobId = (int) (input('wishlist_job_id') ?: ($_SESSION['pending_apply'] ?? 0));
        unset($_SESSION['pending_apply']);
        if ($jobId) {
            $exists = DB::value("SELECT id FROM jobs WHERE id = ? AND status = 'published'", [$jobId]);
            if ($exists) {
                DB::run(
                    'INSERT IGNORE INTO wishlists (seeker_id, job_id) VALUES (?, ?)',
                    [$seeker['id'], $jobId]
                );
                flash('info', 'The job you were looking at is waiting in your saved list.');
                redirect('/jobs/' . $jobId);
            }
        }

        $intended = input('intended') ?: ($_SESSION['intended'] ?? null);
        unset($_SESSION['intended']);
        flash('success', 'Welcome back, ' . $seeker['name'] . '.');
        redirect($intended ?: '/dashboard');
    }

    public function logout(): void
    {
        verify_csrf();
        $me = Auth::current();
        if ($me) {
            log_activity($me['guard'], (int) $me['id'], 'logout', 'Signed out');
        }
        Auth::logout();
        session_regenerate_id(true);
        flash('success', 'You have been signed out.');
        redirect('/');
    }

    /* ------------------------------------------------- password recovery */

    public function forgotForm(): void
    {
        view('auth.forgot', [
            'pageTitle' => 'Reset your password',
            'stage'     => !empty($_SESSION['pwreset']['email']) ? 'verify' : 'request',
            'email'     => $_SESSION['pwreset']['email'] ?? '',
            'demoCode'  => $_SESSION['pwreset_demo_code'] ?? null,
        ]);
    }

    public function forgotSend(): void
    {
        verify_csrf();
        $email  = strtolower((string) input('email'));
        $errors = validate(['email' => 'required|email'], ['email' => $email]);
        if ($errors) {
            flash_errors($errors, ['email' => $email]);
            redirect('/forgot-password');
        }

        $seeker = DB::first('SELECT id, name FROM job_seekers WHERE email = ?', [$email]);
        // Always report the same outcome so the form cannot be used to discover accounts.
        if ($seeker && Otp::cooldownRemaining('seeker_reset', $email) === 0) {
            $code = Otp::issue('email', 'seeker_reset', $email);
            Mailer::otp($email, $code, 'reset your password');
            if (Otp::demoMode()) {
                $_SESSION['pwreset_demo_code'] = $code;
            }
        }
        $_SESSION['pwreset'] = ['email' => $email];
        flash('success', 'If an account exists for ' . $email . ', a one-time password is on its way.');
        redirect('/forgot-password');
    }

    public function forgotReset(): void
    {
        verify_csrf();
        $email = $_SESSION['pwreset']['email'] ?? null;
        if (!$email) {
            redirect('/forgot-password');
        }

        $data = [
            'code'     => input('code'),
            'password' => input('password'),
            'password_confirmation' => input('password_confirmation'),
        ];
        $errors = validate([
            'code'     => 'required|digits:6',
            'password' => 'required|min:8|max:64',
            'password_confirmation' => 'required|same:password',
        ], $data);
        if ($errors) {
            flash_errors($errors);
            redirect('/forgot-password');
        }

        $result = Otp::verify('seeker_reset', $email, $data['code']);
        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/forgot-password');
        }

        DB::update(
            'job_seekers',
            ['password' => password_hash($data['password'], PASSWORD_BCRYPT)],
            'email = :email',
            ['email' => $email]
        );
        unset($_SESSION['pwreset'], $_SESSION['pwreset_demo_code']);
        flash('success', 'Your password has been reset. Please sign in.');
        redirect('/login');
    }

}
