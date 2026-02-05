<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import TitleHeader from '@/components/ui/title-header/header.vue';

const props = defineProps<{
    logs: Array<{
        id: number;
        action: string;
        created_at: string;
        user_name: string | null;
        voter_name: string | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: '/reports' },
    { title: 'Logs', href: '/reports/logs' },
];

const getActorName = (log: typeof props.logs[0]) => {
    return log.user_name ?? log.voter_name ?? 'System';
};
</script>

<template>
    <Head title="Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <TitleHeader 
                title="System Logs" 
                description="Audit trail of system changes and administrative actions." 
            />
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
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
            </div>
        </div>
    </AppLayout>
</template>