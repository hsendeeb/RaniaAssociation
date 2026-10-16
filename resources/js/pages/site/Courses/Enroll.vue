<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Course } from '@/types/site';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, CalendarDays, Clock, GraduationCap, Layers, ShieldCheck, UserCog } from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    course: Course;
    defaults: { name: string; email: string; phone: string };
}>();

const form = useForm({
    name: props.defaults.name,
    email: props.defaults.email,
    phone: props.defaults.phone,
});

function submit(): void {
    form.post(route('enrollments.store', props.course.slug));
}
</script>

<template>
    <Head :title="`التسجيل في ${props.course.title}`" />

    <!-- Breadcrumb -->
    <div class="border-b border-border bg-card">
        <Container class="flex flex-wrap items-center gap-2 py-4 text-sm text-muted-foreground">
            <Link :href="route('home')" class="transition hover:text-brand-700">الرئيسية</Link>
            <span>/</span>
            <Link :href="route('courses.index')" class="transition hover:text-brand-700">الدورات</Link>
            <span>/</span>
            <Link :href="route('courses.show', props.course.slug)" class="transition hover:text-brand-700">
                {{ props.course.title }}
            </Link>
            <span>/</span>
            <span class="font-medium text-foreground">التسجيل</span>
        </Container>
    </div>

    <section class="bg-hero-wash">
        <Container class="py-12 sm:py-16">
            <h1 class="text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">التسجيل في الدورة</h1>
            <p class="mt-3 max-w-2xl text-base leading-relaxed text-muted-foreground">
                أكمل بياناتك لإرسال طلب التسجيل، وسيتم مراجعته من قِبل إدارة الجمعية والرد عليك.
            </p>
        </Container>
    </section>

    <Container class="grid gap-8 py-12 sm:py-16 lg:grid-cols-[1.5fr_1fr]">
        <!-- Form -->
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8">
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-foreground">الاسم الكامل</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-xl border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-brand-500 focus:ring-brand-500"
                        placeholder="اكتب اسمك الكامل"
                    />
                    <p v-if="form.errors.name" class="mt-1.5 text-xs text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-foreground">البريد الإلكتروني</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            dir="ltr"
                            class="w-full rounded-xl border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-brand-500 focus:ring-brand-500"
                            placeholder="you@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-destructive">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-foreground">رقم الجوال</label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            dir="ltr"
                            class="w-full rounded-xl border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-brand-500 focus:ring-brand-500"
                            placeholder="+961 3x xxx xxx"
                        />
                        <p v-if="form.errors.phone" class="mt-1.5 text-xs text-destructive">{{ form.errors.phone }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-xl border border-brand-100 bg-brand-50/60 p-4 text-sm text-brand-800">
                    <ShieldCheck class="mt-0.5 size-5 shrink-0 text-brand-600" />
                    <p>سيبدأ تسجيلك بحالة «قيد الانتظار» حتى توافق الإدارة، ويمكنك متابعة الحالة من صفحة لوحتي.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? 'جارٍ الإرسال...' : 'إرسال طلب التسجيل' }}
                    </button>
                    <Link
                        :href="route('courses.show', props.course.slug)"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-border px-5 py-3 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                    >
                        إلغاء
                    </Link>
                </div>
            </form>
        </div>

        <!-- Course summary -->
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
                <div class="relative aspect-[16/10] w-full overflow-hidden">
                    <img
                        v-if="props.course.cover_image"
                        :src="props.course.cover_image"
                        :alt="props.course.title"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900">
                        <GraduationCap class="size-16 text-white/25" />
                    </div>
                </div>

                <div class="p-6">
                    <span v-if="props.course.category" class="text-xs font-semibold text-brand-600">
                        {{ props.course.category.name }}
                    </span>
                    <h2 class="mt-1 text-lg font-bold leading-snug text-foreground">{{ props.course.title }}</h2>
                    <p v-if="props.course.summary" class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                        {{ props.course.summary }}
                    </p>

                    <dl class="mt-5 space-y-3 border-t border-border pt-5 text-sm">
                        <div v-if="props.course.instructor" class="flex items-center justify-between">
                            <dt class="inline-flex items-center gap-2 text-muted-foreground"><UserCog class="size-4" /> المدرّب</dt>
                            <dd class="font-medium text-foreground">{{ props.course.instructor.name }}</dd>
                        </div>
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
                            <dd class="font-medium text-foreground">{{ props.course.lessons_count ?? 0 }}</dd>
                        </div>
                        <div v-if="props.course.starts_at" class="flex items-center justify-between">
                            <dt class="inline-flex items-center gap-2 text-muted-foreground"><CalendarDays class="size-4" /> تاريخ البدء</dt>
                            <dd class="font-medium text-foreground" dir="ltr">{{ props.course.starts_at }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex items-center justify-between border-t border-border pt-5">
                        <span class="text-sm text-muted-foreground">الرسوم</span>
                        <span v-if="props.course.is_free" class="rounded-full bg-brand-50 px-3 py-1 text-sm font-bold text-brand-700">مجاني</span>
                        <span v-else class="text-sm font-bold text-foreground" dir="ltr">
                            <span class="text-xs font-medium text-muted-foreground">$</span>{{ props.course.price }}
                        </span>
                    </div>

                    <Link
                        :href="route('courses.show', props.course.slug)"
                        class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800"
                    >
                        تفاصيل الدورة
                        <ArrowLeft class="size-4" />
                    </Link>
                </div>
            </div>
        </aside>
    </Container>
</template>
