<?php
namespace App\Controllers;

use App\Core\Database as DB;
use App\Core\Lookup;

class HomeController
{
    public function index(): void
    {
        $slides = DB::all('SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order, id LIMIT 6');

        $stats = [
            'jobs'      => (int) DB::value("SELECT COUNT(*) FROM jobs WHERE status = 'published' AND (last_date IS NULL OR last_date >= CURDATE())"),
            'employers' => (int) DB::value("SELECT COUNT(*) FROM employers WHERE status = 'verified'"),
            'seekers'   => (int) DB::value('SELECT COUNT(*) FROM job_seekers WHERE is_active = 1'),
            'skills'    => (int) DB::value("SELECT COUNT(*) FROM skill_programmes WHERE status = 'published'"),
            'services'  => (int) DB::value("SELECT COUNT(*) FROM career_services WHERE status = 'published'"),
        ];

        $latestJobs = DB::all(
            "SELECT j.id, j.title, j.job_location, j.district, j.salary_min, j.salary_max, j.last_date,
                    j.employment_type, e.company_name, e.logo
             FROM jobs j
             JOIN employers e ON e.id = j.employer_id
             WHERE j.status = 'published' AND (j.last_date IS NULL OR j.last_date >= CURDATE())
             ORDER BY j.published_at DESC, j.id DESC
             LIMIT 6"
        );

        $topSkills = DB::all(
            "SELECT id, title, provider, mode, fee, is_free, district
             FROM skill_programmes WHERE status = 'published'
             ORDER BY start_date IS NULL, start_date ASC, id DESC LIMIT 4"
        );

        $services = DB::all(
            "SELECT id, title, summary, service_mode, is_free, icon
             FROM career_services WHERE status = 'published' ORDER BY id ASC LIMIT 4"
        );

        view('home.index', [
            'slides'     => $slides,
            'stats'      => $stats,
            'latestJobs' => $latestJobs,
            'topSkills'  => $topSkills,
            'services'   => $services,
            'districts'  => Lookup::DISTRICTS,
        ]);
    }
}
