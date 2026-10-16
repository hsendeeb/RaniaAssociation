<script setup lang="ts">
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage();
const toastVisible = ref(false);

const errorMessage = computed(() => page.props.flash?.error ?? null);
const successMessage = computed(() => page.props.flash?.success ?? null);
const message = computed(() => successMessage.value ?? errorMessage.value);
const isError = computed(() => Boolean(errorMessage.value));

watch(
    message,
    (value) => {
        if (!value) {
            return;
        }

        toastVisible.value = true;
        window.setTimeout(() => (toastVisible.value = false), 5000);
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <SiteHeader />

        <main class="flex-1">
            <slot />
        </main>

        <SiteFooter />

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-4 opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="translate-y-4 opacity-0"
        >
            <div
                v-if="toastVisible && message"
                class="fixed inset-x-4 bottom-4 z-[60] mx-auto flex max-w-md items-center gap-3 rounded-xl border bg-card p-4 shadow-lg sm:inset-x-auto sm:end-6"
                :class="isError ? 'border-destructive/40' : 'border-brand-200'"
            >
                <AlertCircle v-if="isError" class="size-5 shrink-0 text-destructive" />
                <CheckCircle2 v-else class="size-5 shrink-0 text-brand-600" />
                <p class="text-sm font-medium text-foreground">{{ message }}</p>
            </div>
        </Transition>
    </div>
</template>
