<script setup lang="ts">
import { CirclePower } from "lucide-vue-next";
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { computed } from 'vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  fullName: string;
  studentNumber: number;
  studentYear: number;
  classType: string; 
  sex: string;
}>();

const emit = defineEmits(['logout']);

const initials = computed(() => {
  if (!props.fullName) return '??';
  return props.fullName
    .split(' ')
    .map(name => name[0])
    .join('')
    .toUpperCase();
});
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="relative h-10 w-10 rounded-full p-0">
        <Avatar class="h-10 w-10">
          <AvatarFallback>{{ initials }}</AvatarFallback>
        </Avatar>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="w-56" align="end">
      <DropdownMenuLabel>
        <div class="flex flex-col space-y-1">
          <p class="text-sm font-medium">{{ fullName }}</p>
          <p class="text-xs text-muted-foreground">
            Student Number: {{ studentNumber }}
          </p>
          <p class="text-xs text-muted-foreground">
            Student Year: {{ studentYear }}
          </p>
          <p class="text-xs text-muted-foreground">
            Class Type: {{ classType }}
          </p>
          <p class="text-xs text-muted-foreground">
            Sex: {{ sex }}
          </p>
        </div>
      </DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem 
        @click="emit('logout')" 
        class="text-red-600 focus:text-red-600"
      >
        <CirclePower class="mr-2 h-4 w-4" />
        <span>Log out</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>