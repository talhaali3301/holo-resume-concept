<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import LobbyFallback from '@/Components/lobby/LobbyFallback.vue'
import { useOpenContact } from '@/composables/useContact'
import PortfolioLayout from '@/Layouts/PortfolioLayout.vue'
import SceneFrame from '@/scenes/SceneFrame.vue'
import type { Destination, Identity, PageMeta } from '@/types/portfolio'

defineOptions({ layout: PortfolioLayout, inheritAttrs: false })

const props = defineProps<{
    identity: Identity
    destinations: Destination[]
    entry: { hall: string; standard: string; skills: string }
    meta: PageMeta
}>()

const openContact = useOpenContact()
const hot = ref<string | null>(null)
const sceneBindings = computed(() => ({ destinations: props.destinations }))
const headlineLines = computed(() => {
    const headline = props.identity.headline
    const comma = headline.indexOf(',')

    if (comma > 0 && comma < headline.length - 1) {
        return [headline.slice(0, comma + 1), headline.slice(comma + 1).trim()]
    }

    return [headline]
})

function onSelect(id: string) {
    if (id === 'projects') {
        router.visit(props.entry.hall)

        return
    }

    if (id === 'skills') {
        router.visit(props.entry.skills)

        return
    }

    openContact()
}

function activate(destination: Destination) {
    if (destination.id === 'contact' || destination.href === null) {
        openContact()

        return
    }

    router.visit(destination.href)
}
</script>

<template>
    <Head :title="meta.title">
        <meta head-key="description" name="description" :content="meta.description" />
    </Head>
    <main id="content" class="room-stage">
        <SceneFrame class="is-room" scene="lobby" :bindings="sceneBindings" @select="onSelect" @hover="hot = $event">
            <template #fallback>
                <LobbyFallback />
            </template>
        </SceneFrame>

        <div class="hud hud-copy">
            <div class="identity-card panel">
                <p class="eyebrow">{{ identity.name ?? identity.role }}</p>
                <h1 class="hud-title">
                    <template v-for="(line, index) in headlineLines" :key="line">
                        {{ line }}<br v-if="index < headlineLines.length - 1" />
                    </template>
                </h1>
                <div class="mt-4 flex flex-col items-start gap-3">
                    <Link :href="entry.hall" class="btn btn-primary" prefetch>Explore the portfolio</Link>
                    <Link :href="entry.standard" class="btn btn-quiet" prefetch>Read the standard portfolio</Link>
                </div>
            </div>
        </div>

        <nav class="hud hud-portals" aria-label="Rooms">
            <component
                :is="destination.href ? Link : 'button'"
                v-for="(destination, index) in destinations"
                :key="destination.id"
                class="portal-chip"
                :class="{ 'is-hot': hot === destination.id }"
                :href="destination.href ?? undefined"
                :type="destination.href ? undefined : 'button'"
                @mouseenter="hot = destination.id"
                @mouseleave="hot = null"
                @focus="hot = destination.id"
                @blur="hot = null"
                @click="destination.href ? undefined : activate(destination)"
            >
                <span class="index-no">{{ String(index + 1).padStart(2, '0') }}</span>
                <span>
                    <span class="portal-chip__label block font-medium">{{ destination.label }}</span>
                    <span class="portal-chip__note">{{ destination.summary }}</span>
                </span>
            </component>
        </nav>
    </main>
</template>
