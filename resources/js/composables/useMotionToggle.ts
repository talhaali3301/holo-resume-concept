import { computed, ref, type ComputedRef } from 'vue'
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion'

export interface MotionToggle {
    reduced: ComputedRef<boolean>
    toggle: () => void
}

/**
 * Shared "Motion on/off" behaviour: a manual override layered on top of the
 * system's prefers-reduced-motion setting. Either one being "reduced" wins.
 */
export function useMotionToggle(): MotionToggle {
    const systemReduced = usePrefersReducedMotion()
    const override = ref<'full' | 'reduced' | null>(null)
    const reduced = computed(() => (override.value ? override.value === 'reduced' : systemReduced.value))

    function toggle() {
        override.value = reduced.value ? 'full' : 'reduced'
    }

    return { reduced, toggle }
}
