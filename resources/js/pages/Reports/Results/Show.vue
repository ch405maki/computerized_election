<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface CandidateResult {
  id: number;
  candidate_name: string;
  candidate_party: string | null;
  candidate_picture: string | null;
  votes: number;
}

const props = defineProps<{
  election: {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
  } | null;
  positions?: Record<string, CandidateResult[]>; // Updated here
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Results History', href: '/results' },
  { title: props.election?.name || 'Election Results', href: route('results.show', props.election?.id) },
]);

const getCandidateImage = (picturePath: string | null) => {
  return picturePath 
    ? `/storage/${picturePath}`
    : '/images/default-candidate.png';
};

const exportToExcel = () => {
  const exportUrl = route('results.export', props.election?.id);
  window.open(exportUrl, '_blank');
};
</script>

<template>
  <Head :title="`Results - ${election?.name || 'Election'}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-8">
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold">Election Results</h1>
          <h2 class="text-xl text-gray-600">{{ election?.name || 'Loading...' }}</h2>
          <p v-if="election" class="text-gray-500">
            {{ new Date(election.start_date).toLocaleDateString() }} - 
            {{ new Date(election.end_date).toLocaleDateString() }}
          </p>
        </div>
        <button 
          @click="exportToExcel"
          class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded"
        >
          Export to Excel
        </button>
      </div>

      <!-- Loading state -->
      <div v-if="!positions" class="text-center py-8">
        <p class="text-muted-foreground">Loading election results...</p>
      </div>

      <!-- Results display -->
      <div v-else class="space-y-8">
        <template v-if="Object.keys(positions).length > 0">
          <div v-for="(candidates, positionName) in positions" :key="positionName" class="rounded-lg shadow">
            <h3 class="text-lg font-semibold px-6 pt-6 pb-2">{{ positionName }}</h3>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[50px]">Rank</TableHead>
                  <TableHead>Candidate Name</TableHead>
                  <TableHead>Party</TableHead>
                  <TableHead class="text-right">Votes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(candidate, index) in [...candidates].sort((a, b) => b.votes - a.votes)" :key="candidate.id">
                  <TableCell>{{ index + 1 }}</TableCell>
                  <TableCell>
                    <div class="flex items-center gap-3">
                      {{ candidate.candidate_name }}
                    </div>
                  </TableCell>
                  <TableCell>
                    {{ candidate.candidate_party }}
                  </TableCell>
                  <TableCell class="text-right">
                    {{ candidate.votes }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </template>
        <div v-else class="text-center py-8 text-muted-foreground">
          No results available for this election.
        </div>
      </div>
    </div>
  </AppLayout>
</template>
