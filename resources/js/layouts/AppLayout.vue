<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- The global responsive wrapper -->
        <main class="mx-auto flex w-full max-w-full flex-col gap-4 p-4 md:gap-6 md:p-6">
            <slot />
        </main>
        
    </AppLayout>
</template>
