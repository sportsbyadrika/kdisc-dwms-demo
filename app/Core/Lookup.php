<?php
namespace App\Core;

/**
 * Shared option lists used by forms and filters.
 */
class Lookup
{
    public const DISTRICTS = [
        'Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha', 'Kottayam', 'Idukki',
        'Ernakulam', 'Thrissur', 'Palakkad', 'Malappuram', 'Kozhikode', 'Wayanad', 'Kannur', 'Kasaragod',
    ];

    public const QUALIFICATIONS = [
        'below_10' => 'Below 10th',
        'sslc'     => 'SSLC / 10th',
        'plus_two' => 'Plus Two / 12th',
        'iti'      => 'ITI',
        'diploma'  => 'Diploma',
        'ug'       => 'Graduation (UG)',
        'pg'       => 'Post Graduation (PG)',
        'phd'      => 'Doctorate (PhD)',
        'other'    => 'Other',
    ];

    /** The "any" option only makes sense on the job side. */
    public const JOB_QUALIFICATIONS = ['any' => 'Any qualification'] + self::QUALIFICATIONS;

    public const EMPLOYMENT_TYPES = [
        'full_time'      => 'Full time',
        'part_time'      => 'Part time',
        'contract'       => 'Contract',
        'internship'     => 'Internship',
        'apprenticeship' => 'Apprenticeship',
        'freelance'      => 'Freelance',
    ];

    public const WORK_MODES = [
        'on_site' => 'On site',
        'hybrid'  => 'Hybrid',
        'remote'  => 'Remote',
    ];

    public const SALARY_PERIODS = [
        'monthly' => 'per month',
        'annual'  => 'per year',
        'daily'   => 'per day',
        'hourly'  => 'per hour',
    ];

    public const OWNERSHIP_TYPES = [
        'proprietorship'  => 'Sole proprietorship',
        'partnership'     => 'Partnership firm',
        'llp'             => 'Limited liability partnership',
        'private_limited' => 'Private limited company',
        'public_limited'  => 'Public limited company',
        'government'      => 'Government body',
        'psu'             => 'Public sector undertaking',
        'ngo'             => 'NGO / Society / Trust',
        'cooperative'     => 'Co-operative society',
        'other'           => 'Other',
    ];

    public const EMPLOYEE_RANGES = ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'];

    public const DOC_TYPES = [
        'driving_license' => 'Driving licence',
        'pan_card'        => 'PAN card',
        'passport'        => 'Passport',
        'voter_id'        => 'Voter ID',
        'ration_card'     => 'Ration card',
        'photo'           => 'Passport size photograph',
        'other'           => 'Other document',
    ];

    public const KYC_METHODS = [
        'aadhaar'         => 'Aadhaar (e-KYC with OTP)',
        'pan'             => 'PAN card',
        'driving_license' => 'Driving licence',
        'passport'        => 'Passport',
    ];

    public const PROFICIENCY = [
        'beginner'     => 'Beginner',
        'intermediate' => 'Intermediate',
        'advanced'     => 'Advanced',
        'expert'       => 'Expert',
    ];

    public const APPLICATION_STATUS = [
        'applied'     => 'Applied',
        'shortlisted' => 'Shortlisted',
        'interview'   => 'Interview',
        'selected'    => 'Selected',
        'rejected'    => 'Not selected',
        'withdrawn'   => 'Withdrawn',
    ];

    public const ACHIEVEMENT_CATEGORIES = [
        'award'       => 'Award / Recognition',
        'publication' => 'Publication',
        'sports'      => 'Sports',
        'arts'        => 'Arts & Culture',
        'social'      => 'Social service',
        'patent'      => 'Patent',
        'other'       => 'Other',
    ];

    public const PERMISSIONS = [
        'dashboard.view'   => 'View the admin dashboard',
        'users.manage'     => 'Create and manage users',
        'roles.manage'     => 'Create and manage roles',
        'offices.manage'   => 'Manage offices, departments and sections',
        'hero.manage'      => 'Manage home page hero panel',
        'skills.manage'    => 'Manage skilling programmes',
        'careers.manage'   => 'Manage career services',
        'employers.verify' => 'Verify employer registrations',
        'jobs.moderate'    => 'Moderate published job titles',
        'seekers.view'     => 'View job seeker profiles',
        'messages.view'    => 'Read contact enquiries',
        'settings.manage'  => 'Change site settings',
    ];

    public static function label(array $map, ?string $key, string $fallback = '—'): string
    {
        return $map[$key] ?? $fallback;
    }
}
