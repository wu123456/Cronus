module.exports = {
  // entry: './index.js',
  entry: './index-tables.js',

  output: {
    // filename: 'bundle.js',
    filename: 'tables.js',
    publicPath: ''
  },

  module: {
    loaders: [
      { test: /\.js$/, exclude: /node_modules/, loader: 'babel-loader?presets[]=es2015&presets[]=react' }
    ]
  }
}
