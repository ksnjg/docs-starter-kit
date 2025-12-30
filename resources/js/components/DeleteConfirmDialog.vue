<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Props {
  open: boolean;
  title?: string;
  description?: string;
  itemName?: string;
  processing?: boolean;
}

withDefaults(defineProps<Props>(), {
  title: 'Are you sure?',
  description: 'This action cannot be undone.',
  processing: false,
});

const emit = defineEmits<{
  'update:open': [value: boolean];
  confirm: [];
}>();

const handleOpenChange = (value: boolean) => {
  emit('update:open', value);
};

const handleConfirm = () => {
  emit('confirm');
};
</script>

<template>
  <AlertDialog :open="open" @update:open="handleOpenChange">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ description }}
          <strong v-if="itemName">"{{ itemName }}"</strong>
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="processing">Cancel</AlertDialogCancel>
        <AlertDialogAction
          @click="handleConfirm"
          :disabled="processing"
          class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        >
          {{ processing ? 'Deleting...' : 'Delete' }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
