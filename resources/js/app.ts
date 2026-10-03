import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h, type DefineComponent } from 'vue'

const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue')

createInertiaApp({
    title: (title) => (title ? `${title} — Holo Resume` : 'Holo Resume'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.vue`]

        if (!page) {
            throw new Error(`Unknown page: ${name}`)
        }

        return page()
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
    progress: {
        color: '#8eecff',
        showSpinner: false,
    },
})
