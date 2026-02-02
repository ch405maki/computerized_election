<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ElectionForm from '@/components/election/ElectionForm.vue';
import ElectionsList from '@/components/election/ElectionsList.vue';
import TitleHeader from '@/components/ui/title-header/header.vue';

// Define the election status type to match your other components
type ElectionStatus = 'active' | 'upcoming' | 'completed';

interface Election {
  id: number;
  name: string;
  status: ElectionStatus;
  start_date: string;
  end_date: string;
}

const props = defineProps<{
    elections: Election[]; // Use the properly typed interface
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Elections', href: '/elections' },
];
</script>

<template>
    <Head title="Elections" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex justify-end gap-2 items-center">
                <TitleHeader 
                    title="Election Management" 
                    description="Configure election cycles, dates, and active status." 
                />
                <ElectionForm />
            </div>
            <div>
                <ElectionsList :elections="elections" />
            </div>
        </div>
    </AppLayout>
</template>