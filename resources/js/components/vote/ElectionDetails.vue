<script setup lang="ts">
import CandidateCard from '@/components/vote/CandidateCard.vue';

defineProps<{
  elections: Array<{
    id: number;
    name: string;
    candidates: Array<{
      id: number;
      candidate_name: string;
      candidate_picture: string;
      position: { id: number; name: string } | null;
    }>;
  }>;
  selectedElection: number;
  selectedCandidates: { [positionId: number]: number };
}>();

const emit = defineEmits(['select-candidate']);

const groupByPosition = (candidates: any[]) => {
  return candidates.reduce((groups, candidate) => {
    if (!candidate.position) {
      console.warn("Candidate missing position:", candidate);
      return groups;
    }
    
    const positionId = candidate.position.id;
    if (!groups[positionId]) {
      groups[positionId] = {
        positionName: candidate.position.name,
        candidates: []
      };
    }
    groups[positionId].candidates.push(candidate);
    return groups;
  }, {} as Record<number, { positionName: string, candidates: any[] }>);
};
</script>

<template>
  <div 
    v-for="election in elections.filter(e => e.id === selectedElection)" 
    :key="election.id" 
    class="border rounded-lg p-4"
  >
    <h2 class="text-lg font-semibold">{{ election.name }}</h2>

    <div class="space-y-4 mt-4">
      <div 
        v-for="(group, positionId) in groupByPosition(election.candidates)" 
        :key="positionId"
        class="border-b pb-4"
      >
        <h3 class="font-semibold">{{ group.positionName }}</h3>
        <p v-if="!selectedCandidates[positionId]" class="text-sm text-red-500">
          Please select a candidate
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
          <CandidateCard
            v-for="candidate in group.candidates"
            :key="candidate.id"
            :candidate="candidate"
            :is-selected="selectedCandidates[positionId] === candidate.id"
            @click="$emit('select-candidate', positionId, candidate.id)"
          />
        </div>
      </div>
    </div>
  </div>
</template>