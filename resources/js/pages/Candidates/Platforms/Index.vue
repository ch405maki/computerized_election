<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import WelcomeHeader from '@/components/welcome/WelcomeHeader.vue';
import WelcomeFooter from '@/components/welcome/WelcomeFooter.vue';

const candidatePositions = [
  {
    position: 'President',
    candidates: [
      {
        name: 'Candidate A',
        platform: 'Focus on student welfare, scholarships, and campus improvements.',
        card_bg: '/images/PRESIDENT.jpg',
        profile_pic: '/images/PRESIDENT.jpg', 
      },
      {
        name: 'Candidate B',
        platform: 'Promote transparency, digital transformation, and inclusive policies.',
        card_bg: '/images/bg-only.jpg',
        profile_pic: '/images/Vice-Internal.jpg',
      },
    ],
  },
  {
    position: 'Vice President Internal',
    candidates: [
      {
        name: 'Candidate C',
        platform: 'Strengthen community engagement, leadership training, and sustainability.',
        card_bg: '/images/bg-only.jpg',
        profile_pic: '/images/bg-only.jpg',
      },
      {
        name: 'Candidate D',
        platform: 'Advocate for mental health awareness, better clinic facilities, and student counseling.',
        card_bg: '/images/bg-only.jpg',
        profile_pic: '/images/bg-only.jpg',
      },
    ],
  },
  {
    position: 'Vice President External',
    candidates: [
      {
        name: 'Candidate E',
        platform: 'Improve campus Wi-Fi, provide free printing kiosks, and modernize the library.',
        card_bg: '/images/bg-only.jpg',
        profile_pic: '/images/bg-only.jpg',
      },
      {
        name: 'Candidate F',
        platform: 'Enhance sports programs, fund student organizations, and host more career fairs.',
        card_bg: '/images/bg-only.jpg',
        profile_pic: '/images/bg-only.jpg',
      },
    ],
  },
];

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
  <Head title="'Position' Platforms" />
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative"
    style="background-image: url('/images/AU BG.jpg');"
  >
    <WelcomeHeader class="bg-purple-200/25" />

    <div class="min-h-[calc(100vh-130px)] flex justify-center py-10 px-4">
      <div class="flex flex-col items-center w-full max-w-7xl gap-6">
        <h1 class="text-xl md:text-2xl font-bold text-white tracking-widest mb-4 text-center">
          Candidates' Platforms
        </h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pb-24 w-full justify-items-center">
          <template v-for="posData in candidatePositions" :key="posData.position">
            <Card
              v-for="candidate in posData.candidates"
              :key="candidate.name"
              @click="openPopup(posData)"
              class="relative overflow-hidden rounded-2xl w-full max-w-[330px] h-auto text-white border backdrop-blur-xl shadow-2xl bg-cover bg-center bg-no-repeat cursor-pointer hover:ring-2 hover:ring-purple-500 transition-all duration-300"
              :style="{ backgroundImage: `url(${candidate.card_bg || '/images/bg-only.jpg'})` }"
            >
              <div class="absolute inset-0 bg-black/60 pointer-events-none"></div>

              <div class="relative z-10 px-5 py-3 pointer-events-none">
                <CardHeader>
                  <CardTitle class="text-center tracking-widest mb-2 text-lg">
                    {{ candidate.name }}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <p class="text-sm leading-relaxed text-gray-100">
                    {{ candidate.platform }}
                  </p>
                </CardContent>
              </div>
            </Card>
          </template>
        </div>
      </div>
    </div>

    <div v-if="selectedPosition" @click.self="closePopup" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 overflow-y-auto">
      <div 
        class="relative w-full max-w-[750px] my-auto rounded-3xl overflow-hidden bg-cover bg-center border border-white/20 p-6 md:p-8 flex flex-col items-center shadow-2xl"
        :style="{ backgroundImage: `url('/images/bg-only.jpg')` }"
      >
        <div class="absolute inset-0 bg-black/75"></div>
        <button @click="closePopup" class="absolute top-4 right-4 text-white text-2xl font-bold z-[110]">&times;</button>

        <div class="relative z-10 w-full flex flex-col items-center">
          <h2 class="text-lg md:text-xl font-bold tracking-widest text-purple-200 uppercase mb-6 text-center">
            {{ selectedPosition.position }} Comparison
          </h2>
          
          <div class="flex flex-row items-center justify-center gap-2 md:gap-10 w-full">
            <div class="flex flex-col items-center gap-2 w-[45%]">
              <div @click="openZoom(selectedPosition.candidates[0].profile_pic)" class="w-full aspect-square border-2 border-white/30 rounded-lg overflow-hidden cursor-zoom-in shadow-lg">
                <img :src="selectedPosition.candidates[0].profile_pic || '/images/bg-only.jpg'" class="w-full h-full object-cover" />
              </div>
              <p class="text-white font-bold text-center text-xs md:text-lg">{{ selectedPosition.candidates[0].name }}</p>
            </div>

            <div class="text-xl font-black text-white/30 italic">VS</div>

            <div class="flex flex-col items-center gap-2 w-[45%]">
              <div @click="openZoom(selectedPosition.candidates[1].profile_pic)" class="w-full aspect-square border-2 border-white/30 rounded-lg overflow-hidden cursor-zoom-in shadow-lg">
                <img :src="selectedPosition.candidates[1].profile_pic || '/images/bg-only.jpg'" class="w-full h-full object-cover" />
              </div>
              <p class="text-white font-bold text-center text-xs md:text-lg">{{ selectedPosition.candidates[1].name }}</p>
            </div>
          </div>
        </div>

        <div v-if="zoomImage" @click="zoomImage = null" class="absolute inset-0 z-[120] bg-black/95 flex items-center justify-center">
           <img :src="zoomImage" class="max-w-[95%] max-h-[95%] object-contain shadow-2xl" />
        </div>
      </div>
    </div>

    <Button
      @click="goToVoting"
      class="fixed bottom-20 right-6 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-4 py-2 md:px-6 md:py-3 text-sm md:text-base shadow-lg transition z-50"
    >
      Go to Voting Page
    </Button>

    <WelcomeFooter />
  </div>
</template>