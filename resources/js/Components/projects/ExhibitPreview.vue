<script setup lang="ts">
defineProps<{
    variant: 'schedule' | 'grid' | 'pulse' | 'contour' | 'hub' | 'lanes'
}>()
</script>

<template>
    <svg class="exhibit-preview" viewBox="0 0 200 120" preserveAspectRatio="none" aria-hidden="true">
        <g v-if="variant === 'schedule'" stroke="var(--color-blue)" stroke-width="1" fill="none" opacity="0.8">
            <line x1="14" y1="20" x2="186" y2="20" />
            <line x1="14" y1="48" x2="186" y2="48" />
            <line x1="14" y1="76" x2="186" y2="76" />
            <line x1="14" y1="104" x2="186" y2="104" />
            <rect x="30" y="14" width="34" height="12" rx="2" />
            <rect x="80" y="42" width="54" height="12" rx="2" />
            <rect x="46" y="70" width="40" height="12" stroke="var(--color-gold)" rx="2" />
            <rect x="110" y="98" width="30" height="12" rx="2" />
        </g>
        <g v-else-if="variant === 'grid'" stroke="var(--color-blue)" stroke-width="1" fill="none" opacity="0.8">
            <rect x="16" y="16" width="168" height="88" />
            <line x1="16" y1="38" x2="184" y2="38" />
            <line x1="16" y1="60" x2="184" y2="60" />
            <line x1="16" y1="82" x2="184" y2="82" />
            <line x1="58" y1="16" x2="58" y2="104" />
            <line x1="100" y1="16" x2="100" y2="104" />
            <line x1="142" y1="16" x2="142" y2="104" />
            <circle cx="121" cy="71" r="4" fill="var(--color-gold)" stroke="none" />
        </g>
        <g v-else-if="variant === 'pulse'" stroke="var(--color-blue)" stroke-width="1" fill="none" opacity="0.85">
            <polyline points="12,70 44,70 56,30 68,96 80,52 96,70 128,70 140,40 152,84 168,70 188,70" />
            <circle cx="140" cy="40" r="3.4" fill="var(--color-gold)" stroke="none" />
        </g>
        <g v-else-if="variant === 'contour'" stroke="var(--color-blue)" stroke-width="1" fill="none" opacity="0.8">
            <path d="M10 90 Q 60 40 100 60 T 190 50" />
            <path d="M10 70 Q 60 20 100 40 T 190 30" opacity="0.6" />
            <path d="M10 108 Q 60 66 100 82 T 190 74" opacity="0.6" />
            <circle cx="150" cy="38" r="3.4" fill="var(--color-gold)" stroke="none" />
        </g>
        <g v-else-if="variant === 'hub'" stroke="var(--color-blue)" stroke-width="1" fill="none" opacity="0.8">
            <circle cx="100" cy="60" r="10" />
            <line x1="100" y1="50" x2="100" y2="16" />
            <line x1="108" y1="65" x2="172" y2="90" />
            <line x1="92" y1="65" x2="28" y2="90" />
            <line x1="108" y1="55" x2="166" y2="30" />
            <line x1="92" y1="55" x2="34" y2="30" />
            <circle cx="100" cy="16" r="3" fill="var(--color-gold)" stroke="none" />
        </g>
        <g v-else stroke="var(--color-blue)" stroke-width="1" fill="none" opacity="0.8">
            <rect x="24" y="18" width="30" height="20" />
            <rect x="70" y="30" width="46" height="30" />
            <rect x="24" y="56" width="60" height="16" />
            <rect x="130" y="18" width="40" height="52" stroke="var(--color-gold)" />
            <line x1="24" y1="92" x2="176" y2="92" opacity="0.5" />
            <line x1="24" y1="100" x2="140" y2="100" opacity="0.5" />
        </g>
    </svg>
</template>
