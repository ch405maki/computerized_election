<script setup lang="ts">
import UserDropdown from './UserDropdown.vue';

defineProps<{
    fullName: string;
    studentNumber: number;
    studentYear: number;
    classType: string;
    sex: string;
}>();

defineEmits(['logout']);
</script>

<template>
    <div class="sticky top-0 flex items-center justify-between border-b bg-purple-800 px-6 shadow-sm">
        <div class="flex items-center py-2">
            <h1 class="flex items-center justify-center space-x-4">
                <img src="/images/logo/au-logo.png" alt="au-Logo" class="h-[47px] w-auto" />
                <img src="/images/logo/ausl.png" alt="ausl-Logo" class="h-[42px] w-auto" />
                <img src="/images/logo/alsg-logo.jpg" alt="alsg-Logo" class="h-[40px] w-auto rounded-full object-cover" />
                <img src="/images/logo/comelec-logo.jpg" alt="comelec-Logo" class="h-[41px] w-auto rounded-full object-cover" />
                <span class="text-lg font-black uppercase tracking-widest text-white">AUSL Election System</span>
            </h1>
        </div>

        <div v-if="fullName" class="flex items-center gap-2">
            <UserDropdown
                :full-name="fullName"
                :student-number="studentNumber"
                :student-year="studentYear"
                :class-type="classType"
                :sex="sex"
                @logout="$emit('logout')"
            />
            <div class="hidden items-center gap-2 md:flex">
                <h1 class="text-white">
                    <span class="text-sm uppercase tracking-widest">{{ fullName }}</span>
                </h1>
            </div>
        </div>
    </div>
</template>
