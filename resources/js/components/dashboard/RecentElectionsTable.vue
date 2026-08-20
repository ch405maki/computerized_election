<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Election {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    votes_count: number;
}

defineProps<{
    elections: Election[];
    formatDate: (dateString: string) => string;
}>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Recent Elections</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="overflow-hidden rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Election</TableHead>
                            <TableHead>Period</TableHead>
                            <TableHead>Total Votes</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="election in elections" :key="election.id">
                            <TableCell>{{ election.name }}</TableCell>
                            <TableCell>{{ formatDate(election.start_date) }} to {{ formatDate(election.end_date) }}</TableCell>
                            <TableCell>{{ election.votes_count }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </CardContent>
    </Card>
</template>
