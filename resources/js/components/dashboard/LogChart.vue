<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
  logs: {
    id: number;
    action: string;
    created_at: string;
    user_name: string | null;
    student_number: string | null;
  }[];
}>();

const getActorName = (log: typeof props.logs[0]) => {
  return log.user_name ?? log.student_number ?? 'System';
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Recent Activity</CardTitle>
    </CardHeader>
    <CardContent>
        <div class="max-h-[500px] overflow-y-auto relative rounded-md">
            <Table>
                <TableHeader class="sticky top-0 z-10 bg-white drop-shadow-sm">
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
        </div>
    </CardContent>
  </Card>
</template>