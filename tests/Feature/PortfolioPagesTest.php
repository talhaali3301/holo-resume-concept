<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Portfolio\PortfolioCatalog;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PortfolioPagesTest extends TestCase
{
    public function test_the_three_screens_render(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lobby')
                ->has('identity.headline')
                ->has('destinations', 3)
                ->has('entry.hall')
                ->where('meta.title', 'Lobby'))
            ->assertSee('<meta name="description"', false)
            ->assertSee('Operations Console', false);

        $this->get('/projects')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects')
                ->has('projects')
                ->where('view', 'gallery')
                ->where('selected', null));

        $this->get('/skills')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Skills')
                ->has('skills')
                ->has('milestones')
                ->has('links')
                ->where('view', 'gallery'));
    }

    public function test_project_and_skill_links_are_addressable(): void
    {
        $catalog = PortfolioCatalog::load();
        $project = $catalog->projects()[0];
        $skill = $catalog->skills()[0];

        $this->get('/projects/'.$project['slug'])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects')
                ->where('selected.slug', $project['slug']));

        $this->get('/skills/'.$skill['slug'].'?view=standard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Skills')
                ->where('selected.slug', $skill['slug'])
                ->where('view', 'standard'));

        $this->get('/projects?view=nope')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('view', 'gallery'));

        $this->get('/projects/not-a-real-project')->assertNotFound()->assertSee('Page not found');
        $this->get('/skills/not-a-real-skill')->assertNotFound();
    }

    public function test_markup_escapes_project_text(): void
    {
        $this->app->instance(PortfolioCatalog::class, new PortfolioCatalog([
            'projects' => [
                ['slug' => 'unsafe', 'title' => '<script>alert(1)</script>'],
            ],
        ]));

        $this->get('/projects/unsafe')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_sitemap_and_robots_list_the_public_screens(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(url('/projects'), false)
            ->assertSee(url('/skills'), false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Allow: /', false)
            ->assertSee(url('/sitemap.xml'), false);
    }
}
