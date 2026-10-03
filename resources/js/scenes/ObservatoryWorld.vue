<script setup lang="ts">
import { computed, markRaw, onUnmounted, ref, watch } from 'vue'
import { BufferGeometry, Float32BufferAttribute, LineBasicMaterial, LineSegments, Vector3 } from 'three'
import { layoutSkills } from '@/lib/skillLayout'
import { useCameraRig } from '@/scenes/useCameraRig'
import type { Milestone, Skill, SkillLink } from '@/types/portfolio'

const props = defineProps<{
    skills: Skill[]
    links: SkillLink[]
    milestones: Milestone[]
    selectedSlug?: string | null
    hoveredSlug?: string | null
    motion: boolean
    parallaxX: number
    parallaxY: number
}>()

const emit = defineEmits<{
    select: [slug: string]
    hover: [slug: string | null]
    marker: [slug: string]
}>()

const hovered = ref<string | null>(null)
const points = computed(() => layoutSkills(props.skills))
const nodes = computed(() =>
    points.value.map((point) => {
        const skill = props.skills.find((item) => item.slug === point.slug)

        return {
            ...point,
            category: skill?.category ?? 'other',
            weight: skill?.projects.length ?? 1,
        }
    }),
)

const idleMaterial = markRaw(new LineBasicMaterial({ color: '#8eecff', transparent: true, opacity: 0.22 }))
const activeMaterial = markRaw(new LineBasicMaterial({ color: '#d7f4ff', transparent: true, opacity: 0.85 }))
const idleLines = ref<LineSegments>(markRaw(new LineSegments(new BufferGeometry(), idleMaterial)))
const activeLines = ref<LineSegments>(markRaw(new LineSegments(new BufferGeometry(), activeMaterial)))

function buildLines(onlyActive: boolean): LineSegments {
    const map = new Map(points.value.map((point) => [point.slug, point.position]))
    const positions: number[] = []
    const focus = props.selectedSlug

    for (const link of props.links) {
        const active = focus !== null && focus !== undefined && (link.from === focus || link.to === focus)

        if (onlyActive !== Boolean(active)) {
            continue
        }

        const from = map.get(link.from)
        const to = map.get(link.to)

        if (!from || !to) {
            continue
        }

        positions.push(from[0], from[1], from[2], to[0], to[1], to[2])
    }

    const geometry = new BufferGeometry()
    geometry.setAttribute('position', new Float32BufferAttribute(positions, 3))

    return new LineSegments(geometry, onlyActive ? activeMaterial : idleMaterial)
}

watch(
    [points, () => props.links, () => props.selectedSlug],
    () => {
        idleLines.value.geometry.dispose()
        activeLines.value.geometry.dispose()
        idleLines.value = markRaw(buildLines(false))
        activeLines.value = markRaw(buildLines(true))
    },
    { immediate: true },
)

const stations = computed(() =>
    props.milestones.map((milestone, index) => ({
        ...milestone,
        position: [-2.4 + index * 1.55, 0.06, 2.35] as [number, number, number],
    })),
)

const stars = [
    [-3.1, 2.5, -1.2],
    [2.7, 2.7, -1.6],
    [-1.2, 2.8, -2],
    [3.1, 1.2, -0.6],
] as const

const position = new Vector3()
const look = new Vector3()

useCameraRig(
    (target) => {
        const shiftX = props.motion ? props.parallaxX * 0.28 : 0
        const shiftY = props.motion ? props.parallaxY * -0.1 : 0
        const focus = nodes.value.find((node) => node.slug === props.selectedSlug)

        if (focus) {
            position.set(focus.position[0] * 0.22 + shiftX, 1.72 + shiftY, 6.15)
            look.set(focus.position[0] * 0.72, focus.position[1], focus.position[2])
        } else {
            position.set(shiftX, 1.7 + shiftY, 6.5)
            look.set(shiftX * 0.12, 1.2, 0)
        }

        target.position.copy(position)
        target.look.copy(look)
    },
    () => props.motion,
    (elapsed, moving) => {
        activeMaterial.opacity = moving ? 0.55 + 0.3 * Math.sin(elapsed * 2.2) : 0.8
    },
)

onUnmounted(() => {
    idleLines.value.geometry.dispose()
    activeLines.value.geometry.dispose()
    idleMaterial.dispose()
    activeMaterial.dispose()
})

function connected(slug: string): boolean {
    const focus = props.selectedSlug

    if (!focus || focus === slug) {
        return true
    }

    return props.links.some((link) => (link.from === focus && link.to === slug) || (link.to === focus && link.from === slug))
}

function emissive(category: string): string {
    if (category === 'practice') {
        return '#6aa6ff'
    }

    if (category === 'expertise') {
        return '#d7e4ff'
    }

    return '#8eecff'
}

function nodeScale(slug: string, weight: number): number {
    const base = 0.85 + Math.min(weight, 6) * 0.1
    const hot = props.selectedSlug === slug || hovered.value === slug || props.hoveredSlug === slug

    if (hot) {
        return base * 1.35
    }

    if (props.selectedSlug && !connected(slug)) {
        return base * 0.8
    }

    return base
}

function emphasis(slug: string): number {
    if (props.selectedSlug === slug || hovered.value === slug || props.hoveredSlug === slug) {
        return 1.2
    }

    if (props.selectedSlug && !connected(slug)) {
        return 0.12
    }

    return 0.4
}

function setHover(slug: string | null) {
    hovered.value = slug
    emit('hover', slug)
}
</script>

<template>
    <TresPerspectiveCamera :position="[0, 1.7, 6.5]" :fov="40" :near="0.1" :far="40" />
    <TresAmbientLight :intensity="0.32" color="#b7c6e8" />
    <TresDirectionalLight :position="[-3, 5, 4]" :intensity="0.9" color="#e7eeff" />
    <TresPointLight :position="[0, 1.6, 2.2]" :intensity="4.5" color="#8eecff" :distance="14" :decay="2" />
    <primitive :object="idleLines" />
    <primitive :object="activeLines" />

    <TresMesh :position="[0, 0.15, 0.15]" :rotation-x="Math.PI / 2">
        <TresTorusGeometry :args="[2.15, 0.008, 12, 80]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.32" />
    </TresMesh>
    <TresMesh :position="[0, 0.02, 2.35]" :rotation-x="-Math.PI / 2">
        <TresPlaneGeometry :args="[6.4, 0.02]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.35" />
    </TresMesh>

    <TresMesh v-for="star in stars" :key="star.join(':')" :position="star">
        <TresSphereGeometry :args="[0.02, 8, 8]" />
        <TresMeshBasicMaterial color="#d5e7ff" />
    </TresMesh>

    <TresMesh
        v-for="node in nodes"
        :key="node.slug"
        :position="node.position"
        :scale="nodeScale(node.slug, node.weight)"
        @pointerenter="setHover(node.slug)"
        @pointerleave="setHover(null)"
        @click="emit('select', node.slug)"
    >
        <TresMesh v-if="node.slug === selectedSlug" :scale="1.9">
            <TresSphereGeometry :args="[0.13, 16, 16]" />
            <TresMeshBasicMaterial :color="emissive(node.category)" :transparent="true" :opacity="0.16" :depth-write="false" />
        </TresMesh>
        <TresSphereGeometry :args="[0.13, 24, 24]" />
        <TresMeshStandardMaterial
            :color="node.slug === selectedSlug ? '#f4f8ff' : '#c5e9ff'"
            :emissive="emissive(node.category)"
            :emissive-intensity="emphasis(node.slug)"
            :roughness="0.25"
            :metalness="0.15"
        />
    </TresMesh>

    <TresMesh
        v-for="station in stations"
        :key="station.slug"
        :position="station.position"
        @click="emit('marker', station.slug)"
    >
        <TresBoxGeometry :args="[0.16, 0.08, 0.16]" />
        <TresMeshStandardMaterial color="#d7e4ff" emissive="#8eecff" :emissive-intensity="0.7" />
    </TresMesh>
</template>
