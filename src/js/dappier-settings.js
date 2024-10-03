/**
 * Hide/show the create agent fields.
 *
 * @since 0.1.0
 */
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