<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3'; // Import router from Inertia
import VoterRegistrationDialog from '@/components/voter/VoterRegistrationDialog.vue';
import VotersTable from '@/components/voter/VotersTable.vue';
import { Button } from '@/components/ui/button';
import { KeyRound } from "lucide-vue-next";

interface Voter {
  id: number;
  student_number: string;
  full_name: string;
  student_year: string;
  class_type: string;
  sex: string;
  is_active?: boolean;
}

const props = defineProps<{
  voters: Voter[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Voters',
    href: '/voters',
  },
  {
    title: 'Register',
    href: '/voters/register',
  },
];

// Navigation function using Inertia
const navigateToActivationPage = () => {
    router.get('/voters/status');
};
</script>

<template>
<Head title="Voter Management" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
    <div class="flex justify-end gap-2">
        <Button 
            variant="outline" 
            @click="navigateToActivationPage"
            :disabled="voters.length === 0"
        >
            <KeyRound />
            Activation
        </Button>
        
        <VoterRegistrationDialog />
    </div>
    
        <div class="rounded-xl border">
            <VotersTable :voters="voters" />
        </div>
    </div>
</AppLayout>
</template>