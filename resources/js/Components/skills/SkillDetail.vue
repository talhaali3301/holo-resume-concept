<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import type { Skill } from '@/types/portfolio'

const props = defineProps<{
    skill: Skill
}>()

const categoryName = computed(() => {
    switch (props.skill.category) {
        case 'technology':
            return 'Technology'
        case 'practice':
            return 'Practice'
        case 'expertise':
            return 'Expertise'
        default:
            return 'Other'
    }
})

const emit = defineEmits<{
    close: []
}>()

function onKey(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        event.preventDefault()
        emit('close')
    }
}

onMounted(() => window.addEventListener('keydown', onKey))
onUnmounted(() => window.removeEventListener('keydown', onKey))
</script>

<template>
    <section class="panel mt-4 p-5" aria-labelledby="skill-detail-title">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="eyebrow">{{ categoryName }}</p>
                <h2 id="skill-detail-title" class="mt-2 mb-0 font-display text-4xl font-normal" tabindex="-1">{{ skill.title }}</h2>
            </div>
            <button type="button" class="btn btn-ghost" @click="emit('close')">Close</button>
        </div>
        <p v-if="skill.sample" class="badge mt-4">Sample</p>
        <p v-if="skill.description" class="mt-4 leading-relaxed text-muted">{{ skill.description }}</p>
        <h3 class="mt-6 text-sm tracking-[0.16em] text-cyan uppercase">Used in</h3>
        <ul v-if="skill.projects.length" class="m-0 grid list-none gap-2 p-0">
            <li v-for="project in skill.projects" :key="project.slug">
                <Link :href="project.href" class="btn btn-ghost w-full justify-start">
                    {{ project.title }}
                    <span v-if="project.sample" class="text-xs tracking-[0.12em] uppercase">Sample</span>
                </Link>
            </li>
        </ul>
        <p v-else class="text-muted">No projects are linked to this skill yet.</p>
    </section>
</template>
