import { onMounted, onUnmounted, ref, type Ref } from 'vue'

export interface StageScale {
    root: Ref<HTMLElement | null>
    scale: Ref<number>
}

/**
 * The orbital compositions are authored at a fixed 1440x900 reference frame
 * (exact coordinates for orbits, markers and labels). Rather than
 * re-deriving every position per breakpoint, the whole frame is scaled as
 * one unit to fit the viewport, the same way a stage set is built once and
 * dollied toward or away from the camera.
 */
export function useStageScale(referenceWidth = 1440, referenceHeight = 900, maxScale = 1.25): StageScale {
    const root = ref<HTMLElement | null>(null)
    const scale = ref(1)
    let observer: ResizeObserver | null = null

    function measure() {
        const el = root.value

        if (!el) {
            return
        }

        const parent = el.parentElement

        if (!parent) {
            return
        }

        const availableWidth = parent.clientWidth
        const availableHeight = parent.clientHeight

        if (availableWidth === 0 || availableHeight === 0) {
            return
        }

        scale.value = Math.min(availableWidth / referenceWidth, availableHeight / referenceHeight, maxScale)
    }

    onMounted(() => {
        measure()

        if (root.value?.parentElement && 'ResizeObserver' in window) {
            observer = new ResizeObserver(() => measure())
            observer.observe(root.value.parentElement)
        } else {
            window.addEventListener('resize', measure)
        }
    })

    onUnmounted(() => {
        observer?.disconnect()
        window.removeEventListener('resize', measure)
    })

    return { root, scale }
}
