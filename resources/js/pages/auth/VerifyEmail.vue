<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout title="التحقق من البريد الإلكتروني" description="يرجى التحقق من بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه إليك.">
        <Head title="التحقق من البريد الإلكتروني" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            تم إرسال رابط تحقق جديد إلى البريد الإلكتروني الذي زودتنا به عند التسجيل.
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-center">
            <Button :disabled="form.processing" variant="secondary">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                إعادة إرسال رابط التحقق
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> تسجيل الخروج </TextLink>
        </form>
    </AuthLayout>
</template>
