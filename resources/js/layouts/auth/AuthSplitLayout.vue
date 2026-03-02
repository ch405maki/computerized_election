<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = page.props.name;
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();

//  images
const images = [
    '/images/adminbg1.png',
    '/images/adminbg2.jpg', 
    '/images/adminbg3.png',
    '/images/ausl6.jpg'
];

const currentIndex = ref(0);
let timer: any = null;

// slideshow
onMounted(() => {
    timer = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % images.length;
    }, 3000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div class="relative min-h-dvh w-full overflow-hidden flex items-center justify-center lg:justify-end">
        
        <div class="absolute inset-0 z-0">
            <transition-group name="fade">
                <div 
                    v-for="(img, index) in images" 
                    :key="img"
                    v-show="currentIndex === index"
                    class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-all"
                    :style="{ backgroundImage: `url('${img}')` }"
                >
                    <div class="absolute inset-0 bg-black/50"></div>
                </div>
            </transition-group>
        </div>

        <div class="relative z-10 w-full px-6 flex justify-center lg:justify-end lg:pr-8 xl:pr-16">
            
            <div class="w-full max-w-[400px] rounded-3xl bg-white/10 backdrop-blur-xl border border-white/20 p-8 shadow-2xl space-y-6">
                
                <div class="flex flex-col space-y-4 text-center items-center"> 
                    <Link :href="route('home')" class="relative z-20 flex flex-col items-center">
                        <div class="h-20 w-20 overflow-hidden"> 
                            <AppLogoIcon class="h-full w-full fill-current text-white" />
                        </div>
                    </Link>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-white" v-if="title">
                            {{ title }}
                        </h1>
                        <p class="text-sm text-gray-200" v-if="description">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <div class="text-white">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active {
  animation: kenburns 8s infinite alternate;
  transition: opacity 1.5s ease;
}

.fade-leave-active {
  transition: opacity 1.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes kenburns {
  from { transform: scale(1); }
  to { transform: scale(1.1); }
}

/* Para siguradong puti ang labels at text sa loob ng slot */
:deep(label) {
    color: white !important;
}
:deep(input) {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}
:deep(input::placeholder) {
    color: rgba(255, 255, 255, 0.5) !important;
}
</style>