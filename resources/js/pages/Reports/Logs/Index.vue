<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import TitleHeader from '@/components/ui/title-header/header.vue';
import YearLevelVotingChart from '@/pages/Reports/Logs/YearLevelVotingChart.vue';

const props = defineProps<{
    logs: Array<{
        id: number;
        action: string;
        created_at: string;
        user_name: string | null;
        voter_name: string | null;
    }>;
    // Receive the new props from the controller
    currentElectionId: number | null;
    canViewChart: boolean;
    electionStatus: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: '/reports' },
    { title: 'Voter Turnout', href: '/reports/log' },
];

// const getActorName = (log: typeof props.logs[0]) => {
//     return log.user_name ?? log.voter_name ?? 'System';
// };

const votingData = ref([]);
const isLoading = ref(false);

const fetchTurnoutData = async () => {
    // Only fetch if we have an election ID and the backend gave us permission
    if (!props.canViewChart || !props.currentElectionId) return;

    isLoading.value = true;
    try {
        const response = await axios.get(`/reports/log/${props.currentElectionId}/turnout-by-year`);
        votingData.value = response.data;
    } catch (error) {
        console.error('Error fetching turnout data:', error);
        votingData.value = []; 
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchTurnoutData();
});
</script>

<template>
    <Head title="Voter Turnout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <TitleHeader 
                title="Voter Turnout" 
                description="Displays graph info of Voted Students per Year Level" 
            />
            
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                
                <div v-if="canViewChart">
                    <YearLevelVotingChart 
                        :votingData="votingData" 
                        :isLoading="isLoading" 
                    />
                </div>

                <div v-else class="flex flex-col items-center justify-center h-96 text-center p-6 bg-card">
                    <h3 class="text-lg font-semibold mb-2">Chart Unavailable</h3>
                    <p class="text-muted-foreground">
                        Turnout data is only available for <strong>active</strong> or <strong>completed</strong> elections that have recorded votes.
                        <br>
                        Current Status: <span class="capitalize">{{ electionStatus }}</span>
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>