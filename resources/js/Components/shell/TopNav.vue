<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { isCurrentPath, nextTrapIndex, withView } from '@/lib/experience'
import { useOpenContact } from '@/composables/useContact'
import type { Shell } from '@/types/portfolio'

const props = defineProps<{
    shell: Shell
}>()

const page = usePage()
const openContact = useOpenContact()
const menuOpen = ref(false)
const menuRoot = ref<HTMLElement | null>(null)
const menuButton = ref<HTMLButtonElement | null>(null)
const firstMenuItem = ref<HTMLElement | null>(null)

const room = computed(() => {
    const path = page.url.split('?')[0] ?? '/'

    if (path.startsWith('/projects')) {
        return { index: '01', name: 'Projects Hall' }
    }

    if (path.startsWith('/skills')) {
        return { index: '02', name: 'Skills Observatory' }
    }

    return { index: '00', name: 'Lobby' }
})

const isStandard = computed(() => page.url.includes('view=standard'))

const standardHref = computed(() => {
    const path = page.url.split('?')[0] ?? '/'
    const base = path === '/' ? '/projects' : path

    return withView(base, 'standard')
})

const spatialHref = computed(() => {
    const path = page.url.split('?')[0] ?? '/'
    const base = path === '/' ? '/projects' : path

    return withView(base, 'gallery')
})

function goStandard() {
    if (!isStandard.value) {
        router.visit(standardHref.value, { preserveScroll: true })
    }
}

function goSpatial() {
    if (isStandard.value) {
        router.visit(spatialHref.value, { preserveScroll: true })
    }
}

function current(href: string): boolean {
    return isCurrentPath(page.url, href)
}

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
    openContact()
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
    firstMenuItem.value?.focus()
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
    <header class="top-nav">
        <Link href="/" class="top-nav__brand">
            <svg class="top-nav__brand-glyph" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <ellipse cx="12" cy="12" rx="10" ry="4.2" stroke="currentColor" stroke-width="1.2" />
                <circle cx="12" cy="12" r="2.6" fill="currentColor" />
            </svg>
            <span class="top-nav__brand-name">{{ shell.product }}</span>
        </Link>
        <div class="top-nav__divider" aria-hidden="true"></div>
        <p class="top-nav__location">
            <b>{{ room.index }}</b> {{ room.name }}
        </p>

        <div class="top-nav__right">
            <div class="top-nav__switch" role="group" aria-label="View mode">
                <button type="button" :class="{ 'is-active': !isStandard }" :aria-pressed="!isStandard" @click="goSpatial">Spatial</button>
                <button type="button" :class="{ 'is-active': isStandard }" :aria-pressed="isStandard" @click="goStandard">Standard</button>
            </div>
            <button
                ref="menuButton"
                type="button"
                class="top-nav__menu-btn"
                :aria-expanded="menuOpen"
                aria-controls="site-menu"
                @click="openMenu"
            >
                <span class="top-nav__menu-icon" aria-hidden="true"><span></span><span></span></span>
                Menu
            </button>
        </div>
    </header>

    <div v-if="menuOpen" class="menu-scrim" @click="closeMenu()"></div>
    <div v-if="menuOpen" id="site-menu" ref="menuRoot" class="menu-panel" role="dialog" aria-modal="true" aria-label="Menu">
        <Link ref="firstMenuItem" href="/" class="menu-row" :aria-current="current('/') ? 'page' : undefined" @click="closeMenu(false)">
            <span><span class="menu-row__index">00</span>Lobby</span>
            <span v-if="current('/')" class="menu-row__tag">Here</span>
        </Link>
        <Link href="/projects" class="menu-row" :aria-current="current('/projects') ? 'page' : undefined" @click="closeMenu(false)">
            <span><span class="menu-row__index">01</span>Projects Hall</span>
            <span v-if="current('/projects')" class="menu-row__tag">Here</span>
        </Link>
        <Link href="/skills" class="menu-row" :aria-current="current('/skills') ? 'page' : undefined" @click="closeMenu(false)">
            <span><span class="menu-row__index">02</span>Skills Observatory</span>
            <span v-if="current('/skills')" class="menu-row__tag">Here</span>
        </Link>
        <button type="button" class="menu-row" @click="chooseContact">
            <span><span class="menu-row__index">03</span>Contact</span>
        </button>
        <div class="top-nav__switch mt-2 sm:hidden" role="group" aria-label="View mode">
            <button type="button" :class="{ 'is-active': !isStandard }" :aria-pressed="!isStandard" @click="goSpatial(); closeMenu(false)">Spatial</button>
            <button type="button" :class="{ 'is-active': isStandard }" :aria-pressed="isStandard" @click="goStandard(); closeMenu(false)">Standard</button>
        </div>
        <p v-if="shell.sample" class="sample-note mt-3 px-1">Concept content</p>
    </div>
</template>
