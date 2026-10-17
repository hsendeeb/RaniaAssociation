<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import CourseCard from '@/components/site/CourseCard.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import InstructorCard from '@/components/site/InstructorCard.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Category, Course, Instructor } from '@/types/site';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, Layers, ShieldCheck, Sparkles, UserCog, Users } from 'lucide-vue-next';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    featuredCourses: Course[];
    latestCourses: Course[];
    categories: Category[];
    instructors: Instructor[];
    stats: { courses: number; lessons: number; students: number; instructors: number };
}>();

const statCards = [
    { label: 'دورة منشورة', value: props.stats.courses, icon: BookOpen },
    { label: 'درس تعليمي', value: props.stats.lessons, icon: Layers },
    { label: 'طالب مسجّل', value: props.stats.students, icon: Users },
    { label: 'مدرّب خبير', value: props.stats.instructors, icon: UserCog },
];
</script>

<template>
    <Head title="الرئيسية" />

    <!-- Hero -->
    <section class="bg-hero-wash relative overflow-hidden">
        <Container class="relative py-16 sm:py-20 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div class="animate-fade-up">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3.5 py-1.5 text-sm font-medium text-brand-700"
                    >
                        <Sparkles class="size-4" />
                        منصة تعليمية تابعة لجمعية رانيا
                    </span>
                    <h1 class="mt-6 text-balance text-4xl font-extrabold leading-tight tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                        نتعلّم، نُبدع، <span class="text-brand-700">ونبني المستقبل</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-muted-foreground sm:text-lg">
                        برامج ودورات تدريبية معتمدة بإشراف نخبة من المدربين، مصمّمة لتنمية المهارات وبناء المعرفة وخدمة المجتمع.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            :href="route('courses.index')"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800"
                        >
                            تصفّح الدورات
                            <ArrowLeft class="size-4" />
                        </Link>
                        <Link
                            :href="route('about')"
                            class="inline-flex items-center rounded-xl border border-border bg-card px-5 py-3 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                        >
                            تعرّف علينا
                        </Link>
                    </div>
                </div>

                <div class="relative hidden lg:block">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-3xl border border-border bg-card shadow-xl">
                        <img src="/images/hero.png" alt="جمعية رانيا" class="h-full w-full object-cover" />
                    </div>
                    <div class="absolute -bottom-6 -start-6 flex items-center gap-3 rounded-2xl border border-border bg-card p-4 shadow-lg">
                        <span class="flex size-11 items-center justify-center rounded-xl bg-gold-100 text-gold-600">
                            <ShieldCheck class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-bold text-foreground">شهادات معتمدة</p>
                            <p class="text-xs text-muted-foreground">من جمعية رانيا</p>
                        </div>
                    </div>
                </div>
            </div>
        </Container>
    </section>

    <!-- Stats -->
    <section class="border-y border-border bg-card">
        <Container class="grid grid-cols-2 gap-6 py-10 md:grid-cols-4">
            <div v-for="stat in statCards" :key="stat.label" class="flex items-center gap-4">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <component :is="stat.icon" class="size-6" />
                </span>
                <div>
                    <p class="text-2xl font-extrabold text-foreground">{{ stat.value }}</p>
                    <p class="text-sm text-muted-foreground">{{ stat.label }}</p>
                </div>
            </div>
        </Container>
    </section>

    <!-- Categories -->
    <section v-if="props.categories.length" class="py-16 sm:py-20">
        <Container>
            <SectionHeading eyebrow="التصنيفات" title="استكشف مجالات التعلّم" description="اختر المجال الذي يناسب اهتمامك وابدأ رحلتك التعليمية." />
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <Link
                    v-for="category in props.categories"
                    :key="category.id"
                    :href="route('courses.index', { category: category.slug })"
                    class="group rounded-2xl border border-border bg-card p-5 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white"
                    >
                        <Layers class="size-5" />
                    </span>
                    <h3 class="mt-4 font-bold text-foreground">{{ category.name }}</h3>
                    <p class="mt-1 text-sm text-muted-foreground">{{ category.courses_count ?? 0 }} دورة</p>
                </Link>
            </div>
        </Container>
    </section>

    <!-- Featured courses -->
    <section class="bg-sand-50 py-16 dark:bg-card/40 sm:py-20">
        <Container>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <SectionHeading eyebrow="مميّزة" title="دورات مختارة لك" />
                <Link
                    :href="route('courses.index')"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800"
                >
                    كل الدورات
                    <ArrowLeft class="size-4" />
                </Link>
            </div>

            <div v-if="props.featuredCourses.length" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <CourseCard v-for="course in props.featuredCourses" :key="course.id" :course="course" />
            </div>
            <EmptyState v-else class="mt-10" title="لا توجد دورات بعد" description="سيتم عرض الدورات المميّزة هنا بعد نشرها." />
        </Container>
    </section>

    <!-- Instructors -->
    <section v-if="props.instructors.length" class="py-16 sm:py-20">
        <Container>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <SectionHeading eyebrow="فريقنا" title="نخبة من المدربين" description="خبراء متخصصون يشاركون معرفتهم وخبراتهم معك." />
                <Link
                    :href="route('instructors.index')"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800"
                >
                    كل المدربين
                    <ArrowLeft class="size-4" />
                </Link>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <InstructorCard v-for="instructor in props.instructors" :key="instructor.id" :instructor="instructor" />
            </div>
        </Container>
    </section>

    <!-- Latest courses -->
    <section class="bg-sand-50 py-16 dark:bg-card/40 sm:py-20">
        <Container>
            <SectionHeading eyebrow="جديدنا" title="أحدث الدورات" />
            <div v-if="props.latestCourses.length" class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <CourseCard v-for="course in props.latestCourses" :key="course.id" :course="course" />
            </div>
            <EmptyState v-else class="mt-10" title="لا توجد دورات بعد" description="تابعنا قريبًا لإطلاق أول البرامج التدريبية." />
        </Container>
    </section>

    <!-- CTA -->
    <section class="py-16 sm:py-20">
        <Container>
            <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 px-6 py-12 text-center sm:px-16">
                <h2 class="text-balance text-3xl font-extrabold text-white sm:text-4xl">ابدأ رحلتك التعليمية اليوم</h2>
                <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-white/75">
                    انضم إلى مجتمع جمعية رانيا التعليمي وطوّر مهاراتك مع نخبة من المدربين.
                </p>
                <Link
                    :href="route('courses.index')"
                    class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-brand-800 shadow-sm transition hover:bg-sand-100"
                >
                    تصفّح الدورات الآن
                    <ArrowLeft class="size-4" />
                </Link>
            </div>
        </Container>
    </section>
</template>
