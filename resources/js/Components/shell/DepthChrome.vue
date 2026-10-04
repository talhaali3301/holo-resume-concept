<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

export type ScreenId = 'lobby' | 'projects' | 'skills' | 'contact'

const props = defineProps<{
    current: ScreenId
    reduced: boolean
    routes: Record<ScreenId, string>
    standardHref: string
    brand: string
}>()

const emit = defineEmits<{
    'toggle-motion': []
    navigate: [id: ScreenId]
}>()

const railOrder: ScreenId[] = ['contact', 'skills', 'projects', 'lobby']
const railColor: Record<ScreenId, string> = {
    contact: '#D63E5E',
    skills: '#FF7C48',
    projects: '#FFB85C',
    lobby: '#F4EEE4',
}
const currentRailColor: Record<ScreenId, string> = {
    ...railColor,
    contact: '#F0607E',
}

function onRailClick(id: ScreenId, event: MouseEvent) {
    if (id === props.current) {
        return
    }

    event.preventDefault()
    emit('navigate', id)
}
</script>

<template>
    <header class="lobby-top">
        <Link :href="routes.lobby" class="lobby-brand">
            <svg width="26" height="24" viewBox="0 0 26 24" aria-hidden="true">
                <rect x="8" y="1" width="17" height="12" rx="3" fill="#D63E5E" />
                <rect x="4.5" y="5.5" width="17" height="12" rx="3" fill="#FF7C48" />
                <rect x="1" y="10" width="17" height="12" rx="3" fill="#F4EEE4" />
            </svg>
            {{ brand }}
        </Link>
        <nav aria-label="Other views">
            <a :href="standardHref">Portfolio index</a>
            <a :href="standardHref">Standard view</a>
            <button class="lobby-motion" type="button" :aria-pressed="!reduced" @click="emit('toggle-motion')">
                <i></i><span>{{ reduced ? 'Motion off' : 'Motion on' }}</span>
            </button>
        </nav>
    </header>

    <nav class="lobby-rail" aria-label="Depth">
        <span class="lobby-rail__cap">Depth</span>
        <component
            :is="id === current ? 'span' : 'a'"
            v-for="id in railOrder"
            :key="id"
            class="lobby-rail__item"
            :href="id === current ? undefined : routes[id]"
            :style="{ '--c': id === current ? currentRailColor[id] : railColor[id] }"
            :aria-current="id === current ? 'true' : undefined"
            @click="onRailClick(id, $event)"
        >
            {{ id[0].toUpperCase() + id.slice(1) }}
            <span class="lobby-rail__dot"></span>
        </component>
    </nav>
</template>
