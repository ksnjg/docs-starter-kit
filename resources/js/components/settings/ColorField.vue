<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
  label: string;
  modelValue: string;
  error?: string;
  placeholder?: string;
}

defineProps<Props>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const handleColorChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};

const handleTextChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};
</script>

<template>
  <div class="space-y-2">
    <Label>{{ label }}</Label>
    <div class="flex gap-2">
      <Input
        type="color"
        :value="modelValue"
        class="h-10 w-14 cursor-pointer p-1"
        @input="handleColorChange"
      />
      <Input
        :value="modelValue"
        :placeholder="placeholder"
        class="flex-1 font-mono"
        @input="handleTextChange"
      />
    </div>
    <InputError :message="error" />
  </div>
</template>
