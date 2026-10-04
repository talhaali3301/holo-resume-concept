<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import DepthChrome, { type ScreenId } from '@/Components/shell/DepthChrome.vue'
import { useOpenContact } from '@/composables/useContact'
import { useMediaQuery } from '@/composables/useMediaQuery'
import { useMotionToggle } from '@/composables/useMotionToggle'
import { useStageScale } from '@/composables/useStageScale'
import { withView } from '@/lib/experience'
import { countWord, slotMap, type ProjectSlot } from '@/lib/projectDeck'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import type { PageMeta, Project, Shell, SkillRef, ViewMode } from '@/types/portfolio'

defineOptions({ layout: PortfolioLayout, inheritAttrs: false })

const props = defineProps<{
    projects: Project[]
    skills: SkillRef[]
    selected: Project | null
    view: ViewMode
    routes: { index: string; lobby: string; skills: string }
    meta: PageMeta
}>()

const page = usePage<{ shell: Shell }>()
const brand = computed(() => page.props.shell.product)

const openContact = useOpenContact()
const isMobile = useMediaQuery('(max-width: 899px)')
const { root: stageRoot, scale } = useStageScale(1440, 900)
const { reduced, toggle: toggleMotion } = useMotionToggle()

const railRoutes: Record<ScreenId, string> = { lobby: props.routes.lobby, projects: props.routes.index, skills: props.routes.skills, contact: '#' }
const standardHref = computed(() => withView(props.routes.index, 'standard'))
const tagline = computed(() => `${countWord(props.projects.length)[0].toUpperCase()}${countWord(props.projects.length).slice(1)} pieces, one at a time.`)
const standardLede = computed(() => `${countWord(props.projects.length)[0].toUpperCase()}${countWord(props.projects.length).slice(1)} sample builds, read top to bottom.`)

const front = ref(0)

watch(
    () => props.selected?.slug,
    (slug) => {
        if (!slug) {
            return
        }

        const index = props.projects.findIndex((project) => project.slug === slug)

        if (index >= 0) {
            front.value = index
        }
    },
    { immediate: true },
)

interface DeckItem {
    project: Project
    index: number
    slot: ProjectSlot
}

const deck = computed<DeckItem[]>(() => {
    const slots = slotMap(front.value, props.projects.length)

    return props.projects.map((project, index) => ({ project, index, slot: slots[index] }))
})
const frontProject = computed(() => props.projects[front.value] ?? null)
const counter = computed(() => `${String(front.value + 1).padStart(2, '0')} / ${String(props.projects.length).padStart(2, '0')}`)

function pad(n: number) {
    return String(n).padStart(2, '0')
}

const sheen = ref(false)

watch(front, () => {
    sheen.value = false
    nextTick(() => {
        sheen.value = true
    })

    nextTick(() => {
        stageRoot.value?.querySelector<HTMLElement>('[data-slot="front"] .projects-sheet__reading')?.focus()
    })
})

function selectProject(slug: string) {
    const project = props.projects.find((item) => item.slug === slug)

    if (!project || project.slug === frontProject.value?.slug) {
        return
    }

    router.visit(withView(project.href, props.view), { preserveScroll: true })
}

function step(direction: number) {
    if (props.projects.length < 2) {
        return
    }

    const nextIndex = (front.value + direction + props.projects.length) % props.projects.length
    const project = props.projects[nextIndex]

    if (project) {
        router.visit(withView(project.href, props.view), { preserveScroll: true })
    }
}

function setView(next: ViewMode) {
    const href = props.selected?.href ?? props.routes.index
    router.visit(withView(href, next), { preserveScroll: true })
}

function onKeydown(event: KeyboardEvent) {
    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
        return
    }

    if (event.key === 'ArrowLeft') {
        event.preventDefault()
        step(-1)
    } else if (event.key === 'ArrowRight') {
        event.preventDefault()
        step(1)
    }
}

const leaving = ref(false)

function onNavigate(id: ScreenId) {
    if (id === 'contact') {
        openContact()

        return
    }

    const href = id === 'lobby' ? props.routes.lobby : id === 'skills' ? props.routes.skills : props.routes.index

    if (reduced.value) {
        router.visit(href)

        return
    }

    leaving.value = true
    window.setTimeout(() => router.visit(href), 520)
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
    stackRef.value?.style.setProperty('--rx', '0deg')
    stackRef.value?.style.setProperty('--ry', '0deg')
    stackRef.value?.style.setProperty('--float', '0px')
}

function frame(now: number) {
    if (!reduced.value && stackRef.value) {
        const t = now / 1000
        cx += (tx - cx) * 0.06
        cy += (ty - cy) * 0.06
        const rx = -cy * 3 + Math.sin(t * 0.5) * 0.4
        const ry = cx * 4.5 + Math.sin(t * 0.37) * 0.7
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
    window.addEventListener('keydown', onKeydown)
    rafId = requestAnimationFrame(frame)
})

onUnmounted(() => {
    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerleave', onPointerLeave)
    window.removeEventListener('keydown', onKeydown)
    cancelAnimationFrame(rafId)
})
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>

    <main id="content" :class="view === 'gallery' ? 'projects-viewport' : 'projects-standard standard-page'">
        <template v-if="view === 'gallery'">
            <div v-if="!isMobile" class="projects-stage-wrap">
                <div
                    ref="stageRoot"
                    class="stage-1440 projects-stage"
                    :class="{ calm: reduced, leaving }"
                    :style="{ transform: `translate(-50%, -50%) scale(${scale})` }"
                >
                    <div class="projects-bloom" aria-hidden="true"></div>

                    <div class="projects-scene">
                        <div ref="stackRef" class="projects-stack">
                            <div class="projects-backdrop" aria-hidden="true">
                                <span class="projects-backdrop__title">
                                    <span class="projects-backdrop__index">01</span>
                                    <h1 class="projects-backdrop__name">Projects</h1>
                                </span>
                                <span class="projects-backdrop__desc">Step through the gallery without losing your place.</span>
                            </div>

                            <component
                                :is="item.slot === 'front' ? 'article' : item.slot === 'hidden' ? 'div' : 'button'"
                                v-for="item in deck"
                                :key="item.project.slug"
                                class="projects-sheet"
                                :class="[`slot-${item.slot}`, { sheen: sheen && item.slot === 'front' }]"
                                :data-slot="item.slot"
                                :type="item.slot !== 'front' && item.slot !== 'hidden' ? 'button' : undefined"
                                :aria-label="item.slot !== 'front' && item.slot !== 'hidden' ? `Bring ${item.project.title} forward` : undefined"
                                :aria-hidden="item.slot === 'hidden' ? 'true' : undefined"
                                :tabindex="item.slot === 'hidden' ? -1 : undefined"
                                :inert="item.slot === 'hidden' ? true : undefined"
                                @click="item.slot !== 'front' && item.slot !== 'hidden' && selectProject(item.project.slug)"
                            >
                                <div class="projects-sheet__pane">
                                    <span class="projects-sheet__pane-index">{{ pad(item.index + 1) }}</span>
                                    <span class="projects-sheet__pane-title">{{ item.project.title }}</span>
                                    <span class="projects-sheet__pane-reveal">
                                        <span class="projects-sheet__pane-desc">{{ item.project.label }}</span>
                                        <span class="projects-chip">
                                            Bring forward
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 8h11M8 3l5 5-5 5" /></svg>
                                        </span>
                                    </span>
                                </div>

                                <div class="projects-sheet__reading" tabindex="-1">
                                    <div class="projects-sheet__meta-row">
                                        <span class="projects-sheet__counter">{{ counter }}<template v-if="item.project.sample">&nbsp;&middot;&nbsp;SAMPLE PROJECT</template></span>
                                        <span class="projects-sheet__focus"><span class="projects-sheet__focus-dot"></span>IN FOCUS</span>
                                    </div>
                                    <h2 class="projects-sheet__title">{{ item.project.title }}</h2>
                                    <div v-if="item.project.image" class="projects-sheet__shot">
                                        <img :src="item.project.image" :alt="item.project.imageAlt" loading="lazy" />
                                    </div>
                                    <div class="projects-sheet__grid">
                                        <div class="projects-sheet__field">
                                            <span class="projects-sheet__field-label">THE BRIEF</span>
                                            <span class="projects-sheet__field-value">{{ item.project.purpose }}</span>
                                        </div>
                                        <div class="projects-sheet__field">
                                            <span class="projects-sheet__field-label">WHAT I BUILT</span>
                                            <span class="projects-sheet__field-value">{{ item.project.built }}</span>
                                        </div>
                                    </div>
                                    <div class="projects-sheet__stack">
                                        <span v-for="tech in item.project.technologies" :key="tech" class="projects-sheet__techchip">{{ tech }}</span>
                                    </div>
                                    <div class="projects-sheet__actions">
                                        <div class="projects-sheet__links">
                                            <a v-if="item.project.caseStudyUrl" class="projects-sheet__case" :href="item.project.caseStudyUrl" target="_blank" rel="noopener noreferrer">
                                                <span>Read the case study</span>
                                                <span class="projects-sheet__case-icon">
                                                    <svg width="20" height="20" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                                                </span>
                                            </a>
                                            <a v-if="item.project.repositoryUrl" class="projects-sheet__source" :href="item.project.repositoryUrl" target="_blank" rel="noopener noreferrer">View source</a>
                                        </div>
                                        <div class="projects-sheet__nav">
                                            <button type="button" class="projects-sheet__nav-btn" aria-label="Previous project" :disabled="projects.length < 2" @click.stop="step(-1)">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M11 3L5 9l6 6" /></svg>
                                            </button>
                                            <button type="button" class="projects-sheet__nav-btn" aria-label="Next project" :disabled="projects.length < 2" @click.stop="step(1)">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3l6 6-6 6" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </component>

                            <p v-if="projects.length === 0" class="projects-empty">No exhibits yet. Add projects in content/portfolio.php.</p>
                        </div>
                    </div>

                    <DepthChrome :current="'projects'" :reduced="reduced" :routes="railRoutes" :standard-href="standardHref" :brand="brand" @toggle-motion="toggleMotion" @navigate="onNavigate" />

                    <div class="projects-voice">
                        <span class="projects-voice__line">{{ tagline }}</span>
                        <span class="projects-voice__tag">Concept &middot; Sample content</span>
                    </div>
                    <div class="projects-hint">Click any pane to bring it forward</div>
                </div>
            </div>

            <div v-else class="projects-mobile">
                <div class="projects-mobile__head">
                    <p class="projects-mobile__tag">Projects</p>
                    <h1 class="projects-mobile__title">{{ tagline }}</h1>
                </div>

                <p v-if="projects.length === 0" class="projects-empty">No exhibits yet. Add projects in content/portfolio.php.</p>

                <template v-else-if="frontProject">
                    <div class="projects-mobile__tabs" role="tablist" aria-label="Projects">
                        <button
                            v-for="(project, i) in projects"
                            :key="project.slug"
                            type="button"
                            class="projects-mobile__tab"
                            :class="{ 'is-active': i === front }"
                            role="tab"
                            :aria-selected="i === front"
                            @click="selectProject(project.slug)"
                        >
                            <span class="projects-mobile__tab-index">{{ pad(i + 1) }}</span>{{ project.title }}
                        </button>
                    </div>

                    <div class="projects-mobile__sheet">
                        <div class="projects-sheet__meta-row">
                            <span class="projects-sheet__counter">{{ counter }}<template v-if="frontProject.sample">&nbsp;&middot;&nbsp;SAMPLE PROJECT</template></span>
                        </div>
                        <h2 class="projects-sheet__title projects-sheet__title--mobile">{{ frontProject.title }}</h2>
                        <div v-if="frontProject.image" class="projects-sheet__shot">
                            <img :src="frontProject.image" :alt="frontProject.imageAlt" loading="lazy" />
                        </div>
                        <div class="projects-sheet__grid projects-sheet__grid--mobile">
                            <div class="projects-sheet__field">
                                <span class="projects-sheet__field-label">THE BRIEF</span>
                                <span class="projects-sheet__field-value">{{ frontProject.purpose }}</span>
                            </div>
                            <div class="projects-sheet__field">
                                <span class="projects-sheet__field-label">WHAT I BUILT</span>
                                <span class="projects-sheet__field-value">{{ frontProject.built }}</span>
                            </div>
                        </div>
                        <div class="projects-sheet__stack">
                            <span v-for="tech in frontProject.technologies" :key="tech" class="projects-sheet__techchip">{{ tech }}</span>
                        </div>
                        <div class="projects-sheet__links">
                            <a v-if="frontProject.caseStudyUrl" class="projects-sheet__case" :href="frontProject.caseStudyUrl" target="_blank" rel="noopener noreferrer">
                                <span>Read the case study</span>
                                <span class="projects-sheet__case-icon">
                                    <svg width="20" height="20" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                                </span>
                            </a>
                            <a v-if="frontProject.repositoryUrl" class="projects-sheet__source" :href="frontProject.repositoryUrl" target="_blank" rel="noopener noreferrer">View source</a>
                        </div>
                        <div class="projects-mobile__nav">
                            <button type="button" class="projects-sheet__nav-btn" aria-label="Previous project" :disabled="projects.length < 2" @click="step(-1)">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M11 3L5 9l6 6" /></svg>
                            </button>
                            <button type="button" class="projects-sheet__nav-btn" aria-label="Next project" :disabled="projects.length < 2" @click="step(1)">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3l6 6-6 6" /></svg>
                            </button>
                        </div>
                    </div>
                </template>

                <div class="projects-mobile__bar">
                    <a class="projects-mobile__link" :href="routes.lobby" @click.prevent="onNavigate('lobby')">Lobby</a>
                    <a class="projects-mobile__link" :href="routes.skills" @click.prevent="onNavigate('skills')">Skills</a>
                    <button type="button" class="projects-mobile__link" @click="openContact">Contact</button>
                </div>
            </div>
        </template>

        <template v-else>
            <p class="eyebrow">Standard view</p>
            <div class="standard-head mt-3 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="page-title">Projects</h1>
                    <p class="lede">{{ standardLede }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a :href="routes.lobby" class="btn btn-ghost">Lobby</a>
                    <button type="button" class="btn btn-primary" @click="setView('gallery')">Show the hall</button>
                </div>
            </div>
            <p v-if="projects.length === 0" class="mt-10 text-muted">No exhibits yet. Add projects in content/portfolio.php.</p>
            <ol v-else class="standard-projects">
                <li v-for="(project, i) in projects" :id="`project-${project.slug}`" :key="project.slug" class="standard-project">
                    <div class="standard-project__head">
                        <span class="standard-project__index">{{ pad(i + 1) }}</span>
                        <div>
                            <div class="standard-project__title-row">
                                <h2 class="standard-project__title">{{ project.title }}</h2>
                                <span v-if="project.sample" class="standard-project__sample">Sample project</span>
                            </div>
                            <p class="standard-project__label">{{ project.label }}</p>
                        </div>
                    </div>
                    <div class="standard-project__grid">
                        <div>
                            <span class="case-file__label">The brief</span>
                            <p class="standard-project__text">{{ project.purpose }}</p>
                        </div>
                        <div>
                            <span class="case-file__label">What I built</span>
                            <p class="standard-project__text">{{ project.built }}</p>
                        </div>
                    </div>
                    <div class="standard-project__stack">
                        <span v-for="tech in project.technologies" :key="tech" class="projects-sheet__techchip">{{ tech }}</span>
                    </div>
                    <div v-if="project.caseStudyUrl || project.repositoryUrl" class="standard-project__links">
                        <a v-if="project.caseStudyUrl" class="btn btn-primary" :href="project.caseStudyUrl" target="_blank" rel="noopener noreferrer">Read the case study</a>
                        <a v-if="project.repositoryUrl" class="btn-quiet" :href="project.repositoryUrl" target="_blank" rel="noopener noreferrer">View source</a>
                    </div>
                </li>
            </ol>
        </template>
    </main>
</template>
