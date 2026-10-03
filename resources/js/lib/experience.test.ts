import { describe, expect, it } from 'vitest'
import { layoutFrames, nextTrapIndex, relatedSkills, resolveView, withView } from '@/lib/experience'
import { layoutSkills } from '@/lib/skillLayout'

describe('portfolio interaction helpers', () => {
    it('resolves only the standard view as standard', () => {
        expect(resolveView('standard')).toBe('standard')
        expect(resolveView('gallery')).toBe('gallery')
        expect(resolveView('nope')).toBe('gallery')
        expect(resolveView(null)).toBe('gallery')
    })

    it('adds and removes the view query without dropping other parameters', () => {
        expect(withView('/projects/demo', 'standard')).toBe('/projects/demo?view=standard')
        expect(withView('/projects/demo?view=standard', 'gallery')).toBe('/projects/demo')
        expect(withView('/projects?foo=1', 'standard')).toBe('/projects?foo=1&view=standard')
    })

    it('cycles focus inside a trap', () => {
        expect(nextTrapIndex(0, 3, false)).toBe(1)
        expect(nextTrapIndex(2, 3, false)).toBe(0)
        expect(nextTrapIndex(0, 3, true)).toBe(2)
        expect(nextTrapIndex(-1, 3, false)).toBe(0)
        expect(nextTrapIndex(0, 0, false)).toBe(0)
    })

    it('drops unknown and duplicate skill references', () => {
        const skills = [
            { slug: 'vue', title: 'Vue', category: 'technology', href: '/skills/vue' },
            { slug: 'laravel', title: 'Laravel', category: 'technology', href: '/skills/laravel' },
        ]

        expect(relatedSkills(['missing', 'vue', 'vue', 'laravel'], skills).map((skill) => skill.slug)).toEqual([
            'vue',
            'laravel',
        ])
    })

    it('places gallery frames on alternating walls', () => {
        const frames = layoutFrames(4)

        expect(frames[0]?.position[0]).toBeLessThan(0)
        expect(frames[1]?.position[0]).toBeGreaterThan(0)
        expect(frames[2]?.position[2]).toBeLessThan(frames[0]?.position[2] ?? 0)
        expect(layoutFrames(0)).toEqual([])
    })

    it('keeps skills in one category from sharing a position', () => {
        const points = layoutSkills([
            { slug: 'vue', category: 'technology' },
            { slug: 'laravel', category: 'technology' },
            { slug: 'testing', category: 'practice' },
            { slug: 'unknown', category: 'made-up' },
        ])
        const vue = points.find((point) => point.slug === 'vue')
        const laravel = points.find((point) => point.slug === 'laravel')

        expect(vue?.position).not.toEqual(laravel?.position)
        expect(points.find((point) => point.slug === 'unknown')).toBeTruthy()
        expect(points.find((point) => point.slug === 'testing')?.position[1]).toBeLessThan(vue?.position[1] ?? 0)
    })
})
