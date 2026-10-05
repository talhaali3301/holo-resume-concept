<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import DepthChrome, { type ScreenId } from '@/Components/shell/DepthChrome.vue'
import { useOpenContact } from '@/composables/useContact'
import { useMediaQuery } from '@/composables/useMediaQuery'
import { useMotionToggle } from '@/composables/useMotionToggle'
import { useStageScale } from '@/composables/useStageScale'
import { withView } from '@/lib/experience'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import type { PageMeta, Shell, Skill, SkillLayer, SkillLink, ViewMode } from '@/types/portfolio'

defineOptions({ layout: PortfolioLayout, inheritAttrs: false })

const props = defineProps<{
    skills: Skill[]
    links: SkillLink[]
    selected: Skill | null
    view: ViewMode
    routes: { index: string; lobby: string; projects: string }
    meta: PageMeta
}>()

const page = usePage<{ shell: Shell }>()
const brand = computed(() => page.props.shell.product)

const openContact = useOpenContact()
const isMobile = useMediaQuery('(max-width: 899px)')
const { root: stageRoot, scale } = useStageScale(1440, 900)
const { reduced, toggle: toggleMotion } = useMotionToggle()

const railRoutes: Record<ScreenId, string> = { lobby: props.routes.lobby, projects: props.routes.projects, skills: props.routes.index, contact: '#' }
const standardHref = computed(() => withView(props.routes.index, 'standard'))

interface LayerSpec {
    key: SkillLayer
    label: string
    title: string
    subtitle: string
}

const LAYERS: LayerSpec[] = [
    { key: 'infrastructure', label: 'DEEP', title: 'Infrastructure', subtitle: 'Where it runs and how it ships' },
    { key: 'application', label: 'MIDDLE', title: 'Application', subtitle: 'Logic, data, and the flows between them' },
    { key: 'interface', label: 'FRONT', title: 'Interface', subtitle: 'What people see and touch' },
]

const skillsByLayer = computed(() => {
    const map = new Map<SkillLayer, Skill[]>()

    for (const layer of LAYERS) {
        map.set(layer.key, [])
    }

    for (const skill of props.skills) {
        map.get(skill.layer)?.push(skill)
    }

    return map
})

const pickedSlug = computed(() => props.selected?.slug ?? null)

const PROJECT_PREVIEW_LIMIT = 3

function usedInText(skill: Skill): string {
    if (skill.projects.length === 0) {
        return `${skill.title} isn't linked to a project yet.`
    }

    return `${skill.title} is used in`
}

function visibleProjects(skill: Skill) {
    return skill.projects.slice(0, PROJECT_PREVIEW_LIMIT)
}

function extraProjectCount(skill: Skill): number {
    return Math.max(0, skill.projects.length - PROJECT_PREVIEW_LIMIT)
}

function pickSkill(slug: string) {
    const skill = props.skills.find((item) => item.slug === slug)

    if (!skill || skill.slug === pickedSlug.value) {
        return
    }

    router.visit(withView(skill.href, props.view), { preserveScroll: true })
}

function setView(next: ViewMode) {
    const href = props.selected?.href ?? props.routes.index
    router.visit(withView(href, next), { preserveScroll: true })
}

const standardLede = computed(() => `${props.skills.length} skills across three layers, read top to bottom.`)

const leaving = ref(false)

function goTo(href: string) {
    if (reduced.value) {
        router.visit(href)

        return
    }

    leaving.value = true
    window.setTimeout(() => router.visit(href), 520)
}

function onNavigate(id: ScreenId) {
    if (id === 'contact') {
        openContact()

        return
    }

    goTo(id === 'lobby' ? props.routes.lobby : id === 'projects' ? props.routes.projects : props.routes.index)
}

function onOpenStandard() {
    goTo(standardHref.value)
}

function onOpenProjects() {
    goTo(props.routes.projects)
}

const stackRef = ref<HTMLElement | null>(null)
let rafId = 0
let tx = 0
let ty = 0
let cx = 0
let cy = 0

function onPointerMove(event: PointerEvent) {
    tx = (event.clientX / window.innerWidth - 0.5) * 2
    ty = (event.clientY / window.innerHeight - 0.5) * 2
}

function onPointerLeave() {
    tx = 0
    ty = 0
}

function resetTilt() {
    stackRef.value?.style.setProperty('--rx', '-7deg')
    stackRef.value?.style.setProperty('--ry', '-16deg')
    stackRef.value?.style.setProperty('--float', '0px')
}

function frame(now: number) {
    if (!reduced.value && stackRef.value) {
        const t = now / 1000
        cx += (tx - cx) * 0.06
        cy += (ty - cy) * 0.06
        const rx = -7 - cy * 3 + Math.sin(t * 0.5) * 0.4
        const ry = -16 + cx * 4.5 + Math.sin(t * 0.37) * 0.7
        stackRef.value.style.setProperty('--rx', `${rx.toFixed(3)}deg`)
        stackRef.value.style.setProperty('--ry', `${ry.toFixed(3)}deg`)
        stackRef.value.style.setProperty('--float', `${(Math.sin(t * 0.6) * 4).toFixed(2)}px`)
    }

    rafId = requestAnimationFrame(frame)
}

watch(reduced, (isReduced) => {
    if (isReduced) {
        resetTilt()
    }
})

onMounted(() => {
    window.addEventListener('pointermove', onPointerMove)
    window.addEventListener('pointerleave', onPointerLeave)
    rafId = requestAnimationFrame(frame)
})

onUnmounted(() => {
    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerleave', onPointerLeave)
    cancelAnimationFrame(rafId)
})
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>

    <main id="content" :class="view === 'gallery' ? 'skills-viewport' : 'skills-standard-page standard-page'">
        <template v-if="view === 'gallery'">
            <div v-if="!isMobile" class="skills-stage-wrap">
                <div
                    ref="stageRoot"
                    class="stage-1440 skills-stage"
                    :class="{ calm: reduced, leaving }"
                    :style="{ transform: `translate(-50%, -50%) scale(${scale})` }"
                >
                    <div class="skills-bloom" aria-hidden="true"></div>

                    <div class="skills-intro">
                        <span class="skills-intro__title">
                            <span class="skills-intro__index">02</span>
                            <h1 class="skills-intro__name">Skills</h1>
                        </span>
                        <span class="skills-intro__desc">One stack, read front to back: what people touch, the logic behind it, and where it runs.</span>
                    </div>

                    <div class="skills-scene">
                        <div ref="stackRef" class="skills-stack">
                            <section
                                v-for="layer in LAYERS"
                                :key="layer.key"
                                class="skills-layer"
                                :data-layer="layer.key"
                            >
                                <div class="skills-layer__head">
                                    <span class="skills-layer__head-title">
                                        <span class="skills-layer__head-label">{{ layer.label }}</span>
                                        <h2 class="skills-layer__head-name">{{ layer.title }}</h2>
                                    </span>
                                    <span class="skills-layer__head-sub">{{ layer.subtitle }}</span>
                                </div>
                                <div class="skills-layer__body">
                                    <div class="skills-layer__chips">
                                        <button
                                            v-for="skill in skillsByLayer.get(layer.key)"
                                            :key="skill.slug"
                                            type="button"
                                            class="skills-chip"
                                            :class="{ 'is-picked': skill.slug === pickedSlug }"
                                            @click="pickSkill(skill.slug)"
                                        >
                                            <span v-if="skill.slug === pickedSlug" class="skills-chip__dot"></span>
                                            {{ skill.title }}
                                        </button>
                                        <p v-if="skillsByLayer.get(layer.key)?.length === 0" class="skills-layer__empty">No skills in this layer yet.</p>
                                    </div>
                                    <div v-if="selected && selected.layer === layer.key" class="skills-layer__detail">
                                        <span>{{ usedInText(selected) }}</span>
                                        <template v-for="(project, i) in visibleProjects(selected)" :key="project.slug">
                                            <a :href="project.href">{{ project.title }}</a><span v-if="i < visibleProjects(selected).length - 1">, </span>
                                        </template>
                                        <span v-if="extraProjectCount(selected) > 0" class="skills-layer__detail-tag">and {{ extraProjectCount(selected) }} more</span>
                                        <span v-if="selected.projects.length && selected.projects.some((p) => p.sample)" class="skills-layer__detail-tag">&middot; sample projects</span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <DepthChrome :current="'skills'" :reduced="reduced" :routes="railRoutes" :standard-href="standardHref" :brand="brand" @toggle-motion="toggleMotion" @navigate="onNavigate" @open-standard="onOpenStandard" @open-projects="onOpenProjects" />

                    <div class="skills-voice">
                        <span class="skills-voice__line">The whole stack, front to back.</span>
                        <span class="skills-voice__tag">Concept &middot; Sample projects &middot; Designed &amp; built by Talha Ali, <a class="credit-link" href="https://robocoders.dev/" target="_blank" rel="noopener noreferrer">Robo Coders</a></span>
                    </div>
                    <div class="skills-hint">Pick a skill to see where it is used</div>
                </div>
            </div>

            <div v-else class="skills-mobile">
                <div class="skills-mobile__head">
                    <p class="skills-mobile__tag">Skills</p>
                    <h1 class="skills-mobile__title">One stack, front to back.</h1>
                    <p class="skills-mobile__desc">What people touch, the logic behind it, and where it runs.</p>
                </div>

                <div v-for="layer in LAYERS" :key="layer.key" class="skills-mobile__layer" :data-layer="layer.key">
                    <div class="skills-layer__head">
                        <span class="skills-layer__head-title">
                            <span class="skills-layer__head-label">{{ layer.label }}</span>
                            <h2 class="skills-layer__head-name">{{ layer.title }}</h2>
                        </span>
                        <span class="skills-layer__head-sub">{{ layer.subtitle }}</span>
                    </div>
                    <div class="skills-layer__body">
                        <div class="skills-layer__chips">
                            <button
                                v-for="skill in skillsByLayer.get(layer.key)"
                                :key="skill.slug"
                                type="button"
                                class="skills-chip"
                                :class="{ 'is-picked': skill.slug === pickedSlug }"
                                @click="pickSkill(skill.slug)"
                            >
                                <span v-if="skill.slug === pickedSlug" class="skills-chip__dot"></span>
                                {{ skill.title }}
                            </button>
                        </div>
                        <div v-if="selected && selected.layer === layer.key" class="skills-layer__detail">
                            <span>{{ usedInText(selected) }}</span>
                            <template v-for="(project, i) in visibleProjects(selected)" :key="project.slug">
                                <a :href="project.href">{{ project.title }}</a><span v-if="i < visibleProjects(selected).length - 1">, </span>
                            </template>
                            <span v-if="extraProjectCount(selected) > 0" class="skills-layer__detail-tag">and {{ extraProjectCount(selected) }} more</span>
                            <span v-if="selected.projects.length && selected.projects.some((p) => p.sample)" class="skills-layer__detail-tag">&middot; sample projects</span>
                        </div>
                    </div>
                </div>

                <div class="projects-mobile__bar">
                    <a class="projects-mobile__link" :href="routes.lobby" @click.prevent="onNavigate('lobby')">Lobby</a>
                    <a class="projects-mobile__link" :href="routes.projects" @click.prevent="onNavigate('projects')">Projects</a>
                    <button type="button" class="projects-mobile__link" @click="openContact">Contact</button>
                </div>
                <p class="credit-line credit-line--center">Concept &middot; Sample projects &middot; Designed &amp; built by Talha Ali, <a class="credit-link" href="https://robocoders.dev/" target="_blank" rel="noopener noreferrer">Robo Coders</a></p>
            </div>
        </template>

        <template v-else>
            <p class="eyebrow">Standard view</p>
            <div class="standard-head mt-3 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="page-title">Skills</h1>
                    <p class="lede">{{ standardLede }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a :href="routes.lobby" class="btn btn-ghost">Lobby</a>
                    <button type="button" class="btn btn-primary" @click="setView('gallery')">Show the stack</button>
                </div>
            </div>

            <div class="skills-standard-layers">
                <section v-for="layer in LAYERS" :key="layer.key" class="skills-standard-layer">
                    <div class="skills-standard-layer__head">
                        <span class="standard-project__sample">{{ layer.label }}</span>
                        <h2 class="standard-project__title">{{ layer.title }}</h2>
                        <p class="standard-project__label">{{ layer.subtitle }}</p>
                    </div>
                    <ul class="skills-standard-list">
                        <li v-for="skill in skillsByLayer.get(layer.key)" :key="skill.slug" class="skills-standard-item">
                            <div class="skills-standard-item__head">
                                <h3 class="skills-standard-item__title">{{ skill.title }}</h3>
                                <span v-if="skill.sample" class="standard-project__sample">Sample</span>
                            </div>
                            <p v-if="skill.description" class="standard-project__text">{{ skill.description }}</p>
                            <p class="skills-standard-item__used">
                                <span>{{ usedInText(skill) }}</span>
                                <template v-for="(project, i) in skill.projects" :key="project.slug">
                                    <a :href="project.href">{{ project.title }}</a><span v-if="i < skill.projects.length - 1">, </span>
                                </template>
                            </p>
                        </li>
                        <li v-if="skillsByLayer.get(layer.key)?.length === 0" class="text-muted">No skills in this layer yet.</li>
                    </ul>
                </section>
            </div>

            <p class="credit-line mt-10">Concept &middot; Sample projects &middot; Designed &amp; built by Talha Ali, <a class="credit-link" href="https://robocoders.dev/" target="_blank" rel="noopener noreferrer">Robo Coders</a></p>
        </template>
    </main>
</template>
