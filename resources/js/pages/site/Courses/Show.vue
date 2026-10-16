<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import CourseCard from '@/components/site/CourseCard.vue';
import StatusBadge from '@/components/site/StatusBadge.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Course, Enrollment } from '@/types/site';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, CalendarDays, Clock, GraduationCap, Layers, Lock, PlayCircle, UserCog, Users } from 'lucide-vue-next';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    course: Course;
    relatedCourses: Course[];
    availableSeats: boolean;
    enrollment: Enrollment | null;
}>();

const paragraphs = (props.course.description ?? '').split('\n').filter((line) => line.trim().length > 0);
</script>

<template>
    <Head :title="props.course.title" />

    <!-- Breadcrumb -->
    <div class="border-b border-border bg-card">
        <Container class="flex flex-wrap items-center gap-2 py-4 text-sm text-muted-foreground">
            <Link :href="route('home')" class="transition hover:text-brand-700">الرئيسية</Link>
            <span>/</span>
            <Link :href="route('courses.index')" class="transition hover:text-brand-700">الدورات</Link>
            <span>/</span>
            <span class="font-medium text-foreground">{{ props.course.title }}</span>
        </Container>
    </div>

    <!-- Hero -->
    <section class="bg-hero-wash">
        <Container class="grid gap-10 py-12 lg:grid-cols-[1.6fr_1fr] lg:py-16">
            <div>
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <Link
                        v-if="props.course.category"
                        :href="route('courses.index', { category: props.course.category.slug })"
                        class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700"
                    >
                        {{ props.course.category.name }}
                    </Link>
                    <span v-if="props.course.level.label" class="rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground">
                        {{ props.course.level.label }}
                    </span>
                </div>

                <h1 class="text-balance text-3xl font-extrabold leading-tight tracking-tight text-foreground sm:text-4xl">
                    {{ props.course.title }}
                </h1>
                <p v-if="props.course.summary" class="mt-4 max-w-2xl text-base leading-relaxed text-muted-foreground">
                    {{ props.course.summary }}
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-muted-foreground">
                    <span v-if="props.course.instructor" class="inline-flex items-center gap-1.5">
                        <UserCog class="size-4 text-brand-500" />
                        {{ props.course.instructor.name }}
                    </span>
                    <span v-if="props.course.duration_hours" class="inline-flex items-center gap-1.5">
                        <Clock class="size-4 text-brand-500" />
                        {{ props.course.duration_hours }} ساعة
                    </span>
                    <span v-if="props.course.lessons_count !== undefined" class="inline-flex items-center gap-1.5">
                        <BookOpen class="size-4 text-brand-500" />
                        {{ props.course.lessons_count }} درس
                    </span>
                    <span v-if="props.course.students_count !== undefined" class="inline-flex items-center gap-1.5">
                        <Users class="size-4 text-brand-500" />
                        {{ props.course.students_count }} طالب
                    </span>
                </div>
            </div>

            <div
                class="relative overflow-hidden rounded-3xl border border-border bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 shadow-lg"
            >
                <img
                    v-if="props.course.cover_image"
                    :src="props.course.cover_image"
                    :alt="props.course.title"
                    class="h-full min-h-64 w-full object-cover"
                />
                <div v-else class="flex min-h-64 items-center justify-center">
                    <GraduationCap class="size-24 text-white/20" />
                </div>
            </div>
        </Container>
    </section>

    <Container class="grid gap-12 py-12 lg:grid-cols-[1.6fr_1fr] lg:py-16">
        <!-- Main -->
        <div class="space-y-12">
            <section>
                <h2 class="mb-4 text-xl font-bold text-foreground">عن الدورة</h2>
                <div v-if="paragraphs.length" class="space-y-4 text-base leading-loose text-muted-foreground">
                    <p v-for="(paragraph, index) in paragraphs" :key="index">{{ paragraph }}</p>
                </div>
                <p v-else class="text-base leading-loose text-muted-foreground">سيتم إضافة وصف الدورة قريبًا.</p>
            </section>

            <section>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-foreground">محتوى الدورة</h2>
                    <span class="text-sm text-muted-foreground">{{ props.course.lessons?.length ?? 0 }} درس</span>
                </div>

                <ol v-if="props.course.lessons?.length" class="space-y-2.5">
                    <li
                        v-for="(lesson, index) in props.course.lessons"
                        :key="lesson.id"
                        class="flex items-center gap-4 rounded-xl border border-border bg-card p-4"
                    >
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">
                            {{ index + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-foreground">{{ lesson.title }}</p>
                            <p v-if="lesson.duration_minutes" class="mt-0.5 text-xs text-muted-foreground">{{ lesson.duration_minutes }} دقيقة</p>
                        </div>
                        <PlayCircle v-if="lesson.video_url" class="size-5 shrink-0 text-brand-500" />
                        <Lock v-else class="size-4 shrink-0 text-muted-foreground/60" />
                    </li>
                </ol>
                <p v-else class="rounded-xl border border-dashed border-border bg-card/50 p-6 text-center text-sm text-muted-foreground">
                    لم تُضف دروس لهذه الدورة بعد.
                </p>
            </section>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <div class="flex items-baseline justify-between">
                    <span v-if="props.course.is_free" class="text-2xl font-extrabold text-brand-700">مجاني</span>
                    <span v-else class="text-2xl font-extrabold text-foreground" dir="ltr">
                        <span class="text-sm font-medium text-muted-foreground">$</span>{{ props.course.price }}
                    </span>
                </div>

                <!-- Enrolled -->
                <template v-if="props.enrollment && props.enrollment.status.value !== 'cancelled'">
                    <div class="mt-5 flex items-center justify-between rounded-xl bg-muted px-4 py-3">
                        <span class="text-sm font-medium text-muted-foreground">حالة تسجيلك</span>
                        <StatusBadge :status="props.enrollment.status" />
                    </div>
                    <Link
                        :href="route('dashboard')"
                        class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-border px-5 py-3 text-sm font-bold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                    >
                        اذهب إلى لوحتي
                        <ArrowLeft class="size-4" />
                    </Link>
                    <p v-if="props.enrollment.status.value === 'pending'" class="mt-3 text-center text-xs text-muted-foreground">
                        طلبك قيد المراجعة من قِبل الإدارة.
                    </p>
                </template>

                <!-- Cancelled: allow re-enrolment -->
                <template v-else-if="props.enrollment">
                    <Link
                        :href="route('enrollments.create', props.course.slug)"
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800"
                    >
                        إعادة التسجيل في الدورة
                        <ArrowLeft class="size-4" />
                    </Link>
                </template>

                <!-- Not enrolled -->
                <template v-else>
                    <Link
                        v-if="props.availableSeats"
                        :href="route('enrollments.create', props.course.slug)"
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800"
                    >
                        سجّل في الدورة
                        <ArrowLeft class="size-4" />
                    </Link>
                    <span
                        v-else
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-muted px-5 py-3 text-sm font-bold text-muted-foreground"
                    >
                        الدورة ممتلئة
                    </span>
                    <p class="mt-3 text-center text-xs text-muted-foreground">
                        {{ props.availableSeats ? 'سيصلك رد الإدارة بعد مراجعة طلب تسجيلك.' : 'يمكنك التواصل معنا للانضمام إلى قائمة الانتظار.' }}
                    </p>
                </template>

                <dl class="mt-6 space-y-3 border-t border-border pt-5 text-sm">
                    <div v-if="props.course.level.label" class="flex items-center justify-between">
                        <dt class="inline-flex items-center gap-2 text-muted-foreground"><Layers class="size-4" /> المستوى</dt>
                        <dd class="font-medium text-foreground">{{ props.course.level.label }}</dd>
                    </div>
                    <div v-if="props.course.duration_hours" class="flex items-center justify-between">
                        <dt class="inline-flex items-center gap-2 text-muted-foreground"><Clock class="size-4" /> المدة</dt>
                        <dd class="font-medium text-foreground">{{ props.course.duration_hours }} ساعة</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="inline-flex items-center gap-2 text-muted-foreground"><BookOpen class="size-4" /> الدروس</dt>
                        <dd class="font-medium text-foreground">{{ props.course.lessons_count ?? props.course.lessons?.length ?? 0 }}</dd>
                    </div>
                    <div v-if="props.course.starts_at" class="flex items-center justify-between">
                        <dt class="inline-flex items-center gap-2 text-muted-foreground"><CalendarDays class="size-4" /> تاريخ البدء</dt>
                        <dd class="font-medium text-foreground" dir="ltr">{{ props.course.starts_at }}</dd>
                    </div>
                    <div v-if="props.course.capacity" class="flex items-center justify-between">
                        <dt class="inline-flex items-center gap-2 text-muted-foreground"><Users class="size-4" /> المقاعد</dt>
                        <dd class="font-medium text-foreground">{{ props.course.capacity }}</dd>
                    </div>
                </dl>
            </div>

            <div v-if="props.course.instructor" class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-foreground">المدرّب</h3>
                <div class="flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full ring-2 ring-brand-50">
                        <img
                            v-if="props.course.instructor.avatar"
                            :src="props.course.instructor.avatar"
                            :alt="props.course.instructor.name"
                            class="size-full object-cover"
                        />
                        <span
                            v-else
                            class="flex size-full items-center justify-center bg-gradient-to-br from-brand-600 to-brand-800 text-lg font-bold text-white"
                        >
                            {{ props.course.instructor.initials }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-foreground">{{ props.course.instructor.name }}</p>
                        <p v-if="props.course.instructor.specialization" class="truncate text-sm text-brand-600">
                            {{ props.course.instructor.specialization }}
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('instructors.show', props.course.instructor.id)"
                    class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800"
                >
                    عرض الملف الشخصي
                    <ArrowLeft class="size-4" />
                </Link>
            </div>
        </aside>
    </Container>

    <!-- Related -->
    <section v-if="props.relatedCourses.length" class="bg-sand-50 py-16 dark:bg-card/40">
        <Container>
            <h2 class="text-2xl font-extrabold text-foreground">دورات ذات صلة</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <CourseCard v-for="course in props.relatedCourses" :key="course.id" :course="course" />
            </div>
        </Container>
    </section>
</template>
