<script setup lang="ts">
import { statusLabel } from '@/lib/experience'
import type { Project } from '@/types/portfolio'

defineProps<{
    projects: Project[]
    selectedSlug?: string | null
    hoveredSlug?: string | null
}>()

const emit = defineEmits<{
    select: [slug: string, event: Event]
    hover: [slug: string | null]
}>()
</script>

<template>
    <nav aria-label="Projects" class="flex gap-3 overflow-x-auto pb-1 lg:max-h-[70vh] lg:flex-col lg:overflow-y-auto lg:pr-1">
        <button
            v-for="project in projects"
            :id="`project-${project.slug}`"
            :key="project.slug"
            type="button"
            class="panel min-h-11 w-[78%] shrink-0 cursor-pointer px-4 py-3 text-left lg:w-auto"
            :class="project.slug === selectedSlug ? 'border-cyan' : ''"
            :aria-current="project.slug === selectedSlug ? 'true' : undefined"
            @click="emit('select', project.slug, $event)"
            @mouseenter="emit('hover', project.slug)"
            @mouseleave="emit('hover', null)"
            @focus="emit('hover', project.slug)"
            @blur="emit('hover', null)"
        >
            <span class="flex flex-wrap items-center gap-2">
                <span v-if="project.sample" class="badge">Sample</span>
                <span v-if="statusLabel(project.status)" class="text-xs tracking-[0.14em] text-muted uppercase">{{ statusLabel(project.status) }}</span>
                <span v-if="project.slug === selectedSlug" class="text-xs tracking-[0.14em] text-cyan uppercase">Viewing</span>
                <span v-else-if="project.slug === hoveredSlug" class="text-xs tracking-[0.14em] text-muted uppercase">Highlighted</span>
            </span>
            <span class="mt-2 block font-heading text-2xl leading-none">{{ project.title }}</span>
            <span v-if="project.summary" class="mt-2 block text-sm leading-relaxed text-muted">{{ project.summary }}</span>
        </button>
    </nav>
</template>
