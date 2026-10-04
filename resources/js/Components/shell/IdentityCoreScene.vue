<script setup lang="ts">
import { TresCanvas } from '@tresjs/core'
import { ACESFilmicToneMapping } from 'three'
import { pixelRatioCap } from '@/lib/experience'
import IdentityCoreWorld from '@/Components/shell/IdentityCoreWorld.vue'

defineProps<{
    motion: boolean
}>()

const emit = defineEmits<{
    ready: []
    failure: []
}>()

const dpr = pixelRatioCap()
</script>

<template>
    <TresCanvas
        alpha
        :clear-alpha="0"
        render-mode="always"
        :fps-limit="30"
        :dpr="dpr"
        :antialias="true"
        :shadows="false"
        :tone-mapping="ACESFilmicToneMapping"
        :tone-mapping-exposure="1.05"
        @ready="emit('ready')"
        @error="emit('failure')"
    >
        <TresPerspectiveCamera :position="[0, 0, 4.2]" :fov="32" :near="0.1" :far="20" />
        <IdentityCoreWorld :motion="motion" />
    </TresCanvas>
</template>
