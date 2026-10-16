<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, HeartHandshake, Lightbulb, ShieldCheck, Target, Users } from 'lucide-vue-next';

defineOptions({ layout: SiteLayout });

const values = [
    { title: 'المعرفة للجميع', description: 'نؤمن بأن التعلّم حقّ متاح لكل فرد في المجتمع.', icon: BookOpen },
    { title: 'الجودة والإتقان', description: 'نقدّم محتوى معتمدًا بإشراف خبراء متخصصين.', icon: ShieldCheck },
    { title: 'روح المجتمع', description: 'نبني بيئة تعليمية داعمة ومحفّزة على المشاركة.', icon: HeartHandshake },
    { title: 'الابتكار', description: 'نستخدم أساليب حديثة في التعليم والتدريب.', icon: Lightbulb },
];
</script>

<template>
    <Head title="من نحن" />

    <section class="bg-hero-wash border-b border-border">
        <Container class="py-16 sm:py-20">
            <SectionHeading
                align="center"
                eyebrow="من نحن"
                title="جمعية رانيا التعليمية"
                description="منصة تعليمية تابعة لجمعية رانيا، نعمل على نشر المعرفة وتمكين الأفراد عبر برامج ودورات تدريبية معتمدة."
            />
        </Container>
    </section>

    <Container class="py-16 sm:py-20">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-foreground">رسالتنا</h2>
                <p class="mt-4 text-base leading-loose text-muted-foreground">
                    نسعى في جمعية رانيا إلى تقديم تعليم نوعي يواكب احتياجات المجتمع، من خلال برامج تدريبية مصمّمة بعناية تجمع بين المعرفة النظرية
                    والتطبيق العملي، وتفتح آفاقًا جديدة للمتعلّمين في مختلف المجالات.
                </p>
                <p class="mt-4 text-base leading-loose text-muted-foreground">
                    نرافق المتعلّم من لحظة التسجيل حتى إتمام البرنامج، مع متابعة مستمرة ودعم يضمن تجربة تعليمية ناجحة ومثمرة.
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex items-start gap-4 rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <Target class="size-5" />
                    </span>
                    <div>
                        <h3 class="font-bold text-foreground">رؤيتنا</h3>
                        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">أن نكون المرجع التعليمي الأول في مجتمعنا.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <Users class="size-5" />
                    </span>
                    <div>
                        <h3 class="font-bold text-foreground">قيمنا</h3>
                        <p class="mt-1 text-sm leading-relaxed text-muted-foreground">الإتقان، والمشاركة، والتمكين المستمر.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-20">
            <SectionHeading align="center" eyebrow="قيمنا" title="ما يميّزنا" />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="value in values" :key="value.title" class="rounded-2xl border border-border bg-card p-6 text-center shadow-sm">
                    <span
                        class="mx-auto flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white"
                    >
                        <component :is="value.icon" class="size-6" />
                    </span>
                    <h3 class="mt-4 font-bold text-foreground">{{ value.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ value.description }}</p>
                </div>
            </div>
        </div>

        <div class="mt-20 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900 px-6 py-12 text-center sm:px-16">
            <h2 class="text-balance text-3xl font-extrabold text-white sm:text-4xl">هل أنت جاهز للبدء؟</h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-white/75">تصفّح دوراتنا واختر ما يناسب طموحك.</p>
            <Link
                :href="route('courses.index')"
                class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-brand-800 shadow-sm transition hover:bg-sand-100"
            >
                تصفّح الدورات
                <ArrowLeft class="size-4" />
            </Link>
        </div>
    </Container>
</template>
