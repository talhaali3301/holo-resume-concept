<script setup lang="ts">
import { TresCanvas } from '@tresjs/core'
import type { TresContext } from '@tresjs/core'
import { ACESFilmicToneMapping, FogExp2, SRGBColorSpace, WebGLRenderer } from 'three'
import { onUnmounted, watch } from 'vue'
import { pixelRatioCap } from '@/lib/experience'
import { createBloomPipeline } from '@/scenes/usePostProcessing'
import LobbyWorld from '@/scenes/LobbyWorld.vue'
import type { Destination } from '@/types/portfolio'

const dpr = pixelRatioCap()

defineProps<{
    destinations: Destination[]
    motion: boolean
    parallaxX: number
    parallaxY: number
}>()

const emit = defineEmits<{
    select: [id: string]
    ready: []
    failure: []
}>()

let detach: (() => void) | null = null
let stopSizeWatch: (() => void) | null = null
let pipeline: ReturnType<typeof createBloomPipeline> | null = null

function onReady(context: TresContext) {
    context.scene.value.fog = new FogExp2('#05070d', 0.052)
    const renderer = context.renderer.instance
    const camera = context.camera.activeCamera.value

    if (renderer instanceof WebGLRenderer && camera) {
        const canvas = renderer.domElement
        const lost = (event: Event) => {
            event.preventDefault()
            emit('failure')
        }
        canvas.addEventListener('webglcontextlost', lost)
        detach = () => canvas.removeEventListener('webglcontextlost', lost)

        pipeline = createBloomPipeline(renderer, context.scene.value, camera, context.sizes.width.value, context.sizes.height.value, {
            threshold: 0.72,
            strength: 0.75,
            radius: 0.75,
        })

        context.renderer.replaceRenderFunction((notifyFrameRendered) => {
            pipeline?.render()
            notifyFrameRendered()
        })

        stopSizeWatch = watch([context.sizes.width, context.sizes.height], ([width, height]) => {
            pipeline?.setSize(width, height)
        })
    }

    emit('ready')
}

onUnmounted(() => {
    detach?.()
    stopSizeWatch?.()
    pipeline?.dispose()
})
</script>

<template>
    <TresCanvas
        clear-color="#05070d"
        render-mode="on-demand"
        :fps-limit="30"
        :dpr="dpr"
        :antialias="true"
        :shadows="false"
        :tone-mapping="ACESFilmicToneMapping"
        :tone-mapping-exposure="1.1"
        :output-color-space="SRGBColorSpace"
        @ready="onReady"
        @error="emit('failure')"
    >
        <LobbyWorld
            :destinations="destinations"
            :motion="motion"
            :parallax-x="parallaxX"
            :parallax-y="parallaxY"
            @select="emit('select', $event)"
        />
    </TresCanvas>
</template>
