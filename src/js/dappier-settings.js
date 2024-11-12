document.addEventListener( 'DOMContentLoaded', function() {
	// Hide/show the create agent fields.
	document.getElementById('aimodel_id').addEventListener('change', function() {
		var agentName    = document.querySelector('.agent_name');
		var agentDesc    = document.querySelector('.agent_desc');
		var agentPersona = document.querySelector('.agent_persona');

		// If creating.
		if ( '_create_agent' === this.value ) {
			agentName.style.display    = 'block';
			agentDesc.style.display    = 'block';
			agentPersona.style.display = 'block';
		}
		// Not creating.
		else {
			agentName.style.display    = 'none';
			agentDesc.style.display    = 'none';
			agentPersona.style.display = 'none';
		}
	});

	// Get all color fields.
	const colorFields = document.querySelectorAll( '.dappier-color-picker' );

	// If we have color fields.
	if ( colorFields.length ) {
		// Initialize them.
		colorFields.forEach(field => {
			jQuery(field).wpColorPicker();
		});
	}
});