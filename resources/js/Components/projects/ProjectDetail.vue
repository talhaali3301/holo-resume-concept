<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { nextTrapIndex, relatedSkills, statusLabel } from '@/lib/experience'
import { lockScroll, unlockScroll } from '@/lib/scrollLock'
import type { Project, SkillRef } from '@/types/portfolio'

const props = defineProps<{
    project: Project
    skills: SkillRef[]
}>()

const emit = defineEmits<{
    close: []
}>()

const root = ref<HTMLElement | null>(null)
const closeButton = ref<HTMLButtonElement | null>(null)
const imageBroken = ref(false)
const linkedSkills = computed(() => relatedSkills(props.project.skills, props.skills))

function nodes(): HTMLElement[] {
    if (!root.value) {
        return []
    }

    return [...root.value.querySelectorAll<HTMLElement>('a[href], button:not([disabled])')]
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        event.preventDefault()
        emit('close')

        return
    }

    if (event.key !== 'Tab') {
        return
    }

    const items = nodes()
    const index = items.indexOf(document.activeElement as HTMLElement)
    event.preventDefault()
    items[nextTrapIndex(index, items.length, event.shiftKey)]?.focus()
}

watch(
    () => props.project.slug,
    async () => {
        imageBroken.value = false
        await nextTick()
        closeButton.value?.focus()
    },
)

onMounted(async () => {
    lockScroll()
    window.addEventListener('keydown', onKeydown)
    await nextTick()
    closeButton.value?.focus()
})

onUnmounted(() => {
    unlockScroll()
    window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <div class="sheet panel" role="dialog" aria-modal="true" aria-labelledby="project-detail-title" ref="root">
        <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-white/10 bg-panel/95 px-5 py-4">
            <p class="meta">Project</p>
            <div class="flex flex-wrap gap-2">
                <slot name="nav" />
                <button ref="closeButton" type="button" class="btn btn-ghost" @click="emit('close')">Close</button>
            </div>
        </div>
        <div class="px-5 py-6 sm:px-6">
            <p class="meta">
                <template v-if="project.sample">Sample record</template>
                <template v-if="project.sample && statusLabel(project.status)"> · </template>
                <template v-if="statusLabel(project.status)">{{ statusLabel(project.status) }}</template>
            </p>
            <h2 id="project-detail-title" class="mt-4 mb-3 font-heading text-4xl font-normal tracking-tight">{{ project.title }}</h2>
            <p v-if="project.summary" class="text-lg text-muted leading-relaxed">{{ project.summary }}</p>

            <img
                v-if="project.image && !imageBroken"
                :src="project.image"
                :alt="project.imageAlt"
                width="800"
                height="500"
                class="mt-6 w-full rounded-2xl border border-white/10"
                @error="imageBroken = true"
            />
            <div v-else class="mt-6 grid h-40 place-items-center rounded-2xl border border-dashed border-white/15 text-sm text-muted">
                Visual unavailable
            </div>
            <p v-if="project.imageNote" class="mt-2 text-sm text-muted">{{ project.imageNote }}</p>

            <section v-if="project.purpose" class="mt-6">
                <h3 class="meta">Purpose</h3>
                <p class="leading-relaxed">{{ project.purpose }}</p>
            </section>
            <section v-if="project.role" class="mt-5">
                <h3 class="meta">Role</h3>
                <p class="leading-relaxed">{{ project.role }}</p>
            </section>
            <section v-if="project.technologies.length" class="mt-5">
                <h3 class="meta">Technologies</h3>
                <ul class="m-0 flex list-none flex-wrap gap-2 p-0">
                    <li v-for="tech in project.technologies" :key="tech" class="badge">{{ tech }}</li>
                </ul>
            </section>
            <section v-if="project.features.length" class="mt-5">
                <h3 class="meta">Features</h3>
                <ul class="m-0 grid gap-2 pl-5">
                    <li v-for="feature in project.features" :key="feature">{{ feature }}</li>
                </ul>
            </section>
            <section v-if="project.caseStudy" class="mt-5">
                <h3 class="meta">Case study</h3>
                <p class="leading-relaxed whitespace-pre-wrap">{{ project.caseStudy }}</p>
            </section>
            <section v-if="linkedSkills.length" class="mt-5">
                <h3 class="meta">Related skills</h3>
                <ul class="m-0 flex list-none flex-wrap gap-2 p-0">
                    <li v-for="skill in linkedSkills" :key="skill.slug">
                        <Link :href="skill.href" class="btn btn-ghost">{{ skill.title }}</Link>
                    </li>
                </ul>
            </section>
            <div v-if="project.demoUrl || project.repositoryUrl" class="mt-6 flex flex-wrap gap-3">
                <a v-if="project.demoUrl" class="btn btn-primary" :href="project.demoUrl" target="_blank" rel="noopener noreferrer">Live demo</a>
                <a v-if="project.repositoryUrl" class="btn btn-ghost" :href="project.repositoryUrl" target="_blank" rel="noopener noreferrer">Repository</a>
            </div>
        </div>
    </div>
</template>
