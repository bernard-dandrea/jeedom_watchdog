// Last Modified : 2025/09/02 11:49:57

// affiche le bouton Documentation (code repris de plugin.js)
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

		spanRightButton = document.querySelector('[data-action="configure"]');

		title = '{{Accéder à la documentation du plugin}}';
		if (isset(data.documentation_beta) && data.documentation_beta != '' && data.update.configuration.version == 'beta') {
			button = '<a class="btn btn-primary "  style="margin-right:5px" target="_blank" href="' + data.documentation_beta + '" title="' + title + '"><i class="fas fa-book"></i> </a>'
			spanRightButton.insertAdjacentHTML('beforebegin', button)
		}
		else if (isset(data.documentation) && data.documentation != '') {
			button = '<a class="btn btn-primary " style="margin-right:5px" target="_blank" href="' + data.documentation + '" title="' + title + '"><i class="fas fa-book"></i> </a>'
			spanRightButton.insertAdjacentHTML('beforebegin', button)
		}
	}
})