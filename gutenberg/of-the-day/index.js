// License: GPLv2+
// https://gist.github.com/pento/cf38fd73ce0f13fcf0f0ae7d6c4b685d

var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	SelectControl = wp.components.SelectControl,
	InspectorControls = wp.editor.InspectorControls;

// https://fontawesome.com/icons/calendar-check?style=regular
const iconCalendar = el('svg', { width: 20, height: 20, viewBox: '0 0 482 512' },
 el('path', { d: "M400 64h-48V12c0-6.627-5.373-12-12-12h-40c-6.627 0-12 5.373-12 12v52H160V12c0-6.627-5.373-12-12-12h-40c-6.627 0-12 5.373-12 12v52H48C21.49 64 0 85.49 0 112v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 400H54a6 6 0 0 1-6-6V160h352v298a6 6 0 0 1-6 6zm-52.849-200.65L198.842 404.519c-4.705 4.667-12.303 4.637-16.971-.068l-75.091-75.699c-4.667-4.705-4.637-12.303.068-16.971l22.719-22.536c4.705-4.667 12.303-4.637 16.97.069l44.104 44.461 111.072-110.181c4.705-4.667 12.303-4.637 16.971.068l22.536 22.718c4.667 4.705 4.636 12.303-.069 16.97z" } )
);

registerBlockType( 'lezwatchtv/of-the-day', {
	title: '... Of The Day (LezWatchTV)',
	icon: iconCalendar,
	category: 'lezwatchtv',

	edit: function( props ) {
		return [
			el( ServerSideRender, {
				block: 'lezwatchtv/of-the-day',
				attributes: props.attributes,
			} ),
			el( InspectorControls, {},
				el( SelectControl, {
					type: 'string',
					label: 'Content Type',
					value: props.attributes.otd,
					onChange: ( value ) => { props.setAttributes( { otd: value } ); },
					options: [
						{ value: 'character', label: 'Character' },
						{ value: 'show', label: 'TV Show' },
						{ value: 'death', label: 'Character Death' },
						{ value: 'birthday', label: 'Actor Birthday' },
					],
				} )
			),
		];
	},

	save: function() {
		return null;
	},
} );
