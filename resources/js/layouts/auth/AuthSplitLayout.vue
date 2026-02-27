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
    <div class="relative grid h-dvh w-full items-center justify-center lg:grid-cols-12 overflow-hidden">
        
        <div class="relative hidden h-full flex-col bg-zinc-900 text-white lg:flex lg:col-span-7 xl:col-span-8 overflow-hidden">
            
            <transition-group name="fade">
                <div 
                    v-for="(img, index) in images" 
                    :key="img"
                    v-show="currentIndex === index"
                    class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-all"
                    :style="{ backgroundImage: `url('${img}')` }"
                >
                    <div class="absolute inset-0 bg-black/40"></div>
                </div>
            </transition-group>
        </div>

        <div class="px-8 lg:p-8 lg:col-span-5 xl:col-span-4 bg-white h-full flex flex-col justify-center">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[380px]">
                <div class="flex flex-col space-y-4 text-center items-center"> 
                    <Link :href="route('home')" class="relative z-20 flex flex-col items-center">
                        <div class="h-20 w-20 overflow-hidden"> 
                            <AppLogoIcon class="h-full w-full fill-current text-slate-900" />
                        </div>
                    </Link>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900" v-if="title">
                            {{ title }}
                        </h1>
                        <p class="text-sm text-muted-foreground" v-if="description">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
<style scoped>
.fade-enter-active {
  animation: kenburns 5s infinite alternate;
  transition: opacity 1s ease;
}

.fade-leave-active {
  transition: opacity 2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>