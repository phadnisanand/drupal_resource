const mixins = {
  'button-style': (mixin, ...args) => {
    const [bg = '#007BFF', text = 'white'] = args[0].split(' ');
    return {
      'padding': '10px 20px',
      'border': 'none',
      'background-color': bg,
      'color': text,
      'border-radius': '4px',
      'font-size': '20px',
      '&:active': {
        'transform': 'scale(0.95)',
      }
    };
  }
};

const postcssPresetEnv = require('postcss-preset-env');
//const cssnano = require('cssnano');
const postcssImport = require('postcss-import');
const postcssPxtorem = require('postcss-pxtorem');
const postcssUrl = require('postcss-url');
const postcssMixins = require('postcss-mixins');
const postcssNested = require('postcss-nested');
//const postcssNesting = require('postcss-nesting');

module.exports = {
  map: true,
  plugins: [
    postcssImport,
    postcssNested,
    //postcssNesting,
    postcssPresetEnv,
    postcssMixins({ mixins }),
    postcssPxtorem({
      // These are the same settings as Drupal core (11.3.2) uses.
      propList: [
        '*',
        '!background-position',
        '!border',
        '!border-width',
        '!box-shadow',
        '!border-top*',
        '!border-right*',
        '!border-bottom*',
        '!border-left*',
        '!border-start*',
        '!border-end*',
        '!outline*',
      ],
      mediaQuery: true,
      minPixelValue: 3
    }),
    postcssUrl({
      filter: '**/*.svg',
      url: 'inline',
      optimizeSvgEncode: true,
    }),
    //cssnano,
  ]
}
