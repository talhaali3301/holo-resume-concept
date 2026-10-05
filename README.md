# Holo Resume

A concept for a developer portfolio that feels like a place you move through, not a page you scroll.

Each section is a sheet in a stack. You start in the Lobby and bring Projects, Skills or Contact forward while the rest waits behind. I built it to show how I approach interface design as well as code.

**Author:** Talha Ali, [Robo Coders](https://robocoders.dev/)

## Preview

![Lobby](https://raw.githubusercontent.com/talhaali3301/holo-resume-concept/main/docs/screenshots/lobby.png)

![Projects Hall](https://raw.githubusercontent.com/talhaali3301/holo-resume-concept/main/docs/screenshots/projects.png)

![Skills](https://raw.githubusercontent.com/talhaali3301/holo-resume-concept/main/docs/screenshots/skills.png)

## Features

- **Lobby:** a stack of four sheets and the starting point.
- **Projects Hall:** one project sits up front with the full write-up while the others wait at the sides. Click a pane or use the arrow keys to bring it forward. Every project has its own URL.
- **Skills:** the technologies I use and what I build with them.
- **Contact:** where to reach me.
- **Standard view:** the same content as a plain page, for anyone who would rather just read.
- **Motion toggle:** switches the animation off.
- **Mobile:** a flatter layout made for small screens.

## Tech stack

Laravel, Vue 3, Inertia.js, TypeScript, Vite and Tailwind CSS.

There is no login, admin panel or CMS. All content lives in one file, `content/portfolio.php`.

## Getting started

Requirements: PHP, Composer and Node.js.

```bash
git clone https://github.com/talhaali3301/holo-resume-concept.git
cd holo-resume-concept
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Start the two servers in separate terminals:

```bash
php artisan serve
npm run dev
```

Then open `http://127.0.0.1:8000` in your browser.

## Customising

Edit `content/portfolio.php` to change the name, role, skills, projects and links. The layout picks up whatever you put there.

## About the content

My name, skills and links are real. The six projects are samples written for this concept, and each one is labelled as a sample project on the page.

## Contact

- Website: https://robocoders.dev/
- LinkedIn: https://www.linkedin.com/in/talha-ali-b7959b132
- GitHub: https://github.com/talhaali3301
