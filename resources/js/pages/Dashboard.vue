<script setup lang="ts">
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue';
import LogChart from '@/components/dashboard/LogChart.vue';
import ParticipationChart from '@/components/dashboard/ParticipationChart.vue';
import RecentElectionsTable from '@/components/dashboard/RecentElectionsTable.vue';
import StatsGrid from '@/components/dashboard/StatsGrid.vue';
import VoteRankingChart from '@/components/dashboard/VoteRankingChart.vue';
import VoteRankingTable from '@/components/dashboard/VoteRankingTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useToast } from 'vue-toastification';

interface CandidateVote {
    name: string;
    party: string;
    votes: number;
    image?: string;
}

interface PositionVotes {
    position: string;
    candidates: CandidateVote[];
}

interface VoteThreshold {
    percentage: number;
    required_votes: number;
}

const props = defineProps<{
    vote_threshold?: VoteThreshold | null;
    stats: {
        total_elections: number;
        active_elections: number;
        total_voters: number;
        votes_today: number;
        participation_rate: number;
    };
    recent_elections: Array<{
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        votes_count: number;
    }>;
    participation_data: Array<{
        date: string;
        votes: number;
    }>;
    logs: Array<{
        id: number;
        action: string;
        created_at: string;
        user_name: string | null;
        student_number: string | null;
    }>;
}>();

const toast = useToast();
const page = usePage();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

const isLoading = ref(false);
const showRanking = ref(false);
const showChart = ref(false);
const voteRanking = ref<PositionVotes[]>([]);
let refreshInterval: number | null = null;

const hasPermission = (permission: string) => {
    const user = (page.props as any).auth?.user;
    return !!(user?.permissions && user.permissions[permission]);
};

const canShowRanking = computed(() => hasPermission('showRanking'));
const canShowChart = computed(() => hasPermission('showChart'));
const canShowCandidateNames = computed(() => hasPermission('showCandidateNames'));

const maxVotes = computed(() => (props.participation_data.length ? Math.max(...props.participation_data.map((d) => d.votes)) : 1));

const formatDate = (dateString: string) => new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

const getElectionStatus = (election: { start_date: string; end_date: string }) => {
    const today = new Date();
    if (new Date(election.start_date) > today) return 'upcoming';
    if (new Date(election.end_date) < today) return 'completed';
    return 'active';
};

const fetchVoteRanking = async () => {
    if (!canShowRanking.value && !canShowChart.value) return;

    isLoading.value = true;
    try {
        const { data } = await axios.get('/api/vote-ranking');

        if (Array.isArray(data.rankings)) {
            const grouped = data.rankings.reduce((acc: Record<string, CandidateVote[]>, item: any) => {
                const isAbstain = item.candidate?.trim().toLowerCase() === 'abstain';
                const displayName = isAbstain ? 'Abstain' : (item.candidate || 'Unknown Candidate');

                (acc[item.position] ||= []).push({
                    name: displayName,
                    party: item.party,
                    votes: item.votes,
                    image: item.image || undefined,
                });
                return acc;
            }, {});

            voteRanking.value = Object.entries(grouped).map(([position, candidates]) => ({
                position,
                candidates: (candidates as CandidateVote[]).sort((a, b) => b.votes - a.votes),
            }));
        } else {
            voteRanking.value = [];
        }
    } catch (error) {
        toast.error('Failed to load vote rankings');
        voteRanking.value = [];
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchVoteRanking();
    refreshInterval = window.setInterval(fetchVoteRanking, 30000);
});

onUnmounted(() => refreshInterval && clearInterval(refreshInterval));

const refreshData = async () => {
    await fetchVoteRanking();
    window.location.reload();
    toast.success('Dashboard updated!');
};

const toggleRanking = () => (showRanking.value = !showRanking.value);
const toggleChart = () => (showChart.value = !showChart.value);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <DashboardHeader
                    :isLoading="isLoading"
                    :showRanking="showRanking"
                    :showChart="showChart"
                    :canShowRanking="canShowRanking"
                    :canShowChart="canShowChart"
                    @refresh="refreshData"
                    @toggleRanking="toggleRanking"
                    @toggleChart="toggleChart"
                />

                <StatsGrid :stats="stats" />

                <VoteRankingTable v-if="showRanking && canShowRanking" :voteRanking="voteRanking" :isLoading="isLoading" :canShowCandidateNames="canShowCandidateNames"/>

                <template v-if="showChart && canShowChart">
                    <VoteRankingChart
                        :voteRanking="voteRanking"
                        :isLoading="isLoading"
                        :voteThreshold="vote_threshold"
                        :canShowCandidateNames="canShowCandidateNames"
                    />

                    <ParticipationChart
                        :participationData="participation_data"
                        :maxVotes="maxVotes"
                        :formatDate="formatDate"
                        :isLoading="isLoading"
                    />
                </template>

                <RecentElectionsTable :elections="recent_elections" :getElectionStatus="getElectionStatus" :formatDate="formatDate" />

                <LogChart :logs="logs" />
            </div>
        </div>
    </AppLayout>
</template>