<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { FeedbackFormField } from '@/types/feedback';
import { Star } from 'lucide-vue-next';

interface Props {
  field: FeedbackFormField;
}

defineProps<Props>();

const modelValue = defineModel<string | number | string[]>();

const handleCheckboxChange = (option: string, checked: boolean) => {
  const current = (modelValue.value as string[]) || [];
  if (checked) {
    modelValue.value = [...current, option];
  } else {
    modelValue.value = current.filter((v) => v !== option);
  }
};

const isChecked = (option: string) => {
  return ((modelValue.value as string[]) || []).includes(option);
};

const setRating = (rating: number) => {
  modelValue.value = rating;
};

const handleRadioChange = (option: string) => {
  modelValue.value = option;
};
</script>

<template>
  <div class="space-y-2">
    <Label class="text-sm font-medium">
      {{ field.label }}
      <span v-if="field.required" class="text-destructive">*</span>
    </Label>

    <Input
      v-if="field.type === 'text'"
      :model-value="modelValue as string"
      @update:model-value="modelValue = $event"
      type="text"
      class="w-full"
    />

    <Input
      v-else-if="field.type === 'email'"
      :model-value="modelValue as string"
      @update:model-value="modelValue = $event"
      type="email"
      placeholder="your@email.com"
      class="w-full"
    />

    <Textarea
      v-else-if="field.type === 'textarea'"
      :model-value="modelValue as string"
      @update:model-value="modelValue = $event"
      rows="3"
      class="w-full"
    />

    <div v-else-if="field.type === 'radio'" class="space-y-2">
      <div v-for="option in field.options" :key="option" class="flex items-center gap-2">
        <input
          type="radio"
          :id="`radio-${option}`"
          :name="field.label"
          :value="option"
          :checked="modelValue === option"
          @change="handleRadioChange(option)"
          class="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
        />
        <Label :for="`radio-${option}`" class="cursor-pointer font-normal">{{ option }}</Label>
      </div>
    </div>

    <div v-else-if="field.type === 'checkbox'" class="space-y-2">
      <div v-for="option in field.options" :key="option" class="flex items-center gap-2">
        <Checkbox
          :id="`checkbox-${option}`"
          :checked="isChecked(option)"
          @update:checked="handleCheckboxChange(option, $event as boolean)"
        />
        <Label :for="`checkbox-${option}`" class="cursor-pointer font-normal">{{ option }}</Label>
      </div>
    </div>

    <div v-else-if="field.type === 'rating'" class="flex gap-1">
      <button
        v-for="i in 5"
        :key="i"
        type="button"
        @click="setRating(i)"
        class="p-1 transition-colors hover:text-yellow-500"
      >
        <Star
          class="h-6 w-6"
          :class="
            (modelValue as number) >= i
              ? 'fill-yellow-400 text-yellow-400'
              : 'text-muted-foreground'
          "
        />
      </button>
    </div>
  </div>
</template>
