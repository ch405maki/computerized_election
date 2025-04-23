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
  { title: 'Results History', href: '/results' },
];

const formattedDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

</script>

<template>
  <Head title="Election Results" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">
      <h1 class="text-2xl font-bold">Election Results</h1>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Election Name</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Election Period</TableHead>
            <TableHead class="text-right">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="election in elections" :key="election.id">
            <TableCell class="font-medium">{{ election.name }}</TableCell>
            <TableCell>
              <span :class="{
                'text-green-600': election.status === 'completed',
                'text-blue-600': election.status === 'active',
                'text-gray-600': election.status === 'upcoming'
              }">
                {{ election.status.charAt(0).toUpperCase() + election.status.slice(1) }}
              </span>
            </TableCell>
            <TableCell>
              {{ formattedDate(election.start_date) }} - 
              {{ formattedDate(election.end_date) }}
            </TableCell>
            <TableCell class="text-right">
              <Link 
                v-if="election.status === 'completed'"
                :href="route('results.show', election.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
              >
                View Results
              </Link>
              <span v-else class="text-gray-500">
                Results unavailable
              </span>
            </TableCell>
          </TableRow>
          <TableRow v-if="elections.length === 0">
            <TableCell colspan="4" class="text-center py-4 text-muted-foreground">
              No elections found
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </AppLayout>
</template>