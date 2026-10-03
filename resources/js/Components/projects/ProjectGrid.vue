<script setup lang="ts">
import { statusLabel } from '@/lib/experience'
import type { Project } from '@/types/portfolio'

defineProps<{
    projects: Project[]
    selectedSlug?: string | null
}>()

const emit = defineEmits<{
    select: [slug: string, event: Event]
}>()
</script>

<template>
    <div class="exhibit">
        <article
            v-for="(project, index) in projects"
            :key="project.slug"
            class="panel flex flex-col p-5"
            :class="index === 0 ? 'exhibit__feature' : 'exhibit__item'"
        >
            <div class="exhibit__screen" aria-hidden="true"></div>
            <p class="meta">
                {{ String(index + 1).padStart(2, '0') }}
                <template v-if="statusLabel(project.status)"> · {{ statusLabel(project.status) }}</template>
                <template v-if="project.slug === selectedSlug"> · Viewing</template>
            </p>
            <h2 class="mt-3 mb-2 font-heading text-3xl font-normal">{{ project.title }}</h2>
            <p class="m-0 flex-1 leading-relaxed text-muted">{{ project.summary }}</p>
            <p v-if="project.technologies.length" class="mt-4 mb-0 text-sm text-muted">{{ project.technologies.join(' · ') }}</p>
            <button
                :id="`project-${project.slug}`"
                type="button"
                class="btn btn-ghost mt-5 self-start"
                @click="emit('select', project.slug, $event)"
            >
                Open details
            </button>
        </article>
    </div>
</template>
