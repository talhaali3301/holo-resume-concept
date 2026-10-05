<script setup lang="ts">
import { computed, provide, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ContactDialog from '@/Components/shell/ContactDialog.vue'
import { openContactKey } from '@/composables/useContact'
import { lockScroll, unlockScroll } from '@/lib/scrollLock'
import type { Shell } from '@/types/portfolio'

const page = usePage<{ shell: Shell }>()
const shell = computed(() => page.props.shell)
const contactOpen = ref(false)

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
    <div :inert="contactOpen || undefined">
        <slot />
    </div>
    <ContactDialog :open="contactOpen" :contact="shell.contact" @close="closeContact" />
</template>
