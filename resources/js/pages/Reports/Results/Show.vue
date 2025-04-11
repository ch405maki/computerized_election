<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface CandidateResult {
  id: number;
  name: string;
  party: string | null;
  photo: string | null;
  votes_count: number;
}

interface PositionResult {
  id: number;
  name: string;
  candidates: CandidateResult[];
}

const props = defineProps<{
  election: {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
  } | null;
  positions?: PositionResult[];
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

const resultsByPosition = computed(() => {
  if (!props.positions || !Array.isArray(props.positions)) {
    return {};
  }

  const groups: Record<string, CandidateResult[]> = {};
  
  props.positions.forEach(position => {
    if (position?.name && Array.isArray(position.candidates)) {
      groups[position.name] = [...position.candidates]
        .sort((a, b) => b.votes_count - a.votes_count);
    }
  });

  return groups;
});
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
      </div>

      <!-- Loading state -->
      <div v-if="!positions" class="text-center py-8">
        <p class="text-muted-foreground">Loading election results...</p>
      </div>

      <!-- Results display -->
      <div v-else class="space-y-8">
        <template v-if="Object.keys(resultsByPosition).length > 0">
          <div v-for="(candidates, position) in resultsByPosition" :key="position" class="rounded-lg shadow">
            <h3 class="text-lg font-semibold px-6 pt-6 pb-2">{{ position }}</h3>
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
                <TableRow v-for="(candidate, index) in candidates" :key="candidate.id">
                  <TableCell>{{ index + 1 }}</TableCell>
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <img 
                        :src="getCandidateImage(candidate.photo)" 
                        :alt="`${candidate.name}'s photo`"
                        class="w-8 h-8 rounded-full object-cover"
                      >
                      {{ candidate.name }}
                    </div>
                  </TableCell>
                  <TableCell>
                    {{ candidate.party || 'Independent' }}
                  </TableCell>
                  <TableCell class="text-right">
                    {{ candidate.votes_count }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </template>
        <div v-else class="text-center py-8 text-muted-foreground">
          No results available for this election
        </div>
      </div>
    </div>
  </AppLayout>
</template>