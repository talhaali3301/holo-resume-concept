<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, onUnmounted, ref, watch } from 'vue'
import HallFallback from '@/Components/projects/HallFallback.vue'
import ProjectDetail from '@/Components/projects/ProjectDetail.vue'
import ProjectGrid from '@/Components/projects/ProjectGrid.vue'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import { withView } from '@/lib/experience'
import SceneFrame from '@/scenes/SceneFrame.vue'
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

const trigger = ref<HTMLElement | null>(null)
const hovered = ref<string | null>(null)
const animating = ref(false)
let motionTimer = 0

const sceneBindings = computed(() => ({
    projects: props.projects,
    selectedSlug: props.selected?.slug ?? null,
    hoveredSlug: hovered.value,
}))

watch(
    () => props.selected?.slug,
    () => {
        animating.value = true
        window.clearTimeout(motionTimer)
        motionTimer = window.setTimeout(() => {
            animating.value = false
        }, 800)
    },
)

onUnmounted(() => window.clearTimeout(motionTimer))

function openProject(slug: string, event?: Event) {
    const project = props.projects.find((item) => item.slug === slug)

    if (!project) {
        return
    }

    trigger.value = event?.currentTarget instanceof HTMLElement ? event.currentTarget : null
    router.visit(withView(project.href, props.view), { preserveScroll: true })
}

function closeProject() {
    const slug = props.selected?.slug
    const focusTarget = trigger.value

    router.visit(withView(props.routes.index, props.view), {
        preserveScroll: true,
        onFinish: () => {
            const fallback = slug ? document.getElementById(`project-${slug}`) : null
            ;(focusTarget ?? fallback)?.focus()
        },
    })
}

function setView(next: ViewMode) {
    const href = props.selected?.href ?? props.routes.index
    router.visit(withView(href, next), { preserveScroll: true })
}

const selectedIndex = computed(() => props.projects.findIndex((project) => project.slug === props.selected?.slug))

function step(direction: number) {
    if (props.projects.length === 0) {
        return
    }

    const start = selectedIndex.value >= 0 ? selectedIndex.value : 0
    const next = (start + direction + props.projects.length) % props.projects.length
    const project = props.projects[next]

    if (project) {
        openProject(project.slug)
    }
}
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>
    <main id="content" :class="view === 'gallery' ? 'room-stage' : 'standard-page frame'">
        <template v-if="view === 'gallery'">
            <div :inert="selected ? true : undefined">
                <SceneFrame
                    class="is-room"
                    scene="hall"
                    :bindings="sceneBindings"
                    :animating="animating"
                    @select="openProject"
                    @hover="hovered = $event"
                >
                    <template #fallback>
                        <HallFallback />
                    </template>
                </SceneFrame>
                <div class="hud hud-index">
                    <p class="eyebrow">Hall</p>
                    <h1 class="hud-title text-[clamp(2rem,3vw,2.8rem)]">Projects</h1>
                    <p v-if="projects.length === 0" class="text-muted">No projects have been added yet. Add them in content/portfolio.php.</p>
                    <nav v-else aria-label="Projects" class="mt-2">
                        <button
                            v-for="(project, index) in projects"
                            :id="`project-${project.slug}`"
                            :key="project.slug"
                            type="button"
                            class="index-btn"
                            :class="{ 'is-current': project.slug === selected?.slug || project.slug === hovered }"
                            :aria-current="project.slug === selected?.slug ? 'true' : undefined"
                            @click="openProject(project.slug, $event)"
                            @mouseenter="hovered = project.slug"
                            @mouseleave="hovered = null"
                            @focus="hovered = project.slug"
                            @blur="hovered = null"
                        >
                            <span class="index-no">{{ String(index + 1).padStart(2, '0') }}</span>
                            <span>{{ project.title }}</span>
                        </button>
                    </nav>
                </div>
            </div>
        </template>
        <template v-else>
            <div :inert="selected ? true : undefined">
                <p class="eyebrow">Standard record</p>
                <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 class="page-title">Projects hall</h1>
                        <p class="lede">The same record, read as an exhibition of plates.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <Link :href="routes.lobby" class="btn btn-ghost">Lobby</Link>
                        <button type="button" class="btn btn-primary" @click="setView('gallery')">Show the hall</button>
                    </div>
                </div>
                <p v-if="projects.length === 0" class="mt-10 text-muted">No projects have been added yet. Add them in content/portfolio.php.</p>
                <ProjectGrid v-else class="mt-8" :projects="projects" :selected-slug="selected?.slug" @select="openProject" />
            </div>
        </template>
        <ProjectDetail v-if="selected" :project="selected" :skills="skills" @close="closeProject">
            <template #nav>
                <button type="button" class="btn btn-ghost" @click="step(-1)">Previous</button>
                <button type="button" class="btn btn-ghost" @click="step(1)">Next</button>
            </template>
        </ProjectDetail>
    </main>
</template>
