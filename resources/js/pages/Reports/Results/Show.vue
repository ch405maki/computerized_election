<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Sheet, FileDown } from 'lucide-vue-next';
import { computed } from 'vue';
// Import the new PDF utility you just created
import { exportElectionResultsPdf } from '@/lib/exportElectionResultsPdf';

interface CandidateResult {
  id: number;
  candidate_name: string;
  candidate_party: string | null;
  candidate_picture: string | null;
  votes: number;
}

const props = defineProps<{
  election: {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
  } | null;
  positions?: Record<string, CandidateResult[]>;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Results History', href: '/reports/results' },
  { title: props.election?.name || 'Election Results', href: route('results.show', props.election?.id) },
]);

const exportToExcel = () => {
  const exportUrl = route('results.export', props.election?.id);
  window.open(exportUrl, '_blank');
};

// --- NEW PDF DOWNLOAD FUNCTION ---
const downloadPDF = async () => {
  if (!props.election || !props.positions) {
    alert("Election data is not fully loaded yet.");
    return;
  }
  
  // Call the utility function, passing the required props
  await exportElectionResultsPdf(props.election, props.positions);
};

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
  <Head :title="`Results - ${election?.name || 'Election'}`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-8">
      
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold mb-2">Election Results</h1>
          <h2 class="text-xl">{{ election?.name || 'Loading...' }}</h2>
          <p v-if="election" class="text-muted-foreground">
            {{ formattedDate(election.start_date) }} - 
            {{ formattedDate(election.end_date) }}
          </p>
        </div>
        
        <div class="flex flex-col gap-2 items-end">
          <!-- <Button @click="exportToExcel" variant="default">
            <Sheet class="w-4 h-4 mr-2"/>
            <span>Export to Excel</span>
          </Button> -->
          
          <Button @click="downloadPDF" variant="destructive">
            <FileDown class="w-4 h-4 mr-2"/>
            <span>Download PDF</span>
          </Button>
        </div>
      </div>

      <div v-if="!positions" class="text-center py-8">
        <p class="text-muted-foreground">Loading election results...</p>
      </div>

      <div v-else class="space-y-8">
        <template v-if="Object.keys(positions).length > 0">
          
          <div v-for="(candidates, positionName) in positions" :key="positionName" class="rounded-lg border shadow-sm">
            
            <h3 class="text-lg text-white font-semibold px-4 py-2 bg-[#6b21a8] rounded-t">
              {{ positionName }}
            </h3>
            
            <Table>
              <TableHeader>
                <TableRow class="hover:bg-transparent">
                  <TableHead class="w-[80px] text-center">Rank</TableHead>
                  <TableHead>Candidate Name</TableHead>
                  <TableHead>Party</TableHead>
                  <TableHead class="text-right">Votes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow 
                  v-for="(candidate, index) in [...candidates].sort((a, b) => b.votes - a.votes)" 
                  :key="candidate.id"
                >
                  <TableCell class="text-center">{{ index + 1 }}</TableCell>
                  <TableCell>{{ candidate.candidate_name }}</TableCell>
                  <TableCell>{{ candidate.candidate_party || 'Independent' }}</TableCell>
                  <TableCell class="text-right">{{ candidate.votes }}</TableCell>
                </TableRow>
                
                <TableRow class="hover:bg-transparent bg-muted/50">
                  <TableCell></TableCell>
                  <TableCell></TableCell>
                  <TableCell class="font-bold text-black pt-4">TOTAL VOTES</TableCell>
                  <TableCell class="text-right font-bold text-black pt-4">
                    {{ [...candidates].reduce((total, c) => total + c.votes, 0) }}
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </template>
        <div v-else class="text-center py-8 text-muted-foreground">
          No results available for this election.
        </div>
      </div>

    </div>
  </AppLayout>
</template>