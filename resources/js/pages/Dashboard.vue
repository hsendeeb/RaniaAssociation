<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import StatusBadge from '@/components/site/StatusBadge.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Enrollment, EnrollmentStats } from '@/types/site';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, CheckCircle2, Clock, GraduationCap, LayoutDashboard, LogOut, Trash2, UserCog } from 'lucide-vue-next';
import { computed } from 'vue';
import { route } from 'ziggy-js';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    enrollments: Enrollment[];
    stats: EnrollmentStats;
    isAdmin: boolean;
}>();

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? '');

const statCards = computed(() => [
    { label: 'إجمالي التسجيلات', value: props.stats.total, icon: BookOpen },
    { label: 'قيد الانتظار', value: props.stats.pending, icon: Clock },
    { label: 'دورات نشطة', value: props.stats.active, icon: GraduationCap },
    { label: 'دورات مكتملة', value: props.stats.completed, icon: CheckCircle2 },
]);

function cancel(enrollment: Enrollment): void {
    if (!window.confirm('هل أنت متأكد من إلغاء التسجيل في هذه الدورة؟')) {
        return;
    }

    router.delete(route('enrollments.destroy', enrollment.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="لوحتي" />

    <!-- Greeting -->
    <section class="bg-hero-wash border-b border-border">
        <Container class="flex flex-wrap items-end justify-between gap-4 py-12 sm:py-14">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-brand-600">لوحتي</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">مرحبًا، {{ userName }}</h1>
                <p class="mt-2 text-base text-muted-foreground">تابع دوراتك وحالة تسجيلاتك من مكان واحد.</p>
            </div>
            <Link
                v-if="props.isAdmin"
                :href="route('filament.admin.pages.dashboard')"
                class="inline-flex items-center gap-2 rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
            >
                <LayoutDashboard class="size-4" />
                لوحة الإدارة
            </Link>
        </Container>
    </section>

    <Container class="py-10 sm:py-14">
        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div v-for="stat in statCards" :key="stat.label" class="flex items-center gap-4 rounded-2xl border border-border bg-card p-5 shadow-sm">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <component :is="stat.icon" class="size-5" />
                </span>
                <div>
                    <p class="text-2xl font-extrabold text-foreground">{{ stat.value }}</p>
                    <p class="text-xs text-muted-foreground">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <!-- Enrollments -->
        <h2 class="mt-12 text-xl font-bold text-foreground">دوراتي</h2>

        <div v-if="props.enrollments.length" class="mt-6 space-y-4">
            <article
                v-for="enrollment in props.enrollments"
                :key="enrollment.id"
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
            >
                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center">
                    <!-- Thumb -->
                    <div class="h-24 w-full shrink-0 overflow-hidden rounded-xl sm:w-40">
                        <img
                            v-if="enrollment.course?.cover_image"
                            :src="enrollment.course.cover_image"
                            :alt="enrollment.course.title"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-600 to-brand-800">
                            <GraduationCap class="size-8 text-white/30" />
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <StatusBadge :status="enrollment.status" />
                            <span v-if="enrollment.course?.category" class="text-xs font-medium text-muted-foreground">
                                {{ enrollment.course.category.name }}
                            </span>
                        </div>

                        <h3 class="mt-2 text-lg font-bold leading-snug text-foreground">
                            <Link
                                v-if="enrollment.course"
                                :href="route('courses.show', enrollment.course.slug)"
                                class="transition hover:text-brand-700"
                            >
                                {{ enrollment.course.title }}
                            </Link>
                        </h3>

                        <p v-if="enrollment.course?.instructor" class="mt-1 inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                            <UserCog class="size-4 text-brand-500" />
                            {{ enrollment.course.instructor.name }}
                        </p>

                        <!-- Progress -->
                        <div class="mt-4 max-w-sm">
                            <div class="mb-1.5 flex items-center justify-between text-xs text-muted-foreground">
                                <span>نسبة الإنجاز</span>
                                <span class="font-semibold text-foreground">{{ enrollment.progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-brand-600" :style="{ width: `${enrollment.progress}%` }" />
                            </div>
                        </div>

                        <p v-if="enrollment.status.value === 'pending'" class="mt-3 text-xs text-gold-700">طلبك قيد المراجعة من قِبل الإدارة.</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-stretch">
                        <Link
                            v-if="enrollment.course && enrollment.status.value !== 'cancelled'"
                            :href="route('courses.show', enrollment.course.slug)"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800"
                        >
                            متابعة
                            <ArrowLeft class="size-4" />
                        </Link>
                        <Link
                            v-else-if="enrollment.course"
                            :href="route('enrollments.create', enrollment.course.slug)"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-border px-4 py-2.5 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                        >
                            إعادة التسجيل
                        </Link>
                        <button
                            v-if="['pending', 'active'].includes(enrollment.status.value)"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-destructive/40 hover:text-destructive"
                            @click="cancel(enrollment)"
                        >
                            <Trash2 class="size-4" />
                            إلغاء
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <EmptyState v-else class="mt-6" title="لم تسجّل في أي دورة بعد" description="تصفّح الدورات المتاحة وابدأ رحلتك التعليمية مع جمعية رانيا.">
            <Link
                :href="route('courses.index')"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-800"
            >
                تصفّح الدورات
                <ArrowLeft class="size-4" />
            </Link>
        </EmptyState>

        <!-- Logout: full width at the bottom on mobile, bottom-left on lg and up -->
        <div class="mt-12 flex border-t border-border pt-6 lg:justify-end">
            <Link
                method="post"
                :href="route('logout')"
                as="button"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 py-3 text-sm font-semibold text-muted-foreground transition hover:border-destructive/40 hover:text-destructive lg:w-auto"
            >
                <LogOut class="size-4" />
                تسجيل خروج
            </Link>
        </div>
    </Container>
</template>
