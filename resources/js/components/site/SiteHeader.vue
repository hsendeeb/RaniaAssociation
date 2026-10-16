<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import type { User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { GraduationCap, LayoutDashboard, Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const open = ref(false);

const navItems = [
    { label: 'الرئيسية', routeName: 'home', path: '/' },
    { label: 'الدورات', routeName: 'courses.index', path: '/courses' },
    { label: 'المدربون', routeName: 'instructors.index', path: '/instructors' },
    { label: 'من نحن', routeName: 'about', path: '/about' },
    { label: 'تواصل معنا', routeName: 'contact', path: '/contact' },
];

const currentPath = computed(() => page.url.split('?')[0]);
const user = computed(() => (page.props.auth?.user as User | null) ?? null);

function isActive(path: string): boolean {
    return path === '/' ? currentPath.value === '/' : currentPath.value.startsWith(path);
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-border/70 bg-background/80 backdrop-blur-lg">
        <Container>
            <div class="flex h-16 items-center justify-between gap-4 lg:h-20">
                <Link :href="route('home')" class="flex items-center gap-2.5" @click="open = false">
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white shadow-sm"
                    >
                        <GraduationCap class="size-5" />
                    </span>
                    <span class="flex flex-col leading-none">
                        <span class="text-base font-extrabold text-foreground">جمعية رانيا</span>
                        <span class="mt-1 text-xs text-muted-foreground">منصة تعليمية</span>
                    </span>
                </Link>

                <nav class="hidden items-center gap-1 lg:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="isActive(item.path) ? 'text-brand-700' : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-2 lg:flex">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        <LayoutDashboard class="size-4" />
                        لوحتي
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        >
                            تسجيل الدخول
                        </Link>
                    </template>
                    <Link
                        :href="route('courses.index')"
                        class="inline-flex items-center rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-800"
                    >
                        تصفّح الدورات
                    </Link>
                </div>

                <button
                    type="button"
                    class="inline-flex size-10 items-center justify-center rounded-lg border border-border text-foreground transition hover:bg-muted lg:hidden"
                    :aria-expanded="open"
                    aria-label="القائمة"
                    @click="open = !open"
                >
                    <X v-if="open" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>
        </Container>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="-translate-y-2 opacity-0"
        >
            <div v-if="open" class="border-t border-border bg-background lg:hidden">
                <Container class="py-4">
                    <nav class="flex flex-col gap-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.routeName"
                            :href="route(item.routeName)"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium transition"
                            :class="isActive(item.path) ? 'bg-brand-50 text-brand-700' : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                            @click="open = false"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="mt-3 grid gap-2">
                        <Link
                            v-if="user"
                            :href="route('dashboard')"
                            class="flex items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                            @click="open = false"
                        >
                            <LayoutDashboard class="size-4" />
                            لوحتي
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="flex items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                                @click="open = false"
                            >
                                تسجيل الدخول
                            </Link>
                            <Link
                                :href="route('register')"
                                class="flex items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                                @click="open = false"
                            >
                                حساب جديد
                            </Link>
                        </template>
                        <Link
                            :href="route('courses.index')"
                            class="flex items-center justify-center rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800"
                            @click="open = false"
                        >
                            تصفّح الدورات
                        </Link>
                    </div>
                </Container>
            </div>
        </Transition>
    </header>
</template>
