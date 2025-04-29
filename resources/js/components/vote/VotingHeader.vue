<script setup lang="ts">
import UserDropdown from './UserDropdown.vue';

defineProps<{
  fullName: string;
  studentNumber: number;
}>();

defineEmits(['logout']);
</script>

<template>
  <div class="flex items-center justify-between px-6 py-4 bg-purple-800 shadow-sm border-b">
    <div class="flex items-center space-x-4">
      <h1 class="flex items-center space-x-4">
        <img src="/images/logo/au-logo.png" alt="au-Logo" class="h-[65px] w-auto">
        <img src="/images/logo/ausl.png" alt="ausl-Logo" class="h-[60px] w-auto">
        <img src="/images/logo/comelec-logo.jpg" alt="comelec-Logo" class="object-cover rounded-full h-[60px] w-auto">
        <img src="/images/logo/alsg-logo.jpg" alt="alsg-Logo" class="object-cover rounded-full h-[60px] w-auto">
        <span class="text-white">AUSL Election System</span>
      </h1>
    </div>
    
    <div v-if="fullName" class="flex items-center gap-2">
      <UserDropdown
        :full-name="fullName"
        :student-number="studentNumber"
        @logout="$emit('logout')"
      />
      <div class="hidden md:flex items-center gap-2">
        <span class="text-white">{{ fullName }}</span>
      </div>
    </div>
  </div>
</template>