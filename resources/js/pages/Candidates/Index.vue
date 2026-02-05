<script setup lang="ts">
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
    candidate_party: string;
    candidate_picture: string;
    election: {
      id: number;
      name: string;
    };
    position: {
      id: number;
      name: string;
    };
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
          <CandidateForm 
            :positions="positions" 
            :elections="elections"
            @candidateCreated="refreshCandidates"
          />
        </div>
        <div>
          <CandidatesList :candidates="candidates" />
        </div>
      </div>
  </AppLayout>
</template>