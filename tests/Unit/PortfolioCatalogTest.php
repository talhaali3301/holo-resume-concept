<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Portfolio\PortfolioCatalog;
use Tests\TestCase;

class PortfolioCatalogTest extends TestCase
{
    public function test_shipped_content_keeps_valid_relationships(): void
    {
        $catalog = PortfolioCatalog::load();
        $skillIds = array_column($catalog->skills(), 'slug');

        $this->assertNotEmpty($catalog->projects());
        $this->assertNotEmpty($catalog->skills());
        $this->assertNotEmpty($catalog->milestones());

        foreach ($catalog->projects() as $project) {
            foreach ($project['skills'] as $skill) {
                $this->assertContains($skill, $skillIds);
            }
        }

        foreach ($catalog->skillLinks() as $link) {
            $this->assertContains($link['from'], $skillIds);
            $this->assertContains($link['to'], $skillIds);
        }

        if ($catalog->identity()['sample'] === true) {
            foreach ($catalog->projects() as $project) {
                $this->assertTrue($project['sample'], 'Sample profiles must label every project as sample.');
            }

            foreach ($catalog->milestones() as $milestone) {
                $this->assertTrue($milestone['sample']);
                $this->assertSame(
                    'Unverified',
                    $milestone['period'],
                    'Sample profile milestones must keep an unverified period. Set identity.sample to false when dates are real.',
                );
            }
        }
    }

    public function test_invalid_entries_and_links_are_removed(): void
    {
        $catalog = new PortfolioCatalog([
            'skills' => [
                ['slug' => 'vue', 'title' => 'Vue', 'category' => 'technology'],
                ['slug' => 'vue', 'title' => 'Duplicate'],
                ['title' => 'Nope', 'category' => 'invented'],
                ['slug' => 'missing-title'],
            ],
            'projects' => [
                [
                    'slug' => 'alpha',
                    'title' => 'Alpha',
                    'skills' => ['vue', 'missing', 'vue'],
                    'demo_url' => 'javascript:alert(1)',
                    'repository_url' => 'http://example.com/repo',
                    'image' => '/images/does-not-exist.svg',
                    'status' => 'shipped',
                ],
                [
                    'slug' => 'alpha',
                    'title' => 'Duplicate project',
                ],
                [
                    'title' => 'External plate',
                    'image' => 'https://example.com/plate.png',
                    'demo_url' => 'https://example.com/demo',
                ],
                ['summary' => 'No title'],
            ],
            'contact' => [
                'email' => 'not-an-email',
                'links' => [
                    ['label' => 'Site', 'url' => 'https://user:pass@example.com'],
                    ['label' => 'Notes', 'url' => 'https://example.com/notes'],
                    ['label' => '', 'url' => 'https://example.com/empty'],
                ],
            ],
            'milestones' => [
                ['title' => 'Phase'],
            ],
        ]);

        $this->assertSame(['vue', 'nope'], array_column($catalog->skills(), 'slug'));
        $this->assertSame('other', $catalog->skills()[1]['category']);

        $alpha = $catalog->project('alpha');
        $this->assertNotNull($alpha);
        $this->assertSame(['vue'], $alpha['skills']);
        $this->assertNull($alpha['demoUrl']);
        $this->assertNull($alpha['repositoryUrl']);
        $this->assertNull($alpha['image']);
        $this->assertNull($alpha['status']);
        $this->assertNull($catalog->project('duplicate-project'));

        $external = $catalog->project('external-plate');
        $this->assertNotNull($external);
        $this->assertSame('https://example.com/plate.png', $external['image']);
        $this->assertSame('https://example.com/demo', $external['demoUrl']);

        $this->assertNull($catalog->contact()['email']);
        $this->assertSame([['label' => 'Notes', 'url' => 'https://example.com/notes']], $catalog->contact()['links']);
        $this->assertSame('Unverified', $catalog->milestones()[0]['period']);
    }

    public function test_skill_links_stay_sparse(): void
    {
        $skills = [];

        foreach (['a', 'b', 'c', 'd', 'e'] as $slug) {
            $skills[] = ['slug' => $slug, 'title' => strtoupper($slug), 'category' => 'technology'];
        }

        $catalog = new PortfolioCatalog([
            'skills' => $skills,
            'projects' => [
                ['title' => 'Bundle', 'skills' => ['a', 'b', 'c', 'd', 'e']],
            ],
        ]);

        $links = $catalog->skillLinks();
        $degree = [];

        foreach ($links as $link) {
            $degree[$link['from']] = ($degree[$link['from']] ?? 0) + 1;
            $degree[$link['to']] = ($degree[$link['to']] ?? 0) + 1;
        }

        $this->assertNotEmpty($links);
        $this->assertLessThan(10, count($links));
        $this->assertLessThanOrEqual(2, max($degree));
    }

    public function test_empty_source_does_not_crash(): void
    {
        $catalog = new PortfolioCatalog([]);

        $this->assertSame([], $catalog->projects());
        $this->assertSame([], $catalog->skills());
        $this->assertSame([], $catalog->milestones());
        $this->assertSame([], $catalog->skillLinks());
        $this->assertNotSame('', $catalog->identity()['headline']);
    }
}
