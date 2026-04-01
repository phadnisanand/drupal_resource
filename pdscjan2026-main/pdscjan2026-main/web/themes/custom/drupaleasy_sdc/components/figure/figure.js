(function (Drupal) {
  'use strict';

  Drupal.behaviors.spinnyImage = {
    attach: function (context, settings) {
      const spinnyFigures = context.querySelectorAll('.spinny:not(.processed)');

      spinnyFigures.forEach(function(spinnyFigure) {
        const img = spinnyFigure.querySelector('img');

        if (img && typeof fabric !== 'undefined') {
          // Create canvas with same dimensions as image
          const canvas = new fabric.Canvas(document.createElement('canvas'));
          canvas.setWidth(img.offsetWidth);
          canvas.setHeight(img.offsetHeight);

          // Replace image with canvas and fix positioning
          const canvasElement = canvas.getElement();
          canvasElement.style.position = 'static';
          canvasElement.style.display = 'block';
          img.parentNode.replaceChild(canvasElement, img);

          // Load the image into Fabric and spin it
          fabric.Image.fromURL(img.src, function(fabricImg) {
            fabricImg.set({
              left: canvas.width / 2,
              top: canvas.height / 2,
              originX: 'center',
              originY: 'center'
            });

            canvas.add(fabricImg);

            fabricImg.animate('angle', 360, {
              duration: 2000,
              onChange: canvas.renderAll.bind(canvas)
            });
          });

          // Mark as processed
          spinnyFigure.classList.add('processed');
        }
      });
    }
  };

})(Drupal);
