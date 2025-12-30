<script setup lang="ts">
import AppShell from '@/components/AppShell.vue';
</script>

<template>
  <AppShell variant="header">
    <main class="flex min-h-screen w-full flex-col">
      <slot />
    </main>
  </AppShell>
</template>
