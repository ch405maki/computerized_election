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
        class="cursor-pointer rounded-lg border p-4 transition hover:bg-gray-100"
        :class="{
            'border-2 border-purple-500 bg-blue-50': isSelected,
            'border-gray-200': !isSelected,
        }"
    >
        <img
            v-if="candidate.candidate_picture"
            :src="`/storage/${candidate.candidate_picture}`"
            alt="Candidate Picture"
            class="mx-auto h-20 w-20 rounded-full border object-cover"
        />

        <img v-else src="/images/anonymous.jpg" alt="No Uploaded Image" class="mx-auto h-20 w-20 rounded-full border object-cover" />

        <p class="mt-2 text-center font-bold">{{ candidate.candidate_name }}</p>
        <p class="mt-2 text-center">{{ candidate.candidate_party }}</p>
    </div>
</template>
