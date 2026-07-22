<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from '@/components/ui/button';
import VoterEditSheet from "@/components/voter/VoterEditSheet.vue";
import DeleteVoterDialog from "@/components/voter/DeleteVoterDialog.vue";
import { FilePenLine } from "lucide-vue-next";
import { usePage } from '@inertiajs/vue3';

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

// --- Permissions Logic ---
const page = usePage();
const permissions = computed(() => (page.props.auth?.user as any)?.permissions || {});

const canEdit = computed(() => permissions.value.editVoter);
const canDelete = computed(() => permissions.value.deleteVoter);
const hasAnyActionPermission = computed(() => canEdit.value || canDelete.value);

// --- Local State ---
const localVoters = ref<Voter[]>([...props.voters]);
const editingVoter = ref<Voter | null>(null);

// Keep local state synced if Inertia refreshes the props data
watch(() => props.voters, (newVoters) => {
  localVoters.value = [...newVoters];
});

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
        <TableHead v-if="hasAnyActionPermission" class="text-right">Actions</TableHead>
      </TableRow>
    </TableHeader>
    
    <TableBody>
      <template v-if="localVoters.length > 0">
        <TableRow v-for="voter in localVoters" :key="voter.id">
          <TableCell class="font-medium">{{ voter.student_number }}</TableCell>
          <TableCell>{{ voter.full_name }}</TableCell>
          <TableCell>{{ voter.student_year }}</TableCell>
          <TableCell>{{ voter.sex }}</TableCell>
          
          <TableCell v-if="hasAnyActionPermission" class="text-right flex justify-end items-center space-x-2">
            <Button 
              v-if="canEdit"
              variant="outline" 
              size="sm" 
              @click="editingVoter = voter"
            >
              <FilePenLine class="w-4 h-4"/>
            </Button>

            <DeleteVoterDialog 
              v-if="canDelete" 
              :voter="voter" 
            />
          </TableCell>
        </TableRow>
      </template>
      
      <TableRow v-else>
        <TableCell 
          :colspan="hasAnyActionPermission ? 5 : 4" 
          class="text-center py-4 text-muted-foreground"
        >
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