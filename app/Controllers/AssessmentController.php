<?php
namespace App\Controllers;

/**
 * Assessment tests offered through partner platforms.
 *
 * Every test is hosted by the partner, not by DWMS, so each card leads to an
 * interstitial that names the destination before the visitor leaves the site.
 * Partners live in one array here — add or retire one by editing this list.
 */
class AssessmentController
{
    private const PARTNERS = [
        'tcs-ion' => [
            'name'     => 'TCS iON',
            'tagline'  => 'Aptitude and employability',
            'summary'  => 'A proctored aptitude and employability test used by recruiters across India. Covers quantitative ability, reasoning, verbal ability and domain knowledge, and issues a shareable score report.',
            'covers'   => ['Quantitative aptitude', 'Logical reasoning', 'Verbal ability', 'Domain knowledge'],
            'url'      => 'https://www.tcsion.com/',
            'icon'     => 'chart',
            'tone'     => 'from-brand-500 to-brand-700',
        ],
        'foundit' => [
            'name'     => 'foundit',
            'tagline'  => 'Career and skill assessments',
            'summary'  => 'Skill and personality assessments from the job platform formerly known as Monster India. Results sit alongside your profile and help match you to the roles you are ready for.',
            'covers'   => ['Role-based skill tests', 'Personality profiling', 'Career readiness'],
            'url'      => 'https://www.foundit.in/',
            'icon'     => 'target',
            'tone'     => 'from-emerald-500 to-emerald-700',
        ],
        'english-score' => [
            'name'     => 'EnglishScore',
            'tagline'  => 'English proficiency',
            'summary'  => 'The British Council mobile English test. Grammar, listening, reading and vocabulary in about half an hour, with a certificate employers recognise.',
            'covers'   => ['Grammar', 'Listening', 'Reading', 'Vocabulary'],
            'url'      => 'https://www.englishscore.com/',
            'icon'     => 'globe',
            'tone'     => 'from-amber-500 to-amber-600',
        ],
    ];

    public function index(): void
    {
        view('assessments.index', [
            'pageTitle'       => 'Assessment tests',
            'metaDescription' => 'Take an aptitude, skill or English proficiency assessment through the DWMS 2.0 partner platforms — TCS iON, foundit and EnglishScore.',
            'partners'        => self::PARTNERS,
        ]);
    }

    /** Interstitial shown before handing the visitor to a partner site. */
    public function leave(string $slug): void
    {
        $partner = self::PARTNERS[$slug] ?? null;
        if (!$partner) {
            abort(404, 'That assessment partner is not listed.');
        }

        view('assessments.leave', [
            'pageTitle' => 'Leaving DWMS 2.0',
            'noIndex'   => true,
            'slug'      => $slug,
            'partner'   => $partner,
            'host'      => (string) parse_url($partner['url'], PHP_URL_HOST),
        ]);
    }
}
