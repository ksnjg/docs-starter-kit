<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import type { FeedbackFormField } from '@/types/feedback';
import { GripVertical, Plus, Trash2 } from 'lucide-vue-next';

const fields = defineModel<FeedbackFormField[]>('fields', { required: true });

const fieldTypes = [
  { value: 'text', label: 'Text Input' },
  { value: 'textarea', label: 'Text Area' },
  { value: 'radio', label: 'Radio Buttons' },
  { value: 'checkbox', label: 'Checkboxes' },
  { value: 'rating', label: 'Rating Scale' },
  { value: 'email', label: 'Email' },
];

const addField = () => {
  fields.value.push({
    type: 'text',
    label: '',
    required: false,
    options: [],
  });
};

const removeField = (index: number) => {
  fields.value.splice(index, 1);
};

const updateFieldType = (index: number, type: string) => {
  fields.value[index].type = type as FeedbackFormField['type'];
  if (['radio', 'checkbox'].includes(type) && !fields.value[index].options?.length) {
    fields.value[index].options = ['Option 1'];
  }
};

const addOption = (index: number) => {
  if (!fields.value[index].options) {
    fields.value[index].options = [];
  }
  fields.value[index].options!.push(`Option ${fields.value[index].options!.length + 1}`);
};

const removeOption = (fieldIndex: number, optionIndex: number) => {
  fields.value[fieldIndex].options?.splice(optionIndex, 1);
};

const needsOptions = (type: string) => ['radio', 'checkbox'].includes(type);
</script>

<template>
  <div class="space-y-4">
    <div v-for="(field, index) in fields" :key="index" class="rounded-lg border p-4">
      <div class="flex items-start gap-3">
        <GripVertical class="mt-2 h-5 w-5 cursor-grab text-muted-foreground" />

        <div class="flex-1 space-y-3">
          <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-1">
              <Label>Field Label</Label>
              <Input v-model="field.label" placeholder="Enter field label" />
            </div>
            <div class="space-y-1">
              <Label>Field Type</Label>
              <Select
                :model-value="field.type"
                @update:model-value="updateFieldType(index, $event as string)"
              >
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="ft in fieldTypes" :key="ft.value" :value="ft.value">
                    {{ ft.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div v-if="needsOptions(field.type)" class="space-y-2">
            <Label>Options</Label>
            <div v-for="(option, optIndex) in field.options" :key="optIndex" class="flex gap-2">
              <Input v-model="field.options![optIndex]" placeholder="Option text" />
              <Button variant="ghost" size="icon" @click="removeOption(index, optIndex)">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
            <Button variant="outline" size="sm" @click="addOption(index)">
              <Plus class="mr-1 h-3 w-3" /> Add Option
            </Button>
          </div>

          <div class="flex items-center gap-2">
            <Checkbox :id="`required-${index}`" v-model:checked="field.required" />
            <Label :for="`required-${index}`">Required field</Label>
          </div>
        </div>

        <Button variant="ghost" size="icon" @click="removeField(index)">
          <Trash2 class="h-4 w-4 text-destructive" />
        </Button>
      </div>
    </div>

    <Button variant="outline" @click="addField" class="w-full">
      <Plus class="mr-2 h-4 w-4" /> Add Field
    </Button>
  </div>
</template>
