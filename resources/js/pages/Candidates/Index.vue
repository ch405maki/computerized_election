<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import CandidateForm from '@/components/candidate/CandidateForm.vue';
import CandidatesList from '@/components/candidate/CandidatesList.vue';

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
];
</script>

<template>
  <Head title="Candidates" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-8">
      <div class="grid grid-cols-1 gap-8">
        <CandidateForm 
          :positions="positions" 
          :elections="elections" 
        />
        <CandidatesList :candidates="candidates" />
      </div>
    </div>
  </AppLayout>
</template>