<?php partial('legal-doc', [
  'heading' => 'Terms of use',
  'sub'     => 'The conditions on which job seekers, employers and officials may use DWMS 2.0.',
  'updated' => date('d M Y', strtotime('-30 days')),
  'sections' => [
    ['Acceptance', [
      'By registering for or using DWMS 2.0 you agree to these terms. If you use the platform on behalf of an organisation, you confirm that you are authorised to bind that organisation.',
    ]],
    ['Eligibility and accounts', [
      [
        'One account per person. Duplicate job seeker registrations may be merged or removed.',
        'You are responsible for keeping your password confidential and for all activity under your account.',
        'Information you enter must be accurate and current. Deliberately false information may lead to suspension.',
        'Employer accounts are activated only after the verification desk approves the statutory details submitted.',
      ],
    ]],
    ['Acceptable use', [
      'You may use the platform to look for work, to hire, or to administer the employment services you are assigned to. You may not:',
      [
        'Post vacancies that do not exist, or that charge candidates a fee for consideration.',
        'Publish discriminatory criteria other than those permitted by law.',
        'Scrape, bulk-download or resell candidate data.',
        'Attempt to gain access to accounts, data or administrative functions you are not entitled to.',
        'Upload malware, or content that is unlawful, defamatory or infringing.',
      ],
    ]],
    ['Employer obligations', [
      'Employers must keep each published job title accurate, close it when filled, and update application statuses so candidates know where they stand. Employers may use candidate data only to assess that candidate for the role applied to.',
    ]],
    ['Content you upload', [
      'You keep ownership of your resume, certificates and other uploads. You grant us the licence needed to store them, show them to the employers you apply to, and display them back to you.',
    ]],
    ['Availability', [
      'The platform is provided on an "as available" basis. Scheduled maintenance is announced in advance where practicable. We are not liable for losses arising from interruptions outside our reasonable control.',
    ]],
    ['No guarantee of employment', [
      'DWMS 2.0 is an intermediary. Listing on the platform is not an offer of employment, and selection decisions rest entirely with the employer.',
    ]],
    ['Suspension and closure', [
      'Accounts that breach these terms may be suspended or closed. Where the breach is minor we notify you first and allow a reasonable period to correct it.',
    ]],
    ['Changes to these terms', [
      'We may update these terms. Material changes are notified on the platform at least 14 days before they take effect. Continued use after that date constitutes acceptance.',
    ]],
    ['Governing law', [
      'These terms are governed by the laws of India, and the courts at the seat of the administering department have exclusive jurisdiction.',
    ]],
  ],
]); ?>
