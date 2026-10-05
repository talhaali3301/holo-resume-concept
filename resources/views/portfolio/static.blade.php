{{-- Readable portfolio for browsers without JavaScript. Hidden automatically when scripts run. --}}
<noscript>
    <main class="static-portfolio">
        <p class="static-kicker">{{ $portfolioStatic['identity']['product'] }}</p>
        @if ($portfolioStatic['identity']['sample'])
            <p>Sample profile</p>
        @endif
        @if ($portfolioStatic['identity']['name'])
            <p>{{ $portfolioStatic['identity']['name'] }}</p>
        @endif
        <p>{{ $portfolioStatic['identity']['role'] }}</p>
        <h1>{{ $portfolioStatic['identity']['headline'] }}</h1>
        <p>{{ $portfolioStatic['identity']['lede'] }}</p>
        @if ($portfolioStatic['identity']['body'] !== '')
            <p>{{ $portfolioStatic['identity']['body'] }}</p>
        @endif
        <nav aria-label="Primary">
            <a href="{{ $portfolioStatic['lobby'] }}">Lobby</a>
            <a href="{{ $portfolioStatic['projectsIndex'] }}">Projects</a>
            <a href="{{ $portfolioStatic['skillsIndex'] }}">Skills</a>
        </nav>

        <section>
            <h2>Projects</h2>
            @forelse ($portfolioStatic['projects'] as $project)
                <article>
                    <h3><a href="{{ $project['href'] }}">{{ $project['title'] }}</a></h3>
                    @if ($project['sample'])
                        <p>Sample content</p>
                    @endif
                    @if ($project['summary'] !== '')
                        <p>{{ $project['summary'] }}</p>
                    @endif
                    @if ($project['purpose'] !== '')
                        <p>{{ $project['purpose'] }}</p>
                    @endif
                    @if ($project['role'] !== '')
                        <p>{{ $project['role'] }}</p>
                    @endif
                    @if ($project['technologies'] !== [])
                        <p>{{ implode(', ', $project['technologies']) }}</p>
                    @endif
                </article>
            @empty
                <p>No projects have been added yet.</p>
            @endforelse
        </section>

        <section>
            <h2>Skills</h2>
            @forelse ($portfolioStatic['skills'] as $skill)
                <article>
                    <h3><a href="{{ $skill['href'] }}">{{ $skill['title'] }}</a></h3>
                    @if ($skill['description'] !== '')
                        <p>{{ $skill['description'] }}</p>
                    @endif
                </article>
            @empty
                <p>No skills have been added yet.</p>
            @endforelse
        </section>

        <section>
            <h2>{{ $portfolioStatic['contact']['headline'] }}</h2>
            @if ($portfolioStatic['contact']['body'] !== '')
                <p>{{ $portfolioStatic['contact']['body'] }}</p>
            @endif
            @if ($portfolioStatic['contact']['email'])
                <p><a href="mailto:{{ $portfolioStatic['contact']['email'] }}">{{ $portfolioStatic['contact']['email'] }}</a></p>
            @endif
            @foreach ($portfolioStatic['contact']['links'] as $link)
                <p><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></p>
            @endforeach
        </section>
    </main>
</noscript>
