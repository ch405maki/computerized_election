<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
  election: {
    id: number;
    name: string;
    // Include other election properties you need
  }
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'History',
    href: '/Reports/Results',
  },
  {
    title: 'Show Results',
    href: '/Show',
  },
];

</script>

<template>
  <Head :title="`Results - ${election.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">Results for {{ election.name }}</h1>
      
      <!-- Your results display will go here -->
      <div class="bg-black rounded-lg shadow p-6">
        
      </div>
    </div>
  </AppLayout>
</template>