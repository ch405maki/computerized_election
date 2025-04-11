<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface Election {
  id: number;
  name: string;
  status: 'active' | 'upcoming' | 'completed';
  start_date: string;
  end_date: string;
}

const props = defineProps<{
  elections: Election[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'History', href: '/results' },
];
</script>

<template>
  <Head title="Results" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Election Name</TableHead>
            <TableHead class="text-right">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="election in elections" :key="election.id">
            <TableCell class="font-medium">{{ election.name }}</TableCell>
            <TableCell class="text-right">
              <Link 
                :href="route('results.show', election.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
              >
                View Results
              </Link>
            </TableCell>
          </TableRow>
          <TableRow v-if="elections.length === 0">
            <TableCell colspan="2" class="text-center py-4 text-muted-foreground">
              No elections found
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </AppLayout>
</template>