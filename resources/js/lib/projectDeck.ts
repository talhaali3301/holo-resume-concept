export interface PaneSlot {
    index: number
    side: 'left' | 'right'
    rank: number
}

/**
 * The deck has room for one reading sheet (the front project) plus up to
 * four flanking panes: one "previous" pane on the left, and up to three
 * "next" panes on the right (the exact geometry calibrated in the mockup).
 * With five or fewer projects every project is visible at once. With more,
 * the window simply slides as `front` changes — the rest are reachable via
 * the arrow keys or the prev/next buttons, the same way Lobby only ever
 * shows four sheets regardless of how many destinations exist.
 */
export function paneWindow(front: number, count: number): PaneSlot[] {
    if (count <= 1) {
        return []
    }

    const used = new Set<number>([((front % count) + count) % count])
    const slots: PaneSlot[] = []

    const prev = ((front - 1) % count + count) % count

    if (!used.has(prev)) {
        used.add(prev)
        slots.push({ index: prev, side: 'left', rank: 1 })
    }

    let rank = 1

    for (let step = 1; step < count && rank <= 3; step++) {
        const index = ((front + step) % count + count) % count

        if (used.has(index)) {
            continue
        }

        used.add(index)
        slots.push({ index, side: 'right', rank })
        rank++
    }

    return slots
}

const LEFT_PANE = { x: -612, width: 230, rotateY: 40 }
const RIGHT_PANES: Record<number, { x: number; width: number }> = {
    1: { x: 206, width: 210 },
    2: { x: 318, width: 210 },
    3: { x: 430, width: 210 },
}

export function paneGeometry(slot: PaneSlot) {
    if (slot.side === 'left') {
        return { ...LEFT_PANE }
    }

    const geometry = RIGHT_PANES[slot.rank] ?? RIGHT_PANES[3]

    return { x: geometry.x, width: geometry.width, rotateY: -40 }
}

const COUNT_WORDS = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten']

export function countWord(count: number): string {
    return COUNT_WORDS[count] ?? String(count)
}
