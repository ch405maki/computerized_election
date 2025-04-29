<script setup lang="ts">
defineProps<{
  candidate: {
    id: number;
    candidate_name: string;
    candidate_picture: string;
    candidate_party: string;
  };
  isSelected: boolean;
}>();
</script>

<template>
  <div
    class="border rounded-lg p-4 cursor-pointer transition hover:bg-gray-100 dark:hover:bg-gray-800"
    :class="{
      'border-2 border-purple-500 bg-blue-50 dark:bg-blue-900/20': isSelected,
      'border-gray-200': !isSelected
    }"
  >
    <img 
      v-if="candidate.candidate_picture" 
      :src="`/storage/${candidate.candidate_picture}`" 
      alt="Candidate Picture" 
      class="w-20 h-20 rounded-full mx-auto object-cover border"
    />
    <p class="text-center mt-2 font-bold">{{ candidate.candidate_name }}</p>
    <p class="text-center mt-2">{{ candidate.candidate_party }}</p>
  </div>
</template>