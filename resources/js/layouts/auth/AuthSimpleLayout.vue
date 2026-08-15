<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <!-- Removed w-9, added w-auto and gap-3 to place them side-by-side -->
                        <div class="mb-1 flex h-9 w-auto items-center justify-center gap-3 rounded-md">
                            
                            <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-white" />                            
                            <!-- Second Logo -->
                            <img 
                                src="/images/logo/comelec-logo.jpg" 
                                alt="Second Logo" 
                                class="size-9 rounded-full object-cover" 
                            />                            
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>