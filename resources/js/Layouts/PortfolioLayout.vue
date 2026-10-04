<script setup lang="ts">
import { computed, provide, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ContactDialog from '@/Components/shell/ContactDialog.vue'
import TopNav from '@/Components/shell/TopNav.vue'
import { openContactKey } from '@/composables/useContact'
import { lockScroll, unlockScroll } from '@/lib/scrollLock'
import type { Shell } from '@/types/portfolio'

const page = usePage<{ shell: Shell }>()
const shell = computed(() => page.props.shell)
const contactOpen = ref(false)

// Lobby and Projects own their own full-viewport chrome (brand, nav, depth
// rail) per their own design — they do not sit inside the shared top nav /
// room wrapper that Skills still uses.
const hasOwnChrome = computed(() => page.component === 'Lobby' || page.component === 'Projects')

const gallery = computed(() => !page.url.includes('view=standard'))

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
</script>

<template>
    <a href="#content" class="skip-link">Skip to content</a>
    <template v-if="hasOwnChrome">
        <div :inert="contactOpen || undefined">
            <slot />
        </div>
    </template>
    <template v-else>
        <div class="atmosphere" aria-hidden="true"></div>
        <TopNav :shell="shell" />
        <div class="app-shell" :class="{ 'is-gallery': gallery }" :inert="contactOpen || undefined">
            <div class="room">
                <slot />
            </div>
        </div>
    </template>
    <ContactDialog :open="contactOpen" :contact="shell.contact" @close="closeContact" />
</template>
