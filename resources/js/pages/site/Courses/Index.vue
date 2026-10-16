<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import CourseCard from '@/components/site/CourseCard.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import Pagination from '@/components/site/Pagination.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Category, Course, Paginated } from '@/types/site';
import { Head, Link, router } from '@inertiajs/vue3';
import { RotateCcw, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { route } from 'ziggy-js';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    courses: Paginated<Course>;
    categories: Category[];
    levels: { value: string; label: string }[];
    filters: { category: string | null; level: string | null; q: string | null };
}>();

const search = ref(props.filters.q ?? '');
const category = ref(props.filters.category ?? '');
const level = ref(props.filters.level ?? '');

function applyFilters(): void {
    router.get(
        route('courses.index'),
        {
            category: category.value || undefined,
            level: level.value || undefined,
            q: search.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function selectCategory(slug: string | null): void {
    category.value = slug ?? '';
    applyFilters();
}

function resetFilters(): void {
    search.value = '';
    category.value = '';
    level.value = '';
    router.get(route('courses.index'), {}, { preserveState: true, preserveScroll: true, replace: true });
}

let debounce: number | undefined;
watch(search, () => {
    window.clearTimeout(debounce);
    debounce = window.setTimeout(applyFilters, 350);
});

const hasFilters = () => Boolean(search.value || category.value || level.value);
</script>

<template>
    <Head title="الدورات" />

    <section class="bg-hero-wash border-b border-border">
        <Container class="py-14 sm:py-16">
            <SectionHeading eyebrow="البرامج التدريبية" title="الدورات" description="تصفّح جميع الدورات المنشورة وابحث عن ما يناسبك." />
        </Container>
    </section>

    <Container class="py-10 sm:py-14">
        <!-- Filters -->
        <div class="rounded-2xl border border-border bg-card p-4 shadow-sm sm:p-6">
            <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                <label class="relative block">
                    <Search class="pointer-events-none absolute end-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="ابحث عن دورة..."
                        class="w-full rounded-xl border-border bg-background px-4 py-2.5 pe-10 text-sm text-foreground placeholder:text-muted-foreground focus:border-brand-500 focus:ring-brand-500"
                    />
                </label>

                <div class="flex items-center gap-3">
                    <select
                        v-model="level"
                        class="rounded-xl border-border bg-background px-3 py-2.5 text-sm text-foreground focus:border-brand-500 focus:ring-brand-500"
                        @change="applyFilters"
                    >
                        <option value="">كل المستويات</option>
                        <option v-for="item in props.levels" :key="item.value" :value="item.value">{{ item.label }}</option>
                    </select>

                    <button
                        v-if="hasFilters()"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-border px-3 py-2.5 text-sm font-medium text-muted-foreground transition hover:border-brand-300 hover:text-brand-700"
                        @click="resetFilters"
                    >
                        <RotateCcw class="size-4" />
                        إعادة
                    </button>
                </div>
            </div>

            <div v-if="props.categories.length" class="mt-4 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full px-3.5 py-1.5 text-sm font-medium transition"
                    :class="category === '' ? 'bg-brand-600 text-white' : 'bg-muted text-muted-foreground hover:text-foreground'"
                    @click="selectCategory(null)"
                >
                    الكل
                </button>
                <button
                    v-for="item in props.categories"
                    :key="item.id"
                    type="button"
                    class="rounded-full px-3.5 py-1.5 text-sm font-medium transition"
                    :class="category === item.slug ? 'bg-brand-600 text-white' : 'bg-muted text-muted-foreground hover:text-foreground'"
                    @click="selectCategory(item.slug)"
                >
                    {{ item.name }}
                </button>
            </div>
        </div>

        <!-- Results -->
        <p class="mt-8 text-sm text-muted-foreground">
            عرض
            <span class="font-semibold text-foreground">{{ props.courses.meta.from ?? 0 }}</span>
            –
            <span class="font-semibold text-foreground">{{ props.courses.meta.to ?? 0 }}</span>
            من
            <span class="font-semibold text-foreground">{{ props.courses.meta.total }}</span>
            دورة
        </p>

        <div v-if="props.courses.data.length" class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <CourseCard v-for="course in props.courses.data" :key="course.id" :course="course" />
        </div>
        <EmptyState v-else class="mt-6" title="لا توجد دورات مطابقة" description="لم نجد دورات تطابق معايير البحث. جرّب تعديل الفلاتر.">
            <button
                type="button"
                class="inline-flex items-center rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-800"
                @click="resetFilters"
            >
                إعادة ضبط الفلاتر
            </button>
        </EmptyState>

        <div v-if="props.courses.data.length" class="mt-12">
            <Pagination :links="props.courses.meta.links" />
        </div>

        <div class="mt-12 text-center">
            <Link :href="route('home')" class="text-sm font-medium text-brand-700 hover:text-brand-800">← العودة للرئيسية</Link>
        </div>
    </Container>
</template>
