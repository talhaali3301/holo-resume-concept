<script setup lang="ts">
import { computed, markRaw, onUnmounted, ref } from 'vue'
import {
    AdditiveBlending,
    BoxGeometry,
    BufferGeometry,
    EdgesGeometry,
    Float32BufferAttribute,
    Group,
    IcosahedronGeometry,
    LineBasicMaterial,
    LineSegments,
    Mesh,
    MeshBasicMaterial,
    MeshStandardMaterial,
    PlaneGeometry,
    Points,
    PointsMaterial,
    PointLight,
    SphereGeometry,
    Vector3,
} from 'three'
import { createFloor } from '@/scenes/floor'
import { createGatewayGlow } from '@/scenes/gateway'
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

function setHover(id: string | null) {
    hovered.value = id
    emit('hover', id)
}

// Narrow viewports get genuinely smaller geometry (not just a pulled-back
// camera): that keeps the gateways contained in a middle band, clear of the
// fixed intro/destination text zones above and below.
const scale = narrow ? 0.6 : 1
const GATEWAY_WIDTH = 1.55 * scale
const GATEWAY_HEIGHT = 5.1 * scale

interface GatewayConfig {
    id: string
    position: [number, number]
    width: number
    height: number
    color: string
}

const spread = narrow ? 2.1 : 3.75
const depth = narrow ? -3.2 : -4.3
const depthCenter = narrow ? -3.6 : -4.85

const layout: GatewayConfig[] = [
    { id: 'projects', position: [-spread, depth], width: GATEWAY_WIDTH, height: GATEWAY_HEIGHT, color: '#5ee6ff' },
    { id: 'skills', position: [0, depthCenter], width: GATEWAY_WIDTH, height: GATEWAY_HEIGHT, color: '#5ee6ff' },
    { id: 'contact', position: [spread, depth], width: GATEWAY_WIDTH * 0.86, height: GATEWAY_HEIGHT * 0.88, color: '#8d7bff' },
]

const gateways = computed(() => layout.filter((gateway) => props.destinations.some((d) => d.id === gateway.id)))

const floor = markRaw(createFloor(40, 50))
floor.group.position.set(0, 0, -5)

function buildGateway(config: GatewayConfig): Group {
    const group = new Group()
    group.position.set(config.position[0], 0, config.position[1])

    const thickness = config.width * 0.028
    const frameMaterial = new MeshStandardMaterial({
        color: '#0a0e18',
        emissive: config.color,
        emissiveIntensity: 1.4,
        roughness: 0.35,
        metalness: 0.55,
    })

    const left = new Mesh(new BoxGeometry(thickness, config.height, thickness), frameMaterial)
    left.position.set(-config.width / 2, config.height / 2, 0)
    const right = new Mesh(new BoxGeometry(thickness, config.height, thickness), frameMaterial)
    right.position.set(config.width / 2, config.height / 2, 0)
    const top = new Mesh(new BoxGeometry(config.width + thickness, thickness, thickness), frameMaterial)
    top.position.set(0, config.height, 0)
    group.add(left, right, top)

    const glow = createGatewayGlow(config.width * 0.92, config.height * 0.96, config.color, 0.16)
    glow.position.set(0, 0, -0.04)
    group.add(glow)

    group.add(buildGlimpse(config))

    const hit = new Mesh(
        new PlaneGeometry(config.width + 0.4, config.height + 0.3),
        new MeshBasicMaterial({ color: '#000000', transparent: true, opacity: 0 }),
    )
    hit.position.set(0, config.height / 2, 0.1)
    hit.userData.gatewayId = config.id
    group.add(hit)

    return group
}

function buildGlimpse(config: GatewayConfig): Group {
    const group = new Group()
    const depth = -0.9

    if (config.id === 'projects') {
        const positions: Array<[number, number, number, number, number]> = [
            [-0.32, config.height * 0.62, depth, 0.3, 0.19],
            [0.26, config.height * 0.48, depth - 0.3, 0.26, 0.17],
            [-0.1, config.height * 0.78, depth - 0.55, 0.22, 0.14],
        ]

        for (const [x, y, z, w, h] of positions) {
            const material = new MeshBasicMaterial({ color: config.color, transparent: true, opacity: 0.85 })
            const screen = new Mesh(new PlaneGeometry(w, h), material)
            screen.position.set(x, y, z)
            group.add(screen)
        }
    }

    if (config.id === 'skills') {
        const count = 14
        const positions = new Float32Array(count * 3)

        for (let i = 0; i < count; i += 1) {
            positions[i * 3] = (Math.random() - 0.5) * 0.9
            positions[i * 3 + 1] = config.height * 0.35 + Math.random() * config.height * 0.5
            positions[i * 3 + 2] = depth - Math.random() * 0.8
        }

        const geometry = new BufferGeometry()
        geometry.setAttribute('position', new Float32BufferAttribute(positions, 3))
        const points = new Points(geometry, new PointsMaterial({ color: config.color, size: 0.03, transparent: true, opacity: 0.9 }))
        group.add(points)
    }

    if (config.id === 'contact') {
        const beacon = new Mesh(
            new SphereGeometry(0.11, 20, 20),
            new MeshBasicMaterial({ color: '#f4f8ff' }),
        )
        beacon.position.set(0, config.height * 0.52, depth + 0.2)
        group.add(beacon)
    }

    return group
}

const gatewayGroups = computed(() => gateways.value.map((config) => markRaw(buildGateway(config))))

const gatewayLights = gateways.value.map((config) => {
    const light = new PointLight(config.color, 2.6, 6, 2)
    light.position.set(config.position[0], 1.1, config.position[1] + 0.6)

    return markRaw(light)
})

const emblemGroup = markRaw(new Group())
emblemGroup.position.set(0, narrow ? 2.1 : 1.62, narrow ? -2.1 : -1.6)
const emblemGeometry = new IcosahedronGeometry(0.52 * scale, 1)
const emblemEdges = new LineSegments(
    new EdgesGeometry(emblemGeometry),
    new LineBasicMaterial({ color: '#d7f4ff', transparent: true, opacity: 0.8 }),
)
const emblemFill = new Mesh(
    emblemGeometry,
    new MeshBasicMaterial({ color: '#5ee6ff', transparent: true, opacity: 0.06, blending: AdditiveBlending, depthWrite: false }),
)
emblemGroup.add(emblemFill, emblemEdges)

const emblemLight = markRaw(new PointLight('#8eecff', 3.2, 5, 2))
emblemLight.position.set(0, 0.4, -1.6)

onUnmounted(() => {
    floor.dispose()
    emblemGeometry.dispose()
    emblemEdges.geometry.dispose()
    ;(emblemEdges.material as LineBasicMaterial).dispose()
    ;(emblemFill.material as MeshBasicMaterial).dispose()
    gatewayGroups.value.forEach((group) => {
        group.traverse((child) => {
            if (child instanceof Mesh || child instanceof Points) {
                child.geometry.dispose()
                const material = child.material
                if (Array.isArray(material)) {
                    material.forEach((m) => m.dispose())
                } else {
                    material.dispose()
                }
            }
        })
    })
})

const position = new Vector3()
const look = new Vector3()
const DRIFT_CYCLE = 17

useCameraRig(
    (target, elapsed) => {
        const driftAmp = props.motion ? 0.14 : 0
        const driftX = Math.sin((elapsed / DRIFT_CYCLE) * Math.PI * 2) * driftAmp
        const driftY = Math.sin((elapsed / (DRIFT_CYCLE * 1.4)) * Math.PI * 2) * driftAmp * 0.35

        const shiftX = props.motion ? props.parallaxX * 0.22 : 0
        const shiftY = props.motion ? props.parallaxY * -0.08 : 0

        const lean = gateways.value.find((g) => g.id === hovered.value)
        const leanX = lean ? lean.position[0] * 0.1 : 0

        if (narrow) {
            position.set(driftX, 1.9 + driftY, 18)
            look.set(leanX, 1.6, -2.6)
        } else {
            position.set(driftX + shiftX, 1.64 + driftY + shiftY, 6.5)
            look.set(leanX + shiftX * 0.25, 1.5, -2.6)
        }

        target.position.copy(position)
        target.look.copy(look)
    },
    () => props.motion,
    (elapsed, moving) => {
        floor.material.uniforms.uTime.value = elapsed
        floor.material.uniforms.uMotion.value = moving ? 1 : 0
        emblemGroup.rotation.y = elapsed * 0.14
        emblemGroup.rotation.x = Math.sin(elapsed * 0.11) * 0.08

        gatewayLights.forEach((light, index) => {
            const base = 2.6
            light.intensity = moving ? base * (0.88 + 0.12 * Math.sin(elapsed * 1.1 + index * 1.7)) : base
        })
        emblemLight.intensity = moving ? 3.2 * (0.85 + 0.15 * Math.sin(elapsed * 1.4)) : 3.2
    },
)
</script>

<template>
    <TresPerspectiveCamera :position="narrow ? [0, 1.9, 18] : [0, 1.64, 6.5]" :fov="narrow ? 42 : 38" :near="0.1" :far="40" />

    <TresAmbientLight :intensity="0.08" color="#8aa0c8" />
    <TresDirectionalLight :position="[0, 7, -9]" :intensity="0.3" color="#eef4ff" />
    <primitive v-for="light in gatewayLights" :key="light.uuid" :object="light" />
    <primitive :object="emblemLight" />

    <primitive :object="floor.group" />
    <primitive :object="emblemGroup" />

    <primitive
        v-for="(group, index) in gatewayGroups"
        :key="gateways[index]?.id"
        :object="group"
        @pointerenter="setHover(gateways[index]?.id ?? null)"
        @pointerleave="setHover(null)"
        @click="gateways[index] && emit('select', gateways[index]!.id)"
    />
</template>
