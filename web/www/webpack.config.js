const path = require('path');

const src = "./private_ts";

pages = [
    "main.ts",
    "test.ts"
];

var entries = {};
for (let page of pages) {
    entries[path.basename(page, ".ts")] = path.resolve(src, page);
}

module.exports = {
    entry: entries,
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
        ],
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js'],
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'public'),
    },
    devtool: 'inline-source-map',
    mode: 'development',
    devServer: {
        static: ['./public', '../cdn/public'],
        client: {
            progress: true,
        },
    },
};
