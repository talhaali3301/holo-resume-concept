<script setup lang="ts">
import { markRaw, onUnmounted, ref } from 'vue'
import { BufferGeometry, Float32BufferAttribute, Mesh, MeshBasicMaterial, Points, PointsMaterial, TorusGeometry, Vector3 } from 'three'
import { createFloor } from '@/scenes/floor'
import { useCameraRig } from '@/scenes/useCameraRig'
import type { Destination } from '@/types/portfolio'

const props = defineProps<{
    destinations: Destination[]
    motion: boolean
    parallaxX: number
    parallaxY: number
}>()

const emit = defineEmits<{
    select: [id: string]
    hover: [id: string | null]
}>()

const hovered = ref<string | null>(null)
const narrow = typeof window !== 'undefined' && window.matchMedia('(max-width: 800px)').matches
const floor = markRaw(createFloor(16, 14))
floor.mesh.position.set(0, 0, -0.4)

const ringMaterial = new MeshBasicMaterial({ color: '#8eecff', transparent: true, opacity: 0.75 })
const ring = markRaw(new Mesh(new TorusGeometry(0.46, 0.01, 12, 72), ringMaterial))
ring.rotation.x = Math.PI / 2.15
ring.position.set(1.45, 0.55, 3.15)
const ringB = markRaw(new Mesh(new TorusGeometry(0.28, 0.008, 10, 48), ringMaterial.clone()))
ringB.rotation.x = Math.PI / 3
ringB.position.set(1.45, 0.55, 3.15)

const dust = (() => {
    if (typeof window !== 'undefined' && window.matchMedia('(max-width: 800px)').matches) {
        return null
    }

    const positions = new Float32Array(24 * 3)

    for (let index = 0; index < 24; index += 1) {
        positions[index * 3] = (Math.random() - 0.5) * 8
        positions[index * 3 + 1] = 0.4 + Math.random() * 2.4
        positions[index * 3 + 2] = -3 + Math.random() * 5
    }

    const geometry = new BufferGeometry()
    geometry.setAttribute('position', new Float32BufferAttribute(positions, 3))
    const points = new Points(geometry, new PointsMaterial({ color: '#9ad8ee', size: 0.018, transparent: true, opacity: 0.45 }))

    return markRaw(points)
})()

const portals = [
    { id: 'projects', position: [-0.2, 0, 0.2] as [number, number, number], color: '#8eecff', width: 1.35 },
    { id: 'skills', position: [1.7, 0, -1.45] as [number, number, number], color: '#6aa6ff', width: 1.15 },
    { id: 'contact', position: [3.45, 0, 0.2] as [number, number, number], color: '#d7e4ff', width: 1.05 },
].filter((portal) => props.destinations.some((destination) => destination.id === portal.id))

const position = new Vector3()
const look = new Vector3()

useCameraRig(
    (target) => {
        const shiftX = props.motion ? props.parallaxX * 0.42 : 0
        const shiftY = props.motion ? props.parallaxY * -0.12 : 0

        if (narrow) {
            position.set(1.55, 2.05, 8.2)
            look.set(1.55, 1.15, -0.4)
        } else {
            position.set(shiftX + 0.2, 1.52 + shiftY, 5.1)
            look.set(1.55 + shiftX * 0.12, 1.18, -0.8)
        }
        target.position.copy(position)
        target.look.copy(look)
    },
    () => props.motion,
    (elapsed, moving) => {
        floor.material.uniforms.uTime.value = elapsed
        floor.material.uniforms.uMotion.value = moving ? 1 : 0

        if (moving) {
            ring.rotation.z = elapsed * 0.22
            ringB.rotation.y = elapsed * 0.18

            if (dust) {
                dust.rotation.y = elapsed * 0.02
            }
        }
    },
)

onUnmounted(() => {
    floor.material.dispose()
    floor.mesh.geometry.dispose()
    ring.geometry.dispose()
    ringMaterial.dispose()
    ringB.geometry.dispose()
    ;(ringB.material as MeshBasicMaterial).dispose()
    dust?.geometry.dispose()
    ;(dust?.material as PointsMaterial | undefined)?.dispose()
})

function intensity(id: string): number {
    return hovered.value === id ? 1.15 : 0.28
}

function setHover(id: string | null) {
    hovered.value = id
    emit('hover', id)
}
</script>

<template>
    <TresPerspectiveCamera :position="narrow ? [1.55, 2.05, 8.2] : [0.2, 1.52, 5.1]" :fov="narrow ? 52 : 40" :near="0.1" :far="40" />
    <TresAmbientLight :intensity="0.45" color="#c5d4ef" />
    <TresDirectionalLight :position="[2.4, 5.5, 4]" :intensity="1.6" color="#f2f6ff" />
    <TresPointLight :position="[0, 2.4, 1.2]" :intensity="12" color="#8eecff" :distance="18" :decay="2" />
    <primitive :object="floor.mesh" />
    <primitive :object="ring" />
    <primitive :object="ringB" />
    <primitive v-if="dust" :object="dust" />

    <TresMesh :position="[0, 1.75, -4.6]">
        <TresBoxGeometry :args="[14, 3.6, 0.16]" />
        <TresMeshStandardMaterial color="#10182a" :roughness="0.9" />
    </TresMesh>
    <TresMesh :position="[-6.6, 1.75, -0.6]">
        <TresBoxGeometry :args="[0.16, 3.6, 9]" />
        <TresMeshStandardMaterial color="#0d1424" :roughness="0.92" />
    </TresMesh>
    <TresMesh :position="[6.6, 1.75, -0.6]">
        <TresBoxGeometry :args="[0.16, 3.6, 9]" />
        <TresMeshStandardMaterial color="#0d1424" :roughness="0.92" />
    </TresMesh>
    <TresMesh :position="[0, 3.5, -0.6]">
        <TresBoxGeometry :args="[13.2, 0.08, 9]" />
        <TresMeshStandardMaterial color="#0c1220" />
    </TresMesh>
    <TresMesh :position="[0, 3.34, -1.4]">
        <TresBoxGeometry :args="[4.2, 0.02, 0.06]" />
        <TresMeshStandardMaterial color="#8eecff" emissive="#8eecff" :emissive-intensity="0.7" />
    </TresMesh>
    <TresMesh :position="[1.45, 0.012, 2.55]" :rotation-x="-Math.PI / 2">
        <TresRingGeometry :args="[0.42, 0.48, 40]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.7" />
    </TresMesh>
    <TresMesh :position="[1.45, 0.42, 2.35]">
        <TresBoxGeometry :args="[0.012, 0.7, 0.012]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.85" />
    </TresMesh>
    <TresMesh :position="[1.55, 0.02, -0.4]" :rotation-x="-Math.PI / 2">
        <TresPlaneGeometry :args="[0.04, 4.2]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.35" />
    </TresMesh>
    <TresMesh :position="[0, 0.04, -4.45]">
        <TresBoxGeometry :args="[12, 0.03, 0.04]" />
        <TresMeshBasicMaterial color="#8eecff" :transparent="true" :opacity="0.45" />
    </TresMesh>

    <TresGroup v-for="portal in portals" :key="portal.id" :position="portal.position">
        <TresMesh :position="[0, 2.55, -0.2]">
            <TresBoxGeometry :args="[0.08, 1.7, 0.08]" />
            <TresMeshBasicMaterial :color="portal.color" :transparent="true" :opacity="hovered === portal.id ? 0.28 : 0.08" />
        </TresMesh>
        <TresMesh :position="[-(portal.width / 2), 1.15, 0]">
            <TresBoxGeometry :args="[0.055, 2.3, 0.08]" />
            <TresMeshStandardMaterial :color="portal.color" :emissive="portal.color" :emissive-intensity="intensity(portal.id)" :roughness="0.3" :metalness="0.4" />
        </TresMesh>
        <TresMesh :position="[portal.width / 2, 1.15, 0]">
            <TresBoxGeometry :args="[0.055, 2.3, 0.08]" />
            <TresMeshStandardMaterial :color="portal.color" :emissive="portal.color" :emissive-intensity="intensity(portal.id)" :roughness="0.3" :metalness="0.4" />
        </TresMesh>
        <TresMesh :position="[0, 2.28, 0]">
            <TresBoxGeometry :args="[portal.width + 0.08, 0.055, 0.08]" />
            <TresMeshStandardMaterial :color="portal.color" :emissive="portal.color" :emissive-intensity="intensity(portal.id)" :roughness="0.3" :metalness="0.4" />
        </TresMesh>
        <TresMesh v-if="portal.id === 'skills'" :position="[0, 2.55, 0]" :rotation-x="Math.PI / 2">
            <TresTorusGeometry :args="[0.34, 0.012, 8, 32]" />
            <TresMeshBasicMaterial :color="portal.color" />
        </TresMesh>
        <TresMesh v-if="portal.id === 'contact'" :position="[0, 2.62, 0]" :rotation-z="Math.PI / 4">
            <TresBoxGeometry :args="[0.22, 0.22, 0.04]" />
            <TresMeshStandardMaterial :color="portal.color" emissive="#d7e4ff" :emissive-intensity="intensity(portal.id)" />
        </TresMesh>
        <TresMesh :position="[0, 1.15, -0.02]">
            <TresPlaneGeometry :args="[portal.width - 0.12, 2.1]" />
            <TresMeshStandardMaterial color="#0c121c" :emissive="portal.color" :emissive-intensity="hovered === portal.id ? 0.35 : 0.06" />
        </TresMesh>
        <TresMesh :position="[0, 0.015, 0.7]" :rotation-x="-Math.PI / 2">
            <TresPlaneGeometry :args="[portal.width * 0.72, 1.35]" />
            <TresMeshBasicMaterial :color="portal.color" :transparent="true" :opacity="hovered === portal.id ? 0.34 : 0.07" :depth-write="false" />
        </TresMesh>
        <TresMesh
            :position="[0, 1.15, 0.08]"
            @pointerenter="setHover(portal.id)"
            @pointerleave="setHover(null)"
            @click="emit('select', portal.id)"
        >
            <TresPlaneGeometry :args="[portal.width + 0.3, 2.4]" />
            <TresMeshBasicMaterial color="#000000" :transparent="true" :opacity="0" :depth-write="false" />
        </TresMesh>
    </TresGroup>
</template>
