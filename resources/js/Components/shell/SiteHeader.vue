<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import BrandMark from '@/Components/shell/BrandMark.vue'
import { isCurrentPath, nextTrapIndex, withView } from '@/lib/experience'
import type { Shell } from '@/types/portfolio'
import { usePage } from '@inertiajs/vue3'

const props = defineProps<{
    shell: Shell
}>()

const emit = defineEmits<{
    contact: []
}>()

const page = usePage()
const menuOpen = ref(false)
const menuRoot = ref<HTMLElement | null>(null)
const menuButton = ref<HTMLButtonElement | null>(null)
const closeButton = ref<HTMLButtonElement | null>(null)

function current(href: string): boolean {
    return isCurrentPath(page.url, href)
}

const room = computed(() => {
    const path = page.url.split('?')[0] ?? '/'

    if (path.startsWith('/projects')) {
        return { code: '02', name: 'Projects hall' }
    }

    if (path.startsWith('/skills')) {
        return { code: '03', name: 'Skills observatory' }
    }

    return { code: '01', name: 'Lobby' }
})

const standardHref = computed(() => {
    const path = page.url.split('?')[0] ?? '/'
    const base = path === '/' ? '/projects' : path

    return withView(base, page.url.includes('view=standard') ? 'gallery' : 'standard')
})

const standardLabel = computed(() => (page.url.includes('view=standard') ? 'Show the space' : 'Standard portfolio'))

function openMenu() {
    menuOpen.value = true
}

function closeMenu(restore = true) {
    menuOpen.value = false

    if (restore) {
        nextTick(() => menuButton.value?.focus())
    }
}

function chooseContact() {
    closeMenu(false)
    emit('contact')
}

function onKeydown(event: KeyboardEvent) {
    if (!menuOpen.value || !menuRoot.value) {
        return
    }

    if (event.key === 'Escape') {
        event.preventDefault()
        closeMenu()

        return
    }

    if (event.key !== 'Tab') {
        return
    }

    const nodes = [...menuRoot.value.querySelectorAll<HTMLElement>('a[href], button:not([disabled])')]
    const index = nodes.indexOf(document.activeElement as HTMLElement)
    const next = nextTrapIndex(index, nodes.length, event.shiftKey)
    event.preventDefault()
    nodes[next]?.focus()
}

watch(menuOpen, async (open) => {
    if (!open) {
        return
    }

    await nextTick()
    closeButton.value?.focus()
})

watch(
    () => page.url,
    () => {
        menuOpen.value = false
    },
)

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
    <header class="status-bar">
        <div class="status-bar__row">
            <Link :href="shell.nav[0]?.href ?? '/'" class="flex min-h-11 items-center gap-2.5 text-mist no-underline">
                <BrandMark />
                <span class="leading-tight">
                    <span class="block text-sm font-semibold tracking-[0.16em] uppercase">{{ shell.brand }}</span>
                    <span class="block text-xs text-muted">{{ shell.product }}</span>
                </span>
            </Link>

            <p class="room-readout">{{ room.name }}</p>

            <button
                ref="menuButton"
                type="button"
                class="btn btn-ghost ml-auto min-h-11 min-w-11 px-4"
                :aria-expanded="menuOpen"
                aria-controls="site-menu"
                @click="openMenu"
            >
                Menu
            </button>
        </div>
    </header>

    <div v-if="menuOpen" class="menu-scrim" @click.self="closeMenu()">
        <div id="site-menu" ref="menuRoot" class="menu-sheet" role="dialog" aria-modal="true" aria-label="Menu">
            <div class="mb-6 flex items-center justify-between">
                <p class="m-0 text-sm tracking-[0.14em] text-muted uppercase">{{ shell.product }}</p>
                <button ref="closeButton" type="button" class="btn btn-ghost" @click="closeMenu()">Close</button>
            </div>
            <nav class="grid" aria-label="Primary">
                <Link
                    v-for="item in shell.nav"
                    :key="item.id"
                    :href="item.href"
                    class="menu-link"
                    :class="{ 'is-current': current(item.href) }"
                    :aria-current="current(item.href) ? 'page' : undefined"
                    @click="closeMenu(false)"
                >
                    {{ item.label }}
                    <span v-if="current(item.href)">Current</span>
                </Link>
                <Link :href="standardHref" class="menu-link" @click="closeMenu(false)">{{ standardLabel }}</Link>
            </nav>
            <button type="button" class="btn btn-primary mt-auto" @click="chooseContact">Work with me</button>
            <p v-if="shell.sample" class="meta mt-4">Concept content</p>
        </div>
    </div>
</template>
