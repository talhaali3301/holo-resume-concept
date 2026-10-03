import { onMounted, onUnmounted, ref, type Ref } from 'vue'

export function usePrefersReducedMotion(): Ref<boolean> {
    const reduced = ref(false)
    let media: MediaQueryList | null = null

    const update = () => {
        reduced.value = media?.matches ?? false
    }

    onMounted(() => {
        media = window.matchMedia('(prefers-reduced-motion: reduce)')
        update()
        media.addEventListener('change', update)
    })

    onUnmounted(() => {
        media?.removeEventListener('change', update)
    })

    return reduced
}
