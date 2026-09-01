<?php partial('legal-doc', [
  'heading' => 'Privacy policy',
  'sub'     => 'How DWMS 2.0 collects, uses, shares and protects the personal data of job seekers, employers and officials.',
  'updated' => date('d M Y', strtotime('-30 days')),
  'sections' => [
    ['Information we collect', [
      'We collect the information you provide when you register and build your profile, and the technical information your browser sends when you use the platform.',
      [
        'Identity and contact details — name, photograph, date of birth, gender, e-mail address and mobile number.',
        'Verification data — the e-KYC reference returned after a successful verification, and the document type and masked number of any proof you upload.',
        'Profile data — addresses, qualifications, experience, certifications, achievements, skills and resumes.',
        'Activity data — jobs you view, save and apply to, programmes you enrol in and services you request.',
        'Technical data — IP address, browser type and session timestamps, retained for security and audit purposes.',
      ],
    ]],
    ['How your information is used', [
      'Your data is used to operate the platform: to authenticate you, to match your profile against vacancies and programmes, to share applications with the employers you apply to, and to let departmental officials verify the records they are responsible for.',
      'We also use aggregated, de-identified statistics to plan skilling and employment programmes. These statistics never identify an individual.',
    ]],
    ['Aadhaar and e-KYC consent', [
      'Aadhaar-based e-KYC is optional and is performed only after you tick the consent statement shown on the e-KYC screen. Consent is recorded with a timestamp.',
      'We store the verification outcome and a masked reference. The full Aadhaar number is not retained in the platform database once verification completes.',
      'Details returned by the e-KYC service are shared only with the government department named on the consent screen, and only for the purpose of verifying your registration.',
      'You may withdraw consent by writing to the contact address below. Withdrawing consent removes the verified status from your profile.',
    ]],
    ['Who your information is shared with', [
      [
        'Employers — when you apply to a vacancy, that employer sees your profile, resume and the answers in your application. They do not see your identity document numbers.',
        'Departmental officials — officials assigned to your office or district may view your profile to verify documents and provide employment services.',
        'Skilling and service providers — when you enrol in a programme or request a service, your contact details are shared with that provider.',
        'Nobody else. We do not sell personal data, and we do not share it with advertisers.',
      ],
    ]],
    ['How long we keep it', [
      'An active profile is retained for as long as your account exists. If your account stays inactive for 36 months we notify you before archiving the profile.',
      'Application records are retained for five years to support audit and grievance redressal. Verification logs are retained for the period required by the relevant department.',
    ]],
    ['How we protect it', [
      'Passwords are stored as salted hashes and are never readable by staff. Sessions expire after a period of inactivity and all form submissions are protected against cross-site request forgery.',
      'Uploaded files are stored outside the executable path, and access to administrative screens is controlled by role-based permissions.',
    ]],
    ['Your rights', [
      [
        'Access — view everything held about you from your dashboard.',
        'Correction — edit your profile at any time; corrections take effect immediately.',
        'Withdrawal of consent — withdraw e-KYC consent, which removes verified status.',
        'Account closure — request closure by writing to the grievance officer; we confirm within 30 days.',
      ],
    ]],
    ['Contact and grievances', [
      'Write to the grievance officer using the contact form or the address published on the contact page. We acknowledge every complaint within two working days and aim to close it within 30 days.',
    ]],
  ],
]); ?>
