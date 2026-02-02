<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell 
        class="flex-col" 
        style="background-image: url('/images/bg.jpg'); 
        background-repeat: no-repeat; 
        background-size: cover; 
        background-position: center;"
    >
        <AppHeader :breadcrumbs="breadcrumbs" class="sticky top-0"/>
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
