<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Eye } from "lucide-vue-next";

interface Election {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  votes_count: number;
}

defineProps<{
  elections: Election[];
  getElectionStatus: (election: {start_date: string; end_date: string}) => string;
  formatDate: (dateString: string) => string;
}>();
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
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>