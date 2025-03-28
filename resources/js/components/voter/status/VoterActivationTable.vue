<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';

defineProps<{
  inactiveVoters: Array<{
    id: number;
    student_number: string;
    full_name: string;
    student_year: string;
    class_type: string;
    created_at: string;
  }>;
  isLoading: boolean;
}>();

const emit = defineEmits(['activate', 'activateAll']);
</script>

<template>
  <div class="border rounded-lg overflow-hidden">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Student Number</TableHead>
          <TableHead>Full Name</TableHead>
          <TableHead>Year</TableHead>
          <TableHead>Class</TableHead>
          <TableHead>Registered At</TableHead>
          <TableHead class="text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-if="inactiveVoters.length === 0">
          <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
            No pending voter activations
          </TableCell>
        </TableRow>
        <TableRow v-for="voter in inactiveVoters" :key="voter.id">
          <TableCell>{{ voter.student_number }}</TableCell>
          <TableCell>{{ voter.full_name }}</TableCell>
          <TableCell>{{ voter.student_year }}</TableCell>
          <TableCell>{{ voter.class_type }}</TableCell>
          <TableCell>{{ voter.created_at }}</TableCell>
          <TableCell class="text-right">
            <Button 
              @click="emit('activate', voter.id)"
              :disabled="isLoading"
              size="sm"
            >
              <span v-if="!isLoading">Activate</span>
              <span v-else>Activating...</span>
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>