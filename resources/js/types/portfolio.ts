export interface Identity {
    sample: boolean
    brand: string
    product: string
    name: string | null
    role: string
    stack: string
    headline: string
    lede: string
    body: string
    availability: string
}

export interface ContactLink {
    label: string
    url: string
}

export interface Contact {
    sample: boolean
    email: string | null
    headline: string
    body: string
    links: ContactLink[]
}

export type ProjectStatus = 'live' | 'concept' | 'in_progress' | 'archived'

export interface Project {
    slug: string
    title: string
    category: string
    label: string
    summary: string
    purpose: string
    built: string
    role: string
    technologies: string[]
    features: string[]
    image: string | null
    imageAlt: string
    imageNote: string | null
    demoUrl: string | null
    repositoryUrl: string | null
    caseStudyUrl: string | null
    caseStudy: string | null
    skills: string[]
    status: ProjectStatus | null
    sample: boolean
    href: string
}

export interface SkillRef {
    slug: string
    title: string
    category: string
    href: string
}

export interface RelatedProject {
    slug: string
    title: string
    summary: string
    sample: boolean
    href: string
}

export type SkillLayer = 'interface' | 'application' | 'infrastructure'

export interface Skill {
    slug: string
    title: string
    category: string
    layer: SkillLayer
    description: string
    sample: boolean
    href: string
    projects: RelatedProject[]
}

export interface SkillLink {
    from: string
    to: string
    weight: number
}

export interface Milestone {
    slug: string
    title: string
    period: string
    summary: string
    sample: boolean
}

export interface Destination {
    id: string
    kicker: string
    label: string
    summary: string
    href: string | null
}

export interface NavItem {
    id: string
    label: string
    href: string
}

export interface Shell {
    brand: string
    product: string
    role: string
    sample: boolean
    nav: NavItem[]
    contact: Contact
}

export interface PageMeta {
    title: string
    fullTitle: string
    description: string
}

export type ViewMode = 'gallery' | 'standard'
