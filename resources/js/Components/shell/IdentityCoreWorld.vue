<script setup lang="ts">
import { useLoop } from '@tresjs/core'
import { ref } from 'vue'

const props = defineProps<{
    motion: boolean
}>()

const group = ref()

const { onBeforeRender } = useLoop()
onBeforeRender(({ delta }) => {
    if (!props.motion || !group.value) {
        return
    }

    group.value.rotation.y += delta * 0.12
})
</script>

<template>
    <TresAmbientLight :intensity="0.35" color="#caa36b" />
    <TresDirectionalLight :position="[-2.4, 2.4, 3]" :intensity="2.4" color="#fff3da" />
    <TresPointLight :position="[1.6, -1, 2]" :intensity="0.6" color="#f2c58a" :distance="10" :decay="2" />

    <TresGroup ref="group">
        <TresMesh>
            <TresSphereGeometry :args="[1, 48, 48]" />
            <TresMeshStandardMaterial color="#b88045" emissive="#3b2a1f" :emissive-intensity="0.6" :roughness="0.42" :metalness="0.18" />
        </TresMesh>
        <TresMesh :rotation-x="Math.PI / 2.1" :rotation-z="0.157">
            <TresTorusGeometry :args="[1.55, 0.006, 8, 96]" />
            <TresMeshBasicMaterial color="#f2c58a" :transparent="true" :opacity="0.75" />
        </TresMesh>
    </TresGroup>
</template>
