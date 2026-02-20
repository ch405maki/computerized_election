<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { useToast } from "vue-toastification";
import VotingLayout from '@/layouts/VotingLayout.vue';
import ElectionDetails from '@/components/vote/ElectionDetails.vue';
import VoteSubmitButton from '@/components/vote/VoteSubmitButton.vue';
import VotingHeader from '@/components/vote/VotingHeader.vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const logout = () => {
    router.post(route('voter.logout'));
};

const props = defineProps<{
  elections: Array<{
    id: number;
    name: string;
    candidates: Array<{
      id: number;
      candidate_name: string;
      candidate_picture: string;
      candidate_party: string;
      position: { id: number; name: string } | null;
    }>;
  }>;
  voter: { full_name: string; id: number; student_number: number; student_year: number; class_type: string; sex: string;} | null;
}>();

const toast = useToast();
const selectedElection = ref<number | null>(null);
const selectedCandidates = ref<{ [positionId: number]: number }>({});
const isVoting = ref(false);
const positionsInElection = ref<number[]>([]);

// Automatically select the first election on component mount
onMounted(() => {
  if (props.elections.length > 0) {
    selectedElection.value = props.elections[0].id;
  }

  
});

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
    toast.error("No election available.");
    return;
  }

  if (!allPositionsSelected.value) {
    toast.error("Please select one candidate for each position.");
    return;
  }

  if (!props.voter?.id) {
    toast.error("Voter information is missing.");
    return;
  }

  isVoting.value = true;

  try {
    const response = await axios.post('/api/votes', {
      voter_id: props.voter.id,
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
      selectedCandidates.value = {};
      
      toast.success(response.data.message);

      //Using the Logout Route to clear session
      setTimeout(() => {
        router.post(route('voter.logout'));
      }, 2000);
      
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
  <VotingLayout>
  <Head title="Vote" />
  <div class="mt-1 p-6 space-y-6 min-h-screen bg-cover bg-center bg-no-repeat">
    <!-- Show election name instead of selector when there's only one election -->
    <div v-if="elections.length === 1" class="mb-4">
        <h2 class="text-2xl font-semibold">{{ elections[0].name }}</h2>
    </div>

      <div v-if="elections.length === 0" class="text-muted-foreground">
        No active elections available.
      </div>
      

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
          :selected-candidates="selectedCandidates"
          :elections="elections"
          :selected-election="selectedElection"
          @vote="vote"
      />
  </div>
  </VotingLayout>
</template>
