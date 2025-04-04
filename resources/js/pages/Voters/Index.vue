<script setup lang="ts">
import { ref, computed } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3'; // Import router from Inertia
import VoterRegistrationDialog from '@/components/voter/VoterRegistrationDialog.vue';
import VotersTable from '@/components/voter/VotersTable.vue';
import { Button } from '@/components/ui/button';
import { KeyRound } from "lucide-vue-next";
import { Upload } from "lucide-vue-next";
import axios from "axios";
import type { AxiosError } from "axios";
import { useToast } from "vue-toastification";
import { Search } from "lucide-vue-next";
import { Input } from '@/components/ui/input'

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

const fileInput = ref<HTMLInputElement | null>(null);
const toast = useToast();
const loading = ref(false);
const searchQuery = ref(""); // Search query

// Filtered Voters
const filteredVoters = computed(() => {
  if (!searchQuery.value) {
    return props.voters; // Return all voters if no search query
  }

  const query = searchQuery.value.toLowerCase();
  return props.voters.filter(
    (voter) =>
      voter.student_number.toLowerCase().includes(query) ||
      voter.full_name.toLowerCase().includes(query) ||
      voter.student_year.toLowerCase().includes(query) ||
      voter.class_type.toLowerCase().includes(query) ||
      voter.sex.toLowerCase().includes(query)
  );
});


const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    const formData = new FormData();
    formData.append("file", file);

    try {
      const response = await axios.post("/api/upload-voters", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      toast.success(response.data.message);
      setTimeout(() => location.reload(), 2000); // Reload to reflect changes
    } catch (err: unknown) {
      const error = err as AxiosError;
      if (error.response && error.response.data) {
        const status: number = error.response.status;
        const errorMessage: string = (error.response.data as any).message || "An error occurred.";

        if (status === 422) {
          toast.error(`Validation Error: ${errorMessage}`);
        } else if (status === 500) {
          toast.error("Server Error: Please check your file data.");
        } else {
          toast.error(errorMessage);
        }
      } else {
        toast.error("Network error. Please try again.");
      }
    } finally {
      loading.value = false;
    }
  }
 
};

</script>

<template>
<Head title="Voter Management" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
    <div class="flex justify-end gap-2">
       <!-- Search Input -->
       <div class="relative w-full max-w-xs">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            type="text"
            placeholder="Search voters..."
            class="w-full pl-9 h-9"
          />
        </div>
      <!-- Upload Excel Button -->
      <input
            type="file"
            ref="fileInput"
            accept=".xlsx, .xls"
            class="hidden"
            @change="handleFileUpload"
          />
          <Button
            @click="triggerFileInput"
            :disabled="loading"
            variant="outline" 
          >
            <Upload class="w-4 h-4 mr-2" />
            <span v-if="loading">Uploading...</span>
            <span v-else>Upload Excel</span>
          </Button>
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
            <VotersTable :voters="filteredVoters" />
        </div>
    </div>
</AppLayout>
</template>