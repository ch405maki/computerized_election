<template>
  <Head title="Voter Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center gap-2">
          <TitleHeader 
            title="Voter List" 
            description="View and manage registered voters and their credentials." 
          />

          <div class="space-x-2 flex items-center">
            <div class="relative w-full max-w-xs">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search voters..."
                class="w-full pl-9 h-9"
              />
            </div>
            
            <input
              type="file"
              ref="fileInput"
              accept=".xlsx, .xls, .csv"
              class="hidden"
              @change="handleFileUpload"
            />
            
            <Button
              size="sm"
              @click="triggerFileInput"
              :disabled="loading"
              variant="outline"
              class="flex items-center gap-2 relative"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              <Upload v-else class="w-4 h-4" />
              <span>{{ loading ? 'Uploading...' : 'Upload Excel' }}</span>
              
              <!-- Progress Indicator -->
              <div v-if="loading && uploadProgress > 0" 
                   class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary/20">
                <div class="h-full bg-primary transition-all duration-300" 
                     :style="{ width: uploadProgress + '%' }"></div>
              </div>
            </Button>

            <Button
              size="sm"
              variant="outline" 
              @click="navigateToActivationPage"
              :disabled="voters.data.length === 0"
              class="flex items-center gap-2"
            >
              <KeyRound class="w-4 h-4" />
              Activation
            </Button>
            
            <VoterRegistrationDialog />
          </div>
        </div>

        <!-- Upload Status Banner -->
        <div v-if="uploadStatus" 
             :class="['p-3 rounded-lg border flex items-start gap-3 animate-fade-in',
               uploadStatus.type === 'success' ? 'bg-green-50 border-green-200' :
               uploadStatus.type === 'error' ? 'bg-red-50 border-red-200' :
               'bg-blue-50 border-blue-200']">
          
          <div v-if="uploadStatus.type === 'success'" class="text-green-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div v-else-if="uploadStatus.type === 'error'" class="text-red-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          
          <div class="flex-1">
            <h4 class="font-medium mb-1" 
                :class="uploadStatus.type === 'success' ? 'text-green-800' : 
                          uploadStatus.type === 'error' ? 'text-red-800' : 'text-blue-800'">
              {{ uploadStatus.title }}
            </h4>
            <p class="text-sm" 
               :class="uploadStatus.type === 'success' ? 'text-green-700' : 
                        uploadStatus.type === 'error' ? 'text-red-700' : 'text-blue-700'">
              {{ uploadStatus.message }}
            </p>
            
            <div v-if="uploadStatus.type === 'error' && uploadErrors.length" class="mt-2">
               <button @click="showErrorDetails = !showErrorDetails" 
                       class="text-sm underline hover:no-underline focus:outline-none">
                 {{ showErrorDetails ? 'Hide details' : 'Show details' }}
               </button>
               <div v-if="showErrorDetails" class="mt-2 bg-white p-3 rounded border max-h-60 overflow-y-auto">
                 <ul class="space-y-2 text-sm">
                   <li v-for="(error, index) in uploadErrors" :key="index" class="border-b pb-2 last:border-0">
                     <div v-if="error.row" class="font-medium">Row {{ error.row }}:</div>
                     <div v-if="error.errors" class="text-red-600">
                       {{ Array.isArray(error.errors) ? error.errors.join(', ') : error.errors }}
                     </div>
                   </li>
                 </ul>
               </div>
            </div>
          </div>
          
          <button @click="clearUploadStatus" class="text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>

        <div v-if="processingLargeFile" class="p-4 bg-blue-50 border border-blue-200 rounded-lg animate-pulse">
           <div class="flex items-center gap-3">
            <Loader2 class="w-5 h-5 text-blue-600 animate-spin" />
            <div>
              <h4 class="font-medium text-blue-800">Processing large file...</h4>
              <p class="text-sm text-blue-700">Please wait while we process {{ estimatedRowCount.toLocaleString() }} rows.</p>
            </div>
           </div>
        </div>
      </div>
      
      <div class="relative rounded-xl border">
        <div v-if="isFetching" 
             class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px] rounded-xl transition-all duration-300">
             <div class="flex flex-col items-center gap-2">
                <Loader2 class="w-8 h-8 text-primary animate-spin" />
                <span class="text-xs text-muted-foreground font-medium">Updating...</span>
             </div>
        </div>

        <VotersTable :voters="voters.data" :class="{ 'opacity-40': isFetching }" />
      </div>

      <div v-if="voters.data.length > 0" class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-2">
        <div class="text-sm text-muted-foreground">
            Showing <span class="font-medium text-foreground">{{ voters.from }}</span> to <span class="font-medium text-foreground">{{ voters.to }}</span> of <span class="font-medium text-foreground">{{ voters.total }}</span> results
        </div>
        
        <div class="flex items-center space-x-2">
            <Button
                variant="outline"
                class="hidden h-8 w-8 p-0 lg:flex"
                :disabled="voters.current_page === 1"
                @click="handlePageChange(1)"
            >
                <span class="sr-only">Go to first page</span>
                <ChevronsLeft class="h-4 w-4" />
            </Button>

            <Button
                variant="outline"
                class="h-8 w-8 p-0"
                :disabled="voters.current_page === 1"
                @click="handlePageChange(voters.current_page - 1)"
            >
                <span class="sr-only">Go to previous page</span>
                <ChevronLeft class="h-4 w-4" />
            </Button>

            <template v-for="(page, index) in paginationRange" :key="index">
                <div v-if="page === '...'" class="flex items-center justify-center h-8 w-8">
                    <span class="text-sm text-muted-foreground">...</span>
                </div>
                <Button
                    v-else
                    :variant="voters.current_page === page ? 'default' : 'outline'"
                    class="h-8 w-8 p-0"
                    @click="handlePageChange(page as number)"
                >
                    {{ page }}
                </Button>
            </template>

            <Button
                variant="outline"
                class="h-8 w-8 p-0"
                :disabled="voters.current_page === voters.last_page"
                @click="handlePageChange(voters.current_page + 1)"
            >
                <span class="sr-only">Go to next page</span>
                <ChevronRight class="h-4 w-4" />
            </Button>

            <Button
                variant="outline"
                class="hidden h-8 w-8 p-0 lg:flex"
                :disabled="voters.current_page === voters.last_page"
                @click="handlePageChange(voters.last_page)"
            >
                <span class="sr-only">Go to last page</span>
                <ChevronsRight class="h-4 w-4" />
            </Button>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import VoterRegistrationDialog from '@/components/voter/VoterRegistrationDialog.vue';
import VotersTable from '@/components/voter/VotersTable.vue';
import { Button } from '@/components/ui/button';
import { 
    KeyRound, 
    Upload, 
    Loader2, 
    Search,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight
} from "lucide-vue-next";
import axios from "axios";
import type { AxiosError, AxiosProgressEvent } from "axios";
import { useToast } from "vue-toastification";
import { Input } from '@/components/ui/input'
import TitleHeader from '@/components/ui/title-header/header.vue';

interface Voter {
  id: number;
  student_number: string;
  full_name: string;
  student_year: string;
  class_type: string;
  sex: string;
  is_active?: boolean;
}

interface PaginatedVoters {
  data: Voter[];
  current_page: number;
  from: number;
  last_page: number;
  per_page: number; // Required for pagination math
  to: number;
  total: number;
  links: any[]; // Kept for type safety if Laravel sends it
}

interface UploadStatus {
  type: 'success' | 'error' | 'info';
  title: string;
  message: string;
}

interface UploadResult {
  processed: number;
  skipped: number;
  errors: string[];
  import_id?: string;
  message?: string;
}

interface ValidationError {
  row?: number;
  attribute?: string;
  errors?: string[] | string;
  values?: any;
}

const props = defineProps<{
  voters: PaginatedVoters;
  filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Voters', href: '/voters' },
  { title: 'Register', href: '/voters/register' },
];

// Refs
const fileInput = ref<HTMLInputElement | null>(null);
const toast = useToast();
const loading = ref(false);
const isFetching = ref(false);
const uploadProgress = ref(0);
const searchQuery = ref(props.filters?.search || "");
const uploadStatus = ref<UploadStatus | null>(null);
const uploadResult = ref<UploadResult | null>(null);
const uploadErrors = ref<ValidationError[]>([]);
const showErrorDetails = ref(false);
const processingLargeFile = ref(false);
const estimatedRowCount = ref(0);

// --- Custom Pagination Logic ---
const paginationRange = computed(() => {
    const current = props.voters.current_page;
    const total = props.voters.last_page;
    const delta = 1; // Number of pages to show on each side of current page

    // If total pages are 7 or less, show all
    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    const range: (number | string)[] = [];
    const left = current - delta;
    const right = current + delta;

    // Always add first page
    range.push(1);

    // Add ellipsis if gap exists between 1 and left window
    if (left > 2) {
        range.push("...");
    }

    // Add pages in the middle window
    for (let i = Math.max(2, left); i <= Math.min(total - 1, right); i++) {
        range.push(i);
    }

    // Add ellipsis if gap exists between right window and last page
    if (right < total - 1) {
        range.push("...");
    }

    // Always add last page
    if (total > 1) {
        range.push(total);
    }

    return range;
});

// Navigation
const handlePageChange = (newPage: number) => {
    if (newPage < 1 || newPage > props.voters.last_page || newPage === props.voters.current_page) {
        return;
    }

    router.get('/voters', 
        { search: searchQuery.value, page: newPage }, 
        {
        preserveState: true,
        preserveScroll: true,
        onStart: () => { isFetching.value = true },
        onFinish: () => { isFetching.value = false }
        }
    );
};

// --- Debounce Helper ---
function debounce<T extends (...args: any[]) => any>(fn: T, wait: number) {
  let timeout: ReturnType<typeof setTimeout>;
  return function(this: any, ...args: Parameters<T>) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), wait);
  };
}

// --- Watchers ---
watch(searchQuery, debounce((value: string) => {
    router.get('/voters', { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => { isFetching.value = true },
        onFinish: () => { isFetching.value = false }
    });
}, 300));

// --- Navigation & File Actions ---
const navigateToActivationPage = () => {
  router.get('/voters/status');
};

// File Handling
const triggerFileInput = () => {
  fileInput.value?.click();
};

const estimateRowCount = (file: File): number => {
  // Very rough estimation: 1KB ≈ 10 rows for Excel
  const estimatedRows = Math.ceil(file.size / 100);
  return Math.min(estimatedRows, 10000);
};

// --- File Upload Logic ---
const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (!file) return;

  // Clear previous status
  clearUploadStatus();
  uploadErrors.value = [];
  showErrorDetails.value = false;

  // File size validation (10MB max)
  const maxSize = 10 * 1024 * 1024; // 10MB
  if (file.size > maxSize) {
    toast.error("File size must be less than 10MB");
    if (fileInput.value) fileInput.value.value = '';
    return;
  }

  // Estimate row count for user feedback
  estimatedRowCount.value = estimateRowCount(file);
  if (estimatedRowCount.value > 1000) {
    processingLargeFile.value = true;
  }

  loading.value = true;
  uploadProgress.value = 0;

  const formData = new FormData();
  formData.append("file", file);
  
  // Auto-use queue for large files (>5MB or >3000 estimated rows)
  const useQueue = file.size > 5 * 1024 * 1024 || estimatedRowCount.value > 3000;
  if (useQueue) {
    formData.append("use_queue", "true");
  }

  try {
    const response = await axios.post("/api/upload-voters", formData, {
      headers: { 
        "Content-Type": "multipart/form-data",
        "X-Requested-With": "XMLHttpRequest"
      },
      onUploadProgress: (progressEvent: AxiosProgressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }
      },
      timeout: 0, 
    });

    // Handle queued response
    if (response.data.queued) {
      uploadStatus.value = {
        type: 'info',
        title: 'Processing in Background',
        message: `Your file with approximately ${estimatedRowCount.value.toLocaleString()} rows is being processed. You'll be notified when complete.`
      };
      toast.info(`File processing started. Import ID: ${response.data.import_id}`);
    } else {
      // Handle immediate completion
      uploadResult.value = response.data;
      uploadStatus.value = {
        type: 'success',
        title: 'Upload Successful!',
        message: response.data.message || 'Voters imported successfully.'
      };
      
      // Show detailed results
      const processed = response.data.processed || 0;
      const skipped = response.data.skipped || 0;
      
      if (processed > 0 || skipped > 0) {
        toast.success(`Import completed: ${processed} processed, ${skipped} skipped`);
      }
      
      // Refresh data
      router.reload({ only: ['voters'] });
    }

  } catch (err: unknown) {
    const error = err as AxiosError;
    if (error.response?.data) {
        const data = error.response.data as any;
        const status = error.response.status;
        if (status === 422) {
             uploadErrors.value = data.errors || [];
             uploadStatus.value = { type: 'error', title: 'Validation Error', message: 'Validation failed.' };
        } else if (status === 409) {
             uploadStatus.value = { type: 'error', title: 'Duplicate Data', message: data.message };
        } else {
             uploadStatus.value = { type: 'error', title: 'Error', message: data.message || 'Upload failed' };
        }
    } else {
        uploadStatus.value = { type: 'error', title: 'Network/Timeout', message: 'Connection error' };
    }
    console.error(error);
  } finally {
    loading.value = false;
    uploadProgress.value = 0;
    processingLargeFile.value = false;
    // Reset file input
    if (fileInput.value) fileInput.value.value = '';
  }
};

const clearUploadStatus = () => {
  uploadStatus.value = null;
  uploadResult.value = null;
  uploadErrors.value = [];
  showErrorDetails.value = false;
};

// Auto-clear success status after 10 seconds
onMounted(() => {
  if (uploadStatus.value?.type === 'success') {
    setTimeout(() => {
      uploadStatus.value = null;
      uploadResult.value = null;
    }, 10000);
  }
});
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>