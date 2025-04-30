<script setup lang="ts">
import UserDropdown from './UserDropdown.vue';

defineProps<{
  fullName: string;
  studentNumber: number;
}>();

defineEmits(['logout']);
</script>

<template>
  <div class=" sticky top-0 flex items-center justify-between px-6 bg-purple-800 shadow-sm border-b">
    <div class="flex items-center  py-2">
      <h1 class="flex items-center justify-center space-x-4">
        <img src="/images/logo/au-logo.png" alt="au-Logo" class="h-[47px] w-auto">
        <img src="/images/logo/ausl.png" alt="ausl-Logo" class="h-[42px] w-auto">
        <img src="/images/logo/alsg-logo.jpg" alt="alsg-Logo" class="object-cover rounded-full h-[40px] w-auto">
        <img src="/images/logo/comelec-logo.jpg" alt="comelec-Logo" class="object-cover rounded-full h-[41px] w-auto">
        <span class="text-white text-lg font-semibold font-black tracking-widest uppercase">AUSL Election System</span>
      </h1>
    </div>
    
    <div v-if="fullName" class="flex items-center gap-2">
      <UserDropdown
        :full-name="fullName"
        :student-number="studentNumber"
        @logout="$emit('logout')"
      />
      <div class="hidden md:flex items-center gap-2">
        <h1 class="text-white"><span class="text-sm tracking-widest uppercase">{{ fullName }}</span></h1>
      </div>
    </div>
  </div>
</template>