<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Check, ChevronsUpDown, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

export interface Option {
  label: string;
  value: string;
}

const props = withDefaults(
  defineProps<{
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyMessage?: string;
    maxDisplayed?: number;
    disabled?: boolean;
    class?: string;
  }>(),
  {
    placeholder: 'Seleccionar...',
    searchPlaceholder: 'Buscar...',
    emptyMessage: 'No se encontraron resultados.',
    maxDisplayed: 2,
    disabled: false,
  },
);

const modelValue = defineModel<string[]>({ default: () => [] });

const open = ref(false);

const selectedLabels = computed(() => {
  return modelValue.value
    .map((val) => props.options.find((opt) => opt.value === val)?.label)
    .filter(Boolean) as string[];
});

function toggleOption(value: string) {
  const index = modelValue.value.indexOf(value);
  if (index === -1) {
    modelValue.value = [...modelValue.value, value];
  } else {
    modelValue.value = modelValue.value.filter((v) => v !== value);
  }
}

function removeOption(value: string, event: Event) {
  event.stopPropagation();
  modelValue.value = modelValue.value.filter((v) => v !== value);
}

function clearAll(event: Event) {
  event.stopPropagation();
  modelValue.value = [];
}

function isSelected(value: string): boolean {
  return modelValue.value.includes(value);
}
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <button
        type="button"
        role="combobox"
        :aria-expanded="open"
        :disabled="disabled"
        :class="
          cn(
            'flex min-h-[34px] w-full items-center justify-between rounded-md border bg-transparent px-3 py-1.5 text-sm shadow-xs outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-neutral-900',
            props.class,
          )
        "
      >
        <div class="flex flex-1 flex-wrap gap-1">
          <template v-if="modelValue.length === 0">
            <span class="text-muted-foreground">{{ placeholder }}</span>
          </template>
          <template v-else-if="modelValue.length <= maxDisplayed">
            <Badge
              v-for="label in selectedLabels"
              :key="label"
              variant="secondary"
              class="px-1.5 py-0 text-xs"
            >
              {{ label }}
              <X
                class="ml-1 h-3 w-3 cursor-pointer hover:text-destructive"
                @click="removeOption(options.find((o) => o.label === label)?.value ?? '', $event)"
              />
            </Badge>
          </template>
          <template v-else>
            <Badge variant="secondary" class="px-1.5 py-0 text-xs">
              {{ modelValue.length }} seleccionados
            </Badge>
          </template>
        </div>
        <div class="flex items-center gap-1">
          <X
            v-if="modelValue.length > 0"
            class="h-4 w-4 shrink-0 cursor-pointer opacity-50 hover:opacity-100"
            @click="clearAll"
          />
          <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
        </div>
      </button>
    </PopoverTrigger>
    <PopoverContent class="w-(--reka-popover-trigger-width) p-0" align="start">
      <Command>
        <CommandInput :placeholder="searchPlaceholder" />
        <CommandList>
          <CommandEmpty>{{ emptyMessage }}</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              @select="toggleOption(option.value)"
            >
              <div
                :class="
                  cn(
                    'mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary',
                    isSelected(option.value)
                      ? 'bg-primary text-primary-foreground'
                      : 'opacity-50 [&_svg]:invisible',
                  )
                "
              >
                <Check class="h-3 w-3" />
              </div>
              {{ option.label }}
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
