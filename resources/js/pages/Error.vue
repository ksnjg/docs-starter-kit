<script setup lang="ts">
import ErrorLayout from '@/layouts/ErrorLayout.vue';
import { dashboard, home } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
  status: number;
  message?: string;
}

const props = defineProps<Props>();

const page = usePage();

interface ErrorType {
  title: string;
  description: string;
}

interface IconType {
  path: string;
  color: string;
}

const errorTypes: Record<number, ErrorType> = {
  400: {
    title: '400: Bad Request',
    description: 'Sorry, your request contains invalid syntax and cannot be processed.',
  },
  401: {
    title: '401: Unauthorized',
    description: 'Sorry, you need to be authenticated to access this page.',
  },
  403: {
    title: '403: Forbidden',
    description: 'Sorry, you do not have permission to access this page.',
  },
  404: {
    title: '404: Page Not Found',
    description: 'Sorry, the page you are looking for could not be found.',
  },
  405: {
    title: '405: Method Not Allowed',
    description: 'Sorry, the method specified in the request is not allowed.',
  },
  408: {
    title: '408: Request Timeout',
    description: 'Sorry, the server timed out waiting for the request.',
  },
  413: {
    title: '413: Payload Too Large',
    description: 'Sorry, the request entity is larger than limits defined by the server.',
  },
  422: {
    title: '422: Unprocessable Entity',
    description:
      'Sorry, the request was well-formed but could not be processed due to semantic errors.',
  },
  429: {
    title: '429: Too Many Requests',
    description: 'Sorry, you have sent too many requests in a given amount of time.',
  },
  500: {
    title: '500: Server Error',
    description: 'Oops, something went wrong on our servers.',
  },
  502: {
    title: '502: Bad Gateway',
    description: 'Sorry, the server received an invalid response from the upstream server.',
  },
  503: {
    title: '503: Service Unavailable',
    description: 'Sorry, we are performing maintenance. Please try again later.',
  },
  504: {
    title: '504: Gateway Timeout',
    description: 'Sorry, the server acted as a gateway and did not receive a timely response.',
  },
};

const error = computed<ErrorType>(() => {
  const defaultError: ErrorType = errorTypes[props.status] || {
    title: `${props.status}: Error`,
    description: 'An unexpected error occurred.',
  };

  return {
    title: defaultError.title,
    description: props.message || defaultError.description,
  };
});

const isAuthenticated = computed(() => !!(page.props.auth as { user?: unknown } | undefined)?.user);

const isServerError = computed<boolean>(() => {
  return [500, 501, 502, 504].includes(props.status);
});

const iconType = computed<IconType>(() => {
  if (isServerError.value) {
    if (props.status === 502 || props.status === 504) {
      return {
        path: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3',
        color: 'text-red-500',
      };
    }
    return {
      path: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
      color: 'text-red-500',
    };
  }
  if (props.status === 503) {
    return {
      path: 'M21.75 6.75a2.25 2.25 0 00-2.25-2.25h-15a2.25 2.25 0 00-2.25 2.25v10.5a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V12M6 12h.008v.008H6V12zm2.25 0h.008v.008H8.25V12zm2.25 0h.008v.008H10.5V12z',
      color: 'text-red-500',
    };
  }
  if (props.status === 401 || props.status === 403) {
    return {
      path: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
      color: 'text-yellow-500',
    };
  }

  if (props.status === 404) {
    return {
      path: 'M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z',
      color: 'text-yellow-500',
    };
  }

  if (props.status === 408) {
    return {
      path: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
      color: 'text-yellow-500',
    };
  }

  if (props.status === 429) {
    return {
      path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z',
      color: 'text-yellow-500',
    };
  }

  if (props.status === 413) {
    return {
      path: 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z',
      color: 'text-yellow-500',
    };
  }

  if (props.status === 422) {
    return {
      path: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
      color: 'text-yellow-500',
    };
  }

  return {
    path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
    color: 'text-yellow-500',
  };
});
</script>

<template>
  <Head>
    <title>{{ error.title }}</title>
    <meta name="description" :content="error.description" />
  </Head>

  <ErrorLayout>
    <div
      class="mx-auto flex min-h-[80dvh] w-full max-w-7xl flex-col items-center justify-center px-5 py-8"
    >
      <div class="w-full max-w-lg text-center">
        <svg
          class="mx-auto mb-6 h-24 w-24"
          :class="iconType.color"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
        >
          <path stroke-linecap="round" stroke-linejoin="round" :d="iconType.path" />
        </svg>

        <h1 class="mb-4 text-4xl font-bold text-gray-900 dark:text-gray-100">
          {{ error.title }}
        </h1>
        <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
          {{ error.description }}
        </p>

        <div class="flex flex-col justify-center gap-4 sm:flex-row">
          <Link
            v-if="isAuthenticated"
            :href="dashboard()"
            class="rounded-lg bg-gray-900 px-6 py-3 font-medium text-white transition-colors hover:bg-black focus:ring-2 focus:ring-gray-800 focus:ring-offset-2 focus:outline-none dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
          >
            Go to dashboard
          </Link>
          <Link
            v-else
            :href="home()"
            class="rounded-lg bg-gray-900 px-6 py-3 font-medium text-white transition-colors hover:bg-black focus:ring-2 focus:ring-gray-800 focus:ring-offset-2 focus:outline-none dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
          >
            Go home
          </Link>
        </div>
      </div>
    </div>
  </ErrorLayout>
</template>
