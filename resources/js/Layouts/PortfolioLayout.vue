<script setup lang="ts">
import { computed, onUnmounted, provide, ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import ContactDialog from '@/Components/shell/ContactDialog.vue'
import SiteHeader from '@/Components/shell/SiteHeader.vue'
import { openContactKey } from '@/composables/useContact'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'
import { withView } from '@/lib/experience'
import { lockScroll, unlockScroll } from '@/lib/scrollLock'
import type { Shell } from '@/types/portfolio'

const page = usePage<{ shell: Shell }>()
const shell = computed(() => page.props.shell)
const contactOpen = ref(false)
const veil = ref(false)
const reduced = usePrefersReducedMotion()

const room = computed(() => {
    const path = page.url.split('?')[0] ?? '/'

    if (path.startsWith('/projects')) {
        return { id: 'hall', hint: 'Choose a project. Escape closes it.' }
    }

    if (path.startsWith('/skills')) {
        return { id: 'observatory', hint: 'Choose a skill. Escape closes it.' }
    }

    return { id: 'lobby', hint: 'Look around, then choose a room.' }
})

const gallery = computed(() => !page.url.includes('view=standard'))

const standardHref = computed(() => {
    const path = page.url.split('?')[0] ?? '/'
    const base = path === '/' ? '/projects' : path
    const next = page.url.includes('view=standard') ? 'gallery' : 'standard'

    return withView(base, next)
})

const standardLabel = computed(() => (page.url.includes('view=standard') ? 'Show the space' : 'Standard view'))

function openContact() {
    contactOpen.value = true
}

function closeContact() {
    contactOpen.value = false
}

provide(openContactKey, openContact)

watch(contactOpen, (open) => {
    if (open) {
        lockScroll()
    } else {
        unlockScroll()
    }
})

watch(
    () => page.url,
    () => {
        contactOpen.value = false
    },
)

const stopStart = router.on('start', () => {
    veil.value = !reduced.value
})

const stopFinish = router.on('finish', () => {
    veil.value = false
})

onUnmounted(() => {
    stopStart()
    stopFinish()

    if (contactOpen.value) {
        unlockScroll()
    }
})
</script>

<template>
    <a href="#content" class="skip-link">Skip to content</a>
    <div class="atmosphere" aria-hidden="true"></div>
    <div class="app-shell" :class="{ 'is-gallery': gallery }" :inert="contactOpen || undefined">
        <SiteHeader :shell="shell" @contact="openContact" />
        <div class="room">
            <slot />
        </div>
        <div v-if="gallery" class="control-dock">
            <p class="m-0">{{ room.hint }}</p>
            <Link class="btn btn-quiet" :href="standardHref">{{ standardLabel }}</Link>
        </div>
    </div>
    <div class="room-veil" :class="{ 'is-on': veil }" aria-hidden="true"></div>
    <ContactDialog :open="contactOpen" :contact="shell.contact" @close="closeContact" />
</template>
