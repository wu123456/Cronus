module.exports = {
  // entry: './index.js',
  entry: './index-game.js',

  output: {
    // filename: 'bundle.js',
    filename: 'game.js',
    publicPath: ''
  },

  module: {
    loaders: [
      { test: /\.js$/, exclude: /node_modules/, loader: 'babel-loader?presets[]=es2015&presets[]=react' }
    ]
  }
}
