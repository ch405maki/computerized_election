<script setup lang="ts">
defineProps<{
  elections: Array<{
    id: number;
    name: string;
  }>;
}>();

const modelValue = defineModel<number | null>();
</script>

<template>
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Select Election</label>
    <select 
      v-model="modelValue"
      class="border rounded-md p-2 w-full max-w-md"
    >
      <option :value="null">Choose an election</option>
      <option 
        v-for="election in elections" 
        :key="election.id" 
        :value="election.id"
      >
        {{ election.name }}
      </option>
    </select>
  </div>
</template>