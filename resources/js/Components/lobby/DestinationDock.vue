<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { Destination } from '@/types/portfolio'

defineProps<{
    destinations: Destination[]
}>()

const emit = defineEmits<{
    contact: []
}>()
</script>

<template>
    <ul class="mt-4 grid list-none gap-3 p-0 sm:grid-cols-3">
        <li v-for="item in destinations" :key="item.id">
            <Link v-if="item.href" :href="item.href" class="dest" prefetch>
                <span class="eyebrow">{{ item.kicker }}</span>
                <span class="dest-title">{{ item.label }}</span>
                <span class="dest-copy">{{ item.summary }}</span>
            </Link>
            <button v-else type="button" class="dest" @click="emit('contact')">
                <span class="eyebrow">{{ item.kicker }}</span>
                <span class="dest-title">{{ item.label }}</span>
                <span class="dest-copy">{{ item.summary }}</span>
            </button>
        </li>
    </ul>
</template>
