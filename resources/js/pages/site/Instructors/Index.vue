<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import EmptyState from '@/components/site/EmptyState.vue';
import InstructorCard from '@/components/site/InstructorCard.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import SiteLayout from '@/layouts/site/SiteLayout.vue';
import type { Instructor } from '@/types/site';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: SiteLayout });

const props = defineProps<{
    instructors: Instructor[];
}>();
</script>

<template>
    <Head title="المدربون" />

    <section class="bg-hero-wash border-b border-border">
        <Container class="py-14 sm:py-16">
            <SectionHeading eyebrow="فريقنا" title="المدربون" description="نخبة من الخبراء المتخصصين يشاركون معرفتهم مع المتعلّمين." />
        </Container>
    </section>

    <Container class="py-12 sm:py-16">
        <div v-if="props.instructors.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <InstructorCard v-for="instructor in props.instructors" :key="instructor.id" :instructor="instructor" />
        </div>
        <EmptyState v-else title="لا يوجد مدربون بعد" description="سيتم عرض فريق المدربين هنا قريبًا." />
    </Container>
</template>
