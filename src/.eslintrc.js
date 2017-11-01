const fs = require('fs')

module.exports = {
    "env": {
        "browser": true,
        "commonjs": true,
        "es6": true,
        "jest": true
    },
    "extends": ["eslint:recommended", "plugin:react/recommended"],
    "installedESLint": true,
    "parser": "babel-eslint",
    "parserOptions": {
        "ecmaVersion": 7,
        "ecmaFeatures": {
            "experimentalObjectRestSpread": true,
            "jsx": true
        },
        "sourceType": "module"
    },
    "plugins": [
        "react",
        "graphql"
    ],
    "rules": {
        "react/jsx-uses-vars": 1,
        "react/jsx-uses-react": [1],
        "react/jsx-wrap-multilines": [1, {declaration: true, assignment: true, return: true}],
        "react/jsx-equals-spacing": [2, "never"],
        "react/prop-types": [1, {ignore: ['params', 'dispatch', 'router']}],
        "react/prefer-stateless-function": [1, {ignorePureComponents: true}],
        "indent": [
            "error",
            4
        ],
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "single"
        ],
        "semi": [
            "error",
            "never"
        ],
         "graphql/template-strings": ['error', {
            env: 'apollo',
            //schemaJson: require('./schema.json')
            //schemaString: fs.readFileSync(`${__dirname}/foo.graphql`)
         }]
    },
    "globals": {
        "window": true,
        "process": true
    }
};
