<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import type { SidebarItem } from '@/components/docs/DocsNavigation.vue';
import DocsNavigation from '@/components/docs/DocsNavigation.vue';
import DocsNavigationTabs from '@/components/docs/DocsNavigationTabs.vue';
import GithubIcon from '@/components/icons/GithubIcon.vue';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts';
import type { SiteSettings } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const siteSettings = computed(() => page.props.siteSettings as SiteSettings | undefined);

const { appearance, updateAppearance } = useAppearance();

useKeyboardShortcuts([
  {
    key: 'k',
    ctrl: true,
    handler: () => {
      const searchInput = document.querySelector<HTMLInputElement>(
        '[placeholder="Search docs..."]',
      );
      if (searchInput) {
        searchInput.focus();
        searchInput.select();
      }
    },
  },
]);

interface NavigationTab {
  id: number;
  title: string;
  slug: string;
  icon: string | null;
  is_default: boolean;
}

interface Props {
  navigationTabs: NavigationTab[];
  activeNavId: number | null;
  sidebarItems: SidebarItem[];
  currentPath: string;
}

defineProps<Props>();

const cssVariables = computed(() => ({
  '--docs-primary': siteSettings.value?.theme?.primaryColor ?? '#3B82F6',
  '--docs-secondary': siteSettings.value?.theme?.secondaryColor ?? '#6366F1',
  '--docs-accent': siteSettings.value?.theme?.accentColor ?? '#F59E0B',
  '--docs-sidebar-width': `${siteSettings.value?.layout?.sidebarWidth ?? 280}px`,
  '--docs-content-width': `${siteSettings.value?.layout?.contentWidth ?? 900}px`,
  '--docs-font-body': siteSettings.value?.typography?.bodyFont ?? 'Inter',
  '--docs-font-heading': siteSettings.value?.typography?.headingFont ?? 'Inter',
  '--docs-font-code': siteSettings.value?.typography?.codeFont ?? 'JetBrains Mono',
  '--docs-font-size': `${siteSettings.value?.typography?.baseFontSize ?? 16}px`,
  '--docs-line-height': siteSettings.value?.typography?.lineHeight ?? 1.6,
}));
</script>

<template>
  <div class="docs-layout min-h-screen bg-background" :style="cssVariables">
    <header class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur">
      <div class="flex h-14 items-center justify-between px-4">
        <Link href="/" class="flex items-center gap-2">
          <AppLogo class="h-8 w-auto" />
          <span class="font-semibold">{{ siteSettings?.siteName ?? 'Docs' }}</span>
        </Link>
        <div class="flex items-center gap-4">
          <a
            v-if="siteSettings?.social?.github"
            :href="siteSettings.social.github"
            target="_blank"
            rel="noopener noreferrer"
            class="text-muted-foreground transition-colors hover:text-foreground"
          >
            <GithubIcon class="h-5 w-5" />
          </a>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9">
                <Sun v-if="appearance === 'light'" class="h-4 w-4" />
                <Moon v-else-if="appearance === 'dark'" class="h-4 w-4" />
                <Monitor v-else class="h-4 w-4" />
                <span class="sr-only">Toggle theme</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem @click="updateAppearance('light')">
                <Sun class="mr-2 h-4 w-4" />
                Light
              </DropdownMenuItem>
              <DropdownMenuItem @click="updateAppearance('dark')">
                <Moon class="mr-2 h-4 w-4" />
                Dark
              </DropdownMenuItem>
              <DropdownMenuItem @click="updateAppearance('system')">
                <Monitor class="mr-2 h-4 w-4" />
                System
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          <Button v-if="$page.props.auth?.user" variant="ghost" size="sm" as-child>
            <Link href="/dashboard">Admin</Link>
          </Button>
        </div>
      </div>
      <DocsNavigationTabs :tabs="navigationTabs" :active-id="activeNavId" />
    </header>

    <SidebarProvider :default-open="true">
      <DocsNavigation :items="sidebarItems" :current-path="currentPath" />
      <SidebarInset>
        <main class="docs-main flex-1 overflow-hidden">
          <slot />
        </main>
      </SidebarInset>
    </SidebarProvider>

    <footer
      v-if="siteSettings?.layout?.showFooter && siteSettings?.layout?.footerText"
      class="border-t bg-muted/30 px-4 py-6 text-center text-sm text-muted-foreground"
      v-html="siteSettings.layout.footerText"
    />

    <Teleport to="head">
      <style v-if="siteSettings?.theme?.customCss">
        {{ siteSettings.theme.customCss }}
      </style>
    </Teleport>
  </div>
</template>

<style>
.docs-layout {
  font-family: var(--docs-font-body), system-ui, sans-serif;
  font-size: var(--docs-font-size);
  line-height: var(--docs-line-height);
}

.docs-layout h1,
.docs-layout h2,
.docs-layout h3,
.docs-layout h4,
.docs-layout h5,
.docs-layout h6 {
  font-family: var(--docs-font-heading), system-ui, sans-serif;
}

.docs-layout pre,
.docs-layout code {
  font-family: var(--docs-font-code), monospace;
}

.docs-main {
  max-width: var(--docs-content-width);
}
</style>
