<?php

declare(strict_types=1);

namespace App\Portfolio;

use Illuminate\Http\Request;

final class PortfolioPresenter
{
    public function __construct(private readonly PortfolioCatalog $catalog) {}

    /**
     * @return array<string, mixed>
     */
    public function shell(): array
    {
        $identity = $this->catalog->identity();

        return [
            'brand' => $identity['brand'],
            'product' => $identity['product'],
            'role' => $identity['role'],
            'sample' => $identity['sample'],
            'nav' => [
                ['id' => 'lobby', 'label' => 'Lobby', 'href' => route('lobby')],
                ['id' => 'projects', 'label' => 'Projects', 'href' => route('projects.index')],
                ['id' => 'skills', 'label' => 'Skills', 'href' => route('skills.index')],
            ],
            'contact' => $this->catalog->contact(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function lobby(): array
    {
        return [
            'identity' => $this->catalog->identity(),
            'destinations' => $this->destinations(),
            'entry' => [
                'hall' => route('projects.index'),
                'standard' => route('projects.index', ['view' => 'standard']),
                'skills' => route('skills.index'),
            ],
            'meta' => $this->meta('lobby'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function projectsPage(Request $request, ?string $slug): array
    {
        $projects = $this->projects();

        return [
            'projects' => $projects,
            'skills' => $this->skillRefs(),
            'selected' => $this->find($projects, $slug),
            'view' => $this->view($request),
            'routes' => [
                'index' => route('projects.index'),
                'lobby' => route('lobby'),
                'skills' => route('skills.index'),
            ],
            'meta' => $this->meta('projects'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function skillsPage(Request $request, ?string $slug): array
    {
        $skills = $this->skills();

        return [
            'skills' => $skills,
            'links' => $this->catalog->skillLinks(),
            'milestones' => $this->catalog->milestones(),
            'selected' => $this->find($skills, $slug),
            'view' => $this->view($request),
            'routes' => [
                'index' => route('skills.index'),
                'lobby' => route('lobby'),
                'projects' => route('projects.index'),
            ],
            'meta' => $this->meta('skills'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function document(Request $request): array
    {
        $meta = $this->meta($this->screen($request));

        return [
            'portfolioMeta' => [
                'fullTitle' => $meta['fullTitle'],
                'description' => $meta['description'],
                'url' => url()->current(),
                'image' => asset('og.png'),
            ],
            'portfolioStatic' => [
                'identity' => $this->catalog->identity(),
                'projects' => $this->projects(),
                'skills' => $this->skills(),
                'milestones' => $this->catalog->milestones(),
                'contact' => $this->catalog->contact(),
                'lobby' => route('lobby'),
                'projectsIndex' => route('projects.index'),
                'skillsIndex' => route('skills.index'),
            ],
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Holo Resume',
                'description' => $meta['description'],
                'url' => url('/'),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function sitemapUrls(): array
    {
        $urls = [
            route('lobby'),
            route('projects.index'),
            route('skills.index'),
        ];

        foreach ($this->catalog->projects() as $project) {
            $urls[] = route('projects.show', ['project' => $project['slug']]);
        }

        foreach ($this->catalog->skills() as $skill) {
            $urls[] = route('skills.show', ['skill' => $skill['slug']]);
        }

        return $urls;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function projects(): array
    {
        return array_map(function (array $project): array {
            $project['href'] = route('projects.show', ['project' => $project['slug']]);

            return $project;
        }, $this->catalog->projects());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function skills(): array
    {
        return array_map(function (array $skill): array {
            $skill['href'] = route('skills.show', ['skill' => $skill['slug']]);
            $skill['projects'] = array_map(function (array $project): array {
                $project['href'] = route('projects.show', ['project' => $project['slug']]);

                return $project;
            }, $skill['projects']);

            return $skill;
        }, $this->catalog->skills());
    }

    /**
     * @return list<array{slug: string, title: string, category: string, href: string}>
     */
    private function skillRefs(): array
    {
        return array_map(fn (array $skill): array => [
            'slug' => $skill['slug'],
            'title' => $skill['title'],
            'category' => $skill['category'],
            'href' => route('skills.show', ['skill' => $skill['slug']]),
        ], $this->catalog->skills());
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    private function find(array $items, ?string $slug): ?array
    {
        if ($slug === null) {
            return null;
        }

        foreach ($items as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, kicker: string, label: string, summary: string, href: string|null}>
     */
    private function destinations(): array
    {
        return [
            [
                'id' => 'projects',
                'kicker' => 'Hall',
                'label' => 'Projects',
                'summary' => 'Open the gallery and read each piece without leaving the page.',
                'href' => route('projects.index'),
            ],
            [
                'id' => 'skills',
                'kicker' => 'Observatory',
                'label' => 'Skills',
                'summary' => 'See the technologies I use and what I build with them.',
                'href' => route('skills.index'),
            ],
            [
                'id' => 'contact',
                'kicker' => 'Connect',
                'label' => 'Contact',
                'summary' => 'Start a conversation about your next web application.',
                'href' => null,
            ],
        ];
    }

    private function view(Request $request): string
    {
        return $request->query('view') === 'standard' ? 'standard' : 'gallery';
    }

    private function screen(Request $request): string
    {
        if ($request->routeIs('projects.*')) {
            return 'projects';
        }

        if ($request->routeIs('skills.*')) {
            return 'skills';
        }

        return 'lobby';
    }

    /**
     * @return array{title: string, fullTitle: string, description: string}
     */
    private function meta(string $screen): array
    {
        $pages = [
            'lobby' => [
                'title' => 'Lobby',
                'description' => 'A walkable sample portfolio for a web application developer, with a projects hall, a skills observatory, and a standard reading view.',
            ],
            'projects' => [
                'title' => 'Projects hall',
                'description' => 'Sample projects in the Holo Resume gallery. Open a piece for its purpose, role, technologies, and links when they are configured.',
            ],
            'skills' => [
                'title' => 'Skills observatory',
                'description' => 'Sample skills, the projects they connect to, and a career timeline whose dates are unverified until you replace them.',
            ],
        ];

        $page = $pages[$screen] ?? $pages['lobby'];

        return [
            'title' => $page['title'],
            'fullTitle' => $page['title'].' — Holo Resume',
            'description' => $page['description'],
        ];
    }
}
