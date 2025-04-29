<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
import { Trash } from "lucide-vue-next";
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
            <TableHead>Code</TableHead>
            <TableHead>Name</TableHead>
            <TableHead>Party</TableHead>
            <TableHead>Position</TableHead>
            <TableHead>Election</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="candidates.length === 0">
            <TableCell colspan="7" class="text-center">
              No candidates found
            </TableCell>
          </TableRow>
          <TableRow v-for="candidate in candidates" :key="candidate.id">
            <TableCell class="w-10 h-10">
              <img 
                v-if="candidate.candidate_picture" 
                :src="`/storage/${candidate.candidate_picture}`" 
                alt="Candidate Picture" 
                class="w-10 h-10 rounded-full object-cover border"
              />
              <span v-else class="text-gray-400 text-xs">No Image</span>
            </TableCell>
            <TableCell>{{ candidate.candidate_code }}</TableCell>
            <TableCell>{{ candidate.candidate_name }}</TableCell>
            <TableCell>{{ candidate.candidate_party || 'Independent' }}</TableCell>
            <TableCell>{{ candidate.position.name }}</TableCell>
            <TableCell>{{ candidate.election.name }}</TableCell>
            <TableCell>
              <div  class="text-right">
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

    <!-- Delete Confirmation Dialog -->
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