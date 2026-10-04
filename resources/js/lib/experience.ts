import type { SkillRef, ViewMode } from '@/types/portfolio'

export function withView(href: string, view: ViewMode): string {
    const [path, query = ''] = href.split('?')
    const params = new URLSearchParams(query)

    if (view === 'standard') {
        params.set('view', 'standard')
    } else {
        params.delete('view')
    }

    const next = params.toString()

    return next ? `${path}?${next}` : path
}

export function nextTrapIndex(current: number, count: number, shift: boolean): number {
    if (count <= 0) {
        return 0
    }

    const index = Number.isFinite(current) ? current : 0

    if (shift) {
        return index <= 0 ? count - 1 : index - 1
    }

    return index >= count - 1 ? 0 : index + 1
}

export function relatedSkills(ids: string[], skills: SkillRef[]): SkillRef[] {
    const byId = new Map(skills.map((skill) => [skill.slug, skill]))
    const seen = new Set<string>()
    const result: SkillRef[] = []

    for (const id of ids) {
        if (seen.has(id)) {
            continue
        }

        const skill = byId.get(id)

        if (!skill) {
            continue
        }

        seen.add(id)
        result.push(skill)
    }

    return result
}

export function pixelRatioCap(): [number, number] {
    if (typeof window === 'undefined') {
        return [1, 1.5]
    }

    const narrow = window.matchMedia('(max-width: 800px)').matches

    return narrow ? [1, 1.5] : [1, 2]
}

export function detectWebGL(): boolean {
    if (typeof document === 'undefined') {
        return false
    }

    try {
        const canvas = document.createElement('canvas')

        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'))
    } catch {
        return false
    }
}

export function statusLabel(status: string | null): string | null {
    switch (status) {
        case 'live':
            return 'Live'
        case 'concept':
            return 'Concept'
        case 'in_progress':
            return 'In progress'
        case 'archived':
            return 'Archived'
        default:
            return null
    }
}

export function categoryLabel(category: string): string {
    switch (category) {
        case 'technology':
            return 'Technologies'
        case 'practice':
            return 'Practices'
        case 'expertise':
            return 'Expertise'
        default:
            return 'Other'
    }
}

export function isCurrentPath(path: string, href: string): boolean {
    const current = path.split('?')[0] ?? '/'
    const target = href.startsWith('http') ? new URL(href).pathname : (href.split('?')[0] ?? href)

    if (target === '/') {
        return current === '/'
    }

    return current === target || current.startsWith(`${target}/`)
}
