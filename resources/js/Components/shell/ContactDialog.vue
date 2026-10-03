<script setup lang="ts">
import { nextTick, onUnmounted, ref, watch } from 'vue'
import { nextTrapIndex } from '@/lib/experience'
import type { Contact } from '@/types/portfolio'

const props = defineProps<{
    open: boolean
    contact: Contact
    sample: boolean
}>()

const emit = defineEmits<{
    close: []
}>()

const root = ref<HTMLElement | null>(null)
const closeButton = ref<HTMLButtonElement | null>(null)
let opener: HTMLElement | null = null

function focusable(): HTMLElement[] {
    if (!root.value) {
        return []
    }

    return [...root.value.querySelectorAll<HTMLElement>('a[href], button:not([disabled])')]
}

function onKeydown(event: KeyboardEvent) {
    if (!props.open) {
        return
    }

    if (event.key === 'Escape') {
        event.preventDefault()
        emit('close')

        return
    }

    if (event.key !== 'Tab') {
        return
    }

    const nodes = focusable()
    const index = nodes.indexOf(document.activeElement as HTMLElement)
    event.preventDefault()
    nodes[nextTrapIndex(index, nodes.length, event.shiftKey)]?.focus()
}

watch(
    () => props.open,
    async (open) => {
        if (open) {
            opener = document.activeElement instanceof HTMLElement ? document.activeElement : null
            window.addEventListener('keydown', onKeydown)
            await nextTick()
            closeButton.value?.focus()

            return
        }

        window.removeEventListener('keydown', onKeydown)
        opener?.focus()
        opener = null
    },
)

onUnmounted(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 grid place-items-end bg-black/55 p-4 sm:place-items-center" @click.self="emit('close')">
        <div
            ref="root"
            class="panel w-full max-w-lg p-6 sm:p-8"
            role="dialog"
            aria-modal="true"
            aria-labelledby="contact-title"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p v-if="sample || contact.sample" class="badge">Sample profile</p>
                    <h2 id="contact-title" class="mt-3 mb-0 font-display text-4xl font-normal tracking-tight">{{ contact.headline }}</h2>
                </div>
                <button ref="closeButton" type="button" class="btn btn-ghost" @click="emit('close')">Close</button>
            </div>
            <p v-if="contact.body" class="mt-4 text-muted leading-relaxed">{{ contact.body }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a v-if="contact.email" class="btn btn-primary" :href="`mailto:${contact.email}`">Email</a>
                <a
                    v-for="link in contact.links"
                    :key="link.url"
                    class="btn btn-ghost"
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ link.label }}
                </a>
            </div>
            <p v-if="!contact.email && contact.links.length === 0" class="mt-6 text-sm text-muted">
                No email or profile link is configured yet.
            </p>
        </div>
    </div>
</template>
