document.addEventListener( 'DOMContentLoaded', function() {
	const aiModel      = document.getElementById('aimodel_id');
	const agentName    = document.querySelector('.agent_name');
	const agentDesc    = document.querySelector('.agent_desc');
	const agentPersona = document.querySelector('.agent_persona');
	const colorFields  = document.querySelectorAll( '.dappier-color-picker' );

	// If no agents exist, the default is to create. Show fields.
	if ( '_create_agent' === aiModel.value ) {
		agentName.style.display    = 'block';
		agentDesc.style.display    = 'block';
		agentPersona.style.display = 'block';
	}

	// Hide/show the create agent fields.
	document.getElementById('aimodel_id').addEventListener('change', function() {
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

	// If we have color fields.
	if ( colorFields.length ) {
		// Initialize them.
		colorFields.forEach(field => {
			jQuery(field).wpColorPicker();
		});
	}
});