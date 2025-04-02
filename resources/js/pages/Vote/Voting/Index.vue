<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

defineProps<{
    voter: { full_name: string } | null;
}>();

const logout = () => {
    router.post(route('voter.logout'));
};
</script>

<template>
    <div class="p-6 text-center">
        <h1 class="text-2xl font-bold">Welcome to Voting Page</h1>
        <p v-if="voter" class="mt-2 text-lg">Hello, {{ voter.full_name }}!</p>

        <Button class="mt-4 bg-red-500 hover:bg-red-600" @click="logout">
            Logout
        </Button>
    </div>
</template>
