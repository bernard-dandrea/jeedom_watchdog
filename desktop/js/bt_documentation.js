// Last Modified : 2025/09/03 14:38:17

// affiche le bouton Documentation (code repris en partie de plugin.js)

/* placer le code suivant là où on souhaite placer le bouton (remplacer plugin par le nom du plugin)

	<div id="insert_documentation">
		<?php include_file('desktop', 'bt_documentation', 'js', 'plugin'); ?>
	</div>

			*/


jeedom.plugin.get({
	id: 'watchdog',
	full: 1,
	error: function (error) {
		jeedomUtils.showAlert({
			message: error.message,
			level: 'danger'
		})
	},
	success: function (data) {


		$target = '';
		if (isset(data.documentation_beta) && data.documentation_beta != '' && data.update.configuration.version == 'beta') {
			$target = data.documentation_beta;
		}
		else if (isset(data.documentation) && data.documentation != '') {
			$target = data.documentation;
		}

		if ($target != '') {

			const conteneur = document.getElementById('insert_documentation');
			if (conteneur) {
				const monBouton = document.createElement('button');

				const icone = document.createElement('i');
				icone.classList.add('fas', 'fa-book');

				const texteBouton = document.createElement('span');
				texteBouton.textContent = " "; // Notez l'espace avant le texte pour séparer de l'icône
				monBouton.appendChild(icone);
				monBouton.appendChild(texteBouton);
				monBouton.title = "{{Accéder à la documentation}}";
				monBouton.classList.add('btn-primary');
				monBouton.classList.add('btn');
				monBouton.style.marginRight = '5px';

				monBouton.addEventListener('click', function () {
					window.open($target, '_blank');
				});

				conteneur.appendChild(monBouton);

			}
		}
	}
})