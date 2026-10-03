<script setup lang="ts">
import { computed } from 'vue'
import { layoutSkills } from '@/lib/skillLayout'
import type { Skill, SkillLink } from '@/types/portfolio'

const props = defineProps<{
    skills: Skill[]
    links: SkillLink[]
}>()

const nodes = computed(() =>
    layoutSkills(props.skills).map((point) => ({
        slug: point.slug,
        x: ((point.position[0] + 3.1) / 6.2) * 80 + 10,
        y: ((2.4 - point.position[1]) / 2.3) * 70 + 12,
    })),
)

function point(slug: string) {
    return nodes.value.find((node) => node.slug === slug)
}
</script>

<template>
    <svg class="absolute inset-0 h-full w-full" viewBox="0 0 100 100" role="presentation">
        <line
            v-for="link in links"
            :key="`${link.from}-${link.to}`"
            :x1="point(link.from)?.x ?? 0"
            :y1="point(link.from)?.y ?? 0"
            :x2="point(link.to)?.x ?? 0"
            :y2="point(link.to)?.y ?? 0"
            stroke="#8eecff"
            stroke-opacity="0.4"
            vector-effect="non-scaling-stroke"
        />
        <circle v-for="node in nodes" :key="node.slug" :cx="node.x" :cy="node.y" r="1.3" fill="#8eecff" />
    </svg>
</template>
