<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ElectionForm from '@/components/election/ElectionForm.vue';
import ElectionsList from '@/components/election/ElectionsList.vue';
import TitleHeader from '@/components/ui/title-header/header.vue';

type ElectionStatus = 'active' | 'upcoming' | 'completed';

interface Election {
  id: number;
  name: string;
  status: ElectionStatus;
  start_date: string;
  end_date: string;
}

const props = defineProps<{
    elections: Election[]; 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Elections', href: '/elections' },
];

const page = usePage();

// Parse and format all permissions into a clean boolean object once
const userPermissions = computed<Record<string, boolean>>(() => {
    const user: any = page.props.auth?.user;
    if (!user || !user.permissions) return {};

    let rawPermissions = user.permissions;

    if (typeof rawPermissions === 'string') {
        try {
            rawPermissions = JSON.parse(rawPermissions);
        } catch (e) {
            return {};
        }
    }

    const formattedPermissions: Record<string, boolean> = {};
    if (rawPermissions && typeof rawPermissions === 'object') {
        for (const [key, value] of Object.entries(rawPermissions)) {
            formattedPermissions[key] = value === true || value === 'true' || value === 1;
        }
    }

    return formattedPermissions;
});
</script>

<template>
    <Head title="Elections" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <TitleHeader 
                    title="Election Management" 
                    description="Configure election cycles, dates, and active status." 
            />
            <div class="flex justify-end gap-2 items-center">
                <ElectionForm :can-create="userPermissions.createElection" />
            </div>
            <div>
                <ElectionsList 
                    :elections="elections" 
                    :can-edit="userPermissions.editElection"
                    :can-delete="userPermissions.deleteElection"
                />
            </div>
        </div>
    </AppLayout>
</template>