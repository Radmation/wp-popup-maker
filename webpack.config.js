const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const postcssPresetEnv = require("postcss-preset-env");
const devMode = process.env.NODE_ENV !== "production";
const CopyWebPackPlugin = require('copy-webpack-plugin');
const webpack = require('webpack');

// Common config settings
let commonConfig = {
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ["@babel/preset-env"]
          }
        }
      },
      {
        test: /\.(sa|sc)ss$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: "css-loader",
            options: {
              importLoaders: 2
            }
          },
          {
            loader: "postcss-loader",
            options: {
              ident: "postcss",
              plugins: devMode
                  ? () => []
                  : () => [
                    postcssPresetEnv({
                      browsers: [">1%"]
                    }),
                    require("cssnano")()
                  ]
            }
          },
          {
            loader: "sass-loader"
          }
        ]
      }
    ]
  }
};

// Admin config
let adminConfig = Object.assign({}, commonConfig, {
  mode: devMode ? "development" : "production",
  entry: {
    "kbi-styles": './core/admin/src/scss/index.scss',
    "kbi-scripts" : './core/admin/src/js/index.js'
  },
  output: {
    filename: devMode ? "js/[name].js" : "js/[name].min.js",
    path: path.resolve(__dirname, 'dist/admin')
  },
  resolve: {
    alias: {
      "jquery-ui": "jquery-ui-dist/jquery-ui.js"
    }
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: devMode ? "css/[name].css" : "css/[name].min.css"
    })
  ]
});

// Return Array of Configurations. We can multiple configs by adding them below separated by a comma. Example: module.exports=[config1,config2,config3];
module.exports = [
  adminConfig
];