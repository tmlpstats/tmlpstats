var path = require('path')
var webpack = require('webpack')

var plugins = [
    new webpack.optimize.CommonsChunkPlugin({
        name: 'commons',
        filename: 'commons.js',
        minChunks: function(module, count) {
            // module.resource is the filename, module.context is the module name
            if (/node_modules/.test(module.context)) {
                return true
            } else if (count > 3) {
                return true
            }
            return false
        }
    }),
]

if (process.env.NODE_ENV == 'production') {
    plugins.push(
        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
        }),
        new webpack.optimize.UglifyJsPlugin()
    )
}

module.exports = {
    entry: {
        main: './resources/assets/js/main',
        commons: ['es6-promise', 'react', 'react-router'],
    },
    output: {
        path: path.join(__dirname, 'public', 'js'),
        filename: 'main.js'
    },
    module: {
        loaders: [
            {
                test: /\.(js|jsx)$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader', // 'babel-loader' is also a valid name to reference
                include: [__dirname],
            }
        ]
    },
    plugins: plugins,
    resolve: {
        extensions: ['.js', '.jsx']
    },
    node: {
        process: true,
        window: true
    }

}
