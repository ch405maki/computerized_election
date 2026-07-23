<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
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
  ChevronsRight,
  CheckCircle,
  AlertCircle,
  Info,
  X
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
  per_page: number;
  to: number;
  total: number;
  links: any[];
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

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as any);

const props = defineProps<{
  voters: PaginatedVoters;
  filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Voters', href: '/voters' },
  { title: 'Voter List', href: '/voters/register' },
];

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
let pollInterval: ReturnType<typeof setInterval> | null = null;

// --- DRY Helpers ---
const setStatus = (type: UploadStatus['type'], title: string, message: string) => {
  uploadStatus.value = { type, title, message };
};

const stopPolling = () => {
  if (pollInterval) clearInterval(pollInterval);
  processingLargeFile.value = false;
};

const fetchVoters = (params: Record<string, any>, replace = false) => {
  router.get('/voters', params, {
    preserveState: true,
    preserveScroll: true,
    replace,
    onStart: () => { isFetching.value = true },
    onFinish: () => { isFetching.value = false }
  });
};

// --- Theme Mapping for Upload Status ---
const statusTheme = computed(() => {
  if (!uploadStatus.value) return null;
  const themes = {
    success: { bg: 'bg-green-50 border-green-200', text: 'text-green-800', msg: 'text-green-700', icon: CheckCircle, iconColor: 'text-green-600' },
    error: { bg: 'bg-red-50 border-red-200', text: 'text-red-800', msg: 'text-red-700', icon: AlertCircle, iconColor: 'text-red-600' },
    info: { bg: 'bg-blue-50 border-blue-200', text: 'text-blue-800', msg: 'text-blue-700', icon: Info, iconColor: 'text-blue-600' }
  };
  return themes[uploadStatus.value.type];
});

// --- Custom Pagination Logic ---
const paginationRange = computed(() => {
  const current = props.voters.current_page;
  const total = props.voters.last_page;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

  const range: (number | string)[] = [1];
  const left = current - 1;
  const right = current + 1;

  if (left > 2) range.push("...");
  for (let i = Math.max(2, left); i <= Math.min(total - 1, right); i++) range.push(i);
  if (right < total - 1) range.push("...");
  if (total > 1) range.push(total);

  return range;
});

// --- Actions ---
const handlePageChange = (newPage: number) => {
  if (newPage >= 1 && newPage <= props.voters.last_page && newPage !== props.voters.current_page) {
    fetchVoters({ search: searchQuery.value, page: newPage });
  }
};

function debounce<T extends (...args: any[]) => any>(fn: T, wait: number) {
  let timeout: ReturnType<typeof setTimeout>;
  return function (this: any, ...args: Parameters<T>) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), wait);
  };
}

const handleSearch = debounce(() => {
  fetchVoters({ search: searchQuery.value }, true);
}, 300);

// --- File Upload Logic ---
const handleFileUpload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;

  clearUploadStatus();

  if (file.size > 10 * 1024 * 1024) {
    toast.error("File size must be less than 10MB");
    if (fileInput.value) fileInput.value.value = '';
    return;
  }

  estimatedRowCount.value = Math.min(Math.ceil(file.size / 100), 10000);
  processingLargeFile.value = estimatedRowCount.value > 1000;
  
  loading.value = true;
  uploadProgress.value = 0;

  const formData = new FormData();
  formData.append("file", file);

  try {
    const response = await axios.post("/api/upload-voters", formData, {
      headers: { "Content-Type": "multipart/form-data", "X-Requested-With": "XMLHttpRequest" },
      onUploadProgress: (e: AxiosProgressEvent) => {
        if (e.total) uploadProgress.value = Math.round((e.loaded * 100) / e.total);
      },
      timeout: 0,
    });

    if (response.data.queued) {
      setStatus('info', 'Processing in Background', `Your file with approximately ${estimatedRowCount.value.toLocaleString()} rows is being processed. The page will refresh automatically when done.`);
      toast.info(`File processing started.`);

      pollInterval = setInterval(async () => {
        try {
          const statusRes = await axios.get(`/api/import-status/${response.data.import_id}`);
          const currentStatus = statusRes.data.status;

          if (currentStatus === 'completed') {
            stopPolling();
            setStatus('success', 'Upload Successful!', 'Background import has finished.');
            toast.success("All voters have been imported!");
            router.reload({ only: ['voters'] });
          } else if (currentStatus === 'failed') {
            stopPolling();
            setStatus('error', 'Import Failed', 'There was an issue processing your file. Please check your column headers and try again.');
            toast.error("Import failed.");
          }
        } catch (e) {
          console.error("Failed to check status", e);
          stopPolling();
        }
      }, 3000);

    } else {
      const { processed = 0, skipped = 0, message = 'Voters imported successfully.' } = response.data;
      uploadResult.value = response.data;
      setStatus('success', 'Upload Successful!', message);
      
      if (processed > 0 || skipped > 0) toast.success(`Import completed: ${processed} processed, ${skipped} skipped`);
      router.reload({ only: ['voters'] });
    }

  } catch (err: any) {
    const errorData = err.response?.data;
    if (errorData) {
      if (err.response.status === 422) {
        uploadErrors.value = errorData.errors || [];
        setStatus('error', 'Validation Error', 'Validation failed.');
      } else {
        setStatus('error', err.response.status === 409 ? 'Duplicate Data' : 'Error', errorData.message || 'Upload failed');
      }
    } else {
      setStatus('error', 'Network/Timeout', 'Connection error');
    }
  } finally {
    loading.value = false;
    uploadProgress.value = 0;
    if (!pollInterval) processingLargeFile.value = false;
    if (fileInput.value) fileInput.value.value = '';
  }
};

const clearUploadStatus = () => {
  uploadStatus.value = null;
  uploadResult.value = null;
  uploadErrors.value = [];
  showErrorDetails.value = false;
};

onUnmounted(stopPolling);

onMounted(() => {
  if (uploadStatus.value?.type === 'success') {
    setTimeout(() => {
      uploadStatus.value = null;
      uploadResult.value = null;
    }, 10000);
  }
});
</script>

<template>
  <Head title="Voter Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <div class="flex flex-col gap-4">
        <!-- Header & Toolbar -->
        <div class="flex justify-between items-center gap-2">
          <TitleHeader title="Voter List" description="View and manage registered voters and their credentials." />

          <div class="space-x-2 flex items-center">
            <div class="relative w-full max-w-xs">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Search voters..." class="w-full pl-9 h-9" />
            </div>

            <input v-if="currentUser?.permissions?.uploadExcel" type="file" ref="fileInput" accept=".xlsx, .xls, .csv" class="hidden" @change="handleFileUpload" />

            <Button v-if="currentUser?.permissions?.uploadExcel" size="sm" @click="() => fileInput?.click()" :disabled="loading" variant="outline" class="flex items-center gap-2 relative">
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              <Upload v-else class="w-4 h-4" />
              <span>{{ loading ? 'Uploading...' : 'Upload Excel' }}</span>

              <div v-if="loading && uploadProgress > 0" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary/20">
                <div class="h-full bg-primary transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
              </div>
            </Button>

            <Button size="sm" variant="outline" @click="() => router.get('/voters/status')" :disabled="voters.data.length === 0" class="flex items-center gap-2">
              <KeyRound class="w-4 h-4" />
              Activation
            </Button>
            
            <VoterRegistrationDialog v-if="currentUser?.permissions?.addVoter" />
          </div>
        </div>

        <!-- Dynamic Status Notification -->
        <div v-if="statusTheme" :class="['p-3 rounded-lg border flex items-start gap-3 animate-fade-in', statusTheme.bg]">
          <component :is="statusTheme.icon" :class="['w-5 h-5 mt-0.5', statusTheme.iconColor]" />

          <div class="flex-1">
            <h4 :class="['font-medium mb-1', statusTheme.text]">{{ uploadStatus?.title }}</h4>
            <p :class="['text-sm', statusTheme.msg]">{{ uploadStatus?.message }}</p>

            <div v-if="uploadStatus?.type === 'error' && uploadErrors.length" class="mt-2">
              <button @click="showErrorDetails = !showErrorDetails" class="text-sm underline hover:no-underline focus:outline-none">
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
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Processing Large File Indicator -->
        <div v-if="processingLargeFile" class="p-4 bg-blue-50 border border-blue-200 rounded-lg animate-pulse">
          <div class="flex items-center gap-3">
            <Loader2 class="w-5 h-5 text-blue-600 animate-spin" />
            <div>
              <h4 class="font-medium text-blue-800">Processing large file...</h4>
              <p class="text-sm text-blue-700">Please wait while we process {{ estimatedRowCount.toLocaleString() }} rows. The page will refresh automatically.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="relative rounded-xl border">
        <div v-if="isFetching" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px] rounded-xl transition-all duration-300">
          <div class="flex flex-col items-center gap-2">
            <Loader2 class="w-8 h-8 text-primary animate-spin" />
            <span class="text-xs text-muted-foreground font-medium">Updating...</span>
          </div>
        </div>
        <VotersTable :voters="voters.data" :class="{ 'opacity-40': isFetching }" />
      </div>

      <!-- Pagination -->
      <div v-if="voters.data.length > 0" class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-2">
        <div class="text-sm text-muted-foreground">
          Showing <span class="font-medium text-foreground">{{ voters.from }}</span> to <span class="font-medium text-foreground">{{ voters.to }}</span> of <span class="font-medium text-foreground">{{ voters.total }}</span> results
        </div>

        <div class="flex items-center space-x-2">
          <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="voters.current_page === 1" @click="handlePageChange(1)">
            <span class="sr-only">Go to first page</span>
            <ChevronsLeft class="h-4 w-4" />
          </Button>

          <Button variant="outline" class="h-8 w-8 p-0" :disabled="voters.current_page === 1" @click="handlePageChange(voters.current_page - 1)">
            <span class="sr-only">Go to previous page</span>
            <ChevronLeft class="h-4 w-4" />
          </Button>

          <template v-for="(page, index) in paginationRange" :key="index">
            <div v-if="page === '...'" class="flex items-center justify-center h-8 w-8 text-sm text-muted-foreground">...</div>
            <Button v-else :variant="voters.current_page === page ? 'default' : 'outline'" class="h-8 w-8 p-0" @click="handlePageChange(page as number)">
              {{ page }}
            </Button>
          </template>

          <Button variant="outline" class="h-8 w-8 p-0" :disabled="voters.current_page === voters.last_page" @click="handlePageChange(voters.current_page + 1)">
            <span class="sr-only">Go to next page</span>
            <ChevronRight class="h-4 w-4" />
          </Button>

          <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="voters.current_page === voters.last_page" @click="handlePageChange(voters.last_page)">
            <span class="sr-only">Go to last page</span>
            <ChevronsRight class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fade-in 0.3s ease-out; }
</style>