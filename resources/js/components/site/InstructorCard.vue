<script setup lang="ts">
import type { Instructor } from '@/types/site';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen } from 'lucide-vue-next';

const props = defineProps<{ instructor: Instructor }>();
</script>

<template>
    <article
        class="group flex flex-col items-center rounded-2xl border border-border bg-card p-6 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg"
    >
        <div class="mb-4 flex size-24 items-center justify-center overflow-hidden rounded-full ring-4 ring-brand-50">
            <img
                v-if="props.instructor.avatar"
                :src="props.instructor.avatar"
                :alt="props.instructor.name"
                loading="lazy"
                class="size-full object-cover"
            />
            <div
                v-else
                class="flex size-full items-center justify-center bg-gradient-to-br from-brand-600 to-brand-800 text-2xl font-bold text-white"
            >
                {{ props.instructor.initials }}
            </div>
        </div>

        <h3 class="text-lg font-bold text-foreground">{{ props.instructor.name }}</h3>
        <p v-if="props.instructor.specialization" class="mt-0.5 text-sm font-medium text-brand-600">{{ props.instructor.specialization }}</p>
        <p v-if="props.instructor.bio" class="mt-3 line-clamp-3 text-sm leading-relaxed text-muted-foreground">{{ props.instructor.bio }}</p>

        <div class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
            <BookOpen class="size-3.5" />
            {{ props.instructor.courses_count ?? 0 }} دورة
        </div>

        <Link
            :href="route('instructors.show', props.instructor.id)"
            class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 transition hover:gap-2.5 hover:text-brand-800"
        >
            عرض الملف
            <ArrowLeft class="size-4" />
        </Link>
    </article>
</template>
