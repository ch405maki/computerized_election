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
        <label class="mb-1 block text-sm font-medium">Select Election</label>
        <select v-model="modelValue" class="w-full max-w-md rounded-md border p-2">
            <option :value="null">Choose an election</option>
            <option v-for="election in elections" :key="election.id" :value="election.id">
                {{ election.name }}
            </option>
        </select>
    </div>
</template>
