import { globalIgnores } from 'eslint/config';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import pluginVue from 'eslint-plugin-vue';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';

export default defineConfigWithVueTs(
    {
        name: 'app/files-to-lint',
        files: ['**/*.{ts,mts,tsx,vue}'],
    },
    globalIgnores(['**/vendor/**', '**/node_modules/**', '**/public/**', '**/bootstrap/ssr/**', '**/tailwind.config.js', '**/resources/js/components/ui/**', '**/resources/js/actions/**', '**/resources/js/routes/**', '**/resources/js/wayfinder/**']),
    pluginVue.configs['flat/essential'],
    vueTsConfigs.recommended,
    skipFormatting,
    {
        rules: {
            // Vue
            'vue/multi-word-component-names': 'off',

            // Variables
            'no-var': 'error',
            'prefer-const': 'error',
            'one-var': ['error', 'never'],

            // Equality
            'eqeqeq': ['error', 'smart'],

            // Control flow
            'curly': ['error', 'all'],

            // Best practices
            'no-eval': 'error',
            'no-with': 'error',
            'no-debugger': 'error',

            // TypeScript specific
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/explicit-function-return-type': 'off',
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            '@typescript-eslint/consistent-type-imports': ['error', { prefer: 'type-imports' }],

            // Naming conventions
            '@typescript-eslint/naming-convention': [
                'error',
                {
                    selector: 'variable',
                    format: ['camelCase', 'UPPER_CASE', 'PascalCase'],
                    leadingUnderscore: 'forbid',
                    trailingUnderscore: 'forbid',
                },
                {
                    selector: 'variable',
                    modifiers: ['destructured'],
                    format: null,
                },
                {
                    selector: 'function',
                    format: ['camelCase'],
                },
                {
                    selector: 'typeLike',
                    format: ['PascalCase'],
                },
                {
                    selector: 'enumMember',
                    format: ['UPPER_CASE'],
                },
            ],
        },
    },
);
