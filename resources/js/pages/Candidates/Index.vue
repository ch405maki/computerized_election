<script setup lang="ts">
import CandidateForm from '@/components/candidate/CandidateForm.vue';
import CandidatesList from '@/components/candidate/CandidatesList.vue';
import TitleHeader from '@/components/ui/title-header/header.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    candidates: Array<{
        id: number;
        candidate_code: string;
        candidate_name: string;
        candidate_party: string | null;
        candidate_picture: string | null;
        election: {
            id: number;
            name: string;
        } | null;
        position: {
            id: number;
            name: string;
        } | null;
    }>;
    positions: Array<{
        id: number;
        name: string;
    }>;
    elections: Array<{
        id: number;
        name: string;
    }>;
}>();

const page = usePage();

const userPermissions = computed(() => {
    return (page.props.auth as any)?.user?.permissions || {};
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Candidates', href: '/candidates' },
    { title: 'Current Candidates', href: '/candidates' },
];

const refreshCandidates = () => {
    router.reload({
        only: ['candidates'],
    });
};

const searchQuery = ref('');

const filteredCandidates = computed(() => {
    if (!searchQuery.value) {
        return props.candidates;
    }

    const query = searchQuery.value.toLowerCase();

    return props.candidates.filter((candidate) => {
        return (
            candidate.candidate_name?.toLowerCase().includes(query) ||
            candidate.candidate_code?.toLowerCase().includes(query) ||
            candidate.candidate_party?.toLowerCase().includes(query) ||
            candidate.position?.name.toLowerCase().includes(query) ||
            candidate.election?.name.toLowerCase().includes(query)
        );
    });
});
</script>

<template>
    <Head title="Candidates" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between gap-2">
                <TitleHeader title="Candidates List" description="Manage election candidates, their affiliations, and profiles." />
                <div class="flex items-center gap-3">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search candidates..."
                        class="w-64 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    />
                    <CandidateForm
                        :positions="positions"
                        :elections="elections"
                        :userPermissions="userPermissions"
                        @candidateCreated="refreshCandidates"
                    />
                </div>
            </div>
            <div>
                <CandidatesList :candidates="filteredCandidates" :userPermissions="userPermissions" />
            </div>
        </div>
    </AppLayout>
</template>
