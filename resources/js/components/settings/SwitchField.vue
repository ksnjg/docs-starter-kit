<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

interface Props {
  label: string;
  description?: string;
  modelValue: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();
</script>

<template>
  <div class="flex items-center justify-between rounded-lg border p-3">
    <div>
      <Label class="text-base">{{ label }}</Label>
      <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
    </div>
    <Switch :checked="modelValue" @update:checked="emit('update:modelValue', $event)" />
  </div>
</template>
