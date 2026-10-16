<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    eyebrow?: string;
    title: string;
    description?: string;
    align?: 'start' | 'center';
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    align: 'start',
});
</script>

<template>
    <div :class="cn('max-w-2xl', props.align === 'center' && 'mx-auto text-center', props.class)">
        <p v-if="props.eyebrow" class="mb-2 text-sm font-bold uppercase tracking-wide text-brand-600">{{ props.eyebrow }}</p>
        <h2 class="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">{{ props.title }}</h2>
        <p v-if="props.description" class="mt-3 text-base leading-relaxed text-muted-foreground">{{ props.description }}</p>
    </div>
</template>
