<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";

// Components
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue';
import StatsGrid from '@/components/dashboard/StatsGrid.vue';
import VoteRankingTable from '@/components/dashboard/VoteRankingTable.vue';
import RecentElectionsTable from '@/components/dashboard/RecentElectionsTable.vue';
import ParticipationChart from '@/components/dashboard/ParticipationChart.vue';
import VoteRankingChart from '@/components/dashboard/VoteRankingChart.vue';

// Props
const props = defineProps<{
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
}>();

const toast = useToast();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];
const isLoading = ref(false);
const isChartView = ref(false); // Set to false for table to be default

// Vote ranking data
interface CandidateVote {
  name: string;
  votes: number;
  image?: string;
}

interface PositionVotes {
  position: string;
  candidates: CandidateVote[];
}

const voteRanking = ref<PositionVotes[]>([]);

// Computed
const maxVotes = computed(() => {
  if (!props.participation_data.length) return 1;
  return Math.max(...props.participation_data.map(d => d.votes), 1);
});

// Methods
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const getElectionStatus = (election: { start_date: string; end_date: string }) => {
  const today = new Date();
  const startDate = new Date(election.start_date);
  const endDate = new Date(election.end_date);

  if (startDate > today) return 'upcoming';
  if (endDate < today) return 'completed';
  return 'active';
};

const fetchVoteRanking = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/vote-ranking');

    if (response.data.rankings && Array.isArray(response.data.rankings)) {
      const groupedData: Record<string, CandidateVote[]> = {};

      response.data.rankings.forEach((item: any) => {
        if (!groupedData[item.position]) {
          groupedData[item.position] = [];
        }
        groupedData[item.position].push({
          name: item.candidate,
          votes: item.votes,
          image: item.image || undefined
        });
      });

      voteRanking.value = Object.entries(groupedData).map(([position, candidates]) => ({
        position,
        candidates: candidates.sort((a, b) => b.votes - a.votes)
      }));
    } else {
      console.warn('Unexpected response format:', response.data);
      voteRanking.value = [];
    }
  } catch (error) {
    toast.error('Failed to load vote rankings');
    console.error('Error fetching vote rankings:', error);
    voteRanking.value = [];
  } finally {
    isLoading.value = false;
  }
};

const refreshData = async () => {
  await fetchVoteRanking();
  window.location.reload();
  toast.success('Dashboard updated!');
};

// Toggle between chart and table view
const toggleView = () => {
  isChartView.value = !isChartView.value;
};

onMounted(fetchVoteRanking);
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="space-y-6">
        <!-- Updated: Pass toggle state & event to DashboardHeader -->
        <DashboardHeader
          :isLoading="isLoading"
          :isChartView="isChartView"
          @refresh="refreshData"
          @toggleView="toggleView"
        />

        <StatsGrid :stats="stats" />

        <!-- Conditional Rendering: Table (Default) or Chart -->
        <VoteRankingTable v-if="!isChartView" :voteRanking="voteRanking" :isLoading="isLoading" />
        <VoteRankingChart v-else :isChartView="isChartView" :voteRanking="voteRanking" :isLoading="isLoading" />

        <RecentElectionsTable
          :elections="recent_elections"
          :getElectionStatus="getElectionStatus"
          :formatDate="formatDate"
        />

        <ParticipationChart
          :participationData="participation_data"
          :maxVotes="maxVotes"
          :formatDate="formatDate"
        />
      </div>
    </div>
  </AppLayout>
</template>
