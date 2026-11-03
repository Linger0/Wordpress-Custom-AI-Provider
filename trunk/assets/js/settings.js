( function () {
	'use strict';

	const container = document.getElementById( 'linger-ai-bridge-models' );
	if ( ! container ) {
		return;
	}
	const body = container.querySelector( 'tbody' );
	const template = document.getElementById( 'linger-ai-bridge-model-template' );
	const addButton = container.querySelector( '[data-add-model]' );
	const maxModels = Number( container.dataset.maxModels );
	let nextIndex = body.rows.length;

	function refresh() {
		const rows = Array.from( body.rows );
		addButton.disabled = rows.length >= maxModels;
		container.querySelector( '[data-model-limit]' ).hidden = ! addButton.disabled;
		rows.forEach( function ( row ) {
			const id = row.querySelector( '[data-model-id]' ).value.trim();
			const radio = row.querySelector( '[data-model-default]' );
			radio.value = id;
			radio.disabled = ! id || ! row.querySelector( '[data-model-enabled]' ).checked;
			if ( radio.disabled ) {
				radio.checked = false;
			}
			row.querySelector( '[data-remove-model]' ).disabled = rows.length === 1;
			row.querySelector( '[data-model-stream]' ).disabled = row.querySelector( '[data-model-endpoint]' ).value !== 'responses';
		} );
		if ( ! body.querySelector( '[data-model-default]:checked' ) ) {
			const first = body.querySelector( '[data-model-default]:not(:disabled)' );
			if ( first ) {
				first.checked = true;
			}
		}
	}

	addButton.addEventListener( 'click', function () {
		if ( body.rows.length >= maxModels ) {
			return;
		}
		const fragment = template.content.cloneNode( true );
		fragment.querySelectorAll( '[name]' ).forEach( function ( input ) {
			input.name = input.name.replace( '__INDEX__', String( nextIndex ) );
		} );
		nextIndex += 1;
		body.appendChild( fragment );
		const row = body.lastElementChild;
		row.querySelector( '[data-model-enabled]' ).checked = true;
		refresh();
		row.querySelector( '[data-model-id]' ).focus();
	} );

	body.addEventListener( 'click', function ( event ) {
		const button = event.target.closest( '[data-remove-model]' );
		if ( ! button || body.rows.length <= 1 ) {
			return;
		}
		const row = button.closest( 'tr' );
		const focusRow = row.nextElementSibling || row.previousElementSibling;
		row.remove();
		refresh();
		focusRow.querySelector( '[data-model-id]' ).focus();
	} );
	body.addEventListener( 'input', refresh );
	body.addEventListener( 'change', refresh );
	refresh();
}() );
