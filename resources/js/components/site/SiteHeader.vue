<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, LayoutDashboard, LogOut, Menu, X } from 'lucide-vue-next';
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

const { getInitials } = useInitials();

function isActive(path: string): boolean {
    return path === '/' ? currentPath.value === '/' : currentPath.value.startsWith(path);
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-border/70 bg-background/80 backdrop-blur-lg">
        <Container>
            <div class="flex h-16 items-center justify-between gap-4 lg:h-20">
                <Link :href="route('home')" class="flex items-center gap-2.5" @click="open = false">
                    <img
                        src="/images/logo.jpeg"
                        alt="جمعية رانيا"
                        class="size-10 shrink-0 rounded-xl object-cover"
                    />
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
                    <DropdownMenu v-if="user">
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full border border-border bg-card py-1 pe-3 ps-1 text-sm font-medium text-foreground transition hover:border-brand-300 focus-visible:outline-none"
                            >
                                <Avatar class="size-8 shrink-0 overflow-hidden rounded-full">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="bg-brand-100 text-xs font-semibold text-brand-700">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span class="max-w-32 truncate">{{ user.name }}</span>
                                <ChevronDown class="size-4 text-muted-foreground" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-52">
                            <DropdownMenuItem :as-child="true">
                                <Link :href="route('dashboard')" class="flex w-full items-center gap-2">
                                    <LayoutDashboard class="size-4" />
                                    لوحتي
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem :as-child="true">
                                <Link method="post" :href="route('logout')" as="button" class="flex w-full items-center gap-2">
                                    <LogOut class="size-4" />
                                    تسجيل خروج
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Link
                        v-else
                        :href="route('login')"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        تسجيل الدخول
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
                        <template v-if="user">
                            <div class="flex items-center gap-3 rounded-lg border border-border bg-card px-3 py-2.5">
                                <Avatar class="size-9 shrink-0 overflow-hidden rounded-full">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="bg-brand-100 text-xs font-semibold text-brand-700">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-foreground">{{ user.name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">{{ user.email }}</p>
                                </div>
                            </div>
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-foreground transition hover:border-brand-300 hover:text-brand-700"
                                @click="open = false"
                            >
                                <LayoutDashboard class="size-4" />
                                لوحتي
                            </Link>
                        </template>
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
                            v-if="user"
                            method="post"
                            :href="route('logout')"
                            as="button"
                            class="flex items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-destructive/40 hover:text-destructive"
                            @click="open = false"
                        >
                            <LogOut class="size-4" />
                            تسجيل خروج
                        </Link>
                    </div>
                </Container>
            </div>
        </Transition>
    </header>
</template>
