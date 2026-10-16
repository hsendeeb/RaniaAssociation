<script setup lang="ts">
import { SearchX } from 'lucide-vue-next';

interface Props {
    title?: string;
    description?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'لا توجد نتائج',
    description: 'لم نجد أي عناصر مطابقة لبحثك. جرّب تعديل المعايير.',
});
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card/50 px-6 py-16 text-center">
        <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-muted text-muted-foreground">
            <SearchX class="size-7" />
        </div>
        <h3 class="text-lg font-bold text-foreground">{{ title }}</h3>
        <p class="mt-1 max-w-sm text-sm text-muted-foreground">{{ description }}</p>
        <div class="mt-5">
            <slot />
        </div>
    </div>
</template>
