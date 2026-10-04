import { describe, expect, it } from 'vitest'
import { isCurrentPath, nextTrapIndex, relatedSkills, withView } from '@/lib/experience'
import { layoutSkills } from '@/lib/skillLayout'

describe('portfolio interaction helpers', () => {
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

    it('matches the current path including nested routes', () => {
        expect(isCurrentPath('/projects/demo', '/projects')).toBe(true)
        expect(isCurrentPath('/projects', '/projects')).toBe(true)
        expect(isCurrentPath('/skills', '/projects')).toBe(false)
        expect(isCurrentPath('/', '/')).toBe(true)
        expect(isCurrentPath('/projects', '/')).toBe(false)
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
