<?php partial('legal-doc', [
  'heading' => 'Accessibility statement',
  'sub'     => 'We want every citizen to be able to use DWMS 2.0, whatever device or assistive technology they use.',
  'updated' => date('d M Y', strtotime('-14 days')),
  'sections' => [
    ['Our commitment', [
      'DWMS 2.0 is built to meet WCAG 2.1 Level AA. Accessibility is treated as a functional requirement, not an afterthought — new screens are checked before they are released.',
    ]],
    ['What we have done', [
      [
        'Every page can be operated with a keyboard alone, and a visible focus ring shows where you are.',
        'A "skip to content" link is the first item on every page.',
        'Colour contrast meets the 4.5:1 ratio for body text, and colour is never the only way meaning is conveyed.',
        'Form fields have visible labels, and errors are announced in text next to the field they belong to.',
        'Images that carry meaning have text alternatives; decorative images are hidden from screen readers.',
        'The layout reflows to a single column at 320 pixels wide and supports 200% zoom without loss of content.',
      ],
    ]],
    ['Known limitations', [
      'Some documents uploaded by employers and training providers are PDFs that we did not author and that may not be tagged for screen readers. If you need an accessible version of any document, ask us and we will provide one within five working days.',
    ]],
    ['Tell us about a barrier', [
      'If something on this platform stops you from completing a task, write to us through the contact page with the page address and a description of the problem. We aim to respond within two working days.',
    ]],
  ],
]); ?>
