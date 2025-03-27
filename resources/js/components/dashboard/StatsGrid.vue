<script setup lang="ts">
import StatCard from './StatCard.vue';
import { Layers, Activity, Users, BarChart2 } from "lucide-vue-next";

defineProps<{
  stats: {
    total_elections: number;
    active_elections: number;
    total_voters: number;
    votes_today: number;
    participation_rate: number;
  };
}>();
</script>

<template>
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <StatCard 
      title="Total Elections" 
      :value="stats.total_elections" 
      :description="`${stats.active_elections} currently active`"
      :icon="Layers"
    />
    <StatCard 
      title="Active Elections" 
      :value="stats.active_elections" 
      description="Currently running"
      :icon="Activity"
    />
    <StatCard 
      title="Registered Voters" 
      :value="stats.total_voters" 
      :description="`${stats.participation_rate}% participation`"
      :icon="Users"
    />
    <StatCard 
      title="Votes Today" 
      :value="stats.votes_today" 
      description="Recent activity"
      :icon="BarChart2"
    />
  </div>
</template>