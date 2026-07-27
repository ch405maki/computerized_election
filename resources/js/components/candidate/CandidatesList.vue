<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useToast } from "vue-toastification";
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
  userPermissions?: any; 
}>();

const toast = useToast();
const isDeleting = ref(false);
const selectedCandidate = ref<{ id: number; candidate_name: string } | null>(null);

const showDeleteDialog = ref(false);
const showPasswordDialog = ref(false);
const deletePassword = ref('');

const canDeleteCandidate = computed(() => {
  let perms = props.userPermissions;
  if (!perms) return false;

  if (typeof perms === 'string') {
    try {
      perms = JSON.parse(perms);
    } catch {
      return false;
    }
  }

  if (Array.isArray(perms)) return perms.includes('deleteCandidate');

  return ['true', true, 1].includes(perms.deleteCandidate);
});

type SortKey = 
  'candidate_code' | 
  'candidate_name' | 
  'candidate_party' | 
  'position.name' | 
  'election.name';

const tableColumns: Array<{ key: SortKey; label: string }> = [
  { key: 'candidate_code', label: 'Code' },
  { key: 'candidate_name', label: 'Name' },
  { key: 'candidate_party', label: 'Party' },
  { key: 'position.name', label: 'Position' },
  { key: 'election.name', label: 'Election' },
];

const sortKey = ref<SortKey | null>(null);
const sortOrder = ref<'asc' | 'desc'>('asc');

const sortBy = (key: SortKey) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const getNestedValue = (obj: any, path: string) => {
  return path.split('.').reduce((acc, part) => acc && acc[part], obj);
};

const sortedCandidates = computed(() => {
  if (!sortKey.value) return props.candidates;

  return [...props.candidates].sort((a, b) => {
    const valA = String(getNestedValue(a, sortKey.value as string) || '').toLowerCase();
    const valB = String(getNestedValue(b, sortKey.value as string) || '').toLowerCase();

    if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1;
    if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});

const openDeleteDialog = (candidate: { id: number; candidate_name: string }) => {
  selectedCandidate.value = candidate;
  deletePassword.value = ''; 
  showDeleteDialog.value = true;
};

const proceedToPasswordConfirmation = () => {
  showDeleteDialog.value = false;
  showPasswordDialog.value = true;
};

const deleteCandidate = async () => {
  if (!selectedCandidate.value) return;

  if (!deletePassword.value) {
    toast.error("Password is required to confirm deletion");
    return;
  }

  isDeleting.value = true;

  try {
    await axios.post('/election/verify-password', {
      password: deletePassword.value
    });

    await axios.delete(`/api/candidates/${selectedCandidate.value.id}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    toast.success("Candidate deleted successfully!");
    showPasswordDialog.value = false;

    setTimeout(() => {
      window.location.reload();
    }, 2000);
    
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
      toast.error('Incorrect admin password.');
    } else if (axios.isAxiosError(error)) {
      toast.error(error.response?.data?.message || "Failed to delete candidate");
    } else {
      toast.error("An unexpected error occurred");
    }
  } finally {
    isDeleting.value = false;
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
            <TableHead 
              v-for="col in tableColumns" 
              :key="col.key" 
              @click="sortBy(col.key)" 
              class="cursor-pointer select-none hover:bg-muted/50 transition-colors"
            >
              <div class="flex items-center gap-1">
                {{ col.label }}
                <ArrowUp v-if="sortKey === col.key && sortOrder === 'asc'" class="w-4 h-4" />
                <ArrowDown v-else-if="sortKey === col.key && sortOrder === 'desc'" class="w-4 h-4" />
                <ArrowUpDown v-else class="w-4 h-4 text-muted-foreground/50" />
              </div>
            </TableHead>            
            <TableHead v-if="canDeleteCandidate" class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="candidates.length === 0">
            <TableCell :colspan="canDeleteCandidate ? 7 : 6" class="text-center">
              No candidates found
            </TableCell>
          </TableRow>
          
          <TableRow v-for="candidate in sortedCandidates" :key="candidate.id">
            <TableCell class="w-10 h-10">
              <img 
                :src="candidate.candidate_picture ? `/storage/${candidate.candidate_picture}` : '/images/anonymous.jpg'" 
                :alt="candidate.candidate_picture ? 'Candidate Picture' : 'No Uploaded Image'" 
                class="w-10 h-10 rounded-full object-cover border"
                :class="!candidate.candidate_picture && 'mx-auto'"
              />
            </TableCell>
            <TableCell>{{ candidate.candidate_code }}</TableCell>
            <TableCell>{{ candidate.candidate_name }}</TableCell>
            <TableCell>{{ candidate.candidate_party || 'Independent' }}</TableCell>
            <TableCell>{{ candidate.position.name }}</TableCell>
            <TableCell>{{ candidate.election.name }}</TableCell>
            
            <TableCell v-if="canDeleteCandidate">
              <div class="text-right">
                <Button 
                    size="sm"
                    variant="destructive"
                    @click="openDeleteDialog(candidate)"
                    :disabled="isDeleting"
                  >
                  <Trash class="w-4 h-4 hover:text-red-700" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <AlertDialog v-model:open="showDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Candidate Confirmation</AlertDialogTitle>
          <AlertDialogDescription>
            <p>
              This action cannot be undone. This will permanently delete 
              <span class="font-semibold">{{ selectedCandidate?.candidate_name }}</span> 
              and remove all associated data.
            </p>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction @click="proceedToPasswordConfirmation">
            Continue
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog v-model:open="showPasswordDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Password Required</AlertDialogTitle>
          <AlertDialogDescription>
            <div class="space-y-4 pt-2">
              <p class="font-medium text-foreground">
                Confirm your password to delete the candidate.
              </p>
              <Input 
                type="password"   
                v-model="deletePassword" 
                placeholder="Enter admin password" 
                @keyup.enter="deleteCandidate"
              />
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="deletePassword = ''" :disabled="isDeleting">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            :disabled="isDeleting || !deletePassword"
            @click.prevent="deleteCandidate"
            class="bg-red-600 hover:bg-red-700 disabled:opacity-50"
          >
            <span v-if="!isDeleting">Delete Candidate</span>
            <span v-else>Deleting...</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>