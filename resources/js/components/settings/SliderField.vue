<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Slider } from '@/components/ui/slider';
import { computed } from 'vue';

interface Props {
  label: string;
  modelValue: number;
  min: number;
  max: number;
  step?: number;
  unit?: string;
  error?: string;
  description?: string;
}

const props = withDefaults(defineProps<Props>(), {
  step: 1,
  unit: '',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void;
}>();

const sliderValue = computed(() => [props.modelValue]);

const handleUpdate = (value: number[] | undefined) => {
  if (value && value.length > 0) {
    emit('update:modelValue', value[0]);
  }
};
</script>

<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <Label>{{ label }}</Label>
      <span class="text-sm text-muted-foreground">{{ modelValue }}{{ unit }}</span>
    </div>
    <Slider
      :model-value="sliderValue"
      :min="min"
      :max="max"
      :step="step"
      @update:model-value="handleUpdate"
    />
    <p v-if="description" class="text-xs text-muted-foreground">{{ description }}</p>
    <InputError :message="error" />
  </div>
</template>
