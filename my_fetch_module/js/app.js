(function (Drupal, once, drupalSettings) {
  Drupal.behaviors.fetchExample = {
    attach: function (context) {

      once('fetchExample', context.querySelectorAll('#load-data'))
        .forEach(function (button) {

          button.addEventListener('click', function () {

            fetch(drupalSettings.myModule.apiUrl)
              .then(response => response.json())
              .then(data => {

                const list = document.getElementById('data-list');
                list.innerHTML = '';

                data.forEach(item => {
                  const li = document.createElement('li');
                  li.textContent = item.name + ' - ' + item.role;
                  list.appendChild(li);
                });

              })
              .catch(error => {
                console.error('Error:', error);
              });

          });

        });

    }
  };
})(Drupal, once, drupalSettings);
