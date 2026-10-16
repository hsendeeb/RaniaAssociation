<script setup lang="ts">
import type { Course } from '@/types/site';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Clock, Star, Users } from 'lucide-vue-next';

const props = defineProps<{ course: Course }>();
</script>

<template>
    <article
        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg"
    >
        <Link :href="route('courses.show', props.course.slug)" class="relative block aspect-[16/10] overflow-hidden">
            <img
                v-if="props.course.cover_image"
                :src="props.course.cover_image"
                :alt="props.course.title"
                loading="lazy"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900">
                <BookOpen class="size-14 text-white/30" />
            </div>

            <span
                v-if="props.course.category"
                class="absolute end-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-brand-800 shadow-sm backdrop-blur"
            >
                {{ props.course.category.name }}
            </span>
            <span
                v-if="props.course.is_featured"
                class="absolute start-3 top-3 inline-flex items-center gap-1 rounded-full bg-gold-400/95 px-2.5 py-1 text-xs font-bold text-gold-700 shadow-sm"
            >
                <Star class="size-3.5 fill-current" />
                مميّزة
            </span>
        </Link>

        <div class="flex flex-1 flex-col p-5">
            <div class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                <span v-if="props.course.level.label" class="rounded-md bg-brand-50 px-2 py-0.5 font-medium text-brand-700">
                    {{ props.course.level.label }}
                </span>
                <span v-if="props.course.duration_hours" class="inline-flex items-center gap-1 text-muted-foreground">
                    <Clock class="size-3.5" />
                    {{ props.course.duration_hours }} ساعة
                </span>
                <span v-if="props.course.lessons_count" class="inline-flex items-center gap-1 text-muted-foreground">
                    <BookOpen class="size-3.5" />
                    {{ props.course.lessons_count }} درس
                </span>
            </div>

            <h3 class="mb-2 text-lg font-bold leading-snug text-foreground">
                <Link :href="route('courses.show', props.course.slug)" class="transition hover:text-brand-700">
                    {{ props.course.title }}
                </Link>
            </h3>
            <p class="mb-4 line-clamp-2 flex-1 text-sm leading-relaxed text-muted-foreground">{{ props.course.summary }}</p>

            <div class="mt-auto flex items-center justify-between gap-3 border-t border-border pt-4">
                <span class="inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                    <Users class="size-4 text-brand-500" />
                    {{ props.course.instructor?.name ?? '—' }}
                </span>
                <span v-if="props.course.is_free" class="rounded-full bg-brand-50 px-3 py-1 text-sm font-bold text-brand-700"> مجاني </span>
                <span v-else class="text-sm font-bold text-foreground" dir="ltr">
                    <span class="text-xs font-medium text-muted-foreground">$</span>{{ props.course.price }}
                </span>
            </div>
        </div>
    </article>
</template>
