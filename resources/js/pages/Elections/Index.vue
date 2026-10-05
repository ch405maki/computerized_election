<script setup lang="ts">
import ElectionForm from '@/components/election/ElectionForm.vue';
import ElectionsList from '@/components/election/ElectionsList.vue';
import { Button } from '@/components/ui/button';
import TitleHeader from '@/components/ui/title-header/header.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type ElectionStatus = 'active' | 'upcoming' | 'completed' | 'closed';

interface Election {
    id: number;
    name: string;
    status: ElectionStatus;
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    elections: Election[];
    statusCounts?: Record<string, number>;
}>();

type Filter = 'all' | ElectionStatus;

const statusOptions: Filter[] = ['all', 'upcoming', 'active', 'closed', 'completed'];

const initialParam = typeof window !== 'undefined' ? new URLSearchParams(window.location.search).get('status') : null;
const selectedStatus = ref<Filter>(statusOptions.includes(initialParam as Filter) ? (initialParam as Filter) : 'all');

const counts = computed<Record<string, number>>(
    () =>
        props.statusCounts ?? {
            all: props.elections.length,
            upcoming: props.elections.filter((e) => e.status === 'upcoming').length,
            active: props.elections.filter((e) => e.status === 'active').length,
            closed: props.elections.filter((e) => e.status === 'closed').length,
            completed: props.elections.filter((e) => e.status === 'completed').length,
        },
);

const filteredElections = computed(() =>
    selectedStatus.value === 'all' ? props.elections : props.elections.filter((e) => e.status === selectedStatus.value),
);

function selectStatus(status: Filter) {
    selectedStatus.value = status;
    const url = new URL(window.location.href);
    url.searchParams.set('status', status);
    window.history.replaceState({}, '', url);
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Elections', href: '/elections' }];

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
            <TitleHeader title="Election Management" description="Configure election cycles, dates, and active status." />
            <div class="flex items-center justify-end gap-2">
                <Button
                    v-for="status in statusOptions"
                    :key="status"
                    :variant="selectedStatus === status ? 'default' : 'outline'"
                    size="sm"
                    class="capitalize"
                    @click="selectStatus(status)"
                >
                    {{ status }} ({{ counts[status] ?? 0 }})
                </Button>
                <ElectionForm :can-create="userPermissions.createElection" />
            </div>
            <div>
                <ElectionsList
                    :elections="filteredElections"
                    :active-filter="selectedStatus"
                    :can-edit="userPermissions.editElection"
                    :can-delete="userPermissions.deleteElection"
                />
            </div>
        </div>
    </AppLayout>
</template>
