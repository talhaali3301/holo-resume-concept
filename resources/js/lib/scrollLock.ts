let locks = 0

export function lockScroll(): void {
    if (typeof document === 'undefined') {
        return
    }

    locks += 1
    document.body.style.overflow = 'hidden'
}

export function unlockScroll(): void {
    if (typeof document === 'undefined') {
        return
    }

    locks = Math.max(0, locks - 1)

    if (locks === 0) {
        document.body.style.overflow = ''
    }
}
