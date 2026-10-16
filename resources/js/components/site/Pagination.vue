<script setup lang="ts">
import type { PaginationLink } from '@/types/site';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{ links: PaginationLink[] }>();

const previous = computed(() => props.links[0] ?? null);
const next = computed(() => props.links[props.links.length - 1] ?? null);
const pages = computed(() => props.links.slice(1, -1));

const cleanLabel = (label: string) => label.replace(/&laquo;|&raquo;/g, '').trim();
</script>

<template>
    <nav v-if="props.links.length > 3" class="flex items-center justify-center gap-1.5" aria-label="ترقيم الصفحات">
        <Link
            v-if="previous?.url"
            :href="previous.url"
            preserve-scroll
            class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition hover:border-brand-300 hover:text-brand-700"
            aria-label="السابق"
        >
            <ChevronRight class="size-5" />
        </Link>
        <span v-else class="inline-flex size-10 items-center justify-center rounded-lg border border-border/60 bg-muted text-muted-foreground/40">
            <ChevronRight class="size-5" />
        </span>

        <template v-for="(link, index) in pages" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="inline-flex size-10 items-center justify-center rounded-lg border text-sm font-medium transition"
                :class="
                    link.active
                        ? 'border-brand-600 bg-brand-600 text-white shadow-sm'
                        : 'border-border bg-card text-foreground hover:border-brand-300 hover:text-brand-700'
                "
            >
                {{ cleanLabel(link.label) }}
            </Link>
            <span
                v-else
                class="inline-flex size-10 items-center justify-center rounded-lg border border-border/60 bg-muted text-sm text-muted-foreground/50"
            >
                {{ cleanLabel(link.label) }}
            </span>
        </template>

        <Link
            v-if="next?.url"
            :href="next.url"
            preserve-scroll
            class="inline-flex size-10 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition hover:border-brand-300 hover:text-brand-700"
            aria-label="التالي"
        >
            <ChevronLeft class="size-5" />
        </Link>
        <span v-else class="inline-flex size-10 items-center justify-center rounded-lg border border-border/60 bg-muted text-muted-foreground/40">
            <ChevronLeft class="size-5" />
        </span>
    </nav>
</template>
