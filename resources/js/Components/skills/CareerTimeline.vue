<script setup lang="ts">
import type { Milestone } from '@/types/portfolio'

defineProps<{
    milestones: Milestone[]
    active?: string | null
}>()

const emit = defineEmits<{
    select: [slug: string]
}>()
</script>

<template>
    <section aria-labelledby="timeline-title">
        <h2 id="timeline-title" class="meta">Career path</h2>
        <p v-if="milestones.length === 0" class="mt-3 text-sm text-muted">No milestones have been added yet.</p>
        <ol v-else class="timeline-path mt-3">
            <li v-for="milestone in milestones" :key="milestone.slug">
                <button
                    type="button"
                    class="timeline-station"
                    :class="{ 'is-current': milestone.slug === active }"
                    :aria-pressed="milestone.slug === active"
                    @click="emit('select', milestone.slug)"
                >
                    <i aria-hidden="true"></i>
                    <span>
                        <span class="block text-xs tracking-[0.12em] text-muted uppercase">{{ milestone.period }}</span>
                        <span class="block font-display text-xl leading-tight">{{ milestone.title }}</span>
                        <span v-if="milestone.slug === active" class="mt-1 block text-sm leading-relaxed text-muted">{{ milestone.summary }}</span>
                    </span>
                </button>
            </li>
        </ol>
    </section>
</template>
