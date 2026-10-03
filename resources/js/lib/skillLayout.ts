export interface SkillPoint {
    slug: string
    position: [number, number, number]
}

const buckets = ['expertise', 'technology', 'practice', 'other'] as const

const height: Record<(typeof buckets)[number], number> = {
    expertise: 2.15,
    technology: 1.32,
    practice: 0.58,
    other: 1.32,
}

const depth: Record<(typeof buckets)[number], number> = {
    expertise: -0.45,
    technology: 0.1,
    practice: 0.62,
    other: -0.45,
}

const width: Record<(typeof buckets)[number], number> = {
    expertise: 2.2,
    technology: 3.0,
    practice: 2.7,
    other: 2.2,
}

function bucket(category: string): (typeof buckets)[number] {
    if (category === 'technology' || category === 'practice' || category === 'expertise') {
        return category
    }

    return 'other'
}

export function layoutSkills(skills: Array<{ slug: string; category: string }>): SkillPoint[] {
    const grouped = new Map<string, Array<{ slug: string; category: string }>>()

    for (const name of buckets) {
        grouped.set(name, [])
    }

    for (const skill of skills) {
        grouped.get(bucket(skill.category))?.push(skill)
    }

    const points: SkillPoint[] = []

    for (const name of buckets) {
        const items = grouped.get(name) ?? []

        items.forEach((skill, index) => {
            const spread = items.length <= 1 ? 0 : index / (items.length - 1) - 0.5

            points.push({
                slug: skill.slug,
                position: [spread * width[name], height[name], depth[name]],
            })
        })
    }

    return points
}
