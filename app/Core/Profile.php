<?php
namespace App\Core;

/**
 * Job seeker profile completeness — one place that decides what "complete"
 * means, used by the dashboard meter and by the application eligibility check.
 */
class Profile
{
    /** @return array{score:int,items:array<int,array{label:string,done:bool,weight:int,path:string}>} */
    public static function completeness(int $seekerId): array
    {
        $s = Database::first('SELECT * FROM job_seekers WHERE id = ?', [$seekerId]);
        if (!$s) {
            return ['score' => 0, 'items' => []];
        }

        $count = static function (string $table) use ($seekerId): int {
            return (int) Database::value("SELECT COUNT(*) FROM `$table` WHERE seeker_id = ?", [$seekerId]);
        };

        $items = [
            ['label' => 'Name, photograph and mobile number', 'weight' => 15, 'path' => '/dashboard/profile',
             'done' => (bool) ($s['name'] && $s['mobile'] && $s['photo'])],
            ['label' => 'E-mail address verified', 'weight' => 10, 'path' => '/dashboard/profile',
             'done' => (bool) $s['email_verified']],
            ['label' => 'e-KYC completed', 'weight' => 15, 'path' => '/dashboard/kyc',
             'done' => $s['kyc_status'] === 'verified'],
            ['label' => 'Address of communication', 'weight' => 10, 'path' => '/dashboard/address',
             'done' => (bool) Database::value("SELECT id FROM seeker_addresses WHERE seeker_id = ? AND address_type = 'communication'", [$seekerId])],
            ['label' => 'Identity or other proof uploaded', 'weight' => 10, 'path' => '/dashboard/documents',
             'done' => $count('seeker_documents') > 0],
            ['label' => 'Resume uploaded', 'weight' => 10, 'path' => '/dashboard/resume',
             'done' => $count('seeker_resumes') > 0],
            ['label' => 'Qualification added', 'weight' => 15, 'path' => '/dashboard/qualifications',
             'done' => $count('seeker_qualifications') > 0],
            ['label' => 'Skills added', 'weight' => 5, 'path' => '/dashboard/skills',
             'done' => $count('seeker_skills') >= 3],
            ['label' => 'Experience, certification or achievement', 'weight' => 10, 'path' => '/dashboard/experience',
             'done' => $count('seeker_experiences') + $count('seeker_certifications') + $count('seeker_achievements') > 0],
        ];

        $score = 0;
        foreach ($items as $i) {
            if ($i['done']) {
                $score += $i['weight'];
            }
        }
        return ['score' => min(100, $score), 'items' => $items];
    }

    /** Persist the score so it can be sorted on without recomputing. */
    public static function refreshScore(int $seekerId): int
    {
        $score = self::completeness($seekerId)['score'];
        Database::update('job_seekers', ['profile_score' => $score], 'id = :id', ['id' => $seekerId]);
        return $score;
    }

    /** Highest qualification level held, ordered by the ladder below. */
    public static function highestQualification(int $seekerId): ?string
    {
        $ladder = ['below_10', 'sslc', 'plus_two', 'iti', 'diploma', 'ug', 'pg', 'phd'];
        $levels = Database::all('SELECT DISTINCT level FROM seeker_qualifications WHERE seeker_id = ?', [$seekerId]);
        $best = null;
        $bestRank = -1;
        foreach ($levels as $l) {
            $rank = array_search($l['level'], $ladder, true);
            if ($rank !== false && $rank > $bestRank) {
                $bestRank = $rank;
                $best = $l['level'];
            }
        }
        return $best;
    }

    /** Total professional experience in years, from the recorded spells. */
    public static function totalExperienceYears(int $seekerId): float
    {
        $rows = Database::all('SELECT from_date, to_date, is_current FROM seeker_experiences WHERE seeker_id = ?', [$seekerId]);
        $months = 0;
        foreach ($rows as $r) {
            if (!$r['from_date']) {
                continue;
            }
            $from = new \DateTime($r['from_date']);
            $to   = $r['is_current'] || !$r['to_date'] ? new \DateTime() : new \DateTime($r['to_date']);
            if ($to < $from) {
                continue;
            }
            $diff = $from->diff($to);
            $months += $diff->y * 12 + $diff->m;
        }
        return round($months / 12, 1);
    }
}
