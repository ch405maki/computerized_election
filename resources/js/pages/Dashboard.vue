<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";

// Components
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue';
import StatsGrid from '@/components/dashboard/StatsGrid.vue';
import VoteRankingTable from '@/components/dashboard/VoteRankingTable.vue';
import RecentElectionsTable from '@/components/dashboard/RecentElectionsTable.vue';
import ParticipationChart from '@/components/dashboard/ParticipationChart.vue';
import VoteRankingChart from '@/components/dashboard/VoteRankingChart.vue';
import LogChart from '@/components/dashboard/LogChart.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";

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
  logs: Array<{
        id: number;
        action: string;
        created_at: string;
        user_name: string | null;
        voter_name: string | null;
    }>;
}>();

const toast = useToast();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];
const isLoading = ref(false);
const isChartView = ref(false); // Set to false for table to be default
const voteRanking = ref<PositionVotes[]>([]);

let refreshInterval: number | null = null;

// Vote ranking data
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

// Function to fetch data
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
          party: item.party,
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
    toast.error("Failed to load vote rankings");
    console.error("Error fetching vote rankings:", error);
    voteRanking.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Auto-refresh every 30 seconds
onMounted(() => {
  fetchVoteRanking();
  refreshInterval = setInterval(fetchVoteRanking, 30000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});

const refreshData = async () => {
  await fetchVoteRanking();
  window.location.reload();
  toast.success('Dashboard updated!');
};

// Toggle between chart and table view
const toggleView = () => {
  isChartView.value = !isChartView.value;
};

const showCharts = ref(false); 

const toggleCharts = () => {
  showCharts.value = !showCharts.value;
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
          :showCharts="showCharts"
          @refresh="refreshData"
          @toggleView="toggleView"
          @toggleCharts="toggleCharts"
        />

        <StatsGrid :stats="stats" />

        <!-- Add Togle Button to show unshow charts -->
        <!-- Conditional Rendering: Table (Default) or Chart -->
        <template v-if="showCharts">
          <!-- Conditional Rendering: Table (Default) or Chart -->
          <VoteRankingTable
            v-if="!isChartView"
            :voteRanking="voteRanking"
            :isLoading="isLoading"
          />
          <VoteRankingChart
            v-else
            :voteRanking="voteRanking"
            :isLoading="isLoading"
          />
        </template>

        <RecentElectionsTable
          :elections="recent_elections"
          :getElectionStatus="getElectionStatus"
          :formatDate="formatDate"
        />

        <LogChart :logs="logs" />

      </div>
    </div>
  </AppLayout>
</template>
