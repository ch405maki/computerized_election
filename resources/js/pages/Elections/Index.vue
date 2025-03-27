<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ElectionForm from '@/components/election/ElectionForm.vue';
import ElectionsList from '@/components/election/ElectionsList.vue';

const props = defineProps<{
    elections: Array<{
        id: number;
        name: string;
        status: string;
        start_date: string;
        end_date: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Elections', href: '/elections' },
];
</script>

<template>
    <Head title="Elections" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-8">
            <div class="grid grid-cols-1 gap-8">
                <ElectionForm />
                <ElectionsList :elections="props.elections" />
            </div>
        </div>
    </AppLayout>
</template>