<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { useToast } from "vue-toastification";
import { Checkbox } from '@/components/ui/checkbox';
import axios from 'axios';
import { ref } from 'vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import ElectionEditSheet from '@/components/election/ElectionEditSheet.vue';
import { Trash, FilePenLine } from "lucide-vue-next";

type ElectionStatus = 'active' | 'upcoming' | 'completed';

interface Election {
  id: number;
  name: string;
  status: ElectionStatus;
  start_date: string;
  end_date: string;
}

const toast = useToast();
const props = defineProps<{
    elections: Election[]; 
}>();

// Modal state
const isDeleteDialogOpen = ref(false);
const electionToDelete = ref<number | null>(null);
const hasAgreed = ref(false);
const showFinalWarning = ref(false);

const openDeleteDialog = (id: number) => {
  electionToDelete.value = id;
  hasAgreed.value = false;
  isDeleteDialogOpen.value = true;
};

const proceedToFinalWarning = () => {
  if (!hasAgreed.value) {
    toast.error('You must acknowledge the consequences before deleting');
    return;
  }
  showFinalWarning.value = true;
};

const deleteElection = async () => {
  if (!electionToDelete.value) return;

  try {
    await axios.delete(`/api/elections/${electionToDelete.value}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Accept': 'application/json'
      }
    });
    
    toast.success('Election deleted successfully!');
    // Close the dialogs
    isDeleteDialogOpen.value = false;
    showFinalWarning.value = false;

    setTimeout(() => {
      window.location.reload();
    }, 2000);
    
  } catch (error) {
    if (axios.isAxiosError(error)) {
      toast.error(error.response?.data?.message || 'Failed to delete election');
    } else {
      toast.error('An unexpected error occurred');
    }
  } finally {
    electionToDelete.value = null;
    hasAgreed.value = false;
  }
};

const isEditSheetOpen = ref(false);
const selectedElection = ref<Election | null>(null);

const openEditSheet = (election: any) => {
  selectedElection.value = election;
  isEditSheetOpen.value = true;
};

const handleElectionUpdated = () => {
  // Refresh data or update local state
  setTimeout(() => {
    window.location.reload();
  }, 1500);
};

</script>

<template>
  <div class="bg-card rounded-lg">
    <div class="border rounded-lg overflow-hidden">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Start Date</TableHead>
            <TableHead>End Date</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="elections.length === 0">
            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
              No elections found
            </TableCell>
          </TableRow>
          <TableRow v-for="election in elections" :key="election.id">
            <TableCell>{{ election.name }}</TableCell>
            <TableCell>
              <span :class="{
                'text-green-600': election.status === 'active',
                'text-blue-600': election.status === 'upcoming',
                'text-gray-600': election.status === 'completed'
              }">
                {{ election.status }}
              </span>
            </TableCell>
            <TableCell>{{ new Date(election.start_date).toLocaleDateString() }}</TableCell>
            <TableCell>{{ new Date(election.end_date).toLocaleDateString() }}</TableCell>
            <TableCell class="text-right">
              <Button 
                variant="outline" 
                size="sm" 
                class="mr-2"
                @click="openEditSheet(election)"
              >
                <FilePenLine />
              </Button>
              <Button 
                variant="destructive" 
                size="sm"
                @click="openDeleteDialog(election.id)"
              >
              <Trash />
                </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

        <!-- Edit Sheet -->
        <ElectionEditSheet
          v-if="selectedElection"
          :election="selectedElection"
          :open="isEditSheetOpen"
          @close="isEditSheetOpen = false"
          @updated="handleElectionUpdated"
        />

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Election Confirmation</AlertDialogTitle>
          <AlertDialogDescription>
            <div class="space-y-4">
              <p class="font-medium">The following data will be permanently deleted:</p>
              <ul class="list-disc pl-5 space-y-2">
                <li>All candidate information and their profiles</li>
                <li>All voter records and participation data</li>
                <li>Vote tallies and election results</li>
                <li>Any associated documents or media</li>
              </ul>
              
              <div class="flex items-start space-x-2 pt-2">
                <Checkbox id="delete-agreement" v-model:checked="hasAgreed" />
                <label for="delete-agreement" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                  I understand this action cannot be undone and all data will be permanently lost.
                </label>
              </div>
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="proceedToFinalWarning"
            :disabled="!hasAgreed"
            :class="{ 'bg-gray-400 cursor-not-allowed': !hasAgreed }"
          >
            Continue
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Final Warning Dialog -->
    <AlertDialog v-model:open="showFinalWarning">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Final Confirmation Required</AlertDialogTitle>
          <AlertDialogDescription class="space-y-4">
            <p class="font-semibold text-red-600">You are about to permanently delete this election and all its data.</p>
            <p>This action will:</p>
            <ul class="list-disc pl-5 space-y-1">
              <li>Immediately remove all election records</li>
              <li>Affect all users associated with this election</li>
              <li>Make recovery impossible</li>
            </ul>
            <p>Are you absolutely sure you want to proceed?</p>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>No, Go Back</AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteElection"
            class="bg-red-600 hover:bg-red-700"
          >
            Yes, Delete Permanently
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>