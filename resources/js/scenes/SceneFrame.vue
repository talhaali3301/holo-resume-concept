<script setup lang="ts">
import { computed, defineAsyncComponent, onErrorCaptured, onMounted, onUnmounted, ref, useAttrs, type Component } from 'vue'
import { detectWebGL } from '@/lib/experience'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
    scene: 'lobby' | 'hall' | 'observatory'
    bindings?: Record<string, unknown>
    animating?: boolean
}>()

const emit = defineEmits<{
    select: [id: string]
    hover: [id: string | null]
    marker: [id: string]
}>()

const attrs = useAttrs()
const reduced = usePrefersReducedMotion()
const root = ref<HTMLElement | null>(null)
const webgl = ref<boolean | null>(null)
const failed = ref(false)
const live = ref(false)
const containerReady = ref(false)
const tabVisible = ref(true)
const parallax = ref({ x: 0, y: 0 })

const loaders: Record<'lobby' | 'hall' | 'observatory', () => Promise<Component>> = {
    lobby: () => import('@/scenes/LobbyScene.vue'),
    hall: () => import('@/scenes/HallScene.vue'),
    observatory: () => import('@/scenes/ObservatoryScene.vue'),
}

const sceneComponent = defineAsyncComponent({
    loader: () => loaders[props.scene](),
    onError: () => {
        failed.value = true
    },
})

onErrorCaptured(() => {
    failed.value = true

    return false
})

// The canvas mounts once, as soon as webgl is available, it hasn't failed, and the
// stage has a real measured size. It then stays mounted for the life of this screen:
// pausing happens through `motion`/the render loop, not by tearing the canvas down,
// so the TresCanvas `ready` event only ever needs to fire once.
const showCanvas = computed(() => webgl.value === true && !failed.value && containerReady.value)
// Rooms carry a continuous slow idle drift and light shimmer, so motion stays
// on whenever it's allowed at all (tab visible, reduced-motion not requested)
// rather than only during the first settle or while the pointer moves.
const motion = computed(() => !reduced.value && tabVisible.value)

const note = computed(() => {
    if (failed.value) {
        return 'The 3D view is unavailable. The rest of this page still works.'
    }

    if (webgl.value === false) {
        return 'This browser cannot show the 3D view. The written portfolio is ready.'
    }

    return 'Preparing the room'
})

let resizeObserver: ResizeObserver | null = null

function syncTabVisible() {
    tabVisible.value = document.visibilityState !== 'hidden'
}

function measureContainer() {
    if (containerReady.value || !root.value) {
        return
    }

    if (root.value.clientWidth > 0 && root.value.clientHeight > 0) {
        containerReady.value = true
        resizeObserver?.disconnect()
        resizeObserver = null
    }
}

onMounted(() => {
    webgl.value = detectWebGL()
    document.addEventListener('visibilitychange', syncTabVisible)
    syncTabVisible()
    measureContainer()

    if (!containerReady.value && root.value && 'ResizeObserver' in window) {
        resizeObserver = new ResizeObserver(() => measureContainer())
        resizeObserver.observe(root.value)
    }
})

onUnmounted(() => {
    resizeObserver?.disconnect()
    document.removeEventListener('visibilitychange', syncTabVisible)
})

function onMove(event: PointerEvent) {
    if (!root.value || event.pointerType === 'touch') {
        return
    }

    const rect = root.value.getBoundingClientRect()

    if (rect.width === 0 || rect.height === 0) {
        return
    }

    parallax.value = {
        x: ((event.clientX - rect.left) / rect.width) * 2 - 1,
        y: ((event.clientY - rect.top) / rect.height) * 2 - 1,
    }
}

function onLeave() {
    parallax.value = { x: 0, y: 0 }
}
</script>

<template>
    <div ref="root" class="stage" v-bind="attrs" @pointermove="onMove" @pointerleave="onLeave">
        <div v-if="showCanvas" class="stage__media" :class="{ 'is-shown': live }" aria-hidden="true">
            <component
                :is="sceneComponent"
                v-bind="bindings"
                :motion="motion"
                :parallax-x="parallax.x"
                :parallax-y="parallax.y"
                @select="emit('select', $event)"
                @hover="emit('hover', $event)"
                @marker="emit('marker', $event)"
                @ready="live = true"
                @failure="failed = true"
            />
        </div>
        <div v-if="!live || failed || webgl === false" class="stage__fallback">
            <div class="absolute inset-0" aria-hidden="true">
                <slot name="fallback" />
            </div>
            <p class="stage__note" role="status">{{ note }}</p>
        </div>
    </div>
</template>
