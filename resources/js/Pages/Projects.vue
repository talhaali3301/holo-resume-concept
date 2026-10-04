<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import ExhibitPreview from '@/Components/projects/ExhibitPreview.vue'
import IdentityCore from '@/Components/shell/IdentityCore.vue'
import { useStageScale } from '@/composables/useStageScale'
import { relatedSkills, statusLabel, withView } from '@/lib/experience'
import { exhibitSlot, slotFor } from '@/lib/exhibitOrbit'
import { lockScroll, unlockScroll } from '@/lib/scrollLock'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import type { PageMeta, Project, SkillRef, ViewMode } from '@/types/portfolio'

defineOptions({ layout: PortfolioLayout, inheritAttrs: false })

const props = defineProps<{
    projects: Project[]
    skills: SkillRef[]
    selected: Project | null
    view: ViewMode
    routes: { index: string; lobby: string; skills: string }
    meta: PageMeta
}>()

const { root: stageRoot, scale } = useStageScale(1440, 900)
const trigger = ref<HTMLElement | null>(null)
const hovered = ref<string | null>(null)
const front = ref(0)
const panelRoot = ref<HTMLElement | null>(null)
const closeButton = ref<HTMLButtonElement | null>(null)

const previewVariants = ['schedule', 'grid', 'pulse', 'contour', 'hub', 'lanes'] as const

function previewFor(index: number) {
    return previewVariants[index % previewVariants.length]
}

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

const CORE = { x: 720, y: 300 }

const exhibits = computed(() =>
    props.projects.map((project, index) => ({
        project,
        slot: slotFor(index, front.value, props.projects.length),
        geometry: exhibitSlot(slotFor(index, front.value, props.projects.length), props.projects.length),
    })),
)

const frontProject = computed(() => props.projects[front.value] ?? null)
const counter = computed(() => `${String(front.value + 1).padStart(2, '0')} / ${String(props.projects.length).padStart(2, '0')}`)

function openExhibit(slug: string, event?: Event) {
    const project = props.projects.find((item) => item.slug === slug)

    if (!project) {
        return
    }

    trigger.value = event?.currentTarget instanceof HTMLElement ? event.currentTarget : null
    router.visit(withView(project.href, props.view), { preserveScroll: true })
}

function closePanel() {
    const slug = props.selected?.slug
    const focusTarget = trigger.value

    router.visit(withView(props.routes.index, props.view), {
        preserveScroll: true,
        onFinish: () => {
            const fallback = slug ? document.getElementById(`exhibit-${slug}`) : null
            ;(focusTarget ?? fallback)?.focus()
        },
    })
}

function step(direction: number) {
    if (props.projects.length === 0) {
        return
    }

    front.value = (front.value + direction + props.projects.length) % props.projects.length

    if (props.selected) {
        const project = props.projects[front.value]

        if (project) {
            router.visit(withView(project.href, props.view), { preserveScroll: true })
        }
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

    if (event.key === 'Escape' && props.selected) {
        event.preventDefault()
        closePanel()

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

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => window.removeEventListener('keydown', onKeydown))

watch(
    () => props.selected,
    async (selected) => {
        if (!selected) {
            return
        }

        lockScroll()
        await nextTick()
        closeButton.value?.focus()
    },
    { immediate: true },
)

watch(
    () => props.selected,
    (selected, previous) => {
        if (!selected && previous) {
            unlockScroll()
        }
    },
)

onUnmounted(() => {
    if (props.selected) {
        unlockScroll()
    }
})

function panelKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        event.preventDefault()
        closePanel()
    }
}

const linkedSkills = computed(() => (props.selected ? relatedSkills(props.selected.skills, props.skills) : []))
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>
    <main id="content" :class="view === 'gallery' ? 'room-stage' : 'standard-page frame'">
        <template v-if="view === 'gallery'">
            <div class="orbit-room orbit-room--desktop" :inert="selected ? true : undefined">
                <div ref="stageRoot" class="stage-1440" :style="{ transform: `translate(-50%, -50%) scale(${scale})` }">
                    <p class="hall-room-tag">ROOM 01</p>
                    <h1 class="hall-title">Projects Hall</h1>
                    <p class="hall-subtitle">
                        An archive of built systems.
                        {{ projects.length }} {{ projects.length === 1 ? 'exhibit' : 'exhibits' }}<template v-if="projects.some((p) => p.sample)">, sample data</template>.
                    </p>

                    <svg class="orbit-svg" viewBox="0 0 1440 900" aria-hidden="true">
                        <ellipse cx="706" cy="420" rx="800" ry="300" class="orbit-line" opacity="0.1" transform="rotate(4 706 420)" />
                        <ellipse cx="706" cy="420" rx="580" ry="196" transform="rotate(4 706 420)" class="orbit-line" opacity="0.4" />

                        <line
                            v-for="exhibit in exhibits"
                            :key="`line-${exhibit.project.slug}`"
                            :x1="CORE.x"
                            :y1="CORE.y"
                            :x2="exhibit.geometry.left + exhibit.geometry.width / 2"
                            :y2="exhibit.geometry.top + exhibit.geometry.height / 2"
                            class="connection-line"
                            :class="{
                                'is-active': hovered === exhibit.project.slug || (selected && exhibit.slot === 0),
                                'is-selected': selected?.slug === exhibit.project.slug,
                            }"
                        />
                    </svg>

                    <IdentityCore :cx="CORE.x" :cy="CORE.y" :size="56" :halo-scale="4" />

                    <button
                        v-for="exhibit in exhibits"
                        :id="`exhibit-${exhibit.project.slug}`"
                        :key="exhibit.project.slug"
                        type="button"
                        class="exhibit-frame"
                        :class="{ 'is-front': exhibit.slot === 0, 'is-selected': selected?.slug === exhibit.project.slug }"
                        :style="{
                            left: `${exhibit.geometry.left}px`,
                            top: `${exhibit.geometry.top}px`,
                            width: `${exhibit.geometry.width}px`,
                            height: `${exhibit.geometry.height}px`,
                            opacity: selected && selected.slug !== exhibit.project.slug ? 0.38 : exhibit.geometry.opacity,
                            zIndex: exhibit.geometry.z,
                        }"
                        :aria-current="selected?.slug === exhibit.project.slug ? 'true' : undefined"
                        @mouseenter="hovered = exhibit.project.slug"
                        @mouseleave="hovered = null"
                        @focus="hovered = exhibit.project.slug"
                        @blur="hovered = null"
                        @click="openExhibit(exhibit.project.slug, $event)"
                    >
                        <span v-if="selected?.slug === exhibit.project.slug" class="exhibit-frame__case-tag">CASE FILE OPEN</span>
                        <span class="exhibit-frame__preview">
                            <img v-if="exhibit.project.image" :src="exhibit.project.image" :alt="exhibit.project.imageAlt" loading="lazy" />
                            <ExhibitPreview v-else :variant="previewFor(projects.indexOf(exhibit.project))" />
                        </span>
                        <span class="exhibit-frame__plate">
                            <span class="exhibit-frame__plate-index">{{ String(projects.indexOf(exhibit.project) + 1).padStart(2, '0') }}</span>
                            <span class="exhibit-frame__title" :style="{ fontSize: `${exhibit.geometry.titleSize}px` }">{{ exhibit.project.title }}</span>
                            <span class="exhibit-frame__category" :style="{ fontSize: `${exhibit.geometry.categorySize}px` }">{{ exhibit.project.category }}</span>
                        </span>
                    </button>

                    <div v-if="projects.length === 0" class="hall-empty">No exhibits yet. Add projects in content/portfolio.php.</div>

                    <div class="hall-nav">
                        <button type="button" class="hall-nav__btn" aria-label="Previous exhibit" @click="step(-1)">&larr;</button>
                        <span class="hall-nav__counter">{{ counter }}</span>
                        <button type="button" class="hall-nav__btn" aria-label="Next exhibit" @click="step(1)">&rarr;</button>
                    </div>
                    <p class="hall-nav__hint">Select an exhibit to open its case file.</p>

                    <div class="hall-room-links">
                        <Link :href="routes.lobby" class="room-link">
                            <span class="marker room-link__marker"><svg width="40%" height="40%" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="1.4" /></svg></span>
                            <span class="room-link__text"><span class="room-link__index">00 &middot; BACK</span><span class="room-link__name">Lobby</span></span>
                        </Link>
                        <Link :href="routes.skills" class="room-link hall-room-links__next">
                            <span class="room-link__text" style="text-align: right"><span class="room-link__index">02 &middot; NEXT ROOM</span><span class="room-link__name">Skills Observatory</span></span>
                            <span class="marker room-link__marker"><svg width="40%" height="40%" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.4" /></svg></span>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="orbit-room orbit-room--mobile" :inert="selected ? true : undefined">
                <div class="hall-mobile">
                    <p class="hall-room-tag">ROOM 01</p>
                    <h1 class="hall-title hall-title--mobile">Projects Hall</h1>
                    <div class="hall-mobile__track">
                        <div v-for="project in projects" :key="project.slug" class="hall-mobile__slide">
                            <button type="button" class="exhibit-frame is-front hall-mobile__frame" @click="openExhibit(project.slug, $event)">
                                <span class="exhibit-frame__preview">
                                    <img v-if="project.image" :src="project.image" :alt="project.imageAlt" loading="lazy" />
                                    <ExhibitPreview v-else :variant="previewFor(projects.indexOf(project))" />
                                </span>
                                <span class="exhibit-frame__plate">
                                    <span class="exhibit-frame__plate-index">{{ String(projects.indexOf(project) + 1).padStart(2, '0') }}</span>
                                    <span class="exhibit-frame__title">{{ project.title }}</span>
                                    <span class="exhibit-frame__category">{{ project.category }}</span>
                                </span>
                            </button>
                        </div>
                    </div>
                    <p class="hall-nav__hint">Swipe, then tap an exhibit to open its case file.</p>
                </div>
                <div class="hall-mobile__bar">
                    <Link :href="routes.lobby" class="room-link"><span class="marker room-link__marker" style="width: 36px; height: 36px"><svg width="40%" height="40%" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="1.4" /></svg></span></Link>
                    <Link :href="routes.skills" class="room-link"><span class="marker room-link__marker" style="width: 36px; height: 36px"><svg width="40%" height="40%" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="1.4" /></svg></span></Link>
                </div>
            </div>
        </template>

        <template v-else>
            <p class="eyebrow">Standard view</p>
            <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="page-title">Projects</h1>
                    <p class="lede">An archive of built systems, read as a list.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link :href="routes.lobby" class="btn btn-ghost">Lobby</Link>
                    <button type="button" class="btn btn-primary" @click="setView('gallery')">Show the hall</button>
                </div>
            </div>
            <p v-if="projects.length === 0" class="mt-10 text-muted">No exhibits yet. Add projects in content/portfolio.php.</p>
            <ul v-else class="standard-list mt-8">
                <li v-for="project in projects" :key="project.slug">
                    <button type="button" class="standard-row" @click="openExhibit(project.slug, $event)">
                        <span class="standard-row__index">{{ String(projects.indexOf(project) + 1).padStart(2, '0') }}</span>
                        <span>
                            <span class="standard-row__title block">{{ project.title }}</span>
                            <span class="standard-row__meta">{{ project.category }} &middot; {{ project.technologies.join(' / ') }}</span>
                        </span>
                        <span class="standard-row__arrow" aria-hidden="true">&rarr;</span>
                    </button>
                </li>
            </ul>
        </template>

        <div
            v-if="selected"
            ref="panelRoot"
            class="case-file"
            role="dialog"
            aria-modal="true"
            aria-labelledby="case-file-title"
            @keydown="panelKeydown"
        >
            <div class="flex items-start justify-between gap-3">
                <p class="meta">CASE FILE {{ String(front + 1).padStart(2, '0') }} / {{ String(projects.length).padStart(2, '0') }}</p>
                <div class="flex items-center gap-2">
                    <span v-if="selected.sample" class="badge badge-gold">Sample data</span>
                    <button ref="closeButton" type="button" class="btn btn-ghost" style="min-height: 44px" @click="closePanel">Close</button>
                </div>
            </div>
            <h2 id="case-file-title" class="mt-4 mb-1 font-heading text-4xl font-normal tracking-tight">{{ selected.title }}</h2>
            <p class="mb-0" style="color: var(--color-gold); font-size: 0.85rem; letter-spacing: 0.04em">{{ selected.category }}</p>
            <p class="mt-3 text-sm leading-relaxed" style="color: var(--color-bright)">{{ selected.summary }}</p>

            <dl class="mt-4">
                <div v-if="selected.purpose" class="case-file__row">
                    <dt class="case-file__label">Purpose</dt>
                    <dd class="case-file__value">{{ selected.purpose }}</dd>
                </div>
                <div v-if="selected.role" class="case-file__row">
                    <dt class="case-file__label">Role</dt>
                    <dd class="case-file__value">{{ selected.role }}</dd>
                </div>
                <div v-if="selected.technologies.length" class="case-file__row">
                    <dt class="case-file__label">Stack</dt>
                    <dd class="case-file__value">{{ selected.technologies.join(' / ') }}</dd>
                </div>
                <div v-if="selected.features.length" class="case-file__row">
                    <dt class="case-file__label">Capabilities</dt>
                    <dd class="case-file__value">
                        <span v-for="feature in selected.features" :key="feature" class="block">{{ feature }}</span>
                    </dd>
                </div>
                <div v-if="linkedSkills.length" class="case-file__row">
                    <dt class="case-file__label">Skills</dt>
                    <dd class="case-file__value">
                        <Link v-for="(skill, i) in linkedSkills" :key="skill.slug" :href="skill.href" class="mr-1">{{ skill.title }}<template v-if="i < linkedSkills.length - 1">, </template></Link>
                    </dd>
                </div>
                <div v-if="statusLabel(selected.status)" class="case-file__row">
                    <dt class="case-file__label">Status</dt>
                    <dd class="case-file__value">{{ statusLabel(selected.status) }}</dd>
                </div>
            </dl>

            <div v-if="selected.demoUrl || selected.repositoryUrl" class="mt-5 flex flex-wrap items-center gap-4">
                <a v-if="selected.demoUrl" class="btn btn-primary" :href="selected.demoUrl" target="_blank" rel="noopener noreferrer">Live demo</a>
                <a v-if="selected.repositoryUrl" class="btn-quiet" :href="selected.repositoryUrl" target="_blank" rel="noopener noreferrer">Repository</a>
            </div>

            <div class="mt-6 flex items-center gap-3 border-t pt-4" style="border-color: var(--color-line)">
                <button type="button" class="btn btn-ghost" @click="step(-1)">Previous</button>
                <button type="button" class="btn btn-ghost" @click="step(1)">Next</button>
            </div>
        </div>
    </main>
</template>
