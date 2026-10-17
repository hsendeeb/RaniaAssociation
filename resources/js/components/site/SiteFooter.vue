<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import { Link } from '@inertiajs/vue3';
import { Mail, MapPin, Phone } from 'lucide-vue-next';

const year = new Date().getFullYear();

const quickLinks = [
    { label: 'الرئيسية', routeName: 'home' },
    { label: 'الدورات', routeName: 'courses.index' },
    { label: 'المدربون', routeName: 'instructors.index' },
    { label: 'من نحن', routeName: 'about' },
    { label: 'تواصل معنا', routeName: 'contact' },
];
</script>

<template>
    <footer class="mt-24 border-t border-border bg-sand-50 dark:bg-card">
        <Container class="py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5">
                        <img
                            src="/images/logo.jpeg"
                            alt="جمعية رانيا"
                            class="size-10 shrink-0 rounded-xl object-cover"
                        />
                        <span class="text-lg font-extrabold text-foreground">جمعية رانيا</span>
                    </div>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-muted-foreground">
                        منصة تعليمية تابعة لجمعية رانيا، نقدّم دورات وبرامج تدريبية معتمدة تساهم في بناء المهارات ونشر المعرفة في المجتمع.
                    </p>
                </div>

                <div>
                    <h3 class="mb-4 text-sm font-bold text-foreground">روابط سريعة</h3>
                    <ul class="space-y-2.5">
                        <li v-for="link in quickLinks" :key="link.routeName">
                            <Link :href="route(link.routeName)" class="text-sm text-muted-foreground transition hover:text-brand-700">
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-4 text-sm font-bold text-foreground">تواصل معنا</h3>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li class="flex items-center gap-2.5">
                            <Mail class="size-4 text-brand-500" />
                            <span dir="ltr">info@rania-association.test</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <Phone class="size-4 text-brand-500" />
                            <span dir="ltr">+961 1 234 567</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <MapPin class="size-4 text-brand-500" />
                            بيروت، لبنان
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-border pt-6 text-sm text-muted-foreground sm:flex-row">
                <p>© {{ year }} جمعية رانيا. جميع الحقوق محفوظة.</p>
                <Link :href="route('contact')" class="transition hover:text-brand-700">سياسة الخصوصية والدعم</Link>
            </div>
        </Container>
    </footer>
</template>
