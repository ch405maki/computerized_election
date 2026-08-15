<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { computed, ref } from 'vue';

const props = defineProps<{
    logs: {
        id: number;
        action: string;
        created_at: string;
        user_name: string | null;
        student_number: string | null;
    }[];
}>();



const searchQuery = ref('');

const filteredLogs = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.logs;
    }

    const lowerCaseQuery = searchQuery.value.toLowerCase();

    return props.logs.filter((log) => {
        const action = log.action.toLowerCase();
        const date = new Date(log.created_at)
            .toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })
            .toLowerCase();

        const actorName = log.user_name?.toLowerCase() || log.student_number?.toLowerCase() || '';

        return actorName.includes(lowerCaseQuery) || action.includes(lowerCaseQuery) || date.includes(lowerCaseQuery);
    });
});
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
            <CardTitle>Recent Activity</CardTitle>
            <Input v-model="searchQuery" placeholder="Search logs..." class="max-w-sm" />
        </CardHeader>
        <CardContent class="max-h-[400px] overflow-y-auto">
            <div class="overflow-hidden rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow class="sticky top-0 z-10 bg-card shadow-sm hover:bg-card">
                            <TableHead>Action</TableHead>
                            <TableHead>Date</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in filteredLogs" :key="log.id">
                            <TableCell>{{ log.action }}</TableCell>
                            <TableCell>
                                {{
                                    new Date(log.created_at).toLocaleString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: true,
                                    })
                                }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredLogs.length === 0">
                            <TableCell colspan="3" class="py-4 text-center text-muted-foreground">
                                {{ searchQuery ? 'No matching logs found' : 'No logs found' }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </CardContent>
    </Card>
</template>
