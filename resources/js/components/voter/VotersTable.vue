<script setup lang="ts">
import { ref } from "vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import EditUserDialog from "@/components/users/EditUserDialog.vue";
import DeleteVoterDialog from "@/components/voter/DeleteVoterDialog.vue";
import CustomSwitch from '@/components/ui/customswitch/CustomSwitch.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

interface Voter {
  id: number;
  student_number: string;
  full_name: string;
  student_year: string;
  class_type: string;
  sex: string;
}

const props = defineProps<{
  voters: Voter[];
}>();

const toast = useToast();

// Local copy of voters for reactivity
const localVoters = ref<Voter[]>([...props.voters]);



</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Student Number</TableHead>
        <TableHead>Full Name</TableHead>
        <TableHead>Year</TableHead>
        <TableHead>Class Type</TableHead>
        <TableHead>Sex</TableHead>
        <TableHead class="text-right">Action</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="voter in voters" :key="voter.id">
        <TableCell class="font-medium">{{ voter.student_number }}</TableCell>
        <TableCell>{{ voter.full_name }}</TableCell>
        <TableCell>{{ voter.student_year }}</TableCell>
        <TableCell>{{ voter.class_type }}</TableCell>
        <TableCell>{{ voter.sex }}</TableCell>
        <TableCell class="text-right flex justify-end items-center">
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
</template>