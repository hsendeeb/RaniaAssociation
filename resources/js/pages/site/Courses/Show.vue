<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import CourseCard from '@/components/site/CourseCard.vue';
import StatusBadge from '@/components/site/StatusBadge.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Course, Enrollment, Lesson } from '@/types/site';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, CalendarDays, Clock, GraduationCap, Layers, Lock, LogIn, Paperclip, PlayCircle, UserCog, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({ layout: SiteLayout });

const page = usePage();

const props = defineProps<{
    course: Course;
    relatedCourses: Course[];
    availableSeats: boolean;
    enrollment: Enrollment | null;
    canViewContent: boolean;
}>();

const paragraphs = (props.course.description ?? '').split('\n').filter((line) => line.trim().length > 0);

const isGuest = computed(() => !page.props.auth?.user);

const lessonsCount = computed(() => props.course.lessons_count ?? props.course.lessons?.length ?? 0);

const isPendingRequest = computed(() => props.enrollment?.status.value === 'pending');

/** نص الدرس بدون وسوم HTML (للتحقق من وجود محتوى فعلي). */
function lessonText(content: string | null): string {
    return (content ?? '').replace(/<[^>]*>/g, '').trim();
}

/**
 * الدرس قابل للفتح إذا احتوى على فيديو أو نص أو مرفق.
 * الدرس الذي لا يحتوي على أي منها فقط هو الذي يظهر مقفلًا.
 */
function hasLessonBody(lesson: Lesson): boolean {
    return Boolean(lesson.video_url) || Boolean(lesson.attachment_url) || lessonText(lesson.content).length > 0;
}

const activeLessonId = ref<number | null>(null);

function youtubeId(url: string | null): string | null {
    if (!url) {
        return null;
    }

    const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([\w-]{11})/);

    return match ? match[1] : null;
}

function youtubeStart(url: string | null): number | null {
    const value = url?.match(/[?&]t=([0-9hms]+)/)?.[1];

    if (!value) {
        return null;
    }

    if (/^\d+$/.test(value)) {
        return Number.parseInt(value, 10);
    }

    const hours = Number(value.match(/(\d+)h/)?.[1] ?? 0);
    const minutes = Number(value.match(/(\d+)m/)?.[1] ?? 0);
    const seconds = Number(value.match(/(\d+)s/)?.[1] ?? 0);
    const total = hours * 3600 + minutes * 60 + seconds;

    return total > 0 ? total : null;
}

function isYoutube(url: string | null): boolean {
    return youtubeId(url) !== null;
}

function videoSrc(url: string | null): string {
    const id = youtubeId(url);

    if (!id) {
        return url ?? '';
    }

    const start = youtubeStart(url);

    return `https://www.youtube-nocookie.com/embed/${id}${start ? `?start=${start}` : ''}`;
}

function toggleLesson(lesson: Lesson): void {
    if (!hasLessonBody(lesson)) {
        return;
    }

    activeLessonId.value = activeLessonId.value === lesson.id ? null : lesson.id;
}
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
                    <span class="text-sm text-muted-foreground">{{ lessonsCount }} درس</span>
                </div>

                <!-- المحتوى مقفل: يظهر فقط للطلاب الذين وافقت الإدارة على تسجيلهم -->
                <div v-if="!props.canViewContent" class="rounded-2xl border border-dashed border-brand-200 bg-card p-10 text-center">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-brand-50">
                        <Lock class="size-7 text-brand-700" />
                    </div>

                    <!-- طلب التسجيل قيد المراجعة -->
                    <template v-if="isPendingRequest">
                        <h3 class="text-lg font-bold text-foreground">طلب تسجيلك قيد المراجعة</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
                            سيُفتح محتوى الدورة تلقائيًا بعد موافقة الإدارة على طلبك، ويمكنك متابعة حالة الطلب من لوحتك.
                        </p>
                        <Link
                            :href="route('dashboard')"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800"
                        >
                            متابعة من لوحتي
                            <ArrowLeft class="size-4" />
                        </Link>
                    </template>

                    <!-- زائر أو طالب لم تُقبل طلبه بعد -->
                    <template v-else>
                        <h3 class="text-lg font-bold text-foreground">محتوى الدورة مقفل</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
                            يظهر محتوى الدورة للطلاب المقبولين فقط. سجّل في الدورة وسيُفتح المحتوى بعد موافقة الإدارة.
                        </p>
                        <Link
                            v-if="props.availableSeats"
                            :href="route('enrollments.create', props.course.slug)"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800"
                        >
                            سجّل في الدورة
                            <ArrowLeft class="size-4" />
                        </Link>
                        <span
                            v-else
                            class="mt-5 inline-flex items-center rounded-xl bg-muted px-5 py-2.5 text-sm font-bold text-muted-foreground"
                        >
                            الدورة ممتلئة
                        </span>
                        <div v-if="isGuest" class="mt-3 flex items-center justify-center gap-3 text-xs text-muted-foreground">
                            <Link :href="route('login')" class="font-semibold text-brand-700 hover:text-brand-800">
                                تسجيل الدخول
                            </Link>
                            <span>•</span>
                            <Link :href="route('register')" class="font-semibold text-brand-700 hover:text-brand-800">
                                إنشاء حساب جديد
                            </Link>
                        </div>
                    </template>
                </div>

                <ol v-else-if="props.course.lessons?.length" class="space-y-2.5">
                    <li
                        v-for="(lesson, index) in props.course.lessons"
                        :key="lesson.id"
                        class="overflow-hidden rounded-xl border border-border bg-card"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center gap-4 p-4 text-start"
                            :class="hasLessonBody(lesson) ? 'transition hover:bg-muted/50' : 'cursor-default'"
                            :disabled="!hasLessonBody(lesson)"
                            @click="toggleLesson(lesson)"
                        >
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-sm font-bold text-brand-700">
                                {{ index + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-foreground">{{ lesson.title }}</p>
                                <p v-if="lesson.duration_minutes" class="mt-0.5 text-xs text-muted-foreground">{{ lesson.duration_minutes }} دقيقة</p>
                            </div>
                            <PlayCircle
                                v-if="lesson.video_url"
                                class="size-5 shrink-0 text-brand-500 transition"
                                :class="activeLessonId === lesson.id ? 'rotate-90' : ''"
                            />
                            <Paperclip v-else-if="hasLessonBody(lesson)" class="size-4 shrink-0 text-brand-500" />
                            <Lock v-else class="size-4 shrink-0 text-muted-foreground/60" />
                        </button>

                        <div v-if="activeLessonId === lesson.id" class="space-y-4 border-t border-border bg-muted/30 p-4">
                            <div v-if="lesson.video_url">
                                <div v-if="isYoutube(lesson.video_url)" class="aspect-video w-full overflow-hidden rounded-lg bg-black">
                                    <iframe
                                        :src="videoSrc(lesson.video_url)"
                                        :title="lesson.title"
                                        class="h-full w-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                                <template v-else>
                                    <video :src="videoSrc(lesson.video_url)" controls class="w-full rounded-lg"></video>
                                    <a
                                        :href="lesson.video_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800"
                                    >
                                        فتح الفيديو في نافذة جديدة
                                    </a>
                                </template>
                            </div>

                            <div
                                v-if="lessonText(lesson.content)"
                                class="lesson-content text-base leading-loose text-muted-foreground"
                                v-html="lesson.content"
                            ></div>

                            <a
                                v-if="lesson.attachment_url"
                                :href="lesson.attachment_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-bold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                            >
                                <Paperclip class="size-4 text-brand-500" />
                                تحميل مرفق الدرس
                            </a>
                        </div>
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

<style scoped>
/* تنسيق محتوى الدروس الغني القادم من المحرر (Tailwind يُزيل التنسيقات الافتراضية). */
.lesson-content :deep(h1),
.lesson-content :deep(h2),
.lesson-content :deep(h3) {
    margin-block: 1rem 0.5rem;
    font-weight: 700;
    color: var(--foreground);
}

.lesson-content :deep(p) {
    margin-block-end: 0.75rem;
}

.lesson-content :deep(ul) {
    margin-block-end: 0.75rem;
    list-style: disc;
    padding-inline-start: 1.5rem;
}

.lesson-content :deep(ol) {
    margin-block-end: 0.75rem;
    list-style: decimal;
    padding-inline-start: 1.5rem;
}

.lesson-content :deep(a) {
    color: #047857;
    text-decoration: underline;
}

.lesson-content :deep(img) {
    margin-block: 0.75rem;
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
}

.lesson-content :deep(code) {
    border-radius: 0.375rem;
    background-color: rgb(0 0 0 / 6%);
    padding: 0.1rem 0.35rem;
    font-size: 0.9em;
}

.lesson-content :deep(blockquote) {
    margin-block-end: 0.75rem;
    border-inline-start: 3px solid var(--border);
    padding-inline-start: 0.75rem;
}
</style>
