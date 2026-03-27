<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import CandidateForm from '@/components/candidate/CandidateForm.vue';
import CandidatesList from '@/components/candidate/CandidatesList.vue';
import TitleHeader from '@/components/ui/title-header/header.vue';

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
    }| null;
    position: {
      id: number;
      name: string;
    }| null;
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

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Candidates', href: '/candidates' },
  { title: 'Current Candidates', href: '/candidates' },
];

const refreshCandidates = () => {
  router.reload({
    only: ['candidates'],
  });
};

// State for the search bar
const searchQuery = ref('');

// Computed property to filter candidates client-side
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
      <div class="flex justify-between gap-2 items-center">
        <TitleHeader 
          title="Candidates List"
          description="Manage election candidates, their affiliations, and profiles." 
        />

        <div class="flex items-center gap-3">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search candidates..."
            class="px-3 py-2 border border-gray-300 rounded-md text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white w-64" 
          />
          <CandidateForm 
            :positions="positions" 
            :elections="elections" 
            @candidateCreated="refreshCandidates" 
          />
        </div>
      </div>
      <div>
        <CandidatesList :candidates="filteredCandidates" />
      </div>
    </div>
  </AppLayout>
</template>