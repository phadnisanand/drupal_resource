(function (Drupal) {

  Drupal.behaviors.chip = {
    attach(context) {
      once('chip-dismissible', '.chip--dismissible', context).forEach((chip) => {
        chip.addEventListener('click', () => {
          chip.classList.toggle('chip--dismissed');
        });
      });
    },
  };

})(Drupal);
