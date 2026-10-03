<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Portfolio\PortfolioCatalog;
use App\Portfolio\PortfolioPresenter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioCatalog $catalog,
        private readonly PortfolioPresenter $pages,
    ) {}

    public function lobby(): InertiaResponse
    {
        return Inertia::render('Lobby', $this->pages->lobby());
    }

    public function projects(Request $request, ?string $project = null): InertiaResponse
    {
        if ($project !== null && $this->catalog->project($project) === null) {
            abort(404);
        }

        return Inertia::render('Projects', $this->pages->projectsPage($request, $project));
    }

    public function skills(Request $request, ?string $skill = null): InertiaResponse
    {
        if ($skill !== null && $this->catalog->skill($skill) === null) {
            abort(404);
        }

        return Inertia::render('Skills', $this->pages->skillsPage($request, $skill));
    }

    public function sitemap(): Response
    {
        return response()
            ->view('portfolio.sitemap', ['urls' => $this->pages->sitemapUrls()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
