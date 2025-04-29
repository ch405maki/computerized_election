<script setup lang="ts">
import CandidateCard from '@/components/vote/CandidateCard.vue';
import { ref } from 'vue';

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
const activePositionIndex = ref(0); // Track which position is currently visible

const groupByPosition = (candidates: any[]) => {
  return candidates.reduce((groups, candidate) => {
    if (!candidate.position) {
      console.warn("Candidate missing position:", candidate);
      return groups;
    }
    
    const positionId = candidate.position.id;
    if (!groups[positionId]) {
      groups[positionId] = {
        positionId: positionId,
        positionName: candidate.position.name,
        candidates: []
      };
    }
    groups[positionId].candidates.push(candidate);
    return groups;
  }, {} as Record<number, { positionId: number, positionName: string, candidates: any[] }>);
};

const nextPosition = () => {
  activePositionIndex.value++;
};

const prevPosition = () => {
  activePositionIndex.value--;
};
</script>

<template>
  <div 
    v-for="election in elections.filter(e => e.id === selectedElection)" 
    :key="election.id" 
    class="border rounded-lg p-4"
  >
    <div class="space-y-4">
      <template v-for="(group, positionId, index) in groupByPosition(election.candidates)" :key="positionId">
        <div 
          v-if="index === activePositionIndex"  
          class="pb-4"
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

          <div class="flex justify-between mt-4">
            <button
              v-if="activePositionIndex > 0"
              @click="prevPosition"
              class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
            >
              Previous
            </button>
            <div v-else></div> <!-- Empty div for spacing when no Previous button -->
            
            <button
              v-if="activePositionIndex < Object.keys(groupByPosition(election.candidates)).length - 1"
              @click="nextPosition"
              :disabled="!selectedCandidates[positionId]"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-blue-300 disabled:cursor-not-allowed"
            >
              Next
            </button>
            <div v-else></div> <!-- Empty div for spacing when no Next button -->
          </div>
        </div>
      </template>

      <div v-if="activePositionIndex >= Object.keys(groupByPosition(election.candidates)).length" class="text-center py-4">
        <p class="text-lg font-semibold">You have completed all positions!</p>
      </div>
    </div>
  </div>
</template>