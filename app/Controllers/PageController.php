<?php
namespace App\Controllers;

use App\Core\Database as DB;

class PageController
{
    public function about(): void      { view('pages.about', ['pageTitle' => 'About DWMS 2.0']); }
    public function privacy(): void    { view('pages.privacy', ['pageTitle' => 'Privacy policy']); }
    public function terms(): void      { view('pages.terms', ['pageTitle' => 'Terms of use']); }
    public function faq(): void        { view('pages.faq', ['pageTitle' => 'Frequently asked questions']); }
    public function accessibility(): void { view('pages.accessibility', ['pageTitle' => 'Accessibility statement']); }
    public function sitemap(): void    { view('pages.sitemap', ['pageTitle' => 'Sitemap']); }

    public function employers(): void
    {
        $stats = [
            'seekers' => (int) DB::value('SELECT COUNT(*) FROM job_seekers WHERE is_active = 1'),
            'jobs'    => (int) DB::value("SELECT COUNT(*) FROM jobs WHERE status = 'published'"),
            'apps'    => (int) DB::value('SELECT COUNT(*) FROM applications'),
        ];
        view('pages.employers', ['pageTitle' => 'For employers', 'stats' => $stats]);
    }

    public function contact(): void
    {
        view('pages.contact', ['pageTitle' => 'Contact us']);
    }

    public function contactSubmit(): void
    {
        verify_csrf();
        $data = [
            'name'    => input('name'),
            'email'   => input('email'),
            'phone'   => input('phone'),
            'subject' => input('subject'),
            'message' => input('message'),
        ];
        $errors = validate([
            'name'    => 'required|min:2|max:120',
            'email'   => 'required|email',
            'phone'   => 'mobile',
            'subject' => 'max:180',
            'message' => 'required|min:10|max:2000',
        ], $data);

        // Honeypot — bots fill every field they can see.
        if (input('website')) {
            flash('success', 'Thank you. Your message has been received.');
            redirect('/contact');
        }

        if ($errors) {
            flash_errors($errors, $data);
            flash('error', 'Please correct the highlighted fields.');
            redirect('/contact');
        }

        DB::insert('contact_messages', $data);
        flash('success', 'Thank you. Your message has been received — we usually reply within two working days.');
        redirect('/contact');
    }
}
