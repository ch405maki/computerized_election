<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { RefreshCw, Plus, Users, Settings, Eye, Layers, Activity, BarChart2 } from "lucide-vue-next";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '../components/ui/badge'
import { useToast } from "vue-toastification";

// Define props
const props = defineProps<{
  stats: {
    total_elections: number
    active_elections: number
    total_voters: number
    votes_today: number
    participation_rate: number
  }
  recent_elections: Array<{
    id: number
    name: string
    start_date: string
    end_date: string
    votes_count: number
  }>
  participation_data: Array<{
    date: string
    votes: number
  }>
}>();

const toast = useToast();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];
const isLoading = ref(false); // Now using server-side loading

// Compute max votes for chart scaling
const maxVotes = computed(() => {
  if (!props.participation_data.length) return 1;
  return Math.max(...props.participation_data.map(d => d.votes), 1);
});

const quickActions = [
  { icon: Plus, title: 'New Election', link: '/elections/create' },
  { icon: Users, title: 'Manage Voters', link: '/voters' },
  { icon: Settings, title: 'Election Settings', link: '/settings' },
];

// Format date to short form
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const getElectionStatus = (election: {start_date: string, end_date: string}) => {
  const today = new Date();
  const startDate = new Date(election.start_date);
  const endDate = new Date(election.end_date);
  
  if (startDate > today) return 'upcoming';
  if (endDate < today) return 'completed';
  return 'active';
};

// Vote ranking data
const voteRanking = ref<PositionVotes[]>([]);

interface CandidateVote {
  name: string;
  votes: number;
  image?: string;  // Optional image property
}

interface PositionVotes {
  position: string;
  candidates: CandidateVote[];
}

// Fetch vote ranking data
const fetchVoteRanking = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/vote-ranking');
    
    // Transform the flat API response into grouped position data
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

      // Convert to the expected array format
      voteRanking.value = Object.entries(groupedData).map(([position, candidates]) => ({
        position,
        candidates: candidates.sort((a, b) => b.votes - a.votes) // Sort by votes descending
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

// Fetch data on component mount
onMounted(fetchVoteRanking);

// Refresh function
const refreshData = async () => {
  await fetchVoteRanking();
  window.location.reload();
  toast.success("Vote rankings updated!");
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold">Election Dashboard</h1>
                    <div class="flex gap-2">
                        <Button variant="outline" @click="refreshData" :disabled="isLoading">
                            <RefreshCw class="h-4 w-4 mr-2" :class="{'animate-spin': isLoading}" />
                            Refresh
                        </Button>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium">Total Elections</CardTitle>
                            <Layers class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.total_elections }}</div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.active_elections }} currently active
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium">Active Elections</CardTitle>
                            <Activity class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.active_elections }}</div>
                            <p class="text-xs text-muted-foreground">Currently running</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium">Registered Voters</CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.total_voters }}</div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.participation_rate }}% participation
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium">Votes Today</CardTitle>
                            <BarChart2 class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.votes_today }}</div>
                            <p class="text-xs text-muted-foreground">Recent activity</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Quick Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                    </CardHeader>
                    <CardContent class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <Button 
                            v-for="action in quickActions" 
                            :key="action.title"
                            variant="outline"
                            class="flex flex-col items-center justify-center h-32 gap-2"
                            as-child
                        >
                            <router-link :to="action.link">
                                <component :is="action.icon" class="h-6 w-6" />
                                <span>{{ action.title }}</span>
                            </router-link>
                        </Button>
                    </CardContent>
                </Card>

                <!-- Vote Ranking Table -->
<Card>
    <CardHeader>
        <CardTitle>Vote Ranking</CardTitle>
    </CardHeader>
    <CardContent>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Position</TableHead>
                    <TableHead>Candidate</TableHead>
                    <TableHead class="text-right">Votes</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="voteRanking.length > 0">
                    <template v-for="group in voteRanking" :key="group.position">
                        <TableRow>
                            <TableCell class="font-bold" colspan="3">{{ group.position }}</TableCell>
                        </TableRow>
                        <TableRow v-for="(candidate, index) in group.candidates" :key="candidate.name">
                            <TableCell></TableCell>
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-3">
                                    <img 
                                        v-if="candidate.image" 
                                        :src="`/storage/${candidate.image}`"  
                                        class="h-8 w-8 rounded-full object-cover"
                                        :alt="candidate.name"
                                    >
                                    <span>{{ index + 1 }}. {{ candidate.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">{{ candidate.votes }}</TableCell>
                        </TableRow>
                    </template>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell colspan="3" class="text-center py-8 text-muted-foreground">
                            {{ isLoading ? 'Loading vote rankings...' : 'No vote ranking data available' }}
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </CardContent>
</Card>
                <!-- Recent Elections Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Elections</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Election</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Votes</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="election in recent_elections" :key="election.id">
                                    <TableCell class="font-medium">{{ election.name }}</TableCell>
                                    <TableCell>
                                        <Badge 
                                            :variant="getElectionStatus(election) === 'active' ? 'default' : 
                                                    getElectionStatus(election) === 'upcoming' ? 'secondary' : 'outline'"
                                        >
                                            {{ getElectionStatus(election) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{ formatDate(election.start_date) }} to {{ formatDate(election.end_date) }}</TableCell>
                                    <TableCell>{{ election.votes_count }}</TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="ghost" size="sm" as-child>
                                            <router-link :to="`/elections/${election.id}`">
                                                <Eye class="h-4 w-4 mr-1" />
                                                View
                                            </router-link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Voter Participation Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Voter Participation</CardTitle>
                    </CardHeader>
                    <CardContent class="h-[300px]">
                        <div v-if="participation_data.length" class="h-full flex items-end gap-2">
                            <div 
                                v-for="day in participation_data" 
                                :key="day.date"
                                class="flex-1 flex flex-col items-center"
                            >
                                <div 
                                    class="w-full bg-primary rounded-t-sm transition-all"
                                    :style="{ height: `${(day.votes / maxVotes) * 100}%` }"
                                />
                                <span class="text-xs mt-1">{{ formatDate(day.date) }}</span>
                                <span class="text-xs font-medium mt-1">{{ day.votes }}</span>
                            </div>
                        </div>
                        <div v-else class="h-full flex items-center justify-center">
                            <p class="text-muted-foreground">No participation data available</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>