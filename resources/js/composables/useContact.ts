import type { InjectionKey } from 'vue'
import { inject } from 'vue'

export const openContactKey: InjectionKey<() => void> = Symbol('openContact')

export function useOpenContact(): () => void {
    return inject(openContactKey, () => {})
}
