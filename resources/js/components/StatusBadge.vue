<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { computed } from 'vue';

interface Props {
  status: string;
}

const props = defineProps<Props>();

const variant = computed(() => {
  switch (props.status) {
    case 'published':
      return 'default';
    case 'draft':
      return 'secondary';
    case 'archived':
      return 'outline';
    default:
      return 'secondary';
  }
});
</script>

<template>
  <Badge :variant="variant">
    {{ status }}
  </Badge>
</template>
