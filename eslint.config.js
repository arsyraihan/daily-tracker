import pluginVue from 'eslint-plugin-vue';

export default [
    ...pluginVue.configs['flat/recommended'],
    {
        rules: {
            // Disable false-positive from HTML comments being read as ESLint directives
            'vue/comment-directive': 'off',
        },
    },
];
