<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import type { ServerCompatibility } from '@/types/web-cron';
import { AlertTriangle, CheckCircle, CircleHelp, Copy, Info } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
  modelValue: boolean;
  lastWebCronAt: string | null;
  serverCheck: ServerCompatibility;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();

const copied = ref(false);

const checked = computed({
  get: () => props.modelValue,
  set: (value: boolean) => emit('update:modelValue', value),
});

const cronCommand = computed(() => {
  return `* * * * * cd ${props.serverCheck.base_path} && ${props.serverCheck.php_binary} artisan schedule:run >> /dev/null 2>&1`;
});

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(cronCommand.value);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy!', err);
  }
};

const formatDate = (dateString: string | null): string => {
  if (!dateString) {
    return 'Never';
  }
  return new Date(dateString).toLocaleString();
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Web-Cron Schedule Runner</CardTitle>
      <CardDescription> Run all scheduled tasks via visitor traffic </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <!-- Server Compatibility Check -->
      <div class="space-y-3">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium">Server Compatibility</span>
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <Info class="h-4 w-4 cursor-help text-muted-foreground" />
              </TooltipTrigger>
              <TooltipContent class="max-w-xs">
                <p>
                  These checks ensure your server environment is optimized for running background
                  tasks via Web-Cron.
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>

        <div class="flex items-center gap-2">
          <CheckCircle v-if="serverCheck.proc_open.available" class="h-4 w-4 text-green-500" />
          <AlertTriangle v-else class="h-4 w-4 text-yellow-500" />
          <div class="flex items-center gap-1.5 text-sm">
            <span>Background Execution:</span>
            <span
              :class="
                serverCheck.proc_open.available
                  ? 'font-medium text-green-600 dark:text-green-400'
                  : 'font-medium text-yellow-600 dark:text-yellow-400'
              "
            >
              {{ serverCheck.proc_open.available ? 'Supported' : 'Limited' }}
            </span>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="h-3.5 w-3.5 cursor-help text-muted-foreground" />
                </TooltipTrigger>
                <TooltipContent class="max-w-xs">
                  <p v-if="serverCheck.proc_open.available">
                    <strong>proc_open() is enabled.</strong> This allows the scheduler to run in the
                    background without blocking the visitor's request.
                  </p>
                  <p v-else>
                    <strong>proc_open() is disabled.</strong> Tasks will run synchronously, meaning
                    the visitor who triggers the cron will wait until tasks complete. This is common
                    on some shared hosting.
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Info class="h-4 w-4 text-blue-500" />
          <div class="flex items-center gap-1.5 text-sm">
            <span>PHP Version: {{ serverCheck.php_version }}</span>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="h-3.5 w-3.5 cursor-help text-muted-foreground" />
                </TooltipTrigger>
                <TooltipContent>
                  <p>Your current server PHP version.</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Info class="h-4 w-4 text-blue-500" />
          <div class="flex items-center gap-1.5 text-sm">
            <span>Max Execution Time: {{ serverCheck.max_execution_time }}s</span>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="h-3.5 w-3.5 cursor-help text-muted-foreground" />
                </TooltipTrigger>
                <TooltipContent class="max-w-xs">
                  <p>
                    The maximum time (in seconds) a script is allowed to run. Long-running tasks
                    might be killed if this is too low.
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <CheckCircle
            v-if="serverCheck.queue_driver === 'database'"
            class="h-4 w-4 text-green-500"
          />
          <Info v-else class="h-4 w-4 text-blue-500" />
          <div class="flex items-center gap-1.5 text-sm">
            <span>Queue Driver: {{ serverCheck.queue_driver }}</span>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="h-3.5 w-3.5 cursor-help text-muted-foreground" />
                </TooltipTrigger>
                <TooltipContent class="max-w-xs">
                  <p>
                    Determines how background jobs are stored and processed. 'database' is
                    recommended for reliability.
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </div>

        <div class="flex items-center gap-2 text-sm text-muted-foreground">
          <span
            >Pending Jobs: {{ serverCheck.pending_jobs }} | Failed Jobs:
            {{ serverCheck.failed_jobs }}</span
          >
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <Info class="h-3.5 w-3.5 cursor-help text-muted-foreground" />
              </TooltipTrigger>
              <TooltipContent class="max-w-xs">
                <p>
                  Current status of your background job queue. High failed jobs may indicate
                  configuration issues.
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      </div>

      <!-- Warning if proc_open not available -->
      <Alert v-if="!serverCheck.proc_open.available" variant="destructive">
        <AlertTriangle class="h-4 w-4" />
        <AlertTitle>Server Limitation</AlertTitle>
        <AlertDescription>
          Your server has <code class="rounded bg-muted px-1">proc_open()</code> disabled. Tasks
          will run synchronously, which may cause brief page delays.
        </AlertDescription>
      </Alert>

      <!-- Enable Toggle -->
      <div class="flex items-center justify-between rounded-lg border p-4">
        <div>
          <Label class="text-base">Enable Web-Cron</Label>
          <p class="text-sm text-muted-foreground">Run all scheduled tasks via visitor requests</p>
        </div>
        <Switch v-model:checked="checked" />
      </div>

      <!-- Server Cron Info when disabled -->
      <div v-if="!modelValue" class="rounded-lg border p-4">
        <h4 class="text-base font-medium">Server Cron Configuration</h4>
        <p class="mt-1 mb-3 text-sm text-muted-foreground">
          To ensure scheduled tasks are executed while Web-Cron is disabled, you must add this entry
          to your server's crontab. This is the recommended method for production environments to
          ensure tasks run reliably every minute.
        </p>
        <div
          class="relative rounded-md border bg-muted/50 py-3 pr-10 pl-3 font-mono text-xs dark:bg-zinc-900"
        >
          <code class="block break-all text-foreground">{{ cronCommand }}</code>
          <Button
            type="button"
            size="icon"
            variant="ghost"
            class="absolute top-1/2 right-1 h-7 w-7 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            @click="copyToClipboard"
          >
            <Copy v-if="!copied" class="h-3.5 w-3.5" />
            <CheckCircle v-else class="h-3.5 w-3.5 text-green-500" />
          </Button>
        </div>
        <a
          href="https://laravel.com/docs/12.x/scheduling#running-the-scheduler"
          target="_blank"
          rel="noopener noreferrer"
          class="mt-2 inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
        >
          <CircleHelp class="h-3.5 w-3.5" />
          <span>Learn more about running the scheduler</span>
        </a>
      </div>

      <!-- Status when enabled -->
      <div class="space-y-2 rounded-lg p-4 text-sm">
        <div class="flex justify-between">
          <span class="text-muted-foreground">Last Scheduler Run:</span>
          <span>{{ formatDate(lastWebCronAt) }}</span>
        </div>
      </div>

      <!-- Info box -->
      <Alert v-if="modelValue">
        <Info class="h-4 w-4" />
        <AlertTitle>How it works</AlertTitle>
        <AlertDescription>
          <ul class="mt-2 list-inside list-disc space-y-1">
            <li>
              Triggers <code class="rounded bg-muted px-1">schedule:run</code> at most once per
              minute
            </li>
            <li>Laravel decides which tasks are due and runs them</li>
            <li>All future scheduled tasks are automatically included</li>
            <li>Low-traffic sites may experience delayed task execution</li>
          </ul>
        </AlertDescription>
      </Alert>
    </CardContent>
  </Card>
</template>
