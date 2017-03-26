module.exports = {
    extends:'../../.eslintrc.js',
    env: {
        mocha: true
    },
    globals: {
        browser: true,
        $: true,
        $$: true
    },
    rules: {
        'no-console': false // console.log isn't an issue in tests like it might be in production code
    }
}
