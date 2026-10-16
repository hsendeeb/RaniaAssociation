<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import CourseCard from '@/components/site/CourseCard.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Instructor } from '@/types/site';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    instructor: Instructor;
}>();
</script>

<template>
    <Head :title="props.instructor.name" />

    <section class="bg-hero-wash border-b border-border">
        <Container class="py-12 sm:py-16">
            <Link :href="route('instructors.index')" class="mb-6 inline-block text-sm font-medium text-brand-700 hover:text-brand-800">
                ← كل المدربين
            </Link>

            <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
                <div class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl ring-4 ring-brand-50">
                    <img v-if="props.instructor.avatar" :src="props.instructor.avatar" :alt="props.instructor.name" class="size-full object-cover" />
                    <span
                        v-else
                        class="flex size-full items-center justify-center bg-gradient-to-br from-brand-600 to-brand-800 text-3xl font-bold text-white"
                    >
                        {{ props.instructor.initials }}
                    </span>
                </div>

                <div>
                    <h1 class="text-3xl font-extrabold text-foreground">{{ props.instructor.name }}</h1>
                    <p v-if="props.instructor.specialization" class="mt-1 text-base font-medium text-brand-600">
                        {{ props.instructor.specialization }}
                    </p>
                    <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-card px-3 py-1 text-sm text-muted-foreground shadow-sm">
                        <BookOpen class="size-4 text-brand-500" />
                        {{ props.instructor.courses_count ?? 0 }} دورة
                    </div>
                </div>
            </div>

            <p v-if="props.instructor.bio" class="mt-6 max-w-3xl text-base leading-loose text-muted-foreground">
                {{ props.instructor.bio }}
            </p>
        </Container>
    </section>

    <Container class="py-12 sm:py-16">
        <h2 class="mb-8 text-2xl font-extrabold text-foreground">دورات المدرّب</h2>
        <div v-if="props.instructor.courses?.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <CourseCard v-for="course in props.instructor.courses" :key="course.id" :course="course" />
        </div>
        <EmptyState v-else title="لا توجد دورات منشورة" description="لم ينشر هذا المدرّب أي دورات حتى الآن." />
    </Container>
</template>
