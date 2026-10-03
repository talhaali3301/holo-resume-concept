<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, nextTick, onUnmounted, ref, watch } from 'vue'
import CareerTimeline from '@/Components/skills/CareerTimeline.vue'
import ObservatoryFallback from '@/Components/skills/ObservatoryFallback.vue'
import SkillBrowser from '@/Components/skills/SkillBrowser.vue'
import SkillDetail from '@/Components/skills/SkillDetail.vue'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import { withView } from '@/lib/experience'
import SceneFrame from '@/scenes/SceneFrame.vue'
import type { Milestone, PageMeta, Skill, SkillLink, ViewMode } from '@/types/portfolio'

defineOptions({ layout: PortfolioLayout, inheritAttrs: false })

const props = defineProps<{
    skills: Skill[]
    links: SkillLink[]
    milestones: Milestone[]
    selected: Skill | null
    view: ViewMode
    routes: { index: string; lobby: string; projects: string }
    meta: PageMeta
}>()

const trigger = ref<HTMLElement | null>(null)
const hovered = ref<string | null>(null)
const marker = ref<string | null>(props.milestones[0]?.slug ?? null)
const animating = ref(false)
let motionTimer = 0

const sceneBindings = computed(() => ({
    skills: props.skills,
    links: props.links,
    milestones: props.milestones,
    selectedSlug: props.selected?.slug ?? null,
    hoveredSlug: hovered.value,
}))

watch(
    () => props.selected?.slug,
    async (slug) => {
        animating.value = true
        window.clearTimeout(motionTimer)
        motionTimer = window.setTimeout(() => {
            animating.value = false
        }, 700)

        if (!slug) {
            return
        }

        await nextTick()
        const title = document.getElementById('skill-detail-title')
        title?.focus()
        title?.scrollIntoView({ block: 'nearest' })
    },
    { immediate: true },
)

onUnmounted(() => window.clearTimeout(motionTimer))

function openSkill(slug: string, event?: Event) {
    const skill = props.skills.find((item) => item.slug === slug)

    if (!skill) {
        return
    }

    trigger.value = event?.currentTarget instanceof HTMLElement ? event.currentTarget : null
    router.visit(withView(skill.href, props.view), { preserveScroll: true })
}

function closeSkill() {
    const slug = props.selected?.slug
    const focusTarget = trigger.value

    router.visit(withView(props.routes.index, props.view), {
        preserveScroll: true,
        onFinish: () => {
            const fallback = slug ? document.getElementById(`skill-${slug}`) : null
            ;(focusTarget ?? fallback)?.focus()
        },
    })
}

function setView(next: ViewMode) {
    const href = props.selected?.href ?? props.routes.index
    router.visit(withView(href, next), { preserveScroll: true })
}
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>
    <main id="content" :class="view === 'gallery' ? 'room-stage' : 'standard-page frame'">
        <template v-if="view === 'gallery'">
            <SceneFrame
                class="is-room"
                scene="observatory"
                :bindings="sceneBindings"
                :animating="animating"
                @select="openSkill"
                @hover="hovered = $event"
                @marker="marker = $event"
            >
                <template #fallback>
                    <ObservatoryFallback :skills="skills" :links="links" />
                </template>
            </SceneFrame>
            <div class="hud hud-index">
                <p class="eyebrow">Observatory</p>
                <h1 class="hud-title text-[clamp(2rem,3vw,2.8rem)]">Skills</h1>
                <p v-if="skills.length === 0" class="text-muted">No skills have been added yet. Add them in content/portfolio.php.</p>
                <SkillBrowser v-else :skills="skills" :selected-slug="selected?.slug ?? null" @select="openSkill" />
                <CareerTimeline class="mt-6" :milestones="milestones" :active="marker" @select="marker = $event" />
            </div>
            <SkillDetail v-if="selected" class="sheet" :skill="selected" @close="closeSkill" />
        </template>
        <template v-else>
            <p class="eyebrow">Standard record</p>
            <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="page-title">Skills observatory</h1>
                    <p class="lede">Technologies, practices, and sample milestones. Dates stay unverified until you replace them.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link :href="routes.lobby" class="btn btn-ghost">Lobby</Link>
                    <button type="button" class="btn btn-primary" @click="setView('gallery')">Show the observatory</button>
                </div>
            </div>
            <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div>
                    <p v-if="skills.length === 0" class="text-muted">No skills have been added yet. Add them in content/portfolio.php.</p>
                    <SkillBrowser v-else :skills="skills" :selected-slug="selected?.slug ?? null" @select="openSkill" />
                    <SkillDetail v-if="selected" :skill="selected" @close="closeSkill" />
                </div>
                <CareerTimeline :milestones="milestones" :active="marker" @select="marker = $event" />
            </div>
        </template>
    </main>
</template>
