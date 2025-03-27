<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from "vue-toastification";
import ElectionSelector from '@/components/vote/ElectionSelector.vue';
import ElectionDetails from '@/components/vote/ElectionDetails.vue';
import VoteSubmitButton from '@/components/vote/VoteSubmitButton.vue';
import axios from 'axios';

const props = defineProps<{
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
}>();

const toast = useToast();
const selectedElection = ref<number | null>(null);
const selectedCandidates = ref<{ [positionId: number]: number }>({});
const isVoting = ref(false);
const positionsInElection = ref<number[]>([]);

watch(selectedElection, (newElectionId) => {
  if (newElectionId) {
    const election = props.elections.find(e => e.id === newElectionId);
    if (election) {
      positionsInElection.value = [...new Set(
        election.candidates
          .filter(c => c.position)
          .map(c => c.position!.id)
      )];
    }
  } else {
    positionsInElection.value = [];
  }
  selectedCandidates.value = {};
});

const allPositionsSelected = computed(() => {
  return positionsInElection.value.every(posId => 
    selectedCandidates.value[posId] !== undefined
  );
});

const selectedVotes = computed(() => {
  return Object.entries(selectedCandidates.value).map(([positionId, candidateId]) => ({
    position_id: parseInt(positionId),
    candidate_id: candidateId,
    election_id: selectedElection.value!
  }));
});

const selectCandidate = (positionId: number, candidateId: number) => {
  selectedCandidates.value = { 
    ...selectedCandidates.value, 
    [positionId]: candidateId 
  };
};

const vote = async () => {
  if (!selectedElection.value) {
    toast.error("Please select an election.");
    return;
  }

  if (!allPositionsSelected.value) {
    toast.error("Please select one candidate for each position.");
    return;
  }

  isVoting.value = true;

  try {
    const response = await axios.post('/api/votes', {
      voter_id: 1,
      votes: selectedVotes.value
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      timeout: 10000
    });

    if (response.data.success) {
      toast.success(response.data.message);
      selectedElection.value = null;
      selectedCandidates.value = {};
    } else {
      toast.error(response.data.message || "Vote submission failed.");
    }
  } catch (error: any) {
    if (error.response) {
      if (error.response.status === 422 && error.response.data.errors) {
        const errors = error.response.data.errors;
        Object.values(errors).flat().forEach((message: any) => {
          toast.error(message);
        });
      } else {
        toast.error(error.response.data?.message || "Failed to submit votes.");
      }
    } else if (error.code === 'ECONNABORTED') {
      toast.error("Request timed out. Please check your connection and try again.");
    } else if (error.request) {
      toast.error("Network error. Please check your connection.");
    } else {
      toast.error("An unexpected error occurred. Please try again.");
    }
    console.error("Voting error:", error);
  } finally {
    isVoting.value = false;
  }
};
</script>

<template>
  <Head title="Vote" />

  <AppLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold">Vote for Your Candidates</h1>

      <div v-if="elections.length === 0" class="text-muted-foreground">
        No active elections available.
      </div>

      <ElectionSelector 
        v-model="selectedElection"
        :elections="elections"
      />

      <ElectionDetails
        v-if="selectedElection"
        :elections="elections"
        :selected-election="selectedElection"
        :selected-candidates="selectedCandidates"
        @select-candidate="selectCandidate"
      />

      <VoteSubmitButton
        v-if="selectedElection"
        :is-voting="isVoting"
        :all-positions-selected="allPositionsSelected"
        @vote="vote"
      />
    </div>
  </AppLayout>
</template>