export interface ExhibitSlot {
    left: number
    top: number
    width: number
    height: number
    opacity: number
    z: number
    titleSize: number
    categorySize: number
}

const CENTER = { x: 706, y: 460 }
const RADII = { x: 580, y: 196 }
const TILT = (4 * Math.PI) / 180
const FRONT_ANGLE = Math.PI / 2
const BASE = { width: 320, height: 214, titleSize: 25, categorySize: 13 }

function angleFor(slotIndex: number, count: number): number {
    const step = (2 * Math.PI) / Math.max(count, 1)

    return FRONT_ANGLE + slotIndex * step
}

function circularDistance(angle: number, from: number): number {
    const twoPi = Math.PI * 2
    let diff = Math.abs(((angle - from + Math.PI) % twoPi) - Math.PI)

    if (diff < 0) {
        diff += twoPi
    }

    return diff
}

/**
 * Exhibits ride one elliptical orbit at different depths. Slot 0 is the
 * front (nearest, largest, brightest); other slots recede symmetrically
 * toward the back as their angular distance from the front grows.
 */
export function exhibitSlot(slotIndex: number, count: number): ExhibitSlot {
    const angle = angleFor(slotIndex, count)
    const distance = circularDistance(angle, FRONT_ANGLE)
    const depth = distance / Math.PI // 0 at front, 1 at the exact back

    const dx = RADII.x * Math.cos(angle)
    const dy = RADII.y * Math.sin(angle)
    const rotatedX = dx * Math.cos(TILT) - dy * Math.sin(TILT)
    const rotatedY = dx * Math.sin(TILT) + dy * Math.cos(TILT)

    const scale = 1 - depth * 0.58
    const opacity = 1 - depth * 0.35
    const width = BASE.width * scale
    const height = BASE.height * scale
    const centerX = CENTER.x + rotatedX
    const centerY = CENTER.y + rotatedY - 40 // lift the track so the front frame's base sits near the spec'd y

    return {
        left: centerX - width / 2,
        top: centerY - height / 2,
        width,
        height,
        opacity,
        z: Math.round((1 - depth) * 100),
        titleSize: Math.max(16, BASE.titleSize * scale),
        categorySize: Math.max(11, BASE.categorySize * scale),
    }
}

/** Slot index for a project at `index`, given the orbit's current `front` index. */
export function slotFor(index: number, front: number, count: number): number {
    return ((index - front) % count + count) % count
}
