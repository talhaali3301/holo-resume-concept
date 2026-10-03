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

        <div class="hud lobby-intro">
            <p class="lobby-kicker">{{ identity.product }}</p>
            <h1 class="lobby-headline">{{ identity.headline }}</h1>
            <p class="lobby-sub">{{ identity.lede }}</p>
            <div class="flex flex-wrap items-center gap-3">
                <Link :href="entry.hall" class="btn btn-primary" prefetch>Enter</Link>
                <Link :href="entry.standard" class="btn btn-ghost" prefetch>Standard view</Link>
            </div>
        </div>

        <nav class="hud gateways" aria-label="Rooms">
            <component
                :is="destination.href ? Link : 'button'"
                v-for="(destination, index) in destinations"
                :key="destination.id"
                class="gateway-label"
                :class="{ 'is-hot': hot === destination.id }"
                :href="destination.href ?? undefined"
                :type="destination.href ? undefined : 'button'"
                @mouseenter="hot = destination.id"
                @mouseleave="hot = null"
                @focus="hot = destination.id"
                @blur="hot = null"
                @click="destination.href ? undefined : activate(destination)"
            >
                <span class="gateway-label__index">{{ String(index + 1).padStart(2, '0') }}</span>
                <span class="gateway-label__name">{{ destination.label }}</span>
                <span class="gateway-label__note">{{ destination.summary }}</span>
            </component>
        </nav>
    </main>
</template>
