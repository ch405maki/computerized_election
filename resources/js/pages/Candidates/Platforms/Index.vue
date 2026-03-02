<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import WelcomeHeader from '@/components/welcome/WelcomeHeader.vue';
import WelcomeFooter from '@/components/welcome/WelcomeFooter.vue';

// Example candidate data — replace with Inertia props or backend data later
const candidates = [
  {
    name: 'Candidate A',
    platform: 'Focus on student welfare, scholarships, and campus improvements.',
  },
  {
    name: 'Candidate B',
    platform: 'Promote transparency, digital transformation, and inclusive policies.',
  },
  {
    name: 'Candidate C',
    platform: 'Strengthen community engagement, leadership training, and sustainability.',
  },
];

// Redirect function
const goToVoting = () => {
  router.visit('/voting'); // matches your vote.voting route
};
</script>

<template>
<Head title="Candidates' Platforms" />
<div
class="min-h-screen bg-cover bg-center bg-no-repeat relative"
style="background-image: url('/images/AU BG.jpg');"
>
<WelcomeHeader class="bg-purple-200/25" />

<div class="min-h-[calc(100vh-130px)] grid place-items-center">
    <div class="flex flex-col items-center gap-6">
    <!-- Page Title -->
    <h1 class="text-2xl font-bold text-white tracking-widest mb-4">
        Candidates' Platforms
    </h1>

    <!-- Candidate Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
        v-for="candidate in candidates"
        :key="candidate.name"
        class="rounded-2xl w-[330px] h-auto text-white bg-transparent-100 border backdrop-blur-xl px-5 shadow-2xl"
        >
        <CardHeader>
            <CardTitle class="text-center tracking-widest mb-2">
            {{ candidate.name }}
            </CardTitle>
        </CardHeader>
        <CardContent>
            <p class="text-sm leading-relaxed">
            {{ candidate.platform }}
            </p>
        </CardContent>
        </Card>
    </div>
    </div>
</div>

<!-- Voting Button (lower right corner) -->
<Button
@click="goToVoting"
class="fixed bottom-20 right-6 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 shadow-lg transition"
>
Go to Voting Page
</Button>

<WelcomeFooter />
</div>
</template>