const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');

module.exports = {
  mode: 'production',
  entry: './docs/src/index.js',
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'bundle.css',
    }),
  ],
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: '/docs/dist/',
            },
          },
          'css-loader',
        ],
      },
    ],
  },
  output: {
    path: path.resolve(__dirname + '/docs', 'dist'),
    filename: 'bundle.js'
  }
};
