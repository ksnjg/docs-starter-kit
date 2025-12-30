<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { FeedbackStats } from '@/types/feedback';
import { MessageSquare, ThumbsDown, ThumbsUp } from 'lucide-vue-next';

defineProps<{ stats: FeedbackStats }>();
</script>

<template>
  <div class="grid gap-4 md:grid-cols-4">
    <Card>
      <CardHeader class="pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">Total Responses</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="flex items-center gap-2">
          <MessageSquare class="h-5 w-5 text-muted-foreground" />
          <span class="text-2xl font-bold">{{ stats.total }}</span>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader class="pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">Helpful</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="flex items-center gap-2">
          <ThumbsUp class="h-5 w-5 text-green-500" />
          <span class="text-2xl font-bold text-green-600">{{ stats.helpful }}</span>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader class="pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">Not Helpful</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="flex items-center gap-2">
          <ThumbsDown class="h-5 w-5 text-red-500" />
          <span class="text-2xl font-bold text-red-600">{{ stats.notHelpful }}</span>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader class="pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">Helpfulness Score</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ stats.helpfulPercentage }}%</div>
      </CardContent>
    </Card>
  </div>
</template>
