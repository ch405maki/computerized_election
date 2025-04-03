<template>
  <Head title="Users Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Search and Buttons -->
      <div class="flex items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full max-w-xs">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            type="text"
            placeholder="Search users..."
            class="w-full pl-9 h-9"
          />
        </div>

        <!-- Buttons -->
        <div class="flex items-center gap-4">
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

          <!-- Create User Button -->
          <CreateUserDialog />
        </div>
      </div>

      <!-- Users Table -->
      <div class="relative min-h-[100vh] flex-1 rounded-xl border">
        <UsersTable :users="filteredUsers" />
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

<script setup lang="ts">
import { ref, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import CreateUserDialog from "@/components/users/CreateUserDialog.vue";
import UsersTable from "@/components/users/UsersTable.vue";
import { Button } from "@/components/ui/button";
import { Input } from '@/components/ui/input'
import { Upload, Search } from "lucide-vue-next";
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

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
}

const props = defineProps<{ users: User[] }>();

const breadcrumbs = [{ title: "Users Management", href: "/users" }];
const fileInput = ref<HTMLInputElement | null>(null);
const toast = useToast();
const loading = ref(false);
const searchQuery = ref("");
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadStatus = ref("Preparing upload...");

const filteredUsers = computed(() => {
  if (!searchQuery.value) return props.users;

  const query = searchQuery.value.toLowerCase();
  return props.users.filter(
    (user) =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query) ||
      user.role.toLowerCase().includes(query) ||
      user.status.toLowerCase().includes(query)
  );
});

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
    const response = await axios.post("/api/upload-users", formData, {
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

    toast.success(response.data.message || "Users imported successfully!");
    
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