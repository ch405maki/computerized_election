<script setup lang="ts">
import { ref } from "vue";
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
import { Progress } from '@/components/ui/progress';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

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
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadStatus = ref("Preparing upload...");

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (!file) return;

  loading.value = true;
  isUploading.value = true;
  uploadProgress.value = 0;
  uploadStatus.value = "Uploading file...";

  const formData = new FormData();
  formData.append("file", file);

  try {
    const response = await axios.post("/api/upload-voters", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
        }
      },
    });

    uploadStatus.value = "Processing data...";
    uploadProgress.value = 100;

    toast.success(response.data.message || "Voters imported successfully!");
    
    setTimeout(() => {
      window.location.reload();
    }, 2000);
  } catch (err: unknown) {
    const error = err as AxiosError;
    
    if (error.response?.data) {
      const responseData = error.response.data as any;
      toast.error(responseData.message || "An error occurred during upload");
    } else {
      toast.error("Network error. Please check your connection and try again");
    }
  } finally {
    loading.value = false;
    isUploading.value = false;
    if (fileInput.value) fileInput.value.value = "";
  }
};

</script>

<template>
<Head title="Voter Management" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
    <div class="flex justify-end gap-2">
      <!-- Upload Excel Button -->
      <div class="relative">
            <input
              type="file"
              ref="fileInput"
              accept=".xlsx, .xls, .csv"
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
          </div>
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

    <!-- Upload Progress Modal -->
    <Dialog :open="isUploading">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Uploading Users</DialogTitle>
          <DialogDescription>
            Please wait while we process your Excel file...
          </DialogDescription>
        </DialogHeader>
        <div class="py-4">
          <Progress :value="uploadProgress" class="h-2" />
          <div class="mt-2 text-sm text-muted-foreground text-center">
            {{ uploadStatus }}
          </div>
        </div>
      </DialogContent>
    </Dialog>
</AppLayout>
</template>