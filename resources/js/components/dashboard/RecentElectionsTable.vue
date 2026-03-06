<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Eye } from "lucide-vue-next";
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

interface Election {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  votes_count: number;
}

const props = defineProps<{
  elections: Election[];
  getElectionStatus: (election: {start_date: string; end_date: string}) => string;
  formatDate: (dateString: string) => string;
}>();

const toast  = useToast();

const handleViewClick = (election: Election) => {
  const status = props.getElectionStatus(election);
  
  if (status === 'active') {
    toast.error("This election is still ongoing. Results are not yet available.");
  } else {
    router.get(`/results/${election.id}`);
  }
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Recent Elections</CardTitle>
    </CardHeader>
    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Election</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Period</TableHead>
            <TableHead>Votes</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="election in elections" :key="election.id">
            <TableCell class="font-medium">{{ election.name }}</TableCell>
            <TableCell>
              <Badge 
                :variant="getElectionStatus(election) === 'active' ? 'default' : 
                         getElectionStatus(election) === 'upcoming' ? 'secondary' : 'outline'"
              >
                {{ getElectionStatus(election) }}
              </Badge>
            </TableCell>
            <TableCell>{{ formatDate(election.start_date) }} to {{ formatDate(election.end_date) }}</TableCell>
            <TableCell>{{ election.votes_count }}</TableCell>
            <TableCell class="text-right">
              <Button variant="ghost" size="sm" @click="handleViewClick(election)">
                <Eye class="h-4 w-4 mr-1" />
                View
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>