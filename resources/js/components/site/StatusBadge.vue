<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { EnrollmentStatusValue } from '@/types/site';
import { computed } from 'vue';

interface Props {
    status: EnrollmentStatusValue;
    class?: string;
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const styles: Record<string, string> = {
    pending: 'bg-gold-100 text-gold-700 ring-gold-200',
    active: 'bg-brand-50 text-brand-700 ring-brand-200',
    completed: 'bg-brand-600 text-white ring-brand-600',
    cancelled: 'bg-muted text-muted-foreground ring-border',
};

const classes = computed(() => styles[props.status.value] ?? styles.cancelled);
</script>

<template>
    <span :class="cn('inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', classes, props.class)">
        {{ props.status.label }}
    </span>
</template>
