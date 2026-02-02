<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ScrollText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3'
import TitleHeader from '@/components/ui/title-header/header.vue';

function viewResults(id: number | string) {
  router.visit(route('results.show', id))
}

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
    <div class="flex flex-col gap-4 p-4 ">
      <TitleHeader
        title="Election Results" 
        description="Breakdown of vote counts and winning statistics." 
      />
      <div class="border rounded-lg overflow-hidden">
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
              <Button 
                v-if="election.status === 'completed'"
                @click="viewResults(election.id)"
                class="inline-flex items-center px-4 py-2 bg-zinc-800 text-white rounded hover:bg-zinc-700 transition-colors"
              >
                <ScrollText />
                View Results
              </Button>
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
    </div>


  </AppLayout>
</template>