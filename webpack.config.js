const path = require("path");
const CopyPlugin = require("copy-webpack-plugin");

module.exports = (env, argv) => {
    let production = argv.mode === "production";

    return {
        entry: [
            "./src/index.js",
            "./assets/js/admin/gutenberg/form-block.js",
            "./src/blocks/index.js"
        ],
        output: {
            path: path.resolve(__dirname + "/chunks"),
            publicPath: "/",
            filename: "[name].js",
        },
        devtool: "source-map",
        resolve: {
            extensions: [".js", ".jsx", ".json"],
			alias: {
                '@blocks': path.resolve(__dirname, 'src/blocks'),
            },
        },
        module: {
            rules: [
                {
                    test: /\.(js|jsx)$/,
                    exclude: /node_modules/,
                    use: ["babel-loader", "eslint-loader"],
                },
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        // Creates `style` nodes from JS strings
                        "style-loader",
                        // Translates CSS into CommonJS
                        "css-loader",
                        // Compiles Sass to CSS
                        "sass-loader",
                    ],
                },
            ],
        },
        plugins: [
            new CopyPlugin({
                patterns: [
                    {
                        from: "src/blocks/**/block.json",
                        to({ absoluteFilename }) {
                            return path.resolve(
                                __dirname,
                                "chunks",
                                path.basename(path.dirname(absoluteFilename)),
                                "block.json"
                            );
                        },
                    },
                ],
            }),
        ],
		externals: {
            "@wordpress/blocks": ["wp", "blocks"],
        },
    };
};
