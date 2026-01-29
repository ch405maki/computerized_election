<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface CandidateVote {
  name: string;
  votes: number;
  party: string;
  image?: string;
}

interface PositionVotes {
  position: string;
  candidates: CandidateVote[];
}

defineProps<{
  voteRanking: PositionVotes[];
  isLoading: boolean;
}>();
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Vote Ranking</CardTitle>
    </CardHeader>
    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Position</TableHead>
            <TableHead>Candidate</TableHead>
            <TableHead>Party</TableHead>
            <TableHead class="text-right">Votes</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="voteRanking.length > 0">
            <template v-for="group in voteRanking" :key="group.position">
              <TableRow>
                <TableCell class="font-bold" colspan="4">{{ group.position }}</TableCell>
              </TableRow>
              <TableRow 
                v-for="(candidate, index) in group.candidates" 
                :key="candidate.name"
                :class="index === 0 ? 'bg-muted/50' : ''"
                >
                <TableCell></TableCell>
                <TableCell class="font-medium">
                    <div class="flex items-center gap-3">
                    <img 
                        v-if="candidate.image" 
                        src="/images/anonymous.jpg"  
                        class="h-8 w-8 rounded-full object-cover"
                        :alt="candidate.name"
                    >
                    <span>{{ index + 1 }}. Anonymous</span>
                    </div>
                </TableCell>
                <TableCell class="font-medium">Anonymous</TableCell>
                <TableCell class="text-right">{{ candidate.votes }}</TableCell>
                </TableRow>

            </template>
          </template>
          <template v-else>
            <TableRow>
              <TableCell colspan="4" class="text-center py-8 text-muted-foreground">
                {{ isLoading ? 'Loading vote rankings...' : 'No vote ranking data available' }}
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>