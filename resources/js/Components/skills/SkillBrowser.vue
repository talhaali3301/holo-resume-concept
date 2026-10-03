<script setup lang="ts">
import { computed, ref } from 'vue'
import { categoryLabel } from '@/lib/experience'
import type { Skill } from '@/types/portfolio'

const props = defineProps<{
    skills: Skill[]
    selectedSlug?: string | null
}>()

const emit = defineEmits<{
    select: [slug: string, event: Event]
}>()

const query = ref('')

const groups = computed(() => {
    const needle = query.value.trim().toLowerCase()
    const filtered = props.skills.filter((skill) => {
        if (!needle) {
            return true
        }

        return [skill.title, skill.category, skill.description].join(' ').toLowerCase().includes(needle)
    })
    const order = ['technology', 'practice', 'expertise', 'other']

    return order
        .map((category) => ({
            category,
            label: categoryLabel(category),
            skills: filtered.filter((skill) => (order.includes(skill.category) ? skill.category : 'other') === category),
        }))
        .filter((group) => group.skills.length > 0)
})

const count = computed(() => groups.value.reduce((total, group) => total + group.skills.length, 0))
</script>

<template>
    <form role="search" class="mb-4" @submit.prevent>
        <label for="skill-search" class="mb-2 block text-sm text-muted">Search skills</label>
        <input
            id="skill-search"
            v-model="query"
            type="search"
            name="skill-search"
            class="min-h-11 w-full rounded-full border border-white/10 bg-ink/70 px-4 text-mist"
            placeholder="Try a technology or practice"
        />
    </form>
    <p class="sr-only" aria-live="polite">{{ count }} skills shown</p>
    <p v-if="count === 0" class="text-muted">No skills match that search.</p>
    <div v-for="group in groups" :key="group.category" class="mb-4">
        <h3 class="meta mt-4">{{ group.label }}</h3>
        <ul class="m-0 grid list-none p-0">
            <li v-for="skill in group.skills" :key="skill.slug">
                <button
                    :id="`skill-${skill.slug}`"
                    type="button"
                    class="index-btn"
                    :class="{ 'is-current': skill.slug === selectedSlug }"
                    :aria-current="skill.slug === selectedSlug ? 'true' : undefined"
                    @click="emit('select', skill.slug, $event)"
                >
                    <span class="font-medium">{{ skill.title }}</span>
                    <span v-if="skill.slug === selectedSlug" class="index-no">Viewing</span>
                </button>
            </li>
        </ul>
    </div>
</template>
