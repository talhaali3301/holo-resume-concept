<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { computed, nextTick, onMounted, onUnmounted, ref, watch, type ComponentPublicInstance } from 'vue'
import DepthChrome, { type ScreenId } from '@/Components/shell/DepthChrome.vue'
import { useOpenContact } from '@/composables/useContact'
import { useMediaQuery } from '@/composables/useMediaQuery'
import { useMotionToggle } from '@/composables/useMotionToggle'
import { useStageScale } from '@/composables/useStageScale'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import type { Destination, Identity, PageMeta } from '@/types/portfolio'

defineOptions({ layout: PortfolioLayout, inheritAttrs: false })

const props = defineProps<{
    identity: Identity
    destinations: Destination[]
    entry: { hall: string; standard: string; skills: string }
    meta: PageMeta
}>()

const openContact = useOpenContact()
const isMobile = useMediaQuery('(max-width: 899px)')
const { root: stageRoot, scale } = useStageScale(1440, 900)
const { reduced, toggle: toggleMotion } = useMotionToggle()

const railRoutes: Record<ScreenId, string> = { lobby: '/', projects: props.entry.hall, skills: props.entry.skills, contact: '#' }

const order = ['lobby', 'projects', 'skills', 'contact'] as const
type SheetId = (typeof order)[number]

const glow: Record<SheetId, string> = {
    lobby: 'rgba(255, 126, 66, 0.30)',
    projects: 'rgba(255, 184, 92, 0.30)',
    skills: 'rgba(255, 110, 60, 0.34)',
    contact: 'rgba(214, 62, 94, 0.36)',
}

const destinationById = computed(() => {
    const map: Partial<Record<SheetId, Destination>> = {}

    for (const d of props.destinations) {
        if (d.id === 'projects' || d.id === 'skills' || d.id === 'contact') {
            map[d.id] = d
        }
    }

    return map
})

interface SheetContent {
    id: SheetId
    index: string
    title: string
    description: string
}

const sheets = computed<SheetContent[]>(() => [
    { id: 'lobby', index: '00', title: 'Lobby', description: 'Identity and starting point' },
    { id: 'projects', index: '01', title: 'Projects', description: destinationById.value.projects?.summary ?? '' },
    { id: 'skills', index: '02', title: 'Skills', description: destinationById.value.skills?.summary ?? '' },
    { id: 'contact', index: '03', title: 'Contact', description: destinationById.value.contact?.summary ?? '' },
])

const active = ref<SheetId>('lobby')
const sheenId = ref<SheetId | null>(null)
const bodyRefs: Partial<Record<SheetId, HTMLElement>> = {}

function setBodyRef(id: SheetId, el: Element | ComponentPublicInstance | null) {
    if (el instanceof HTMLElement) {
        bodyRefs[id] = el
    }
}

function slotFor(id: SheetId): number {
    const activeIndex = order.indexOf(active.value)
    const i = order.indexOf(id)

    return (i - activeIndex + order.length) % order.length
}

function go(id: SheetId) {
    if (id === active.value) {
        return
    }

    active.value = id
    sheenId.value = null
    nextTick(() => {
        sheenId.value = id
    })

    window.setTimeout(() => bodyRefs[id]?.focus(), reduced.value ? 50 : 500)
}

function enterHall() {
    if (reduced.value) {
        go('projects')
        router.visit(props.entry.hall)

        return
    }

    go('projects')
    window.setTimeout(() => router.visit(props.entry.hall), 950)
}

function enterSkills() {
    router.visit(props.entry.skills)
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        go('lobby')
    }
}

const stackRef = ref<HTMLElement | null>(null)
const introActive = ref(true)
const settlingActive = ref(false)
let rafId = 0
let tx = 0
let ty = 0
let cx = 0
let cy = 0
let openTimer = 0
let settleTimer = 0

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
    stackRef.value?.style.setProperty('--ry', '-21deg')
    stackRef.value?.style.setProperty('--float', '0px')
}

function frame(now: number) {
    if (!reduced.value && stackRef.value) {
        const t = now / 1000
        cx += (tx - cx) * 0.06
        cy += (ty - cy) * 0.06
        const rx = -7 - cy * 3.5 + Math.sin(t * 0.5) * 0.6
        const ry = -21 + cx * 5 + Math.sin(t * 0.37) * 1.0
        stackRef.value.style.setProperty('--rx', `${rx.toFixed(3)}deg`)
        stackRef.value.style.setProperty('--ry', `${ry.toFixed(3)}deg`)
        stackRef.value.style.setProperty('--float', `${(Math.sin(t * 0.6) * 5).toFixed(2)}px`)
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

    openTimer = window.setTimeout(
        () => {
            settlingActive.value = true
            introActive.value = false
            sheenId.value = 'lobby'
            settleTimer = window.setTimeout(() => {
                settlingActive.value = false
            }, 1800)
        },
        reduced.value ? 0 : 250,
    )
})

onUnmounted(() => {
    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerleave', onPointerLeave)
    window.removeEventListener('keydown', onKeydown)
    cancelAnimationFrame(rafId)
    window.clearTimeout(openTimer)
    window.clearTimeout(settleTimer)
})
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>

    <main id="content" class="lobby-viewport">
        <div v-if="!isMobile" class="lobby-stage-wrap">
            <div
                ref="stageRoot"
                class="stage-1440 lobby-stage"
                :class="{ intro: introActive, settling: settlingActive, calm: reduced }"
                :style="{ transform: `translate(-50%, -50%) scale(${scale})`, '--lobby-glow': glow[active] }"
            >
                <div class="lobby-bloom" aria-hidden="true"></div>

                <div class="lobby-scene">
                    <div ref="stackRef" class="lobby-stack">
                        <article
                            v-for="sheet in sheets"
                            :key="sheet.id"
                            class="lobby-sheet"
                            :class="[`s-${sheet.id}`, { sheen: sheenId === sheet.id }]"
                            :data-slot="slotFor(sheet.id)"
                        >
                            <button class="lobby-strip" type="button" :aria-label="`Bring ${sheet.title} forward`" @click="go(sheet.id)">
                                <span class="lobby-strip__title">
                                    <span class="lobby-strip__idx">{{ sheet.index }}</span>
                                    <span class="lobby-strip__name">{{ sheet.title }}</span>
                                </span>
                                <span class="lobby-strip__side">
                                    <span class="lobby-strip__desc">{{ sheet.description }}</span>
                                    <span class="lobby-strip__chip">
                                        Bring forward
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 8h11M8 3l5 5-5 5" /></svg>
                                    </span>
                                </span>
                            </button>

                            <div
                                v-if="sheet.id === 'lobby'"
                                :ref="(el) => setBodyRef('lobby', el)"
                                class="lobby-body lobby-rise"
                                tabindex="-1"
                            >
                                <div class="lobby-meta">
                                    <span class="lobby-meta__where">00 &nbsp;LOBBY</span>
                                    <span class="lobby-meta__here"><i></i>You are here</span>
                                </div>
                                <div class="lobby-identity">
                                    <h1>{{ identity.name ?? 'Your Name' }}</h1>
                                    <div class="lobby-role-row">
                                        <span class="lobby-role">{{ identity.role }}</span>
                                        <span class="lobby-stackline">{{ identity.stack }}</span>
                                    </div>
                                    <div class="lobby-pitch">{{ identity.lede }}</div>
                                </div>
                                <div class="lobby-actions">
                                    <button class="lobby-cta" type="button" @click="enterHall">
                                        <span class="lobby-cta__label">Enter the experience</span>
                                        <span class="lobby-cta__tile">
                                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                                        </span>
                                    </button>
                                    <a class="lobby-quiet" :href="entry.standard">Read standard profile</a>
                                </div>
                            </div>

                            <div
                                v-else
                                :ref="(el) => setBodyRef(sheet.id, el)"
                                class="lobby-body"
                                tabindex="-1"
                            >
                                <div class="lobby-meta">
                                    <span>{{ sheet.index }} &nbsp;{{ sheet.title.toUpperCase() }}</span>
                                    <span v-if="identity.sample">SAMPLE CONTENT</span>
                                </div>
                                <div class="lobby-identity">
                                    <h2>{{ sheet.title }}</h2>
                                    <div class="lobby-space-copy">{{ sheet.description }}</div>
                                </div>
                                <div class="lobby-actions">
                                    <button
                                        v-if="sheet.id === 'projects'"
                                        class="lobby-cta"
                                        type="button"
                                        @click="router.visit(entry.hall)"
                                    >
                                        <span class="lobby-cta__label">Enter the experience</span>
                                        <span class="lobby-cta__tile">
                                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                                        </span>
                                    </button>
                                    <button
                                        v-else-if="sheet.id === 'skills'"
                                        class="lobby-cta"
                                        type="button"
                                        @click="enterSkills"
                                    >
                                        <span class="lobby-cta__label">Enter the experience</span>
                                        <span class="lobby-cta__tile">
                                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                                        </span>
                                    </button>
                                    <button v-else class="lobby-cta" type="button" @click="openContact">
                                        <span class="lobby-cta__label">Start a conversation</span>
                                        <span class="lobby-cta__tile">
                                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                                        </span>
                                    </button>
                                    <button class="lobby-back" type="button" @click="go('lobby')">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 9H3M8 4L3 9l5 5" /></svg>
                                        Back to Lobby
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <DepthChrome
                    :current="active"
                    :reduced="reduced"
                    :routes="railRoutes"
                    :standard-href="entry.standard"
                    :brand="identity.product"
                    @toggle-motion="toggleMotion"
                    @navigate="go"
                />

                <div class="lobby-voice">
                    <span class="lobby-voice__line">A portfolio built to be explored.</span>
                    <span class="lobby-voice__tag">Concept &middot; Sample projects &middot; Designed &amp; built by Talha Ali, <a class="credit-link" href="https://robocoders.dev/" target="_blank" rel="noopener noreferrer">Robo Coders</a></span>
                </div>
                <div class="lobby-hint">Click any layer to bring it forward</div>
            </div>
        </div>

        <div v-else class="lobby-mobile">
            <div class="lobby-mobile__tabs" role="tablist" aria-label="Rooms">
                <button
                    v-for="sheet in sheets.slice(1)"
                    :key="sheet.id"
                    type="button"
                    class="lobby-mobile__tab"
                    :class="`s-${sheet.id}`"
                    role="tab"
                    @click="sheet.id === 'contact' ? openContact() : router.visit(sheet.id === 'projects' ? entry.hall : entry.skills)"
                >
                    {{ sheet.title }}
                </button>
            </div>

            <div class="lobby-mobile__card">
                <div class="lobby-meta">
                    <span class="lobby-meta__where">00 &nbsp;LOBBY</span>
                    <span class="lobby-meta__here"><i></i>You are here</span>
                </div>
                <div class="lobby-identity">
                    <h1>{{ identity.name ?? 'Your Name' }}</h1>
                    <div class="lobby-role-row">
                        <span class="lobby-role">{{ identity.role }}</span>
                        <span class="lobby-stackline">{{ identity.stack }}</span>
                    </div>
                    <div class="lobby-pitch">{{ identity.lede }}</div>
                </div>
                <div class="lobby-voice lobby-voice--mobile">
                    <span class="lobby-voice__line">A portfolio built to be explored.</span>
                    <span class="lobby-voice__tag">Concept &middot; Sample projects &middot; Designed &amp; built by Talha Ali, <a class="credit-link" href="https://robocoders.dev/" target="_blank" rel="noopener noreferrer">Robo Coders</a></span>
                </div>
            </div>

            <div class="lobby-mobile__actions">
                <a class="lobby-quiet" :href="entry.standard">Read standard profile</a>
                <button class="lobby-cta lobby-cta--full" type="button" @click="router.visit(entry.hall)">
                    <span class="lobby-cta__label">Enter the experience</span>
                    <span class="lobby-cta__tile">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="#0A0C10" stroke-width="2.2" aria-hidden="true"><path d="M3 11h15M12 5l6 6-6 6" /></svg>
                    </span>
                </button>
            </div>
        </div>
    </main>
</template>
