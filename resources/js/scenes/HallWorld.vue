<script setup lang="ts">
import { computed, markRaw, onUnmounted, ref } from 'vue'
import { Mesh, PlaneGeometry, Vector3 } from 'three'
import { layoutFrames } from '@/lib/experience'
import { createFloor } from '@/scenes/floor'
import { createScreen, frameAccents } from '@/scenes/screen'
import { useCameraRig } from '@/scenes/useCameraRig'
import type { Project } from '@/types/portfolio'

const props = defineProps<{
    projects: Project[]
    selectedSlug?: string | null
    hoveredSlug?: string | null
    motion: boolean
    parallaxX: number
    parallaxY: number
}>()

const emit = defineEmits<{
    select: [slug: string]
    hover: [slug: string | null]
}>()

const hovered = ref<string | null>(null)
const frames = computed(() => layoutFrames(props.projects.length))
const depth = computed(() => 8 + Math.ceil(Math.max(props.projects.length, 1) / 2) * 2.4)
const floor = markRaw(createFloor(6.4, depth.value))
floor.mesh.position.set(0, 0, 0.4 - depth.value / 2)
const ceilingLights = [-2.1, -4.3, -6.5, -8.7]
const variety = [1, 0.92, 1.08, 0.96, 1.05, 0.9]
const screens = props.projects.map((_, index) => markRaw(createScreen(frameAccents[index % frameAccents.length] ?? '#8eecff')))
const screenMeshes = screens.map((material) => {
    const mesh = new Mesh(new PlaneGeometry(1.16, 0.7), material)
    mesh.position.z = 0.045

    return markRaw(mesh)
})
const selectedFrame = computed(() => {
    const index = props.projects.findIndex((project) => project.slug === props.selectedSlug)

    return index >= 0 ? frames.value[index] : null
})

const position = new Vector3()
const look = new Vector3()

useCameraRig(
    (target) => {
        const index = props.projects.findIndex((project) => project.slug === props.selectedSlug)
        const shiftX = props.motion ? props.parallaxX * 0.16 : 0
        const shiftY = props.motion ? props.parallaxY * -0.06 : 0
        const frame = index >= 0 ? frames.value[index] : null

        if (!frame) {
            position.set(shiftX, 1.58 + shiftY, 4.15)
            look.set(0, 1.42, -3.2)
        } else {
            position.set(frame.position[0] * 0.22 + shiftX, 1.5 + shiftY, frame.position[2] + 3.15)
            look.set(frame.position[0] * 0.72, 1.42, frame.position[2])
        }

        target.position.copy(position)
        target.look.copy(look)
    },
    () => props.motion,
    (elapsed, moving) => {
        floor.material.uniforms.uTime.value = elapsed
        floor.material.uniforms.uMotion.value = moving ? 1 : 0

        screens.forEach((material, index) => {
            material.uniforms.uTime.value = elapsed
            const slug = props.projects[index]?.slug
            material.uniforms.uLive.value = slug && (slug === props.selectedSlug || slug === hovered.value || slug === props.hoveredSlug) ? 1 : 0
        })
    },
)

onUnmounted(() => {
    floor.material.dispose()
    floor.mesh.geometry.dispose()
    screenMeshes.forEach((mesh) => {
        mesh.geometry.dispose()
        ;(mesh.material as { dispose: () => void }).dispose()
    })
})

function frameScale(slug: string, index: number): number {
    const base = variety[index % variety.length] ?? 1

    if (props.selectedSlug === slug) {
        return base * 1.08
    }

    if (hovered.value === slug || props.hoveredSlug === slug) {
        return base * 1.04
    }

    return base
}

function glow(slug: string): number {
    if (props.selectedSlug === slug) {
        return 1.5
    }

    if (hovered.value === slug || props.hoveredSlug === slug) {
        return 1.05
    }

    return 0.12
}
</script>

<template>
    <TresPerspectiveCamera :position="[0, 1.58, 4.15]" :fov="38" :near="0.1" :far="48" />
    <TresAmbientLight :intensity="0.18" color="#9eb0d4" />
    <TresDirectionalLight :position="[1.5, 5.5, 4]" :intensity="0.72" color="#e4ecff" />
    <TresPointLight :position="[0, 2.7, -1.2]" :intensity="3.2" color="#8eecff" :distance="16" :decay="2" />
    <primitive :object="floor.mesh" />

    <TresMesh :position="[-3.2, 1.7, 0.4 - depth / 2]">
        <TresBoxGeometry :args="[0.14, 3.4, depth]" />
        <TresMeshStandardMaterial color="#10182a" :roughness="0.92" />
    </TresMesh>
    <TresMesh :position="[3.2, 1.7, 0.4 - depth / 2]">
        <TresBoxGeometry :args="[0.14, 3.4, depth]" />
        <TresMeshStandardMaterial color="#10182a" :roughness="0.92" />
    </TresMesh>
    <TresMesh :position="[0, 3.38, 0.4 - depth / 2]">
        <TresBoxGeometry :args="[6.4, 0.08, depth]" />
        <TresMeshStandardMaterial color="#0c1220" />
    </TresMesh>
    <TresMesh :position="[0, 1.7, 0.4 - depth]">
        <TresBoxGeometry :args="[6.4, 3.4, 0.12]" />
        <TresMeshStandardMaterial color="#0d1526" :roughness="0.9" />
    </TresMesh>
    <TresMesh v-for="z in ceilingLights" :key="z" :position="[0, 3.24, z]">
        <TresBoxGeometry :args="[0.85, 0.016, 0.045]" />
        <TresMeshStandardMaterial color="#b7f4ff" emissive="#8eecff" :emissive-intensity="0.35" />
    </TresMesh>

    <TresMesh v-if="selectedFrame" :position="[selectedFrame.position[0], 2.35, selectedFrame.position[2]]">
        <TresBoxGeometry :args="[0.015, 1.5, 0.015]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.45" />
    </TresMesh>

    <TresGroup
        v-for="(project, index) in projects"
        :key="project.slug"
        :position="frames[index]?.position"
        :rotation-y="frames[index]?.rotationY"
        :scale="frameScale(project.slug, index)"
    >
        <TresMesh>
            <TresBoxGeometry :args="[1.42, 0.96, 0.05]" />
            <TresMeshStandardMaterial color="#1a2233" :emissive="frameAccents[index % frameAccents.length]" :emissive-intensity="glow(project.slug) * 0.45" :metalness="0.45" :roughness="0.42" />
        </TresMesh>
        <primitive :object="screenMeshes[index]" />
        <TresMesh
            :position="[0, 0, 0.07]"
            @pointerenter="hovered = project.slug; emit('hover', project.slug)"
            @pointerleave="hovered = null; emit('hover', null)"
            @click="emit('select', project.slug)"
        >
            <TresPlaneGeometry :args="[1.5, 1.05]" />
            <TresMeshBasicMaterial color="#000000" :transparent="true" :opacity="0" :depth-write="false" />
        </TresMesh>
    </TresGroup>
</template>
