<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="dark">
        <title>{{ $title }} — Holo Resume</title>
        <style>
            :root { color-scheme: dark; }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 2rem;
                background:
                    radial-gradient(ellipse at 20% 0%, rgba(80, 120, 255, 0.18), transparent 42%),
                    #070b14;
                color: #e8eef8;
                font-family: Georgia, "Times New Roman", serif;
            }
            main { max-width: 36rem; }
            p { font-family: "Segoe UI", sans-serif; color: #a9b6cb; line-height: 1.6; }
            a {
                color: #071018;
                background: #8eecff;
                display: inline-flex;
                min-height: 2.75rem;
                align-items: center;
                padding: 0 1.1rem;
                border-radius: 999px;
                text-decoration: none;
                font-family: "Segoe UI", sans-serif;
                font-weight: 600;
            }
            a:focus-visible { outline: 2px solid #8eecff; outline-offset: 3px; }
            h1 { font-size: clamp(2.4rem, 6vw, 4.2rem); font-weight: 400; letter-spacing: -0.03em; margin: 0 0 1rem; }
        </style>
    </head>
    <body>
        <main>
            <p>Holo Resume</p>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <a href="{{ url('/') }}">Back to the lobby</a>
        </main>
    </body>
</html>
