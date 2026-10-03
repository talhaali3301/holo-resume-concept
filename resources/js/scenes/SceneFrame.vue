<script setup lang="ts">
import { computed, defineAsyncComponent, onErrorCaptured, onMounted, onUnmounted, ref, useAttrs, watch, type Component } from 'vue'
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
const visible = ref(true)
const interacting = ref(false)
const settle = ref(true)
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

const motion = computed(() => !reduced.value && visible.value && (interacting.value || settle.value || props.animating === true))
const showCanvas = computed(() => webgl.value === true && !failed.value && visible.value)

const note = computed(() => {
    if (failed.value) {
        return 'The 3D view is unavailable. The rest of this page still works.'
    }

    if (webgl.value === false) {
        return 'This browser cannot show the 3D view. The written portfolio is ready.'
    }

    return 'Preparing the room'
})

let observer: IntersectionObserver | null = null
let settleTimer = 0
let intersecting = true

function syncVisible() {
    visible.value = intersecting && document.visibilityState !== 'hidden'
}

function onVisibility() {
    syncVisible()
}

onMounted(() => {
    webgl.value = detectWebGL()
    settleTimer = window.setTimeout(() => {
        settle.value = false
    }, 1100)
    document.addEventListener('visibilitychange', onVisibility)

    if (root.value && 'IntersectionObserver' in window) {
        observer = new IntersectionObserver(
            ([entry]) => {
                intersecting = entry?.isIntersecting ?? true
                syncVisible()
            },
            { threshold: 0.08 },
        )
        observer.observe(root.value)
    }
})

onUnmounted(() => {
    window.clearTimeout(settleTimer)
    observer?.disconnect()
    document.removeEventListener('visibilitychange', onVisibility)
})

watch(showCanvas, (value) => {
    if (value) {
        live.value = false
    }
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
    interacting.value = true
}

function onLeave() {
    parallax.value = { x: 0, y: 0 }
    interacting.value = false
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
