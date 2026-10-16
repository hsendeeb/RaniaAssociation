<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
    <div :class="cn('mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8', props.class)">
        <slot />
    </div>
</template>
