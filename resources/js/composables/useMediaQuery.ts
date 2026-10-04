import { onMounted, onUnmounted, ref, type Ref } from 'vue'

export function useMediaQuery(query: string): Ref<boolean> {
    const matches = ref(typeof window !== 'undefined' ? window.matchMedia(query).matches : false)
    let media: MediaQueryList | null = null

    const update = () => {
        matches.value = media?.matches ?? false
    }

    onMounted(() => {
        media = window.matchMedia(query)
        update()
        media.addEventListener('change', update)
    })

    onUnmounted(() => {
        media?.removeEventListener('change', update)
    })

    return matches
}
