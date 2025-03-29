export default {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'body-max-line-length': [2, 'always', 250],
        'header-max-length': [2, 'always', 150],
    },
};
