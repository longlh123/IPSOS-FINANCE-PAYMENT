const webpack = require('webpack');

module.exports = {
  // Your existing Webpack configuration
  resolve: {
    fallback: {
      crypto: require.resolve('crypto-browserify'),
      constants: require.resolve('constants-browserify'),
      stream: require.resolve('stream-browserify'),
      buffer: require.resolve('buffer'),
      vm: require.resolve('vm-browserify'),
      process: require.resolve('process/browser.js'), // Note the .js extension
    },
  },
  plugins: [
    new webpack.ProvidePlugin({
      Buffer: ['buffer', 'Buffer'],
      process: 'process/browser',
    }),
  ],
  module: {
    rules: [
      {
        test: /\.m?js/,
        resolve: {
          fullySpecified: false, // Ensure fully specified import is not required
        },
      },
    ],
  },
};
