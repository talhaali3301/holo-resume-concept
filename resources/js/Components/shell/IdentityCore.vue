<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue'
import { detectWebGL } from '@/lib/experience'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'

const props = withDefaults(
    defineProps<{
        cx: number
        cy: number
        size: number
        haloScale?: number
        static?: boolean
        forceReduced?: boolean
    }>(),
    { haloScale: 5, static: false, forceReduced: false },
)

const systemReduced = usePrefersReducedMotion()
const reduced = computed(() => props.forceReduced || systemReduced.value)
const webgl = ref<boolean | null>(null)
const failed = ref(false)
const live = ref(false)
const tabVisible = ref(true)

const SceneComponent = defineAsyncComponent(() => import('@/Components/shell/IdentityCoreScene.vue'))

const showCanvas = computed(() => webgl.value === true && !failed.value)
const motion = computed(() => !reduced.value && tabVisible.value)

function syncTabVisible() {
    tabVisible.value = document.visibilityState !== 'hidden'
}

onMounted(() => {
    webgl.value = detectWebGL()
    document.addEventListener('visibilitychange', syncTabVisible)
    syncTabVisible()
})

onUnmounted(() => {
    document.removeEventListener('visibilitychange', syncTabVisible)
})

const style = computed(() =>
    props.static
        ? { width: `${props.size}px`, height: `${props.size}px` }
        : {
              left: `${props.cx}px`,
              top: `${props.cy}px`,
              width: `${props.size}px`,
              height: `${props.size}px`,
              transform: 'translate(-50%, -50%)',
          },
)

const haloStyle = computed(() => ({
    width: `${props.haloScale * 100}%`,
}))
</script>

<template>
    <div class="identity-core" :style="style" aria-hidden="true">
        <div class="identity-core__halo" :style="haloStyle"></div>
        <div class="identity-core__sphere"></div>
        <div v-if="showCanvas" class="identity-core__canvas" :class="{ 'is-shown': live }">
            <SceneComponent :motion="motion" @ready="live = true" @failure="failed = true" />
        </div>
    </div>
</template>
