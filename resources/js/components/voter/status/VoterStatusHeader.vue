<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { KeyRound, Search } from "lucide-vue-next";
import { Input } from '@/components/ui/input'
import { ref } from 'vue';

defineProps<{
  hasInactiveVoters: boolean;
  isActivatingAll: boolean;
}>();

const emit = defineEmits(['activateAll', 'search']);

const searchQuery = ref('');

const handleSearch = () => {
  emit('search', searchQuery.value);
};
</script>

<template>
  <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
    <!-- Search Field -->
    <div class="relative w-full sm:w-64">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
      <Input
        placeholder="Search voters..."
        class="pl-9"
        v-model="searchQuery"
        @input="handleSearch"
      />
    </div>

    <!-- Activate All Button -->
    <Button 
      variant="default" 
      v-if="hasInactiveVoters" 
      @click="$emit('activateAll')"
      :disabled="isActivatingAll"
    >
      <KeyRound class="mr-2 h-4 w-4" />
      <span v-if="!isActivatingAll">Activate All</span>
      <span v-else>Activating...</span>
    </Button>
  </div>
</template>