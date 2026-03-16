<script setup lang="ts">
import { ref, computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';

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

const searchQuery = ref('');

const filteredLogs = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.logs;
  }
  
  const lowerCaseQuery = searchQuery.value.toLowerCase();
  
  return props.logs.filter((log) => {
    const actorName = getActorName(log).toLowerCase();
    const action = log.action.toLowerCase();
    const date = new Date(log.created_at).toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }).toLowerCase();
    
    // Search by actor name or action
    return actorName.includes(lowerCaseQuery) || action.includes(lowerCaseQuery) || date.includes(lowerCaseQuery);
  });
});
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
      <CardTitle>Recent Activity</CardTitle>
      <Input 
        v-model="searchQuery" 
        placeholder="Search logs..." 
        class="max-w-sm"
      />
    </CardHeader>
    <CardContent class="max-h-[400px] overflow-y-auto">
        <Table>
            <TableHeader>
                <TableRow class="sticky top-0 bg-card hover:bg-card z-10 shadow-sm">
                    <TableHead>Actor</TableHead>
                    <TableHead>Action</TableHead>
                    <TableHead>Date</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="log in filteredLogs" :key="log.id">
                    <TableCell>{{ getActorName(log) }}</TableCell>
                    <TableCell>{{ log.action }}</TableCell>
                    <TableCell>
                        {{ new Date(log.created_at).toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) }}
                    </TableCell>
                </TableRow>
                <TableRow v-if="filteredLogs.length === 0">
                    <TableCell colspan="3" class="text-center py-4 text-muted-foreground">
                        {{ searchQuery ? 'No matching logs found' : 'No logs found' }}
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </CardContent>
  </Card>
</template>