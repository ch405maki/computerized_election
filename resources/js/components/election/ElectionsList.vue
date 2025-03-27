<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { useToast } from "vue-toastification";
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

const toast = useToast();
const props = defineProps<{
    elections: Array<{
        id: number;
        name: string;
        status: string;
        start_date: string;
        end_date: string;
    }>;
}>();

// Modal state
const isDeleteDialogOpen = ref(false);
const electionToDelete = ref<number | null>(null);

const openDeleteDialog = (id: number) => {
  electionToDelete.value = id;
  isDeleteDialogOpen.value = true;
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
    // Close the dialog
    isDeleteDialogOpen.value = false;

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
  }
};
</script>

<template>
  <div class="bg-card rounded-lg shadow-sm border p-6">
    <h2 class="text-2xl font-bold mb-6">Current Elections</h2>
    <div class="border rounded-lg overflow-hidden">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Start Date</TableHead>
            <TableHead>End Date</TableHead>
            <TableHead>Actions</TableHead>
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
            <TableCell>
              <Button variant="outline" size="sm" class="mr-2">Edit</Button>
              <Button 
                variant="destructive" 
                size="sm"
                @click="openDeleteDialog(election.id)"
              >
                Delete
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the election and remove all associated data.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction @click="deleteElection">Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>