<?php

declare(strict_types=1);

namespace App\Portfolio;

use Illuminate\Support\Str;

/**
 * Loads and sanitises portfolio content.
 *
 * Invalid links, missing images, unknown skill references, and incomplete
 * entries are dropped or emptied. They never throw during a page render.
 */
final class PortfolioCatalog
{
    /** @var array<string, mixed>|null */
    private ?array $prepared = null;

    /**
     * @param  array<string, mixed>  $source
     */
    public function __construct(private readonly array $source) {}

    public static function load(?string $path = null): self
    {
        $path ??= base_path('content/portfolio.php');

        if (! is_file($path)) {
            return new self([]);
        }

        $data = require $path;

        return new self(is_array($data) ? $data : []);
    }

    /**
     * @return array{
     *     sample: bool,
     *     brand: string,
     *     product: string,
     *     name: string|null,
     *     role: string,
     *     stack: string,
     *     headline: string,
     *     lede: string,
     *     body: string,
     *     availability: string
     * }
     */
    public function identity(): array
    {
        return $this->prepared()['identity'];
    }

    /**
     * @return array{sample: bool, email: string|null, headline: string, body: string, links: list<array{label: string, url: string}>}
     */
    public function contact(): array
    {
        return $this->prepared()['contact'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function projects(): array
    {
        return $this->prepared()['projects'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function project(string $slug): ?array
    {
        foreach ($this->projects() as $project) {
            if ($project['slug'] === $slug) {
                return $project;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function skills(): array
    {
        return $this->prepared()['skills'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function skill(string $slug): ?array
    {
        foreach ($this->skills() as $skill) {
            if ($skill['slug'] === $slug) {
                return $skill;
            }
        }

        return null;
    }

    /**
     * @return list<array{from: string, to: string, weight: int}>
     */
    public function skillLinks(): array
    {
        /** @var array<string, int> $weights */
        $weights = [];

        foreach ($this->projects() as $project) {
            $skills = array_values(array_unique($project['skills']));
            $count = count($skills);

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $pair = [$skills[$i], $skills[$j]];
                    sort($pair);
                    $key = $pair[0].'|'.$pair[1];
                    $weights[$key] = ($weights[$key] ?? 0) + 1;
                }
            }
        }

        arsort($weights);

        /** @var array<string, int> $degree */
        $degree = [];
        $links = [];

        foreach ($weights as $key => $weight) {
            [$from, $to] = explode('|', $key, 2);
            $degree[$from] = $degree[$from] ?? 0;
            $degree[$to] = $degree[$to] ?? 0;

            if ($degree[$from] >= 2 || $degree[$to] >= 2) {
                continue;
            }

            $degree[$from]++;
            $degree[$to]++;
            $links[] = [
                'from' => $from,
                'to' => $to,
                'weight' => $weight,
            ];
        }

        return $links;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepared(): array
    {
        if ($this->prepared === null) {
            $this->prepared = $this->prepare($this->source);
        }

        return $this->prepared;
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function prepare(array $source): array
    {
        $skills = $this->prepareSkills($source['skills'] ?? []);
        $known = [];

        foreach ($skills as $skill) {
            $known[$skill['slug']] = true;
        }

        $projects = $this->prepareProjects($source['projects'] ?? [], $known);

        return [
            'identity' => $this->prepareIdentity(is_array($source['identity'] ?? null) ? $source['identity'] : []),
            'contact' => $this->prepareContact(is_array($source['contact'] ?? null) ? $source['contact'] : []),
            'projects' => $projects,
            'skills' => $this->attachProjects($skills, $projects),
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{sample: bool, brand: string, product: string, name: string|null, role: string, stack: string, headline: string, lede: string, body: string, availability: string}
     */
    private function prepareIdentity(array $raw): array
    {
        return [
            'sample' => $this->flag($raw, true),
            'brand' => $this->line($raw['brand'] ?? null, 40) ?? 'Holo',
            'product' => $this->line($raw['product'] ?? null, 80) ?? 'Holo Resume',
            'name' => $this->line($raw['name'] ?? null, 80),
            'role' => $this->line($raw['role'] ?? null, 120) ?? 'Web application developer',
            'stack' => $this->line($raw['stack'] ?? null, 120) ?? 'Laravel / Vue / Product Engineering',
            'headline' => $this->line($raw['headline'] ?? null, 180) ?? 'Web application developer',
            'lede' => $this->line($raw['lede'] ?? null, 600) ?? 'A portfolio you can walk through, with a standard reading view beside it.',
            'body' => $this->block($raw['body'] ?? null, 2000) ?? '',
            'availability' => $this->line($raw['availability'] ?? null, 240) ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{sample: bool, email: string|null, headline: string, body: string, links: list<array{label: string, url: string}>}
     */
    private function prepareContact(array $raw): array
    {
        $links = [];

        foreach (is_array($raw['links'] ?? null) ? $raw['links'] : [] as $link) {
            if (! is_array($link)) {
                continue;
            }

            $label = $this->line($link['label'] ?? null, 40);
            $url = $this->httpsUrl($link['url'] ?? null);

            if ($label === null || $url === null) {
                continue;
            }

            $links[] = ['label' => $label, 'url' => $url];
        }

        return [
            'sample' => $this->flag($raw, true),
            'email' => $this->email($raw['email'] ?? null),
            'headline' => $this->line($raw['headline'] ?? null, 80) ?? 'Work with me',
            'body' => $this->block($raw['body'] ?? null, 800) ?? '',
            'links' => $links,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $skills
     * @param  list<array<string, mixed>>  $projects
     * @return list<array<string, mixed>>
     */
    private function attachProjects(array $skills, array $projects): array
    {
        return array_map(function (array $skill) use ($projects): array {
            $related = [];

            foreach ($projects as $project) {
                if (! in_array($skill['slug'], $project['skills'], true)) {
                    continue;
                }

                $related[] = [
                    'slug' => $project['slug'],
                    'title' => $project['title'],
                    'summary' => $project['summary'],
                    'sample' => $project['sample'],
                ];
            }

            $skill['projects'] = $related;

            return $skill;
        }, $skills);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function prepareSkills(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $skills = [];
        $seen = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $title = $this->line($entry['title'] ?? null, 80);

            if ($title === null) {
                continue;
            }

            $slug = $this->slug($entry['slug'] ?? $title);

            if ($slug === null || isset($seen[$slug])) {
                continue;
            }

            $seen[$slug] = true;
            $category = $this->line($entry['category'] ?? null, 40) ?? 'other';

            if (! in_array($category, ['technology', 'practice', 'expertise'], true)) {
                $category = 'other';
            }

            $layer = $this->line($entry['layer'] ?? null, 40) ?? 'application';

            if (! in_array($layer, ['interface', 'application', 'infrastructure'], true)) {
                $layer = 'application';
            }

            $skills[] = [
                'slug' => $slug,
                'title' => $title,
                'category' => $category,
                'layer' => $layer,
                'description' => $this->block($entry['description'] ?? null, 600) ?? '',
                'sample' => $this->flag($entry, true),
            ];
        }

        return $skills;
    }

    /**
     * @param  array<string, bool>  $knownSkills
     * @return list<array<string, mixed>>
     */
    private function prepareProjects(mixed $raw, array $knownSkills): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $projects = [];
        $seen = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $title = $this->line($entry['title'] ?? null, 120);

            if ($title === null) {
                continue;
            }

            $slug = $this->slug($entry['slug'] ?? $title);

            if ($slug === null || isset($seen[$slug])) {
                continue;
            }

            $seen[$slug] = true;
            $skills = [];

            foreach (is_array($entry['skills'] ?? null) ? $entry['skills'] : [] as $skill) {
                $skillSlug = $this->slug($skill);

                if ($skillSlug !== null && isset($knownSkills[$skillSlug]) && ! in_array($skillSlug, $skills, true)) {
                    $skills[] = $skillSlug;
                }
            }

            $status = $this->line($entry['status'] ?? null, 40);
            $image = $this->image($entry['image'] ?? null);

            $projects[] = [
                'slug' => $slug,
                'title' => $title,
                'category' => $this->line($entry['category'] ?? null, 60) ?? '',
                'label' => $this->line($entry['label'] ?? null, 120) ?? '',
                'summary' => $this->line($entry['summary'] ?? null, 280) ?? '',
                'purpose' => $this->block($entry['purpose'] ?? null, 600) ?? '',
                'built' => $this->block($entry['built'] ?? null, 600) ?? '',
                'role' => $this->line($entry['role'] ?? null, 240) ?? '',
                'technologies' => $this->lines($entry['technologies'] ?? null),
                'features' => $this->lines($entry['features'] ?? null),
                'image' => $image,
                'imageAlt' => $this->line($entry['image_alt'] ?? null, 180) ?? $title,
                'imageNote' => $this->line($entry['image_note'] ?? null, 180),
                'demoUrl' => $this->httpsUrl($entry['demo_url'] ?? null),
                'repositoryUrl' => $this->httpsUrl($entry['repository_url'] ?? null),
                'caseStudyUrl' => $this->httpsUrl($entry['case_study_url'] ?? null),
                'caseStudy' => $this->block($entry['case_study'] ?? null, 4000),
                'skills' => $skills,
                'status' => in_array($status, ['live', 'concept', 'in_progress', 'archived'], true) ? $status : null,
                'sample' => $this->flag($entry, true),
            ];
        }

        return $projects;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function flag(array $raw, bool $default): bool
    {
        return array_key_exists('sample', $raw) ? (bool) $raw['sample'] : $default;
    }

    private function slug(mixed $value): ?string
    {
        $line = is_string($value) ? trim($value) : null;

        if ($line === null || $line === '') {
            return null;
        }

        $slug = Str::slug($line);

        return $slug !== '' ? $slug : null;
    }

    private function line(mixed $value, int $limit): ?string
    {
        $text = $this->plain($value, $limit);

        if ($text === null) {
            return null;
        }

        $collapsed = preg_replace("/\s+/u", ' ', $text);

        if (! is_string($collapsed)) {
            return null;
        }

        $collapsed = trim($collapsed);

        return $collapsed === '' ? null : $collapsed;
    }

    private function block(mixed $value, int $limit): ?string
    {
        return $this->plain($value, $limit);
    }

    private function plain(mixed $value, int $limit): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = trim(str_replace("\0", '', $value));

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) > $limit) {
            $text = rtrim(mb_substr($text, 0, $limit));
        }

        return $text;
    }

    /**
     * @return list<string>
     */
    private function lines(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            $line = $this->line($item, 160);

            if ($line === null) {
                continue;
            }

            $items[] = $line;

            if (count($items) >= 12) {
                break;
            }
        }

        return $items;
    }

    private function email(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $email = trim($value);

        if ($email === '' || preg_match('/[\r\n\0]/', $email) === 1) {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null;
    }

    private function httpsUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        if (strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            return null;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        return $url;
    }

    private function image(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $path = trim($value);

        if (str_starts_with($path, '/images/')) {
            if (str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, "\0")) {
                return null;
            }

            return is_file(public_path(ltrim($path, '/'))) ? $path : null;
        }

        return $this->httpsUrl($path);
    }
}
