<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Clock, Mail, MapPin, Phone, Send } from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineOptions({ layout: SiteLayout });

const form = useForm({
    name: '',
    email: '',
    phone: '',
    message: '',
});

function submit(): void {
    form.post(route('contact.send'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

const contactItems = [
    { label: 'البريد الإلكتروني', value: 'info@rania-association.test', icon: Mail, dir: 'ltr' as const },
    { label: 'الهاتف', value: '+961 1 234 567', icon: Phone, dir: 'ltr' as const },
    { label: 'العنوان', value: 'بيروت، لبنان', icon: MapPin, dir: 'rtl' as const },
    { label: 'أوقات العمل', value: 'الأحد – الخميس، 9ص – 5م', icon: Clock, dir: 'rtl' as const },
];
</script>

<template>
    <Head title="تواصل معنا" />

    <section class="bg-hero-wash border-b border-border">
        <Container class="py-14 sm:py-16">
            <SectionHeading eyebrow="تواصل" title="تواصل معنا" description="لديك سؤال أو ترغب بالتسجيل في دورة؟ نحن هنا لمساعدتك." />
        </Container>
    </section>

    <Container class="grid gap-12 py-12 sm:py-16 lg:grid-cols-[1.4fr_1fr]">
        <!-- Form -->
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8">
            <form class="space-y-5" @submit.prevent="submit">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-foreground">الاسم الكامل</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-xl border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-brand-500 focus:ring-brand-500"
                            placeholder="اكتب اسمك"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>
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
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-foreground">
                        رقم الجوال <span class="text-muted-foreground">(اختياري)</span>
                    </label>
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

                <div>
                    <label for="message" class="mb-1.5 block text-sm font-medium text-foreground">رسالتك</label>
                    <textarea
                        id="message"
                        v-model="form.message"
                        rows="5"
                        class="w-full rounded-xl border-border bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:border-brand-500 focus:ring-brand-500"
                        placeholder="كيف يمكننا مساعدتك؟"
                    />
                    <p v-if="form.errors.message" class="mt-1.5 text-xs text-destructive">{{ form.errors.message }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Send class="size-4" />
                    {{ form.processing ? 'جارٍ الإرسال...' : 'إرسال الرسالة' }}
                </button>
            </form>
        </div>

        <!-- Info -->
        <div class="space-y-4">
            <div v-for="item in contactItems" :key="item.label" class="flex items-start gap-4 rounded-2xl border border-border bg-card p-5 shadow-sm">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                    <component :is="item.icon" class="size-5" />
                </span>
                <div>
                    <p class="text-sm font-bold text-foreground">{{ item.label }}</p>
                    <p class="mt-0.5 text-sm text-muted-foreground" :dir="item.dir">{{ item.value }}</p>
                </div>
            </div>
        </div>
    </Container>
</template>
