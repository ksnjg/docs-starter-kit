<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import type { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

interface Props {
  requiresConfirmation?: boolean;
  twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
  requiresConfirmation: false,
  twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Autenticación en dos pasos',
    href: show.url(),
  },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
  clearTwoFactorAuthData();
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Autenticación en dos pasos" />
    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="Autenticación en dos pasos"
          description="Administra la configuración de tu autenticación en dos pasos"
        />

        <div v-if="!twoFactorEnabled" class="flex flex-col items-start justify-start space-y-4">
          <Badge variant="destructive">Desactivado</Badge>

          <p class="text-muted-foreground">
            Cuando actives la autenticación en dos pasos, se te solicitará un PIN seguro durante el
            inicio de sesión. Este PIN se obtiene desde una aplicación compatible con TOTP en tu
            teléfono.
          </p>

          <div>
            <Button v-if="hasSetupData" @click="showSetupModal = true">
              <ShieldCheck />Continuar configuración
            </Button>
            <Form
              v-else
              v-bind="enable.form()"
              @success="showSetupModal = true"
              #default="{ processing }"
            >
              <Button type="submit" :disabled="processing">
                <ShieldCheck />Activar 2FA</Button
              ></Form
            >
          </div>
        </div>

        <div v-else class="flex flex-col items-start justify-start space-y-4">
          <Badge variant="default">Activado</Badge>

          <p class="text-muted-foreground">
            Con la autenticación en dos pasos activada, se te solicitará un PIN seguro y aleatorio
            durante el inicio de sesión, el cual puedes obtener desde la aplicación compatible con
            TOTP en tu teléfono.
          </p>

          <TwoFactorRecoveryCodes />

          <div class="relative inline">
            <Form v-bind="disable.form()" #default="{ processing }">
              <Button variant="destructive" type="submit" :disabled="processing">
                <ShieldBan />
                Desactivar 2FA
              </Button>
            </Form>
          </div>
        </div>

        <TwoFactorSetupModal
          v-model:isOpen="showSetupModal"
          :requiresConfirmation="requiresConfirmation"
          :twoFactorEnabled="twoFactorEnabled"
        />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
