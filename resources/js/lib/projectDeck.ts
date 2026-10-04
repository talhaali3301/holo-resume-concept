export type ProjectSlot = 'front' | 'left' | 'right-1' | 'right-2' | 'right-3' | 'hidden'

interface WindowSlot {
    index: number
    side: 'left' | 'right'
    rank: number
}

/**
 * The deck has room for one reading sheet (the front project) plus up to
 * four flanking panes: one "previous" pane on the left, and up to three
 * "next" panes on the right (the exact geometry calibrated in the mockup,
 * which happens to fit exactly five projects at once). With more projects
 * than that, the window simply slides as `front` changes — the rest are
 * reachable via the arrow keys or the prev/next buttons, the same way
 * Lobby only ever shows four sheets regardless of how many destinations
 * exist.
 */
function paneWindow(front: number, count: number): WindowSlot[] {
    if (count <= 1) {
        return []
    }

    const used = new Set<number>([front])
    const slots: WindowSlot[] = []
    const prev = ((front - 1) % count + count) % count

    if (!used.has(prev)) {
        used.add(prev)
        slots.push({ index: prev, side: 'left', rank: 1 })
    }

    let rank = 1

    for (let step = 1; step < count && rank <= 3; step++) {
        const index = (front + step) % count

        if (used.has(index)) {
            continue
        }

        used.add(index)
        slots.push({ index, side: 'right', rank })
        rank++
    }

    return slots
}

/** Per-project slot for the current `front` index, keyed by project position. */
export function slotMap(front: number, count: number): ProjectSlot[] {
    const slots = new Array<ProjectSlot>(count).fill('hidden')

    if (count === 0) {
        return slots
    }

    const f = ((front % count) + count) % count
    slots[f] = 'front'

    for (const slot of paneWindow(f, count)) {
        slots[slot.index] = slot.side === 'left' ? 'left' : (`right-${slot.rank}` as ProjectSlot)
    }

    return slots
}

const COUNT_WORDS = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten']

export function countWord(count: number): string {
    return COUNT_WORDS[count] ?? String(count)
}
