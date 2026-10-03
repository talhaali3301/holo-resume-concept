<script setup lang="ts">
import { TresCanvas } from '@tresjs/core'
import type { TresContext } from '@tresjs/core'
import { Fog } from 'three'
import { onUnmounted } from 'vue'
import { pixelRatioCap } from '@/lib/experience'
import HallWorld from '@/scenes/HallWorld.vue'
import type { Project } from '@/types/portfolio'

const dpr = pixelRatioCap()

defineProps<{
    projects: Project[]
    selectedSlug?: string | null
    hoveredSlug?: string | null
    motion: boolean
    parallaxX: number
    parallaxY: number
}>()

const emit = defineEmits<{
    select: [slug: string]
    hover: [slug: string | null]
    ready: []
    failure: []
}>()

let detach: (() => void) | null = null

function onReady(context: TresContext) {
    context.scene.value.fog = new Fog('#070b14', 16, 32)
    const renderer = context.renderer.instance

    if ('domElement' in renderer) {
        const canvas = renderer.domElement
        const lost = (event: Event) => {
            event.preventDefault()
            emit('failure')
        }
        canvas.addEventListener('webglcontextlost', lost)
        detach = () => canvas.removeEventListener('webglcontextlost', lost)
    }

    emit('ready')
}

onUnmounted(() => detach?.())
</script>

<template>
    <TresCanvas
        clear-color="#070b14"
        render-mode="on-demand"
        :fps-limit="30"
        :dpr="dpr"
        :antialias="true"
        :shadows="false"
        @ready="onReady"
        @error="emit('failure')"
    >
        <HallWorld
            :projects="projects"
            :selected-slug="selectedSlug"
            :hovered-slug="hoveredSlug"
            :motion="motion"
            :parallax-x="parallaxX"
            :parallax-y="parallaxY"
            @select="emit('select', $event)"
            @hover="emit('hover', $event)"
        />
    </TresCanvas>
</template>
