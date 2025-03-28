<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import axios from 'axios';
import { ref, watch } from 'vue';
import VoterStatusHeader from '@/components/voter/status/VoterStatusHeader.vue';
import VoterActivationTable from '@/components/voter/status/VoterActivationTable.vue';

const toast = useToast();
const isLoading = ref(false);
const isActivatingAll = ref(false);
const searchQuery = ref('');

const props = defineProps<{
  inactiveVoters: Array<{
    id: number;
    student_number: string;
    full_name: string;
    student_year: string;
    class_type: string;
    created_at: string;
  }>;
}>();

// Reactive filtered voters
const filteredVoters = ref(props.inactiveVoters);

// Watch for search changes
watch(searchQuery, (newQuery) => {
  if (!newQuery) {
    filteredVoters.value = props.inactiveVoters;
  } else {
    const query = newQuery.toLowerCase();
    filteredVoters.value = props.inactiveVoters.filter(voter => 
      voter.student_number.toLowerCase().includes(query) ||
      voter.full_name.toLowerCase().includes(query) ||
      voter.class_type.toLowerCase().includes(query) ||
      voter.student_year.toLowerCase().includes(query)
  );
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Voters',
    href: '/voters',
  },
  {
    title: 'Pending Voter Activations',
    href: '/voters/status',
  },
];

const activateVoter = async (voterId: number) => {
  isLoading.value = true;
  try {
    const response = await axios.post(`/api/voters/status/activate/${voterId}`);
    toast.success(response.data.message);
    
    // Remove the activated voter from the list
    filteredVoters.value = filteredVoters.value.filter(voter => voter.id !== voterId);
    
    // If you need to ensure sync with server, use:
    // await fetchUpdatedVoters();
    
  } catch (error) {
    handleError(error);
  } finally {
    isLoading.value = false;
  }
};

const activateAll = async () => {
  isActivatingAll.value = true;
  try {
    const response = await axios.post('/api/voters/status/activate-all');
    toast.success(response.data.message);
    router.reload({ only: ['inactiveVoters'] });
  } catch (error) {
    handleError(error);
  } finally {
    isActivatingAll.value = false;
  }
};

const handleError = (error: unknown) => {
  if (axios.isAxiosError(error) && error.response) {
    toast.error(error.response.data.message || 'Failed to activate voter');
  } else {
    toast.error('An unexpected error occurred');
  }
};
</script>

<template>
  <Head title="Voter Status" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <VoterStatusHeader 
        :has-inactive-voters="filteredVoters.length > 0"
        :is-activating-all="isActivatingAll"
        @activate-all="activateAll"
        @search="(query) => searchQuery = query"
      />

      <VoterActivationTable 
        :inactive-voters="filteredVoters"
        :is-loading="isLoading"
        @activate="activateVoter"
      />
    </div>
  </AppLayout>
</template>