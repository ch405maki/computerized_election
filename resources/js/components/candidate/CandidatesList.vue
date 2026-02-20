<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
// Import sorting icons alongside Trash
import { Trash, ArrowUpDown, ArrowUp, ArrowDown } from "lucide-vue-next";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = defineProps<{
  candidates: Array<{
    id: number;
    candidate_code: string;
    candidate_name: string;
    candidate_party: string;
    candidate_picture: string;
    election: { name: string };
    position: { name: string };
  }>;
}>();

const toast = useToast();
const isDeleting = ref(false);
const selectedCandidate = ref<{ id: number; candidate_name: string } | null>(null);
const showDeleteDialog = ref(false);

// --- Sorting Logic ---
type SortKey = 'candidate_code' | 'candidate_name' | 'candidate_party' | 'position.name' | 'election.name';

const sortKey = ref<SortKey | null>(null);
const sortOrder = ref<'asc' | 'desc'>('asc');

const sortBy = (key: SortKey) => {
  if (sortKey.value === key) {
    // Toggle order if clicking the same column
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column to sort and default to ascending
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const sortedCandidates = computed(() => {
  // If no sort key is selected, return the original data
  if (!sortKey.value) return props.candidates;

  // Create a copy of the array to prevent prop mutation
  return [...props.candidates].sort((a, b) => {
    let valA = '';
    let valB = '';

    // Extract values, handling nested objects for election and position
    if (sortKey.value === 'position.name') {
      valA = a.position?.name?.toLowerCase() || '';
      valB = b.position?.name?.toLowerCase() || '';
    } else if (sortKey.value === 'election.name') {
      valA = a.election?.name?.toLowerCase() || '';
      valB = b.election?.name?.toLowerCase() || '';
    } else {
      // @ts-ignore
      valA = String(a[sortKey.value] || '').toLowerCase();
      // @ts-ignore
      valB = String(b[sortKey.value] || '').toLowerCase();
    }

    // Compare values based on sort order
    if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1;
    if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});
// ---------------------

const openDeleteDialog = (candidate: { id: number; candidate_name: string }) => {
  selectedCandidate.value = candidate;
  showDeleteDialog.value = true;
};

const deleteCandidate = async () => {
  if (!selectedCandidate.value) return;

  isDeleting.value = true;

  try {
    await axios.delete(`/api/candidates/${selectedCandidate.value.id}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    toast.success("Candidate deleted successfully!");

    setTimeout(() => {
      window.location.reload();
    }, 2000);

  } catch (error) {
    if (axios.isAxiosError(error)) {
      toast.error(error.response?.data?.message || "Failed to delete candidate");
    } else {
      toast.error("An unexpected error occurred");
    }
  } finally {
    isDeleting.value = false;
    showDeleteDialog.value = false;
    selectedCandidate.value = null;
  }
};
</script>

<template>
  <div class="bg-card">
    <div class="border rounded-lg overflow-hidden">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Picture</TableHead>
            
            <TableHead @click="sortBy('candidate_code')" class="cursor-pointer select-none hover:bg-muted/50 transition-colors">
              <div class="flex items-center gap-1">
                Code
                <ArrowUp v-if="sortKey === 'candidate_code' && sortOrder === 'asc'" class="w-4 h-4" />
                <ArrowDown v-else-if="sortKey === 'candidate_code' && sortOrder === 'desc'" class="w-4 h-4" />
                <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground/50" />
              </div>
            </TableHead>

            <TableHead @click="sortBy('candidate_name')" class="cursor-pointer select-none hover:bg-muted/50 transition-colors">
              <div class="flex items-center gap-1">
                Name
                <ArrowUp v-if="sortKey === 'candidate_name' && sortOrder === 'asc'" class="w-4 h-4" />
                <ArrowDown v-else-if="sortKey === 'candidate_name' && sortOrder === 'desc'" class="w-4 h-4" />
                <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground/50" />
              </div>
            </TableHead>

            <TableHead @click="sortBy('candidate_party')" class="cursor-pointer select-none hover:bg-muted/50 transition-colors">
              <div class="flex items-center gap-1">
                Party
                <ArrowUp v-if="sortKey === 'candidate_party' && sortOrder === 'asc'" class="w-4 h-4" />
                <ArrowDown v-else-if="sortKey === 'candidate_party' && sortOrder === 'desc'" class="w-4 h-4" />
                <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground/50" />
              </div>
            </TableHead>

            <TableHead @click="sortBy('position.name')" class="cursor-pointer select-none hover:bg-muted/50 transition-colors">
              <div class="flex items-center gap-1">
                Position
                <ArrowUp v-if="sortKey === 'position.name' && sortOrder === 'asc'" class="w-4 h-4" />
                <ArrowDown v-else-if="sortKey === 'position.name' && sortOrder === 'desc'" class="w-4 h-4" />
                <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground/50" />
              </div>
            </TableHead>

            <TableHead @click="sortBy('election.name')" class="cursor-pointer select-none hover:bg-muted/50 transition-colors">
              <div class="flex items-center gap-1">
                Election
                <ArrowUp v-if="sortKey === 'election.name' && sortOrder === 'asc'" class="w-4 h-4" />
                <ArrowDown v-else-if="sortKey === 'election.name' && sortOrder === 'desc'" class="w-4 h-4" />
                <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground/50" />
              </div>
            </TableHead>
            
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="candidates.length === 0">
            <TableCell colspan="7" class="text-center">
              No candidates found
            </TableCell>
          </TableRow>
          
          <TableRow v-for="candidate in sortedCandidates" :key="candidate.id">
            <TableCell class="w-10 h-10">
              <img 
                v-if="candidate.candidate_picture" 
                :src="`/storage/${candidate.candidate_picture}`" 
                alt="Candidate Picture" 
                class="w-10 h-10 rounded-full object-cover border"
              />
              <img
                v-else 
                src="/images/anonymous.jpg" 
                alt="No Uploaded Image" 
                class="w-10 h-10 rounded-full mx-auto object-cover border"
              />
            </TableCell>
            <TableCell>{{ candidate.candidate_code }}</TableCell>
            <TableCell>{{ candidate.candidate_name }}</TableCell>
            <TableCell>{{ candidate.candidate_party || 'Independent' }}</TableCell>
            <TableCell>{{ candidate.position.name }}</TableCell>
            <TableCell>{{ candidate.election.name }}</TableCell>
            <TableCell>
              <div class="text-right">
                <button 
                    size="sm" 
                    @click="openDeleteDialog(candidate)"
                    :disabled="isDeleting"
                  >
                  <Trash class="w-4 h-4 text-red-600 hover:text-red-700" />
                </button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <AlertDialog v-model:open="showDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete 
            <span class="font-semibold">{{ selectedCandidate?.candidate_name }}</span> 
            and remove all associated data.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            variant="destructive"
            :disabled="isDeleting"
            @click="deleteCandidate"
          >
            <span v-if="!isDeleting">Delete Candidate</span>
            <span v-else>Deleting...</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>