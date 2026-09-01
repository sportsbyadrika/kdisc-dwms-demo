<?php
namespace App\Controllers;

use App\Core\Database as DB;
use App\Core\Lookup;
use App\Core\Otp;
use App\Core\Profile;

/**
 * e-KYC for job seekers.
 *
 * Aadhaar is the primary route: the seeker enters the Aadhaar number, records
 * an explicit consent to share the Aadhaar demographic details with the
 * assigned government department, requests an OTP and submits it.
 *
 * The UIDAI integration is deliberately deferred — sendOtp() and verify()
 * currently issue and check a locally generated OTP. Everything around them
 * (consent capture and timestamp, masking, audit trail, status transitions)
 * is final, so switching to the real gateway means replacing the two marked
 * blocks only. The full Aadhaar number is never written to the database.
 */
class KycController extends SeekerBaseController
{
    public function show(): void
    {
        $department = null;
        if ($this->seeker['kyc_department_id']) {
            $department = DB::first('SELECT id, name, type FROM offices WHERE id = ?', [$this->seeker['kyc_department_id']]);
        }
        $departments = DB::all("SELECT id, name, type FROM offices WHERE is_active = 1 ORDER BY type = 'office' DESC, name");

        $this->shell('jobseeker.kyc', [
            'pageTitle'   => 'e-KYC',
            'methods'     => Lookup::KYC_METHODS,
            'department'  => $department,
            'departments' => $departments,
            'demoCode'    => $_SESSION['kyc_demo_code'] ?? null,
            'pendingRef'  => $_SESSION['kyc_pending'] ?? null,
            'cooldown'    => isset($_SESSION['kyc_pending']['identifier'])
                ? Otp::cooldownRemaining('aadhaar_kyc', $_SESSION['kyc_pending']['identifier'])
                : 0,
        ]);
    }

    /** Step 1 — capture the Aadhaar number, the consent and issue an OTP. */
    public function sendOtp(): void
    {
        verify_csrf();
        $aadhaar = preg_replace('/\D/', '', (string) input('aadhaar'));
        $data = [
            'aadhaar'       => $aadhaar,
            'consent'       => input('consent'),
            'department_id' => input('department_id'),
        ];
        $errors = validate([
            'aadhaar'       => 'required|digits:12',
            'consent'       => 'accepted',
            'department_id' => 'required|numeric',
        ], $data);

        if (!$errors && !$this->verhoeffValid($aadhaar)) {
            $errors['aadhaar'] = 'That does not look like a valid Aadhaar number. Please check and try again.';
        }
        if (!$errors && !DB::value('SELECT id FROM offices WHERE id = ? AND is_active = 1', [$data['department_id']])) {
            $errors['department_id'] = 'Choose the department the details may be shared with.';
        }
        if ($errors) {
            flash_errors($errors);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/dashboard/kyc');
        }

        $masked = 'XXXX XXXX ' . substr($aadhaar, -4);
        $wait   = Otp::cooldownRemaining('aadhaar_kyc', $masked);
        if ($wait > 0) {
            flash('info', 'An OTP was just requested. Please wait ' . $wait . ' seconds before requesting another.');
            redirect('/dashboard/kyc');
        }

        // -------- replace with the UIDAI OTP request when the gateway is live
        $code = Otp::issue('aadhaar', 'aadhaar_kyc', $masked, [
            'seeker_id'     => $this->id(),
            'department_id' => (int) $data['department_id'],
            'last4'         => substr($aadhaar, -4),
        ]);
        if (Otp::demoMode()) {
            $_SESSION['kyc_demo_code'] = $code;
        }
        // -------- end of the stubbed gateway call

        DB::update('job_seekers', [
            'kyc_status'        => 'pending',
            'kyc_method'        => 'aadhaar',
            'kyc_ref'           => $masked,
            'kyc_consent'       => 1,
            'kyc_consent_at'    => date('Y-m-d H:i:s'),
            'kyc_department_id' => (int) $data['department_id'],
        ], 'id = :id', ['id' => $this->id()]);

        $_SESSION['kyc_pending'] = ['identifier' => $masked, 'masked' => $masked];
        log_activity('seeker', $this->id(), 'kyc_otp_requested', 'Aadhaar OTP requested for ' . $masked);

        flash('success', 'A one-time password has been sent to the mobile number registered with Aadhaar ' . $masked . '.');
        redirect('/dashboard/kyc');
    }

    /** Step 2 — verify the OTP and mark the profile verified. */
    public function verify(): void
    {
        verify_csrf();
        $pending = $_SESSION['kyc_pending'] ?? null;
        if (!$pending) {
            flash('error', 'Please request a one-time password first.');
            redirect('/dashboard/kyc');
        }

        $code   = (string) input('code');
        $errors = validate(['code' => 'required|digits:6'], ['code' => $code]);
        if ($errors) {
            flash_errors($errors);
            redirect('/dashboard/kyc');
        }

        // -------- replace with the UIDAI OTP verification when the gateway is live
        $result = Otp::verify('aadhaar_kyc', $pending['identifier'], $code);
        // -------- end of the stubbed gateway call

        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/dashboard/kyc');
        }

        DB::update('job_seekers', [
            'kyc_status'      => 'verified',
            'kyc_verified_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $this->id()]);

        unset($_SESSION['kyc_pending'], $_SESSION['kyc_demo_code']);
        Profile::refreshScore($this->id());
        log_activity('seeker', $this->id(), 'kyc_verified', 'Aadhaar e-KYC completed for ' . $pending['masked']);

        flash('success', 'Your e-KYC is complete. Your profile now carries a verified badge.');
        redirect('/dashboard/kyc');
    }

    /** Abandon a half-finished attempt. */
    public function cancel(): void
    {
        verify_csrf();
        unset($_SESSION['kyc_pending'], $_SESSION['kyc_demo_code']);
        if ($this->seeker['kyc_status'] === 'pending') {
            DB::update('job_seekers', ['kyc_status' => 'not_started'], 'id = :id', ['id' => $this->id()]);
        }
        flash('info', 'The e-KYC attempt has been cancelled.');
        redirect('/dashboard/kyc');
    }

    /**
     * Aadhaar numbers carry a Verhoeff check digit; validating it locally stops
     * obvious typos before a request ever reaches the gateway.
     */
    private function verhoeffValid(string $number): bool
    {
        static $d = [
            [0,1,2,3,4,5,6,7,8,9],[1,2,3,4,0,6,7,8,9,5],[2,3,4,0,1,7,8,9,5,6],
            [3,4,0,1,2,8,9,5,6,7],[4,0,1,2,3,9,5,6,7,8],[5,9,8,7,6,0,4,3,2,1],
            [6,5,9,8,7,1,0,4,3,2],[7,6,5,9,8,2,1,0,4,3],[8,7,6,5,9,3,2,1,0,4],
            [9,8,7,6,5,4,3,2,1,0],
        ];
        static $p = [
            [0,1,2,3,4,5,6,7,8,9],[1,5,7,6,2,8,3,0,9,4],[5,8,0,3,7,9,6,1,4,2],
            [8,9,1,6,0,4,3,5,2,7],[9,4,5,3,1,2,6,8,7,0],[4,2,8,6,5,7,3,9,0,1],
            [2,7,9,3,8,0,6,4,1,5],[7,0,4,6,9,1,3,2,5,8],
        ];
        if (!preg_match('/^[2-9]\d{11}$/', $number)) {
            return false; // Aadhaar never starts with 0 or 1
        }
        $c      = 0;
        $digits = array_reverse(str_split($number));
        foreach ($digits as $i => $digit) {
            $c = $d[$c][$p[$i % 8][(int) $digit]];
        }
        return $c === 0;
    }
}
