<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import WelcomeHeader from '@/components/welcome/WelcomeHeader.vue';
import WelcomeFooter from '@/components/welcome/WelcomeFooter.vue';

const props = defineProps<{
elections: Array<{
id: number;
name: string;
candidates: Array<{
    id: number;
    candidate_name: string;
    candidate_picture: string;
    platform_image?: string; // Ensure this matches your DB/Controller field
    position: { id: number; name: string } | null;
}>;
}>;
}>();

const candidatePositions = computed(() => {
if (!props.elections || props.elections.length === 0) return [];
const election = props.elections[0];

const grouped = election.candidates.reduce((acc: any, candidate) => {
const posName = candidate.position?.name || 'Other';
if (!acc[posName]) {
    acc[posName] = {
    position: posName,
    candidates: []
    };
}
acc[posName].candidates.push({
    name: candidate.candidate_name,
    profile_pic: candidate.candidate_picture || '/images/bg-only.jpg',
    platform_img: candidate.platform_image, // Passing the image path
});
return acc;
}, {});

return Object.values(grouped);
});

const selectedPosition = ref<any | null>(null);
const zoomImage = ref<string | null>(null);

const openPopup = (posData: any) => {
selectedPosition.value = posData;
};

const closePopup = () => {
selectedPosition.value = null;
zoomImage.value = null;
};

const openZoom = (img: string) => {
zoomImage.value = img;
};

const goToVoting = () => {
router.visit('/voting');
};
</script>

<template>
<Head title="Candidates' Platforms" />
<div
class="h-screen bg-cover bg-center bg-no-repeat relative flex flex-col overflow-hidden"
style="background-image: url('/images/AU BG.jpg');"
>
<WelcomeHeader class="bg-purple-200/25" />

<main class="flex-grow overflow-y-auto custom-scrollbar">
    <div class="flex flex-col items-center w-full max-w-7xl gap-4 mx-auto py-6 px-4">
    <h1 class="text-xl md:text-2xl font-bold text-white tracking-widest mb-2 text-center uppercase">
        Candidates' Platforms
    </h1>

    <div v-if="candidatePositions.length === 0" class="mt-20 text-white/50 text-center">
        <p class="text-lg italic">No active candidates found at the moment.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pb-32 w-full justify-items-center">
        <Card
        v-for="posData in candidatePositions"
        :key="posData.position"
        class="relative overflow-hidden rounded-2xl w-full max-w-[330px] min-h-[260px] text-white border border-white/20 backdrop-blur-md shadow-2xl bg-cover bg-center bg-no-repeat"
        :style="{ backgroundImage: `url('/images/bg-only.jpg')` }"
        >
        <div class="absolute inset-0 bg-black/75"></div>

        <div class="relative z-10 p-5 flex flex-col h-full justify-between">
            <div>
            <CardHeader class="p-0 mb-4">
                <CardTitle class="text-center tracking-widest text-lg font-bold border-b border-purple-500/40 pb-2 uppercase">
                {{ posData.position }}
                </CardTitle>
            </CardHeader>
            
            <CardContent class="p-0">
                <div class="space-y-3">
                <p class="text-[10px] uppercase tracking-[0.2em] text-purple-300 font-semibold mb-1">Candidates:</p>
                <div 
                    v-for="candidate in posData.candidates" 
                    :key="candidate.name"
                    class="flex items-center gap-2"
                >
                    <div class="w-1.5 h-1.5 rounded-full bg-purple-500"></div>
                    <p class="text-sm font-medium tracking-wide text-gray-100 italic">{{ candidate.name }}</p>
                </div>
                </div>
            </CardContent>
            </div>

            <div class="mt-6">
            <Button
                @click="openPopup(posData)"
                variant="outline"
                class="w-full h-10 bg-white/5 hover:bg-purple-600 border-white/20 text-white text-[10px] uppercase tracking-widest transition-all"
            >
                View Details & Platforms
            </Button>
            </div>
        </div>
        </Card>
    </div>
    </div>
</main>

<div v-if="selectedPosition" @click.self="closePopup" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 p-4 overflow-y-auto">
    <div 
    class="relative w-full max-w-6xl my-auto rounded-3xl overflow-hidden bg-cover bg-center border border-white/20 p-6 md:p-10 shadow-2xl"
    :style="{ backgroundImage: `url('/images/bg-only.jpg')` }"
    >
    <div class="absolute inset-0 bg-black/80"></div>
    <button @click="closePopup" class="absolute top-4 right-6 text-white text-4xl font-light z-[110] hover:text-purple-400">&times;</button>

    <div class="relative z-10 w-full flex flex-col items-center">
        <h2 class="text-xl md:text-2xl font-bold tracking-[0.3em] text-purple-200 uppercase mb-10 text-center border-b border-purple-500/30 pb-3 w-full">
        {{ selectedPosition.position }}
        </h2>
        
        <div class="grid w-full gap-8 h-full overflow-y-auto max-h-[65vh] custom-scrollbar px-2"
            :class="[
                selectedPosition.candidates.length === 1 ? 'grid-cols-1 max-w-md mx-auto' : 
                selectedPosition.candidates.length === 2 ? 'grid-cols-1 md:grid-cols-2' : 
                'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
            ]">
        
        <div v-for="candidate in selectedPosition.candidates" 
                :key="candidate.name" 
                class="flex flex-col items-center gap-6 p-6 rounded-2xl bg-white/5 border border-white/10"
        >
            <div class="flex flex-col items-center gap-3">
            <div class="w-24 h-24 md:w-28 md:h-28 border-2 border-purple-500/50 rounded-full overflow-hidden shadow-xl">
                <img :src="candidate.profile_pic" class="w-full h-full object-cover" />
            </div>
            <p class="text-white font-bold text-lg tracking-wide uppercase">{{ candidate.name }}</p>
            </div>

            <div class="w-full flex flex-col gap-2">
            <p class="text-[10px] uppercase tracking-widest text-purple-300 font-bold text-center">Campaign Platform</p>
            
            <div v-if="candidate.platform_img" 
                    @click="openZoom(candidate.platform_img)"
                    class="group relative w-full aspect-[3/4] rounded-xl overflow-hidden border border-white/20 cursor-zoom-in bg-black/40 shadow-inner"
            >
                <img :src="candidate.platform_img" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110" />
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="text-white text-xs font-bold tracking-tighter bg-purple-600/80 px-3 py-1 rounded-full uppercase">Click to Enlarge</span>
                </div>
            </div>

            <div v-else class="w-full aspect-[3/4] rounded-xl border border-dashed border-white/20 flex flex-col items-center justify-center bg-black/20 text-white/30 italic text-xs">
                No platform image uploaded
            </div>
            </div>
        </div>

        </div>
    </div>
    </div>
</div>

<div v-if="zoomImage" @click="zoomImage = null" class="fixed inset-0 z-[200] bg-black/98 flex flex-col items-center justify-center p-4">
    <button class="absolute top-6 right-8 text-white text-5xl font-thin">&times;</button>
    <img :src="zoomImage" class="max-w-full max-h-[90vh] object-contain shadow-2xl rounded-lg border border-white/10" />
    <p class="text-white/40 mt-4 text-[10px] tracking-[0.4em] uppercase">Click anywhere to close</p>
</div>

<Button
    @click="goToVoting"
    class="fixed bottom-24 right-6 bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-4 rounded-full shadow-2xl transition-all hover:scale-105 z-50 uppercase tracking-widest text-xs"
>
    Go to Voting Page
</Button>

<WelcomeFooter class="shrink-0" />
</div>
</template>