<script setup lang="ts">
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <img src="/images/logo.jpeg" alt="جمعية رانيا" class="size-8 shrink-0 rounded-md object-cover" />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-none">جمعية رانيا</span>
    </div>
</template>
