<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div class="space-y-1">
    <h1 class="text-xl font-bold tracking-tight">
      <slot name="title">{{ title }}</slot>
    </h1>
    <p v-if="description || $slots.description" class="text-sm text-muted-foreground">
      <slot name="description">{{ description }}</slot>
    </p>
  </div>
</template>