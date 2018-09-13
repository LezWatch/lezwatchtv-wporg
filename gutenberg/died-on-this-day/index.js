// License: GPLv2+
// https://gist.github.com/pento/cf38fd73ce0f13fcf0f0ae7d6c4b685d

var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	DateTimePicker = wp.components.DateTimePicker,
	InspectorControls = wp.editor.InspectorControls;

// https://fontawesome.com/icons/dizzy?style=regular
const iconDizzy = el('svg', { width: 20, height: 20, viewBox: '0 0 496 512' },
 el('path', { d: "M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm0 448c-110.3 0-200-89.7-200-200S137.7 56 248 56s200 89.7 200 200-89.7 200-200 200zm-33.8-217.9c7.8-7.8 7.8-20.5 0-28.3L196.3 192l17.9-17.9c7.8-7.8 7.8-20.5 0-28.3-7.8-7.8-20.5-7.8-28.3 0L168 163.7l-17.8-17.8c-7.8-7.8-20.5-7.8-28.3 0-7.8 7.8-7.8 20.5 0 28.3l17.9 17.9-17.9 17.9c-7.8 7.8-7.8 20.5 0 28.3 7.8 7.8 20.5 7.8 28.3 0l17.8-17.8 17.8 17.8c7.9 7.7 20.5 7.7 28.4-.2zm160-92.2c-7.8-7.8-20.5-7.8-28.3 0L328 163.7l-17.8-17.8c-7.8-7.8-20.5-7.8-28.3 0-7.8 7.8-7.8 20.5 0 28.3l17.9 17.9-17.9 17.9c-7.8 7.8-7.8 20.5 0 28.3 7.8 7.8 20.5 7.8 28.3 0l17.8-17.8 17.8 17.8c7.8 7.8 20.5 7.8 28.3 0 7.8-7.8 7.8-20.5 0-28.3l-17.8-18 17.9-17.9c7.7-7.8 7.7-20.4 0-28.2zM248 272c-35.3 0-64 28.7-64 64s28.7 64 64 64 64-28.7 64-64-28.7-64-64-64z" } )
);

registerBlockType( 'lezwatchtv/died-on-this-day', {
	title: 'Died On This Day (LezWatchTV)',
	icon: iconDizzy,
	category: 'lezwatchtv',

	edit: function( props ) {
		return [
			el( ServerSideRender, {
				block: 'lezwatchtv/died-on-this-day',
				attributes: props.attributes,
			} ),
			el( InspectorControls, {},
				el( DateTimePicker, {
					currentTime : props.attributes.date,
					locale : 'en',
					onChange: ( selected ) => { props.setAttributes( { date: selected } ); },
					selected : props.attributes.date
				} )
			),
		];
	},

	save: function() {
		return null;
	},
} );
