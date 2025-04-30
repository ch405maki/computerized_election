<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
  logs: {
    id: number;
    action: string;
    created_at: string;
    user_name: string | null;
    voter_name: string | null;
  }[];
}>();

const getActorName = (log: typeof props.logs[0]) => {
  return log.user_name ?? log.voter_name ?? 'System';
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Recent Activity</CardTitle>
    </CardHeader>
    <CardContent>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Actor</TableHead>
                    <TableHead>Action</TableHead>
                    <TableHead>Date</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="log in logs" :key="log.id">
                    <TableCell>{{ getActorName(log) }}</TableCell>
                    <TableCell>{{ log.action }}</TableCell>
                    <TableCell>
                        {{ new Date(log.created_at).toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) }}
                    </TableCell>
                </TableRow>
                <TableRow v-if="logs.length === 0">
                    <TableCell colspan="3" class="text-center py-4 text-muted-foreground">
                        No logs found
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </CardContent>
  </Card>
</template>