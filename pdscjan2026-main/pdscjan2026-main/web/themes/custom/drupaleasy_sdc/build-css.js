const fs = require('fs');
const postcss = require('postcss');
const { glob } = require('glob');
const { plugins } = require('./postcss.config.js');

glob('components/**/*.pcss.css').then(async (files) => {
  for (const file of files) {
    const css = fs.readFileSync(file, 'utf8');
    const result = await postcss(plugins).process(css, { from: file });

    const outPath = file.replace(/\.pcss\.css$/, '.css');
    fs.writeFileSync(outPath, result.css);
    console.log(`✔ Wrote ${outPath}`);
  }
});
