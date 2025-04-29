<script setup lang="ts">
import UserDropdown from './UserDropdown.vue';

defineProps<{
  fullName: string;
  studentNumber: number;
}>();

defineEmits(['logout']);
</script>

<template>
  <div class="flex items-center justify-between px-6 py-4 bg-white shadow-sm border-b">
    <div class="flex items-center space-x-4">
      <h1 class="text-xl font-bold">Voting System</h1>
    </div>
    
    <div v-if="fullName" class="flex items-center gap-2">
      <UserDropdown
        :full-name="fullName"
        :student-number="studentNumber"
        @logout="$emit('logout')"
      />
      <div class="hidden md:flex items-center gap-2">
        <span class="text-gray-700">{{ fullName }}</span>
      </div>
    </div>
  </div>
</template>