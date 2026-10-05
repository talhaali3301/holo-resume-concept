<?php

declare(strict_types=1);

/**
 * Holo Resume content.
 *
 * This file is the only place you need to edit to replace the demo portfolio.
 * Every entry shipped with the project is sample content. It is not a claim
 * about a real client, employer, date, metric, or testimonial.
 *
 * Set "sample" => false on the identity, and on each project, skill, and
 * milestone, only when that entry is your own verified information.
 * While identity.sample is true, keep milestone periods as "Unverified".
 *
 * Links:
 * - Use https URLs only. Invalid, empty, and non-https links are omitted.
 * - Leave demo_url and repository_url unset until you have a real URL.
 * - Images must be a site path under /images/ that exists in public/, or an https URL.
 *
 * Slugs should be lowercase words separated by hyphens. They become the
 * shareable URLs /projects/{slug} and /skills/{slug}.
 */

return [
    'identity' => [
        'sample' => true,
        'brand' => 'Holo',
        'product' => 'Holo Resume',
        'name' => null,
        'role' => 'Web application developer',
        'stack' => 'Laravel / Vue / Product Engineering',
        'headline' => 'A developer portfolio you can walk through.',
        'lede' => 'Building web applications, interfaces, and digital products.',
        'body' => 'This profile is concept content. It shows how the space behaves. It does not describe a real client history, employer, or measured outcome. Replace it in content/portfolio.php.',
        'availability' => 'Concept note. Add your real availability, location, or current focus here.',
    ],

    'contact' => [
        'sample' => true,
        'email' => null,
        'headline' => 'Work with me',
        'body' => '',
        'links' => [
            // ['label' => 'GitHub', 'url' => 'https://github.com/your-account'],
            // ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/your-profile'],
        ],
    ],

    'projects' => [
        [
            'slug' => 'operations-console',
            'title' => 'Operations Console',
            'category' => 'Internal tools',
            'label' => 'A queue of requests, owners, and release state',
            'summary' => 'A sample workspace for scanning a queue of requests, owners, and release state.',
            'purpose' => 'Show a dense operational product that stays calm enough to read in a minute.',
            'built' => 'A filterable queue, a detail sheet, and status shown in text, not colour alone.',
            'role' => 'Sample role: interface structure and frontend implementation. Replace with your actual responsibility.',
            'technologies' => ['Laravel', 'Vue', 'Inertia.js', 'TypeScript', 'Tailwind CSS'],
            'features' => [
                'A filterable queue with a keyboard-reachable review path',
                'Status shown with text, not colour alone',
                'A detail sheet that returns focus when it closes',
            ],
            'status' => 'concept',
            'sample' => true,
            'skills' => ['laravel', 'vue', 'inertia', 'typescript', 'tailwind', 'accessibility', 'testing', 'product-interfaces'],
            'case_study' => 'Sample case study. The console is a fictional exercise in arranging operational information so a busy person can see what needs attention. Replace this with what you actually built, the constraints you worked under, and only the outcomes you can verify.',
        ],
        [
            'slug' => 'booking-workspace',
            'title' => 'Booking Workspace',
            'category' => 'Scheduling',
            'label' => 'Availability, holds, and confirmed sessions',
            'summary' => 'A sample scheduling surface for availability, holds, and confirmed sessions.',
            'purpose' => 'Make a calendar workflow understandable without a wall of form fields.',
            'built' => 'Day and week views, a hold-then-confirm flow, and a review step before booking.',
            'role' => 'Sample role: application flow and interface implementation.',
            'technologies' => ['Laravel', 'Vue', 'Inertia.js', 'PostgreSQL', 'Tailwind CSS'],
            'features' => [
                'Day and week layouts that collapse cleanly on a phone',
                'Holds distinguished from confirmed sessions in text',
                'A review step before a booking is committed',
            ],
            'status' => 'concept',
            'sample' => true,
            'skills' => ['laravel', 'vue', 'inertia', 'postgresql', 'tailwind', 'product-interfaces', 'api-design'],
            'case_study' => 'Sample case study. This workspace is a fictional scheduling product used to demonstrate structure, empty states, and a clear primary action. Do not treat it as a launched client product.',
        ],
        [
            'slug' => 'content-studio',
            'title' => 'Content Studio',
            'category' => 'Editorial',
            'label' => 'Draft, review, and publish structured pages',
            'summary' => 'A sample editorial desk for drafting, reviewing, and publishing structured pages.',
            'purpose' => 'Keep a long-form editing task legible for writers and reviewers.',
            'built' => 'A draft-review-published path and a reading column with a comfortable measure.',
            'role' => 'Sample role: information architecture and interface implementation.',
            'technologies' => ['Vue', 'TypeScript', 'Tailwind CSS'],
            'features' => [
                'A draft, review, and published path with plain-language states',
                'A reading column that stays within a comfortable measure',
                'Keyboard access to the primary editing actions',
            ],
            'status' => 'in_progress',
            'sample' => true,
            'skills' => ['vue', 'typescript', 'tailwind', 'information-architecture', 'accessibility', 'product-interfaces'],
            'case_study' => 'Sample case study. The studio is an unfinished fictional concept. The status “In progress” describes the sample, not a real engagement.',
        ],
        [
            'slug' => 'inventory-board',
            'title' => 'Inventory Board',
            'category' => 'Inventory',
            'label' => 'Stock positions, thresholds, and replenishment',
            'summary' => 'A sample board for stock positions, thresholds, and incoming replenishment.',
            'purpose' => 'Turn a table of quantities into something a person can act on.',
            'built' => 'Thresholds labelled in words, a compact phone layout, and a note on each adjustment.',
            'role' => 'Sample role: data shaping and interface implementation.',
            'technologies' => ['Laravel', 'Vue', 'PostgreSQL'],
            'features' => [
                'Thresholds labeled in words as well as colour',
                'A compact phone layout that does not require horizontal scrolling',
                'An activity note attached to each adjustment',
            ],
            'status' => 'concept',
            'sample' => true,
            'skills' => ['laravel', 'vue', 'postgresql', 'api-design', 'testing', 'product-interfaces'],
            'case_study' => 'Sample case study. No warehouse, client, or stock figure is represented here. Use this entry as a pattern for describing a real operational tool later.',
        ],
        [
            'slug' => 'support-inbox',
            'title' => 'Support Inbox',
            'category' => 'Support',
            'label' => 'Assign, pause, and resolve conversations',
            'summary' => 'A sample inbox for assigning, pausing, and resolving customer conversations.',
            'purpose' => 'Help a support team see the next conversation without losing the thread.',
            'built' => 'Explicit assignment and resolution actions, and filters that work without hover.',
            'role' => 'Sample role: interface implementation with an emphasis on keyboard use.',
            'technologies' => ['Vue', 'TypeScript', 'Inertia.js', 'Tailwind CSS'],
            'features' => [
                'Assignment and resolution as explicit actions',
                'A conversation column with a sticky composer region',
                'Filters that remain available without hover',
            ],
            'status' => 'archived',
            'sample' => true,
            'skills' => ['vue', 'typescript', 'inertia', 'tailwind', 'accessibility', 'information-architecture'],
            'case_study' => 'Sample case study. Archived, in this demo, means the fictional concept is no longer the focus. It is not a retired production system.',
        ],
        [
            'slug' => 'spatial-preview',
            'title' => 'Spatial Preview',
            'category' => 'Spatial UI',
            'label' => 'A product explained in a small 3D view',
            'summary' => 'A sample scene for explaining a product in a small, navigable 3D view.',
            'purpose' => 'Add a spatial explanation without making the 3D view the only way to understand the work.',
            'built' => 'A shallow 3D scene paired with a matching written explanation beside it.',
            'role' => 'Sample role: scene composition and the accessible reading path beside it.',
            'technologies' => ['Vue', 'TypeScript', 'Three.js'],
            'features' => [
                'A shallow scene with a matching text explanation',
                'Pointer look-around that is never required',
                'A static composition when motion is reduced or WebGL is unavailable',
            ],
            'status' => 'concept',
            'sample' => true,
            'skills' => ['vue', 'typescript', 'three', 'accessibility', 'product-interfaces'],
            'case_study' => 'Sample case study. This entry exists so the gallery can demonstrate a Three.js project. It is not a shipped spatial product.',
        ],
    ],

    'skills' => [
        [
            'slug' => 'laravel',
            'title' => 'Laravel',
            'category' => 'technology',
            'layer' => 'application',
            'description' => 'Sample skill. Server-side application structure, routing, and the pages this portfolio delivers.',
            'sample' => true,
        ],
        [
            'slug' => 'vue',
            'title' => 'Vue',
            'category' => 'technology',
            'layer' => 'interface',
            'description' => 'Sample skill. Interface components and the interaction layer of the sample projects.',
            'sample' => true,
        ],
        [
            'slug' => 'inertia',
            'title' => 'Inertia.js',
            'category' => 'technology',
            'layer' => 'application',
            'description' => 'Sample skill. Navigation between Laravel and Vue without a separate frontend application.',
            'sample' => true,
        ],
        [
            'slug' => 'typescript',
            'title' => 'TypeScript',
            'category' => 'technology',
            'layer' => 'interface',
            'description' => 'Sample skill. Typed frontend logic for pages, scenes, and interaction helpers.',
            'sample' => true,
        ],
        [
            'slug' => 'tailwind',
            'title' => 'Tailwind CSS',
            'category' => 'technology',
            'layer' => 'interface',
            'description' => 'Sample skill. The shared spacing, colour, and layout system for the interface.',
            'sample' => true,
        ],
        [
            'slug' => 'postgresql',
            'title' => 'PostgreSQL',
            'category' => 'technology',
            'layer' => 'infrastructure',
            'description' => 'Sample skill. Relational data as it would appear in a product. Not a claim about a production database you can visit.',
            'sample' => true,
        ],
        [
            'slug' => 'three',
            'title' => 'Three.js',
            'category' => 'technology',
            'layer' => 'interface',
            'description' => 'Sample skill. Browser-rendered scenes used as an optional layer on top of the written portfolio.',
            'sample' => true,
        ],
        [
            'slug' => 'accessibility',
            'title' => 'Accessibility',
            'category' => 'practice',
            'layer' => 'interface',
            'description' => 'Sample practice. Keyboard access, visible focus, named controls, and a reading path that does not depend on the 3D scene.',
            'sample' => true,
        ],
        [
            'slug' => 'testing',
            'title' => 'Testing',
            'category' => 'practice',
            'layer' => 'application',
            'description' => 'Sample practice. Automated checks for routes and for the relationships between projects and skills.',
            'sample' => true,
        ],
        [
            'slug' => 'api-design',
            'title' => 'API design',
            'category' => 'practice',
            'layer' => 'application',
            'description' => 'Sample practice. Shaping the data a screen needs. This portfolio itself does not expose a public API.',
            'sample' => true,
        ],
        [
            'slug' => 'product-interfaces',
            'title' => 'Product interfaces',
            'category' => 'expertise',
            'layer' => 'interface',
            'description' => 'Sample area of focus. Workspaces people use to get something done. Replace this with the kind of product work you actually want to be hired for.',
            'sample' => true,
        ],
        [
            'slug' => 'information-architecture',
            'title' => 'Information architecture',
            'category' => 'expertise',
            'layer' => 'interface',
            'description' => 'Sample area of focus. Organising a product so a new visitor can understand it quickly.',
            'sample' => true,
        ],
    ],

    'milestones' => [
        [
            'slug' => 'foundations',
            'title' => 'Foundations',
            'period' => 'Unverified',
            'summary' => 'Sample milestone. A placeholder for early practice. Replace it with a phase you want to show, and only add a date you can stand behind.',
            'sample' => true,
        ],
        [
            'slug' => 'product-work',
            'title' => 'Product work',
            'period' => 'Unverified',
            'summary' => 'Sample milestone. A placeholder for application work. Do not invent an employer or a job title here.',
            'sample' => true,
        ],
        [
            'slug' => 'independent-work',
            'title' => 'Independent work',
            'period' => 'Unverified',
            'summary' => 'Sample milestone. A placeholder for freelance work, collaborations, or self-directed products.',
            'sample' => true,
        ],
        [
            'slug' => 'current-focus',
            'title' => 'Current focus',
            'period' => 'Unverified',
            'summary' => 'Sample milestone. A placeholder for what you want a visitor to understand about the present.',
            'sample' => true,
        ],
    ],
];
