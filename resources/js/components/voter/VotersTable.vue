<script setup lang="ts">
import { ref } from "vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from '@/components/ui/button';
import EditUserDialog from "@/components/users/EditUserDialog.vue";
import VoterEditSheet from "@/components/voter/VoterEditSheet.vue";
import DeleteVoterDialog from "@/components/voter/DeleteVoterDialog.vue";
import CustomSwitch from '@/components/ui/customswitch/CustomSwitch.vue';
import { useToast } from 'vue-toastification';
import { FilePenLine } from "lucide-vue-next";
import axios from 'axios';

interface Voter {
  id: number;
  student_number: string;
  full_name: string;
  student_year: string;
  class_type: string;
  sex: string;
  password: string;
}

const props = defineProps<{
  voters: Voter[];
}>();

const toast = useToast();

// Local copy of voters for reactivity
const localVoters = ref<Voter[]>([...props.voters]);

// State for the Sheet
const editingVoter = ref<Voter | null>(null);

const handleEdit = (voter: Voter) => {
  editingVoter.value = voter;
};

const updateLocalList = (updatedVoter: Voter) => {
  const index = localVoters.value.findIndex(v => v.id === updatedVoter.id);
  if (index !== -1) localVoters.value[index] = updatedVoter;
};
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Student Number</TableHead>
        <TableHead>Full Name</TableHead>
        <TableHead>Year</TableHead>
        <TableHead>Sex</TableHead>
        <TableHead class="text-right">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="voter in voters" :key="voter.id">
        <TableCell class="font-medium">{{ voter.student_number }}</TableCell>
        <TableCell>{{ voter.full_name }}</TableCell>
        <TableCell>{{ voter.student_year }}</TableCell>
        <TableCell>{{ voter.sex }}</TableCell>
        <TableCell class="text-right flex justify-end items-center">
          <Button 
                variant="outline" 
                size="sm" 
                class="mr-2"
                @click="handleEdit(voter)"
              >
                <FilePenLine class="w-4 h-4"/>
            </Button>

          <!-- Delete User -->
          <DeleteVoterDialog :voter="voter" />
        </TableCell>
      </TableRow>
      <TableRow v-if="voters.length === 0">
        <TableCell colspan="5" class="text-center py-4 text-muted-foreground">
          No voters found
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
  <VoterEditSheet 
    v-if="editingVoter" 
    :voter="editingVoter" 
    @close="editingVoter = null" 
    @updated="updateLocalList"
  />
</template>